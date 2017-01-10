<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
 * wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt. Nach dem erfolgreichen einloggen,
 * wird die zuvor angestrebte Aktion durchgeführt.
 *
 * @param int $cRedirect
 * @return stdClass
 */
function gibRedirect($cRedirect)
{
    $oRedirect = new stdClass();

    switch ($cRedirect) {
        case R_LOGIN_WUNSCHLISTE:
            $linkHelper                  = LinkHelper::getInstance();
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = verifyGPCDataInteger('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'n';
            $oTMP->Wert                  = verifyGPCDataInteger('n');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'Wunschliste';
            $oTMP->Wert                  = 1;
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_WUNSCHLISTE;
            $oRedirect->cURL             = $linkHelper->getStaticRoute('wunschliste.php', false);
            $oRedirect->cName            = Shop::Lang()->get('wishlist', 'redirect');
            break;
        case R_LOGIN_BEWERTUNG:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = verifyGPCDataInteger('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'bfa';
            $oTMP->Wert                  = 1;
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_BEWERTUNG;
            $oRedirect->cURL             = 'bewertung.php?a=' . verifyGPCDataInteger('a') . '&bfa=1';
            $oRedirect->cName            = Shop::Lang()->get('review', 'redirect');
            break;
        case R_LOGIN_TAG:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = verifyGPCDataInteger('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_TAG;
            $oRedirect->cURL             = 'index.php?a=' . verifyGPCDataInteger('a');
            $oRedirect->cName            = Shop::Lang()->get('tag', 'redirect');
            break;
        case R_LOGIN_NEWSCOMMENT:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 's';
            $oTMP->Wert                  = verifyGPCDataInteger('s');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'n';
            $oTMP->Wert                  = verifyGPCDataInteger('n');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_NEWSCOMMENT;
            $oRedirect->cURL             = 'index.php?s=' . verifyGPCDataInteger('s') . '&n=' . verifyGPCDataInteger('n');
            $oRedirect->cName            = Shop::Lang()->get('news', 'redirect');
            break;
        case R_LOGIN_UMFRAGE:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'u';
            $oTMP->Wert                  = verifyGPCDataInteger('u');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_UMFRAGE;
            $oRedirect->cURL             = 'index.php?u=' . verifyGPCDataInteger('u');
            $oRedirect->cName            = Shop::Lang()->get('poll', 'redirect');
            break;
        case R_LOGIN_RMA:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 's';
            $oTMP->Wert                  = verifyGPCDataInteger('s');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_RMA;
            $oRedirect->cURL             = 'index.php?s=' . verifyGPCDataInteger('s');
            $oRedirect->cName            = Shop::Lang()->get('rma', 'redirect');
            break;
        default:
            break;
    }
    executeHook(HOOK_JTL_INC_SWITCH_REDIRECT, ['cRedirect' => &$cRedirect, 'oRedirect' => &$oRedirect]);

    $_SESSION['JTL_REDIRECT'] = $oRedirect;

    return $oRedirect;
}

/**
 * Schaut nach dem Login, ob Kategorien nicht sichtbar sein dürfen und löscht eventuell diese aus der Session
 *
 * @param int $kKundengruppe
 * @return bool
 */
function pruefeKategorieSichtbarkeit($kKundengruppe)
{
    $kKundengruppe = (int)$kKundengruppe;
    if (!$kKundengruppe) {
        return false;
    }
    $cacheID      = 'catlist_p_' . Shop::Cache()->getBaseID(false, false, $kKundengruppe, true, false, true);
    $save         = false;
    $categoryList = Shop::Cache()->get($cacheID);
    $useCache     = true;
    if ($categoryList === false) {
        $useCache     = false;
        $categoryList = $_SESSION;
    }

    $oKatSichtbarkeit_arr = Shop::DB()->selectAll('tkategoriesichtbarkeit', 'kKundengruppe', $kKundengruppe, 'kKategorie');

    if (is_array($oKatSichtbarkeit_arr) && count($oKatSichtbarkeit_arr) > 0) {
        $cKatKey_arr = array_keys($categoryList);
        foreach ($oKatSichtbarkeit_arr as $oKatSichtbarkeit) {
            for ($i = 0; $i < count($_SESSION['kKategorieVonUnterkategorien_arr'][0]); $i++) {
                if ($categoryList['kKategorieVonUnterkategorien_arr'][0][$i] == $oKatSichtbarkeit->kKategorie) {
                    unset($categoryList['kKategorieVonUnterkategorien_arr'][0][$i]);
                    $save = true;
                }
                $categoryList['kKategorieVonUnterkategorien_arr'][0] = array_merge($categoryList['kKategorieVonUnterkategorien_arr'][0]);
            }

            if (isset($categoryList['kKategorieVonUnterkategorien_arr'][$oKatSichtbarkeit->kKategorie])) {
                unset($categoryList['kKategorieVonUnterkategorien_arr'][$oKatSichtbarkeit->kKategorie]);
                $save = true;
            }
            $ckkCount = count($cKatKey_arr);
            for ($i = 0; $i < $ckkCount; $i++) {
                if (isset($categoryList['oKategorie_arr'][$oKatSichtbarkeit->kKategorie])) {
                    unset($categoryList['oKategorie_arr'][$oKatSichtbarkeit->kKategorie]);
                    $save = true;
                }
            }
        }
    }
    if ($save === true) {
        if ($useCache === true) {
            //category list has changed - write back changes to cache
            Shop::Cache()->set($cacheID, $categoryList, [CACHING_GROUP_CATEGORY]);
        } else {
            $_SESSION['oKategorie_arr'] = $categoryList;
        }
    }

    return true;
}

/**
 * @param int $kKunde
 * @return bool
 */
function setzeWarenkorbPersInWarenkorb($kKunde)
{
    $kKunde = (int)$kKunde;
    if (!$kKunde) {
        return false;
    }
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    if (isset($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $oWarenkorbPos) {
            if ($oWarenkorbPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $kArtikelGeschenk = (int)$oWarenkorbPos->kArtikel;
                // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
                $oArtikelGeschenk = Shop::DB()->query(
                    "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                        JOIN tartikel ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                        AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true), 1
                );
                if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
                    fuegeEinInWarenkorbPers($kArtikelGeschenk, 1, [], null, null, (int)C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
            } else {
                fuegeEinInWarenkorbPers($oWarenkorbPos->kArtikel, $oWarenkorbPos->nAnzahl, $oWarenkorbPos->WarenkorbPosEigenschaftArr, $oWarenkorbPos->cUnique, $oWarenkorbPos->kKonfigitem);
            }
        }
        $_SESSION['Warenkorb']->PositionenArr = [];
    }

    $oWarenkorbPers = new WarenkorbPers($kKunde);
    if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
        foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
            if ($oWarenkorbPersPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $kArtikelGeschenk = (int)$oWarenkorbPersPos->kArtikel;
                // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
                $oArtikelGeschenk = Shop::DB()->query(
                    "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                        JOIN tartikel ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                        AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true), 1
                );
                if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
                    if ($oArtikelGeschenk->fLagerbestand <= 0 && $oArtikelGeschenk->cLagerKleinerNull === 'N' && $oArtikelGeschenk->cLagerBeachten === 'Y') {
                        break;
                    } else {
                        executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                                              ->fuegeEin($kArtikelGeschenk, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
                    }
                }
            } else {
                fuegeEinInWarenkorb($oWarenkorbPersPos->kArtikel, $oWarenkorbPersPos->fAnzahl, $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr, 1, $oWarenkorbPersPos->cUnique, $oWarenkorbPersPos->kKonfigitem);
            }
        }
    }

    return true;
}

/**
 * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
 *
 * @param int $kKundengruppe
 */
function pruefeWarenkorbArtikelSichtbarkeit($kKundengruppe)
{
    $kKundengruppe = (int)$kKundengruppe;
    if ($kKundengruppe > 0 && isset($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPosition) {
            // Wenn die Position ein Artikel ist
            $bKonfig = (isset($oPosition->cUnique) && strlen($oPosition->cUnique) === 10);
            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && !$bKonfig) {
                // Artikelsichtbarkeit prüfen
                $oArtikelSichtbarkeit = Shop::DB()->query(
                    "SELECT kArtikel
                      FROM tartikelsichtbarkeit
                      WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                        AND kKundengruppe = " . $kKundengruppe, 1
                );

                if (isset($oArtikelSichtbarkeit->kArtikel) && $oArtikelSichtbarkeit->kArtikel > 0 && intval($_SESSION['Warenkorb']->PositionenArr[$i]->kKonfigitem) === 0) {
                    unset($_SESSION['Warenkorb']->PositionenArr[$i]);
                }

                // Auf vorhandenen Preis prüfen
                $oArtikelPreis = Shop::DB()->query(
                    "SELECT fVKNetto
                       FROM tpreise
                       WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                           AND kKundengruppe = " . $kKundengruppe, 1
                );

                if (!isset($oArtikelPreis->fVKNetto)) {
                    unset($_SESSION['Warenkorb']->PositionenArr[$i]);
                }
            }
        }
    }
}
