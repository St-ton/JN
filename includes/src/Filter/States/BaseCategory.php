<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterInterface;
use Filter\Items\Category;
use Filter\ProductFilter;

/**
 * Class BaseCategory
 * @package Filter\States
 */
class BaseCategory extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kKategorie' => 'ValueCompat',
        'cName'      => 'Name'
    ];

    /**
     * @var bool
     */
    private $includeSubCategories = false;

    /**
     * BaseCategory constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('k')
             ->setUrlParamSEO(\SEP_KAT);
    }

    /**
     * @return bool
     */
    public function getIncludeSubCategories(): bool
    {
        return $this->includeSubCategories;
    }

    /**
     * @param bool $includeSubCategories
     * @return Category
     */
    public function setIncludeSubCategories($includeSubCategories): self
    {
        $this->includeSubCategories = (bool)$includeSubCategories;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->value = (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        if ($this->getValue() > 0) {
            $oSeo_arr = $this->productFilter->getDB()->queryPrepared(
                "SELECT tseo.cSeo, tseo.kSprache, tkategorie.cName AS cKatName, tkategoriesprache.cName
                    FROM tseo
                        LEFT JOIN tkategorie
                            ON tkategorie.kKategorie = tseo.kKey
                        LEFT JOIN tkategoriesprache
                            ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                            AND tkategoriesprache.kSprache = tseo.kSprache
                    WHERE cKey = 'kKategorie' 
                        AND kKey = :val
                    ORDER BY tseo.kSprache",
                ['val' => $this->getValue()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache === (int)$oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
            foreach ($oSeo_arr as $item) {
                if ((int)$item->kSprache === \Shop::getLanguage()) {
                    if (!empty($item->cName)) {
                        $this->setName($item->cName);
                    } elseif (!empty($item->cKatName)) {
                        $this->setName($item->cKatName);
                    }
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kKategorie';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkategorie';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return $this->getIncludeSubCategories() === true
            ? ' tkategorieartikel.kKategorie IN (
                        SELECT tchild.kKategorie FROM tkategorie AS tparent
                            JOIN tkategorie AS tchild
                                ON tchild.lft BETWEEN tparent.lft AND tparent.rght
                                WHERE tparent.kKategorie = ' . $this->getValue() . ')'
            : 'tkategorieartikel.kKategorie = ' . $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())
            ->setType('JOIN')
            ->setOrigin(__CLASS__)
            ->setTable('tkategorieartikel')
            ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel')
            ->setComment('JOIN from ' . __METHOD__);
    }
}
