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
class URLGenerator
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

    private function setProductsPerPage()
    {
        $this->productsPerPage = ((int)$this->config['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0)
            ? (int)$this->config['artikeluebersicht']['artikeluebersicht_artikelproseite']
            : 20;
        if ($this->config['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'Y') {
            $nStdDarstellung = (int)$this->config['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                ? (int)$this->config['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']
                : \ERWDARSTELLUNG_ANSICHT_LISTE;
            if ($nStdDarstellung > 0) {
                switch ($nStdDarstellung) {
                    case \ERWDARSTELLUNG_ANSICHT_LISTE:
                        $this->productsPerPage = (int)$this->config['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        break;
                    case \ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $this->productsPerPage = (int)$this->config['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                        break;
                    case \ERWDARSTELLUNG_ANSICHT_MOSAIK:
                        $this->productsPerPage = (int)$this->config['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                        break;
                }
            }
        }
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
        $filterConfig = new \Filter\Config();
        $filterConfig->setLanguageID($langID);
        $filterConfig->setLanguages($languages);
        $filterConfig->setConfig($this->config);
        $filterConfig->setCustomerGroupID(\Session\Session::CustomerGroup()->getID());
        $filterConfig->setBaseURL($this->baseURL);
        $naviFilter = new \Filter\ProductFilter($filterConfig, $this->db, $this->cache);
        switch ($cKey) {
            case 'kKategorie':
                $params['kKategorie'] = $kKey;
                $naviFilter->initStates($params);
                break;

            case 'kHersteller':
                $params['kHersteller'] = $kKey;
                $naviFilter->initStates($params);
                break;

            case 'kSuchanfrage':
                $params['kSuchanfrage'] = $kKey;
                $naviFilter->initStates($params);
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
                        $naviFilter->getSearchQuery()->setID($kKey)->setName($oSuchanfrage->cSuche);
                    }
                }
                break;

            case 'kMerkmalWert':
                $params['kMerkmalWert'] = $kKey;
                $naviFilter->initStates($params);
                break;

            case 'kTag':
                $params['kTag'] = $kKey;
                $naviFilter->initStates($params);
                break;

            case 'kSuchspecial':
                $params['kSuchspecial'] = $kKey;
                $naviFilter->initStates($params);
                break;

            default:
                return $url;
        }
        $oSuchergebnisse = $naviFilter->generateSearchResults(null, false, $this->productsPerPage);
        if (($cKey === 'kKategorie' && $kKey > 0) || $oSuchergebnisse->getProductCount() > 0) {
            $url = $naviFilter->getFilterURL()->getURL();
        }

        return $url;
    }
}
