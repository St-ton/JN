<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class jtlAPI
 */
final class jtlAPI
{
    /**
     * @return object|null
     */
    public static function getSubscription()
    {
        try {
            $oNice      = Nice::getInstance();
            $cParam_arr = ['a' => 'getsubscription', 'key' => $oNice->getAPIKey(), 'domain' => $oNice->getDomain()];

            $oSecurity = new SecurityAPI();
            $oSecurity->setParam_arr($cParam_arr);

            $cParam_arr['p'] = $oSecurity->createKey();
            $cReturn         = CommunicationAPI::postData($cParam_arr);

            return json_decode($cReturn);
        } catch (Exception $exc) {
            Jtllog::writeLog("jtlAPI Exception: {$exc->getMessage()}");

            return null;
        }
    }

    /**
     * @param int $nVersion
     * @return object|null
     */
    public static function checkVersion($nVersion)
    {
        try {
            $oNice      = Nice::getInstance();
            $cParam_arr = [
                'a'      => 'getshop3version',
                'v'      => $nVersion,
                'domain' => $oNice->getDomain()
            ];

            $oSecurity = new SecurityAPI();
            $oSecurity->setParam_arr($cParam_arr);

            $cParam_arr['p'] = $oSecurity->createKey();
            $cReturn         = CommunicationAPI::postData($cParam_arr);

            return json_decode($cReturn);
        } catch (Exception $exc) {
            Jtllog::writeLog('jtlAPI Exception: {$exc->getMessage()}');

            return null;
        }
    }

    /**
     * @deprecated since 4.0
     * @param $nVersion
     * @return mixed
     */
    public static function checkShop3Version($nVersion)
    {
        return self::checkVersion($nVersion);
    }
}

/**
 * CommunicationAPI Class
 *
 * @access public
 * @author Daniel Boehmer
 */
final class CommunicationAPI
{
    /**
     * @var string
     */
    private static $cAPIUrl = 'http://jtladmin.jtl-software.de/jtlAPI.php';

    /**
     * @param array     $xPostData_arr
     * @param bool      $bPost
     * @param null|bool $bForceUrl
     * @return mixed
     * @throws Exception
     */
    private static function doCall($xPostData_arr, $bPost, $bForceUrl = null)
    {
        if (function_exists('curl_init')) {
            $cUrl = self::$cAPIUrl;
            if ($bForceUrl !== null) {
                $cUrl = $bForceUrl;
            }

            $ch = curl_init();
            @curl_setopt($ch, CURLOPT_POST, $bPost);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $xPostData_arr);
            @curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
            @curl_setopt($ch, CURLOPT_URL, $cUrl);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            @curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DEFAULT_CURL_OPT_VERIFYPEER);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, DEFAULT_CURL_OPT_VERIFYHOST);

            $cContent  = @curl_exec($ch);

            curl_close($ch);
        } else {
            throw new Exception("Die PHP Funktion curl_init existiert nicht!");
        }

        return $cContent;
    }

    /**
     * @param array $xData_arr
     * @param bool  $bPost
     * @param null  $bForceUrl
     * @return mixed|string
     */
    public static function postData($xData_arr = [], $bPost = true, $bForceUrl = null)
    {
        if (is_array($xData_arr)) {
            return self::doCall($xData_arr, $bPost, $bForceUrl);
        }

        return '';
    }

    /**
     * @param string $cFile
     * @param bool   $bDeleteFile
     * @return mixed|string
     */
    public static function sendFile($cFile, $bDeleteFile = false)
    {
        if (file_exists($cFile)) {
            $aData_arr['opt_file'] = '@' . $cFile;

            $cContent = self::doCall($aData_arr, true);

            if ($bDeleteFile) {
                @unlink($cFile);
            }

            return $cContent;
        }

        return '';
    }
}

/**
 * SecurityAPI Class
 *
 * @access public
 * @author Daniel Boehmer
 */
final class SecurityAPI
{
    /**
     * @access private
     * @var string
     */
    private $cSHA1Key;

    /**
     * @access private
     * @var array
     */
    private $cParam_arr;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->cSHA1Key   = '';
        $this->cParam_arr = [];
    }

    /**
     * Create SHA1 Key
     *
     * @access public
     * @param bool $bReturnKey
     * @return string|bool
     */
    public function createKey($bReturnKey = true)
    {
        if (is_array($this->cParam_arr) && count($this->cParam_arr) > 0) {
            $this->cSHA1Key = 'Ms=298h-fQW+DM=jtl';
            foreach ($this->cParam_arr as $cParam) {
                $this->cSHA1Key .= '.' . $cParam;
            }
            $this->cSHA1Key = sha1($this->cSHA1Key);

            if ($bReturnKey) {
                return $this->cSHA1Key;
            }

            return true;
        }

        return false;
    }

    /**
     * Sets the cParam_arr
     *
     * @access public
     * @param array $cParam_arr
     * @return $this
     */
    public function setParam_arr($cParam_arr)
    {
        $this->cParam_arr = $cParam_arr;

        return $this;
    }

    /**
     * Gets the cSHA1Key
     *
     * @access public
     * @return string
     */
    public function getSHA1Key()
    {
        return $this->cSHA1Key;
    }

    /**
     * Gets the cParam_arr
     *
     * @access public
     * @return array
     */
    public function getParam_arr()
    {
        return $this->cParam_arr;
    }
}
