<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services\JTL;

use function Functional\first;
use function Functional\group;
use function Functional\map;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Boxes\Factory;
use JTL\Boxes\FactoryInterface;
use JTL\Boxes\Items\BoxInterface;
use JTL\Boxes\Renderer\RendererInterface;
use JTL\Boxes\Type;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Filter\ProductFilter;
use JTL\Filter\Visibility;
use JTL\Plugin\LegacyPlugin;
use JTL\Plugin\Plugin;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template;
use function Functional\tail;

/**
 * Class BoxService
 * @package JTL\Services\JTL
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
     * @var Factory
     */
    private $factory;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var BoxServiceInterface
     */
    private static $instance;

    /**
     * @inheritdoc
     */
    public static function getInstance(
        array $config,
        FactoryInterface $factory,
        DbInterface $db,
        JTLCacheInterface $cache,
        JTLSmarty $smarty,
        RendererInterface $renderer
    ): BoxServiceInterface {
        return self::$instance ?? new self($config, $factory, $db, $cache, $smarty, $renderer);
    }

    /**
     * BoxService constructor.
     *
     * @inheritdoc
     */
    public function __construct(
        array $config,
        FactoryInterface $factory,
        DbInterface $db,
        JTLCacheInterface $cache,
        JTLSmarty $smarty,
        RendererInterface $renderer
    ) {
        $this->config   = $config;
        $this->factory  = $factory;
        $this->db       = $db;
        $this->cache    = $cache;
        $this->smarty   = $smarty;
        $this->renderer = $renderer;
        self::$instance = $this;
    }

    /**
     * @param int $productID
     * @param int $limit
     */
    public function addRecentlyViewed(int $productID, int $limit = null): void
    {
        if ($productID <= 0) {
            return;
        }
        if ($limit === null) {
            $limit = (int)$this->config['boxen']['box_zuletztangesehen_anzahl'];
        }
        $lastVisited    = $_SESSION['ZuletztBesuchteArtikel'] ?? [];
        $alreadyPresent = false;
        foreach ($lastVisited as $product) {
            if ($product->kArtikel === $productID) {
                $alreadyPresent = true;
                break;
            }
        }
        if ($alreadyPresent === false) {
            if (\count($lastVisited) >= $limit) {
                $lastVisited = tail($lastVisited);
            }
            $lastVisited[] = (object)['kArtikel' => $productID];
        }
        $_SESSION['ZuletztBesuchteArtikel'] = $lastVisited;
        \executeHook(\HOOK_ARTIKEL_INC_ZULETZTANGESEHEN);
    }

    /**
     * @param int  $pageType
     * @param bool $global
     * @return array|bool
     */
    public function getVisibility(int $pageType, bool $global = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $this->visibility = [];
        $boxes            = $this->db->selectAll('tboxenanzeige', 'nSeite', $pageType);
        if (\count($boxes) > 0) {
            foreach ($boxes as $box) {
                $this->visibility[$box->ePosition] = (bool)$box->bAnzeigen;
            }

            return $this->visibility;
        }

        return $pageType !== 0 && $global
            ? $this->getVisibility(0)
            : false;
    }

    /**
     * @param int          $boxID
     * @param int          $pageType
     * @param string|array $filter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $pageType, $filter = ''): int
    {
        if (\is_array($filter)) {
            $filter = \implode(',', \array_unique($filter));
        }

        return $this->db->update(
            'tboxensichtbar',
            ['kBox', 'kSeite'],
            [$boxID, $pageType],
            (object)['cFilter' => $filter]
        );
    }

    /**
     * @param ProductFilter $pf
     * @return bool
     */
    public function showBoxes(ProductFilter $pf): bool
    {
        $cf  = $pf->getCategoryFilter();
        $mf  = $pf->getManufacturerFilter();
        $prf = $pf->getPriceRangeFilter();
        $rf  = $pf->getRatingFilter();
        $afc = $pf->getAttributeFilterCollection();
        $ssf = $pf->getSearchSpecialFilter();
        $sf  = $pf->searchFilterCompat;

        $invis      = Visibility::SHOW_NEVER;
        $visContent = Visibility::SHOW_CONTENT;

        return (($cf->getVisibility() !== $invis && $cf->getVisibility() !== $visContent)
            || ($mf->getVisibility() !== $invis && $mf->getVisibility() !== $visContent)
            || ($prf->getVisibility() !== $invis && $prf->getVisibility() !== $visContent)
            || ($rf->getVisibility() !== $invis && $rf->getVisibility() !== $visContent)
            || ($afc->getVisibility() !== $invis && $afc->getVisibility() !== $visContent)
            || ($ssf->getVisibility() !== $invis && $ssf->getVisibility() !== $visContent)
            || ($sf->getVisibility() !== $invis && $sf->getVisibility() !== $visContent)
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
     * @param int $pageType
     * @return int
     */
    private function getCurrentPageID(int $pageType): int
    {
        $pageID = 0;
        if ($pageType === \PAGE_ARTIKELLISTE) {
            $pageID = (int)Shop::$kKategorie;
        } elseif ($pageType === \PAGE_ARTIKEL) {
            $pageID = (int)Shop::$kArtikel;
        } elseif ($pageType === \PAGE_EIGENE) {
            $pageID = (int)Shop::$kLink;
        } elseif ($pageType === \PAGE_HERSTELLER) {
            $pageID = (int)Shop::$kHersteller;
        }

        return $pageID;
    }

    /**
     * @param array $positionedBoxes
     * @param int   $pageType
     * @return array
     * @throws \Exception
     * @throws \SmartyException
     */
    public function render(array $positionedBoxes, int $pageType): array
    {
        $pageID    = $this->getCurrentPageID($pageType);
        $product   = $this->smarty->getTemplateVars('Artikel');
        $htmlArray = [
            'top'    => null,
            'right'  => null,
            'bottom' => null,
            'left'   => null
        ];
        $this->smarty->assign('BoxenEinstellungen', $this->config)
                     ->assign('bBoxenFilterNach', $this->showBoxes(Shop::getProductFilter()))
                     ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant());
        foreach ($positionedBoxes as $_position => $boxes) {
            if (!\is_array($boxes)) {
                $boxes = [];
            }
            $htmlArray[$_position]     = '';
            $this->rawData[$_position] = [];
            foreach ($boxes as $box) {
                /** @var BoxInterface $box */
                $renderClass = $box->getRenderer();
                if ($renderClass !== \get_class($this->renderer)) {
                    $this->renderer = new $renderClass($this->smarty);
                }
                $this->renderer->setBox($box);
                $html = \trim($this->renderer->render($pageType, $pageID));
                $box->setRenderedContent($html);
                $htmlArray[$_position]      .= $html;
                $this->rawData[$_position][] = [
                    'obj' => $box,
                    'tpl' => $box->getTemplateFile()
                ];
            }
        }
        $this->smarty->clearAssign('BoxenEinstellungen');
        // avoid modification of article object on render loop
        if ($product !== null) {
            $this->smarty->assign('Artikel', $product);
        }

        return $htmlArray;
    }

    /**
     * @param int  $pageType
     * @param bool $active
     * @param bool $visible
     * @return array
     */
    public function buildList(int $pageType = 0, bool $active = true, bool $visible = false): array
    {
        $boxAdmin          = new BoxAdmin(Shop::Container()->getDB());
        $validPages        = implode(',', $boxAdmin->getValidPageTypes());
        $cacheID           = 'bx_' . $pageType .
            '_' . (int)$active .
            '_' . (int)$visible .
            '_' . Shop::getLanguageID();
        $visiblePositions  = [];
        $templatePositions = Template::getInstance()->getBoxLayoutXML();
        $this->getVisibility($pageType);
        foreach ($this->visibility as $position => $isVisible) {
            if (isset($templatePositions[$position])) {
                $isVisible = $isVisible && $templatePositions[$position];
            }
            if ($isVisible) {
                $visiblePositions[] = $position;
            }
        }
        if ($active === true && \count($visiblePositions) === 0) {
            return [];
        }
        $visiblePositions = map($visiblePositions, function ($e) {
            return "'" . $e . "'";
        });
        $activeSQL        = $active
            ? ' AND tboxen.ePosition IN (' . \implode(',', $visiblePositions) . ')'
            : '';
        $plgnSQL          = $active
            ? ' AND (tplugin.nStatus IS NULL OR tplugin.nStatus = ' .
            State::ACTIVATED . "  OR (tboxvorlage.eTyp != '" . Type::PLUGIN .
            "' AND tboxvorlage.eTyp != '" . Type::EXTENSION . "'))"
            : '';
        if (($grouped = $this->cache->get($cacheID)) === false) {
            $boxData = $this->db->query(
                'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.kContainer, 
                       tboxen.cTitel, tboxen.ePosition, tboxensichtbar.kSeite, tboxensichtbar.nSort, 
                       tboxensichtbar.cFilter, tboxvorlage.eTyp, 
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
                    WHERE tboxen.kContainer > -1 AND FIND_IN_SET(tboxensichtbar.kSeite, "' . $validPages . '") > 0 ' . $activeSQL . $plgnSQL . ' 
                    GROUP BY tboxsprache.kBoxSprache, tboxen.kBox, tboxensichtbar.cFilter
                    ORDER BY tboxensichtbar.nSort, tboxen.kBox ASC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $grouped = group($boxData, function ($e) {
                return (int)$e->kBox;
            });
            $this->cache->set($cacheID, $grouped, [\CACHING_GROUP_OBJECT, \CACHING_GROUP_BOX, 'boxes']);
        }

        return $this->getItems($grouped);
    }

    /**
     * @param array $grouped
     * @return array
     */
    private function getItems(array $grouped): array
    {
        $children = [];
        $result   = [];
        foreach ($grouped as $i => $boxes) {
            $first = first($boxes);
            if ((int)$first->kContainer > 0) {
                $box = $this->factory->getBoxByBaseType($first->kBoxvorlage, $first->eTyp);
                $box->map($boxes);
                if (!isset($children[(int)$first->kContainer])) {
                    $children[(int)$first->kContainer] = [];
                }
                $children[(int)$first->kContainer][] = $box;
                unset($grouped[$i]);
            }
        }
        foreach ($grouped as $boxes) {
            $first = first($boxes);
            $box   = $this->factory->getBoxByBaseType($first->kBoxvorlage, $first->eTyp);
            $box->map($boxes);
            $class = \get_class($box);
            if ($class === LegacyPlugin::class) {
                $plugin = new LegacyPlugin($box->getCustomID());
                $box->setTemplateFile(
                    $plugin->getPaths()->getFrontendPath() .
                    \PFAD_PLUGIN_BOXEN .
                    $box->getTemplateFile()
                );
                $box->setPlugin($plugin);
            } elseif ($class === Plugin::class) {
                $loader = new PluginLoader($this->db, $this->cache);
                $plugin = $loader->init($box->getCustomID());
                $box->setTemplateFile(
                    $plugin->getPaths()->getFrontendPath() .
                    $box->getTemplateFile()
                );
                $box->setExtension($plugin);
            } elseif ($box->getType() === Type::CONTAINER) {
                $box->setChildren($children);
            }
            $result[] = $box;
        }
        $this->boxes = group($result, function (BoxInterface $e) {
            return $e->getPosition();
        });

        return $this->boxes;
    }
}
