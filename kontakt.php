<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Session\Frontend;

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
        $kKundengruppe   = Frontend::getCustomerGroup()->getID();
        $checkBox        = new CheckBox();
        $fehlendeAngaben = array_merge(
            $fehlendeAngaben,
            $checkBox->validateCheckBox(CHECKBOX_ORT_KONTAKT, $kKundengruppe, $_POST, true)
        );
        $nReturnValue    = Form::eingabenKorrekt($fehlendeAngaben);
        $smarty->assign('cPost_arr', Text::filterXSS($_POST));
        executeHook(HOOK_KONTAKT_PAGE_PLAUSI);

        if ($nReturnValue) {
            $step = 'floodschutz';
            if (!Form::checkFloodProtection($conf['kontakt']['kontakt_sperre_minuten'])) {
                $msg = Form::baueKontaktFormularVorgaben();
                $checkBox->triggerSpecialFunction(
                    CHECKBOX_ORT_KONTAKT,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $msg, 'oNachricht' => $msg]
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
            OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID()
        . "', REPLACE(cKundengruppen, ';', ',')) > 0) 
            ORDER BY nSort",
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($subjects as $subject) {
        $localization             = Shop::Container()->getDB()->select(
            'tkontaktbetreffsprache',
            'kKontaktBetreff',
            (int)$subject->kKontaktBetreff,
            'cISOSprache',
            $lang
        );
        $subject->AngezeigterName = $localization->cName ?? $subject->cName;
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
