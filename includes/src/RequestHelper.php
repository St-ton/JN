<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class RequestHelper
 * @since 5.0.0
 */
class RequestHelper
{
    /**
     * @param string $var
     * @return bool
     * @since 5.0.0
     */
    public static function hasGPCData($var): bool
    {
        return isset($_POST[$var]) || isset($_GET[$var]) || isset($_COOKIE[$var]);
    }

    /**
     * @param string $var
     * @return array
     * @since 5.0.0
     */
    public static function verifyGPDataIntegerArray($var): array
    {
        if (isset($_REQUEST[$var])) {
            $val = $_REQUEST[$var];

            return is_numeric($val)
                ? [(int)$val]
                : array_map(function ($e) {
                    return (int)$e;
                }, $val);
        }

        return [];
    }

    /**
     * @param string $var
     * @return int
     * @former verifyGPCDataInteger()
     * @since 5.0.0
     */
    public static function verifyGPCDataInt($var): int
    {
        if (isset($_GET[$var]) && is_numeric($_GET[$var])) {
            return (int)$_GET[$var];
        }
        if (isset($_POST[$var]) && is_numeric($_POST[$var])) {
            return (int)$_POST[$var];
        }
        if (isset($_COOKIE[$var]) && is_numeric($_COOKIE[$var])) {
            return (int)$_COOKIE[$var];
        }

        return 0;
    }

    /**
     * @param string $var
     * @return string
     * @since 5.0.0
     */
    public static function verifyGPDataString($var): string
    {
        if (isset($_POST[$var])) {
            return $_POST[$var];
        }
        if (isset($_GET[$var])) {
            return $_GET[$var];
        }

        return '';
    }

    /**
     * @return string
     * @former getRealIp()
     * @since 5.0.0
     */
    public static function getRealIP(): string
    {
        $ip = null;
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip   = $list[0];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return ($ip = filter_var($ip, FILTER_VALIDATE_IP)) === false ? '0.0.0.0' : $ip;
    }

    /**
     * @param bool $bBestellung
     * @return string
     * @former gibIP()
     * @since 5.0.0
     */
    public static function getIP(bool $bBestellung = false): string
    {
        $ip   = self::getRealIP();
        $conf = Shop::getSettings([CONF_KAUFABWICKLUNG, CONF_GLOBAL]);
        if (($bBestellung && $conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'N')
            || (!$bBestellung && $conf['global']['global_ips_speichern'] === 'N')
        ) {
            $ip = (new IpAnonymizer($ip))->anonymize();
        }

        return $ip;
    }

    /**
     * Gibt einen String für einen Header mit dem angegebenen Status-Code aus
     *
     * @param int $nStatusCode
     * @return string
     * @since 5.0.0
     */
    public static function makeHTTPHeader(int $nStatusCode): string
    {
        $proto = !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $codes = [
            100 => $proto . ' 100 Continue',
            101 => $proto . ' 101 Switching Protocols',
            200 => $proto . ' 200 OK',
            201 => $proto . ' 201 Created',
            202 => $proto . ' 202 Accepted',
            203 => $proto . ' 203 Non-Authoritative Information',
            204 => $proto . ' 204 No Content',
            205 => $proto . ' 205 Reset Content',
            206 => $proto . ' 206 Partial Content',
            300 => $proto . ' 300 Multiple Choices',
            301 => $proto . ' 301 Moved Permanently',
            302 => $proto . ' 302 Found',
            303 => $proto . ' 303 See Other',
            304 => $proto . ' 304 Not Modified',
            305 => $proto . ' 305 Use Proxy',
            307 => $proto . ' 307 Temporary Redirect',
            400 => $proto . ' 400 Bad Request',
            401 => $proto . ' 401 Unauthorized',
            402 => $proto . ' 402 Payment Required',
            403 => $proto . ' 403 Forbidden',
            404 => $proto . ' 404 Not Found',
            405 => $proto . ' 405 Method Not Allowed',
            406 => $proto . ' 406 Not Acceptable',
            407 => $proto . ' 407 Proxy Authentication Required',
            408 => $proto . ' 408 Request Time-out',
            409 => $proto . ' 409 Conflict',
            410 => $proto . ' 410 Gone',
            411 => $proto . ' 411 Length Required',
            412 => $proto . ' 412 Precondition Failed',
            413 => $proto . ' 413 Request Entity Too Large',
            414 => $proto . ' 414 Request-URI Too Large',
            415 => $proto . ' 415 Unsupported Media Type',
            416 => $proto . ' 416 Requested range not satisfiable',
            417 => $proto . ' 417 Expectation Failed',
            500 => $proto . ' 500 Internal Server Error',
            501 => $proto . ' 501 Not Implemented',
            502 => $proto . ' 502 Bad Gateway',
            503 => $proto . ' 503 Service Unavailable',
            504 => $proto . ' 504 Gateway Time-out'
        ];

        return $codes[$nStatusCode] ?? '';
    }


    /**
     * Prueft ob SSL aktiviert ist und auch durch Einstellung genutzt werden soll
     * -1 = SSL nicht aktiv und nicht erlaubt
     * 1 = SSL aktiv durch Einstellung nicht erwünscht
     * 2 = SSL aktiv und erlaubt
     * 4 = SSL nicht aktiv aber erzwungen
     *
     * @return int
     * @former pruefeSSL()
     * @since 5.0.0
     */
    public static function checkSSL(): int
    {
        $conf       = Shop::getSettings([CONF_GLOBAL]);
        $cSSLNutzen = $conf['global']['kaufabwicklung_ssl_nutzen'];
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
        // Ist im Server SSL aktiv?
        if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === '1')) {
            if ($cSSLNutzen === 'P') { // SSL durch Einstellung erlaubt?
                return 2;
            }

            return 1;
        }
        if ($cSSLNutzen === 'P') {
            return 4;
        }

        return -1;
    }

    /**
     * @param Resource $ch
     * @param int $maxredirect
     * @return bool|mixed
     */
    public static function curl_exec_follow($ch, int $maxredirect = 5)
    {
        $mr = $maxredirect <= 0 ? 5 : $maxredirect;
        if (ini_get('open_basedir') === '') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            if ($mr > 0) {
                $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

                $rch = curl_copy_handle($ch);
                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                do {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if ($code === 301 || $code === 302) {
                            preg_match('/Location:(.*?)\n/', $header, $matches);
                            $newurl = trim(array_pop($matches));
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);
                curl_close($rch);
                if (!$mr) {
                    if ($maxredirect === null) {
                        trigger_error('Too many redirects. When following redirects, ' .
                            'libcurl hit the maximum amount.', E_USER_WARNING);
                    } else {
                        $maxredirect = 0;
                    }

                    return false;
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
            }
        }

        return curl_exec($ch);
    }

    /**
     * @param string $cURL
     * @param int    $nTimeout
     * @param null   $cPost
     * @return mixed
     */
    public static function http_get_contents($cURL, int $nTimeout = 15, $cPost = null)
    {
        return self::make_http_request($cURL, $nTimeout, $cPost, false);
    }

    /**
     * @param string $cURL
     * @param int    $nTimeout
     * @param null   $cPost
     * @return mixed
     */
    public static function http_get_status($cURL, int $nTimeout = 15, $cPost = null)
    {
        return self::make_http_request($cURL, $nTimeout, $cPost, true);
    }

    /**
     * @param string $cURL
     * @param int    $nTimeout
     * @param null   $cPost
     * @param bool   $bReturnStatus - false = return content on success / true = return status code instead of content
     * @return mixed
     */
    public static function make_http_request($cURL, int $nTimeout = 15, $cPost = null, $bReturnStatus = false)
    {
        $nCode = 0;
        $cData = '';

        if (function_exists('curl_init')) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $cURL);
            curl_setopt($curl, CURLOPT_TIMEOUT, $nTimeout);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, DEFAULT_CURL_OPT_VERIFYPEER);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, DEFAULT_CURL_OPT_VERIFYHOST);
            curl_setopt($curl, CURLOPT_REFERER, Shop::getURL());

            if ($cPost !== null) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $cPost);
            }

            $cData     = self::curl_exec_follow($curl);
            $cInfo_arr = curl_getinfo($curl);
            $nCode     = (int)$cInfo_arr['http_code'];

            curl_close($curl);
        } elseif (ini_get('allow_url_fopen')) {
            @ini_set('default_socket_timeout', $nTimeout);
            $fileHandle = @fopen($cURL, 'r');
            if ($fileHandle) {
                @stream_set_timeout($fileHandle, $nTimeout);

                $cData = '';
                while (($buffer = fgets($fileHandle)) !== false) {
                    $cData .= $buffer;
                }
                if (preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $http_response_header[0], $match)) {
                    $nCode = (int)$match[1];
                }
                fclose($fileHandle);
            }
        }
        if (!($nCode >= 200 && $nCode < 300)) {
            $cData = '';
        }

        return $bReturnStatus ? $nCode : $cData;
    }

    /**
     * @return bool
     * @since 5.0.0
     */
    public static function isAjaxRequest(): bool
    {
        return isset($_REQUEST['isAjax'])
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Affiliate trennen
     *
     * @param string|bool $seo
     * @return string|bool
     * @former extFremdeParameter()
     * @since 5.0.0
     */
    public static function extractExternalParams($seo)
    {
        $oSeo_arr = preg_split('/[' . EXT_PARAMS_SEPERATORS_REGEX . ']+/', $seo);
        if (is_array($oSeo_arr) && count($oSeo_arr) > 1) {
            $seo = $oSeo_arr[0];
            $cnt = count($oSeo_arr);
            for ($i = 1; $i < $cnt; $i++) {
                $keyValue = explode('=', $oSeo_arr[$i]);
                if (count($keyValue) > 1) {
                    list($cName, $cWert)                = $keyValue;
                    $_SESSION['FremdParameter'][$cName] = $cWert;
                }
            }
        }

        return $seo;
    }
}
