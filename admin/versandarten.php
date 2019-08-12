<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Checkout\Versandart;
use JTL\Checkout\ZipValidator;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_SHIPMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
Tax::setTaxRates();
$db              = Shop::Container()->getDB();
$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$shippingType    = null;
$step            = 'uebersicht';
$shippingMethod  = null;
$taxRateKeys     = array_keys($_SESSION['Steuersatz']);
$alertHelper     = Shop::Container()->getAlertService();
$countryHelper   = Shop::Container()->getCountryService();
$languages       = LanguageHelper::getAllLanguages();

$missingShippingClassCombis = getMissingShippingClassCombi();
$smarty->assign('missingShippingClassCombis', $missingShippingClassCombis);

if (Form::validateToken()) {
    if (isset($_POST['neu'], $_POST['kVersandberechnung'])
        && (int)$_POST['neu'] === 1
        && (int)$_POST['kVersandberechnung'] > 0
    ) {
        $step = 'neue Versandart';
    }
    if (isset($_POST['kVersandberechnung']) && (int)$_POST['kVersandberechnung'] > 0) {
        $shippingType = getShippingTypes(Request::verifyGPCDataInt('kVersandberechnung'));
    }

    if (isset($_POST['del']) && (int)$_POST['del'] > 0 && Versandart::deleteInDB($_POST['del'])) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successShippingMethodDelete'), 'successShippingMethodDelete');
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    }
    if (isset($_POST['edit']) && (int)$_POST['edit'] > 0) {
        $step                            = 'neue Versandart';
        $shippingMethod                  = $db->select('tversandart', 'kVersandart', (int)$_POST['edit']);
        $VersandartZahlungsarten         = $db->selectAll(
            'tversandartzahlungsart',
            'kVersandart',
            (int)$_POST['edit'],
            '*',
            'kZahlungsart'
        );
        $VersandartStaffeln              = $db->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$_POST['edit'],
            '*',
            'fBis'
        );
        $shippingType                    = getShippingTypes((int)$shippingMethod->kVersandberechnung);
        $shippingMethod->cVersandklassen = trim($shippingMethod->cVersandklassen);

        $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
            ->assign('VersandartStaffeln', $VersandartStaffeln)
            ->assign('Versandart', $shippingMethod)
            ->assign('gewaehlteLaender', explode(' ', $shippingMethod->cLaender));
    }

    if (isset($_POST['clone']) && (int)$_POST['clone'] > 0) {
        $step = 'uebersicht';
        if (Versandart::cloneShipping($_POST['clone'])) {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                __('successShippingMethodDuplicated'),
                'successShippingMethodDuplicated'
            );
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorShippingMethodDuplicated'),
                'errorShippingMethodDuplicated'
            );
        }
    }

    if (isset($_GET['cISO'], $_GET['zuschlag'], $_GET['kVersandart'])
        && (int)$_GET['zuschlag'] === 1
        && (int)$_GET['kVersandart'] > 0
    ) {
        $step = 'Zuschlagsliste';
    }

    if (isset($_GET['delzus']) && (int)$_GET['delzus'] > 0) {
        $step = 'Zuschlagsliste';
        $db->queryPrepared(
            'DELETE tversandzuschlag, tversandzuschlagsprache
                FROM tversandzuschlag
                LEFT JOIN tversandzuschlagsprache
                  ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                WHERE tversandzuschlag.kVersandzuschlag = :kVersandzuschlag',
            ['kVersandzuschlag' => $_GET['delzus']],
            ReturnType::DEFAULT
        );
        $db->delete('tversandzuschlagplz', 'kVersandzuschlag', (int)$_GET['delzus']);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successDeleteSurchargeList'), 'successlistDelete');
    }
    // Zuschlagliste editieren
    if (Request::verifyGPCDataInt('editzus') > 0) {
        $kVersandzuschlag = Request::verifyGPCDataInt('editzus');
        $cISO             = Text::convertISO6392ISO(Request::verifyGPDataString('cISO'));
        if ($kVersandzuschlag > 0 && (mb_strlen($cISO) > 0 && $cISO !== 'noISO')) {
            $step = 'Zuschlagsliste';
            $fee  = $db->select('tversandzuschlag', 'kVersandzuschlag', $kVersandzuschlag);
            if (isset($fee->kVersandzuschlag) && $fee->kVersandzuschlag > 0) {
                $fee->oVersandzuschlagSprache_arr = [];
                $localizations                    = $db->selectAll(
                    'tversandzuschlagsprache',
                    'kVersandzuschlag',
                    (int)$fee->kVersandzuschlag
                );
                foreach ($localizations as $localized) {
                    $fee->oVersandzuschlagSprache_arr[$localized->cISOSprache] = $localized;
                }
            }
            $smarty->assign('oVersandzuschlag', $fee);
        }
    }

    if (isset($_GET['delplz']) && (int)$_GET['delplz'] > 0) {
        $step = 'Zuschlagsliste';
        $db->delete('tversandzuschlagplz', 'kVersandzuschlagPlz', (int)$_GET['delplz']);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successDeleteZIP'), 'successZIPDelete');
    }

    if (isset($_POST['neueZuschlagPLZ']) && (int)$_POST['neueZuschlagPLZ'] === 1) {
        $step         = 'Zuschlagsliste';
        $zipValidator = new ZipValidator($_POST['cISO']);
        $plzFee       = new stdClass();

        $plzFee->kVersandzuschlag = (int)$_POST['kVersandzuschlag'];
        if (!empty($_POST['cPLZ'])) {
            $plzFee->cPLZ = $zipValidator->validateZip($_POST['cPLZ']);
        }
        if (!empty($_POST['cPLZAb']) && !empty($_POST['cPLZBis'])) {
            unset($plzFee->cPLZ);
            $plzFee->cPLZAb  = $zipValidator->validateZip($_POST['cPLZAb']);
            $plzFee->cPLZBis = $zipValidator->validateZip($_POST['cPLZBis']);
            if ($plzFee->cPLZAb > $plzFee->cPLZBis) {
                $plzFee->cPLZAb  = $zipValidator->validateZip($_POST['cPLZBis']);
                $plzFee->cPLZBis = $zipValidator->validateZip($_POST['cPLZAb']);
            }
        }

        $versandzuschlag = $db->select('tversandzuschlag', 'kVersandzuschlag', (int)$plzFee->kVersandzuschlag);

        if (!empty($plzFee->cPLZ) || !empty($plzFee->cPLZAb)) {
            //schaue, ob sich PLZ ueberschneiden
            if (!empty($plzFee->cPLZ)) {
                $plz_x = $db->queryPrepared(
                    'SELECT tversandzuschlagplz.*
                        FROM tversandzuschlagplz, tversandzuschlag
                        WHERE (tversandzuschlagplz.cPLZ = :surchargeZip
                            OR :surchargeZip BETWEEN tversandzuschlagplz.cPLZAb
                            AND tversandzuschlagplz.cPLZBis)
                            AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                            AND tversandzuschlag.cISO = :surchargeISO
                            AND tversandzuschlag.kVersandart = :surchargeShipmentMode',
                    [
                        'surchargeZip'          => $plzFee->cPLZ,
                        'surchargeISO'          => $versandzuschlag->cISO,
                        'surchargeShipmentMode' => (int)$versandzuschlag->kVersandart
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $plz_x = $db->queryPrepared(
                    'SELECT tversandzuschlagplz.*
                        FROM tversandzuschlagplz, tversandzuschlag
                        WHERE (tversandzuschlagplz.cPLZ BETWEEN :surchargeZipFrom AND :surchargeZipTo
                            OR :surchargeZipTo >= tversandzuschlagplz.cPLZAb
                            AND tversandzuschlagplz.cPLZBis >= :surchargeZipFrom)
                            AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                            AND tversandzuschlag.cISO = :surchargeISO
                            AND tversandzuschlag.kVersandart = :surchargeShipmentMode',
                    [
                        'surchargeZipTo'        => $plzFee->cPLZBis,
                        'surchargeZipFrom'      => $plzFee->cPLZAb,
                        'surchargeISO'          => $versandzuschlag->cISO,
                        'surchargeShipmentMode' => (int)$versandzuschlag->kVersandart
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );
            }
            // (string-)merge the possible resulting 'overlaps'
            // (multiple single ZIP or multiple ZIP-ranges)
            $szPLZ = $szPLZRange = $szOverlap = '';
            foreach ($plz_x as $oResult) {
                if (!empty($oResult->cPLZ) && (0 < mb_strlen($szPLZ))) {
                    $szPLZ .= ', ' . $oResult->cPLZ;
                } elseif (!empty($oResult->cPLZ) && (mb_strlen($szPLZ) === 0)) {
                    $szPLZ = $oResult->cPLZ;
                }
                if (!empty($oResult->cPLZAb) && 0 < mb_strlen($szPLZRange)) {
                    $szPLZRange .= ', ' . $oResult->cPLZAb . '-' . $oResult->cPLZBis;
                } elseif (!empty($oResult->cPLZAb) && mb_strlen($szPLZRange) === 0) {
                    $szPLZRange = $oResult->cPLZAb . '-' . $oResult->cPLZBis;
                }
            }
            if ((0 < mb_strlen($szPLZ)) && (0 < mb_strlen($szPLZRange))) {
                $szOverlap = $szPLZ . ' und ' . $szPLZRange;
            } else {
                $szOverlap = (0 < mb_strlen($szPLZ)) ? $szPLZ : $szPLZRange;
            }
            // form an error-string, if there are any errors, or insert the input into the DB
            if (0 < mb_strlen($szOverlap)) {
                if (!empty($plzFee->cPLZ)) {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(
                            __('errorZIPOverlap'),
                            $plzFee->cPLZ,
                            $szOverlap
                        ),
                        'errorZIPOverlap'
                    );
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(
                            __('errorZIPAreaOverlap'),
                            $plzFee->cPLZAb . '-' . $plzFee->cPLZBis,
                            $szOverlap
                        ),
                        'errorZIPAreaOverlap'
                    );
                }
            } elseif ($db->insert('tversandzuschlagplz', $plzFee)) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successZIPAdd'), 'successZIPAdd');
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        } else {
            $szErrorString = $zipValidator->getError();
            if ($szErrorString !== '') {
                $alertHelper->addAlert(Alert::TYPE_ERROR, $szErrorString, 'errorZIPValidator');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorZIPMissing'), 'errorZIPMissing');
            }
        }
    }

    if (isset($_POST['neuerZuschlag']) && (int)$_POST['neuerZuschlag'] === 1) {
        $step = 'Zuschlagsliste';
        $fee  = new stdClass();
        if (Request::verifyGPCDataInt('kVersandzuschlag') > 0) {
            $fee->kVersandzuschlag = Request::verifyGPCDataInt('kVersandzuschlag');
        }

        $fee->kVersandart = (int)$_POST['kVersandart'];
        $fee->cISO        = $_POST['cISO'];
        $fee->cName       = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $fee->fZuschlag   = (float)str_replace(',', '.', $_POST['fZuschlag']);
        if ($fee->cName && $fee->fZuschlag != 0) {
            $kVersandzuschlag = 0;
            if (isset($fee->kVersandzuschlag) && $fee->kVersandzuschlag > 0) {
                $db->delete('tversandzuschlag', 'kVersandzuschlag', (int)$fee->kVersandzuschlag);
            }
            if (($kVersandzuschlag = $db->insert('tversandzuschlag', $fee)) > 0) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successListAdd'), 'successListAdd');
            }
            if (isset($fee->kVersandzuschlag) && $fee->kVersandzuschlag > 0) {
                $kVersandzuschlag = (int)$fee->kVersandzuschlag;
            }
            $zuschlagSprache = new stdClass();

            $zuschlagSprache->kVersandzuschlag = $kVersandzuschlag;
            foreach ($languages as $language) {
                $code                         = $language->getIso();
                $zuschlagSprache->cISOSprache = $code;
                $zuschlagSprache->cName       = $fee->cName;
                if ($_POST['cName_' . $code]) {
                    $zuschlagSprache->cName = $_POST['cName_' . $code];
                }

                $db->delete(
                    'tversandzuschlagsprache',
                    ['kVersandzuschlag', 'cISOSprache'],
                    [$kVersandzuschlag, $code]
                );
                $db->insert('tversandzuschlagsprache', $zuschlagSprache);
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        } else {
            if (!$fee->cName) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorListNameMissing'), 'errorListNameMissing');
            }
            if (!$fee->fZuschlag) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorListPriceMissing'), 'errorListPriceMissing');
            }
        }
    }

    if (isset($_POST['neueVersandart']) && (int)$_POST['neueVersandart'] > 0) {
        $shippingMethod                           = new stdClass();
        $shippingMethod->cName                    = htmlspecialchars(
            $_POST['cName'],
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $shippingMethod->kVersandberechnung       = (int)$_POST['kVersandberechnung'];
        $shippingMethod->cAnzeigen                = $_POST['cAnzeigen'];
        $shippingMethod->cBild                    = $_POST['cBild'];
        $shippingMethod->nSort                    = (int)$_POST['nSort'];
        $shippingMethod->nMinLiefertage           = (int)$_POST['nMinLiefertage'];
        $shippingMethod->nMaxLiefertage           = (int)$_POST['nMaxLiefertage'];
        $shippingMethod->cNurAbhaengigeVersandart = $_POST['cNurAbhaengigeVersandart'];
        $shippingMethod->cSendConfirmationMail    = $_POST['cSendConfirmationMail'] ?? 'Y';
        $shippingMethod->cIgnoreShippingProposal  = $_POST['cIgnoreShippingProposal'] ?? 'N';
        $shippingMethod->eSteuer                  = $_POST['eSteuer'];
        $shippingMethod->fPreis                   = (float)str_replace(',', '.', $_POST['fPreis'] ?? 0);
        // Versandkostenfrei ab X
        $shippingMethod->fVersandkostenfreiAbX = (isset($_POST['versandkostenfreiAktiv'])
            && (int)$_POST['versandkostenfreiAktiv'] === 1)
            ? (float)$_POST['fVersandkostenfreiAbX']
            : 0;
        // Deckelung
        $shippingMethod->fDeckelung = (int)($_POST['versanddeckelungAktiv'] ?? 0) === 1
            ? (float)$_POST['fDeckelung']
            : 0;

        $shippingMethod->cLaender = '';
        $Laender                  = $_POST['land'];
        if (is_array($Laender)) {
            foreach ($Laender as $Land) {
                $shippingMethod->cLaender .= $Land . ' ';
            }
        }

        $VersandartZahlungsarten = [];
        foreach (Request::verifyGPDataIntegerArray('kZahlungsart') as $kZahlungsart) {
            $versandartzahlungsart               = new stdClass();
            $versandartzahlungsart->kZahlungsart = $kZahlungsart;
            if ($_POST['fAufpreis_' . $kZahlungsart] != 0) {
                $versandartzahlungsart->fAufpreis    = (float)str_replace(
                    ',',
                    '.',
                    $_POST['fAufpreis_' . $kZahlungsart]
                );
                $versandartzahlungsart->cAufpreisTyp = $_POST['cAufpreisTyp_' . $kZahlungsart];
            }
            $VersandartZahlungsarten[] = $versandartzahlungsart;
        }

        $VersandartStaffeln       = [];
        $upperLimits              = []; // Haelt alle fBis der Staffel
        $staffelDa                = true;
        $shippingFreeValid        = true;
        $fMaxVersandartStaffelBis = 0;
        if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl'
            || $shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
            || $shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'
        ) {
            $staffelDa = false;
            if (count($_POST['bis']) > 0 && count($_POST['preis']) > 0) {
                $staffelDa = true;
            }
            //preisstaffel beachten
            if (!isset($_POST['bis'][0])
                || mb_strlen($_POST['bis'][0]) === 0
                || !isset($_POST['preis'][0])
                || mb_strlen($_POST['preis'][0]) === 0
            ) {
                $staffelDa = false;
            }
            if (is_array($_POST['bis']) && is_array($_POST['preis'])) {
                foreach ($_POST['bis'] as $i => $fBis) {
                    if (isset($_POST['preis'][$i]) && mb_strlen($fBis) > 0) {
                        unset($oVersandstaffel);
                        $oVersandstaffel         = new stdClass();
                        $oVersandstaffel->fBis   = (float)str_replace(',', '.', $fBis);
                        $oVersandstaffel->fPreis = (float)str_replace(',', '.', $_POST['preis'][$i]);

                        $VersandartStaffeln[] = $oVersandstaffel;
                        $upperLimits[]        = $oVersandstaffel->fBis;
                    }
                }
            }
            // Dummy Versandstaffel hinzufuegen, falls Versandart nach Warenwert und Versandkostenfrei ausgewaehlt wurde
            if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
                && $shippingMethod->fVersandkostenfreiAbX > 0
            ) {
                $oVersandstaffel         = new stdClass();
                $oVersandstaffel->fBis   = 999999999;
                $oVersandstaffel->fPreis = 0.0;
                $VersandartStaffeln[]    = $oVersandstaffel;
            }
        }
        // Kundengruppe
        $shippingMethod->cKundengruppen = '';
        if (!$_POST['kKundengruppe']) {
            $_POST['kKundengruppe'] = [-1];
        }
        if (is_array($_POST['kKundengruppe'])) {
            if (in_array(-1, $_POST['kKundengruppe'])) {
                $shippingMethod->cKundengruppen = '-1';
            } else {
                $shippingMethod->cKundengruppen = ';' . implode(';', $_POST['kKundengruppe']) . ';';
            }
        }
        //Versandklassen
        $shippingMethod->cVersandklassen = ((!empty($_POST['kVersandklasse']) && $_POST['kVersandklasse'] !== '-1')
            ? ' ' . $_POST['kVersandklasse'] . ' '
            : '-1');

        if (count($_POST['land']) >= 1
            && count($_POST['kZahlungsart']) >= 1
            && $shippingMethod->cName
            && $staffelDa
            && $shippingFreeValid
        ) {
            $kVersandart = 0;
            if ((int)$_POST['kVersandart'] === 0) {
                $kVersandart = $db->insert('tversandart', $shippingMethod);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successShippingMethodCreate'), $shippingMethod->cName),
                    'successShippingMethodCreate'
                );
            } else {
                //updaten
                $kVersandart = (int)$_POST['kVersandart'];
                $db->update('tversandart', 'kVersandart', $kVersandart, $shippingMethod);
                $db->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
                $db->delete('tversandartstaffel', 'kVersandart', $kVersandart);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successShippingMethodChange'), $shippingMethod->cName),
                    'successShippingMethodChange'
                );
            }
            if ($kVersandart > 0) {
                foreach ($VersandartZahlungsarten as $versandartzahlungsart) {
                    $versandartzahlungsart->kVersandart = $kVersandart;
                    $db->insert('tversandartzahlungsart', $versandartzahlungsart);
                }

                foreach ($VersandartStaffeln as $versandartstaffel) {
                    $versandartstaffel->kVersandart = $kVersandart;
                    $db->insert('tversandartstaffel', $versandartstaffel);
                }
                $versandSprache = new stdClass();

                $versandSprache->kVersandart = $kVersandart;
                foreach ($languages as $language) {
                    $versandSprache->cISOSprache = $language->cISO;
                    $versandSprache->cName       = $shippingMethod->cName;
                    if ($_POST['cName_' . $language->cISO]) {
                        $versandSprache->cName = htmlspecialchars(
                            $_POST['cName_' . $language->cISO],
                            ENT_COMPAT | ENT_HTML401,
                            JTL_CHARSET
                        );
                    }
                    $versandSprache->cLieferdauer = '';
                    if ($_POST['cLieferdauer_' . $language->cISO]) {
                        $versandSprache->cLieferdauer = htmlspecialchars(
                            $_POST['cLieferdauer_' . $language->cISO],
                            ENT_COMPAT | ENT_HTML401,
                            JTL_CHARSET
                        );
                    }
                    $versandSprache->cHinweistext = '';
                    if ($_POST['cHinweistext_' . $language->cISO]) {
                        $versandSprache->cHinweistext = $_POST['cHinweistext_' . $language->cISO];
                    }
                    $versandSprache->cHinweistextShop = '';
                    if ($_POST['cHinweistextShop_' . $language->cISO]) {
                        $versandSprache->cHinweistextShop = $_POST['cHinweistextShop_' . $language->cISO];
                    }
                    $db->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$kVersandart, $language->cISO]);
                    $db->insert('tversandartsprache', $versandSprache);
                }
                $step = 'uebersicht';
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        } else {
            $step = 'neue Versandart';
            if (!$shippingMethod->cName) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodNameMissing'),
                    'errorShippingMethodNameMissing'
                );
            }
            if (count($_POST['land']) < 1) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodCountryMissing'),
                    'errorShippingMethodCountryMissing'
                );
            }
            if (count($_POST['kZahlungsart']) < 1) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodPaymentMissing'),
                    'errorShippingMethodPaymentMissing'
                );
            }
            if (!$staffelDa) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorShippingMethodPriceMissing'),
                    'errorShippingMethodPriceMissing'
                );
            }
            if (!$shippingFreeValid) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorShippingFreeMax'), 'errorShippingFreeMax');
            }
            if ((int)$_POST['kVersandart'] > 0) {
                $shippingMethod = $db->select('tversandart', 'kVersandart', (int)$_POST['kVersandart']);
            }
            $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
                ->assign('VersandartStaffeln', $VersandartStaffeln)
                ->assign('Versandart', $shippingMethod)
                ->assign('gewaehlteLaender', explode(' ', $shippingMethod->cLaender));
        }
    }
}

if ($step === 'neue Versandart') {
    $versandlaender = $countryHelper->getCountrylist();
    if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl') {
        $smarty->assign('einheit', 'kg');
    }
    if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl') {
        $smarty->assign('einheit', $defaultCurrency->cName);
    }
    if ($shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
        $smarty->assign('einheit', 'Stück');
    }
    // prevent "unusable" payment methods from displaying them in the config section (mainly the null-payment)
    $zahlungsarten = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, nSort, cName'
    );
    $smarty->assign('versandKlassen', $db->selectAll('tversandklasse', [], [], '*', 'kVersandklasse'));
    $tmpID = 0;
    if (isset($shippingMethod->kVersandart) && $shippingMethod->kVersandart > 0) {
        $tmpID = $shippingMethod->kVersandart;
    }
    $smarty->assign('zahlungsarten', $zahlungsarten)
        ->assign('versandlaender', $versandlaender)
        ->assign('versandberechnung', $shippingType)
        ->assign('waehrung', $defaultCurrency->cName)
        ->assign('kundengruppen', $db->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        ))
        ->assign('oVersandartSpracheAssoc_arr', getShippingLanguage($tmpID, $languages))
        ->assign('gesetzteVersandklassen', isset($shippingMethod->cVersandklassen)
            ? gibGesetzteVersandklassen($shippingMethod->cVersandklassen)
            : null)
        ->assign('gesetzteKundengruppen', isset($shippingMethod->cKundengruppen)
            ? gibGesetzteKundengruppen($shippingMethod->cKundengruppen)
            : null);
}

if ($step === 'uebersicht') {
    $customerGroups  = $db->query(
        'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $shippingMethods = $db->query(
        'SELECT * FROM tversandart ORDER BY nSort, cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($shippingMethods as $method) {
        $method->versandartzahlungsarten = $db->query(
            'SELECT tversandartzahlungsart.*
                FROM tversandartzahlungsart
                JOIN tzahlungsart
                    ON tzahlungsart.kZahlungsart = tversandartzahlungsart.kZahlungsart
                WHERE tversandartzahlungsart.kVersandart = ' . (int)$method->kVersandart . '
                ORDER BY tzahlungsart.cAnbieter, tzahlungsart.nSort, tzahlungsart.cName',
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($method->versandartzahlungsarten as $smp) {
            $smp->zahlungsart  = $db->select(
                'tzahlungsart',
                'kZahlungsart',
                (int)$smp->kZahlungsart,
                'nActive',
                1
            );
            $smp->cAufpreisTyp = $smp->cAufpreisTyp === 'prozent' ? '%' : '';
        }
        $method->versandartstaffeln         = $db->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$method->kVersandart,
            '*',
            'fBis'
        );
        $method->fPreisBrutto               = berechneVersandpreisBrutto(
            $method->fPreis,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        $method->fVersandkostenfreiAbXNetto = berechneVersandpreisNetto(
            $method->fVersandkostenfreiAbX,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        $method->fDeckelungBrutto           = berechneVersandpreisBrutto(
            $method->fDeckelung,
            $_SESSION['Steuersatz'][$taxRateKeys[0]]
        );
        foreach ($method->versandartstaffeln as $j => $oVersandartstaffeln) {
            $method->versandartstaffeln[$j]->fPreisBrutto = berechneVersandpreisBrutto(
                $oVersandartstaffeln->fPreis,
                $_SESSION['Steuersatz'][$taxRateKeys[0]]
            );
        }

        $method->versandberechnung = getShippingTypes((int)$method->kVersandberechnung);
        $method->versandklassen    = gibGesetzteVersandklassenUebersicht($method->cVersandklassen);
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
            $method->einheit = 'kg';
        }
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
            $method->einheit = $defaultCurrency->cName;
        }
        if ($method->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
            $method->einheit = 'Stück';
        }
        $method->land_arr = explode(' ', $method->cLaender);
        $count            = count($method->land_arr);
        foreach ($method->land_arr as $country) {
            $zuschlag = $db->select(
                'tversandzuschlag',
                'cISO',
                $country,
                'kVersandart',
                (int)$method->kVersandart
            );
            if (isset($zuschlag->kVersandart) && $zuschlag->kVersandart > 0) {
                $method->zuschlag_arr[$country] = '(Zuschlag)';
            }
        }
        $method->cKundengruppenName_arr  = [];
        $method->oVersandartSprachen_arr = $db->selectAll(
            'tversandartsprache',
            'kVersandart',
            (int)$method->kVersandart,
            'cName',
            'cISOSprache'
        );
        foreach (Text::parseSSKint($method->cKundengruppen) as $customerGroupID) {
            if ($customerGroupID === -1) {
                $method->cKundengruppenName_arr[] = __('allCustomerGroups');
            } else {
                foreach ($customerGroups as $customerGroup) {
                    if ((int)$customerGroup->kKundengruppe === $customerGroupID) {
                        $method->cKundengruppenName_arr[] = $customerGroup->cName;
                    }
                }
            }
        }
    }

    $missingShippingClassCombis = getMissingShippingClassCombi();
    if (!empty($missingShippingClassCombis)) {
        $errorMissingShippingClassCombis = $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis)
            ->fetch('tpl_inc/versandarten_fehlende_kombis.tpl');
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMissingShippingClassCombis, 'errorMissingShippingClassCombis');
    }

    $smarty->assign('versandberechnungen', getShippingTypes())
        ->assign('versandarten', $shippingMethods)
        ->assign('waehrung', $defaultCurrency->cName);
}

if ($step === 'Zuschlagsliste') {
    $cISO = isset($_GET['cISO']) ? $db->escape($_GET['cISO']) : null;
    if (isset($_POST['cISO'])) {
        $cISO = $db->escape($_POST['cISO']);
    }
    $kVersandart = isset($_GET['kVersandart']) ? (int)$_GET['kVersandart'] : 0;
    if (isset($_POST['kVersandart'])) {
        $kVersandart = (int)$_POST['kVersandart'];
    }
    $shippingMethod = $db->select('tversandart', 'kVersandart', $kVersandart);
    $fees           = $db->selectAll(
        'tversandzuschlag',
        ['kVersandart', 'cISO'],
        [(int)$shippingMethod->kVersandart, $cISO],
        '*',
        'fZuschlag'
    );
    foreach ($fees as $item) {
        $item->zuschlagplz     = $db->selectAll(
            'tversandzuschlagplz',
            'kVersandzuschlag',
            $item->kVersandzuschlag
        );
        $item->angezeigterName = getZuschlagNames($item->kVersandzuschlag);
    }
    $smarty->assign('Versandart', $shippingMethod)
        ->assign('Zuschlaege', $fees)
        ->assign('waehrung', $defaultCurrency->cName)
        ->assign('Land', $countryHelper->getCountry($cISO));
}

$smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$taxRateKeys[0]])
    ->assign('oWaehrung', $db->select('twaehrung', 'cStandard', 'Y'))
    ->assign('step', $step)
    ->display('versandarten.tpl');
