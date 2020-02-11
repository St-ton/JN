<?php
/**
 * Groups configuration for default Minify implementation
 *
 * @package Minify
 */

$isAdmin  = isset($_GET['g']) && ($_GET['g'] === 'admin_js' || $_GET['g'] === 'admin_css');
$template = $isAdmin ? JTL\Backend\AdminTemplate::getInstance() : JTL\Template::getInstance();

return $template->getMinifyArray(true);
