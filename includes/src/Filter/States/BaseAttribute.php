<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\States;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\ProductFilter;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use JTL\Sprache;

/**
 * Class BaseAttribute
 * @package JTL\Filter\States
 */
class BaseAttribute extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kMerkmal'     => 'AttributeIDCompat',
        'kMerkmalWert' => 'ValueCompat',
        'cName'        => 'Name'
    ];

    /**
     * BaseAttribute constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('m');
    }

    /**
     * sets "kMerkmalWert"
     *
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
        $seoData = $this->productFilter->getDB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['kMerkmalWert', $this->getValue()],
            'cSeo, kSprache',
            'kSprache'
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            foreach ($seoData as $oSeo) {
                if ($language->kSprache === (int)$oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                }
            }
        }
        $select = 'tmerkmal.cName';
        $join   = '';
        if (Shop::getLanguage() > 0 && !Sprache::isDefaultLanguageActive()) {
            $select = 'tmerkmalsprache.cName, tmerkmal.cName AS cMMName';
            $join   = ' JOIN tmerkmalsprache 
                             ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                             AND tmerkmalsprache.kSprache = ' . Shop::getLanguage();
        }
        $attributeValues = $this->productFilter->getDB()->query(
            'SELECT tmerkmalwertsprache.cWert, ' . $select . '
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND kSprache = ' . Shop::getLanguage() . '
                JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                ' . $join . '
                WHERE tmerkmalwert.kMerkmalWert = ' . $this->getValue(),
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($attributeValues) > 0) {
            $attributeValue = $attributeValues[0];
            unset($attributeValues[0]);
            if (\mb_strlen($attributeValue->cWert) > 0) {
                if (!empty($this->getName())) {
                    $this->setName($attributeValue->cName . ': ' . $attributeValue->cWert);
                } elseif (!empty($attributeValue->cMMName)) {
                    $this->setName($attributeValue->cMMName . ': ' . $attributeValue->cWert);
                } elseif (!empty($attributeValue->cName)) {
                    $this->setName($attributeValue->cName . ': ' . $attributeValue->cWert);
                }
                if (\count($attributeValues) > 0) {
                    foreach ($attributeValues as $attr) {
                        if (isset($attr->cName) && \mb_strlen($attr->cName) > 0) {
                            $this->setName($this->getName() . ', ' . $attr->cName . ': ' . $attr->cWert);
                        } elseif (isset($attr->cMMName) && \mb_strlen($attr->cMMName) > 0) {
                            $this->setName($this->getName() . ', ' . $attr->cMMName . ': ' . $attr->cWert);
                        }
                    }
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
        return 'kMerkmalWert';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmerkmalwert';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new Join())
            ->setType('JOIN')
            ->setComment('JOIN from ' . __METHOD__)
            ->setTable('(SELECT kArtikel
                              FROM tartikelmerkmal
                              WHERE kMerkmalWert = ' . $this->getValue() . '
                              GROUP BY tartikelmerkmal.kArtikel
                              ) AS tmerkmaljoin')
            ->setOrigin(__CLASS__)
            ->setOn('tmerkmaljoin.kArtikel = tartikel.kArtikel');
    }
}
