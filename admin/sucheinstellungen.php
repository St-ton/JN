<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

$oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
/** @global JTLSmarty $smarty */
$kSektion         = CONF_ARTIKELUEBERSICHT;
$Einstellungen    = Shop::getSettings(array($kSektion));
$standardwaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
$step             = 'einstellungen bearbeiten';
$cHinweis         = '';
$cFehler          = '';
$Conf             = array();

if (isset($_POST['einstellungen_bearbeiten']) && (int)$_POST['einstellungen_bearbeiten'] === 1 && $kSektion > 0 && validateToken()) {
    if ($_POST['suche_fulltext'] === 'Y') {
        // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
        $oValue = Shop::DB()->query('select @@ft_min_word_len AS ft_min_word_len', 1);
        $_POST['suche_min_zeichen'] = $oValue ? $oValue->ft_min_word_len : $_POST['suche_min_zeichen'];
    }

    $shopSettings  = Shopsetting::getInstance();
    $cHinweis     .= saveAdminSectionSettings($kSektion, $_POST);

    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
    $shopSettings->reset();

    $fulltextChanged = false;
    foreach ([
            'suche_fulltext',
            'suche_prio_name',
            'suche_prio_suchbegriffe',
            'suche_prio_artikelnummer',
            'suche_prio_kurzbeschreibung',
            'suche_prio_beschreibung',
            'suche_prio_ean',
            'suche_prio_isbn',
            'suche_prio_han',
            'suche_prio_anmerkung'
        ] as $sucheParam) {
        if ($_POST[$sucheParam] != $Einstellungen['artikeluebersicht'][$sucheParam]) {
            $fulltextChanged = true;
            break;
        }
    }
    if ($fulltextChanged) {
        try {
            if (Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)) {
                Shop::DB()->executeQuery("ALTER IGNORE TABLE tartikel DROP KEY idx_tartikel_fulltext", 10);
            }
            if (Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1)
            ) {
                Shop::DB()->executeQuery("ALTER IGNORE TABLE tartikelsprache DROP KEY idx_tartikelsprache_fulltext", 10);
            }
        } catch (Exception $e) {
            // Fehler beim Index löschen ignorieren
            null;
        }
    }

    if ($_POST['suche_fulltext'] === 'Y' && $fulltextChanged) {
        $cSuchspalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return $item_arr[1];
        }, gibSuchSpalten());

        $cSpaltenArtikel_arr = array_intersect($cSuchspalten_arr, ['cName', 'cSeo', 'cSuchbegriffe', 'cArtNr', 'cKurzBeschreibung', 'cBeschreibung', 'cBarcode', 'cISBN', 'cHAN', 'cAnmerkung']);
        $cSpaltenSprache_arr = array_intersect($cSuchspalten_arr, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);

        try {
            $res = Shop::DB()->executeQuery(
                "ALTER TABLE tartikel
                    ADD FULLTEXT KEY idx_tartikel_fulltext (" . implode(', ', $cSpaltenArtikel_arr) . ")",
                10
            ) + Shop::DB()->executeQuery(
                "ALTER TABLE tartikelsprache
                    ADD FULLTEXT KEY idx_tartikelsprache_fulltext (" . implode(', ', $cSpaltenSprache_arr) . ")",
                10
            );
        } catch (Exception $e) {
            $res = 0;
        }

        if ($res < 2) {
            $cFehler = 'Die Indizes für die Volltextsuche konnten nicht angelegt werden! Die Volltextsuche wird deaktiviert.';
            $param   = ['suche_fulltext' => 'N'];
            saveAdminSectionSettings($kSektion, $param);

            Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
            $shopSettings->reset();
        } else {
            $cHinweis .= ' Volltextsuche wurde aktiviert.';
        }
    } elseif ($fulltextChanged) {
        $cHinweis .= ' Volltextsuche wurde deaktiviert.';
    }

    $Einstellungen = Shop::getSettings(array($kSektion));
}

$section = Shop::DB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
$Conf    = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE nModul = 0 
            AND kEinstellungenSektion = $kSektion
        ORDER BY nSort", 2
);

$configCount = count($Conf);
for ($i = 0; $i < $configCount; $i++) {
    if (in_array($Conf[$i]->cInputTyp, array('selectbox', 'listbox'), true)) {
        $Conf[$i]->ConfWerte = Shop::DB()->selectAll('teinstellungenconfwerte', 'kEinstellungenConf', (int)$Conf[$i]->kEinstellungenConf, '*', 'nSort');
    }

    if (isset($Conf[$i]->cWertName)) {
        $Conf[$i]->gesetzterWert = $Einstellungen['artikeluebersicht'][$Conf[$i]->cWertName];
    }
}

$smarty->configLoad('german.conf', 'einstellungen')
    ->assign('action', 'sucheinstellungen.php')
    ->assign('kEinstellungenSektion', $kSektion)
    ->assign('Sektion', $section)
    ->assign('Conf', $Conf)
    ->assign('cPrefDesc', $smarty->getConfigVars('prefDesc' . $kSektion))
    ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $kSektion))
    ->assign('step', $step)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('waehrung', $standardwaehrung->cName)
    ->display('einstellungen.tpl');
