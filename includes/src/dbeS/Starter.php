<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

use Generator;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\dbeS\Push\AbstractPush;
use JTL\dbeS\Push\Invoice;
use JTL\dbeS\Push\MediaFiles;
use JTL\dbeS\Push\Payments;
use JTL\dbeS\Sync\AbstractSync;
use JTL\dbeS\Sync\Attributes;
use JTL\dbeS\Sync\Brocken;
use JTL\dbeS\Sync\Data;
use JTL\dbeS\Sync\Downloads;
use JTL\dbeS\Sync\Globals;
use JTL\dbeS\Sync\Images;
use JTL\dbeS\Sync\ConfigGroups;
use JTL\dbeS\Sync\Manufacturers;
use JTL\dbeS\Sync\Orders;
use JTL\dbeS\Sync\Products;
use JTL\dbeS\Sync\ImageCheck;
use JTL\dbeS\Sync\ImageLink;
use JTL\dbeS\Sync\ImageUpload;
use JTL\dbeS\Sync\Categories;
use JTL\dbeS\Sync\Customer;
use JTL\dbeS\Sync\DeliverySlips;
use JTL\dbeS\Sync\QuickSync;
use JTL\Helpers\Text;
use JTL\XML;
use Psr\Log\LoggerInterface;
use JTL\dbeS\Push\Orders as PushOrders;
use JTL\dbeS\Push\Data as PushData;
use JTL\dbeS\Push\Customers;

/**
 * Class Starter
 * @package JTL\dbeS
 */
class Starter
{
    public const ERROR_NOT_AUTHORIZED = 3;

    public const ERROR_UNZIP = 2;

    public const OK = 0;

    /**
     * @var array
     */
    private static $pullMapping = [
        'Artikel_xml'      => Products::class,
        'Bestellungen_xml' => Orders::class,
        'Bilder_xml'       => Images::class,
        'Brocken_xml'      => Brocken::class,
        'Date_xml'         => Data::class,
        'Download_xml'     => Downloads::class,
        'Globals_xml'      => Globals::class,
        'Hersteller_xml'   => Manufacturers::class,
        'img_check'        => ImageCheck::class,
        'img_link'         => ImageLink::class,
        'img_upload'       => ImageUpload::class,
        'Kategorien_xml'   => Categories::class,
        'Konfig_xml'       => ConfigGroups::class,
        'Kunden_xml'       => Customer::class,
        'Lieferschein_xml' => DeliverySlips::class,
        'Merkmal_xml'      => Attributes::class,
        'QuickSync_xml'    => QuickSync::class,
        'SetKunde_xml'     => Customer::class
    ];

    /**
     * @var array
     */
    private static $pushMapping = [
        'GetBestellungen_xml'  => PushOrders::class,
        'GetData_xml'          => PushData::class,
        'GetKunden_xml'        => Customers::class,
        'GetMediendateien_xml' => MediaFiles::class,
        'GetZahlungen_xml'     => Payments::class,
        'Invoice_xml'          => Invoice::class
    ];

    /**
     * @var array
     */
    private static $netSyncMapping = [
        'Cronjob_xml'           => SyncCronjob::class,
        'GetDownloadStruct_xml' => ProductDownloads::class,
        'Upload_xml'            => Uploader::class
    ];

    /**
     * @var Synclogin
     */
    private $auth;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @var array
     */
    private $postData;

    /**
     * @var array
     */
    private $files;

    /**
     * @var string
     */
    private $unzipPath;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Starter constructor.
     * @param Synclogin         $syncLogin
     * @param FileHandler       $fileHandler
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param LoggerInterface   $log
     */
    public function __construct(
        Synclogin $syncLogin,
        FileHandler $fileHandler,
        DbInterface $db,
        JTLCacheInterface $cache,
        LoggerInterface $log
    ) {
        $this->auth        = $syncLogin;
        $this->fileHandler = $fileHandler;
        $this->logger      = $log;
        $this->db          = $db;
        $this->cache       = $cache;
        $this->checkPermissions();
    }

    private function checkPermissions(): void
    {
        $tmpDir = \PFAD_ROOT . \PFAD_DBES . \PFAD_SYNC_TMP;
        if (!\is_writable($tmpDir)) {
            \syncException(
                'Fehler beim Abgleich: Das Verzeichnis ' . $tmpDir . ' ist nicht beschreibbar!',
                \FREIDEFINIERBARER_FEHLER
            );
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @param string|null $index
     * @return array|string
     */
    public function getPostData(string $index = null)
    {
        return $index === null ? $this->postData : ($this->postData[$index] ?? '');
    }

    /**
     * @param array $postData
     */
    public function setPostData(array $postData): void
    {
        $this->postData = $postData;
    }

    /**
     * @return string
     */
    public function getUnzipPath(): string
    {
        return $this->unzipPath;
    }

    /**
     * @param string $unzipPath
     */
    public function setUnzipPath(string $unzipPath): void
    {
        $this->unzipPath = $unzipPath;
    }

    /**
     * @param $post
     * @return bool
     * @throws \Exception
     */
    public function checkAuth($post): bool
    {
        if (!isset($post['userID'], $post['userPWD'])) {
            return false;
        }

        return $this->auth->checkLogin(\utf8_encode($post['userID']), \utf8_encode($post['userPWD'])) === true;
    }

    /**
     * @param array $files
     * @return array|null
     */
    public function getFiles(array $files): ?array
    {
        return $this->fileHandler->getSyncFiles($files);
    }

    /**
     * @param string $handledFile
     */
    private function executeNetSync(string $handledFile): void
    {
        $mapping = self::$netSyncMapping[$handledFile] ?? null;
        if ($mapping === null) {
            return;
        }
        require_once \PFAD_ROOT . \PFAD_DBES . 'NetSync_inc.php';
        NetSyncHandler::create($mapping, $this->db, $this->logger);
        exit();
    }

    /**
     * @param string $handledFile
     * @param array  $post
     * @param array  $files
     * @return int
     * @throws \Exception
     */
    public function start(string $handledFile, array $post, array $files): int
    {
        if ($handledFile === 'lastjobs') {
            $this->init($post, [], false);
            $lastjobs = new LastJob($this->db, $this->logger);
            $lastjobs->execute();
            echo self::OK;
            exit();
        }
        if ($handledFile === 'mytest') {
            $this->init($post, [], false);
            $test = new Test($this->db);
            echo $test->execute();
            exit();
        }
        $this->executeNetSync($handledFile);
        $direction = 'pull';
        $handler   = self::$pullMapping[$handledFile] ?? null;
        if ($handler === null) {
            $handler = self::$pushMapping[$handledFile] ?? null;
            if ($handler !== null) {
                $direction = 'push';
            }
        }
        if ($handler === null) {
            die();
        }
        $this->setPostData($post);
        $this->setData($files['data']['tmp_name'] ?? null);

        if ($direction === 'pull') {
            $res        = '';
            $unzip      = $handler !== Brocken::class;
            $fromHandle = $handler === Customer::class;
            $return     = $this->init($post, $files, $unzip);
            if ($return === self::OK) {
                /** @var AbstractSync $sync */
                $sync = new $handler($this->db, $this->cache, $this->logger);
                $res  = $sync->handle($this);
            }
            if ($fromHandle === false) {
                echo $return;
                exit();
            }

            echo \is_array($res)
                ? $return . ";\n" . Text::convertISO(XML::serialize($res))
                : $return . ';' . $res;
        } else {
            $this->init($post, [], false);
            /** @var AbstractPush $pusher */
            $pusher = new $handler($this->db, $this->cache, $this->logger);
            $xml    = $pusher->getData();
            if (\is_array($xml) && \count($xml) > 0) {
                $pusher->zipRedirect(\time() . '.jtl', $xml);
            }

            echo self::OK;
        }
        exit();
    }

    /**
     * @param string $handledFile
     */
    private function bypass(string $handledFile): void
    {
        $file = \PFAD_ROOT . \PFAD_DBES . \basename($handledFile) . '.php';
        $real = \realpath($file);
        if ($real === false || \strpos($real, \PFAD_ROOT . \PFAD_DBES) !== 0) {
            exit();
        }
        if (\file_exists($real)) {
            include $real;
        }
        exit();
    }

    /**
     * @param array $post
     * @param array $files
     * @param bool  $unzip
     * @return int
     * @throws \Exception
     */
    public function init(array $post, array $files, bool $unzip = true): int
    {
        if (!$this->checkAuth($post)) {
            return self::ERROR_NOT_AUTHORIZED;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'mailTools.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $this->setPostData($post);
        $this->setData($files['data']['tmp_name'] ?? null);
        if ($unzip !== true) {
            return self::OK;
        }
        $this->files     = $this->getFiles($files);
        $this->unzipPath = $this->fileHandler->getUnzipPath();
        if ($this->files === null) {
            return self::ERROR_UNZIP;
        }

        return self::OK;
    }


    /**
     * @param bool $string
     * @return Generator
     */
    public function getXML(bool $string = false): Generator
    {
        foreach ($this->files as $xmlFile) {
            if (\strpos($xmlFile, '.xml') === false) {
                continue;
            }
            $data = \file_get_contents($xmlFile);

            yield [$xmlFile => $string ? \simplexml_load_string($data) : XML::unserialize($data)];
        }
    }
}
