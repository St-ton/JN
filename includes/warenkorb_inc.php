<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

/**
 * @param array $nPos_arr
 * @return null|void
 */
function loescheWarenkorbPositionen($nPos_arr)
{
    $cart        = Session::Cart();
    $cUnique_arr = [];
    foreach ($nPos_arr as $nPos) {
        //Kupons bearbeiten
        if (!isset($cart->PositionenArr[$nPos])) {
            return;
        }
        if ($cart->PositionenArr[$nPos]->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL
            && $cart->PositionenArr[$nPos]->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK
        ) {
            return;
        }
        $cUnique = $cart->PositionenArr[$nPos]->cUnique;
        // Kindartikel?
        if (!empty($cUnique) && $cart->PositionenArr[$nPos]->kKonfigitem > 0) {
            return;
        }
        executeHook(HOOK_WARENKORB_LOESCHE_POSITION, [
            'nPos'     => $nPos,
            'position' => &$cart->PositionenArr[$nPos]
        ]);

        if (class_exists('Upload')) {
            Upload::deleteArtikelUploads($cart->PositionenArr[$nPos]->kArtikel);
        }

        $cUnique_arr[] = $cUnique;

        unset($cart->PositionenArr[$nPos]);
    }
    $cart->PositionenArr = array_merge($cart->PositionenArr);
    foreach ($cUnique_arr as $cUnique) {
        // Kindartikel löschen
        if (!empty($cUnique)) {
            $positionCount = count($cart->PositionenArr);
            for ($i = 0; $i < $positionCount; $i++) {
                if (isset($cart->PositionenArr[$i]->cUnique)
                    && $cart->PositionenArr[$i]->cUnique == $cUnique
                ) {
                    unset($cart->PositionenArr[$i]);
                    $cart->PositionenArr = array_merge($cart->PositionenArr);
                    $i                   = -1;
                }
            }
        }
    }
    loescheAlleSpezialPos();
    if (!$cart->enthaltenSpezialPos(C_WARENKORBPOS_TYP_ARTIKEL)) {
        unset($_SESSION['Kupon']);
        $_SESSION['Warenkorb'] = new Warenkorb();
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
    freeGiftStillValid();
    // Lösche Position aus dem WarenkorbPersPos
    if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde > 0) {
        (new WarenkorbPers($_SESSION['Kunde']->kKunde))->entferneAlles()->bauePersVonSession();
    }
}

/**
 * @param int $nPos
 * @return void
 */
function loescheWarenkorbPosition($nPos)
{
    loescheWarenkorbPositionen([$nPos]);
}

/**
 *
 */
function uebernehmeWarenkorbAenderungen()
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
    // Gratis Geschenk wurde hinzugefuegt
    if (isset($_POST['gratishinzufuegen'])) {
        return;
    }
    // wurden Positionen gelöscht?
    $drop = null;
    $post = false;
    $cart = Session::Cart();
    if (isset($_POST['dropPos'])) {
        $drop = (int)$_POST['dropPos'];
        $post = true;
    } elseif (isset($_GET['dropPos'])) {
        $drop = (int)$_GET['dropPos'];
    }
    if ($drop !== null) {
        loescheWarenkorbPosition($drop);
        freeGiftStillValid();
        if ($post) {
            //prg
            header('Location: ' . Shop::Container()->getLinkService()
                                                   ->getStaticRoute('warenkorb.php', true, true), true, 303);
        }

        return;
    }
    //wurde WK aktualisiert?
    if (empty($_POST['anzahl'])) {
        return;
    }
    $anzahlPositionen            = count(Session::Cart()->PositionenArr);
    $bMindestensEinePosGeaendert = false;
    //variationen wurden gesetzt oder anzahl der positionen verändert?
    $kArtikelGratisgeschenk = 0;
    foreach ($cart->PositionenArr as $i => $position) {
        if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
            if ($position->kArtikel == 0) {
                continue;
            }
            //stückzahlen verändert?
            if (isset($_POST['anzahl'][$i])) {
                $Artikel = (new Artikel())->fuelleArtikel(
                    $position->kArtikel,
                    Artikel::getDefaultOptions()
                );

                $_POST['anzahl'][$i] = str_replace(',', '.', $_POST['anzahl'][$i]);

                if ((int)$_POST['anzahl'][$i] != $_POST['anzahl'][$i] && $Artikel->cTeilbar !== 'Y') {
                    $_POST['anzahl'][$i] = min((int)$_POST['anzahl'][$i], 1);
                }
                $gueltig = true;
                // Abnahmeintervall
                if ($Artikel->fAbnahmeintervall > 0) {
                    if (function_exists('bcdiv')) {
                        $dVielfache = round(
                            $Artikel->fAbnahmeintervall * ceil(bcdiv($_POST['anzahl'][$i], $Artikel->fAbnahmeintervall, 3)),
                            2
                        );
                    } else {
                        $dVielfache = round(
                            $Artikel->fAbnahmeintervall * ceil($_POST['anzahl'][$i] / $Artikel->fAbnahmeintervall),
                            2
                        );
                    }

                    if ($dVielfache != $_POST['anzahl'][$i]) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                    }
                }
                if ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinesArtikels(
                    $position->kArtikel,
                    $i
                ) < $position->Artikel->fMindestbestellmenge) {
                    //mindestbestellmenge nicht erreicht
                    $gueltig                         = false;
                    $_SESSION['Warenkorbhinweise'][] = lang_mindestbestellmenge(
                        $position->Artikel,
                        (float)$_POST['anzahl'][$i]
                    );
                }
                //hole akt. lagerbestand vom artikel
                if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation !== 'Y'
                    && $Artikel->cLagerKleinerNull !== 'Y'
                    && $Artikel->fPackeinheit * ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinesArtikels(
                        $position->kArtikel,
                        $i
                    )) > $Artikel->fLagerbestand
                ) {
                    $gueltig                         = false;
                    $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                }
                // maximale Bestellmenge des Artikels beachten
                if (isset($Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE])
                    && $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
                ) {
                    if ($_POST['anzahl'][$i] > $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE]) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    }
                }
                //schaue, ob genug auf Lager von jeder var
                if ($Artikel->cLagerBeachten === 'Y'
                    && $Artikel->cLagerVariation === 'Y'
                    && $Artikel->cLagerKleinerNull !== 'Y'
                    && is_array($position->WarenkorbPosEigenschaftArr)
                ) {
                    foreach ($position->WarenkorbPosEigenschaftArr as $eWert) {
                        $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
                        if ($EigenschaftWert->fPackeinheit * ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinerVariation(
                            $position->kArtikel,
                            $eWert->kEigenschaftWert,
                            $i
                        )) > $EigenschaftWert->fLagerbestand) {
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
                    $position->nAnzahl = (float)$_POST['anzahl'][$i];
                    $position->fPreis  = $Artikel->gibPreis(
                        $position->nAnzahl,
                        $position->WarenkorbPosEigenschaftArr
                    );
                    $position->setzeGesamtpreisLocalized();
                    $position->fGesamtgewicht = $position->gibGesamtgewicht();

                    $bMindestensEinePosGeaendert = true;
                }
            }
            // Grundpreise bei Staffelpreisen
            if (isset($position->Artikel->fVPEWert)
                && $position->Artikel->fVPEWert > 0
            ) {
                $nLast = 0;
                for ($j = 1; $j <= 5; $j++) {
                    $cStaffel = 'nAnzahl' . $j;
                    if (isset($position->Artikel->Preise->$cStaffel)
                        && $position->Artikel->Preise->$cStaffel > 0
                        && $position->Artikel->Preise->$cStaffel <= $position->nAnzahl
                    ) {
                        $nLast = $j;
                    }
                }
                if ($nLast > 0) {
                    $cStaffel = 'fPreis' . $nLast;
                    $position->Artikel->baueVPE($position->Artikel->Preise->$cStaffel);
                } else {
                    $position->Artikel->baueVPE();
                }
            }
        } elseif ($position->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $kArtikelGratisgeschenk = $position->kArtikel;
        }
    }
    $kArtikelGratisgeschenk = (int)$kArtikelGratisgeschenk;
    //positionen mit nAnzahl = 0 müssen gelöscht werden
    $cart->loescheNullPositionen();
    if (!$cart->enthaltenSpezialPos(C_WARENKORBPOS_TYP_ARTIKEL)) {
        $_SESSION['Warenkorb'] = new Warenkorb();
        $cart = $_SESSION['Warenkorb'];
    }
    if ($bMindestensEinePosGeaendert) {
        $oKuponTmp = null;
        //existiert ein proz. Kupon, der auf die neu eingefügte Pos greift?
        if (isset($_SESSION['Kupon'])
            && $_SESSION['Kupon']->cWertTyp === 'prozent'
            && $_SESSION['Kupon']->nGanzenWKRabattieren == 0
        ) {
            if ($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true) >= $_SESSION['Kupon']->fMindestbestellwert) {
                $oKuponTmp = $_SESSION['Kupon'];
            }
        }
        loescheAlleSpezialPos();
        if (isset($oKuponTmp->kKupon) && $oKuponTmp->kKupon > 0) {
            $_SESSION['Kupon'] = $oKuponTmp;
            foreach ($cart->PositionenArr as $i => $oWKPosition) {
                $cart->PositionenArr[$i] = checkeKuponWKPos($oWKPosition, $_SESSION['Kupon']);
            }
        }
        plausiNeukundenKupon();
    }
    $cart->setzePositionsPreise();
    // Gesamtsumme Warenkorb < Gratisgeschenk && Gratisgeschenk in den Pos?
    if ($kArtikelGratisgeschenk > 0) {
        // Prüfen, ob der Artikel wirklich ein Gratis Geschenk ist
        $oArtikelGeschenk = Shop::Container()->getDB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE kArtikel = " . $kArtikelGratisgeschenk . "
                    AND cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                    AND CAST(cWert AS DECIMAL) <= " .
            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (empty($oArtikelGeschenk->kArtikel)) {
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
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
    if (isset($_POST['schnellkauf']) && (int)$_POST['schnellkauf'] > 0 && !empty($_POST['ean'])) {
        $hinweis = Shop::Lang()->get('eanNotExist') . ' ' .
            StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']));
        //gibts artikel mit dieser artnr?
        $artikel = Shop::Container()->getDB()->select(
            'tartikel',
            'cArtNr',
            StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']))
        );
        if (empty($artikel->kArtikel)) {
            $artikel = Shop::Container()->getDB()->select(
                'tartikel',
                'cBarcode',
                StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']))
            );
        }
        if (isset($artikel->kArtikel) && $artikel->kArtikel > 0) {
            $oArtikel = (new Artikel())->fuelleArtikel($artikel->kArtikel, Artikel::getDefaultOptions());
            if ($oArtikel !== null && $oArtikel->kArtikel > 0 && fuegeEinInWarenkorb(
                $artikel->kArtikel,
                1,
                ArtikelHelper::getSelectedPropertiesForArticle($artikel->kArtikel)
            )) {
                $hinweis = $artikel->cName . ' ' . Shop::Lang()->get('productAddedToCart');
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
    Session::Cart()
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS)
           ->checkIfCouponIsStillValid();
    unset(
        $_SESSION['Versandart'],
        $_SESSION['VersandKupon'],
        $_SESSION['oVersandfreiKupon'],
        $_SESSION['Verpackung'],
        $_SESSION['TrustedShops'],
        $_SESSION['Zahlungsart']
    );
    resetNeuKundenKupon();
    altenKuponNeuBerechnen();

    executeHook(HOOK_WARENKORB_LOESCHE_ALLE_SPEZIAL_POS);

    Session::Cart()->setzePositionsPreise();
}

/**
 * @return stdClass
 */
function gibXSelling()
{
    $oXselling = new stdClass();
    $conf      = Shop::getSettings([CONF_KAUFABWICKLUNG]);

    if ($conf['kaufabwicklung']['warenkorb_xselling_anzeigen'] === 'Y') {
        $oWarenkorbPos_arr = Session::Cart()->PositionenArr;

        if (is_array($oWarenkorbPos_arr) && count($oWarenkorbPos_arr) > 0) {
            $kArtikel_arr = [];

            foreach ($oWarenkorbPos_arr as $i => $oWarenkorbPos) {
                if (isset($oWarenkorbPos->Artikel->kArtikel) && $oWarenkorbPos->Artikel->kArtikel > 0) {
                    $kArtikel_arr[] = (int)$oWarenkorbPos->Artikel->kArtikel;
                }
            }

            if (count($kArtikel_arr) > 0) {
                $cArtikel_str   = implode(', ', $kArtikel_arr);
                $oXsellkauf_arr = Shop::Container()->getDB()->query(
                    "SELECT *
                        FROM txsellkauf
                        WHERE kArtikel IN ({$cArtikel_str})
                            AND kXSellArtikel NOT IN ({$cArtikel_str})
                        GROUP BY kXSellArtikel
                        ORDER BY nAnzahl DESC
                        LIMIT " . (int)$conf['kaufabwicklung']['warenkorb_xselling_anzahl'],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );

                if (is_array($oXsellkauf_arr) && count($oXsellkauf_arr) > 0) {
                    if (!isset($oXselling->Kauf)) {
                        $oXselling->Kauf = new stdClass();
                    }
                    $oXselling->Kauf->Artikel = [];
                    $defaultOptions           = Artikel::getDefaultOptions();
                    foreach ($oXsellkauf_arr as $oXsellkauf) {
                        $oArtikel = (new Artikel())->fuelleArtikel($oXsellkauf->kXSellArtikel, $defaultOptions);
                        if ($oArtikel !== null && $oArtikel->kArtikel > 0 && $oArtikel->aufLagerSichtbarkeit()) {
                            $oXselling->Kauf->Artikel[] = $oArtikel;
                        }
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
function gibGratisGeschenke(array $Einstellungen)
{
    $oArtikelGeschenke_arr = [];
    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $cSQLSort = ' ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC';
        if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
            $cSQLSort = ' ORDER BY tartikel.cName';
        } elseif ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
            $cSQLSort = ' ORDER BY tartikel.fLagerbestand DESC';
        }

        $oArtikelGeschenkeTMP_arr = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel, tartikelattribut.cWert
                FROM tartikel
                JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                WHERE (tartikel.fLagerbestand > 0 || 
                      (tartikel.fLagerbestand <= 0 && 
                      (tartikel.cLagerBeachten = 'N' || tartikel.cLagerKleinerNull = 'Y')))
                    AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                    AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                        Session::Cart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true) .
            $cSQLSort . " LIMIT 20",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($oArtikelGeschenkeTMP_arr as $i => $oArtikelGeschenkeTMP) {
            $oArtikel = (new Artikel())->fuelleArtikel($oArtikelGeschenkeTMP->kArtikel, Artikel::getDefaultOptions());
            if ($oArtikel !== null
                && ($oArtikel->kEigenschaftKombi > 0
                    || !is_array($oArtikel->Variationen)
                    || count($oArtikel->Variationen) === 0)
            ) {
                $oArtikel->cBestellwert = gibPreisStringLocalized((float)$oArtikelGeschenkeTMP->cWert);
                $oArtikelGeschenke_arr[] = $oArtikel;
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
function pruefeBestellMengeUndLagerbestand($Einstellungen = [])
{
    $cart         = Session::Cart();
    $cHinweis     = '';
    $cArtikelName = '';
    $bVorhanden   = false;
    $cISOSprache  = $_SESSION['cISOSprache'];
    if (!is_array($Einstellungen) || !isset($Einstellungen['global'])) {
        $Einstellungen = Shop::getSettings([CONF_GLOBAL]);
    }
    if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
        foreach ($cart->PositionenArr as $i => $oPosition) {
            if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                // Mit Lager arbeiten und Lagerbestand darf < 0 werden?
                if (isset($oPosition->Artikel) && $oPosition->Artikel->cLagerBeachten === 'Y'
                    && $oPosition->Artikel->cLagerKleinerNull === 'Y'
                    && $Einstellungen['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                ) {
                    if ($oPosition->nAnzahl > $oPosition->Artikel->fLagerbestand) {
                        $bVorhanden    = true;
                        $cName         = is_array($oPosition->cName) ? $oPosition->cName[$cISOSprache] : $oPosition->cName;
                        $cArtikelName .= '<li>' . $cName . '</li>';
                    }
                }
            }
        }
    }
    $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();

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
