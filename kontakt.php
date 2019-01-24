<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kontakt_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_KONTAKT);
$smarty         = Shop::Smarty();
$conf           = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_KONTAKTFORMULAR]);
$linkHelper     = Shop::Container()->getLinkService();
$kLink          = $linkHelper->getSpecialPageLinkKey(LINKTYP_KONTAKT);
$link           = $linkHelper->getPageLink($kLink);
$cCanonicalURL  = '';
$specialContent = new stdClass();
$alertHelper    = Shop::Container()->getAlertService();
$lang           = Shop::getLanguageCode();
if (Form::checkSubject()) {
    $step            = 'formular';
    $fehlendeAngaben = [];
    if (isset($_POST['kontakt']) && (int)$_POST['kontakt'] === 1) {
        $fehlendeAngaben = Form::getMissingContactFormData();
        $kKundengruppe   = \Session\Frontend::getCustomerGroup()->getID();
        $oCheckBox       = new CheckBox();
        $fehlendeAngaben = array_merge(
            $fehlendeAngaben,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_KONTAKT, $kKundengruppe, $_POST, true)
        );
        $nReturnValue    = Form::eingabenKorrekt($fehlendeAngaben);
        $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
        executeHook(HOOK_KONTAKT_PAGE_PLAUSI);

        if ($nReturnValue) {
            $step = 'floodschutz';
            if (!Form::checkFloodProtection($conf['kontakt']['kontakt_sperre_minuten'])) {
                $oNachricht = Form::baueKontaktFormularVorgaben();
                // CheckBox Spezialfunktion ausfuehren
                $oCheckBox->triggerSpecialFunction(
                    CHECKBOX_ORT_KONTAKT,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $oNachricht, 'oNachricht' => $oNachricht]
                )->checkLogging(CHECKBOX_ORT_KONTAKT, $kKundengruppe, $_POST, true);
                Form::editMessage();
                $step = 'nachricht versendet';
            }
        }
    }

    $contents = Shop::Container()->getDB()->selectAll(
        'tspezialcontentsprache',
        ['nSpezialContent', 'cISOSprache'],
        [(int)SC_KONTAKTFORMULAR, $lang]
    );
    foreach ($contents as $content) {
        $specialContent->{$content->cTyp} = $content->cContent;
    }
    $subjects = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tkontaktbetreff
            WHERE (cKundengruppen = 0 
            OR FIND_IN_SET('" . \Session\Frontend::getCustomerGroup()->getID()
        . "', REPLACE(cKundengruppen, ';', ',')) > 0) 
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($subjects as $subject) {
        if ($subject->kKontaktBetreff > 0) {
            $localization             = Shop::Container()->getDB()->select(
                'tkontaktbetreffsprache',
                'kKontaktBetreff',
                (int)$subject->kKontaktBetreff,
                'cISOSprache',
                $lang
            );
            $subject->AngezeigterName = $localization->cName;
        }
    }

    if ($step === 'nachricht versendet') {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, Shop::Lang()->get('messageSent', 'contact'), 'messageSent');
    } elseif ($step === 'floodschutz') {
        $alertHelper->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('youSentUsAMessageShortTimeBefore', 'contact'),
            'youSentUsAMessageShortTimeBefore'
        );
    }
    $cCanonicalURL = $linkHelper->getStaticRoute('kontakt.php');

    $smarty->assign('step', $step)
           ->assign('code', false)
           ->assign('betreffs', $subjects)
           ->assign('Vorgaben', Form::baueKontaktFormularVorgaben())
           ->assign('fehlendeAngaben', $fehlendeAngaben)
           ->assign('nAnzeigeOrt', CHECKBOX_ORT_KONTAKT);
} else {
    Shop::Container()->getLogService()->error('Kein Kontaktbetreff vorhanden! Bitte im Backend unter ' .
        'Einstellungen -> Kontaktformular -> Betreffs einen Betreff hinzuf&uuml;gen.');

    $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('noSubjectAvailable', 'contact'), 'noSubjectAvailable');
}

$smarty->assign('Link', $link)
       ->assign('Spezialcontent', $specialContent);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_KONTAKT_PAGE);
$smarty->display('contact/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
