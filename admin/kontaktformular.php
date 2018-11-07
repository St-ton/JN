<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_CONTACTFORM_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cTab     = 'config';
$step     = 'uebersicht';
if (isset($_GET['del']) && (int)$_GET['del'] > 0 && FormHelper::validateToken()) {
    Shop::Container()->getDB()->delete('tkontaktbetreff', 'kKontaktBetreff', (int)$_GET['del']);
    Shop::Container()->getDB()->delete('tkontaktbetreffsprache', 'kKontaktBetreff', (int)$_GET['del']);

    $cHinweis = 'Der Betreff wurde erfolgreich gelöscht';
}

if (isset($_POST['content']) && (int)$_POST['content'] === 1 && FormHelper::validateToken()) {
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
    $cHinweis .= 'Inhalt wurde erfolgreich gespeichert.';
    $cTab = 'content';
}

if (isset($_POST['betreff']) && (int)$_POST['betreff'] === 1 && FormHelper::validateToken()) {
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
            //einfuegen
            $kKontaktBetreff = Shop::Container()->getDB()->insert('tkontaktbetreff', $neuerBetreff);
            $cHinweis .= 'Betreff wurde erfolgreich hinzugefügt.';
        } else {
            //updaten
            $kKontaktBetreff = (int)$_POST['kKontaktBetreff'];
            Shop::Container()->getDB()->update('tkontaktbetreff', 'kKontaktBetreff', $kKontaktBetreff, $neuerBetreff);
            $cHinweis .= "Der Betreff <strong>$neuerBetreff->cName</strong> wurde erfolgreich geändert.";
        }
        $sprachen            = Sprache::getAllLanguages();
        $neuerBetreffSprache = new stdClass();
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

        $smarty->assign('hinweis', $cHinweis);
    } else {
        $error = 'Der Betreff konnte nicht gespeichert werden';
        $step  = 'betreff';
        $smarty->assign('cFehler', $error);
    }
    $cTab = 'subjects';
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_KONTAKTFORMULAR, $_POST);
    $cTab = 'config';
}

if (((isset($_GET['kKontaktBetreff']) && (int)$_GET['kKontaktBetreff'] > 0) ||
        (isset($_GET['neu']) && (int)$_GET['neu'] === 1)) && FormHelper::validateToken()
) {
    $step = 'betreff';
}

if ($step === 'uebersicht') {
    $Conf = Shop::Container()->getDB()->selectAll(
        'teinstellungenconf',
        'kEinstellungenSektion',
        CONF_KONTAKTFORMULAR,
        '*',
        'nSort'
    );
    $configCount = count($Conf);
    for ($i = 0; $i < $configCount; $i++) {
        if ($Conf[$i]->cInputTyp === 'selectbox') {
            $Conf[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$Conf[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        $setValue = Shop::Container()->getDB()->select(
            'teinstellungen',
            'kEinstellungenSektion',
            CONF_KONTAKTFORMULAR,
            'cName',
            $Conf[$i]->cWertName
        );
        $Conf[$i]->gesetzterWert = $setValue->cWert ?? null;
    }
    $neuerBetreffs = Shop::Container()->getDB()->query(
        'SELECT * FROM tkontaktbetreff ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $nCount        = count($neuerBetreffs);
    for ($i = 0; $i < $nCount; $i++) {
        $kunden = '';
        if (!$neuerBetreffs[$i]->cKundengruppen) {
            $kunden = 'alle';
        } else {
            $kKundengruppen = explode(';', $neuerBetreffs[$i]->cKundengruppen);
            foreach ($kKundengruppen as $kKundengruppe) {
                if (is_numeric($kKundengruppe)) {
                    $kndgrp = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
                    $kunden .= ' ' . $kndgrp->cName;
                }
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
           ->assign('Conf', $Conf)
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
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('Betreff', $neuerBetreff)
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($neuerBetreff))
           ->assign('Betreffname', ($neuerBetreff !== null) ? getNames($neuerBetreff->kKontaktBetreff) : null);
}

$smarty->assign('step', $step)
       ->assign('sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
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
