<?php
/**
 * Groups configuration for default Minify implementation
 *
 * @package Minify
 */

$isAdmin   = isset($_GET['g']) && ($_GET['g'] === 'admin_js' || $_GET['g'] === 'admin_css');
$oTemplate = $isAdmin ? AdminTemplate::getInstance() : Template::getInstance();

return $oTemplate->getMinifyArray(true);
