<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $nPos
 * @return null|void
 */
function loescheWarenkorbPosition($nPos)
{
    //Kupons bearbeiten
    if (!isset($_SESSION['Warenkorb']->PositionenArr[$nPos])) {
        return;
    }
    if ($_SESSION['Warenkorb']->PositionenArr[$nPos]->nPosTyp != C_WARENKORBPOS_TYP_ARTIKEL &&
        $_SESSION['Warenkorb']->PositionenArr[$nPos]->nPosTyp != C_WARENKORBPOS_TYP_GRATISGESCHENK
    ) {
        return;
    }
    $cUnique = $_SESSION['Warenkorb']->PositionenArr[$nPos]->cUnique;
    // Kindartikel?
    if (strlen($cUnique) > 0 && $_SESSION['Warenkorb']->PositionenArr[$nPos]->kKonfigitem > 0) {
        return;
    }
    executeHook(HOOK_WARENKORB_LOESCHE_POSITION, array('nPos' => $nPos, 'position' => &$_SESSION['Warenkorb']->PositionenArr[$nPos]));

    if (class_exists('Upload')) {
        Upload::deleteArtikelUploads($_SESSION['Warenkorb']->PositionenArr[$nPos]->kArtikel);
    }

    unset($_SESSION['Warenkorb']->PositionenArr[$nPos]);
    $_SESSION['Warenkorb']->PositionenArr = array_merge($_SESSION['Warenkorb']->PositionenArr);
    // Kindartikel löschen
    if (strlen($cUnique) > 0) {
        $positionCount = count($_SESSION['Warenkorb']->PositionenArr);
        for ($i = 0; $i < $positionCount; $i++) {
            if (isset($_SESSION['Warenkorb']->PositionenArr[$i]->cUnique) && $_SESSION['Warenkorb']->PositionenArr[$i]->cUnique == $cUnique) {
                unset($_SESSION['Warenkorb']->PositionenArr[$i]);
                $_SESSION['Warenkorb']->PositionenArr = array_merge($_SESSION['Warenkorb']->PositionenArr);
                $i                                    = -1;
            }
        }
    }

    loescheAlleSpezialPos();

    if (!$_SESSION['Warenkorb']->enthaltenSpezialPos(C_WARENKORBPOS_TYP_ARTIKEL)) {
        unset($_SESSION['Kupon']);
        $_SESSION['Warenkorb'] = new Warenkorb();
    }
    foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
        if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            // Prüfen ob der Artikel wirklich ein Gratis Geschenk ist und ob der Gesamtpreis noch zum Gratisgeschenk passt
            $oArtikelGeschenk = Shop::DB()->query(
                "SELECT kArtikel
                    FROM tartikelattribut
                    WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                       AND cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                       AND CAST(cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true), 1
            );

            if ($oArtikelGeschenk->kArtikel == 0) {
                $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
            break;
        }
    }
    // Lösche Position aus dem WarenkorbPersPos
    if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde > 0) {
        $oWarenkorbPers = new WarenkorbPers($_SESSION['Kunde']->kKunde);
        $oWarenkorbPers->entferneAlles()
                       ->bauePersVonSession();
    }
}

/**
 *
 */
function uebernehmeWarenkorbAenderungen()
{
    unset($_SESSION['cPlausi_arr']);
    unset($_SESSION['cPost_arr']);
    // Gratis Geschenk wurde hinzugefuegt
    if (isset($_POST['gratishinzufuegen'])) {
        return;
    }
    // wurden Positionen gelöscht?
    $drop = null;
    $post = false;
    if (isset($_POST['dropPos'])) {
        $drop = intval($_POST['dropPos']);
        $post = true;
    } elseif (isset($_GET['dropPos'])) {
        $drop = intval($_GET['dropPos']);
    }
    if ($drop !== null) {
        loescheWarenkorbPosition($drop);
        if ($post) {
            //prg
            header('Location: ' . Shop::getURL(true) . '/warenkorb.php', true, 303);
        }

        return;
    }
    //wurde WK aktualisiert?
    if (empty($_POST['anzahl'])) {
        return;
    }
    $anzahlPositionen            = count($_SESSION['Warenkorb']->PositionenArr);
    $bMindestensEinePosGeaendert = false;

    //variationen wurden gesetzt oder anzahl der positionen verändert?
    $kArtikelGratisgeschenk = 0;
    for ($i = 0; $i < $anzahlPositionen; $i++) {
        if ($_SESSION['Warenkorb']->PositionenArr[$i]->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
            if ($_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel == 0) {
                continue;
            }
            //stückzahlen verändert?
            if (isset($_POST['anzahl'][$i])) {
                $Artikel = new Artikel();
                $Artikel->fuelleArtikel($_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel, Artikel::getDefaultOptions());

                $_POST['anzahl'][$i] = str_replace(',', '.', $_POST['anzahl'][$i]);

                if (intval($_POST['anzahl'][$i]) != $_POST['anzahl'][$i] && $Artikel->cTeilbar !== 'Y') {
                    $_POST['anzahl'][$i] = min(intval($_POST['anzahl'][$i]), 1);
                }
                $gueltig = true;
                // Abnahmeintervall
                if ($Artikel->fAbnahmeintervall > 0) {
                    $dVielfache = round($Artikel->fAbnahmeintervall * ceil($_POST['anzahl'][$i] / $Artikel->fAbnahmeintervall), 2);

                    if ($dVielfache != $_POST['anzahl'][$i]) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                    }
                }
                if (floatval($_POST['anzahl'][$i]) + $_SESSION['Warenkorb']->gibAnzahlEinesArtikels($_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel, $i) <
                    $_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->fMindestbestellmenge
                ) {
                    //mindestbestellmenge nicht erreicht
                    $gueltig                         = false;
                    $_SESSION['Warenkorbhinweise'][] = lang_mindestbestellmenge($_SESSION['Warenkorb']->PositionenArr[$i]->Artikel, floatval($_POST['anzahl'][$i]));
                }
                //hole akt. lagerbestand vom artikel
                if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation !== 'Y' &&
                    $Artikel->cLagerKleinerNull !== 'Y' &&
                    $Artikel->fPackeinheit * (floatval($_POST['anzahl'][$i]) + $_SESSION['Warenkorb']->gibAnzahlEinesArtikels($_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel, $i)) > $Artikel->fLagerbestand
                ) {
                    $gueltig                         = false;
                    $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                }
                // maximale Bestellmenge des Artikels beachten
                if (isset($Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE]) && $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE] > 0) {
                    if ($_POST['anzahl'][$i] > $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE]) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    }
                }
                //schaue, ob genug auf Lager von jeder var
                if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation === 'Y' &&
                    $Artikel->cLagerKleinerNull !== 'Y' &&
                    is_array($_SESSION['Warenkorb']->PositionenArr[$i]->WarenkorbPosEigenschaftArr)
                ) {
                    foreach ($_SESSION['Warenkorb']->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $eWert) {
                        $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
                        if ($EigenschaftWert->fPackeinheit * (floatval($_POST['anzahl'][$i]) +
                                $_SESSION['Warenkorb']->gibAnzahlEinerVariation($_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel, $eWert->kEigenschaftWert, $i)) > $EigenschaftWert->fLagerbestand
                        ) {
                            $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                            $gueltig                         = false;
                            break;
                        }
                    }
                }
                // Stücklistenkomponente oder Stückliste und ein Teil ist bereits im Warenkorb?
                $xReturn = pruefeWarenkorbStueckliste($Artikel, $_POST['anzahl'][$i]);
                if ($xReturn !== null) {
                    $gueltig                         = false;
                    $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                }

                if ($gueltig) {
                    $_SESSION['Warenkorb']->PositionenArr[$i]->nAnzahl = floatval($_POST['anzahl'][$i]);
                    $_SESSION['Warenkorb']->PositionenArr[$i]->fPreis  = $Artikel->gibPreis(
                        $_SESSION['Warenkorb']->PositionenArr[$i]->nAnzahl,
                        $_SESSION['Warenkorb']->PositionenArr[$i]->WarenkorbPosEigenschaftArr
                    );
                    $_SESSION['Warenkorb']->PositionenArr[$i]->setzeGesamtpreisLoacalized();
                    $_SESSION['Warenkorb']->PositionenArr[$i]->fGesamtgewicht = $_SESSION['Warenkorb']->PositionenArr[$i]->gibGesamtgewicht();

                    $bMindestensEinePosGeaendert = true;
                }
            }
            // Grundpreise bei Staffelpreisen
            if (isset($_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->fVPEWert) && $_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->fVPEWert > 0) {
                $nLast = 0;
                for ($j = 1; $j <= 5; $j++) {
                    $cStaffel = 'nAnzahl' . $j;
                    if (isset($_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->Preise->$cStaffel) && $_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->Preise->$cStaffel > 0) {
                        if ($_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->Preise->$cStaffel <= $_SESSION['Warenkorb']->PositionenArr[$i]->nAnzahl) {
                            $nLast = $j;
                        }
                    }
                }
                if ($nLast > 0) {
                    $cStaffel = 'fPreis' . $nLast;
                    $_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->baueVPE($_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->Preise->$cStaffel);
                } else {
                    $_SESSION['Warenkorb']->PositionenArr[$i]->Artikel->baueVPE();
                }
            }
        } elseif ($_SESSION['Warenkorb']->PositionenArr[$i]->nPosTyp == C_WARENKORBPOS_TYP_GRATISGESCHENK) { // Gratisgeschenk?
            $kArtikelGratisgeschenk = $_SESSION['Warenkorb']->PositionenArr[$i]->kArtikel;
        }
    }
    $kArtikelGratisgeschenk = (int)$kArtikelGratisgeschenk;
    //positionen mit nAnzahl = 0 müssen gelöscht werden
    $_SESSION['Warenkorb']->loescheNullPositionen();
    if (!$_SESSION['Warenkorb']->enthaltenSpezialPos(C_WARENKORBPOS_TYP_ARTIKEL)) {
        $_SESSION['Warenkorb'] = new Warenkorb();
    }
    if ($bMindestensEinePosGeaendert) {
        $oKuponTmp = null;
        //existiert ein proz. Kupon, der auf die neu eingefügte Pos greift?
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent' && $_SESSION['Kupon']->nGanzenWKRabattieren == 0) {
            if ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true) >= $_SESSION['Kupon']->fMindestbestellwert) {
                $oKuponTmp = $_SESSION['Kupon'];
            }
        }
        loescheAlleSpezialPos();
        if (isset($oKuponTmp->kKupon) && $oKuponTmp->kKupon > 0) {
            $_SESSION['Kupon'] = $oKuponTmp;
            if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
                foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oWKPosition) {
                    $_SESSION['Warenkorb']->PositionenArr[$i] = checkeKuponWKPos($oWKPosition, $_SESSION['Kupon']);
                }
            }
        }
    }
    $_SESSION['Warenkorb']->setzePositionsPreise();
    // Gesamtsumme Warenkorb < Gratisgeschenk && Gratisgeschenk in den Pos?
    if ($kArtikelGratisgeschenk > 0) {
        // Prüfen, ob der Artikel wirklich ein Gratis Geschenk ist
        $oArtikelGeschenk = Shop::DB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE kArtikel = " . $kArtikelGratisgeschenk . "
                    AND cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                    AND CAST(cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true), 1
        );

        if (empty($oArtikelGeschenk->kArtikel)) {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }
    }
    // Lösche Position aus dem WarenkorbPersPos
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        $oWarenkorbPers = new WarenkorbPers($_SESSION['Kunde']->kKunde);
        $oWarenkorbPers->entferneAlles()
                       ->bauePersVonSession();
    }
}

/**
 * @return string
 */
function checkeSchnellkauf()
{
    $hinweis = '';
    if (isset($_POST['schnellkauf']) && intval($_POST['schnellkauf']) > 0 && !empty($_POST['ean'])) {
        $hinweis = Shop::Lang()->get('eanNotExist', 'global') . ' ' . StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']));
        //gibts artikel mit dieser artnr?
        $artikel = Shop::DB()->select('tartikel', 'cArtNr', StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean'])));
        if (empty($artikel->kArtikel)) {
            $artikel = Shop::DB()->select('tartikel', 'cBarcode', StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean'])));
        }
        if (isset($artikel->kArtikel) && $artikel->kArtikel > 0) {
            if (fuegeEinInWarenkorb($artikel->kArtikel, 1, ArtikelHelper::getSelectedPropertiesForArticle($artikel->kArtikel))) {
                $hinweis = $artikel->cName . ' ' . Shop::Lang()->get('productAddedToCart', 'global');
            }
        }
    }

    return $hinweis;
}

/**
 *
 */
function loescheAlleSpezialPos()
{
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS)
                          ->checkIfCouponIsStillValid();
    unset($_SESSION['Versandart']);
    unset($_SESSION['VersandKupon']);
    unset($_SESSION['oVersandfreiKupon']);
    unset($_SESSION['Verpackungen']);
    unset($_SESSION['TrustedShops']);
    unset($_SESSION['Zahlungsart']);
    resetNeuKundenKupon();
    altenKuponNeuBerechnen();

    executeHook(HOOK_WARENKORB_LOESCHE_ALLE_SPEZIAL_POS);

    $_SESSION['Warenkorb']->setzePositionsPreise();
}

/**
 * @return stdClass
 */
function gibXSelling()
{
    $oXselling = new stdClass();
    $conf      = Shop::getSettings(array(CONF_KAUFABWICKLUNG));

    if ($conf['kaufabwicklung']['warenkorb_xselling_anzeigen'] === 'Y') {
        $oWarenkorbPos_arr = $_SESSION['Warenkorb']->PositionenArr;

        if (is_array($oWarenkorbPos_arr) && count($oWarenkorbPos_arr) > 0) {
            $cSQL1 = 'WHERE kArtikel IN (';
            $cSQL2 = 'AND kXSellArtikel NOT IN (';
            foreach ($oWarenkorbPos_arr as $i => $oWarenkorbPos) {
                if (isset($oWarenkorbPos->Artikel->kArtikel) && $oWarenkorbPos->Artikel->kArtikel > 0) {
                    $cSQL1 .= (int)$oWarenkorbPos->Artikel->kArtikel . ', ';
                    $cSQL2 .= (int)$oWarenkorbPos->Artikel->kArtikel . ', ';
                }
            }
            $cSQL1 = substr($cSQL1, 0, strlen($cSQL1) - 2);
            $cSQL1 .= ')';
            $cSQL2 = substr($cSQL2, 0, strlen($cSQL2) - 2);
            $cSQL2 .= ')';
            $oXsellkauf_arr = Shop::DB()->query(
                "SELECT *
                    FROM txsellkauf
                    " . $cSQL1 . "
                    " . $cSQL2 . "
                    GROUP BY kXSellArtikel
                    ORDER BY nAnzahl DESC, rand()
                    LIMIT " . intval($conf['kaufabwicklung']['warenkorb_xselling_anzahl']), 2
            );

            if (is_array($oXsellkauf_arr) && count($oXsellkauf_arr) > 0) {
                if (!isset($oXselling->Kauf)) {
                    $oXselling->Kauf = new stdClass();
                }
                $oXselling->Kauf->Artikel = array();
                $oArtikelOptionen         = Artikel::getDefaultOptions();
                foreach ($oXsellkauf_arr as $oXsellkauf) {
                    $oArtikel = new Artikel();
                    $oArtikel->fuelleArtikel($oXsellkauf->kXSellArtikel, $oArtikelOptionen);

                    if ($oArtikel->kArtikel > 0 && $oArtikel->aufLagerSichtbarkeit()) {
                        $oXselling->Kauf->Artikel[] = $oArtikel;
                    }
                }
            }
        }
    }

    return $oXselling;
}

/**
 * @param array $Einstellungen
 * @return array
 */
function gibGratisGeschenke($Einstellungen)
{
    $oArtikelGeschenke_arr = array();

    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $cSQLSort = ' ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC';
        if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
            $cSQLSort = ' ORDER BY tartikel.cName';
        } elseif ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
            $cSQLSort = ' ORDER BY tartikel.fLagerbestand DESC';
        }

        $oArtikelGeschenkeTMP_arr = Shop::DB()->query(
            "SELECT tartikel.kArtikel, tartikelattribut.cWert
                FROM tartikel
                JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                WHERE (tartikel.fLagerbestand > 0 || (tartikel.fLagerbestand <= 0 && (tartikel.cLagerBeachten = 'N' || tartikel.cLagerKleinerNull = 'Y')))
                    AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                    AND CAST(tartikelattribut.cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true) .
            $cSQLSort . " LIMIT 20", 2
        );

        if (is_array($oArtikelGeschenkeTMP_arr) && count($oArtikelGeschenkeTMP_arr) > 0) {
            foreach ($oArtikelGeschenkeTMP_arr as $i => $oArtikelGeschenkeTMP) {
                $oArtikel = new Artikel();
                $oArtikel->fuelleArtikel($oArtikelGeschenkeTMP->kArtikel, Artikel::getDefaultOptions());
                $oArtikel->cBestellwert = gibPreisStringLocalized(doubleval($oArtikelGeschenkeTMP->cWert));

                if (($oArtikel->kEigenschaftKombi > 0 || !is_array($oArtikel->Variationen) || count($oArtikel->Variationen) === 0) && $oArtikel->kArtikel > 0) {
                    $oArtikelGeschenke_arr[] = $oArtikel;
                }
            }
        }
    }

    return $oArtikelGeschenke_arr;
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
 *
 * @param array $Einstellungen
 * @return string
 */
function pruefeBestellMengeUndLagerbestand($Einstellungen = array())
{
    $cHinweis     = '';
    $cArtikelName = '';
    $bVorhanden   = false;
    $cISOSprache  = $_SESSION['cISOSprache'];

    if (!is_array($Einstellungen) || !isset($Einstellungen['global'])) {
        $Einstellungen = Shop::getSettings(array(CONF_GLOBAL));
    }

    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPosition) {
            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                // Mit Lager arbeiten und Lagerbestand darf < 0 werden?
                if (isset($oPosition->Artikel) && $oPosition->Artikel->cLagerBeachten === 'Y' &&
                    $oPosition->Artikel->cLagerKleinerNull === 'Y' &&
                    $Einstellungen['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                ) {
                    if ($oPosition->nAnzahl > $oPosition->Artikel->fLagerbestand) {
                        $bVorhanden  = true;
                        $cName       = is_array($oPosition->cName)
                            ? $oPosition->cName[$cISOSprache]
                            : $oPosition->cName;

                        $cArtikelName .= '<li>' . $cName . '</li>';
                    }
                }
            }
        }
    }
    $_SESSION['Warenkorb']->cEstimatedDelivery = $_SESSION['Warenkorb']->getEstimatedDeliveryTime();

    if ($bVorhanden) {
        $cHinweis = sprintf(Shop::Lang()->get('orderExpandInventory', 'basket'), '<ul>' . $cArtikelName . '</ul>');
    }

    return $cHinweis;
}

/**
 * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
 */
function validiereWarenkorbKonfig()
{
    if (class_exists('Konfigurator')) {
        Konfigurator::postcheckBasket($_SESSION['Warenkorb']);
    }
}
