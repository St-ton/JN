<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$smarty->register_function('getCurrencyConversionSmarty', 'getCurrencyConversionSmarty');
$smarty->register_function('getCurrentPage', 'getCurrentPage');
$smarty->register_function('SmartyConvertDate', 'SmartyConvertDate');
$smarty->register_function('getHelpDesc', 'getHelpDesc');
$smarty->register_function('getExtensionCategory', 'getExtensionCategory');
$smarty->register_function('formatVersion', 'formatVersion');
$smarty->register_modifier('permission', 'permission');

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getCurrencyConversionSmarty($params, &$smarty)
{
    $bForceSteuer = true;
    if (isset($params['bSteuer']) && $params['bSteuer'] == false) {
        $bForceSteuer = false;
    }
    if (!isset($params['fPreisBrutto'])) {
        $params['fPreisBrutto'] = 0;
    }
    if (!isset($params['fPreisNetto'])) {
        $params['fPreisNetto'] = 0;
    }
    if (!isset($params['cClass'])) {
        $params['cClass'] = '';
    }

    return getCurrencyConversion($params['fPreisNetto'], $params['fPreisBrutto'], $params['cClass'], $bForceSteuer);
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getCurrentPage($params, &$smarty)
{
    $pro         = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $path        = $pro . $_SERVER['REQUEST_URI'];
    $path        = preg_replace('/\\?.*/', '', $path);
    $current_url = basename($path, '.php');

    $smarty->assign($params['assign'], $current_url);
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getHelpDesc($params, &$smarty)
{
    $placement = 'left';
    if (isset($params['placement'])) {
        $placement = $params['placement'];
    }

    $button = '<button type="button" class="btn-tooltip btn btn-info btn-heading" data-html="true" data-toggle="tooltip" data-placement="' . $placement . '" title="';

    if (isset($params['cDesc'])) {
        $button .= str_replace('"', '\'', $params['cDesc']);
        if (isset($params['cID'])) {
            $button .= '<hr><strong>Einstellungsnr.:</strong> ' . $params['cID'];
        }
        $button .= '"><i class="fa fa-question"></i></button>';
    } else {
        if (isset($params['cID'])) {
            $button .= '<p><strong>Einstellungsnr.:</strong> ' . $params['cID'] . '</p>';
        }
        $button .= '"><i class="fa fa-question"></i></button>';
    }

    return $button;
}

/**
 * @param mixed $cRecht
 * @return bool
 */
function permission($cRecht)
{
    $bOkay = false;
    global $smarty;

    if (isset($_SESSION['AdminAccount'])) {
        $bOkay = (in_array($cRecht, $_SESSION['AdminAccount']->oGroup->oPermission_arr) || $_SESSION['AdminAccount']->oGroup->kAdminlogingruppe == 1);
    }

    if (!$bOkay) {
        $smarty->debugging = false;
    }

    return $bOkay;
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function SmartyConvertDate($params, &$smarty)
{
    if (isset($params['date']) && strlen($params['date']) > 0) {
        $oDateTime = new DateTime($params['date']);
        if (isset($params['format']) && strlen($params['format']) > 1) {
            $cDate = $oDateTime->format($params['format']);
        } else {
            $cDate = $oDateTime->format('d.m.Y H:i:s');
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $cDate);
        } else {
            return $cDate;
        }
    }

    return '';
}

/**
 * Map marketplace categoryId to localized category name
 * 
 * @param array $params
 * @param JTLSmarty $smarty
 */
function getExtensionCategory($params, &$smarty)
{
    if (!isset($params['cat'])) {
        return;
    }

    $catNames = array(
        4  => 'Templates/Themes',
        5  => 'Sprachpakete',
        6  => 'Druckvorlagen',
        7  => 'Tools',
        8  => 'Marketing',
        9  => 'Zahlungsarten',
        10 => 'Import/Export',
        11 => 'SEO',
        12 => 'Auswertungen'
    );

    $key = (isset($catNames[$params['cat']])) ? $catNames[$params['cat']] : null;
    $smarty->assign('catName', $key);
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return mixed|void
 */
function formatVersion($params, &$smarty)
{
    if (!isset($params['value'])) {
        return;
    }

    $version = (int) $params['value'];

    return substr_replace($version, '.', 1, 0);
}
