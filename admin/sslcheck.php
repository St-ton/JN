<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$hasPermission = $oAccount->permission('SHOP_UPDATE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sslcheck_inc.php';

use JMS\Serializer\SerializerBuilder;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

$smarty->assign('data', sslcheckGetData())
       ->display('sslcheck.tpl');
