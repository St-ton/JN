<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';

$response  = new AjaxResponse();
$action    = isset($_GET['action']) ? $_GET['action'] : null;

if ($oAccount->logged() !== true) {
    $action = 'login';
}

function addFavUrl($title, $url, $sort = -1)
{
    global $oAccount;

    $urlHelper = new UrlHelper($url);
    $id = (int) $_SESSION['AdminAccount']->kAdminlogin;
    $sort = (int) $sort;

    $url = str_replace(
        [Shop::getURL(), Shop::getURL(true)],
        '',
        $urlHelper->normalize()
    );

    $url = strip_tags($url);
    $url = ltrim($url, '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);

    if ($sort < 0) {
        $sort = count($oAccount->getFavorites());
    }

    $item = (object)[
        'kAdminlogin' => $id,
        'cTitel' => $title,
        'cUrl' => $url,
        'nSort' => $sort
    ];

    $item = utf8_convert_recursive($item, false);

    if ($id > 0 && strlen($item->cTitel) > 0 && strlen($item->cUrl) > 0) {
        Shop::DB()->insertRow('tadminfavs', $item);
        return true;
    }

    return false;
}

function removeFavUrl($kAdminfav = 0)
{
    $kAdminfav = (int) $kAdminfav;
    $kAdminlogin = (int) $_SESSION['AdminAccount']->kAdminlogin;

    if ($kAdminfav > 0) {
        Shop::DB()->query("DELETE FROM tadminfavs WHERE kAdminfav={$kAdminfav} AND kAdminlogin={$kAdminlogin}", 3);
    }
    else {
        Shop::DB()->query("DELETE FROM tadminfavs WHERE kAdminlogin={$kAdminlogin}", 3);
    }
}

switch ($action) {
    case 'login': {
        if ($response->isAjax()) {
            $result = $response->buildError('Unauthorized', 401);
            $response->makeResponse($result);
        } else {
            $oAccount->redirectOnFailure();
        }

        return;
    }
    case 'add': {
        $success = false;
        $title = isset($_GET['title']) ? $_GET['title'] : null;
        $url = isset($_GET['url']) ? $_GET['url'] : null;

        if (!empty($title) && !empty($url)) {
            $success = addFavUrl($title, $url);
        }

        if ($success) {
            $result = $response->buildResponse([
                'title' => $title,
                'url' => $url
            ]);
        }
        else {
            $result = $response->buildError('Unauthorized', 401);
        }

        $response->makeResponse($result, $action);
        break;
    }

    case 'list': {
        $result = $response->buildResponse([
            'tpl' => $smarty
                ->assign('favorites', $oAccount->getFavorites())
                ->fetch('tpl_inc/favs_drop.tpl')
        ]);
        $response->makeResponse($result, $action);
        break;
    }

    default: {
        if (isset($_POST['title']) && isset($_POST['url'])) {
            $titles = $_POST['title'];
            $urls = $_POST['url'];

            if (is_array($titles) && is_array($urls) && count($titles) == count($urls)) {
                removeFavUrl();
                foreach ($titles as $i => $title) {
                    addFavUrl($title, $urls[$i], $i);
                }
            }
        }

        $smarty
            ->assign('favorites', $oAccount->getFavorites())
            ->display('favs.tpl');
        break;
    }
}
