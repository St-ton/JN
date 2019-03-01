<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter\Items;

use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\ProductFilter;
use JTL\Filter\States\BaseTag;
use JTL\Filter\Type;

/**
 * Class Tag
 * @package JTL\Filter\Items
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
            (new Join())
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
                ->setOrigin(__CLASS__),
            (new Join())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setOrigin(__CLASS__)
        ];
    }
}
