<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

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
        'compatName'   => 'Name',
        'anzeigen'     => 'ShowCompat',
        'kBox'         => 'ID',
        'kBoxvorlage'  => 'BaseType',
        'nSort'        => 'Sort',
        'cTitel'       => 'Title',
        'cInhalt'      => 'Content',
        'nAnzeigen'    => 'ItemCount',
        'cURL'         => 'URL',
        'Artikel'      => 'Products',
        'oArtikel_arr' => 'Products',
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
    protected $isActive = false;

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
     * AbstractBox constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
        $multilang = false;
        $data      = first($boxData);
        if ($data->eTyp === null) {
            // containers do not have a lot of data..
            $data->eTyp      = BoxType::CONTAINER;
            $data->cTitel    = '';
            $data->cTemplate = 'box_container.tpl';
            $data->cName     = '';
        }
        $this->setID($data->kBox);
        $this->setBaseType($data->kBoxvorlage);
        $this->setCustomID($data->kCustomID);
        $this->setContainerID($data->kContainer);
        $this->setSort($data->nSort);
        $this->setIsActive((int)$data->bAktiv === 1);
        if (!is_bool($this->show)) {
            // may be overridden in concrete classes' __construct
            $this->setShow($this->isActive());
        }
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

        foreach ($boxData as $box) {
            if (!empty($box->cFilter)) {
                $this->filter[$box->visiblePages] = array_map('intval', explode(',', $box->cFilter));
            } else {
                $filter = explode(',', $box->visiblePages);
                foreach ($filter as $item) {
                    $this->filter[$item] = true;
                }
            }
            if (!empty($box->kSprache)) {
                $this->content[(int)$box->kSprache] = $box->cInhalt;
                $this->title[(int)$box->kSprache]   = $box->cTitel;
            }

        }
        ksort($this->filter);
    }

    /**
     * @inheritdoc
     */
    public function isBoxVisible(int $pageType = 0, int $pageID = 0): bool
    {
        if (!$this->isActive || !$this->show) {
            return false;
        }
        $visible = empty($this->filter)
            || ($pageType > 0 && isset($this->filter[$pageType])
                && $this->filter[$pageType] === true);

        if ($visible === false && $pageID > 0 && isset($this->filter[$pageType]) && is_array($this->filter[$pageType])) {
            $visible = in_array($pageID, $this->filter[$pageType], true);
        }

        return $visible;
    }

    /**
     * @inheritdoc
     */
    public function render($smarty, int $pageType = 0, int $pageID = 0): string
    {
        $smarty->assign('oBox', $this);

        try {
            return $this->getTemplateFile() !== '' && $this->isBoxVisible($pageType, $pageID)
                ? $smarty->fetch($this->getTemplateFile())
                : '';
        } catch (\SmartyException $e) {
            return $this->getTemplateFile();
        }
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
        if (is_string($this->title)) {
            return $this->title;
        }
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->title[$idx];
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
        if (is_string($this->content)) {
            return $this->content;
        }
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->content[$idx];
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
    public function getFilter(): array
    {
        return $this->filter;
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
     * @return array
     */
    public function __debugInfo()
    {
        $res           = get_object_vars($this);
        $res['config'] = '*truncated*';
        $res['filter'] = '*truncated*';

        return $res;
    }
}
