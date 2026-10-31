<?php

namespace App\Support;

class BahtText
{
    private const THAI_DIGITS = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];

    private const THAI_PLACES = [2 => 'ร้อย', 3 => 'พัน', 4 => 'หมื่น', 5 => 'แสน'];

    private const EN_ONES = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];

    private const EN_TENS = [2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'];

    /**
     * 1234.50 => หนึ่งพันสองร้อยสามสิบสี่บาทห้าสิบสตางค์ ; 300 => สามร้อยบาทถ้วน
     */
    public static function thai(float|int|string $amount): string
    {
        [$baht, $satang] = self::split($amount);

        $text = ($baht === 0 ? self::THAI_DIGITS[0] : self::thaiNumber($baht)).'บาท';

        return $satang === 0 ? $text.'ถ้วน' : $text.self::thaiNumber($satang).'สตางค์';
    }

    /**
     * 1234.50 => One thousand two hundred thirty-four baht and fifty satang only
     */
    public static function english(float|int|string $amount): string
    {
        [$baht, $satang] = self::split($amount);

        $text = ($baht === 0 ? 'zero' : self::englishNumber($baht)).' baht';

        if ($satang > 0) {
            $text .= ' and '.self::englishNumber($satang).' satang';
        }

        return ucfirst($text.' only');
    }

    private static function split(float|int|string $amount): array
    {
        $satang = (int) round(((float) $amount) * 100);

        return [intdiv($satang, 100), $satang % 100];
    }

    private static function thaiNumber(int $n): string
    {
        if ($n >= 1000000) {
            $high = intdiv($n, 1000000);
            $low = $n % 1000000;

            return self::thaiNumber($high).'ล้าน'.($low > 0 ? self::thaiBelowMillion($low, true) : '');
        }

        return self::thaiBelowMillion($n, false);
    }

    private static function thaiBelowMillion(int $n, bool $hasHigher): string
    {
        $text = '';
        $digits = array_reverse(str_split((string) $n));

        for ($place = count($digits) - 1; $place >= 0; $place--) {
            $digit = (int) $digits[$place];

            if ($digit === 0) {
                continue;
            }

            if ($place === 0) {
                $prior = $hasHigher || $n >= 10;
                $text .= ($digit === 1 && $prior) ? 'เอ็ด' : self::THAI_DIGITS[$digit];
            } elseif ($place === 1) {
                $text .= match ($digit) {
                    1 => 'สิบ',
                    2 => 'ยี่สิบ',
                    default => self::THAI_DIGITS[$digit].'สิบ',
                };
            } else {
                $text .= self::THAI_DIGITS[$digit].self::THAI_PLACES[$place];
            }
        }

        return $text;
    }

    private static function englishNumber(int $n): string
    {
        $scales = [1000000000 => 'billion', 1000000 => 'million', 1000 => 'thousand'];

        foreach ($scales as $value => $name) {
            if ($n >= $value) {
                $rest = $n % $value;

                return self::englishNumber(intdiv($n, $value)).' '.$name.($rest > 0 ? ' '.self::englishNumber($rest) : '');
            }
        }

        if ($n >= 100) {
            $rest = $n % 100;

            return self::EN_ONES[intdiv($n, 100)].' hundred'.($rest > 0 ? ' '.self::englishNumber($rest) : '');
        }

        if ($n >= 20) {
            $ones = $n % 10;

            return self::EN_TENS[intdiv($n, 10)].($ones > 0 ? '-'.self::EN_ONES[$ones] : '');
        }

        return self::EN_ONES[$n];
    }
}
