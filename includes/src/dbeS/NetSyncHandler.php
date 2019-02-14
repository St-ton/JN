<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

use Exception;
use stdClass;

/**
 * Class NetSyncHandler
 *
 * @package JTL\dbeS
 */
class NetSyncHandler
{
    /**
     * @var NetSyncHandler
     */
    private static $instance;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            throw new Exception('Class ' . __CLASS__ . ' already created');
        }
        self::$instance = $this;
        $this->init();
        if (!$this->isAuthed()) {
            static::throwResponse(NetSyncResponse::ERRORLOGIN);
        }
        $this->request((int)$_REQUEST['e']);
    }

    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @return bool
     */
    protected function isAuthed()
    {
        // by token
        if (isset($_REQUEST['t'])) {
            \session_id($_REQUEST['t']);
            \session_start();

            return $_SESSION['bAuthed'];
        }
        // by syncdata
        $cName   = \urldecode($_REQUEST['uid']);
        $cPass   = \urldecode($_REQUEST['upwd']);
        $bAuthed = (\strlen($cName) > 0 && \strlen($cPass) > 0)
            ? (new Synclogin())->checkLogin($cName, $cPass)
            : false;
        if ($bAuthed) {
            \session_start();
            $_SESSION['bAuthed'] = $bAuthed;
        }

        return $bAuthed;
    }

    /**
     * @param int        $nCode
     * @param null|mixed $oData
     */
    protected static function throwResponse($nCode, $oData = null)
    {
        $oResponse         = new stdClass();
        $oResponse->nCode  = $nCode;
        $oResponse->cToken = '';
        $oResponse->oData  = null;
        if ($nCode === 0) {
            $oResponse->cToken = \session_id();
            $oResponse->oData  = $oData;
        }
        echo \json_encode($oResponse);
        exit;
    }

    /**
     * @param int $request
     */
    protected function request($request)
    {
    }

    /**
     * @param Exception $oException
     */
    public static function exception($oException)
    {
    }

    /**
     * @param string $cClass
     */
    public static function create($cClass)
    {
        if (self::$instance === null && \class_exists($cClass)) {
            new $cClass;
            \set_exception_handler([$cClass, 'exception']);
        }
    }

    /**
     * @param string $filename
     * @param string $mimetype
     * @param string $outname
     */
    public function streamFile($filename, $mimetype, $outname = ''): void
    {
        $userAgent = empty($_SERVER['HTTP_USER_AGENT'])
            ? ''
            : $_SERVER['HTTP_USER_AGENT'];
        $browser   = 'other';
        if (\preg_match('/^Opera(\/| )([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'opera';
        } elseif (\preg_match('/^MSIE ([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'ie';
        } elseif (\preg_match('/^OmniWeb\/([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'omniweb';
        } elseif (\preg_match('/^Mozilla\/([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'mozilla';
        } elseif (\preg_match('/^Konqueror\/([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'konqueror';
        }
        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = 'application/octet-stream';
            if (($browser === 'ie') || ($browser === 'opera')) {
                $mimetype = 'application/octetstream';
            }
        }

        @\ob_end_clean();
        @\ini_set('zlib.output_compression', 'Off');

        \header('Pragma: public');
        \header('Content-Transfer-Encoding: none');

        if ($outname === '') {
            $outname = \basename($filename);
        }
        if ($browser === 'ie') {
            \header('Content-Type: ' . $mimetype);
            \header('Content-Disposition: inline; filename="' . $outname . '"');
        } else {
            \header('Content-Type: ' . $mimetype . '; name="' . $outname . '"');
            \header('Content-Disposition: attachment; filename=' . $outname);
        }
        $size = @\filesize($filename);
        if ($size) {
            \header('Content-length: ' . $size);
        }
        \readfile($filename);
        \unlink($filename);
        exit;
    }
}
