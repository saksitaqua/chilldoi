<?php

namespace App\Support;

/**
 * Builds the EMVCo / Thai QR Payment string that banking apps read as a PromptPay QR.
 */
class PromptPay
{
    /**
     * @param  string  $target  phone (10 digits), national/tax ID (13 digits) or e-wallet ID (15 digits)
     * @param  float|null  $amount  null = the payer types the amount
     * @return string|null null when the target is not a usable PromptPay ID
     */
    public static function payload(string $target, ?float $amount = null): ?string
    {
        $digits = preg_replace('/\D+/', '', $target);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $tag = '01';
            $account = '0066'.substr($digits, 1);
        } elseif (strlen($digits) === 13) {
            $tag = '02';
            $account = $digits;
        } elseif (strlen($digits) === 15) {
            $tag = '03';
            $account = $digits;
        } else {
            return null;
        }

        $hasAmount = $amount !== null && $amount > 0;

        $data = self::field('00', '01')
            .self::field('01', $hasAmount ? '12' : '11')
            .self::field('29', self::field('00', 'A000000677010111').self::field($tag, $account))
            .self::field('53', '764')
            .($hasAmount ? self::field('54', number_format($amount, 2, '.', '')) : '')
            .self::field('58', 'TH')
            .'6304';

        return $data.self::crc16($data);
    }

    private static function field(string $id, string $value): string
    {
        return $id.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    /** CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF) as 4 upper-case hex digits. */
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;

        foreach (str_split($data) as $char) {
            $crc ^= ord($char) << 8;

            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
