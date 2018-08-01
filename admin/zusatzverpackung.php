<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_PACKAGE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
$cHinweis     = '';
$cFehler      = '';
$step         = 'zusatzverpackung';
$oSprache_arr = Sprache::getAllLanguages();
$action       = '';

if (FormHelper::validateToken()) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (isset($_GET['kVerpackung']) && RequestHelper::verifyGPCDataInt('kVerpackung') >= 0) {
        $action = 'edit';
    }
}

if ($action === 'save') {
    $kVerpackung                      = (int)$_POST['kVerpackung'];
    $kKundengruppe_arr                = $_POST['kKundengruppe'] ?? null;
    $oVerpackung = new stdClass();
    $oVerpackung->fBrutto             = (float)str_replace(',', '.', $_POST['fBrutto'] ?? 0);
    $oVerpackung->fMindestbestellwert = (float)str_replace(',', '.', $_POST['fMindestbestellwert'] ?? 0);
    $oVerpackung->fKostenfrei         = (float)str_replace(',', '.', $_POST['fKostenfrei'] ?? 0);
    $oVerpackung->kSteuerklasse       = isset($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : 0;
    $oVerpackung->nAktiv              = isset($_POST['nAktiv']) ? (int)$_POST['nAktiv'] : 0;
    $oVerpackung->cName               = htmlspecialchars(
        strip_tags(trim($_POST['cName_' . $oSprache_arr[0]->cISO])),
        ENT_COMPAT | ENT_HTML401,
        JTL_CHARSET
    );

    if (!(isset($_POST['cName_' . $oSprache_arr[0]->cISO]) && strlen($_POST['cName_' . $oSprache_arr[0]->cISO]) > 0)) {
        $cFehler .= 'Fehler: Bitte geben Sie der Verpackung einen Namen.<br />';
    }
    if (!(is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0)) {
        $cFehler .= 'Fehler: Bitte wählen Sie mindestens eine Kundengruppe aus.<br />';
    }

    if($cFehler !== '') {
        holdInputOnError($oVerpackung, $kKundengruppe_arr, $kVerpackung, $smarty);
        $action = 'edit';
    } else {
        if ($kKundengruppe_arr[0] == '-1') {
            $oVerpackung->cKundengruppe = '-1';
        } else {
            $oVerpackung->cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
        }
        // Update?
        if ($kVerpackung > 0) {
            Shop::Container()->getDB()->query(
                "DELETE tverpackung, tverpackungsprache
                FROM tverpackung
                LEFT JOIN tverpackungsprache 
                    ON tverpackungsprache.kVerpackung = tverpackung.kVerpackung
                WHERE tverpackung.kVerpackung = " . $kVerpackung,
                \DB\ReturnType::AFFECTED_ROWS
            );
            $oVerpackung->kVerpackung = $kVerpackung;
            Shop::Container()->getDB()->insert('tverpackung', $oVerpackung);
        } else {
            $kVerpackung = Shop::Container()->getDB()->insert('tverpackung', $oVerpackung);
        }
        // In tverpackungsprache adden
        foreach ($oSprache_arr as $oSprache) {
            $oVerpackungSprache                = new stdClass();
            $oVerpackungSprache->kVerpackung   = $kVerpackung;
            $oVerpackungSprache->cISOSprache   = $oSprache->cISO;
            $oVerpackungSprache->cName         = !empty($_POST['cName_' . $oSprache->cISO])
                ? htmlspecialchars($_POST['cName_' . $oSprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars($_POST['cName_' . $oSprache_arr[0]->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oVerpackungSprache->cBeschreibung = !empty($_POST['cBeschreibung_' . $oSprache->cISO])
                ? htmlspecialchars($_POST['cBeschreibung_' . $oSprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars($_POST['cBeschreibung_' . $oSprache_arr[0]->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            Shop::Container()->getDB()->insert('tverpackungsprache', $oVerpackungSprache);
        }
        $cHinweis .= 'Die Verpackung "' . $_POST['cName_' .
            $oSprache_arr[0]->cISO] . '" wurde erfolgreich gespeichert.<br />';
    }
} elseif ($action === 'edit' && RequestHelper::verifyGPCDataInt('kVerpackung') > 0) { // Editieren
    $kVerpackung = RequestHelper::verifyGPCDataInt('kVerpackung');
    $oVerpackung = Shop::Container()->getDB()->select('tverpackung', 'kVerpackung', $kVerpackung);

    if (isset($oVerpackung->kVerpackung) && $oVerpackung->kVerpackung > 0) {
        $oVerpackung->oSprach_arr = [];
        $oVerpackungSprach_arr    = Shop::Container()->getDB()->selectAll(
            'tverpackungsprache',
            'kVerpackung',
            $kVerpackung,
            'cISOSprache, cName, cBeschreibung'
        );
        foreach ($oVerpackungSprach_arr as $oVerpackungSprach) {
            $oVerpackung->oSprach_arr[$oVerpackungSprach->cISOSprache] = $oVerpackungSprach;
        }
        $oKundengruppe                  = gibKundengruppeObj($oVerpackung->cKundengruppe);
        $oVerpackung->kKundengruppe_arr = $oKundengruppe->kKundengruppe_arr;
        $oVerpackung->cKundengruppe_arr = $oKundengruppe->cKundengruppe_arr;
    }
    $smarty->assign('kVerpackung', $oVerpackung->kVerpackung)
           ->assign('oVerpackungEdit', $oVerpackung);
} elseif ($action === 'delete') {
    if (isset($_POST['kVerpackung']) && is_array($_POST['kVerpackung']) && count($_POST['kVerpackung']) > 0) {
        foreach ($_POST['kVerpackung'] as $kVerpackung) {
            $kVerpackung = (int)$kVerpackung;
            // tverpackung loeschen
            Shop::Container()->getDB()->delete('tverpackung', 'kVerpackung', $kVerpackung);
            // tverpackungsprache loeschen
            Shop::Container()->getDB()->delete('tverpackungsprache', 'kVerpackung', $kVerpackung);
        }
        $cHinweis .= 'Die markierten Verpackungen wurden erfolgreich gelöscht.<br />';
    } else {
        $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Verpackung.<br />';
    }
} elseif ($action === 'refresh') {
    if (isset($_POST['nAktivTMP']) && is_array($_POST['nAktivTMP']) && count($_POST['nAktivTMP']) > 0) {
        foreach ($_POST['nAktivTMP'] as $kVerpackung) {
            $upd         = new stdClass();
            $upd->nAktiv = isset($_POST['nAktiv']) && in_array($kVerpackung, $_POST['nAktiv'], true) ? 1 : 0;
            Shop::Container()->getDB()->update('tverpackung', 'kVerpackung', (int)$kVerpackung, $upd);
        }
        $cHinweis .= 'Ihre markierten Verpackungen wurden erfolgreich aktualisiert.<br />';
    }
}

$oKundengruppe_arr = Shop::Container()->getDB()->query(
    'SELECT kKundengruppe, cName FROM tkundengruppe',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$oSteuerklasse_arr = Shop::Container()->getDB()->query(
    'SELECT * FROM tsteuerklasse',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$oVerpackungCount = Shop::Container()->getDB()->query(
    'SELECT count(kVerpackung) AS count
            FROM tverpackung',
    \DB\ReturnType::SINGLE_OBJECT
);
$itemsPerPage=10;
$oPagination  = (new Pagination('standard'))
    ->setItemsPerPageOptions([$itemsPerPage, $itemsPerPage*2, $itemsPerPage*5])
    ->setItemCount($oVerpackungCount->count)
    ->assemble();
$oVerpackung_arr = Shop::Container()->getDB()->query(
    'SELECT * FROM tverpackung 
       ORDER BY cName' .
      ($oPagination->getLimitSQL() !== '' ? ' LIMIT ' . $oPagination->getLimitSQL() : ''),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

foreach ($oVerpackung_arr as $i => $oVerpackung) {
    $oKundengruppe                          = gibKundengruppeObj($oVerpackung->cKundengruppe);
    $oVerpackung_arr[$i]->kKundengruppe_arr = $oKundengruppe->kKundengruppe_arr;
    $oVerpackung_arr[$i]->cKundengruppe_arr = $oKundengruppe->cKundengruppe_arr;
}

$smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
       ->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
       ->assign('oVerpackung_arr', $oVerpackung_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('oSprache_arr', $oSprache_arr)
       ->assign('oPagination', $oPagination)
       ->assign('action', $action)
       ->display('zusatzverpackung.tpl');

/**
 * @param string $cKundengruppe
 * @return stdClass|null
 */
function gibKundengruppeObj($cKundengruppe)
{
    $oKundengruppe        = new stdClass();
    $kKundengruppeTMP_arr = [];
    $cKundengruppeTMP_arr = [];

    if (strlen($cKundengruppe) > 0) {
        // Kundengruppen holen
        $oKundengruppe_arr = Shop::Container()->getDB()->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $kKundengruppe_arr = explode(';', $cKundengruppe);
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            if (!in_array('-1', $kKundengruppe_arr)) {
                foreach ($kKundengruppe_arr as $kKundengruppe) {
                    $kKundengruppe          = (int)$kKundengruppe;
                    $kKundengruppeTMP_arr[] = $kKundengruppe;
                    if (is_array($oKundengruppe_arr) && count($oKundengruppe_arr) > 0) {
                        foreach ($oKundengruppe_arr as $oKundengruppe) {
                            if ($oKundengruppe->kKundengruppe == $kKundengruppe) {
                                $cKundengruppeTMP_arr[] = $oKundengruppe->cName;
                                break;
                            }
                        }
                    }
                }
            } elseif (count($oKundengruppe_arr) > 0) {
                foreach ($oKundengruppe_arr as $oKundengruppe) {
                    $kKundengruppeTMP_arr[] = $oKundengruppe->kKundengruppe;
                    $cKundengruppeTMP_arr[] = $oKundengruppe->cName;
                }
            }
        }
    }
    $oKundengruppe->kKundengruppe_arr = $kKundengruppeTMP_arr;
    $oKundengruppe->cKundengruppe_arr = $cKundengruppeTMP_arr;

    return $oKundengruppe;
}

/**
 * @param object $oVerpackung
 * @param array $kKundengruppe_arr
 * @param int $kVerpackung
 * @param object $smarty
 * @return void
 */
function holdInputOnError($oVerpackung, $kKundengruppe_arr, $kVerpackung, &$smarty) {
    $oVerpackung->oSprach_arr = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'cName') !== false) {
            $cISO = explode('cName_', $key)[1];
            $oVerpackung->oSprach_arr[$cISO]        = new stdClass();
            $oVerpackung->oSprach_arr[$cISO]->cName = $value;
            if (isset($_POST['cBeschreibung_'.$cISO])) {
                $oVerpackung->oSprach_arr[$cISO]->cBeschreibung = $_POST['cBeschreibung_'.$cISO];
            }
        }
    }

    if ($kKundengruppe_arr && $kKundengruppe_arr[0] !== '-1') {
        $oVerpackung->cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
        $oKundengruppe                  = gibKundengruppeObj($oVerpackung->cKundengruppe);
        $oVerpackung->kKundengruppe_arr = $oKundengruppe->kKundengruppe_arr;
        $oVerpackung->cKundengruppe_arr = $oKundengruppe->cKundengruppe_arr;
    } else {
        $oVerpackung->cKundengruppe = '-1';
    }

    $smarty->assign('oVerpackungEdit', $oVerpackung)
           ->assign('kVerpackung', $kVerpackung);
}
