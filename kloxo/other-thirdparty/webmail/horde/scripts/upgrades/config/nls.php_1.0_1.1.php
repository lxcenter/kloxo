$nls['languages']['ar_OM'] = '&#x202d;Arabic (Oman) &#x202e;(&#x0627;&#x0644;&#x0639;&#x0631;&#x0628;&#x064a;&#x0629;)';
$nls['languages']['ar_SY'] = '&#x202d;Arabic (Syria) &#x202e;(&#x0627;&#x0644;&#x0639;&#x0631;&#x0628;&#x064a;&#x0629;)';
$nls['languages']['id_ID'] = 'Bahasa Indonesia';
$nls['languages']['bs_BA'] = 'Bosanski';
$nls['languages']['bg_BG'] = '&#x202d;Bulgarian (&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;)';
$nls['languages']['ca_ES'] = 'Catal&#xe0;';
$nls['languages']['zh_CN'] = '&#x202d;Chinese (Simplified) (&#x7b80;&#x4f53;&#x4e2d;&#x6587;)';
$nls['languages']['zh_TW'] = '&#x202d;Chinese (Traditional) (&#x6b63;&#x9ad4;&#x4e2d;&#x6587;)';
$nls['languages']['cs_CZ'] = '&#x202d;Czech (&#x010c;esky)';
$nls['languages']['da_DK'] = 'Dansk';
$nls['languages']['de_DE'] = 'Deutsch';
$nls['languages']['en_US'] = '&#x202d;English (American)';
$nls['languages']['en_GB'] = '&#x202d;English (British)';
$nls['languages']['en_CA'] = '&#x202d;English (Canadian)';
$nls['languages']['es_ES'] = 'Espa&#xf1;ol';
$nls['languages']['et_EE'] = 'Eesti';
$nls['languages']['fr_FR'] = 'Fran&#xe7;ais';
$nls['languages']['gl_ES'] = 'Galego';
$nls['languages']['el_GR'] = '&#x202d;Greek (&#x0395;&#x03bb;&#x03bb;&#x03b7;&#x03bd;&#x03b9;&#x03ba;&#x03ac;)';
$nls['languages']['he_IL'] = '&#x202d;Hebrew &#x202e;(&#x05E2;&#x05D1;&#x05E8;&#x05D9;&#x05EA;)';
$nls['languages']['is_IS'] = '&#xcd;slenska';
$nls['languages']['it_IT'] = 'Italiano';
$nls['languages']['ja_JP'] = '&#x202d;Japanese (&#x65e5;&#x672c;&#x8a9e;)';
$nls['languages']['km_KH'] = '&#x202d;Khmer (&#x1781;&#x17d2;&#x1798;&#x17c2;&#x179a;)';
$nls['languages']['ko_KR'] = '&#x202d;Korean (&#xd55c;&#xad6d;&#xc5b4;)';
$nls['languages']['lv_LV'] = 'Latvie&#x0161;u';
$nls['languages']['lt_LT'] = 'Lietuvi&#x0173;';
$nls['languages']['mk_MK'] = '&#x202d;Macedonian (&#x041c;&#x0430;&#x043a;&#x0435;&#x0434;&#x043e;&#x043d;&#x0441;&#x043a;&#x0438;)';
$nls['languages']['hu_HU'] = 'Magyar';
$nls['languages']['nl_NL'] = 'Nederlands';
$nls['languages']['nb_NO'] = 'Norsk bokm&#xe5;l';
$nls['languages']['nn_NO'] = 'Norsk nynorsk';
$nls['languages']['fa_IR'] = '&#x202d;Persian &#x202e;(&#x0641;&#x0627;&#x0631;&#x0633;&#x0649;)';
$nls['languages']['pl_PL'] = 'Polski';
$nls['languages']['pt_PT'] = 'Portugu&#xea;s';
$nls['languages']['pt_BR'] = 'Portugu&#xea;s Brasileiro';
$nls['languages']['ro_RO'] = 'Rom&#xe2;n&#xe4;';
$nls['languages']['ru_RU'] = '&#x202d;Russian (&#x0420;&#x0443;&#x0441;&#x0441;&#x043a;&#x0438;&#x0439;)';
$nls['languages']['sk_SK'] = '&#x202d;Slovak (Sloven&#x010d;ina)';
$nls['languages']['sl_SI'] = '&#x202d;Slovenian (Sloven&#x0161;&#x010d;ina)';
$nls['languages']['fi_FI'] = 'Suomi';
$nls['languages']['sv_SE'] = 'Svenska';
$nls['languages']['th_TH'] = '&#x202d;Thai (&#x0e44;&#x0e17;&#x0e22;)';
if (version_compare(PHP_VERSION, '5', 'lt') || version_compare(PHP_VERSION, '6', 'ge')) {
    $nls['languages']['tr_TR'] = 'T&#xfc;rk&#xe7;e';
}
$nls['languages']['uk_UA'] = '&#x202d;Ukrainian (&#x0423;&#x043a;&#x0440;&#x0430;&#x0457;&#x043d;&#x0441;&#x044c;&#x043a;&#x0430;)';


$nls['charsets']['uk_UA'] = 'windows-1251';

/**
 * BSD charsets.
 */
if (strpos(PHP_OS, 'BSD') !== false) {
    $nls['charsets']['ar_OM'] = 'windows-1256';
    $nls['charsets']['ar_SY'] = 'windows-1256';
    $nls['charsets']['bg_BG'] = 'windows-1251';
    $nls['charsets']['bs_BA'] = 'ISO8859-2';
    $nls['charsets']['cs_CZ'] = 'ISO8859-2';
    $nls['charsets']['el_GR'] = 'ISO8859-7';
    $nls['charsets']['fa_IR'] = 'UTF-8';
    $nls['charsets']['he_IL'] = 'UTF-8';
    $nls['charsets']['hu_HU'] = 'ISO8859-2';
    $nls['charsets']['ja_JP'] = 'SHIFT_JIS';
    $nls['charsets']['km_KH'] = 'UTF-8';
    $nls['charsets']['ko_KR'] = 'EUC-KR';
    $nls['charsets']['lt_LT'] = 'ISO8859-13';
    $nls['charsets']['lv_LV'] = 'windows-1257';
    $nls['charsets']['mk_MK'] = 'ISO8859-5';
    $nls['charsets']['pl_PL'] = 'ISO8859-2';
    $nls['charsets']['ru_RU'] = 'windows-1251';
    $nls['charsets']['ru_RU.KOI8-R'] = 'KOI8-R';
    $nls['charsets']['sk_SK'] = 'ISO8859-2';
    $nls['charsets']['sl_SI'] = 'ISO8859-2';
    $nls['charsets']['th_TH'] = 'TIS-620';
    if (version_compare(PHP_VERSION, '5', 'lt') ||
        version_compare(PHP_VERSION, '6', 'ge')) {
        $nls['charsets']['tr_TR'] = 'ISO-8859-9';
    }
    $nls['charsets']['uk_UA'] = 'windows-1251';
    $nls['charsets']['zh_CN'] = 'GB2312';
    $nls['charsets']['zh_TW'] = 'BIG5';
}
