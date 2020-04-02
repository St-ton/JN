<?php

namespace JTL\Catalog\Product;

use Countable;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class Bestseller
 * @package JTL\Catalog\Product
 */
class Bestseller
{
    /**
     * @var array
     */
    protected $products = [];

    /**
     * @var int
     */
    protected $customergrp;

    /**
     * @var int
     */
    protected $limit = 3;

    /**
     * @var int
     */
    protected $minsales = 10;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (\is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = \get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);
            if (\in_array($method, $methods, true) && \method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomergroup()
    {
        return $this->customergrp;
    }

    /**
     * @param int $customergroup
     * @return $this
     */
    public function setCustomergroup(int $customergroup): self
    {
        $this->customergrp = $customergroup;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinSales(): int
    {
        return $this->minsales;
    }

    /**
     * @param int $minsales
     * @return $this
     */
    public function setMinSales(int $minsales): self
    {
        $this->minsales = $minsales;

        return $this;
    }

    /**
     * @return array
     */
    public function fetch(): array
    {
        $products = [];
        if ($this->customergrp !== null) {
            $productsql = GeneralObject::hasCount($this->products)
                ? ' AND tartikel.kArtikel IN (' . \implode(',', \array_map('\intval', $this->products)) . ') '
                : '';
            $storagesql = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $data       = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tartikel
                    JOIN tbestseller
                        ON tbestseller.kArtikel = tartikel.kArtikel
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->customergrp . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND ROUND(tbestseller.fAnzahl) >= ' . $this->minsales . ' ' . $storagesql .  $productsql . '
                    GROUP BY tartikel.kArtikel
                    ORDER BY tbestseller.fAnzahl DESC
                    LIMIT ' . $this->limit,
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $item) {
                $products[] = (int)$item->kArtikel;
            }
        }

        return $products;
    }

    /**
     * @param Countable $products
     * @param int       $customergrp
     * @param bool      $viewallowed
     * @param bool      $onlykeys
     * @param int       $limit
     * @param int       $minsells
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
        if ($viewallowed && \count($products) > 0) {
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
                if ($product !== null && (int)$product->kArtikel > 0) {
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
        if (\is_array($products) && \is_array($bestsellers) && \count($products) > 0 && \count($bestsellers) > 0) {
            foreach ($products as $i => $product) {
                if (\count($products) === 1) {
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
