<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\RequestHelper;

/**
 * Class Communication
 */
final class Communication
{
    /**
     * @param string $cURL
     * @param array  $xPostData_arr
     * @param bool   $bPost
     * @return mixed
     * @throws Exception
     */
    private static function doCall(string $cURL, array $xPostData_arr, bool $bPost = true)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, $bPost);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xPostData_arr);
            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1'
            );
            curl_setopt($ch, CURLOPT_URL, $cURL);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $cContent = RequestHelper::curl_exec_follow($ch);

            curl_close($ch);
        } else {
            throw new Exception('Die PHP Funktion curl_init existiert nicht!');
        }

        return $cContent;
    }

    /**
     * @param string $cURL
     * @param array  $xData_arr
     * @param bool   $bPost
     * @return string
     * @throws Exception
     */
    public static function postData(string $cURL, $xData_arr = [], bool $bPost = true)
    {
        return is_array($xData_arr)
            ? self::doCall($cURL, $xData_arr, $bPost)
            : '';
    }

    /**
     * @param string $cURL
     * @param string $cFile
     * @param bool   $bDeleteFile
     * @return mixed|string
     * @throws Exception
     */
    public static function sendFile(string $cURL, string $cFile, bool$bDeleteFile = false)
    {
        if (file_exists($cFile)) {
            $aData_arr['opt_file'] = '@' . $cFile;
            $cContent              = self::doCall($cURL, $aData_arr, true);
            if ($bDeleteFile) {
                @unlink($cFile);
            }

            return $cContent;
        }

        return '';
    }
}
