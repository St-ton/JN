<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\PHPSettings;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Helpers\Text;
use JTL\TrustedShops;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';

Shop::Container()->getGetText()->loadConfigLocales(true, true);

define('PARTNER_PACKAGE', 'JTL');
define('SHOP_SOFTWARE', 'JTL');

$oAccount->permission('ORDER_TRUSTEDSHOPS_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$step        = 'uebersicht';
$conf        = Shop::getSettings([CONF_TRUSTEDSHOPS]);
$alertHelper = Shop::Container()->getAlertService();

setzeSpracheTrustedShops();

if (isset($_POST['kaeuferschutzeinstellungen'])
    && (int)$_POST['kaeuferschutzeinstellungen'] === 1
    && Form::validateToken()
) {
    if (isset($_POST['delZertifikat'])) {
        $ts = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
        if ($ts->oZertifikat->kTrustedShopsZertifikat > 0) {
            if ($ts->loescheTrustedShopsZertifikat($ts->oZertifikat->kTrustedShopsZertifikat)) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successDelete'), 'successDelete');

                Shop::Container()->getDB()->query(
                    'DELETE FROM teinstellungen
                        WHERE kEinstellungenSektion = ' . CONF_TRUSTEDSHOPS . "
                            AND cName = 'trustedshops_nutzen'",
                    ReturnType::DEFAULT
                );
                $aktWert                        = new stdClass();
                $aktWert->cWert                 = 'N';
                $aktWert->cName                 = 'trustedshops_nutzen';
                $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
                Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCertificateNotFound'), 'errorCertificateNotFound');
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCertificateNotFound'), 'errorCertificateNotFound');
        }
    } else {
        $cPreStatus  = $conf['trustedshops']['trustedshops_nutzen'];
        $confData    = Shop::Container()->getDB()->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = ' . CONF_TRUSTEDSHOPS . "
                    AND cConf = 'Y'
                    AND cWertName != 'trustedshops_kundenbewertung_anzeigen'
                ORDER BY nSort",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $configCount = count($confData);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$confData[$i]->cWertName];
            $aktWert->cName                 = $confData[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
            switch ($confData[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)$aktWert->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
                case 'listbox':
                    bearbeiteListBox($aktWert->cWert, $confData[$i]->cWertName, CONF_TRUSTEDSHOPS);
                    break;
            }

            if ($confData[$i]->cInputTyp !== 'listbox') {
                Shop::Container()->getDB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [CONF_TRUSTEDSHOPS, $confData[$i]->cWertName]
                );
                Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
            }
            $settings = Shopsetting::getInstance();
            $settings->reset();
        }

        if (mb_strlen($_POST['tsId']) > 0
            && (mb_strlen($_POST['wsUser']) > 0
                && mb_strlen($_POST['wsPassword']) > 0
                || $_POST['eType'] === TS_BUYERPROT_CLASSIC)
        ) {
            $cert              = new stdClass();
            $cert->cTSID       = Text::htmlentities(Text::filterXSS(trim($_POST['tsId'])));
            $cert->cWSUser     = Text::htmlentities(Text::filterXSS($_POST['wsUser']));
            $cert->cWSPasswort = Text::htmlentities(Text::filterXSS($_POST['wsPassword']));
            $cert->cISOSprache = $_SESSION['TrustedShops']->oSprache->cISOSprache;
            $cert->nAktiv      = 0;
            $cert->eType       = Text::htmlentities(Text::filterXSS($_POST['eType']));
            $cert->dErstellt   = 'NOW()';

            $ts = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);

            $nReturnValue = (mb_strlen($ts->kTrustedShopsZertifikat) > 0)
                ? $ts->speicherTrustedShopsZertifikat($cert, $ts->kTrustedShopsZertifikat)
                : $ts->speicherTrustedShopsZertifikat($cert);

            mappeTSFehlerCode($nReturnValue);
        } elseif ($cPreStatus === 'Y') {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        }

        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        unset($confData);
    }
} elseif (isset($_POST['kaeuferschutzupdate'])
    && (int)$_POST['kaeuferschutzupdate'] === 1
    && Form::validateToken()
) {
    // Kaeuferprodukte updaten
    $ts = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    //$oZertifikat = $oTrustedShops->gibTrustedShopsZertifikatISO($_SESSION['TrustedShops']->oSprache->cISOSprache);

    if ($ts->oZertifikat->kTrustedShopsZertifikat > 0 && $ts->oZertifikat->nAktiv == 1) {
        $ts->holeKaeuferschutzProdukte($ts->oZertifikat->kTrustedShopsZertifikat);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBuyerProtectSave'), 'successBuyerProtectSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBuyerProtectSave'), 'errorBuyerProtectSave');
    }
} elseif (isset($_POST['kundenbewertungeinstellungen'])
    && (int)$_POST['kundenbewertungeinstellungen'] === 1
    && Form::validateToken()
) {
    // Kundenbewertung Einstellungen
    $ts         = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    $cPreStatus = $conf['trustedshops']['trustedshops_kundenbewertung_anzeigen'];

    $confData    = Shop::Container()->getDB()->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf', 'cWertName'],
        [CONF_TRUSTEDSHOPS, 'Y', 'trustedshops_kundenbewertung_anzeigen'],
        '*',
        'nSort'
    );
    $configCount = count($confData);
    for ($i = 0; $i < $configCount; $i++) {
        $aktWert                        = new stdClass();
        $aktWert->cWert                 = $_POST[$confData[$i]->cWertName];
        $aktWert->cName                 = $confData[$i]->cWertName;
        $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
        switch ($confData[$i]->cInputTyp) {
            case 'kommazahl':
                $aktWert->cWert = (float)$aktWert->cWert;
                break;
            case 'zahl':
            case 'number':
                $aktWert->cWert = (int)$aktWert->cWert;
                break;
            case 'text':
                $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                break;
            case 'listbox':
                bearbeiteListBox($aktWert->cWert, $confData[$i]->cWertName, CONF_TRUSTEDSHOPS);
                break;
        }

        if ($confData[$i]->cInputTyp !== 'listbox') {
            Shop::Container()->getDB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_TRUSTEDSHOPS, $confData[$i]->cWertName]
            );
            Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
        }
    }
    $settings = Shopsetting::getInstance();
    $settings->reset();
    $conf = Shop::getSettings([CONF_TRUSTEDSHOPS]);
    if ($conf['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'N') {
        $ts->aenderKundenbewertungsstatusDB(0, $_SESSION['TrustedShops']->oSprache->cISOSprache);
        $ts->aenderKundenbewertungsstatus(0, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    }

    if (mb_strlen($_POST['kb-tsId']) > 0) {
        $ts->aenderKundenbewertungtsIDDB(
            trim($_POST['kb-tsId']),
            $_SESSION['TrustedShops']->oSprache->cISOSprache
        );
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTSIDMissing'), 'errorTSIDMissing');
    }
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
} elseif (isset($_POST['kundenbewertungupdate']) && (int)$_POST['kundenbewertungupdate'] === 1) {
    if (isset($_POST['tsKundenbewertungActive']) || isset($_POST['tsKundenbewertungDeActive'])) {
        $nStatus = 0;
        if (isset($_POST['tsKundenbewertungActive'])) {
            $nStatus = 1;
        }
        $ts        = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
        $tscRating = $ts->holeKundenbewertungsstatus(
            $_SESSION['TrustedShops']->oSprache->cISOSprache
        );

        if (mb_strlen($tscRating->cTSID) > 0) {
            $nReturnValue = $ts->aenderKundenbewertungsstatus(
                $tscRating->cTSID,
                $nStatus,
                $_SESSION['TrustedShops']->oSprache->cISOSprache
            );
            if ($nReturnValue === 1) {
                $filename = $tscRating->cTSID . '.gif';
                $ts::ladeKundenbewertungsWidgetNeu($filename);
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successStatusSave'), 'successStatusSave');
            } elseif ($nReturnValue === 2) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorStatusSave'), 'errorStatusSave');
            } elseif ($nReturnValue === 3) {
                // Wurde die TS-ID vielleicht schon in einer anderen Sprache benutzt?
                if ($ts->pruefeKundenbewertungsstatusAndereSprache(
                    $tscRating->cTSID,
                    $_SESSION['TrustedShops']->oSprache->cISOSprache
                )) {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTSIDOtherLang'), 'errorTSIDOtherLang');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTSIDInvalid'), 'errorTSIDInvalid');
                }
            } elseif ($nReturnValue === 4) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNotRegistered'), 'errorNotRegistered');
            } elseif ($nReturnValue === 5) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorWrongPasswordUser'), 'errorWrongPasswordUser');
            } elseif ($nReturnValue === 6) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTSActivateFirst'), 'errorTSActivateFirst');
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerRatingNotFound'), 'errorCustomerRatingNotFound');
        }
    }
} elseif (isset($_GET['whatis']) && (int)$_GET['whatis'] === 1) { // Infoseite anzeigen
    $step = 'info';
} elseif (isset($_GET['whatisrating']) && (int)$_GET['whatisrating'] === 1) { // Infoseite Kundenbewertung anzeigen
    $step = 'info_kundenbewertung';
}
if ($step === 'uebersicht') {
    $confData    = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = ' . CONF_TRUSTEDSHOPS . '
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $configCount = count($confData);
    for ($i = 0; $i < $configCount; $i++) {
        Shop::Container()->getGetText()->localizeConfig($confData[$i]);

        if ($confData[$i]->cInputTyp === 'selectbox') {
            $confData[$i]->ConfWerte = Shop::Container()->getDB()->query(
                'SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = ' . (int)$confData[$i]->kEinstellungenConf . '
                    ORDER BY nSort',
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getGetText()->localizeConfigValues($confData[$i], $confData[$i]->ConfWerte);
        } elseif ($confData[$i]->cInputTyp === 'listbox') {
            $confData[$i]->ConfWerte = Shop::Container()->getDB()->query(
                'SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }

        if ($confData[$i]->cInputTyp === 'listbox') {
            $oSetValue                   = Shop::Container()->getDB()->query(
                'SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = ' . CONF_TRUSTEDSHOPS . "
                        AND cName = '" . $confData[$i]->cWertName . "'",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $confData[$i]->gesetzterWert = $oSetValue;
        } else {
            $oSetValue                   = Shop::Container()->getDB()->query(
                'SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = ' . CONF_TRUSTEDSHOPS . "
                        AND cName = '" . $confData[$i]->cWertName . "'",
                ReturnType::SINGLE_OBJECT
            );
            $confData[$i]->gesetzterWert = $oSetValue->cWert ?? null;
        }
    }

    $smarty->assign('oConfig_arr', $confData);

    $ts      = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    $swParam = '?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE;

    if (isset($_POST['kaeuferschutzupdate'], $_POST['tsupdate'])
        && (int)$_POST['kaeuferschutzupdate'] === 1
        && $conf['trustedshops']['trustedshops_nutzen'] === 'Y'
    ) {
        $smarty->assign('oKaeuferschutzProdukteDB', $ts->oKaeuferschutzProdukteDB)
               ->assign(
                   'oZertifikat',
                   $ts->gibTrustedShopsZertifikatISO($_SESSION['TrustedShops']->oSprache->cISOSprache)
               );

        $tscRating = $ts->holeKundenbewertungsstatus(
            $_SESSION['TrustedShops']->oSprache->cISOSprache
        );
        if ($tscRating) {
            $smarty->assign('oTrustedShopsKundenbewertung', $tscRating);
        }

        // Kundenbewertungs URL zur Uebersicht
        $ratingURLs = [
            'de' => 'https://www.trustedshops.de/shopbetreiber/' . $swParam,
            'en' => 'https://www.trustedshops.co.uk/merchants/partners/' . $swParam,
            'fr' => 'https://www.trustedshops.fr/marchands/partenaires/' . $swParam,
            'es' => 'https://www.trustedshops.es/comerciante/partner/' . $swParam,
            'nl' => '',
            'it' => '',
            'pl' => 'https://www.trustedshops.pl/handlowcy/' . $swParam
        ];
    }

    if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y') {
        $smarty->assign('oKaeuferschutzProdukteDB', $ts->oKaeuferschutzProdukteDB);
    }

    $smarty->assign('Einstellungen', $conf)
           ->assign('oZertifikat', $ts->oZertifikat);

    // Kundenbewertungsstatus
    $tscRating = $ts->holeKundenbewertungsstatus(
        $_SESSION['TrustedShops']->oSprache->cISOSprache
    );
    if ($conf['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
        $smarty->assign('oTrustedShopsKundenbewertung', $tscRating);
    }
    $ratingURLs = [
        'de' => 'https://www.trustedshops.de/shopbetreiber/' . $swParam,
        'en' => 'https://www.trustedshops.co.uk/merchants/partners/' . $swParam,
        'fr' => 'https://www.trustedshops.fr/marchands/partenaires/' . $swParam,
        'es' => 'https://www.trustedshops.es/comerciante/partner/' . $swParam,
        'nl' => '',
        'it' => '',
        'pl' => 'https://www.trustedshops.pl/handlowcy/' . $swParam
    ];

    $customerRatingURLs = [];
    if (isset($tscRating->cTSID) && mb_strlen($tscRating->cTSID) > 0) {
        $customerRatingURLs = [
            'de' => 'https://www.trustedshops.com/bewertung/info_' . $tscRating->cTSID . '.html',
            'en' => 'https://www.trustedshops.com/buyerrating/info_' . $tscRating->cTSID . '.html',
            'fr' => 'https://www.trustedshops.com/evaluation/info_' . $tscRating->cTSID . '.html',
            'es' => 'https://www.trustedshops.com/evaluacion/info_' . $tscRating->cTSID . '.html',
            'pl' => 'https://www.trustedshops.pl/opinia/info_' . $tscRating->cTSID . '.html',
            'nl' => 'https://www.trustedshops.nl/verkopersbeoordeling/info_' . $tscRating->cTSID . '.html',
            'it' => 'https://www.trustedshops.it/valutazione-del-negozio/info_' . $tscRating->cTSID . '.html'
        ];
    }

    $langMappings = [
        'de' => 'Deutsch',
        'en' => 'Englisch',
        'fr' => 'Französisch',
        'nl' => 'Niederländisch',
        'it' => 'Italienisch',
        'pl' => 'Polnisch',
        'es' => 'Spanisch'
    ];
    $languages    = [];
    foreach ($langMappings as $i => $lang) {
        $languages[$i]                      = new stdClass();
        $languages[$i]->cISOSprache         = $i;
        $languages[$i]->cNameSprache        = $langMappings[$i];
        $languages[$i]->cURLKundenBewertung = $ratingURLs[$i];
        if (count($customerRatingURLs) > 0) {
            $languages[$i]->cURLKundenBewertungUebersicht = $customerRatingURLs[$i];
        }
    }
    $smarty->assign('Sprachen', $languages);
} elseif ($step === 'info') {
    $smarty->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
} elseif ($step === 'info_kundenbewertung') {
    $smarty->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
}
$smarty->assign('TS_BUYERPROT_CLASSIC', TS_BUYERPROT_CLASSIC)
       ->assign('TS_BUYERPROT_EXCELLENCE', TS_BUYERPROT_EXCELLENCE)
       ->assign('bAllowfopen', PHPSettings::checkAllowFopen())
       ->assign('bSOAP', PHPSettings::checkSOAP())
       ->assign('bCURL', PHPSettings::checkCURL())
       ->assign('step', $step)
       ->display('trustedshops.tpl');

/**
 * @param int $nReturnValue
 */
function mappeTSFehlerCode(int $nReturnValue)
{
    $alertHelper = Shop::Container()->getAlertService();
    $errorMsg    = '';
    if ($nReturnValue === -1) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSaveAddTSBox'), 'successSaveAddTSBox');
    } elseif ($nReturnValue === 1) {
        // Fehlende Sprache + TSID
        $errorMsg = __('errorFillRequired');
    } elseif ($nReturnValue === 2) {
        // Das Zertifikat existiert nicht
        $errorMsg = __('errorCertificateInvalid');
    } elseif ($nReturnValue === 3) {
        // Das Zertifikat ist abgelaufen
        $errorMsg = __('errorCertificateExpired');
    } elseif ($nReturnValue === 4) {
        // Das Zertifikat ist gesperrt
        $errorMsg = __('errorCertificateBlocked');
    } elseif ($nReturnValue === 5) {
        // Shop befindet sich in der Zertifizierung
        $errorMsg = __('errorCertificatePending');
    } elseif ($nReturnValue === 6) {
        // Keine Excellence-Variante mit Kaeuferschutz im Checkout-Prozess
        $errorMsg = __('errorCertificateNoExcellence');
    } elseif ($nReturnValue === 7) {
        // Ungueltige Sprache fuer gewaehlte TS-ID
        $errorMsg = __('errorLangMismatch');
    } elseif ($nReturnValue === 8) {
        // Benutzername & Passwort ungueltig
        $errorMsg = __('errorWebServiceLoginInvalid');
    } elseif ($nReturnValue === 9) {
        // Zertifikat konnte nicht gespeichert werden
        $errorMsg = __('errorCertificateSave');
    } elseif ($nReturnValue === 10) {
        // Falsche Kaeuferschutzvariante
        $errorMsg = __('errorTSIDBuyerProtectionMismatch');
    } elseif ($nReturnValue === 11) {
        // SOAP Fehler
        $errorMsg = '';
    }
    $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMsg, 'errorCodeTS');
}
