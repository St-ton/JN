<?php
/**
 * Groups configuration for default Minify implementation
 *
 * @package Minify
 */

use JTL\Backend\AdminTemplate;
use JTL\Shop;
use JTL\Template\TemplateServiceInterface;

$isAdmin = isset($_GET['g']) && ($_GET['g'] === 'admin_js' || $_GET['g'] === 'admin_css');
if ($isAdmin) {
    return AdminTemplate::getInstance()->getMinifyArray(true);
}
$resources = Shop::Container()->get(TemplateServiceInterface::class)->getActiveTemplate()->getResources();
$resources->init();

return $resources->getMinifyArray(true);
