<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;


use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\Items\Tag;
use Filter\Join;
use Filter\Option;
use Filter\ProductFilter;
use Filter\StateSQL;
use Filter\Type;

/**
 * Class BaseTag
 * @package Filter\States
 */
class BaseTag extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kTag'  => 'ValueCompat',
        'cName' => 'Name'
    ];

    /**
     * BaseTag constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setFrontendName(\Shop::Lang()->get('tags'))
             ->setIsCustom(false)
             ->setUrlParam('t');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        return parent::setValue((int)$value);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $oSeo_obj = $this->productFilter->getDB()->queryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, ttag.cName
                FROM tseo
                LEFT JOIN ttag
                    ON tseo.kKey = ttag.kTag
                WHERE tseo.cKey = 'kTag' 
                    AND tseo.kKey = :val",
            ['val' => $this->getValue()],
            ReturnType::SINGLE_OBJECT
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === (int)$oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cName)) {
            $this->setName($oSeo_obj->cName);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kTag';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttag';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return 'ttag.nAktiv = 1 AND ttagartikel.kTag = ' . $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [
            (new Join())
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
                ->setComment('JOIN1 from ' . __METHOD__)
                ->setOrigin(__CLASS__),
            (new Join())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setComment('JOIN2 from ' . __METHOD__)
                ->setOrigin(__CLASS__)
        ];
    }

    /**
     * @param null $data
     * @return Option[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig('navigationsfilter')['allgemein_tagfilter_benutzen'] === 'N') {
            return $options;
        }
        $state = $this->productFilter->getCurrentStateData($this->getType() === Type:: OR
            ? $this->getClassName()
            : null
        );
        $sql   = (new StateSQL())->from($state);
        $sql->setSelect([
            'ttag.kTag',
            'ttag.cName',
            'ttagartikel.nAnzahlTagging',
            'tartikel.kArtikel'
        ]);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['ttag.kTag', 'tartikel.kArtikel']);

        $sql->addJoin((new Join())
            ->setComment('join1 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('ttagartikel')
            ->setOn('ttagartikel.kArtikel = tartikel.kArtikel')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('join2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('ttag')
            ->setOn('ttagartikel.kTag = ttag.kTag')
            ->setOrigin(__CLASS__));
        $sql->addCondition('ttag.nAktiv = 1');
        $sql->addCondition('ttag.kSprache = ' . $this->getLanguageID());
        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = 'fltr_' . __CLASS__ . \md5($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $tags             = $this->productFilter->getDB()->query(
            "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, 
                COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                    FROM (" . $baseQuery . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                    AND tseo.cKey = 'kTag'
                    AND tseo.kSprache = " . $this->getLanguageID() . "
                GROUP BY ssMerkmal.kTag
                ORDER BY nAnzahl DESC LIMIT 0, " .
            (int)$this->getConfig('navigationsfilter')['tagfilter_max_anzeige'],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $additionalFilter = new Tag($this->productFilter);
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = \count($tags);
        if ($nCount > 0) {
            $nPrioStep = ($tags[0]->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) / 9;
        }
        foreach ($tags as $tag) {
            $tag->nAnzahlTagging = (int)$tag->nAnzahlTagging;
            $class               = $nPrioStep < 1
                ? \rand(1, 10)
                : \round(
                    ($tag->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) /
                    $nPrioStep
                ) + 1;
            $options[]           = (new Option())
                ->setClass((string)$class)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$tag->kTag)
                ))
                ->setParam($this->getUrlParam())
                ->setData('nAnzahlTagging', $tag->nAnzahlTagging)
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($tag->cName)
                ->setValue((int)$tag->kTag)
                ->setCount((int)$tag->nAnzahl);
        }
        $this->options = $options;
        $this->productFilter->getCache()->set($cacheID, $options, [CACHING_GROUP_FILTER]);

        return $options;
    }
}
