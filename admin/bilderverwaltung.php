<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

use JTL\Media\Image;
use JTL\Media\Manager;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_IMAGES_VIEW', true, true);
Shop::Container()->getGetText()->loadAdminLocale('pages/bilderverwaltung');
$manager = new Manager();

$smarty->configLoad('german.conf', 'bilderverwaltung')
       ->assign('items', $manager->getItems())
       ->assign('corruptedImagesByType', $manager->getCorruptedImages(Image::TYPE_PRODUCT, 50))
       ->display('bilderverwaltung.tpl');
