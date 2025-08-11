<?php

use App\Libraries\Aws;
use App\Libraries\Azure;
use App\Libraries\Google;
use App\Libraries\IBM;

function getLanguageNameFromISOcode($languageCode = NULL)
{
    /*
        |
        |    return the Language Name from ISO-Language code.
        |    
        |    eg. $languageCode = 'hi' will returns 'Hindi'
        |
        |    if nothing is passed as $languageCode then returns a whole associative array of
        |    language names of ISO-language code.
        |    eg. ['hi'=>'Hindi', 'gu'=>'Gujarati' .....]
        |    
        */

    $isoLanguages =  array(
        'aa' => 'Afar',
        'ab' => 'Abkhaz',
        'ae' => 'Avestan',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic',
        'an' => 'Aragonese',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'av' => 'Avaric',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bh' => 'Bihari',
        'bi' => 'Bislama',
        'bm' => 'Bambara',
        'bn' => 'Bengali',
        'bo' => 'Tibetan Standard, Tibetan, Central',
        'br' => 'Breton',
        'bs' => 'Bosnian',
        'ca' => 'Catalan; Valencian',
        'ce' => 'Chechen',
        'ch' => 'Chamorro',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'cs' => 'Czech',
        'cu' => 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic',
        'cv' => 'Chuvash',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de' => 'German',
        'dv' => 'Divehi / Dhivehi / Maldivian;',
        'dz' => 'Dzongkha',
        'ee' => 'Ewe',
        'el' => 'Greek / Modern',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Spanish / Castilian',
        'et' => 'Estonian',
        'eu' => 'Basque',
        'fa' => 'Persian',
        'ff' => 'Fula / Fulah / Pulaar / Pular',
        'fi' => 'Finnish',
        'fj' => 'Fijian',
        'fo' => 'Faroese',
        'fr' => 'French',
        'fy' => 'Western Frisian',
        'ga' => 'Irish',
        'gd' => 'Scottish Gaelic / Gaelic',
        'gl' => 'Galician',
        'gn' => 'GuaranÃ­',
        'gu' => 'Gujarati',
        'gv' => 'Manx',
        'ha' => 'Hausa',
        'he' => 'Hebrew (modern)',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hr' => 'Croatian',
        'ht' => 'Haitian; Haitian Creole',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'hz' => 'Herero',
        'ia' => 'Interlingua',
        'id' => 'Indonesian',
        'ie' => 'Interlingue',
        'ig' => 'Igbo',
        'ii' => 'Nuosu',
        'ik' => 'Inupiaq',
        'io' => 'Ido',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'iu' => 'Inuktitut',
        'ja' => 'Japanese (ja)',
        'jv' => 'Javanese (jv)',
        'ka' => 'Georgian',
        'kg' => 'Kongo',
        'ki' => 'Kikuyu, Gikuyu',
        'kj' => 'Kwanyama, Kuanyama',
        'kk' => 'Kazakh',
        'kl' => 'Kalaallisut, Greenlandic',
        'km' => 'Khmer',
        'kn' => 'Kannada',
        'ko' => 'Korean',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri',
        'ku' => 'Kurdish',
        'kv' => 'Komi',
        'kw' => 'Cornish',
        'ky' => 'Kirghiz, Kyrgyz',
        'la' => 'Latin',
        'lb' => 'Luxembourgish, Letzeburgesch',
        'lg' => 'Luganda',
        'li' => 'Limburgish, Limburgan, Limburger',
        'ln' => 'Lingala',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga',
        'lv' => 'Latvian',
        'mg' => 'Malagasy',
        'mh' => 'Marshallese',
        'mi' => 'Maori',
        'mk' => 'Macedonian',
        'ml' => 'Malayalam',
        'mn' => 'Mongolian',
        'mr' => 'Marathi',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'my' => 'Burmese',
        'na' => 'Nauru',
        'nb' => 'Norwegian Bokmål',
        'nd' => 'North Ndebele',
        'ne' => 'Nepali',
        'ng' => 'Ndonga',
        'nl' => 'Dutch',
        'nn' => 'Norwegian Nynorsk',
        'no' => 'Norwegian',
        'nr' => 'South Ndebele',
        'nv' => 'Navajo, Navaho',
        'ny' => 'Chichewa; Chewa; Nyanja',
        'oc' => 'Occitan',
        'oj' => 'Ojibwe, Ojibwa',
        'om' => 'Oromo',
        'or' => 'Oriya',
        'os' => 'Ossetian / Ossetic',
        'pa' => 'Punjabi',
        'pi' => 'Pali',
        'pl' => 'Polish',
        'ps' => 'Pashto, Pushto',
        'pt' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'rn' => 'Kirundi',
        'ro' => 'Romanian / Moldavian / Moldovan',
        'ru' => 'Russian',
        'rw' => 'Kinyarwanda',
        'sa' => 'Sanskrit',
        'sc' => 'Sardinian',
        'sd' => 'Sindhi',
        'se' => 'Northern Sami',
        'sg' => 'Sango',
        'si' => 'Sinhala / Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovene',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'so' => 'Somali',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'ss' => 'Swati',
        'st' => 'Southern Sotho',
        'su' => 'Sundanese',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'tg' => 'Tajik',
        'th' => 'Thai',
        'ti' => 'Tigrinya',
        'tk' => 'Turkmen',
        'tl' => 'Tagalog',
        'tn' => 'Tswana',
        'to' => 'Tonga (Tonga Islands)',
        'tr' => 'Turkish',
        'ts' => 'Tsonga',
        'tt' => 'Tatar',
        'tw' => 'Twi',
        'ty' => 'Tahitian',
        'ug' => 'Uighur, Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'Volapük',
        'wa' => 'Walloon',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang  /  Chuang',
        'zh' => 'Chinese',
        'zu' => 'Zulu',
        'hsb' => 'Upper Sorbian',
        'arn' => 'Mapudungun / Mapuche',
        'dsb' => 'Lower sorbian',
        'fil' => 'Filipino / Pilipino',
        'gsw' => 'Swiss German',
        'kok' => 'konkani',
        'moh' => 'Mohawk',
        'nso' => 'Northern Sotho / Pedi / Sepedi',
        'prs' => 'Afghan Persian / Dari',
        'qut' => 'West Central Quiché',
        'sah' => 'Yakut',
        'sma' => 'Southern Sámi ',
        'smj' => 'Lule Sami',
        'sms' => 'Skolt Sami',
        'smn' => 'Inari Sami',
        'syr' => 'Syriac',
        'tzm' => 'Central Atlas Tamazight',
        'cmn' => 'Mandarin Chinese',
        'yue' => 'Yue Chinese',
        'GB' => 'Wales'
    );

    if (!($languageCode === NULL)) {
        return $isoLanguages[strtolower($languageCode)];
    }

    return $isoLanguages;
}
function getCountryNameFromISOcode($countryCode = NULL)
{
    /*
        |
        |    return the Country Name from ISO-Country code.
        |    
        |    eg. $languageCode = 'IN' will return 'India'.
        |
        |    if nothing is passed as $languageCode then returns a whole associative array of
        |    language names of ISO-language code. 
        |    eg. ['hi'=>'Hindi', 'gu'=>'Gujarati' .....]
        |    
        */

    $isoContry =  array(
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas the',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island (Bouvetoya)',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CS' => 'Serbia and Montenegro',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros the',
        'CD' => 'Congo',
        'CG' => 'Congo the',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FO' => 'Faroe Islands',
        'FK' => 'Falkland Islands (Malvinas)',
        'FJ' => 'Fiji the Fiji Islands',
        'FI' => 'Finland',
        'FR' => 'France, French Republic',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia the',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea',
        'KR' => 'Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyz Republic',
        'LA' => 'Lao',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'AN' => 'Netherlands Antilles',
        'NL' => 'Netherlands the',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Islands',
        'PL' => 'Poland',
        'PT' => 'Portugal, Portuguese Republic',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia (Slovak Republic)',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia, Somali Republic',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen Islands',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland, Swiss Confederation',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States of America',
        'UM' => 'United States Minor Outlying Islands',
        'VI' => 'United States Virgin Islands',
        'UY' => 'Uruguay, Eastern Republic of',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'XA' => "Switzerland",
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
        'GB-WLS' => "united kingdom"
    );

    if (!($countryCode === NULL)) {
        return $isoContry['' . strtoupper($countryCode)];
    }

    return $isoContry;
}

function language_codes($isoCode = NULL)
{
    /*
        |
        |    return language name associate with country name from ISO code.
        |    
        |    eg. $isoCode = 'hi-IN' =>  'Hindi India'
        |
        |    if nothing is passed as $isoCode then returns a whole associative array of 
        |    language name with associative country.
        |
        |    eg. ['hi-In'=>'Hindi India', 'gu-IN'=>'Gujarati India' ...]
        |
        */


    if (($isoCode === NULL || $isoCode === "")) {
        $all = [
            'af-ZA',
            'am-ET',
            'ar-AE',
            'ar-BH',
            'ar-DZ',
            'ar-EG',
            'ar-IQ',
            'ar-JO',
            'ar-KW',
            'ar-LB',
            'ar-LY',
            'ar-MA',
            'arn-CL',
            'ar-OM',
            'ar-QA',
            'ar-SA',
            'ar-SY',
            'ar-TN',
            'ar-YE',
            'as-IN',
            'az-AZ',
            'az-AZ',
            'ba-RU',
            'be-BY',
            'bg-BG',
            'bn-BD',
            'bn-IN',
            'bo-CN',
            'br-FR',
            'bs-BA',
            'bs-BA',
            'ca-ES',
            'co-FR',
            'cs-CZ',
            'cy-GB',
            'da-DK',
            'de-AT',
            'de-CH',
            'de-DE',
            'de-LI',
            'de-LU',
            'dsb-DE',
            'dv-MV',
            'el-GR',
            'en-AU',
            'en-BZ',
            'en-CA',
            'en-GB',
            'en-IE',
            'en-IN',
            'en-JM',
            'en-MY',
            'en-NZ',
            'en-PH',
            'en-SG',
            'en-TT',
            'en-US',
            'en-ZA',
            'en-ZW',
            'es-AR',
            'es-BO',
            'es-CL',
            'es-CO',
            'es-CR',
            'es-DO',
            'es-EC',
            'es-ES',
            'es-GT',
            'es-HN',
            'es-MX',
            'es-NI',
            'es-PA',
            'es-PE',
            'es-PR',
            'es-PY',
            'es-SV',
            'es-US',
            'es-UY',
            'es-VE',
            'et-EE',
            'eu-ES',
            'fa-IR',
            'fi-FI',
            'fil-PH',
            'fo-FO',
            'fr-BE',
            'fr-CA',
            'fr-CH',
            'fr-FR',
            'fr-LU',
            'fr-MC',
            'fy-NL',
            'ga-IE',
            'gd-GB',
            'gl-ES',
            'gsw-FR',
            'gu-IN',
            'ha-NG',
            'he-IL',
            'hi-IN',
            'hr-BA',
            'hr-HR',
            'hsb-DE',
            'hu-HU',
            'hy-AM',
            'id-ID',
            'ig-NG',
            'ii-CN',
            'is-IS',
            'it-CH',
            'it-IT',
            'iu-CA',
            'iu-CA',
            'ja-JP',
            'ka-GE',
            'kk-KZ',
            'kl-GL',
            'km-KH',
            'kn-IN',
            'kok-IN',
            'ko-KR',
            'ky-KG',
            'lb-LU',
            'lo-LA',
            'lt-LT',
            'lv-LV',
            'mi-NZ',
            'mk-MK',
            'ml-IN',
            'mn-MN',
            'moh-CA',
            'mr-IN',
            'ms-BN',
            'ms-MY',
            'mt-MT',
            'nb-NO',
            'ne-NP',
            'nl-BE',
            'nl-NL',
            'nn-NO',
            'nso-ZA',
            'oc-FR',
            'or-IN',
            'pa-IN',
            'pl-PL',
            'prs-AF',
            'ps-AF',
            'pt-BR',
            'pt-PT',
            'qut-GT',
            'rm-CH',
            'ro-RO',
            'ru-RU',
            'rw-RW',
            'sah-RU',
            'sa-IN',
            'se-FI',
            'se-NO',
            'se-SE',
            'si-LK',
            'sk-SK',
            'sl-SI',
            'sma-NO',
            'sma-SE',
            'smj-NO',
            'smj-SE',
            'smn-FI',
            'sms-FI',
            'sq-AL',
            'sr-BA',
            'sr-CS',
            'sr-ME',
            'sr-RS',
            'sr-BA',
            'sr-CS',
            'sr-ME',
            'sr-RS',
            'sv-FI',
            'sv-SE',
            'sw-KE',
            'syr-SY',
            'ta-IN',
            'te-IN',
            'tg-TJ',
            'th-TH',
            'tk-TM',
            'tn-ZA',
            'tr-TR',
            'tt-RU',
            'tzm-DZ',
            'ug-CN',
            'uk-UA',
            'ur-PK',
            'uz-UZ',
            'uz-UZ',
            'vi-VN',
            'wo-SN',
            'xh-ZA',
            'yo-NG',
            'zh-CN',
            'zh-HK',
            'zh-MO',
            'zh-SG',
            'zh-TW',
            'zu-ZA',
        ];

        $temp = [];
        foreach ($all as $value) {
            $lang = substr($value, 0, strpos($value, '-'));
            $country = substr($value, strpos($value, '-') + 1);

            $temp[$value] =  getLanguageNameFromISOcode($lang) . ' - ' . getCountryNameFromISOcode($country) ;
        }
        return $temp;
    } else {
        $temp = [];
        $lang = substr($isoCode, 0, strpos($isoCode, '-'));
        $country = substr($isoCode, strpos($isoCode, '-') + 1);
        $temp[$isoCode] =  getLanguageNameFromISOcode($lang) . ' - ' . getCountryNameFromISOcode($country) ;
        return $temp;
    }
}

function get_voices($language,$free_tier = false)
{
    $flag = false;
    $db      = \Config\Database::connect();
    if(!exists(["variable" => 'azure_voices'],"settings")){
        $flag = true;
    }
    if(!exists(["variable" => 'ibm_voices'],"settings")){
        $flag = true;
    }
    if(!exists(["variable" => 'google_voices'],"settings")){
        $flag = true;
    }
    if(!exists(["variable" => 'aws_voices'],"settings")){
        $flag = true;
    }
    if($flag){
        update_all_providers();
    }
    $aws = new Aws;
    $res = array();
    $aws_voices = [];
    $google_voices = [];
    $azure_voices = [];
    $ibm_voices = [];



    if ($tts_config = get_settings('tts_config', true , true)) {
        if($free_tier){
            if ($tts_config['gcpStatus'] == "enable" && in_array("gcp",$tts_config['freeTierAllowedSps'])) {
                $google_voices = fetch_details('settings', ['variable' => 'google_voices']);
            }
            if ($tts_config['amazonPollyStatus'] == "enable"  && in_array("amazonPolly",$tts_config['freeTierAllowedSps'])) {
                $aws_voices = fetch_details('settings', ['variable' => 'aws_voices']);
            }
            if ($tts_config['azureStatus'] == "enable"  && in_array("azure",$tts_config['freeTierAllowedSps'])) {
                $azure_voices = fetch_details('settings', ['variable' => 'azure_voices']);
            }
            if ($tts_config['ibmStatus'] == "enable"  && in_array("ibm",$tts_config['freeTierAllowedSps'])) {
                $ibm_voices = fetch_details('settings', ['variable' => 'ibm_voices']);
            }
        }else{
            if ($tts_config['gcpStatus'] == "enable") {
                $google_voices = fetch_details('settings', ['variable' => 'google_voices']);
            }
            if ($tts_config['amazonPollyStatus'] == "enable" ) {
                $aws_voices = fetch_details('settings', ['variable' => 'aws_voices']);
            }
            if ($tts_config['azureStatus'] == "enable" ) {
                $azure_voices = fetch_details('settings', ['variable' => 'azure_voices']);
            }
            if ($tts_config['ibmStatus'] == "enable"  ) {
                $ibm_voices = fetch_details('settings', ['variable' => 'ibm_voices']);
            }

        }
    }

    if (!empty($aws_voices)) {
        $aws = json_decode($aws_voices[0]['value']);

        foreach ($aws as $key => $val) {
            if ($language == $aws[$key]->language) {
                $aws[$key]->provider_image = base_url('public/provider/aws.svg');

                array_push($res, $aws[$key]);
            }
        }
    }

    if (!empty($azure_voices)) {
        $azure = json_decode($azure_voices[0]['value']);
        foreach ($azure as $key => $val) {
            if ($language == $azure[$key]->language) {
                $azure[$key]->provider_image = base_url('public/provider/azure.svg');
                array_push($res, $azure[$key]);
            }
        }
    }

    if (!empty($ibm_voices)) {
        $ibm = json_decode($ibm_voices[0]['value']);
        foreach ($ibm as $key => $val) {
            if ($language == $ibm[$key]->language) {
                $ibm[$key]->provider_image = base_url('public/provider/ibm.svg');
                array_push($res, $ibm[$key]);
            }
        }
    }

    if (!empty($google_voices)) {
        $google = json_decode($google_voices[0]['value']);
        foreach ($google as $key => $val) {
            if ($language == $google[$key]->language) {
                $google[$key]->provider_image = base_url('public/provider/google.svg');
                array_push($res, $google[$key]);
            }
        }
    }

    return $res;
}
function supported_languages()
{
    $azure = new App\Libraries\Azure();
    $ibm = new App\Libraries\IBM();
    $google = new App\Libraries\Google();
    $aws = new App\Libraries\Aws();
    $google_languages = $google->get_languages();
    $aws_languages = $aws->get_languages();
    $ibm_languages = $ibm->get_languages();
    $language_codes = array_values(array_unique(array_merge($google_languages, $aws_languages, $ibm_languages)));
    $languages = array();
    foreach ($language_codes as $key => $val) {
        $code = language_codes($language_codes[$key]);
        $languages[$val] = $code[$val];
    }
    return $languages;
}
function update_supported_languages()
{
    $languages = json_encode(supported_languages());
    $db      = \Config\Database::connect();
    if (exists(['variable' => 'languages'], 'settings')) {
        $builder = $db->table('settings');
        $builder = $builder->update(['value' => $languages], ['variable' => 'languages']);
    } else {
        $builder = $db->table('settings');
        $builder = $builder->insert(['variable' => 'languages', 'value' => $languages]);
    }
}
function update_aws()
{
    $aws = new Aws;
    $voices = $aws->save_voices()['Voices'];
    $data = [];
    foreach ($voices as $key => $val) {


        $type = $voices[$key]['SupportedEngines'];
        if (in_array('standard', $type)) {
            $arr = [
                'voice' => $voices[$key]['Id'],
                'display_name' => $voices[$key]['Name'],
                'language' => $voices[$key]['LanguageCode'],
                'provider' => 'aws',
                'type' => 'Standard'
            ];
            array_push($data, $arr);
        }
        if (in_array('neural', $type)) {
            $arr = [
                'voice' => $voices[$key]['Id'],
                'display_name' => $voices[$key]['Name'],
                'language' => $voices[$key]['LanguageCode'],
                'provider' => 'aws',
                'type' => 'Neural'
            ];
            array_push($data, $arr);
        }
    }
    $res = json_encode($data);
    if (exists(['variable' => 'aws_voices'], 'settings')) {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->update(['value' => $res], ['variable' => 'aws_voices']);
    } else {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->insert(['variable' => 'aws_voices', 'value' => $res]);
    }
    return json_decode($res);


}
function update_google()
{

    $google = new Google;
    $google_voices = $google->get_all_voices();

    $res = [];
    for ($i = 0; $i < count($google_voices); $i++) {

        $arr = [
            'voice' => $google_voices[$i]['name'],
            'display_name' => $google_voices[$i]['name'],
            'language' => $google_voices[$i]['languageCodes'][0],
            'provider' => 'google'
        ];
        if (strpos($google_voices[$i]['name'], 'Wavenet') !== false) {
            $arr['type'] = "Neural";
        } elseif (strpos($google_voices[$i]['name'], 'Standard') !== false) {
            $arr['type'] = "Standard";
        }
        array_push($res, $arr);
    }

    $res = json_encode($res);
    if (exists(['variable' => 'google_voices'], 'settings')) {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->update(['value' => $res], ['variable' => 'google_voices']);
    } else {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->insert(['variable' => 'google_voices', 'value' => $res]);
    }
    return json_decode($res);
}
function update_azure()
{
    $azure = new Azure;
    $azure = $azure->get_all_voices();
    $res = [];
    for ($i = 0; $i < count($azure); $i++) {
        $arr[$i] = [
            'voice' => $azure[$i]->ShortName,
            'display_name' => $azure[$i]->DisplayName,
            'language' => $azure[$i]->Locale,
            'type' => $azure[$i]->VoiceType,
            'provider' => 'azure',
        ];
        array_push($res, $arr);
    }
    $voices = json_encode($arr);
    if (exists(['variable' => 'azure_voices'], 'settings')) {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->update(['value' => $voices], ['variable' => 'azure_voices']);
    } else {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->insert(['variable' => 'azure_voices', 'value' => $voices]);
    }
}
function update_ibm()
{
    $provider = new IBM;
    $voices = $provider->get_voices()['voices'];
    $res = [];
    for ($i = 0; $i < count($voices); $i++) {
        $res[$i] = [
            'voice' => $voices[$i]['name'],
            'display_name' => $voices[$i]['display_name'],
            'language' => $voices[$i]['language'],
            'provider' => 'ibm',
            'type' => 'Neural'
        ];
    }
    $voices = json_encode($res);
    if (exists(['variable' => 'ibm_voices'], 'settings')) {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->update(['value' => $voices], ['variable' => 'ibm_voices']);
    } else {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');
        $builder = $builder->insert(['variable' => 'ibm_voices', 'value' => $voices]);
    }
}
function update_all_providers()
{
    try{
        update_aws();
        update_ibm();
        update_google();
        update_azure();
        return true;
    }catch(Exception $e){
        return false;
    }
}
