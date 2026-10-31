<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seller shown on receipts
    |--------------------------------------------------------------------------
    |
    | The shop is run by an individual and is not VAT registered, so receipts are
    | plain "receipts" (not tax invoices). Every value can be overridden in .env.
    |
    */

    'seller' => [
        'trade_name' => env('RECEIPT_TRADE_NAME', 'Chill Doi Camping & Farm'),
        'name_th' => env('RECEIPT_SELLER_NAME_TH', 'นายไพรวัลย์ ศรศรี'),
        'name_en' => env('RECEIPT_SELLER_NAME_EN', ''),
        'address_th' => env('RECEIPT_ADDRESS_TH', '54 หมู่ที่ 1 ต.ห้วยปูลิง อ.เมืองแม่ฮ่องสอน จังหวัดแม่ฮ่องสอน'),
        'address_en' => env('RECEIPT_ADDRESS_EN', '54 Moo 1, Huai Pu Ling Sub-district, Mueang Mae Hong Son District, Mae Hong Son Province'),
        'phone' => env('RECEIPT_PHONE', '086-922-7117 / 091-067-0574'),
        'email' => env('RECEIPT_EMAIL', 'sales@chilldoicampingandfarm.com'),
        'tax_id' => env('RECEIPT_TAX_ID', ''),
    ],

    // Receipt numbers look like INYY-mmxxxx: IN + 2-digit year, a dash, 2-digit month and a
    // 4-digit running number that starts again every year (e.g. IN69-100001 in Oct 2569).
    'prefix' => env('RECEIPT_PREFIX', 'IN'),

    // 'be' = Buddhist Era (2569), 'ce' = Christian Era (2026) for the year in the number and dates.
    'year_era' => env('RECEIPT_YEAR_ERA', 'be'),

];
