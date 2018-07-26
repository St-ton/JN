<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Boxes\Renderer\DefaultRenderer;
use function Functional\false;
use function Functional\first;

/**
 * Class AbstractBox
 * @package Boxes
 */
abstract class AbstractBox implements BoxInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'compatName'     => 'Name',
        'cName'          => 'Name',
        'anzeigen'       => 'ShowCompat',
        'kBox'           => 'ID',
        'kBoxvorlage'    => 'BaseType',
        'nSort'          => 'Sort',
        'eTyp'           => 'Type',
        'cTitel'         => 'Title',
        'cInhalt'        => 'Content',
        'nAnzeigen'      => 'ItemCount',
        'cURL'           => 'URL',
        'Artikel'        => 'Products',
        'oArtikel_arr'   => 'Products',
        'oContainer_arr' => 'Children',
        'bContainer'     => 'ContainerCheckCompat',
        'bAktiv'         => 'IsActive',
        'kContainer'     => 'ContainerID',
        'cFilter'        => 'Filter',
        'oPlugin'        => 'Plugin',
    ];

    /**
     * @var array
     */
    private static $validPageTypes = [
        PAGE_UNBEKANNT,
        PAGE_ARTIKEL,
        PAGE_ARTIKELLISTE,
        PAGE_WARENKORB,
        PAGE_MEINKONTO,
        PAGE_KONTAKT,
        PAGE_UMFRAGE,
        PAGE_NEWS,
        PAGE_NEWSLETTER,
        PAGE_LOGIN,
        PAGE_REGISTRIERUNG,
        PAGE_BESTELLVORGANG,
        PAGE_BEWERTUNG,
        PAGE_DRUCKANSICHT,
        PAGE_PASSWORTVERGESSEN,
        PAGE_WARTUNG,
        PAGE_WUNSCHLISTE,
        PAGE_VERGLEICHSLISTE,
        PAGE_STARTSEITE,
        PAGE_VERSAND,
        PAGE_AGB,
        PAGE_DATENSCHUTZ,
        PAGE_TAGGING,
        PAGE_LIVESUCHE,
        PAGE_HERSTELLER,
        PAGE_SITEMAP,
        PAGE_GRATISGESCHENK,
        PAGE_WRB,
        PAGE_PLUGIN,
        PAGE_NEWSLETTERARCHIV,
        PAGE_NEWSARCHIV,
        PAGE_EIGENE,
        PAGE_AUSWAHLASSISTENT,
        PAGE_BESTELLABSCHLUSS,
        PAGE_RMA
    ];

    /**
     * @var int
     */
    protected $itemCount = 0;

    /**
     * @var bool
     */
    protected $show;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $templateFile = '';

    /**
     * @var \Plugin|null
     */
    protected $plugin;

    /**
     * @var int
     */
    protected $containerID = 0;

    /**
     * @var string
     */
    protected $position;

    /**
     * @var string|array
     */
    protected $title;

    /**
     * @var string|array
     */
    protected $content;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $baseType = 0;

    /**
     * @var int
     */
    protected $customID = 0;

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @var \Artikel[]
     */
    protected $products;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string|null
     */
    protected $json;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $renderedContent = '';

    /**
     * @var bool
     */
    protected $supportsRevisions = false;

    /**
     * @var array
     */
    protected $filter;

    /**
     * @var array
     */
    protected $config;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getRenderer(): string
    {
        return DefaultRenderer::class;
    }

    /**
     * @param string $attrbute
     * @param string $method
     */
    public function addMapping(string $attrbute, string $method)
    {
        self::$mapping[$attrbute] = $method;
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData)
    {
        $data = first($boxData);
        if ($data->eTyp === null) {
            // containers do not have a lot of data..
            $data->eTyp      = BoxType::CONTAINER;
            $data->cTitel    = '';
            $data->cTemplate = 'box_container.tpl';
            $data->cName     = '';
        }
        $this->setID($data->kBox);
        $this->setBaseType((int)$data->kBoxvorlage);
        $this->setCustomID((int)$data->kCustomID);
        $this->setContainerID((int)$data->kContainer);
        $this->setSort((int)$data->nSort);
        $this->setIsActive(true);
        if ($this->products === null) {
            $this->products = new \ArtikelListe();
        }
        if (!empty($data->kSprache)) {
            $this->setTitle([]);
            $this->setContent([]);
        } else {
            $this->setTitle(!empty($data->cTitel) ? $data->cTitel : $data->cName);
            $this->setContent('');
        }
        $this->setPosition($data->ePosition);
        $this->setType($data->eTyp);

        if ($this->getType() !== BoxType::PLUGIN) {
            $data->cTemplate = 'boxes/' . $data->cTemplate;
        }
        $this->setTemplateFile($data->cTemplate);
        $this->setName($data->cName);

        foreach (self::$validPageTypes as $pageType) {
            $this->filter[$pageType] = false;
        }

        foreach ($boxData as $box) {
            if (!empty($box->cFilter)) {
                $this->filter[(int)$box->kSeite] = \array_map('\intval', \explode(',', $box->cFilter));
            } else {
                $pageIDs          = \explode(',', $box->pageIDs);
                $pageVisibilities = \explode(',', $box->pageVisibilities);
                $filter           = \array_combine($pageIDs, $pageVisibilities);
                foreach ($filter as $pageID => $visibility) {
                    $this->filter[(int)$pageID] = (bool)$visibility;
                }
            }
            if (!empty($box->kSprache)) {
                $this->content[(int)$box->kSprache] = $box->cInhalt;
                $this->title[(int)$box->kSprache]   = $box->cTitel;
            }
        }
        \ksort($this->filter);

        if (false($this->filter)) {
            $this->setIsActive(false);
        }
        if (!\is_bool($this->show)) {
            // may be overridden in concrete classes' __construct
            $this->setShow($this->isActive());
        }
    }

    /**
     * @param int $pageID
     * @return bool|array
     */
    public function isVisibleOnPage(int $pageID)
    {
        return $this->filter[$pageID] ?? false;
    }

    /**
     * @inheritdoc
     */
    public function isBoxVisible(int $pageType = 0, int $pageID = 0): bool
    {
        if ($this->show === false) {
            return false;
        }
        $visible = empty($this->filter) || (isset($this->filter[$pageType]) && $this->filter[$pageType] === true);

        if ($visible === false && $pageID > 0 && isset($this->filter[$pageType]) && \is_array($this->filter[$pageType])) {
            $visible = \in_array($pageID, $this->filter[$pageType], true);
        }

        return $visible;
    }

    /**
     * @return bool
     */
    public function show(): bool
    {
        return $this->show;
    }

    /**
     * @return bool
     */
    public function getShow(): bool
    {
        return $this->show;
    }

    /**
     * @param bool $show
     */
    public function setShow(bool $show)
    {
        $this->show = $show;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile)
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @return null|\Plugin
     */
    public function getPlugin(): \Plugin
    {
        return $this->plugin;
    }

    /**
     * @param null|\Plugin $plugin
     */
    public function setPlugin(\Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return int
     */
    public function getContainerID(): int
    {
        return $this->containerID;
    }

    /**
     * @param int $containerID
     */
    public function setContainerID(int $containerID)
    {
        $this->containerID = $containerID;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position)
    {
        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function getTitle($idx = null): string
    {
        if (\is_string($this->title)) {
            return $this->title;
        }
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->title[$idx] ?? '';
    }

    /**
     * @param string|array $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getContent($idx = null): string
    {
        if (\is_string($this->content)) {
            return $this->content;
        }
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->content[$idx] ?? '';
    }

    /**
     * @param string|array $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getBaseType(): int
    {
        return $this->baseType;
    }

    /**
     * @param int $type
     */
    public function setBaseType(int $type)
    {
        $this->baseType = $type;
    }

    /**
     * @inheritdoc
     */
    public function getCustomID(): int
    {
        return $this->customID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomID(int $id)
    {
        $this->customID = $id;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @param int $count
     */
    public function setItemCount(int $count)
    {
        $this->itemCount = $count;
    }

    /**
     * @return bool
     */
    public function supportsRevisions(): bool
    {
        return $this->supportsRevisions;
    }

    /**
     * @param bool $supportsRevisions
     */
    public function setSupportsRevisions(bool $supportsRevisions)
    {
        $this->supportsRevisions = $supportsRevisions;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getShowCompat(): string
    {
        return $this->show === true ? 'Y' : 'N';
    }

    /**
     * @param string $show
     */
    public function setShowCompat(string $show)
    {
        $this->show = $show === 'Y';
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getFilter(int $idx = null)
    {
        return $idx === null ? $this->filter : $this->filter[$idx] ?? true;
    }

    /**
     * @inheritdoc
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getJSON(): string
    {
        return $this->json;
    }

    /**
     * @inheritdoc
     */
    public function setJSON(string $json)
    {
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function setChildren(array $chilren)
    {
        $this->children = $chilren[$this->getID()] ?? [];
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHTML(string $html)
    {
        $this->html = $html;
    }

    /**
     * @inheritdoc
     */
    public function getRenderedContent(): string
    {
        return $this->renderedContent;
    }

    /**
     * @inheritdoc
     */
    public function setRenderedContent(string $renderedContent)
    {
        $this->renderedContent = $renderedContent;
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $oCloud_arr
     * @param string $nSpeed
     * @param string $nOpacity
     * @param bool   $cColor
     * @param bool   $cColorHover
     * @return string
     */
    public static function getJSONString(
        $oCloud_arr,
        $nSpeed = '1',
        $nOpacity = '0.2',
        $cColor = false,
        $cColorHover = false
    ): string {
        $iCur = 0;
        $iMax = 15;
        if (!\count($oCloud_arr)) {
            return '';
        }
        $oTags_arr                       = [];
        $oTags_arr['options']['speed']   = $nSpeed;
        $oTags_arr['options']['opacity'] = $nOpacity;
        $gibTagFarbe                     = function () {
            $cColor = '';
            $cCodes = ['00', '33', '66', '99', 'CC', 'FF'];
            for ($i = 0; $i < 3; $i++) {
                $cColor .= $cCodes[\rand(0, \count($cCodes) - 1)];
            }

            return '0x' . $cColor;
        };

        foreach ($oCloud_arr as $oCloud) {
            if ($iCur++ >= $iMax) {
                break;
            }
            $cName               = $oCloud->cName ?? $oCloud->cSuche;
            $cRandomColor        = (!$cColor || !$cColorHover) ? $gibTagFarbe() : '';
            $cName               = \urlencode($cName);
            $cName               = \str_replace('+', ' ', $cName); /* fix :) */
            $oTags_arr['tags'][] = [
                'name'  => $cName,
                'url'   => $oCloud->cURL,
                'size'  => (\count($oCloud_arr) <= 5) ? '100' : (string)($oCloud->Klasse * 10), /* 10 bis 100 */
                'color' => $cColor ?: $cRandomColor,
                'hover' => $cColorHover ?: $cRandomColor
            ];
        }

        return \urlencode(\json_encode($oTags_arr));
    }

    /**
     * @return int
     */
    public function getContainerCheckCompat(): int
    {
        return $this->getBaseType() === BOX_CONTAINER ? 1 : 0;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res           = \get_object_vars($this);
        $res['config'] = '*truncated*';

        return $res;
    }
}
