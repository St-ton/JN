<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Boxes\BoxFactory;
use Boxes\BoxInterface;
use Boxes\BoxType;
use Boxes\Renderer\DefaultRenderer;
use DB\DbInterface;
use DB\ReturnType;
use Filter\ProductFilter;
use Filter\ProductFilterSearchResults;
use Filter\Visibility;

/**
 * Class BoxService
 */
class BoxService implements BoxServiceInterface
{
    /**
     * @var BoxInterface[]
     */
    public $boxes = [];

    /**
     * @var array
     */
    public $config = [];

    /**
     * unrendered box template file name + data
     *
     * @var array
     */
    public $rawData = [];

    /**
     * @var array
     */
    public $visibility;

    /**
     * @var \Boxes\BoxFactory
     */
    private $factory;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var BoxService
     */
    private static $_instance;

    /**
     * @param array       $config
     * @param BoxFactory  $factory
     * @param DbInterface $db
     * @return BoxService
     */
    public static function getInstance(array $config, BoxFactory $factory, DbInterface $db): self
    {
        return self::$_instance ?? new self($config, $factory, $db);
    }

    /**
     * BoxService constructor.
     *
     * @param array       $config
     * @param BoxFactory  $factory
     * @param DbInterface $db
     */
    public function __construct(array $config, BoxFactory $factory, DbInterface $db)
    {
        $this->config    = $config;
        $this->factory   = $factory;
        $this->db        = $db;
        self::$_instance = $this;
    }

    /**
     * @param int $kArtikel
     * @param int $nMaxAnzahl
     */
    public function addRecentlyViewed(int $kArtikel, $nMaxAnzahl = null)
    {
        if ($kArtikel <= 0) {
            return;
        }
        if ($nMaxAnzahl === null) {
            $nMaxAnzahl = (int)$this->config['boxen']['box_zuletztangesehen_anzahl'];
        }
        if (!isset($_SESSION['ZuletztBesuchteArtikel']) || !is_array($_SESSION['ZuletztBesuchteArtikel'])) {
            $_SESSION['ZuletztBesuchteArtikel'] = [];
        }
        $oArtikel           = new \stdClass();
        $oArtikel->kArtikel = $kArtikel;
        if (isset($_SESSION['ZuletztBesuchteArtikel']) && count($_SESSION['ZuletztBesuchteArtikel']) > 0) {
            $alreadyPresent = false;
            foreach ($_SESSION['ZuletztBesuchteArtikel'] as $_article) {
                if (isset($_article->kArtikel) && $_article->kArtikel === $oArtikel->kArtikel) {
                    $alreadyPresent = true;
                    break;
                }
            }
            if ($alreadyPresent === false) {
                if (count($_SESSION['ZuletztBesuchteArtikel']) < $nMaxAnzahl) {
                    $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
                } else {
                    $oTMP_arr = array_reverse($_SESSION['ZuletztBesuchteArtikel']);
                    array_pop($oTMP_arr);
                    $oTMP_arr                           = array_reverse($oTMP_arr);
                    $oTMP_arr[]                         = $oArtikel;
                    $_SESSION['ZuletztBesuchteArtikel'] = $oTMP_arr;
                }
            }
        } else {
            $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
        }
        executeHook(HOOK_ARTIKEL_INC_ZULETZTANGESEHEN);
    }

    /**
     * @param int  $nSeite
     * @param bool $bGlobal
     * @return array|bool
     */
    public function holeBoxAnzeige(int $nSeite, bool $bGlobal = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $oBoxAnzeige = [];
        $oBox_arr    = $this->db->selectAll('tboxenanzeige', 'nSeite', $nSeite);
        if (is_array($oBox_arr) && count($oBox_arr)) {
            foreach ($oBox_arr as $oBox) {
                $oBoxAnzeige[$oBox->ePosition] = (boolean)$oBox->bAnzeigen;
            }
            $this->visibility = $oBoxAnzeige;

            return $oBoxAnzeige;
        }

        return $nSeite !== 0 && $bGlobal
            ? $this->holeBoxAnzeige(0)
            : false;
    }

    /**
     * @param int          $kBox
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $kBox, int $kSeite, $cFilter = ''): int
    {
        if (is_array($cFilter)) {
            $cFilter = array_unique($cFilter);
            $cFilter = implode(',', $cFilter);
        }
        $_upd          = new \stdClass();
        $_upd->cFilter = $cFilter;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, $kSeite], $_upd);
    }

    /**
     * @param ProductFilter              $pf
     * @param ProductFilterSearchResults $sr
     * @return bool
     */
    public function gibBoxenFilterNach(ProductFilter $pf, ProductFilterSearchResults $sr): bool
    {
        $cf  = $pf->getCategoryFilter();
        $mf  = $pf->getManufacturerFilter();
        $prf = $pf->getPriceRangeFilter();
        $rf  = $pf->getRatingFilter();
        $tf  = $pf->tagFilterCompat;
        $afc = $pf->getAttributeFilterCollection();
        $ssf = $pf->getSearchSpecialFilter();
        $sf  = $pf->searchFilterCompat;

        $invis      = Visibility::SHOW_NEVER();
        $visContent = Visibility::SHOW_CONTENT();

        return ((!$cf->getVisibility()->equals($invis) && !$cf->getVisibility()->equals($visContent))
            || (!$mf->getVisibility()->equals($invis) && !$mf->getVisibility()->equals($visContent))
            || (!$prf->getVisibility()->equals($invis) && !$prf->getVisibility()->equals($visContent))
            || (!$rf->getVisibility()->equals($invis) && !$rf->getVisibility()->equals($visContent))
            || (!$tf->getVisibility()->equals($invis) && !$tf->getVisibility()->equals($visContent))
            || (!$afc->getVisibility()->equals($invis) && !$afc->getVisibility()->equals($visContent))
            || (!$ssf->getVisibility()->equals($invis) && !$ssf->getVisibility()->equals($visContent))
            || (!$sf->getVisibility()->equals($invis) && !$sf->getVisibility()->equals($visContent))
        );
    }

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * @return array
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     */
    public function compatGet(): array
    {
        $boxes = [];
        foreach ($this->rawData as $_type => $_boxes) {
            $boxes[$_type] = [];
            foreach ($_boxes as $_box) {
                $boxes[$_type][] = $_box['obj'];
            }
        }

        return $boxes;
    }

    /**
     * @param array $positionedBoxes
     * @return array
     * @throws \Exception
     * @throws \SmartyException
     */
    public function render(array $positionedBoxes): array
    {
        $smarty   = \Shop::Smarty();
        $pageType = \Shop::getPageType();
        $pageID   = 0;
        if ($pageType === PAGE_ARTIKELLISTE) {
            $pageID = (int)\Shop::$kKategorie;
        } elseif ($pageType === PAGE_ARTIKEL) {
            $pageID = (int)\Shop::$kArtikel;
        } elseif ($pageType === PAGE_EIGENE) {
            $pageID = (int)\Shop::$kLink;
        } elseif ($pageType === PAGE_HERSTELLER) {
            $pageID = (int)\Shop::$kHersteller;
        }
        $originalArticle   = $smarty->getTemplateVars('Artikel');
        $productFilter     = \Shop::getProductFilter();
        $filterAfter       = !empty($this->config)
            ? $this->gibBoxenFilterNach($productFilter, $productFilter->getSearchResults(false))
            : 0;
        $htmlArray         = [
            'top'    => null,
            'right'  => null,
            'bottom' => null,
            'left'   => null
        ];
        $smarty->assign('BoxenEinstellungen', $this->config)
               ->assign('bBoxenFilterNach', $filterAfter)
               ->assign('NettoPreise', \Session::CustomerGroup()->getIsMerchant());

        $boxRenderer = new DefaultRenderer($smarty);
        foreach ($positionedBoxes as $_position => $boxes) {
            if (!is_array($boxes)) {
                $boxes = [];
            }
            $htmlArray[$_position]     = '';
            $this->rawData[$_position] = [];
            foreach ($boxes as $box) {
                /** @var BoxInterface $box */
                $renderClass = $box->getRenderer();
                if ($renderClass !== get_class($boxRenderer)) {
                    $boxRenderer = new $renderClass($smarty);
                }
                $boxRenderer->setBox($box);
                $html = trim($boxRenderer->render($pageType, $pageID));
                $box->setRenderedContent($html);
                $htmlArray[$_position]       .= $html;
                $this->rawData[$_position][] = [
                    'obj' => $box,
                    'tpl' => $box->getTemplateFile()
                ];
            }
        }
        // avoid modification of article object on render loop
        if ($originalArticle !== null) {
            $smarty->assign('Artikel', $originalArticle);
        }

        return $htmlArray;
    }

    /**
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @return array
     */
    public function buildList(int $nSeite = 0, bool $bAktiv = true, bool $bVisible = false): array
    {
        $cacheID           = 'bx_' . $nSeite .
            '_' . (($bAktiv === true) ? '1' : '0') .
            '_' . (($bVisible === true) ? '1' : '0') .
            '_' . \Shop::getLanguageID();
        $this->visibility  = $this->holeBoxAnzeige($nSeite);
        $template          = \Template::getInstance();
        $templatePositions = $template->getBoxLayoutXML();
        $visiblePositions  = [];
        foreach ($this->visibility as $position => $isVisible) {
            if (isset($templatePositions[$position])) {
                $isVisible = $isVisible && $templatePositions[$position];
            }
            if ($isVisible) {
                $visiblePositions[] = $position;
            }
        }
        $visiblePositions = \Functional\map($visiblePositions, function ($e) {
            return "'" . $e . "'";
        });
        $cacheTags        = [CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes'];
        $cSQLAktiv        = $bAktiv
            ? ' AND tboxen.ePosition IN (' . implode(',', $visiblePositions) . ')'
            : '';
        $cPluginAktiv     = $bAktiv
            ? " AND (tplugin.nStatus IS NULL OR tplugin.nStatus = 2  OR tboxvorlage.eTyp != 'plugin')"
            : '';
        if (($grouped = \Shop::Cache()->get($cacheID)) === false) {
            $sql = 'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.kContainer, 
                       tboxen.cTitel, tboxen.ePosition, tboxensichtbar.kSeite, tboxensichtbar.nSort, 
                       tboxensichtbar.bAktiv, tboxensichtbar.cFilter, tboxvorlage.eTyp, 
                       tboxvorlage.cName, tboxvorlage.cTemplate, tplugin.nStatus AS pluginStatus,
                       GROUP_CONCAT(tboxensichtbar.kSeite) AS pageIDs,
                       GROUP_CONCAT(tboxensichtbar.bAktiv) AS pageVisibilities,                       
                       tsprache.kSprache, tboxsprache.cInhalt, tboxsprache.cTitel
                    FROM tboxen
                    LEFT JOIN tboxensichtbar
                        ON tboxen.kBox = tboxensichtbar.kBox
                    LEFT JOIN tplugin
                        ON tboxen.kCustomID = tplugin.kPlugin
                    LEFT JOIN tboxvorlage
                        ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                    LEFT JOIN tboxsprache
                        ON tboxsprache.kBox = tboxen.kBox
                    LEFT JOIN tsprache
                        ON tsprache.cISO = tboxsprache.cISO
                    WHERE tboxen.kContainer > -1 ' . $cSQLAktiv . $cPluginAktiv .
                ' GROUP BY tboxsprache.kBoxSprache, tboxen.kBox, tboxensichtbar.cFilter
                    ORDER BY tboxensichtbar.nSort, tboxen.kBox ASC';
            $boxData = $this->db->query(
                $sql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $grouped = \Functional\group($boxData, function ($e) {
                return $e->kBox;
            });
            \Shop::Cache()->set($cacheID, $grouped, array_unique($cacheTags));
        }
        $children = [];
        foreach ($grouped as $i => $boxes) {
            if (!is_array($boxes)) {
                continue;
            }
            $first = \Functional\first($boxes);
            if ((int)$first->kContainer > 0) {
                $boxInstance = $this->factory->getBoxByBaseType($first->kBoxvorlage);
                $boxInstance->map($boxes);
                if (!isset($children[(int)$first->kContainer])) {
                    $children[(int)$first->kContainer] = [];
                }
                $children[(int)$first->kContainer][] = $boxInstance;
                unset($grouped[$i]);
            }
        }
        $result = [];
        foreach ($grouped as $boxes) {
            if (!is_array($boxes)) {
                continue;
            }
            $first       = \Functional\first($boxes);
            $boxInstance = $this->factory->getBoxByBaseType($first->kBoxvorlage);
            $boxInstance->map($boxes);
            if ($boxInstance->getType() === BoxType::CONTAINER) {
                $boxInstance->setChildren($children);
            }
            $result[] = $boxInstance;
        }
        $byPosition  = \Functional\group($result, function (BoxInterface $e) {
            return $e->getPosition();
        });
        $this->boxes = $byPosition;

        return $byPosition;
    }
}
