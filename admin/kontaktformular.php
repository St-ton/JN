<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_CONTACTFORM_VIEW', true, true);

$cHinweis = '';
$cTab     = 'config';
$step     = 'uebersicht';
if (isset($_GET['del']) && intval($_GET['del']) > 0 && validateToken()) {
    Shop::DB()->delete('tkontaktbetreff', 'kKontaktBetreff', (int)$_GET['del']);
    Shop::DB()->delete('tkontaktbetreffsprache', 'kKontaktBetreff', (int)$_GET['del']);

    $cHinweis = 'Der Betreff wurde erfolgreich gel&ouml;scht';
}

if (isset($_POST['content']) && intval($_POST['content']) === 1 && validateToken()) {
    Shop::DB()->delete('tspezialcontentsprache', 'nSpezialContent', SC_KONTAKTFORMULAR);
    $sprachen = gibAlleSprachen();
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
        $spezialContent3->cContent        = $_POST['cTitle_' . $sprache->cISO];

        Shop::DB()->insert('tspezialcontentsprache', $spezialContent1);
        Shop::DB()->insert('tspezialcontentsprache', $spezialContent2);
        Shop::DB()->insert('tspezialcontentsprache', $spezialContent3);
        unset($spezialContent1);
        unset($spezialContent2);
        unset($spezialContent3);
    }
    $cHinweis .= 'Inhalt wurde erfolgreich gespeichert.';
    $cTab = 'content';
}

if (isset($_POST['betreff']) && intval($_POST['betreff']) === 1 && validateToken()) {
    if ($_POST['cName'] && $_POST['cMail']) {
        $neuerBetreff        = new stdClass();
        $neuerBetreff->cName = $_POST['cName'];
        $neuerBetreff->cMail = $_POST['cMail'];
        if (is_array($_POST['cKundengruppen'])) {
            $neuerBetreff->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (is_array($_POST['cKundengruppen']) && in_array(0, $_POST['cKundengruppen'])) {
            $neuerBetreff->cKundengruppen = 0;
        }
        $neuerBetreff->nSort = 0;
        if (intval($_POST['nSort']) > 0) {
            $neuerBetreff->nSort = intval($_POST['nSort']);
        }

        $kKontaktBetreff = 0;

        if (intval($_POST['kKontaktBetreff']) === 0) {
            //einfuegen
            $kKontaktBetreff = Shop::DB()->insert('tkontaktbetreff', $neuerBetreff);
            $cHinweis .= 'Betreff wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kKontaktBetreff = intval($_POST['kKontaktBetreff']);
            Shop::DB()->update('tkontaktbetreff', 'kKontaktBetreff', $kKontaktBetreff, $neuerBetreff);
            $cHinweis .= "Der Betreff <strong>$neuerBetreff->cName</strong> wurde erfolgreich ge&auml;ndert.";
        }
        $sprachen = gibAlleSprachen();
        if (!isset($neuerBetreffSprache)) {
            $neuerBetreffSprache = new stdClass();
        }
        $neuerBetreffSprache->kKontaktBetreff = $kKontaktBetreff;
        foreach ($sprachen as $sprache) {
            $neuerBetreffSprache->cISOSprache = $sprache->cISO;
            $neuerBetreffSprache->cName       = $neuerBetreff->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $neuerBetreffSprache->cName = $_POST['cName_' . $sprache->cISO];
            }
            Shop::DB()->delete('tkontaktbetreffsprache', array('kKontaktBetreff', 'cISOSprache'), array((int)$kKontaktBetreff, $sprache->cISO));
            Shop::DB()->insert('tkontaktbetreffsprache', $neuerBetreffSprache);
        }

        $smarty->assign('hinweis', $cHinweis);
    } else {
        $error = 'Der Betreff konnte nicht gespeichert werden';
        $step  = 'betreff';
        $smarty->assign('cFehler', $error);
    }
    $cTab = 'subjects';
}

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_KONTAKTFORMULAR, $_POST);
    $cTab = 'config';
}

if (((isset($_GET['kKontaktBetreff']) && intval($_GET['kKontaktBetreff']) > 0) || (isset($_GET['neu']) && intval($_GET['neu']) === 1)) && validateToken()) {
    $step = 'betreff';
}

if ($step === 'uebersicht') {
    $Conf = Shop::DB()->query("
        SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = " . CONF_KONTAKTFORMULAR . "
            ORDER BY nSort", 2
    );
    $configCount = count($Conf);
    for ($i = 0; $i < $configCount; $i++) {
        if ($Conf[$i]->cInputTyp === 'selectbox') {
            $Conf[$i]->ConfWerte = Shop::DB()->query("
                SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = " . (int)$Conf[$i]->kEinstellungenConf . "
                    ORDER BY nSort", 2
            );
        }
        $setValue = Shop::DB()->query("
            SELECT cWert
                FROM teinstellungen
                WHERE kEinstellungenSektion = " . CONF_KONTAKTFORMULAR . "
                    AND cName = '" . $Conf[$i]->cWertName . "'", 1);
        $Conf[$i]->gesetzterWert = (isset($setValue->cWert) ? $setValue->cWert : null);
    }
    $neuerBetreffs = Shop::DB()->query("SELECT * FROM tkontaktbetreff ORDER BY nSort", 2);
    $nCount        = count($neuerBetreffs);
    for ($i = 0; $i < $nCount; $i++) {
        $kunden = '';
        if (!$neuerBetreffs[$i]->cKundengruppen) {
            $kunden = 'alle';
        } else {
            $kKundengruppen = explode(';', $neuerBetreffs[$i]->cKundengruppen);
            if (is_array($kKundengruppen)) {
                foreach ($kKundengruppen as $kKundengruppe) {
                    if (is_numeric($kKundengruppe)) {
                        $kndgrp = Shop::DB()->query("SELECT cName FROM tkundengruppe WHERE kKundengruppe = " . (int)$kKundengruppe, 1);
                        $kunden .= ' ' . $kndgrp->cName;
                    }
                }
            }
        }
        $neuerBetreffs[$i]->Kundengruppen = $kunden;
    }
    $SpezialContent = Shop::DB()->query("
        SELECT *
            FROM tspezialcontentsprache
            WHERE nSpezialContent = " . SC_KONTAKTFORMULAR . "
            ORDER BY cTyp", 2
    );
    $Content      = array();
    $contentCount = count($SpezialContent);
    for ($i = 0; $i < $contentCount; $i++) {
        $Content[$SpezialContent[$i]->cISOSprache . '_' . $SpezialContent[$i]->cTyp] = $SpezialContent[$i]->cContent;
    }
    $smarty->assign('Betreffs', $neuerBetreffs)
           ->assign('Conf', $Conf)
           ->assign('Content', $Content);
}

if ($step === 'betreff') {
    $neuerBetreff = null;
    if (isset($_GET['kKontaktBetreff']) && intval($_GET['kKontaktBetreff']) > 0) {
        $neuerBetreff = Shop::DB()->select('tkontaktbetreff', 'kKontaktBetreff', (int)$_GET['kKontaktBetreff']);
    }

    $kundengruppen = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
    $smarty->assign('Betreff', $neuerBetreff)
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($neuerBetreff))
           ->assign('Betreffname', ($neuerBetreff !== null) ? getNames($neuerBetreff->kKontaktBetreff) : null);
}

$smarty->assign('step', $step)
       ->assign('sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('cTab', $cTab)
       ->display('kontaktformular.tpl');

/**
 * @param object $link
 * @return array
 */
function getGesetzteKundengruppen($link)
{
    $ret = array();
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
function getNames($kKontaktBetreff)
{
    $kKontaktBetreff = (int)$kKontaktBetreff;
    $namen           = array();
    if (!$kKontaktBetreff) {
        return $namen;
    }
    $zanamen = Shop::DB()->query("SELECT * FROM tkontaktbetreffsprache WHERE kKontaktBetreff = " . $kKontaktBetreff, 2);
    $nCount  = count($zanamen);
    for ($i = 0; $i < $nCount; $i++) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cName;
    }

    return $namen;
}
