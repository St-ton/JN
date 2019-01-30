<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$oAccount->permission('SETTINGS_SPECIALPRODUCTS_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'suchspecials';

setzeSprache();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_SUCHSPECIAL, $_POST);
} elseif (isset($_POST['suchspecials']) && (int)$_POST['suchspecials'] === 1 && Form::validateToken()) {
    // Suchspecials aus der DB holen und in smarty assignen
    $searchSpecials   = Shop::Container()->getDB()->selectAll(
        'tseo',
        ['cKey', 'kSprache'],
        ['suchspecial',
         (int)$_SESSION['kSprache']],
        '*',
        'kKey'
    );
    $ssTmp            = [];
    $ssToDelete       = [];
    $bestSellerSeo    = strip_tags(Shop::Container()->getDB()->escape($_POST['bestseller']));
    $specialOffersSeo = Shop::Container()->getDB()->escape($_POST['sonderangebote']);
    $newProductsSeo   = strip_tags(Shop::Container()->getDB()->escape($_POST['neu_im_sortiment']));
    $topOffersSeo     = strip_tags(Shop::Container()->getDB()->escape($_POST['top_angebote']));
    $releaseSeo       = strip_tags(Shop::Container()->getDB()->escape($_POST['in_kuerze_verfuegbar']));
    $topRatedSeo      = strip_tags(Shop::Container()->getDB()->escape($_POST['top_bewertet']));
    if (mb_strlen($bestSellerSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $bestSellerSeo,
        SEARCHSPECIALS_BESTSELLER
    )) {
        $bestSellerSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($bestSellerSeo));

        if ($bestSellerSeo !== $_POST['bestseller']) {
            $cHinweis .= sprintf(
                __('errorExistRename'),
                StringHandler::filterXSS($_POST['bestseller']),
                $bestSellerSeo
            ) . '<br />';
        }
        $oBestSeller       = new stdClass();
        $oBestSeller->kKey = SEARCHSPECIALS_BESTSELLER;
        $oBestSeller->cSeo = $bestSellerSeo;

        $ssTmp[] = $oBestSeller;
    } elseif (mb_strlen($bestSellerSeo) === 0) {
        $ssToDelete[] = SEARCHSPECIALS_BESTSELLER;
    }
    // Pruefe Sonderangebote
    if (mb_strlen($specialOffersSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $specialOffersSeo,
        SEARCHSPECIALS_SPECIALOFFERS
    )) {
        $specialOffersSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($specialOffersSeo));

        if ($specialOffersSeo !== $_POST['sonderangebote']) {
            $cHinweis .= sprintf(
                __('errorSpecialExistRename'),
                StringHandler::filterXSS($_POST['sonderangebote']),
                $specialOffersSeo
            ) . '<br />';
        }
        $specialOffer       = new stdClass();
        $specialOffer->kKey = SEARCHSPECIALS_SPECIALOFFERS;
        $specialOffer->cSeo = $specialOffersSeo;

        $ssTmp[] = $specialOffer;
    } elseif (mb_strlen($specialOffersSeo) === 0) {
        // cSeo loeschen
        $ssToDelete[] = SEARCHSPECIALS_SPECIALOFFERS;
    }
    // Pruefe Neu im Sortiment
    if (mb_strlen($newProductsSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $newProductsSeo,
        SEARCHSPECIALS_NEWPRODUCTS
    )) {
        $newProductsSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($newProductsSeo));

        if ($newProductsSeo !== $_POST['neu_im_sortiment']) {
            $cHinweis .= sprintf(
                __('errorNewExistRename'),
                StringHandler::filterXSS($_POST['neu_im_sortiment']),
                $newProductsSeo
            ) . '<br />';
        }
        $newProducts       = new stdClass();
        $newProducts->kKey = SEARCHSPECIALS_NEWPRODUCTS;
        $newProducts->cSeo = $newProductsSeo;

        $ssTmp[] = $newProducts;
    } elseif (mb_strlen($newProductsSeo) === 0) {
        // cSeo leoschen
        $ssToDelete[] = SEARCHSPECIALS_NEWPRODUCTS;
    }
    // Pruefe Top Angebote
    if (mb_strlen($topOffersSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $topOffersSeo,
        SEARCHSPECIALS_TOPOFFERS
    )) {
        $topOffersSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($topOffersSeo));

        if ($topOffersSeo !== $_POST['top_angebote']) {
            $cHinweis .= sprintf(
                __('errorTopProductsExistRename'),
                StringHandler::filterXSS($_POST['top_angebote']),
                $topOffersSeo
            ) . '<br />';
        }
        $topOffers       = new stdClass();
        $topOffers->kKey = SEARCHSPECIALS_TOPOFFERS;
        $topOffers->cSeo = $topOffersSeo;

        $ssTmp[] = $topOffers;
    } elseif (mb_strlen($topOffersSeo) === 0) {
        // cSeo loeschen
        $ssToDelete[] = SEARCHSPECIALS_TOPOFFERS;
    }
    // Pruefe In kuerze Verfuegbar
    if (mb_strlen($releaseSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $releaseSeo,
        SEARCHSPECIALS_UPCOMINGPRODUCTS
    )) {
        $releaseSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($releaseSeo));
        if ($releaseSeo !== $_POST['in_kuerze_verfuegbar']) {
            $cHinweis .= sprintf(
                __('errorSoonExistRename'),
                StringHandler::filterXSS($_POST['in_kuerze_verfuegbar']),
                $releaseSeo
            ) . '<br />';
        }
        $release       = new stdClass();
        $release->kKey = SEARCHSPECIALS_UPCOMINGPRODUCTS;
        $release->cSeo = $releaseSeo;

        $ssTmp[] = $release;
    } elseif (mb_strlen($releaseSeo) === 0) {
        // cSeo loeschen
        $ssToDelete[] = SEARCHSPECIALS_UPCOMINGPRODUCTS;
    }
    // Pruefe Top bewertet
    if (mb_strlen($topRatedSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $topRatedSeo,
        SEARCHSPECIALS_TOPREVIEWS
    )) {
        $topRatedSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($topRatedSeo));

        if ($topRatedSeo !== $_POST['top_bewertet']) {
            $cHinweis .= sprintf(
                __('errorTopRatingExistRename'),
                StringHandler::filterXSS($_POST['top_bewertet']),
                $topRatedSeo
            ) . '<br />';
        }
        $topRated       = new stdClass();
        $topRated->kKey = SEARCHSPECIALS_TOPREVIEWS;
        $topRated->cSeo = $topRatedSeo;

        $ssTmp[] = $topRated;
    } elseif (mb_strlen($topRatedSeo) === 0) {
        // cSeo loeschen
        $ssToDelete[] = SEARCHSPECIALS_TOPREVIEWS;
    }
    // tseo speichern
    if (count($ssTmp) > 0) {
        $ids = [];
        foreach ($ssTmp as $i => $item) {
            $ids[] = (int)$item->kKey;
        }
        Shop::Container()->getDB()->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . '
                    AND kKey IN (' . implode(',', $ids) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
        foreach ($ssTmp as $item) {
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $item->cSeo;
            $oSeo->cKey     = 'suchspecial';
            $oSeo->kKey     = $item->kKey;
            $oSeo->kSprache = $_SESSION['kSprache'];

            Shop::Container()->getDB()->insert('tseo', $oSeo);
        }
    }
    if (count($ssToDelete) > 0) {
        Shop::Container()->getDB()->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . '
                    AND kKey IN (' . implode(',', $ssToDelete) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    $cHinweis .= __('successSeoSave') . '<br />';
}

$ssSeoData      = Shop::Container()->getDB()->selectAll(
    'tseo',
    ['cKey', 'kSprache'],
    ['suchspecial', (int)$_SESSION['kSprache']],
    '*',
    'kKey'
);
$searchSpecials = [];
foreach ($ssSeoData as $oSuchSpecials) {
    $searchSpecials[$oSuchSpecials->kKey] = $oSuchSpecials->cSeo;
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_SUCHSPECIAL))
       ->assign('oSuchSpecials_arr', $searchSpecials)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('suchspecials.tpl');

/**
 * Prueft ob ein bestimmtes Suchspecial Seo schon vorhanden ist
 *
 * @param array  $oSuchSpecials_arr
 * @param string $cSeo
 * @param int    $kKey
 * @return bool
 */
function pruefeSuchspecialSeo($oSuchSpecials_arr, $cSeo, $kKey)
{
    if ($kKey > 0 && is_array($oSuchSpecials_arr) && count($oSuchSpecials_arr) > 0 && mb_strlen($cSeo)) {
        foreach ($oSuchSpecials_arr as $oSuchSpecials) {
            if ($oSuchSpecials->kKey == $kKey && $oSuchSpecials->cSeo === $cSeo) {
                return true;
            }
        }
    }

    return false;
}
