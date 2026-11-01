<?php

namespace App\Support;

/**
 * Minimal QR Code generator (byte mode, error correction level M, versions 1-6 = up to 106 bytes),
 * rendered as inline SVG. Enough for a PromptPay payload; no external library or service needed.
 */
class QrCode
{
    /** Level M: version => [ecc codewords per block, [[block count, data codewords per block], ...]] */
    private const BLOCKS = [
        1 => [10, [[1, 16]]],
        2 => [16, [[1, 28]]],
        3 => [26, [[1, 44]]],
        4 => [18, [[2, 32]]],
        5 => [24, [[2, 43]]],
        6 => [16, [[4, 27]]],
    ];

    private const ALIGNMENT = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
    ];

    private static array $exp = [];

    private static array $log = [];

    public static function svg(string $text, int $quiet = 4): string
    {
        $matrix = self::matrix($text);
        $size = count($matrix);
        $total = $size + $quiet * 2;

        $path = '';
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $dark) {
                if ($dark) {
                    $path .= 'M'.($x + $quiet).' '.($y + $quiet).'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$total.' '.$total.'" shape-rendering="crispEdges" role="img">'
            .'<rect width="'.$total.'" height="'.$total.'" fill="#fff"/>'
            .'<path d="'.$path.'" fill="#000"/></svg>';
    }

    /** @return array<int, array<int, bool>> rows of modules (true = dark) */
    public static function matrix(string $text): array
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        $version = self::pickVersion(count($bytes));
        $codewords = self::interleave(self::dataCodewords($bytes, $version), $version);

        $size = 17 + 4 * $version;
        [$base, $isFunction] = self::functionPatterns($version, $size);

        $bits = [];
        foreach ($codewords as $word) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($word >> $i) & 1;
            }
        }
        $base = self::placeData($base, $isFunction, $bits, $size);

        $best = null;
        $bestPenalty = PHP_INT_MAX;
        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = self::applyMask($base, $isFunction, $mask, $size);
            self::drawFormat($candidate, $mask, $size);
            $penalty = self::penalty($candidate, $size);

            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $best = $candidate;
            }
        }

        return array_map(fn ($row) => array_map(fn ($cell) => (bool) $cell, $row), $best);
    }

    private static function pickVersion(int $length): int
    {
        foreach (self::BLOCKS as $version => [$ecc, $groups]) {
            $capacity = 0;
            foreach ($groups as [$count, $data]) {
                $capacity += $count * $data;
            }

            // mode (4) + length (8) + payload, all in bits
            if (4 + 8 + $length * 8 <= $capacity * 8) {
                return $version;
            }
        }

        throw new \InvalidArgumentException('Text is too long for this QR generator.');
    }

    private static function dataCodewords(array $bytes, int $version): array
    {
        $capacity = 0;
        foreach (self::BLOCKS[$version][1] as [$count, $data]) {
            $capacity += $count * $data;
        }

        $bits = [0, 1, 0, 0];
        foreach ([count($bytes)] as $length) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($length >> $i) & 1;
            }
        }
        foreach ($bytes as $byte) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($byte >> $i) & 1;
            }
        }

        for ($i = 0; $i < 4 && count($bits) < $capacity * 8; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $words = [];
        foreach (array_chunk($bits, 8) as $chunk) {
            $words[] = bindec(implode('', $chunk));
        }
        for ($pad = 0xEC; count($words) < $capacity; $pad ^= 0xEC ^ 0x11) {
            $words[] = $pad;
        }

        return $words;
    }

    private static function interleave(array $data, int $version): array
    {
        [$eccLength, $groups] = self::BLOCKS[$version];

        $dataBlocks = [];
        $offset = 0;
        foreach ($groups as [$count, $length]) {
            for ($i = 0; $i < $count; $i++) {
                $dataBlocks[] = array_slice($data, $offset, $length);
                $offset += $length;
            }
        }

        $eccBlocks = array_map(fn ($block) => self::reedSolomon($block, $eccLength), $dataBlocks);

        $result = [];
        $longest = max(array_map('count', $dataBlocks));
        for ($i = 0; $i < $longest; $i++) {
            foreach ($dataBlocks as $block) {
                if (isset($block[$i])) {
                    $result[] = $block[$i];
                }
            }
        }
        for ($i = 0; $i < $eccLength; $i++) {
            foreach ($eccBlocks as $block) {
                $result[] = $block[$i];
            }
        }

        return $result;
    }

    private static function gfInit(): void
    {
        if (self::$exp) {
            return;
        }

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return self::$exp[self::$log[$a] + self::$log[$b]];
    }

    private static function reedSolomon(array $data, int $degree): array
    {
        self::gfInit();

        // Generator polynomial coefficients (leading 1 omitted), highest power first.
        $divisor = array_fill(0, $degree, 0);
        $divisor[$degree - 1] = 1;
        $root = 1;
        for ($i = 0; $i < $degree; $i++) {
            for ($j = 0; $j < $degree; $j++) {
                $divisor[$j] = self::gfMul($divisor[$j], $root);
                if ($j + 1 < $degree) {
                    $divisor[$j] ^= $divisor[$j + 1];
                }
            }
            $root = self::gfMul($root, 2);
        }

        $result = array_fill(0, $degree, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($result);
            $result[] = 0;
            foreach ($divisor as $i => $coefficient) {
                $result[$i] ^= self::gfMul($coefficient, $factor);
            }
        }

        return $result;
    }

    /** @return array{0: array, 1: array} modules and a same-shaped "is function module" map */
    private static function functionPatterns(int $version, int $size): array
    {
        $modules = array_fill(0, $size, array_fill(0, $size, 0));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        $set = function (int $x, int $y, bool $dark) use (&$modules, &$isFunction) {
            $modules[$y][$x] = $dark ? 1 : 0;
            $isFunction[$y][$x] = true;
        };

        for ($i = 0; $i < $size; $i++) {
            $set(6, $i, $i % 2 === 0);
            $set($i, 6, $i % 2 === 0);
        }

        foreach ([[3, 3], [$size - 4, 3], [3, $size - 4]] as [$cx, $cy]) {
            for ($dy = -4; $dy <= 4; $dy++) {
                for ($dx = -4; $dx <= 4; $dx++) {
                    $x = $cx + $dx;
                    $y = $cy + $dy;
                    if ($x < 0 || $x >= $size || $y < 0 || $y >= $size) {
                        continue;
                    }
                    $distance = max(abs($dx), abs($dy));
                    $set($x, $y, $distance !== 2 && $distance !== 4);
                }
            }
        }

        $positions = self::ALIGNMENT[$version];
        $last = count($positions) - 1;
        foreach ($positions as $i => $cx) {
            foreach ($positions as $j => $cy) {
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $last) || ($i === $last && $j === 0)) {
                    continue;
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $set($cx + $dx, $cy + $dy, max(abs($dx), abs($dy)) !== 1);
                    }
                }
            }
        }

        // Reserve the format-information areas (real bits are drawn per mask).
        $tmp = $modules;
        self::drawFormat($tmp, 0, $size);
        for ($i = 0; $i < 9; $i++) {
            $isFunction[8][$i] = $isFunction[$i][8] = true;
        }
        for ($i = 0; $i < 8; $i++) {
            $isFunction[8][$size - 1 - $i] = true;
            $isFunction[$size - 1 - $i][8] = true;
        }

        return [$tmp, $isFunction];
    }

    private static function placeData(array $modules, array $isFunction, array $bits, int $size): array
    {
        $i = 0;
        $count = count($bits);

        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }
            for ($vertical = 0; $vertical < $size; $vertical++) {
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    $upward = (($right + 1) & 2) === 0;
                    $y = $upward ? $size - 1 - $vertical : $vertical;

                    if (! $isFunction[$y][$x] && $i < $count) {
                        $modules[$y][$x] = $bits[$i++];
                    }
                }
            }
        }

        return $modules;
    }

    private static function applyMask(array $modules, array $isFunction, int $mask, int $size): array
    {
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($isFunction[$y][$x]) {
                    continue;
                }

                $invert = match ($mask) {
                    0 => ($x + $y) % 2 === 0,
                    1 => $y % 2 === 0,
                    2 => $x % 3 === 0,
                    3 => ($x + $y) % 3 === 0,
                    4 => (intdiv($x, 3) + intdiv($y, 2)) % 2 === 0,
                    5 => $x * $y % 2 + $x * $y % 3 === 0,
                    6 => ($x * $y % 2 + $x * $y % 3) % 2 === 0,
                    default => (($x + $y) % 2 + $x * $y % 3) % 2 === 0,
                };

                if ($invert) {
                    $modules[$y][$x] ^= 1;
                }
            }
        }

        return $modules;
    }

    private static function drawFormat(array &$modules, int $mask, int $size): void
    {
        $data = (0 << 3) | $mask; // error correction level M = 00
        $remainder = $data;
        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ (($remainder >> 9) * 0x537);
        }
        $bits = (($data << 10) | $remainder) ^ 0x5412;

        $bit = fn (int $i) => ($bits >> $i) & 1;

        for ($i = 0; $i <= 5; $i++) {
            $modules[$i][8] = $bit($i);
        }
        $modules[7][8] = $bit(6);
        $modules[8][8] = $bit(7);
        $modules[8][7] = $bit(8);
        for ($i = 9; $i < 15; $i++) {
            $modules[8][14 - $i] = $bit($i);
        }

        for ($i = 0; $i < 8; $i++) {
            $modules[8][$size - 1 - $i] = $bit($i);
        }
        for ($i = 8; $i < 15; $i++) {
            $modules[$size - 15 + $i][8] = $bit($i);
        }
        $modules[$size - 8][8] = 1; // always-dark module
    }

    private static function penalty(array $modules, int $size): int
    {
        $score = 0;
        $dark = 0;
        $lines = [];

        for ($y = 0; $y < $size; $y++) {
            $lines[] = implode('', $modules[$y]);
            $dark += array_sum($modules[$y]);
        }
        for ($x = 0; $x < $size; $x++) {
            $lines[] = implode('', array_column($modules, $x));
        }

        foreach ($lines as $line) {
            if (preg_match_all('/0{5,}|1{5,}/', $line, $runs)) {
                foreach ($runs[0] as $run) {
                    $score += 3 + strlen($run) - 5;
                }
            }
            foreach (['10111010000', '00001011101'] as $pattern) {
                $offset = 0;
                while (($pos = strpos($line, $pattern, $offset)) !== false) {
                    $score += 40;
                    $offset = $pos + 1;
                }
            }
        }

        for ($y = 0; $y < $size - 1; $y++) {
            for ($x = 0; $x < $size - 1; $x++) {
                $color = $modules[$y][$x];
                if ($color === $modules[$y][$x + 1] && $color === $modules[$y + 1][$x] && $color === $modules[$y + 1][$x + 1]) {
                    $score += 3;
                }
            }
        }

        $total = $size * $size;
        $score += (int) (ceil(abs($dark * 20 - $total * 10) / $total) - 1) * 10;

        return $score;
    }
}
