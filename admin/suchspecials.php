<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SETTINGS_SPECIALPRODUCTS_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step        = 'suchspecials';
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();

setzeSprache();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_SUCHSPECIAL, $_POST),
        'saveSettings'
    );
} elseif (isset($_POST['suchspecials']) && (int)$_POST['suchspecials'] === 1 && Form::validateToken()) {
    $searchSpecials   = $db->selectAll(
        'tseo',
        ['cKey', 'kSprache'],
        ['suchspecial',
         (int)$_SESSION['kSprache']],
        '*',
        'kKey'
    );
    $ssTmp            = [];
    $ssToDelete       = [];
    $bestSellerSeo    = strip_tags($db->escape($_POST['bestseller']));
    $specialOffersSeo = $db->escape($_POST['sonderangebote']);
    $newProductsSeo   = strip_tags($db->escape($_POST['neu_im_sortiment']));
    $topOffersSeo     = strip_tags($db->escape($_POST['top_angebote']));
    $releaseSeo       = strip_tags($db->escape($_POST['in_kuerze_verfuegbar']));
    $topRatedSeo      = strip_tags($db->escape($_POST['top_bewertet']));
    if (mb_strlen($bestSellerSeo) > 0 && !pruefeSuchspecialSeo(
        $searchSpecials,
        $bestSellerSeo,
        SEARCHSPECIALS_BESTSELLER
    )) {
        $bestSellerSeo = Seo::checkSeo(Seo::getSeo($bestSellerSeo));

        if ($bestSellerSeo !== $_POST['bestseller']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorBestsellerExistRename'),
                    Text::filterXSS($_POST['bestseller']),
                    $bestSellerSeo
                ),
                'errorBestsellerExistRename'
            );
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
        $specialOffersSeo = Seo::checkSeo(Seo::getSeo($specialOffersSeo));

        if ($specialOffersSeo !== $_POST['sonderangebote']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorSpecialExistRename'),
                    Text::filterXSS($_POST['sonderangebote']),
                    $specialOffersSeo
                ),
                'errorSpecialExistRename'
            );
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
        $newProductsSeo = Seo::checkSeo(Seo::getSeo($newProductsSeo));

        if ($newProductsSeo !== $_POST['neu_im_sortiment']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorNewExistRename'),
                    Text::filterXSS($_POST['neu_im_sortiment']),
                    $newProductsSeo
                ),
                'errorNewExistRename'
            );
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
        $topOffersSeo = Seo::checkSeo(Seo::getSeo($topOffersSeo));

        if ($topOffersSeo !== $_POST['top_angebote']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorTopProductsExistRename'),
                    Text::filterXSS($_POST['top_angebote']),
                    $topOffersSeo
                ),
                'errorTopProductsExistRename'
            );
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
        $releaseSeo = Seo::checkSeo(Seo::getSeo($releaseSeo));
        if ($releaseSeo !== $_POST['in_kuerze_verfuegbar']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorSoonExistRename'),
                    Text::filterXSS($_POST['in_kuerze_verfuegbar']),
                    $releaseSeo
                ),
                'errorSoonExistRename'
            );
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
        $topRatedSeo = Seo::checkSeo(Seo::getSeo($topRatedSeo));

        if ($topRatedSeo !== $_POST['top_bewertet']) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                sprintf(
                    __('errorTopRatingExistRename'),
                    Text::filterXSS($_POST['top_bewertet']),
                    $topRatedSeo
                ),
                'errorTopRatingExistRename'
            );
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
        $db->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . '
                    AND kKey IN (' . implode(',', $ids) . ')',
            ReturnType::AFFECTED_ROWS
        );
        foreach ($ssTmp as $item) {
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $item->cSeo;
            $oSeo->cKey     = 'suchspecial';
            $oSeo->kKey     = $item->kKey;
            $oSeo->kSprache = $_SESSION['kSprache'];

            $db->insert('tseo', $oSeo);
        }
    }
    if (count($ssToDelete) > 0) {
        $db->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . '
                    AND kKey IN (' . implode(',', $ssToDelete) . ')',
            ReturnType::AFFECTED_ROWS
        );
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSeoSave'), 'successSeoSave');
}

$ssSeoData      = $db->selectAll(
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
