<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

$oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$kSektion         = CONF_ARTIKELUEBERSICHT;
$Einstellungen    = Shop::getSettings([$kSektion]);
$standardwaehrung = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
$mysqlVersion     = Shop::Container()->getDB()->query(
    "SHOW VARIABLES LIKE 'innodb_version'",
    \DB\ReturnType::SINGLE_OBJECT
)->Value;
$step             = 'einstellungen bearbeiten';
$cHinweis         = '';
$cFehler          = '';
$Conf             = [];
$createIndex      = false;

if (isset($_GET['action']) && $_GET['action'] === 'createIndex') {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/json');

    $index = strtolower(StringHandler::xssClean($_GET['index']));

    if (!in_array($index, ['tartikel', 'tartikelsprache'], true)) {
        header(Request::makeHTTPHeader(403), true);
        echo json_encode((object)['error' => 'Ungültiger Index angegeben']);
        exit;
    }

    try {
        if (Shop::Container()->getDB()->query(
            "SHOW INDEX FROM $index WHERE KEY_NAME = 'idx_{$index}_fulltext'",
            \DB\ReturnType::SINGLE_OBJECT
        )) {
            Shop::Container()->getDB()->executeQuery(
                "ALTER TABLE $index DROP KEY idx_{$index}_fulltext",
                \DB\ReturnType::QUERYSINGLE
            );
        }
    } catch (Exception $e) {
        // Fehler beim Index löschen ignorieren
    }

    if ($_GET['create'] === 'Y') {
        $cSuchspalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return $item_arr[1];
        }, \Filter\States\BaseSearchQuery::getSearchRows());

        switch ($index) {
            case 'tartikel':
                $cSpalten_arr = array_intersect(
                    $cSuchspalten_arr,
                    ['cName', 'cSeo', 'cSuchbegriffe',
                     'cArtNr', 'cKurzBeschreibung',
                     'cBeschreibung', 'cBarcode',
                     'cISBN', 'cHAN', 'cAnmerkung'
                    ]
                );
                break;
            case 'tartikelsprache':
                $cSpalten_arr = array_intersect(
                    $cSuchspalten_arr,
                    ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']
                );
                break;
            default:
                header(Request::makeHTTPHeader(403), true);
                echo json_encode((object)['error' => 'Ungültiger Index angegeben']);
                exit;
        }

        try {
            Shop::Container()->getDB()->executeQuery(
                'UPDATE tsuchcache SET dGueltigBis = DATE_ADD(NOW(), INTERVAL 10 MINUTE)',
                \DB\ReturnType::QUERYSINGLE
            );
            $res = Shop::Container()->getDB()->executeQuery(
                "ALTER TABLE $index
                    ADD FULLTEXT KEY idx_{$index}_fulltext (" . implode(', ', $cSpalten_arr) . ')',
                \DB\ReturnType::QUERYSINGLE
            );
        } catch (Exception $e) {
            $res = 0;
        }

        if ($res === 0) {
            $cFehler      = 'Der Index für die Volltextsuche konnte nicht angelegt werden! ' .
                'Die Volltextsuche wird deaktiviert.';
            $shopSettings = Shopsetting::getInstance();
            $settings     = $shopSettings[Shopsetting::mapSettingName(CONF_ARTIKELUEBERSICHT)];

            if ($settings['suche_fulltext'] !== 'N') {
                $settings['suche_fulltext'] = 'N';
                saveAdminSectionSettings($kSektion, $settings);

                Shop::Container()->getCache()->flushTags([
                    CACHING_GROUP_OPTION,
                    CACHING_GROUP_CORE,
                    CACHING_GROUP_ARTICLE,
                    CACHING_GROUP_CATEGORY
                ]);
                $shopSettings->reset();
            }
        } else {
            $cHinweis = 'Der Volltextindex für ' . $index . ' wurde angelegt!';
        }
    } else {
        $cHinweis = 'Der Volltextindex für ' . $index . ' wurde gelöscht!';
    }

    header(Request::makeHTTPHeader(200), true);
    echo json_encode((object)['error' => $cFehler, 'hinweis' => $cHinweis]);
    exit;
}

if (isset($_POST['einstellungen_bearbeiten'])
    && (int)$_POST['einstellungen_bearbeiten'] === 1
    && $kSektion > 0
    && Form::validateToken()
) {
    $sucheFulltext = isset($_POST['suche_fulltext']) ? in_array($_POST['suche_fulltext'], ['Y', 'B'], true) : false;

    if ($sucheFulltext) {
        if (version_compare($mysqlVersion, '5.6', '<')) {
            //Volltextindizes werden von MySQL mit InnoDB erst ab Version 5.6 unterstützt
            $_POST['suche_fulltext'] = 'N';
            $cFehler                 = 'Die Volltextsuche erfordert MySQL ab Version 5.6!';
        } else {
            // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
            $oValue                     = Shop::Container()->getDB()->query(
                'SELECT @@ft_min_word_len AS ft_min_word_len',
                \DB\ReturnType::SINGLE_OBJECT
            );
            $_POST['suche_min_zeichen'] = $oValue ? $oValue->ft_min_word_len : $_POST['suche_min_zeichen'];
        }
    }

    $shopSettings = Shopsetting::getInstance();
    $cHinweis    .= saveAdminSectionSettings($kSektion, $_POST);

    Shop::Container()->getCache()->flushTags(
        [CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]
    );
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
        if (isset($_POST[$sucheParam]) && ($_POST[$sucheParam] != $Einstellungen['artikeluebersicht'][$sucheParam])) {
            $fulltextChanged = true;
            break;
        }
    }
    if ($fulltextChanged) {
        $createIndex = $sucheFulltext ? 'Y' : 'N';
    }

    if ($sucheFulltext && $fulltextChanged) {
        $cHinweis .= ' Volltextsuche wurde aktiviert.';
    } elseif ($fulltextChanged) {
        $cHinweis .= ' Volltextsuche wurde deaktiviert.';
    }

    $Einstellungen = Shop::getSettings([$kSektion]);
}

$section = Shop::Container()->getDB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
if ($Einstellungen['artikeluebersicht']['suche_fulltext'] !== 'N'
    && (!Shop::Container()->getDB()->query(
        "SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'",
        \DB\ReturnType::SINGLE_OBJECT
    )
    || !Shop::Container()->getDB()->query(
        "SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
        \DB\ReturnType::SINGLE_OBJECT
    ))) {
    $cFehler = 'Der Volltextindex ist nicht vorhanden! ' .
        'Die Erstellung des Index kann jedoch einige Zeit in Anspruch nehmen. ' .
        '<a href="sucheinstellungen.php" title="Aktualisieren"><i class="alert-danger fa fa-refresh"></i></a>';
    Notification::getInstance()->add(
        NotificationEntry::TYPE_WARNING,
        'Der Volltextindex wird erstellt!',
        'sucheinstellungen.php'
    );
}

$smarty->configLoad('german.conf', 'einstellungen')
    ->assign('action', 'sucheinstellungen.php')
    ->assign('kEinstellungenSektion', $kSektion)
    ->assign('Sektion', $section)
    ->assign('Conf', getAdminSectionSettings(CONF_ARTIKELUEBERSICHT))
    ->assign('cPrefDesc', $smarty->getConfigVars('prefDesc' . $kSektion))
    ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $kSektion))
    ->assign('step', $step)
    ->assign('supportFulltext', version_compare($mysqlVersion, '5.6', '>='))
    ->assign('createIndex', $createIndex)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('waehrung', $standardwaehrung->cName)
    ->display('sucheinstellungen.tpl');
