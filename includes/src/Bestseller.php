<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Bestseller
 */
class Bestseller
{
    /**
     * @var array
     */
    protected $_products;

    /**
     * @var int
     */
    protected $_customergrp;

    /**
     * @var int
     */
    protected $_limit = 3;

    /**
     * @var int
     */
    protected $_minsales = 10;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true) && method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * @param array $products
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->_products = $products;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomergroup()
    {
        return $this->_customergrp;
    }

    /**
     * @param int $customergroup
     * @return $this
     */
    public function setCustomergroup(int $customergroup): self
    {
        $this->_customergrp = $customergroup;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->_limit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->_limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinSales(): int
    {
        return $this->_minsales;
    }

    /**
     * @param int $minsales
     * @return $this
     */
    public function setMinSales(int $minsales): self
    {
        $this->_minsales = $minsales;

        return $this;
    }

    /**
     * @return array
     */
    public function fetch(): array
    {
        $products = [];
        if ($this->_customergrp !== null) {
            // Product SQL
            $productsql = '';
            if ($this->_products !== null && is_array($this->_products) && count($this->_products) > 0) {
                $productsql = ' AND tartikel.kArtikel IN (';
                foreach ($this->_products as $i => $product) {
                    if ($i > 0) {
                        $productsql .= ", {$product}";
                    } else {
                        $productsql .= $product;
                    }
                }
                $productsql .= ')';
            }
            // Storage SQL
            $storagesql = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $obj_arr    = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel
                    FROM tartikel
                    JOIN tbestseller
                        ON tbestseller.kArtikel = tartikel.kArtikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = {$this->_customergrp}
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND round(tbestseller.fAnzahl) >= {$this->_minsales}
                        {$storagesql}
                        {$productsql}
                    GROUP BY tartikel.kArtikel
                    ORDER BY tbestseller.fAnzahl DESC
                    LIMIT {$this->_limit}",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($obj_arr as $obj) {
                $products[] = $obj->kArtikel;
            }
        }

        return $products;
    }

    /**
     * @param iterable $products
     * @param int      $customergrp
     * @param bool     $viewallowed
     * @param bool     $onlykeys
     * @param int      $limit
     * @param int      $minsells
     * @return array
     */
    public static function buildBestsellers(
        $products,
        int $customergrp,
        bool $viewallowed = true,
        bool $onlykeys = true,
        int $limit = 3,
        int $minsells = 10
    ): array {
        if ($viewallowed && count($products) > 0) {
            $options    = [
                'Products'      => $products,
                'Customergroup' => $customergrp,
                'Limit'         => $limit,
                'MinSales'      => $minsells
            ];
            $bestseller = new self($options);
            if ($onlykeys) {
                return $bestseller->fetch();
            }
            $bestsellerkeys = $bestseller->fetch();
            $bestsellers    = [];
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($bestsellerkeys as $bestsellerkey) {
                $product = (new Artikel())->fuelleArtikel($bestsellerkey, $defaultOptions);
                if ($product !== null && $product->kArtikel > 0) {
                    $bestsellers[] = $product;
                }
            }

            return $bestsellers;
        }

        return [];
    }

    /**
     * @param array $products
     * @param array $bestsellers
     * @return array
     */
    public static function ignoreProducts(&$products, $bestsellers): array
    {
        $ignoredkeys = [];
        if (is_array($products) && is_array($bestsellers) && count($products) > 0 && count($bestsellers) > 0) {
            foreach ($products as $i => $product) {
                if (count($products) === 1) {
                    break;
                }
                foreach ($bestsellers as $bestseller) {
                    if ($product->kArtikel === $bestseller->kArtikel) {
                        unset($products[$i]);
                        $ignoredkeys[] = $bestseller->kArtikel;
                        break;
                    }
                }
            }
        }

        return $ignoredkeys;
    }
}
