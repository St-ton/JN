<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
$session = Session::getInstance();
require_once PFAD_ROOT . PFAD_INCLUDES . 'kontakt_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::setPageType(PAGE_KONTAKT);
$AktuelleSeite = 'KONTAKT';
$Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_KONTAKTFORMULAR]);
$linkHelper    = LinkHelper::getInstance();
$kLink         = $linkHelper->getSpecialPageLinkKey(LINKTYP_KONTAKT);
//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$cCanonicalURL          = '';
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
if (pruefeBetreffVorhanden()) {
    $step            = 'formular';
    $fehlendeAngaben = [];
    if (isset($_POST['kontakt']) && (int)$_POST['kontakt'] === 1) {
        $fehlendeAngaben = gibFehlendeEingabenKontaktformular();
        $kKundengruppe   = Session::CustomerGroup()->getID();
        // CheckBox Plausi
        $oCheckBox       = new CheckBox();
        $fehlendeAngaben = array_merge(
            $fehlendeAngaben,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_KONTAKT, $kKundengruppe, $_POST, true)
        );
        $nReturnValue    = eingabenKorrekt($fehlendeAngaben);
        $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
        executeHook(HOOK_KONTAKT_PAGE_PLAUSI);

        if ($nReturnValue) {
            $step = 'floodschutz';
            if (!floodSchutz($Einstellungen['kontakt']['kontakt_sperre_minuten'])) {
                $oNachricht = baueKontaktFormularVorgaben();
                // CheckBox Spezialfunktion ausfuehren
                $oCheckBox->triggerSpecialFunction(
                    CHECKBOX_ORT_KONTAKT,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $oNachricht, 'oNachricht' => $oNachricht]
                )->checkLogging(CHECKBOX_ORT_KONTAKT, $kKundengruppe, $_POST, true);
                bearbeiteNachricht();
                $step = 'nachricht versendet';
            }
        }
    }
    $lang     = $_SESSION['cISOSprache'];
    $Contents = Shop::DB()->selectAll(
        'tspezialcontentsprache',
        ['nSpezialContent', 'cISOSprache'],
        [(int)SC_KONTAKTFORMULAR, $lang]
    );
    $SpezialContent = new stdClass();
    foreach ($Contents as $Content) {
        $SpezialContent->{$Content->cTyp} = $Content->cContent;
    }
    $subjects = Shop::DB()->query(
        "SELECT *
            FROM tkontaktbetreff
            WHERE (cKundengruppen = 0 
            OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                . "', REPLACE(cKundengruppen, ';', ',')) > 0) 
            ORDER BY nSort", 2
    );
    foreach ($subjects as $subject) {
        if ($subject->kKontaktBetreff > 0) {
            $localization = Shop::DB()->select(
                'tkontaktbetreffsprache',
                'kKontaktBetreff',
                (int)$subject->kKontaktBetreff,
                'cISOSprache',
                $lang
            );
            $subject->AngezeigterName = $localization->cName;
        }
    }
    $Vorgaben = baueKontaktFormularVorgaben();
    // Canonical
    $cCanonicalURL = $linkHelper->getStaticRoute('kontakt.php');
    // Metaangaben
    $oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_KONTAKT, $lang);
    $cMetaTitle       = $oMeta->cTitle;
    $cMetaDescription = $oMeta->cDesc;
    $cMetaKeywords    = $oMeta->cKeywords;
    //specific assigns
    $smarty->assign('step', $step)
           ->assign('code', generiereCaptchaCode($Einstellungen['kontakt']['kontakt_abfragen_captcha']))
           ->assign('betreffs', $subjects)
           ->assign('hinweis', isset($hinweis) ? $hinweis : null)
           ->assign('Vorgaben', $Vorgaben)
           ->assign('fehlendeAngaben', $fehlendeAngaben)
           ->assign('nAnzeigeOrt', CHECKBOX_ORT_KONTAKT);
} else {
    Jtllog::writeLog('Kein Kontaktbetreff vorhanden! Bitte im Backend unter ' .
        'Einstellungen -> Kontaktformular -> Betreffs einen Betreff hinzuf&uuml;gen.', JTLLOG_LEVEL_ERROR);
    $smarty->assign('hinweis', Shop::Lang()->get('noSubjectAvailable', 'contact'));
    $SpezialContent = new stdClass();
}

$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('Spezialcontent', $SpezialContent)
       ->assign('requestURL', isset($requestURL) ? $requestURL : null);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_KONTAKT_PAGE);
$smarty->display('contact/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
