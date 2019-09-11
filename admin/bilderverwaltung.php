<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

use JTL\Media\Image;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bilderverwaltung_inc.php';

$oAccount->permission('DISPLAY_IMAGES_VIEW', true, true);

$smarty->configLoad('german.conf', 'bilderverwaltung')
       ->assign('items', getItems())
       ->assign('corruptedImagesByType', getCorruptedImages(Image::TYPE_PRODUCT, 50))
       ->display('bilderverwaltung.tpl');
