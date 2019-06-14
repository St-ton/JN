<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use function Functional\map;
use function Functional\reindex;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_CONTACTFORM_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$tab         = 'config';
$step        = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$languages   = LanguageHelper::getAllLanguages();
if (isset($_GET['del']) && (int)$_GET['del'] > 0 && Form::validateToken()) {
    $db->delete('tkontaktbetreff', 'kKontaktBetreff', (int)$_GET['del']);
    $db->delete('tkontaktbetreffsprache', 'kKontaktBetreff', (int)$_GET['del']);

    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectDelete'), 'successSubjectDelete');
}

if (isset($_POST['content']) && (int)$_POST['content'] === 1 && Form::validateToken()) {
    $db->delete('tspezialcontentsprache', 'nSpezialContent', SC_KONTAKTFORMULAR);
    foreach ($languages as $language) {
        $code                             = $language->getIso();
        $spezialContent1                  = new stdClass();
        $spezialContent2                  = new stdClass();
        $spezialContent3                  = new stdClass();
        $spezialContent1->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent2->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent3->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent1->cISOSprache     = $code;
        $spezialContent2->cISOSprache     = $code;
        $spezialContent3->cISOSprache     = $code;
        $spezialContent1->cTyp            = 'oben';
        $spezialContent2->cTyp            = 'unten';
        $spezialContent3->cTyp            = 'titel';
        $spezialContent1->cContent        = $_POST['cContentTop_' . $code];
        $spezialContent2->cContent        = $_POST['cContentBottom_' . $code];
        $spezialContent3->cContent        = htmlspecialchars(
            $_POST['cTitle_' . $code],
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );

        $db->insert('tspezialcontentsprache', $spezialContent1);
        $db->insert('tspezialcontentsprache', $spezialContent2);
        $db->insert('tspezialcontentsprache', $spezialContent3);
        unset($spezialContent1, $spezialContent2, $spezialContent3);
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successContentSave'), 'successContentSave');
    $tab = 'content';
}

if (isset($_POST['betreff']) && (int)$_POST['betreff'] === 1 && Form::validateToken()) {
    if ($_POST['cName'] && $_POST['cMail']) {
        $newSubject        = new stdClass();
        $newSubject->cName = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $newSubject->cMail = $_POST['cMail'];
        if (is_array($_POST['cKundengruppen'])) {
            $newSubject->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (is_array($_POST['cKundengruppen']) && in_array(0, $_POST['cKundengruppen'])) {
            $newSubject->cKundengruppen = 0;
        }
        $newSubject->nSort = 0;
        if ((int)$_POST['nSort'] > 0) {
            $newSubject->nSort = (int)$_POST['nSort'];
        }
        $kKontaktBetreff = 0;
        if ((int)$_POST['kKontaktBetreff'] === 0) {
            $kKontaktBetreff = $db->insert('tkontaktbetreff', $newSubject);
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectCreate'), 'successSubjectCreate');
        } else {
            $kKontaktBetreff = (int)$_POST['kKontaktBetreff'];
            $db->update('tkontaktbetreff', 'kKontaktBetreff', $kKontaktBetreff, $newSubject);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successSubjectSave'), $newSubject->cName),
                'successSubjectSave'
            );
        }
        $localized                  = new stdClass();
        $localized->kKontaktBetreff = $kKontaktBetreff;
        foreach ($languages as $language) {
            $code                   = $language->getIso();
            $localized->cISOSprache = $code;
            $localized->cName       = $newSubject->cName;
            if ($_POST['cName_' . $code]) {
                $localized->cName = htmlspecialchars(
                    $_POST['cName_' . $code],
                    ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET
                );
            }
            $db->delete(
                'tkontaktbetreffsprache',
                ['kKontaktBetreff', 'cISOSprache'],
                [$kKontaktBetreff, $code]
            );
            $db->insert('tkontaktbetreffsprache', $localized);
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSubjectSave'), 'errorSubjectSave');
        $step = 'betreff';
    }
    $tab = 'subjects';
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_KONTAKTFORMULAR, $_POST),
        'saveSettings'
    );
    $tab = 'config';
}

if (((isset($_GET['kKontaktBetreff']) && (int)$_GET['kKontaktBetreff'] > 0)
        || (isset($_GET['neu']) && (int)$_GET['neu'] === 1)) && Form::validateToken()
) {
    $step = 'betreff';
}

if ($step === 'uebersicht') {
    $subjects = $db->query(
        'SELECT * FROM tkontaktbetreff ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($subjects as $subject) {
        $groups = '';
        if (!$subject->cKundengruppen) {
            $groups = __('alle');
        } else {
            foreach (explode(';', $subject->cKundengruppen) as $customerGroupID) {
                if (!is_numeric($customerGroupID)) {
                    continue;
                }
                $kndgrp  = $db->select('tkundengruppe', 'kKundengruppe', (int)$customerGroupID);
                $groups .= ' ' . $kndgrp->cName;
            }
        }
        $subject->Kundengruppen = $groups;
    }
    $specialContent = $db->selectAll(
        'tspezialcontentsprache',
        'nSpezialContent',
        SC_KONTAKTFORMULAR,
        '*',
        'cTyp'
    );
    $content        = [];
    foreach ($specialContent as $item) {
        $content[$item->cISOSprache . '_' . $item->cTyp] = $item->cContent;
    }
    $smarty->assign('Betreffs', $subjects)
        ->assign('Conf', getAdminSectionSettings(CONF_KONTAKTFORMULAR))
        ->assign('Content', $content);
}

if ($step === 'betreff') {
    $newSubject = null;
    if (isset($_GET['kKontaktBetreff']) && (int)$_GET['kKontaktBetreff'] > 0) {
        $newSubject = $db->select(
            'tkontaktbetreff',
            'kKontaktBetreff',
            (int)$_GET['kKontaktBetreff']
        );
    }

    $customerGroups = $db->query(
        'SELECT * FROM tkundengruppe ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('Betreff', $newSubject)
        ->assign('kundengruppen', $customerGroups)
        ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($newSubject))
        ->assign('Betreffname', ($newSubject !== null) ? getNames($newSubject->kKontaktBetreff) : null);
}

$smarty->assign('step', $step)
    ->assign('cTab', $tab)
    ->display('kontaktformular.tpl');

/**
 * @param object $link
 * @return array
 */
function getGesetzteKundengruppen($link)
{
    $ret = [];
    if (!isset($link->cKundengruppen) || !$link->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    foreach (array_filter(explode(';', $link->cKundengruppen)) as $customerGroupID) {
        $ret[$customerGroupID] = true;
    }

    return $ret;
}

/**
 * @param int $id
 * @return array
 */
function getNames(int $id)
{
    $data = Shop::Container()->getDB()->selectAll('tkontaktbetreffsprache', 'kKontaktBetreff', $id);

    return map(reindex($data, function ($e) {
        return $e->cISOSprache;
    }), function ($e) {
        return $e->cName;
    });
}
