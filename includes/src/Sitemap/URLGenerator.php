<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap;

use Cache\JTLCacheInterface;
use DB\DbInterface;

/**
 * Class URLGenerator
 * @package Sitemap
 */
final class URLGenerator
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $baseURL;
    /**
     * @var int
     */
    private $productsPerPage;

    /**
     * URLGenerator constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param array             $config
     * @param string            $baseURL
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, array $config, string $baseURL)
    {
        $this->db      = $db;
        $this->cache   = $cache;
        $this->baseURL = $baseURL;
        $this->config  = $config;
        $this->setProductsPerPage();
    }

    /**
     * @return int
     */
    private function setProductsPerPage(): int
    {
        $config                = $this->config['artikeluebersicht'];
        $this->productsPerPage = ($ppp = (int)$config['artikeluebersicht_artikelproseite']) > 0
            ? $ppp
            : 20;
        if ($config['artikeluebersicht_erw_darstellung'] === 'Y') {
            $defView = ($def = (int)$config['artikeluebersicht_erw_darstellung_stdansicht']) > 0
                ? $def
                : \ERWDARSTELLUNG_ANSICHT_LISTE;
            switch ($defView) {
                case \ERWDARSTELLUNG_ANSICHT_LISTE:
                    $this->productsPerPage = (int)$config['artikeluebersicht_anzahl_darstellung1'];
                    break;
                case \ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $this->productsPerPage = (int)$config['artikeluebersicht_anzahl_darstellung2'];
                    break;
                case \ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $this->productsPerPage = (int)$config['artikeluebersicht_anzahl_darstellung3'];
                    break;
                default:
                    break;
            }
        }

        return $this->productsPerPage;
    }

    /**
     * @param int    $kKey
     * @param string $cKey
     * @param array  $languages
     * @param int    $langID
     * @return string
     */
    public function getExportURL(int $kKey, string $cKey, array $languages, int $langID): string
    {
        $url    = '';
        $params = [];
        \Shop::setLanguage($langID);
        $pfConfig = new \Filter\Config();
        $pfConfig->setLanguageID($langID);
        $pfConfig->setLanguages($languages);
        $pfConfig->setConfig($this->config);
        $pfConfig->setCustomerGroupID(\Session\Session::CustomerGroup()->getID());
        $pfConfig->setBaseURL($this->baseURL);
        $pf = new \Filter\ProductFilter($pfConfig, $this->db, $this->cache);
        switch ($cKey) {
            case 'kKategorie':
                $params['kKategorie'] = $kKey;
                $pf->initStates($params);
                break;

            case 'kHersteller':
                $params['kHersteller'] = $kKey;
                $pf->initStates($params);
                break;

            case 'kSuchanfrage':
                $params['kSuchanfrage'] = $kKey;
                $pf->initStates($params);
                if ($kKey > 0) {
                    $oSuchanfrage = $this->db->queryPrepared(
                        'SELECT cSuche
                            FROM tsuchanfrage
                            WHERE kSuchanfrage = :ks
                            ORDER BY kSuchanfrage',
                        ['ks' => $kKey],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($oSuchanfrage->cSuche)) {
                        $pf->getSearchQuery()->setID($kKey)->setName($oSuchanfrage->cSuche);
                    }
                }
                break;

            case 'kMerkmalWert':
                $params['kMerkmalWert'] = $kKey;
                $pf->initStates($params);
                break;

            case 'kTag':
                $params['kTag'] = $kKey;
                $pf->initStates($params);
                break;

            case 'kSuchspecial':
                $params['kSuchspecial'] = $kKey;
                $pf->initStates($params);
                break;

            default:
                return $url;
        }
        $searchResults = $pf->generateSearchResults(null, false, $this->productsPerPage);
        if (($cKey === 'kKategorie' && $kKey > 0) || $searchResults->getProductCount() > 0) {
            $url = $pf->getFilterURL()->getURL();
        }

        return $url;
    }
}
