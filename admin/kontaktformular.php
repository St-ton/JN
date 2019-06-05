<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Sprache;
use JTL\DB\ReturnType;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_CONTACTFORM_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cTab        = 'config';
$step        = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();
if (isset($_GET['del']) && (int)$_GET['del'] > 0 && Form::validateToken()) {
    Shop::Container()->getDB()->delete('tkontaktbetreff', 'kKontaktBetreff', (int)$_GET['del']);
    Shop::Container()->getDB()->delete('tkontaktbetreffsprache', 'kKontaktBetreff', (int)$_GET['del']);

    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectDelete'), 'successSubjectDelete');
}

if (isset($_POST['content']) && (int)$_POST['content'] === 1 && Form::validateToken()) {
    Shop::Container()->getDB()->delete('tspezialcontentsprache', 'nSpezialContent', SC_KONTAKTFORMULAR);
    $sprachen = Sprache::getAllLanguages();
    foreach ($sprachen as $sprache) {
        $spezialContent1                  = new stdClass();
        $spezialContent2                  = new stdClass();
        $spezialContent3                  = new stdClass();
        $spezialContent1->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent2->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent3->nSpezialContent = SC_KONTAKTFORMULAR;
        $spezialContent1->cISOSprache     = $sprache->cISO;
        $spezialContent2->cISOSprache     = $sprache->cISO;
        $spezialContent3->cISOSprache     = $sprache->cISO;
        $spezialContent1->cTyp            = 'oben';
        $spezialContent2->cTyp            = 'unten';
        $spezialContent3->cTyp            = 'titel';
        $spezialContent1->cContent        = $_POST['cContentTop_' . $sprache->cISO];
        $spezialContent2->cContent        = $_POST['cContentBottom_' . $sprache->cISO];
        $spezialContent3->cContent        = htmlspecialchars(
            $_POST['cTitle_' . $sprache->cISO],
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );

        Shop::Container()->getDB()->insert('tspezialcontentsprache', $spezialContent1);
        Shop::Container()->getDB()->insert('tspezialcontentsprache', $spezialContent2);
        Shop::Container()->getDB()->insert('tspezialcontentsprache', $spezialContent3);
        unset($spezialContent1, $spezialContent2, $spezialContent3);
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successContentSave'), 'successContentSave');
    $cTab = 'content';
}

if (isset($_POST['betreff']) && (int)$_POST['betreff'] === 1 && Form::validateToken()) {
    if ($_POST['cName'] && $_POST['cMail']) {
        $neuerBetreff        = new stdClass();
        $neuerBetreff->cName = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $neuerBetreff->cMail = $_POST['cMail'];
        if (is_array($_POST['cKundengruppen'])) {
            $neuerBetreff->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (is_array($_POST['cKundengruppen']) && in_array(0, $_POST['cKundengruppen'])) {
            $neuerBetreff->cKundengruppen = 0;
        }
        $neuerBetreff->nSort = 0;
        if ((int)$_POST['nSort'] > 0) {
            $neuerBetreff->nSort = (int)$_POST['nSort'];
        }
        $kKontaktBetreff = 0;
        if ((int)$_POST['kKontaktBetreff'] === 0) {
            $kKontaktBetreff = Shop::Container()->getDB()->insert('tkontaktbetreff', $neuerBetreff);
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSubjectCreate'), 'successSubjectCreate');
        } else {
            $kKontaktBetreff = (int)$_POST['kKontaktBetreff'];
            Shop::Container()->getDB()->update('tkontaktbetreff', 'kKontaktBetreff', $kKontaktBetreff, $neuerBetreff);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successSubjectSave'), $neuerBetreff->cName),
                'successSubjectSave'
            );
        }
        $sprachen                             = Sprache::getAllLanguages();
        $neuerBetreffSprache                  = new stdClass();
        $neuerBetreffSprache->kKontaktBetreff = $kKontaktBetreff;
        foreach ($sprachen as $sprache) {
            $neuerBetreffSprache->cISOSprache = $sprache->cISO;
            $neuerBetreffSprache->cName       = $neuerBetreff->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $neuerBetreffSprache->cName = htmlspecialchars(
                    $_POST['cName_' . $sprache->cISO],
                    ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET
                );
            }
            Shop::Container()->getDB()->delete(
                'tkontaktbetreffsprache',
                ['kKontaktBetreff', 'cISOSprache'],
                [$kKontaktBetreff, $sprache->cISO]
            );
            Shop::Container()->getDB()->insert('tkontaktbetreffsprache', $neuerBetreffSprache);
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSubjectSave'), 'errorSubjectSave');
        $step = 'betreff';
    }
    $cTab = 'subjects';
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_KONTAKTFORMULAR, $_POST),
        'saveSettings'
    );
    $cTab = 'config';
}

if (((isset($_GET['kKontaktBetreff']) && (int)$_GET['kKontaktBetreff'] > 0) ||
        (isset($_GET['neu']) && (int)$_GET['neu'] === 1)) && Form::validateToken()
) {
    $step = 'betreff';
}

if ($step === 'uebersicht') {
    $neuerBetreffs = Shop::Container()->getDB()->query(
        'SELECT * FROM tkontaktbetreff ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $nCount        = count($neuerBetreffs);
    for ($i = 0; $i < $nCount; $i++) {
        $kunden = '';
        if (!$neuerBetreffs[$i]->cKundengruppen) {
            $kunden = __('allCustomerGroups');
        } else {
            $kKundengruppen = explode(';', $neuerBetreffs[$i]->cKundengruppen);
            foreach ($kKundengruppen as $kKundengruppe) {
                if (!is_numeric($kKundengruppe)) {
                    continue;
                }
                $kndgrp  = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
                $kunden .= ' ' . $kndgrp->cName;
            }
        }
        $neuerBetreffs[$i]->Kundengruppen = $kunden;
    }
    $SpezialContent = Shop::Container()->getDB()->selectAll(
        'tspezialcontentsprache',
        'nSpezialContent',
        SC_KONTAKTFORMULAR,
        '*',
        'cTyp'
    );
    $Content        = [];
    $contentCount   = count($SpezialContent);
    for ($i = 0; $i < $contentCount; $i++) {
        $Content[$SpezialContent[$i]->cISOSprache . '_' . $SpezialContent[$i]->cTyp] = $SpezialContent[$i]->cContent;
    }
    $smarty->assign('Betreffs', $neuerBetreffs)
           ->assign('Conf', getAdminSectionSettings(CONF_KONTAKTFORMULAR))
           ->assign('Content', $Content);
}

if ($step === 'betreff') {
    $neuerBetreff = null;
    if (isset($_GET['kKontaktBetreff']) && (int)$_GET['kKontaktBetreff'] > 0) {
        $neuerBetreff = Shop::Container()->getDB()->select(
            'tkontaktbetreff',
            'kKontaktBetreff',
            (int)$_GET['kKontaktBetreff']
        );
    }

    $kundengruppen = Shop::Container()->getDB()->query(
        'SELECT * FROM tkundengruppe ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('Betreff', $neuerBetreff)
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($neuerBetreff))
           ->assign('Betreffname', ($neuerBetreff !== null) ? getNames($neuerBetreff->kKontaktBetreff) : null);
}

$smarty->assign('step', $step)
       ->assign('sprachen', Sprache::getAllLanguages())
       ->assign('cTab', $cTab)
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
    $kdgrp = explode(';', $link->cKundengruppen);
    foreach ($kdgrp as $kKundengruppe) {
        $ret[$kKundengruppe] = true;
    }

    return $ret;
}

/**
 * @param int $kKontaktBetreff
 * @return array
 */
function getNames(int $kKontaktBetreff)
{
    $namen = [];
    if (!$kKontaktBetreff) {
        return $namen;
    }
    $zanamen = Shop::Container()->getDB()->selectAll('tkontaktbetreffsprache', 'kKontaktBetreff', $kKontaktBetreff);
    $nCount  = count($zanamen);
    for ($i = 0; $i < $nCount; ++$i) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cName;
    }

    return $namen;
}
