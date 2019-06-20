<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Helpers\Category;
use JTL\Session\Frontend;

/**
 * Class ProductCategories
 * @package JTL\Boxes\Items
 */
final class ProductCategories extends AbstractBox
{
    /**
     * ProductCategories constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $show = isset($config['global']['global_sichtbarkeit'])
            && ((int)$config['global']['global_sichtbarkeit'] !== 3 || Frontend::getCustomer()->getID() > 0);
        $this->setShow($show);
        if ($show === true) {
            $categories = $this->getCategories();
            $this->setItems($categories);
            $this->setShow(\count($categories) > 0);
        }
    }

    /**
     * @return array
     */
    private function getCategories(): array
    {
        $categories = Category::getInstance();
        $list       = $categories->combinedGetAll();
        $boxID      = $this->getCustomID();
        if ($boxID > 0) {
            $list2 = [];
            foreach ($list as $key => $item) {
                if (isset($item->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX])
                    && (int)$item->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX] === $boxID
                ) {
                    $list2[$key] = $item;
                }
            }
            $list = $list2;
        }

        return $list;
    }
}
