<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sitemapexport.php';
if (!ini_get('safe_mode')) {
    @ini_set('max_execution_time', 0);
}

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);

generateSitemapXML();

if ($_REQUEST['update'] === '1') {
    header('Location: sitemapexport.php?update=1');
} else {
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/xml');
    header('Content-Disposition: attachment; filename="sitemap_index.xml"');
    readfile(PFAD_ROOT . 'sitemap.xml');
}
