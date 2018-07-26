<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\FilterJoin;
use Filter\FilterInterface;
use Filter\Type;
use Filter\ProductFilter;
use Filter\States\BaseTag;

/**
 * Class Tag
 * @package Filter\Items
 */
class Tag extends BaseTag
{
    /**
     * Tag constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setUrlParam('tf')
             ->setVisibility($this->getConfig('navigationsfilter')['allgemein_tagfilter_benutzen'])
             ->setType($this->getConfig('navigationsfilter')['tag_filter_type'] === 'O'
                 ? Type::OR
                 : Type::AND);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = \is_array($value) ? $value : (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttagartikel';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
                ->setOrigin(__CLASS__),
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setOrigin(__CLASS__)
        ];
    }
}
