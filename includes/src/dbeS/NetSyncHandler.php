<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

use Exception;
use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class NetSyncHandler
 * @package JTL\dbeS
 */
class NetSyncHandler
{
    /**
     * @var NetSyncHandler
     */
    private static $instance;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NetSyncHandler constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        if (self::$instance !== null) {
            throw new Exception('Class ' . __CLASS__ . ' already created');
        }
        self::$instance = $this;
        $this->db       = $db;
        $this->logger   = $logger;
        if (!$this->isAuthenticated()) {
            static::throwResponse(NetSyncResponse::ERRORLOGIN);
        }
        $this->request((int)$_REQUEST['e']);
    }

    /**
     * @return bool
     */
    protected function isAuthenticated(): bool
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
            ? (new Synclogin($this->db, $this->logger))->checkLogin($cName, $cPass)
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
    protected static function throwResponse($nCode, $oData = null): void
    {
        $response         = new stdClass();
        $response->nCode  = $nCode;
        $response->cToken = '';
        $response->oData  = null;
        if ($nCode === 0) {
            $response->cToken = \session_id();
            $response->oData  = $oData;
        }
        echo \json_encode($response);
        exit;
    }

    /**
     * @param int $request
     */
    protected function request($request)
    {
    }

    /**
     * @param string          $class
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public static function create(string $class, DbInterface $db, LoggerInterface $logger): void
    {
        if (self::$instance === null && \class_exists($class)) {
            new $class($db, $logger);
            \set_exception_handler([$class, 'exception']);
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

    /**
     * @param string $baseDir
     * @return array
     */
    protected function getFolderStruct(string $baseDir): array
    {
        $folders = [];
        $baseDir = \realpath($baseDir);
        foreach (\scandir($baseDir, \SCANDIR_SORT_ASCENDING) as $folder) {
            if ($folder === '.' || $folder === '..' || $folder[0] === '.') {
                continue;
            }
            $pathName = $baseDir . \DIRECTORY_SEPARATOR . $folder;
            if (\is_dir($pathName)) {
                $systemFolder              = new SystemFolder($folder, $pathName);
                $systemFolder->oSubFolders = $this->getFolderStruct($pathName);
                $folders[]                 = $systemFolder;
            }
        }

        return $folders;
    }

    /**
     * @param string $baseDir
     * @param bool   $preview
     * @return array
     */
    protected function getFilesStruct(string $baseDir, $preview = false): array
    {
        $index   = 0;
        $files   = [];
        $baseDir = \realpath($baseDir);
        foreach (\scandir($baseDir, \SCANDIR_SORT_ASCENDING) as $file) {
            if ($file === '.' || $file === '..' || $file[0] === '.') {
                continue;
            }
            $pathName = $baseDir . \DIRECTORY_SEPARATOR . $file;
            if (\is_file($pathName)) {
                $pathinfo = \pathinfo($pathName);
                $files[]  = new SystemFile(
                    $index++,
                    $pathName,
                    \substr($pathName, \strlen($preview ? \PFAD_DOWNLOADS_PREVIEW : \PFAD_DOWNLOADS)),
                    $pathinfo['filename'],
                    $pathinfo['dirname'],
                    $pathinfo['extension'],
                    \filemtime($pathName),
                    \filesize($pathName)
                );
            }
        }

        return $files;
    }
}
