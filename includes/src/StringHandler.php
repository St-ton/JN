<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class StringHandler
 */
class StringHandler
{
    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlentities($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return htmlentities($cString, $cFlag, $cEncoding);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function unhtmlentities($string)
    {
        // replace numeric entities
        $string = preg_replace_callback(
            '~&#x([0-9a-fA-F]+);~i',
            function ($x) {
                return chr(hexdec($x[1]));
            },
            $string
        );
        $string = preg_replace_callback(
            '~&#([0-9]+);~',
            function ($x) {
                return chr($x[1]);
            },
            $string
        );

        return self::htmlentitydecode($string);
    }

    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlspecialchars($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return htmlspecialchars($cString, $cFlag, $cEncoding);
    }

    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlentitydecode($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return html_entity_decode($cString, $cFlag, $cEncoding);
    }

    /**
     * @param int    $cFlag
     * @param string $cEncoding
     * @return array
     */
    public static function gethtmltranslationtable($cFlag = ENT_QUOTES, $cEncoding = JTL_CHARSET)
    {
        return get_html_translation_table(HTML_ENTITIES, $cFlag, $cEncoding);
    }

    /**
     * @param string|array $input
     * @param int          $nSuche
     * @return mixed|string
     */
    public static function filterXSS($input, $nSuche = 0)
    {
        if (is_array($input)) {
            foreach ($input as &$a) {
                $a = self::filterXSS($a);
            }

            return $input;
        }
        $cString = trim(strip_tags($input));
        $cString = (int)$nSuche === 1
            ? str_replace(['\\\'', '\\'], '', $cString)
            : str_replace(['\"', '\\\'', '\\', '"', '\''], '', $cString);

        if ((int)$nSuche === 1 && strlen($cString) > 10) {
            $cString = substr(str_replace(['(', ')', ';'], '', $cString), 0, 50);
        }

        return $cString;
    }

    /**
     * check if string already is utf8 encoded
     *
     * @source http://w3.org/International/questions/qa-forms-utf-8.html
     * @param string $string
     * @return int
     */
    public static function is_utf8($string)
    {
        $res = preg_match(
            '%^(?:[\x09\x0A\x0D\x20-\x7E]  # ASCII
                                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                            )*$%xs', $string
        );
        if ($res === false) {
            //some kind of pcre error happend - probably PREG_JIT_STACKLIMIT_ERROR.
            //we could check this via preg_last_error()
            $res = function_exists('mb_detect_encoding')
                ? (int)(mb_detect_encoding($string, 'UTF-8', true) === 'UTF-8')
                : 0;
        }

        return $res;
    }

    /**
     * @param string $data
     * @return mixed|string
     */
    public static function xssClean($data)
    {
        $convert = false;
        if (!self::is_utf8($data)) {
            //with non-utf8 input this function would return an empty string
            $convert = true;
            $data    = self::convertUTF8($data);
        }
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data     = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $convert ? self::convertISO($data) : $data;
    }

    /**
     * @param string $cData
     * @return string
     */
    public static function convertUTF8($cData)
    {
        return mb_convert_encoding($cData, 'UTF-8', mb_detect_encoding($cData, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }

    /**
     * @param string $cData
     * @return string
     */
    public static function convertISO($cData)
    {
        return mb_convert_encoding($cData, 'ISO-8859-1', mb_detect_encoding($cData, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }

    /**
     * @param string $ISO
     * @return mixed
     */
    public static function convertISO2ISO639($ISO)
    {
        $cISO_arr = self::getISOMappings();

        return $cISO_arr[$ISO];
    }

    /**
     * @param string $ISO
     * @return int|string
     */
    public static function convertISO6392ISO(string $ISO)
    {
        $cISO_arr = self::getISOMappings();
        foreach ($cISO_arr as $cISO639 => $cISO) {
            if (strtolower($cISO) === strtolower($ISO)) {
                return $cISO639;
            }
        }

        return $ISO;
    }

    /**
     * @return array
     */
    public static function getISOMappings(): array
    {
        return [
            'aar' => 'aa', // Afar
            'abk' => 'ab', // Abkhazian
            'afr' => 'af', // Afrikaans
            'aka' => 'ak', // Akan
            'alb' => 'sq', // Albanian
            'amh' => 'am', // Amharic
            'ara' => 'ar', // Arabic
            'arg' => 'an', // Aragonese
            'arm' => 'hy', // Armenian
            'asm' => 'as', // Assamese
            'ava' => 'av', // Avaric
            'ave' => 'ae', // Avestan
            'aym' => 'ay', // Aymara
            'aze' => 'az', // Azerbaijani
            'bak' => 'ba', // Bashkir
            'bam' => 'bm', // Bambara
            'baq' => 'eu', // Basque
            'bel' => 'be', // Belarusian
            'ben' => 'bn', // Bengali
            'bih' => 'bh', // Bihari languages
            'bis' => 'bi', // Bislama
            'bos' => 'bs', // Bosnian
            'bre' => 'br', // Breton
            'bul' => 'bg', // Bulgarian
            'bur' => 'my', // Burmese
            'cat' => 'ca', // Catalan; Valencian
            'cze' => 'cs', // Czech
            'cha' => 'ch', // Chamorro
            'che' => 'ce', // Chechen
            'chi' => 'zh', // Chinese
            'chu' => 'cu', // Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic
            'chv' => 'cv', // Chuvash
            'cor' => 'kw', // Cornish
            'cos' => 'co', // Corsican
            'cre' => 'cr', // Cree
            'dan' => 'da', // Danish
            'div' => 'dv', // Divehi; Dhivehi; Maldivian
            'dut' => 'nl', // Dutch; Flemish
            'dzo' => 'dz', // Dzongkha
            'eng' => 'en', // English
            'epo' => 'eo', // Esperanto
            'est' => 'et', // Estonian
            'ewe' => 'ee', // Ewe
            'fao' => 'fo', // Faroese
            'fij' => 'fj', // Fijian
            'fin' => 'fi', // Finnish
            'fre' => 'fr', // French
            'fry' => 'fy', // Western Frisian
            'ful' => 'ff', // Fulah
            'geo' => 'ka', // Georgian
            'ger' => 'de', // German
            'gla' => 'gd', // Gaelic; Scottish Gaelic
            'gle' => 'ga', // Irish
            'glg' => 'gl', // Galician
            'glv' => 'gv', // Manx
            'gre' => 'el', // Greek, Modern (1453-)
            'grn' => 'gn', // Guarani
            'guj' => 'gu', // Gujarati
            'hat' => 'ht', // Haitian; Haitian Creole
            'hau' => 'ha', // Hausa
            'heb' => 'he', // Hebrew
            'her' => 'hz', // Herero
            'hin' => 'hi', // Hindi
            'hmo' => 'ho', // Hiri Motu
            'hrv' => 'hr', // Croatian
            'hun' => 'hu', // Hungarian
            'ibo' => 'ig', // Igbo
            'ice' => 'is', // Icelandic
            'ido' => 'io', // Ido
            'iii' => 'ii', // Sichuan Yi; Nuosu
            'iku' => 'iu', // Inuktitut
            'ile' => 'ie', // Interlingue; Occidental
            'ina' => 'ia', // Interlingua (International Auxiliary Language Association)
            'ind' => 'id', // Indonesian
            'ipk' => 'ik', // Inupiaq
            'ita' => 'it', // Italian
            'jav' => 'jv', // Javanese
            'jpn' => 'ja', // Japanese
            'kal' => 'kl', // Kalaallisut; Greenlandic
            'kan' => 'kn', // Kannada
            'kas' => 'ks', // Kashmiri
            'kau' => 'kr', // Kanuri
            'kaz' => 'kk', // Kazakh
            'khm' => 'km', // Central Khmer
            'kik' => 'ki', // Kikuyu; Gikuyu
            'kin' => 'rw', // Kinyarwanda
            'kir' => 'ky', // Kirghiz; Kyrgyz
            'kom' => 'kv', // Komi
            'kon' => 'kg', // Kongo
            'kor' => 'ko', // Korean
            'kua' => 'kj', // Kuanyama; Kwanyama
            'kur' => 'ku', // Kurdish
            'lao' => 'lo', // Lao
            'lat' => 'la', // Latin
            'lav' => 'lv', // Latvian
            'lim' => 'li', // Limburgan; Limburger; Limburgish
            'lin' => 'ln', // Lingala
            'lit' => 'lt', // Lithuanian
            'ltz' => 'lb', // Luxembourgish; Letzeburgesch
            'lub' => 'lu', // Luba-Katanga
            'lug' => 'lg', // Ganda
            'mac' => 'mk', // Macedonian
            'mah' => 'mh', // Marshallese
            'mal' => 'ml', // Malayalam
            'mao' => 'mi', // Maori
            'mar' => 'mr', // Marathi
            'may' => 'ms', // Malay
            'mlg' => 'mg', // Malagasy
            'mlt' => 'mt', // Maltese
            'mon' => 'mn', // Mongolian
            'nau' => 'na', // Nauru
            'nav' => 'nv', // Navajo; Navaho
            'nbl' => 'nr', // Ndebele, South; South Ndebele
            'nde' => 'nd', // Ndebele, North; North Ndebele
            'ndo' => 'ng', // Ndonga
            'nep' => 'ne', // Nepali
            'nno' => 'nn', // Norwegian Nynorsk; Nynorsk, Norwegian
            'nob' => 'nb', // Bokm?l, Norwegian; Norwegian Bokm?l
            'nor' => 'no', // Norwegian
            'nya' => 'ny', // Chichewa; Chewa; Nyanja
            'oci' => 'oc', // Occitan (post 1500)
            'oji' => 'oj', // Ojibwa
            'ori' => 'or', // Oriya
            'orm' => 'om', // Oromo
            'oss' => 'os', // Ossetian; Ossetic
            'pan' => 'pa', // Panjabi; Punjabi
            'per' => 'fa', // Persian
            'pli' => 'pi', // Pali
            'pol' => 'pl', // Polish
            'por' => 'pt', // Portuguese
            'pus' => 'ps', // Pushto; Pashto
            'que' => 'qu', // Quechua
            'roh' => 'rm', // Romansh
            'rum' => 'ro', // Romanian; Moldavian; Moldovan
            'run' => 'rn', // Rundi
            'rus' => 'ru', // Russian
            'sag' => 'sg', // Sango
            'san' => 'sa', // Sanskrit
            'sin' => 'si', // Sinhala; Sinhalese
            'slo' => 'sk', // Slovak
            'slv' => 'sl', // Slovenian
            'sme' => 'se', // Northern Sami
            'smo' => 'sm', // Samoan
            'sna' => 'sn', // Shona
            'snd' => 'sd', // Sindhi
            'som' => 'so', // Somali
            'sot' => 'st', // Sotho, Southern
            'spa' => 'es', // Spanish; Castilian
            'srd' => 'sc', // Sardinian
            'srp' => 'sr', // Serbian
            'ssw' => 'ss', // Swati
            'sun' => 'su', // Sundanese
            'swa' => 'sw', // Swahili
            'swe' => 'sv', // Swedish
            'tah' => 'ty', // Tahitian
            'tam' => 'ta', // Tamil
            'tat' => 'tt', // Tatar
            'tel' => 'te', // Telugu
            'tgk' => 'tg', // Tajik
            'tgl' => 'tl', // Tagalog
            'tha' => 'th', // Thai
            'tib' => 'bo', // Tibetan
            'tir' => 'ti', // Tigrinya
            'ton' => 'to', // Tonga (Tonga Islands)
            'tsn' => 'tn', // Tswana
            'tso' => 'ts', // Tsonga
            'tuk' => 'tk', // Turkmen
            'tur' => 'tr', // Turkish
            'twi' => 'tw', // Twi
            'uig' => 'ug', // Uighur; Uyghur
            'ukr' => 'uk', // Ukrainian
            'urd' => 'ur', // Urdu
            'uzb' => 'uz', // Uzbek
            'ven' => 've', // Venda
            'vie' => 'vi', // Vietnamese
            'vol' => 'vo', // Volapük
            'wel' => 'cy', // Welsh
            'wln' => 'wa', // Walloon
            'wol' => 'wo', // Wolof
            'xho' => 'xh', // Xhosa
            'yid' => 'yi', // Yiddish
            'yor' => 'yo', // Yoruba
            'zha' => 'za', // Zhuang; Chuang
            'zul' => 'zu'
        ];
    }

    /**
     * @param string $string
     * @return string|mixed
     */
    public static function removeDoubleSpaces($string)
    {
        if (!is_string($string)) {
            return $string;
        }
        $string = preg_quote($string, '|');

        return preg_replace('|  +|', ' ', $string);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public static function removeWhitespace($string)
    {
        return preg_replace('/\s+/', ' ', $string);
    }

    /**
     * Creating semicolon separated key string
     *
     * @param array $keys
     * @return string
     */
    public static function createSSK($keys)
    {
        if (!is_array($keys) || count($keys) === 0) {
            return '';
        }

        return sprintf(';%s;', implode(';', $keys));
    }

    /**
     * Parse a semicolon separated key string to an array
     *
     * @param string $ssk
     * @return array
     */
    public static function parseSSK($ssk)
    {
        return is_string($ssk)
            ? array_map('trim', array_filter(explode(';', $ssk)))
            : [];
    }

    /**
     * @note PHP's FILTER_SANITIZE_EMAIL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     *
     * @param string $input
     * @param bool   $validate
     * @return string|false - a filtered string or false if invalid
     */
    public static function filterEmailAddress($input, $validate = true)
    {
        if ((function_exists('mb_detect_encoding') && mb_detect_encoding($input) !== 'UTF-8') || !self::is_utf8($input)) {
            $input = self::convertUTF8($input);
        }
        $input     = function_exists('idn_to_ascii') ? idn_to_ascii($input) : $input;
        $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);

        return $validate
            ? filter_var($sanitized, FILTER_VALIDATE_EMAIL)
            : $sanitized;
    }

    /**
     * @note PHP's FILTER_SANITIZE_URL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     *
     * @param string $input
     * @param bool   $validate
     * @return string|false - a filtered string or false if invalid
     */
    public static function filterURL($input, $validate = true)
    {
        if ((function_exists('mb_detect_encoding') && mb_detect_encoding($input) !== 'UTF-8') || !self::is_utf8($input)) {
            $input = self::convertUTF8($input);
        }
        $input     = function_exists('idn_to_ascii') ? idn_to_ascii($input) : $input;
        $sanitized = filter_var($input, FILTER_SANITIZE_URL);

        return $validate
            ? filter_var($sanitized, FILTER_VALIDATE_URL)
            : $sanitized;
    }

    /**
     * Build an URL string from a given associative array of parts according to PHP's parse_url()
     *
     * @param array $parts
     * @return string - the resulting URL
     */
    public static function buildUrl($parts)
    {
        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
            (isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '') .
            ($parts['host'] ?? '') .
            (isset($parts['port']) ? ':' . $parts['port'] : '') .
            ($parts['path'] ?? '') .
            (isset($parts['query']) ? '?' . $parts['query'] : '') .
            (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * @param string $number
     * @return int
     * @former checkeTel()
     */
    public static function checkPhoneNumber($number): int
    {
        if (!$number) {
            return 1;
        }
        if (!preg_match('/^[0-9\-\(\)\/\+\s]{1,}$/', $number)) {
            return 2;
        }

        return 0;
    }

    /**
     * @param string $data
     * @return int
     */
    public static function checkDate($data): int
    {
        if (!$data) {
            return 1;
        }
        if (!preg_match('/^\d{1,2}\.\d{1,2}\.(\d{4})$/', $data)) {
            return 2;
        }
        list($tag, $monat, $jahr) = explode('.', $data);
        if (!checkdate($monat, $tag, $jahr)) {
            return 3;
        }

        return 0;
    }

    /**
     * Diese Funktion erhält einen Text als String und parsed ihn. Variablen die geparsed werden lauten wie folgt:
     * $#a:ID:NAME#$ => ID = kArtikel NAME => Wunschname ... wird in eine URL (evt. SEO) zum Artikel umgewandelt.
     * $#k:ID:NAME#$ => ID = kKategorie NAME => Wunschname ... wird in eine URL (evt. SEO) zur Kategorie umgewandelt.
     * $#h:ID:NAME#$ => ID = kHersteller NAME => Wunschname ... wird in eine URL (evt. SEO) zum Hersteller umgewandelt.
     * $#m:ID:NAME#$ => ID = kMerkmalWert NAME => Wunschname ... wird in eine URL (evt. SEO) zum MerkmalWert umgewandelt.
     * $#n:ID:NAME#$ => ID = kNews NAME => Wunschname ... wird in eine URL (evt. SEO) zur News umgewandelt.
     * $#t:ID:NAME#$ => ID = kTag NAME => Wunschname ... wird in eine URL (evt. SEO) zum Tag umgewandelt.
     * $#l:ID:NAME#$ => ID = kSuchanfrage NAME => Wunschname ... wird in eine URL (evt. SEO) zur Livesuche umgewandelt.
     *
     * @param string $cText
     * @return mixed
     */
    public static function parseNewsText($cText)
    {
        preg_match_all(
            '/\${1}\#{1}[akhmntl]{1}:[0-9]+\:{0,1}[a-zA-Z0-9äÄöÖüÜß\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\]\ ]{0,}\#{1}\${1}/',
            $cText,
            $cTreffer_arr
        );
        if (!is_array($cTreffer_arr[0]) || count($cTreffer_arr[0]) === 0) {
            return $cText;
        }
        if (!isset($_SESSION['kSprache'])) {
            $_lang    = Sprache::getDefaultLanguage();
            $kSprache = (int)$_lang->kSprache;
        } else {
            $kSprache = Shop::getLanguageID();
        }
        // Parameter
        $cParameter_arr = [
            'a' => URLART_ARTIKEL,
            'k' => URLART_KATEGORIE,
            'h' => URLART_HERSTELLER,
            'm' => URLART_MERKMAL,
            'n' => URLART_NEWS,
            't' => URLART_TAG,
            'l' => URLART_LIVESUCHE
        ];
        foreach ($cTreffer_arr[0] as $cTreffer) {
            $cParameter = substr($cTreffer, strpos($cTreffer, '#') + 1, 1);
            $nBis       = strpos($cTreffer, ':', 4);
            // Es wurde kein Name angegeben
            if ($nBis === false) {
                $nBis  = strpos($cTreffer, ':', 3);
                $nVon  = strpos($cTreffer, '#', $nBis);
                $cKey  = substr($cTreffer, $nBis + 1, ($nVon - 1) - $nBis);
                $cName = '';
            } else {
                $cKey  = substr($cTreffer, 4, $nBis - 4);
                $cName = substr($cTreffer, $nBis + 1, strpos($cTreffer, '#', $nBis) - ($nBis + 1));
            }

            $oObjekt    = new stdClass();
            $bVorhanden = false;
            //switch($cURLArt_arr[$i])
            switch ($cParameter_arr[$cParameter]) {
                case URLART_ARTIKEL:
                    $oObjekt->kArtikel = (int)$cKey;
                    $oObjekt->cKey     = 'kArtikel';
                    $cTabellenname     = 'tartikel';
                    $cSpracheSQL       = '';
                    if (Shop::getLanguageID() > 0 && !Sprache::isDefaultLanguageActive()) {
                        $cTabellenname = 'tartikelsprache';
                        $cSpracheSQL   = " AND tartikelsprache.kSprache = " . Shop::getLanguageID();
                    }
                    $oArtikel = Shop::Container()->getDB()->query(
                        "SELECT {$cTabellenname}.kArtikel, {$cTabellenname}.cName, tseo.cSeo
                            FROM {$cTabellenname}
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = {$cTabellenname}.kArtikel
                                AND tseo.kSprache = {$kSprache}
                            WHERE {$cTabellenname}.kArtikel = " . (int)$cKey . $cSpracheSQL,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oArtikel->kArtikel) && $oArtikel->kArtikel > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oArtikel->cSeo;
                        $oObjekt->cName = !empty($oArtikel->cName) ? $oArtikel->cName : 'Link';
                    }
                    break;

                case URLART_KATEGORIE:
                    $oObjekt->kKategorie = (int)$cKey;
                    $oObjekt->cKey       = 'kKategorie';
                    $cTabellenname       = 'tkategorie';
                    $cSpracheSQL         = '';
                    if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
                        $cTabellenname = "tkategoriesprache";
                        $cSpracheSQL   = " AND tkategoriesprache.kSprache = " . $kSprache;
                    }
                    $oKategorie = Shop::Container()->getDB()->query(
                        "SELECT {$cTabellenname}.kKategorie, {$cTabellenname}.cName, tseo.cSeo
                            FROM {$cTabellenname}
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kKategorie'
                                AND tseo.kKey = {$cTabellenname}.kKategorie
                                AND tseo.kSprache = {$kSprache}
                            WHERE {$cTabellenname}.kKategorie = " . (int)$cKey . $cSpracheSQL,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oKategorie->kKategorie) && $oKategorie->kKategorie > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oKategorie->cSeo;
                        $oObjekt->cName = !empty($oKategorie->cName) ? $oKategorie->cName : 'Link';
                    }
                    break;

                case URLART_HERSTELLER:
                    $oObjekt->kHersteller = (int)$cKey;
                    $oObjekt->cKey        = 'kHersteller';
                    $cTabellenname        = 'thersteller';
                    $oHersteller          = Shop::Container()->getDB()->query(
                        "SELECT thersteller.kHersteller, thersteller.cName, tseo.cSeo
                            FROM thersteller
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kHersteller'
                                AND tseo.kKey = {$cTabellenname}.kHersteller
                                AND tseo.kSprache = {$kSprache}
                            WHERE {$cTabellenname}.kHersteller = " . (int)$cKey,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oHersteller->kHersteller) && $oHersteller->kHersteller > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oHersteller->cSeo;
                        $oObjekt->cName = !empty($oHersteller->cName) ? $oHersteller->cName : 'Link';
                    }
                    break;

                case URLART_MERKMAL:
                    $oObjekt->kMerkmalWert = (int)$cKey;
                    $oObjekt->cKey         = 'kMerkmalWert';
                    $oMerkmalWert          = Shop::Container()->getDB()->query(
                        "SELECT tmerkmalwertsprache.kMerkmalWert, tmerkmalwertsprache.cWert, tseo.cSeo
                            FROM tmerkmalwertsprache
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kMerkmalWert'
                                AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                                AND tseo.kSprache = {$kSprache}
                            WHERE tmerkmalwertsprache.kMerkmalWert = " . (int)$cKey . "
                                AND tmerkmalwertsprache.kSprache = " . $kSprache,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oMerkmalWert->kMerkmalWert) && $oMerkmalWert->kMerkmalWert > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oMerkmalWert->cSeo;
                        $oObjekt->cName = !empty($oMerkmalWert->cWert) ? $oMerkmalWert->cWert : 'Link';
                    }
                    break;

                case URLART_NEWS:
                    $oObjekt->kNews = (int)$cKey;
                    $oObjekt->cKey  = 'kNews';
                    $oNews          = Shop::Container()->getDB()->query(
                        "SELECT tnews.kNews, tnews.cBetreff, tseo.cSeo
                            FROM tnews
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kNews'
                                AND tseo.kKey = tnews.kNews
                                AND tseo.kSprache = {$kSprache}
                            WHERE tnews.kNews = " . (int)$cKey,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oNews->kNews) && $oNews->kNews > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oNews->cSeo;
                        $oObjekt->cName = !empty($oNews->cBetreff) ? $oNews->cBetreff : 'Link';
                    }
                    break;

                case URLART_UMFRAGE:
                    $oObjekt->kNews = (int)$cKey;
                    $oObjekt->cKey  = 'kUmfrage';
                    $oUmfrage       = Shop::Container()->getDB()->query(
                        "SELECT tumfrage.kUmfrage, tumfrage.cName, tseo.cSeo
                            FROM tumfrage
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kUmfrage'
                                AND tseo.kKey = tumfrage.kUmfrage
                                AND tseo.kSprache = {$kSprache}
                            WHERE tumfrage.kUmfrage = " . (int)$cKey,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oUmfrage->kUmfrage) && $oUmfrage->kUmfrage > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oUmfrage->cSeo;
                        $oObjekt->cName = !empty($oUmfrage->cName) ? $oUmfrage->cName : 'Link';
                    }
                    break;

                case URLART_TAG:
                    $oObjekt->kNews = (int)$cKey;
                    $oObjekt->cKey  = 'kTag';
                    $oTag           = Shop::Container()->getDB()->query(
                        "SELECT ttag.kTag, ttag.cName, tseo.cSeo
                            FROM ttag
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kTag'
                                AND tseo.kKey = ttag.kTag
                                AND tseo.kSprache = {$kSprache}
                            WHERE ttag.kTag = " . (int)$cKey,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oTag->kTag) && $oTag->kTag > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oTag->cSeo;
                        $oObjekt->cName = !empty($oTag->cName) ? $oTag->cName : 'Link';
                    }
                    break;

                case URLART_LIVESUCHE:
                    $oObjekt->kNews = (int)$cKey;
                    $oObjekt->cKey  = 'kSuchanfrage';
                    $oSuchanfrage   = Shop::Container()->getDB()->query(
                        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.cSuche, tseo.cSeo
                            FROM tsuchanfrage
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kSuchanfrage'
                                AND tseo.kKey = tsuchanfrage.kSuchanfrage
                                AND tseo.kSprache = {$kSprache}
                            WHERE tsuchanfrage.kSuchanfrage = " . (int)$cKey,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
                        $bVorhanden     = true;
                        $oObjekt->cSeo  = $oSuchanfrage->cSeo;
                        $oObjekt->cName = !empty($oSuchanfrage->cSuche) ? $oSuchanfrage->cSuche : 'Link';
                    }
                    break;
            }
            executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_PARSENEWSTEXT);

            if (strlen($cName) > 0) {
                $oObjekt->cName = $cName;
                $cName          = ':' . $cName;
            }
            if ($bVorhanden) {
                $cURL  = UrlHelper::buildURL($oObjekt, $cParameter_arr[$cParameter]);
                $cText = str_replace(
                    '$#' . $cParameter . ':' . $cKey . $cName . '#$',
                    '<a href="' . Shop::getURL() . '/' . $cURL . '">' . $oObjekt->cName . '</a>',
                    $cText
                );
            } else {
                $cText = str_replace(
                    '$#' . $cParameter . ':' . $cKey . $cName . '#$',
                    '<a href="' . Shop::getURL() . '/" >' . Shop::Lang()->get('parseTextNoLinkID') . '</a>',
                    $cText
                );
            }
        }

        return $cText;
    }

    /**
     * @param int $size
     * @param string $format
     * @return string
     */
    public static function formatSize($size, $format = '%.2f'): string
    {
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
        $res   = '';
        foreach ($units as $n => $unit) {
            $div = 1024 ** $n;
            if ($size > $div) {
                $res = sprintf("$format %s", $size / $div, $unit);
            }
        }

        return $res;
    }

    /**
     * @param string|array|object $data the string, array or object to convert recursively
     * @param bool                $encode true if data should be utf-8-encoded or false if data should be utf-8-decoded
     * @param bool                $copy false if objects should be changed, true if they should be cloned first
     * @return string|array|object converted data
     */
    public static function utf8_convert_recursive($data, $encode = true, $copy = false)
    {
        if (is_string($data)) {
            $isUtf8 = mb_detect_encoding($data, 'UTF-8', true) !== false;

            if ((!$isUtf8 && $encode) || ($isUtf8 && !$encode)) {
                $data = $encode ? self::convertUTF8($data) : self::convertISO($data);
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $val) {
                $newKey = (string)self::utf8_convert_recursive($key, $encode);
                $newVal = self::utf8_convert_recursive($val, $encode);
                unset($data[$key]);
                $data[$newKey] = $newVal;
            }
        } elseif (is_object($data)) {
            if ($copy) {
                $data = clone $data;
            }

            foreach (get_object_vars($data) as $key => $val) {
                $newKey = (string)self::utf8_convert_recursive($key, $encode);
                $newVal = self::utf8_convert_recursive($val, $encode);
                unset($data->$key);
                $data->$newKey = $newVal;
            }
        }

        return $data;
    }

    /**
     * JSON-Encode $data only if it is not already encoded, meaning it avoids double encoding
     *
     * @param mixed $data
     * @return string|bool - false when $data is not encodable
     * @throws Exception
     */
    public static function json_safe_encode($data)
    {
        $data = self::utf8_convert_recursive($data);
        // encode data if not already encoded
        if (is_string($data)) {
            // data is a string
            json_decode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // it is not a JSON string yet
                $data = json_encode($data);
            }
        } else {
            $data = json_encode($data);
        }

        return $data;
    }
}
