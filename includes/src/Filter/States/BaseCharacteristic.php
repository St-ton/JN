<?php declare(strict_types=1);

namespace JTL\Filter\States;

use JTL\Catalog\Product\MerkmalWert;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\ProductFilter;
use JTL\MagicCompatibilityTrait;

/**
 * Class BaseCharacteristic
 * @package JTL\Filter\States
 */
class BaseCharacteristic extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static array $mapping = [
        'kMerkmal'     => 'CharacteristicIDCompat',
        'kMerkmalWert' => 'ValueCompat',
        'cName'        => 'Name'
    ];

    /**
     * BaseCharacteristic constructor.
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
        $currentLanguageID   = $this->getLanguageID();
        $characteristicValue = new MerkmalWert($this->getValue(), $currentLanguageID);
        foreach ($languages as $language) {
            $id              = $language->getId();
            $this->cSeo[$id] = \ltrim($characteristicValue->getURLPath($id), '/');
        }
        if (\mb_strlen($characteristicValue->getValue()) > 0) {
            $this->setName($characteristicValue->getCharacteristicName() . ': ' . $characteristicValue->getValue());
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
