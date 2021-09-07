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
$oSprache_arr = gibAlleSprachen();
$postData     = StringHandler::filterXSS($_POST);
// Zusatzverpackung speichern
if (isset($postData['eintragen']) && (int)$postData['eintragen'] === 1 && validateToken()) {
    $kVerpackung         = (int)$postData['kVerpackung'];
    $fBrutto             = isset($postData['fBrutto']) ? (float)$postData['fBrutto'] : 0;
    $fMindestbestellwert = isset($postData['fMindestbestellwert']) ? (float)$postData['fMindestbestellwert'] : 0;
    $fKostenfrei         = isset($postData['fKostenfrei']) ? (float)$postData['fKostenfrei'] : 0;
    $kSteuerklasse       = isset($postData['kSteuerklasse']) ? (int)$postData['kSteuerklasse'] : 0;
    $kKundengruppe_arr   = isset($postData['kKundengruppe']) ? $postData['kKundengruppe'] : null;
    $nAktiv              = isset($postData['nAktiv']) ? (int)$postData['nAktiv'] : 0;

    if (isset($postData['cName_' . $oSprache_arr[0]->cISO]) && strlen($postData['cName_' . $oSprache_arr[0]->cISO]) > 0) {
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            if (!isset($oVerpackung)) {
                $oVerpackung = new stdClass();
            }
            $oVerpackung->kSteuerklasse = $kSteuerklasse;
            $oVerpackung->cName         = htmlspecialchars(strip_tags(trim($postData['cName_' . $oSprache_arr[0]->cISO])), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

            if ($kKundengruppe_arr[0] == '-1') {
                $oVerpackung->cKundengruppe = '-1';
            } else {
                $oVerpackung->cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
            }
            $oVerpackung->fBrutto             = $fBrutto;
            $oVerpackung->fMindestbestellwert = $fMindestbestellwert;
            $oVerpackung->fKostenfrei         = $fKostenfrei;
            $oVerpackung->nAktiv              = $nAktiv;
            // Update?
            if ($kVerpackung > 0) {
                Shop::DB()->query(
                    "DELETE tverpackung, tverpackungsprache
                        FROM tverpackung
                        LEFT JOIN tverpackungsprache 
                            ON tverpackungsprache.kVerpackung = tverpackung.kVerpackung
                        WHERE tverpackung.kVerpackung = " . $kVerpackung, 3
                );

                $oVerpackung->kVerpackung = $kVerpackung;
                Shop::DB()->insert('tverpackung', $oVerpackung);
            } else {
                $kVerpackung = Shop::DB()->insert('tverpackung', $oVerpackung);
            }
            // In tverpackungsprache adden
            if (is_array($oSprache_arr) && count($oSprache_arr) > 0) {
                foreach ($oSprache_arr as $i => $oSprache) {
                    $oVerpackungSprache                = new stdClass();
                    $oVerpackungSprache->kVerpackung   = $kVerpackung;
                    $oVerpackungSprache->cISOSprache   = $oSprache->cISO;
                    $oVerpackungSprache->cName         = (!empty($postData['cName_' . $oSprache->cISO]))
                        ? htmlspecialchars($postData['cName_' . $oSprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : htmlspecialchars($postData['cName_' . $oSprache_arr[0]->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                    $oVerpackungSprache->cBeschreibung = (!empty($postData['cBeschreibung_' . $oSprache->cISO]))
                        ? htmlspecialchars($postData['cBeschreibung_' . $oSprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : htmlspecialchars($postData['cBeschreibung_' . $oSprache_arr[0]->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                    Shop::DB()->insert('tverpackungsprache', $oVerpackungSprache);
                }
            }

            unset($oVerpackung);
            $cHinweis .= 'Die Verpackung "' . $postData['cName_' .
                $oSprache_arr[0]->cISO] . '" wurde erfolgreich gespeichert.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte w&auml;hlen Sie mindestens eine Kundengruppe aus.<br />';
        }
    } else {
        $cFehler .= 'Fehler: Bitte geben Sie der Verpackung einen Namen.<br />';
    }
} elseif (isset($postData['bearbeiten']) && (int)$postData['bearbeiten'] === 1 && validateToken()) {
    // Verpackungen bearbeiten (aktualisieren / loeschen)
    if (isset($postData['loeschen']) && ($postData['loeschen'] === 'Löschen' ||
            utf8_decode($postData['loeschen'] === 'Löschen') ||
            $postData['loeschen'] === utf8_decode('Löschen'))) {
        if (is_array($postData['kVerpackung']) && count($postData['kVerpackung']) > 0) {
            foreach ($postData['kVerpackung'] as $kVerpackung) {
                $kVerpackung = (int)$kVerpackung;
                // tverpackung loeschen
                Shop::DB()->delete('tverpackung', 'kVerpackung', $kVerpackung);
                // tverpackungsprache loeschen
                Shop::DB()->delete('tverpackungsprache', 'kVerpackung', $kVerpackung);
            }

            $cHinweis .= 'Die markierten Verpackungen wurden erfolgreich gel&ouml;scht.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Verpackung.<br />';
        }
    } elseif (isset($postData['aktualisieren']) &&
        $postData['aktualisieren'] === 'Aktualisieren' && validateToken()) {
        // Aktualisieren
        // Alle Verpackungen deaktivieren
        Shop::DB()->query("UPDATE tverpackung SET nAktiv = 0", 3);
        if (is_array($postData['nAktiv']) && count($postData['nAktiv']) > 0) {
            foreach ($postData['nAktiv'] as $kVerpackung) {
                $upd         = new stdClass();
                $upd->nAktiv = 1;
                Shop::DB()->update('tverpackung', 'kVerpackung', (int)$kVerpackung, $upd);
            }
            $cHinweis .= 'Ihre markierten Verpackungen wurden erfolgreich aktualisiert.<br />';
        }
    }
} elseif (verifyGPCDataInteger('edit') > 0 && validateToken()) { // Editieren
    $kVerpackung = verifyGPCDataInteger('edit');
    $oVerpackung = Shop::DB()->select('tverpackung', 'kVerpackung', $kVerpackung);

    if ($oVerpackung->kVerpackung > 0) {
        $oVerpackung->oSprach_arr = [];
        $oVerpackungSprach_arr    = Shop::DB()->selectAll(
            'tverpackungsprache',
            'kVerpackung',
            $kVerpackung,
            'cISOSprache, cName, cBeschreibung'
        );
        if (is_array($oVerpackungSprach_arr) && count($oVerpackungSprach_arr) > 0) {
            foreach ($oVerpackungSprach_arr as $oVerpackungSprach) {
                $oVerpackung->oSprach_arr[$oVerpackungSprach->cISOSprache] = $oVerpackungSprach;
            }
        }
        $oKundengruppe                  = gibKundengruppeObj($oVerpackung->cKundengruppe);
        $oVerpackung->kKundengruppe_arr = $oKundengruppe->kKundengruppe_arr;
        $oVerpackung->cKundengruppe_arr = $oKundengruppe->cKundengruppe_arr;
    }

    $smarty->assign('kVerpackung', $oVerpackung->kVerpackung)
           ->assign('oVerpackungEdit', $oVerpackung);
}

// tverpackungsprache anzeigen
if (isset($_GET['a']) && (int)$_GET['a'] > 0 && validateToken()) {
    $step                   = 'anzeigen';
    $kVerpackung            = (int)$_GET['a'];
    $oVerpackungSprache_arr = Shop::DB()->selectAll('tverpackungsprache', 'kVerpackung', $kVerpackung);
    $smarty->assign('oVerpackungSprache_arr', $oVerpackungSprache_arr);
} else {
    // Kundengruppen holen
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    // Steuerklassen
    $oSteuerklasse_arr = Shop::DB()->query("SELECT * FROM tsteuerklasse", 2);
    // Verpackung aus der DB holen und assignen
    $oVerpackung_arr = Shop::DB()->query("SELECT * FROM tverpackung", 2);

    // cKundengruppe exploden
    if (is_array($oVerpackung_arr) && count($oVerpackung_arr) > 0) {
        foreach ($oVerpackung_arr as $i => $oVerpackung) {
            $oKundengruppe                          = gibKundengruppeObj($oVerpackung->cKundengruppe);
            $oVerpackung_arr[$i]->kKundengruppe_arr = $oKundengruppe->kKundengruppe_arr;
            $oVerpackung_arr[$i]->cKundengruppe_arr = $oKundengruppe->cKundengruppe_arr;
        }
    }
    $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
           ->assign('oVerpackung_arr', $oVerpackung_arr);
}
$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('oSprache_arr', $oSprache_arr)
       ->display('zusatzverpackung.tpl');

/**
 * @param string $cKundengruppe
 * @return object|null
 */
function gibKundengruppeObj($cKundengruppe)
{
    $oKundengruppe        = new stdClass();
    $kKundengruppeTMP_arr = [];
    $cKundengruppeTMP_arr = [];

    if (strlen($cKundengruppe) > 0) {
        // Kundengruppen holen
        $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
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
            } else {
                if (count($oKundengruppe_arr) > 0) {
                    foreach ($oKundengruppe_arr as $oKundengruppe) {
                        $kKundengruppeTMP_arr[] = $oKundengruppe->kKundengruppe;
                        $cKundengruppeTMP_arr[] = $oKundengruppe->cName;
                    }
                }
            }
        }
    }
    $oKundengruppe->kKundengruppe_arr = $kKundengruppeTMP_arr;
    $oKundengruppe->cKundengruppe_arr = $cKundengruppeTMP_arr;

    return $oKundengruppe;
}
