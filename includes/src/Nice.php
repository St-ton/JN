<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use stdClass;
use XTEA;
use JTL\DB\ReturnType;

/**
 * Class Nice
 * @package JTL
 */
class Nice
{
    /**
     * @var null|Nice
     */
    private static $instance;

    /**
     * @var string
     */
    private $brocken;

    /**
     * @var string
     */
    private $apiKey = '';

    /**
     * @var string
     */
    private $domain = '';

    /**
     * @var array
     */
    private $moduleIDs = [];

    /**
     * @return Nice
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * Nice constructor.
     */
    protected function __construct()
    {
        $this->brocken = $this->load();
        if (\mb_strlen($this->brocken) > 0) {
            $parts = \explode(';', $this->brocken);
            if (!empty($parts[0])) {
                $this->apiKey = $parts[0];
            }
            if (!empty($parts[1])) {
                $this->domain = \trim($parts[1]);
            }
            if (($count = \count($parts)) > 2) {
                for ($i = 2; $i < $count; $i++) {
                    $this->moduleIDs[] = (int)$parts[$i];
                }
            }
        }
        $this->initConstants();
        self::$instance = $this;
    }

    /**
     * @return string
     */
    private function load(): string
    {
        $cacheID = 'cbrocken';
        if (($brocken = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->query(
                'SELECT cBrocken 
                    FROM tbrocken 
                    LIMIT 1',
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($data->cBrocken)) {
                $passA   = \mb_substr(\base64_decode($data->cBrocken), 0, 9);
                $passE   = \mb_substr(
                    \base64_decode($data->cBrocken),
                    \mb_strlen(\base64_decode($data->cBrocken)) - 11
                );
                $xtea    = new XTEA($passA . $passE);
                $brocken = $xtea->decrypt(
                    \str_replace(
                        [$passA, $passE],
                        ['', ''],
                        \base64_decode($data->cBrocken)
                    )
                );
                Shop::Container()->getCache()->set($cacheID, $brocken, [\CACHING_GROUP_CORE]);
            }
        }

        return $brocken;
    }

    /**
     * @param int $moduleID
     * @return bool
     */
    public function checkErweiterung(int $moduleID): bool
    {
        return ($this->apiKey !== ''
            && \mb_strlen($this->apiKey) > 0
            && !empty($this->domain)
            && \count($this->moduleIDs) > 0)
            ? \in_array($moduleID, $this->moduleIDs, true)
            : false;
    }

    /**
     * @return $this
     */
    private function initConstants(): self
    {
        \ifndef('SHOP_ERWEITERUNG_SEO', 8001);
        \ifndef('SHOP_ERWEITERUNG_UMFRAGE', 8021);
        \ifndef('SHOP_ERWEITERUNG_AUSWAHLASSISTENT', 8031);
        \ifndef('SHOP_ERWEITERUNG_UPLOADS', 8041);
        \ifndef('SHOP_ERWEITERUNG_DOWNLOADS', 8051);
        \ifndef('SHOP_ERWEITERUNG_KONFIGURATOR', 8061);
        \ifndef('SHOP_ERWEITERUNG_WARENRUECKSENDUNG', 8071);
        \ifndef('SHOP_ERWEITERUNG_BRANDFREE', 8081);

        return $this;
    }

    /**
     * @return array
     */
    public function gibAlleMoeglichenModule(): array
    {
        $modules = [];
        if (!\defined(\SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
            $this->initConstants();
        }
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_UMFRAGE;
        $module->cName    = 'Umfragen Modul';
        $module->cDefine  = 'SHOP_ERWEITERUNG_UMFRAGE';
        $module->cURL     = 'https://jtl-url.de/ykepb';
        $modules[]        = $module;
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_AUSWAHLASSISTENT;
        $module->cName    = 'Auswahlassistent Modul';
        $module->cDefine  = 'SHOP_ERWEITERUNG_AUSWAHLASSISTENT';
        $module->cURL     = 'https://jtl-url.de/q6tox';
        $modules[]        = $module;
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_UPLOADS;
        $module->cName    = 'Upload Modul';
        $module->cDefine  = 'SHOP_ERWEITERUNG_UPLOADS';
        $module->cURL     = 'https://jtl-url.de/7-cop';
        $modules[]        = $module;
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_DOWNLOADS;
        $module->cName    = 'Download Modul';
        $module->cDefine  = 'SHOP_ERWEITERUNG_DOWNLOADS';
        $module->cURL     = 'https://jtl-url.de/i0zvj';
        $modules[]        = $module;
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_KONFIGURATOR;
        $module->cName    = 'Konfigurator Modul';
        $module->cDefine  = 'SHOP_ERWEITERUNG_KONFIGURATOR';
        $module->cURL     = 'https://jtl-url.de/ni9f5';
        $modules[]        = $module;
        $module           = new stdClass();
        $module->kModulId = \SHOP_ERWEITERUNG_BRANDFREE;
        $module->cName    = 'Brandfree Option';
        $module->cDefine  = 'SHOP_ERWEITERUNG_BRANDFREE';
        $module->cURL     = 'https://jtl-url.de/t4egb';
        $modules[]        = $module;

        return $modules;
    }

    /**
     * @return string
     */
    public function getBrocken(): string
    {
        return $this->brocken;
    }

    /**
     * @return string
     */
    public function getAPIKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function getShopModul(): array
    {
        return $this->moduleIDs;
    }
}
