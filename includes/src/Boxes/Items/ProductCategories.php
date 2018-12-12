<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Session\Session;

/**
 * Class ProductCategories
 * @package Boxes\Items
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
            && ((int)$config['global']['global_sichtbarkeit'] !== 3 || Session::getCustomer()->getID() > 0);
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
        $categories = \Helpers\KategorieHelper::getInstance();
        $list       = $categories->combinedGetAll();
        $boxID      = $this->getCustomID();
        if ($boxID > 0) {
            $list2 = [];
            foreach ($list as $key => $oList) {
                if (isset($oList->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX])
                    && (int)$oList->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX] === $boxID
                ) {
                    $list2[$key] = $oList;
                }
            }
            $list = $list2;
        }

        return $list;
    }
}
