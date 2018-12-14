<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\RequestHelper;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_LIVESEARCH_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

setzeSprache();

$hinweis     = '';
$fehler      = '';
$settingsIDs = [423, 425, 422, 437, 438];
$db          = Shop::Container()->getDB();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
$cLivesucheSQL         = new stdClass();
$cLivesucheSQL->cWhere = '';
$cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
if (strlen(RequestHelper::verifyGPDataString('cSuche')) > 0) {
    $cSuche = $db->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche')));

    if (strlen($cSuche) > 0) {
        $cLivesucheSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $cSuche . "%'";
        $smarty->assign('cSuche', $cSuche);
    } else {
        $fehler = 'Fehler: Bitte geben Sie einen Suchbegriff ein.';
    }
}
if (RequestHelper::verifyGPCDataInt('einstellungen') === 1) {
    $hinweis .= saveAdminSettings($settingsIDs, $_POST);
    $smarty->assign('tab', 'einstellungen');
}

if (RequestHelper::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', RequestHelper::verifyGPCDataInt('nSort'));

    switch (RequestHelper::verifyGPCDataInt('nSort')) {
        case 1:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv DESC ';
            break;
        case 33:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv ASC ';
            break;
    }
} else {
    $smarty->assign('nSort', -1);
}

if (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 1) { //Formular wurde abgeschickt
    // Suchanfragen aktualisieren
    if (isset($_POST['suchanfragenUpdate'])) {
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            foreach ($_POST['kSuchanfrageAll'] as $kSuchanfrage) {
                if (strlen($_POST['nAnzahlGesuche_' . $kSuchanfrage]) > 0
                    && (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage] > 0
                ) {
                    $_upd                 = new stdClass();
                    $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage];
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $_upd);
                }
            }
        }
        // Eintragen in die Mapping Tabelle
        $Suchanfragen = $db->selectAll(
            'tsuchanfrage',
            'kSprache',
            (int)$_SESSION['kSprache'],
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        $nMappingVorhanden = 0;
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            $cSQLDel = ' IN (';
            // nAktiv Reihe updaten
            foreach ($_POST['kSuchanfrageAll'] as $i => $kSuchanfrage) {
                $upd         = new stdClass();
                $upd->nAktiv = 0;
                $db->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $upd);
                // Loeschequery vorbereiten
                if ($i > 0) {
                    $cSQLDel .= ', ' . (int)$kSuchanfrage;
                } else {
                    $cSQLDel .= (int)$kSuchanfrage;
                }
            }

            $cSQLDel .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            $db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            $db->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            if (isset($_POST['nAktiv']) && is_array($_POST['nAktiv'])) {
                foreach ($_POST['nAktiv'] as $i => $nAktiv) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv);
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kSuchanfrage', (int)$nAktiv, (int)$_SESSION['kSprache']]
                    );
                    // Aktivierte Suchanfragen in tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($query->cSuche));
                    $oSeo->cKey     = 'kSuchanfrage';
                    $oSeo->kKey     = $nAktiv;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $oSeo);
                    // Aktivierte Suchanfragen in tsuchanfrage updaten
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    $upd->cSeo   = $oSeo->cSeo;
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv, $upd);
                }
            }
        }
        foreach ($Suchanfragen as $sucheanfrage) {
            if (!isset($_POST['mapping_' . $sucheanfrage->kSuchanfrage])
                || strtolower($sucheanfrage->cSuche) !== strtolower($_POST['mapping_' . $sucheanfrage->kSuchanfrage])
            ) {
                if (!empty($_POST['mapping_' . $sucheanfrage->kSuchanfrage])) {
                    $nMappingVorhanden                      = 1;
                    $suchanfragemapping_obj                 = new stdClass();
                    $suchanfragemapping_obj->kSprache       = $_SESSION['kSprache'];
                    $suchanfragemapping_obj->cSuche         = $sucheanfrage->cSuche;
                    $suchanfragemapping_obj->cSucheNeu      = $_POST['mapping_' . $sucheanfrage->kSuchanfrage];
                    $suchanfragemapping_obj->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                    $Neuesuche                              = $db->select(
                        'tsuchanfrage',
                        'cSuche',
                        $suchanfragemapping_obj->cSucheNeu
                    );
                    if (isset($Neuesuche->kSuchanfrage) && $Neuesuche->kSuchanfrage > 0) {
                        $db->insert('tsuchanfragemapping', $suchanfragemapping_obj);
                        $db->queryPrepared(
                            'UPDATE tsuchanfrage
                                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                WHERE kSprache = :lid
                                    AND cSuche = :src',
                            [
                                'cnt' => $sucheanfrage->nAnzahlGesuche,
                                'lid' => (int)$_SESSION['kSprache'],
                                'src' => $_POST['mapping_' . $sucheanfrage->kSuchanfrage]
                            ],
                            \DB\ReturnType::DEFAULT
                        );
                        $db->delete(
                            'tsuchanfrage',
                            'kSuchanfrage',
                            (int)$sucheanfrage->kSuchanfrage
                        );
                        $upd       = new stdClass();
                        $upd->kKey = (int)$Neuesuche->kSuchanfrage;
                        $db->update(
                            'tseo',
                            ['cKey', 'kKey'],
                            ['kSuchanfrage', (int)$sucheanfrage->kSuchanfrage],
                            $upd
                        );

                        $hinweis .= 'Die Suchanfrage "' . $suchanfragemapping_obj->cSuche .
                            '" wurde erfolgreich auf "' . $suchanfragemapping_obj->cSucheNeu . '" gemappt.<br />';
                    }
                }
            } else {
                $fehler .= 'Die Suchanfrage "' . $sucheanfrage->cSuche .
                    '" kann nicht auf den gleichen Suchebegriff gemappt werden.';
            }
        }

        $hinweis .= 'Die Suchanfragen wurden erfolgreich aktualisiert.<br />';
    } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
        $cMapping = RequestHelper::verifyGPDataString('cMapping');

        if (strlen($cMapping) > 0) {
            if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);

                    if ($query->kSuchanfrage > 0) {
                        if (strtolower($query->cSuche) !== strtolower($cMapping)) {
                            $oSuchanfrageNeu = $db->select('tsuchanfrage', 'cSuche', $cMapping);
                            if (isset($oSuchanfrageNeu->kSuchanfrage) && $oSuchanfrageNeu->kSuchanfrage > 0) {
                                $queryMapping                 = new stdClass();
                                $queryMapping->kSprache       = $_SESSION['kSprache'];
                                $queryMapping->cSuche         = $query->cSuche;
                                $queryMapping->cSucheNeu      = $cMapping;
                                $queryMapping->nAnzahlGesuche = $query->nAnzahlGesuche;

                                $kSuchanfrageMapping = $db->insert(
                                    'tsuchanfragemapping',
                                    $queryMapping
                                );
                                if ($kSuchanfrageMapping > 0) {
                                    $db->queryPrepared(
                                        'UPDATE tsuchanfrage
                                            SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                            WHERE kSprache = :lid
                                                AND kSuchanfrage = :sid',
                                        [
                                            'cnt' => $query->nAnzahlGesuche,
                                            'lid' => (int)$_SESSION['kSprache'],
                                            'sid' => $oSuchanfrageNeu->kSuchanfrage
                                        ],
                                        \DB\ReturnType::DEFAULT
                                    );
                                    $db->delete(
                                        'tsuchanfrage',
                                        'kSuchanfrage',
                                        (int)$query->kSuchanfrage
                                    );
                                    $db->queryPrepared(
                                        "UPDATE tseo
                                            SET kKey = :kid
                                            WHERE cKey = 'kSuchanfrage'
                                                AND kKey = :sid",
                                        [
                                            'kid' => (int)$oSuchanfrageNeu->kSuchanfrage,
                                            'sid' => (int)$query->kSuchanfrage
                                        ],
                                        \DB\ReturnType::DEFAULT
                                    );

                                    $hinweis = 'Ihre markierten Suchanfragen wurden erfolgreich auf "' .
                                        $cMapping . '" gemappt.';
                                }
                            } else {
                                $fehler = 'Fehler: Sie haben versucht auf eine nicht ' .
                                    'existierende Suchanfrage zu mappen.';
                                break;
                            }
                        } else {
                            $fehler = 'Die Suchanfrage "' . $query->cSuche .
                                '" kann nicht auf den gleichen Suchebegriff gemappt werden.';
                            break;
                        }
                    } else {
                        $fehler = 'Fehler: Sie haben versucht eine nicht existierende Suchanfrage zu mappen.';
                        break;
                    }
                }
            } else {
                $fehler = 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.';
            }
        } else {
            $fehler = 'Fehler: Bitte geben Sie ein Mappingname an.';
        }
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        if (is_array($_POST['kSuchanfrage'])) {
            foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                $kSuchanfrage_obj = $db->select(
                    'tsuchanfrage',
                    'kSuchanfrage',
                    (int)$kSuchanfrage
                );
                $obj              = new stdClass();
                $obj->kSprache    = (int)$kSuchanfrage_obj->kSprache;
                $obj->cSuche      = $kSuchanfrage_obj->cSuche;

                $db->delete('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
                $db->insert('tsuchanfrageblacklist', $obj);
                // Aus tseo loeschen
                $db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', (int)$kSuchanfrage]);
                $hinweis .= 'Die Suchanfrage "' . $kSuchanfrage_obj->cSuche .
                    '" wurde erfolgreich gelöscht.<br />';
                $hinweis .= 'Die Suchanfrage "' . $kSuchanfrage_obj->cSuche .
                    '" wurde auf die Blacklist hinzugefügt.<br />';
            }
        } else {
            $fehler .= 'Bitte wählen Sie mindestens eine Suchanfrage aus.<br />';
        }
    }
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 2) { // Erfolglos mapping
    if (isset($_POST['erfolglosEdit'])) { // Editieren
        $smarty->assign('nErfolglosEditieren', 1);
    } elseif (isset($_POST['erfolglosUpdate'])) { // Update
        $Suchanfragenerfolglos = $db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            (int)$_SESSION['kSprache'],
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($Suchanfragenerfolglos as $Suchanfrageerfolglos) {
            $idx = 'mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos;
            if (isset($_POST[$idx]) && strlen($_POST[$idx]) > 0) {
                if (strtolower($Suchanfrageerfolglos->cSuche) !== strtolower($_POST[$idx])) {
                    $suchanfragemapping_obj                 = new stdClass();
                    $suchanfragemapping_obj->kSprache       = $_SESSION['kSprache'];
                    $suchanfragemapping_obj->cSuche         = $Suchanfrageerfolglos->cSuche;
                    $suchanfragemapping_obj->cSucheNeu      = $_POST[$idx];
                    $suchanfragemapping_obj->nAnzahlGesuche = $Suchanfrageerfolglos->nAnzahlGesuche;

                    $oAlteSuche = $db->select(
                        'tsuchanfrageerfolglos',
                        'cSuche',
                        $suchanfragemapping_obj->cSuche
                    );

                    //check if loops would be created with mapping
                    $bIsLoop           = false;
                    $sSearchMappingTMP = $suchanfragemapping_obj->cSucheNeu;
                    while (!empty($sSearchMappingTMP)) {
                        if ($sSearchMappingTMP === $suchanfragemapping_obj->cSuche) {
                            $bIsLoop = true;
                            break;
                        }
                        $oSearchMappingNextTMP = $db->select(
                            'tsuchanfragemapping',
                            'kSprache',
                            $_SESSION['kSprache'],
                            'cSuche',
                            $sSearchMappingTMP
                        );
                        if (!empty($oSearchMappingNextTMP->cSucheNeu)) {
                            $sSearchMappingTMP = $oSearchMappingNextTMP->cSucheNeu;
                        } else {
                            $sSearchMappingTMP = null;
                        }
                    }

                    if (!$bIsLoop) {
                        if (isset($oAlteSuche->kSuchanfrageErfolglos) && $oAlteSuche->kSuchanfrageErfolglos > 0) {
                            $oCheckMapping = $db->select(
                                'tsuchanfrageerfolglos',
                                'cSuche',
                                $suchanfragemapping_obj->cSuche
                            );
                            $db->insert('tsuchanfragemapping', $suchanfragemapping_obj);
                            $db->delete(
                                'tsuchanfrageerfolglos',
                                'kSuchanfrageErfolglos',
                                (int)$oAlteSuche->kSuchanfrageErfolglos
                            );

                            $hinweis .= 'Die Suchanfrage "' . $suchanfragemapping_obj->cSuche .
                                '" wurde erfolgreich auf "' . $suchanfragemapping_obj->cSucheNeu . '" gemappt.<br />';
                        }
                    } else {
                        $fehler .= 'Das Mapping von "' . $suchanfragemapping_obj->cSuche .
                            '" auf "' . $suchanfragemapping_obj->cSucheNeu .
                            '" würde eine Schleife verursachen.<br />';
                    }
                } else {
                    $fehler .= 'Die Suchanfrage "' . $Suchanfrageerfolglos->cSuche .
                        '" kann nicht auf den gleichen Suchbegriff gemappt werden.';
                }
            } elseif ((int)$_POST['nErfolglosEditieren'] === 1) {
                $idx = 'cSuche_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos;

                $Suchanfrageerfolglos->cSuche = StringHandler::filterXSS($_POST[$idx]);
                $upd                          = new stdClass();
                $upd->cSuche                  = $Suchanfrageerfolglos->cSuche;
                $db->update(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$Suchanfrageerfolglos->kSuchanfrageErfolglos,
                    $upd
                );
            }
        }
    } elseif (isset($_POST['erfolglosDelete'])) { // Loeschen
        $kSuchanfrageErfolglos_arr = $_POST['kSuchanfrageErfolglos'];
        if (is_array($kSuchanfrageErfolglos_arr) && count($kSuchanfrageErfolglos_arr) > 0) {
            foreach ($kSuchanfrageErfolglos_arr as $kSuchanfrageErfolglos) {
                $kSuchanfrageErfolglos = (int)$kSuchanfrageErfolglos;
                $db->delete(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    $kSuchanfrageErfolglos
                );
            }
            $hinweis = 'Ihre markierten Suchanfragen wurden erfolgreich gelöscht.';
        } else {
            $fehler = 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.';
        }
    }
    $smarty->assign('tab', 'erfolglos');
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 3) { // Blacklist
    $suchanfragenblacklist = $_POST['suchanfrageblacklist'];
    $suchanfragenblacklist = explode(';', $suchanfragenblacklist);
    $count                 = count($suchanfragenblacklist);

    $db->delete('tsuchanfrageblacklist', 'kSprache', (int)$_SESSION['kSprache']);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($suchanfragenblacklist[$i])) {
            $blacklist_obj           = new stdClass();
            $blacklist_obj->cSuche   = $suchanfragenblacklist[$i];
            $blacklist_obj->kSprache = (int)$_SESSION['kSprache'];
            $db->insert('tsuchanfrageblacklist', $blacklist_obj);
        }
    }
    $smarty->assign('tab', 'blacklist');
    $hinweis .= 'Die Blacklist wurde erfolgreich aktualisiert.';
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 4) { // Mappinglist
    if (isset($_POST['delete'])) {
        if (is_array($_POST['kSuchanfrageMapping'])) {
            foreach ($_POST['kSuchanfrageMapping'] as $kSuchanfrageMapping) {
                $queryMapping = $db->select(
                    'tsuchanfragemapping',
                    'kSuchanfrageMapping',
                    (int)$kSuchanfrageMapping
                );
                if (isset($queryMapping->cSuche) && strlen($queryMapping->cSuche) > 0) {
                    $db->delete(
                        'tsuchanfragemapping',
                        'kSuchanfrageMapping',
                        (int)$kSuchanfrageMapping
                    );
                    $hinweis .= 'Das Mapping "' . $queryMapping->cSuche . '" wurde erfolgreich gelöscht.<br />';
                } else {
                    $fehler .= 'Es wurde kein Mapping mit der ID "' . $kSuchanfrageMapping . '" gefunden.<br />';
                }
            }
        } else {
            $fehler .= 'Bitte wählen Sie mindestens ein Mapping aus.<br />';
        }
    }
    $smarty->assign('tab', 'mapping');
}

$Sprachen                    = Sprache::getAllLanguages();
$nAnzahlSuchanfragen         = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfrage
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . $cLivesucheSQL->cWhere,
    \DB\ReturnType::SINGLE_OBJECT
);
$nAnzahlSuchanfrageerfolglos = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . (int)$_SESSION['kSprache'],
    \DB\ReturnType::SINGLE_OBJECT
);
$nAnzahlSuchanfragenMapping  = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfragemapping
        WHERE kSprache = ' . (int)$_SESSION['kSprache'],
    \DB\ReturnType::SINGLE_OBJECT
);
$oPagiSuchanfragen           = (new Pagination('suchanfragen'))
    ->setItemCount($nAnzahlSuchanfragen->nAnzahl)
    ->assemble();
$oPagiErfolglos              = (new Pagination('erfolglos'))
    ->setItemCount($nAnzahlSuchanfrageerfolglos->nAnzahl)
    ->assemble();
$oPagiMapping                = (new Pagination('mapping'))
    ->setItemCount($nAnzahlSuchanfragenMapping->nAnzahl)
    ->assemble();

$Suchanfragen = $db->query(
    "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
        FROM tsuchanfrage
        LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage'
            AND tseo.kKey = tsuchanfrage.kSuchanfrage
            AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . '
        WHERE tsuchanfrage.kSprache = ' . (int)$_SESSION['kSprache'] . '
            ' . $cLivesucheSQL->cWhere . '
        GROUP BY tsuchanfrage.kSuchanfrage
        ORDER BY ' . $cLivesucheSQL->cOrder . '
        LIMIT ' . $oPagiSuchanfragen->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

if (isset($Suchanfragen->tcSeo) && strlen($Suchanfragen->tcSeo) > 0) {
    $Suchanfragen->cSeo = $Suchanfragen->tcSeo;
}
unset($Suchanfragen->tcSeo);

$Suchanfragenerfolglos = $db->query(
    'SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        ORDER BY nAnzahlGesuche DESC
        LIMIT ' . $oPagiErfolglos->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$Suchanfragenblacklist = $db->query(
    'SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        ORDER BY kSuchanfrageBlacklist',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$Suchanfragenmapping   = $db->query(
    'SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        LIMIT ' . $oPagiMapping->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$smarty->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->assign('Sprachen', $Sprachen)
       ->assign('Suchanfragen', $Suchanfragen)
       ->assign('Suchanfragenerfolglos', $Suchanfragenerfolglos)
       ->assign('Suchanfragenblacklist', $Suchanfragenblacklist)
       ->assign('Suchanfragenmapping', $Suchanfragenmapping)
       ->assign('oPagiSuchanfragen', $oPagiSuchanfragen)
       ->assign('oPagiErfolglos', $oPagiErfolglos)
       ->assign('oPagiMapping', $oPagiMapping)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('livesuche.tpl');
