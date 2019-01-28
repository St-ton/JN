<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Backend\Revision;

$scc = new \scc\DefaultComponentRegistrator(new \sccbs3\Bs3sccRenderer($smarty));
$scc->registerComponents();

/** @global \Smarty\JTLSmarty $smarty */
$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCurrencyConversionSmarty', 'getCurrencyConversionSmarty')
       ->registerPlugin(
           Smarty::PLUGIN_FUNCTION,
           'getCurrencyConversionTooltipButton',
           'getCurrencyConversionTooltipButton'
       )
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCurrentPage', 'getCurrentPage')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'SmartyConvertDate', 'SmartyConvertDate')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getHelpDesc', 'getHelpDesc')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getExtensionCategory', 'getExtensionCategory')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'formatVersion', 'formatVersion')
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'formatByteSize', ['StringHandler', 'formatSize'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'gravatarImage', 'gravatarImage')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getRevisions', 'getRevisions')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'captchaMarkup', 'captchaMarkup')
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'permission', 'permission')
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, '__', [\Shop::Container()->getGetText(), 'translate']);

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 * @return string
 */
function getRevisions(array $params, $smarty): string
{
    $secondary = $params['secondary'] ?? false;
    $data      = $params['data'] ?? null;
    $revision  = new Revision(Shop::Container()->getDB());

    return $smarty->assign('revisions', $revision->getRevisions($params['type'], $params['key']))
                  ->assign('secondary', $secondary)
                  ->assign('data', $data)
                  ->assign('show', $params['show'])
                  ->fetch('tpl_inc/revisions.tpl');
}

/**
 * @param array $params
 * @return string
 */
function getCurrencyConversionSmarty(array $params): string
{
    $bForceSteuer = !(isset($params['bSteuer']) && $params['bSteuer'] === false);
    if (!isset($params['fPreisBrutto'])) {
        $params['fPreisBrutto'] = 0;
    }
    if (!isset($params['fPreisNetto'])) {
        $params['fPreisNetto'] = 0;
    }
    if (!isset($params['cClass'])) {
        $params['cClass'] = '';
    }

    return Currency::getCurrencyConversion(
        $params['fPreisNetto'],
        $params['fPreisBrutto'],
        $params['cClass'],
        $bForceSteuer
    );
}

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 * @return string
 */
function getCurrencyConversionTooltipButton(array $params, $smarty): string
{
    $placement = $params['placement'] ?? 'left';

    if (!isset($params['inputId'])) {
        return '';
    }
    $inputId = $params['inputId'];
    $button  = '<button type="button" class="btn btn-tooltip btn-info" id="' .
        $inputId . 'Tooltip" data-html="true"';
    $button  .= ' data-toggle="tooltip" data-placement="' . $placement . '">';
    $button  .= '<i class="fa fa-eur"></i></button>';

    return $button;
}

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 */
function getCurrentPage($params, $smarty): void
{
    $path = $_SERVER['SCRIPT_NAME'];
    $page = basename($path, '.php');

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $page);
    }
}

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 * @return string
 */
function getHelpDesc(array $params, $smarty): string
{
    $placement   = $params['placement'] ?? 'left';
    $cID         = !empty($params['cID']) ? $params['cID'] : null;
    $description = isset($params['cDesc'])
        ? str_replace('"', '\'', $params['cDesc'])
        : null;

    return $smarty->assign('placement', $placement)
                  ->assign('cID', $cID)
                  ->assign('description', $description)
                  ->fetch('tpl_inc/help_description.tpl');
}

/**
 * @param mixed $cRecht
 * @return bool
 */
function permission($cRecht): bool
{
    $ok = false;
    if (isset($_SESSION['AdminAccount'])) {
        if ((int)$_SESSION['AdminAccount']->oGroup->kAdminlogingruppe === ADMINGROUP) {
            $ok = true;
        } else {
            $orExpressions = explode('|', $cRecht);
            foreach ($orExpressions as $flag) {
                $ok = in_array($flag, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true);
                if ($ok) {
                    break;
                }
            }
        }
    }

    return $ok;
}

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 * @return string
 */
function SmartyConvertDate(array $params, $smarty)
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
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 */
function getExtensionCategory(array $params, $smarty): void
{
    if (!isset($params['cat'])) {
        return;
    }

    $catNames = [
        4  => 'Templates/Themes',
        5  => 'Sprachpakete',
        6  => 'Druckvorlagen',
        7  => 'Tools',
        8  => 'Marketing',
        9  => 'Zahlungsarten',
        10 => 'Import/Export',
        11 => 'SEO',
        12 => 'Auswertungen'
    ];

    $key = $catNames[$params['cat']] ?? null;
    $smarty->assign('catName', $key);
}

/**
 * @param array $params
 * @return string|null
 */
function formatVersion(array $params): ?string
{
    if (!isset($params['value'])) {
        return null;
    }

    return substr_replace((int)$params['value'], '.', 1, 0);
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param array     $params
 *
 * array['email'] - The email address
 * array['s']     - Size in pixels, defaults to 80px [ 1 - 2048 ]
 * array['d']     - Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * array['r']     - Maximum rating (inclusive) [ g | pg | r | x ]
 *
 * @source https://gravatar.com/site/implement/images/php/
 * @return string
 */
function gravatarImage(array $params): string
{
    $email = $params['email'] ?? null;
    if ($email === null) {
        $email = JTLSUPPORT_EMAIL;
    } else {
        unset($params['email']);
    }

    $params = array_merge(['email' => null, 's' => 80, 'd' => 'mm', 'r' => 'g'], $params);

    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= '?' . http_build_query($params, '', '&');

    executeHook(HOOK_BACKEND_FUNCTIONS_GRAVATAR, [
        'url'          => &$url,
        'AdminAccount' => &$_SESSION['AdminAccount']
    ]);

    return $url;
}

/**
 * @param array             $params
 * @param \Smarty\JTLSmarty $smarty
 * @return string
 */
function captchaMarkup(array $params, $smarty): string
{
    if (isset($params['getBody']) && $params['getBody']) {
        return Shop::Container()->getCaptchaService()->getBodyMarkup($smarty);
    }

    return Shop::Container()->getCaptchaService()->getHeadMarkup($smarty);
}
