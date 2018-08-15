<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;


use Filter\AbstractFilter;
use Filter\Option;
use Filter\FilterInterface;
use Filter\ProductFilter;

/**
 * Class Limit
 * @package Filter\Items
 */
class Limit extends AbstractFilter
{
    /**
     * Limit constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('af')
             ->setFrontendName(\Shop::Lang()->get('productsPerPage', 'productOverview'));
    }

    /**
     * @inheritdoc
     */
    public function getProductsPerPageLimit(): int
    {
        if ($this->productFilter->getProductLimit() !== 0) {
            $limit = $this->productFilter->getProductLimit();
        } elseif (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] !== 0) {
            $limit = $_SESSION['ArtikelProSeite'];
        } elseif (isset($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel)
            && $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel !== 0
        ) {
            $limit = $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = ($max = $this->getConfig('artikeluebersicht')['artikeluebersicht_artikelproseite']) !== 0
                ? $max
                : 20;
        }

        return \min((int)$limit, ARTICLES_PER_PAGE_HARD_LIMIT);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        $params           = $this->productFilter->getParams();
        $view             = $this->productFilter->getMetaData()->getExtendedView($params['nDarstellung'])->nDarstellung;
        $optionIdx        = $view === \ERWDARSTELLUNG_ANSICHT_LISTE
            ? 'products_per_page_list'
            : 'products_per_page_gallery';
        $limitOptions     = \explode(',', $this->getConfig('artikeluebersicht')[$optionIdx]);
        $activeValue      = $_SESSION['ArtikelProSeite'] ?? $this->getProductsPerPageLimit();
        foreach ($limitOptions as $i => $limitOption) {
            $limitOption = (int)\trim($limitOption);
            $name        = $limitOption > 0 ? $limitOption : \Shop::Lang()->get('showAll');
            $options[]   = (new Option())
                ->setIsActive($activeValue === $limitOption)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init($limitOption)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($name)
                ->setValue($limitOption)
                ->setSort($i);
        }
        $this->options = $options;

        return $options;
    }
}
