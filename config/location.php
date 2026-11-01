<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop location (used by the public Map page)
    |--------------------------------------------------------------------------
    |
    | Decoded from the Google Maps place link / Plus Code 64CR+4QX (7MFW64CR+4QX).
    | Every value can be overridden in .env.
    |
    */

    'name' => env('SHOP_NAME', 'Chill Doi Camping & Farm'),
    'lat' => (float) env('SHOP_LAT', 19.220312),
    'lng' => (float) env('SHOP_LNG', 98.141937),
    'plus_code' => env('SHOP_PLUS_CODE', '64CR+4QX'),

    // Google Maps "place" page (reviews, photos, opening hours). Opens the app on phones.
    'place_url' => env('SHOP_PLACE_URL', 'https://www.google.com/maps?q=64CR+4QX+Chill+Doi+Camping+%26+Farm,+Huai+Pu+Ling,+Mueang+Mae+Hong+Son+District,+Mae+Hong+Son+58000&ftid=0x30da990074f5f563:0xd34cc81c9661637c'),

    // Optional plain-text driving directions shown under the map (one paragraph per line).
    'directions_th' => env('SHOP_DIRECTIONS_TH', ''),
    'directions_en' => env('SHOP_DIRECTIONS_EN', ''),

];
