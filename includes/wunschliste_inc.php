<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Holt für einen Kunden die aktive Wunschliste (falls vorhanden) aus der DB und fügt diese in die Session
 */
function setzeWunschlisteInSession()
{
    if (!empty($_SESSION['Kunde']->kKunde)) {
        $oWunschliste = Shop::Container()->getDB()->select(
            'twunschliste',
            ['kKunde', 'nStandard'],
            [(int)$_SESSION['Kunde']->kKunde, 1]
        );
        if (isset($oWunschliste->kWunschliste)) {
            $_SESSION['Wunschliste'] = new Wunschliste($oWunschliste->kWunschliste);
            $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();
        }
    }
}

/**
 * @param int $kWunschliste
 * @return string
 */
function wunschlisteLoeschen(int $kWunschliste)
{
    $hinweis = '';
    if ($kWunschliste === 0) {
        return $hinweis;
    }
    // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
    $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
    $customer     = Session::Customer();
    if (isset($oWunschliste->kKunde) && (int)$oWunschliste->kKunde === $customer->getID()) {
        // Hole alle Positionen der Wunschliste
        $oWunschlistePos_arr = Shop::Container()->getDB()->selectAll(
            'twunschlistepos',
            'kWunschliste',
            $kWunschliste,
            'kWunschlistePos'
        );
        // Alle Eigenschaften und Positionen aus DB löschen
        foreach ($oWunschlistePos_arr as $oWunschlistePos) {
            Shop::Container()->getDB()->delete(
                'twunschlisteposeigenschaft',
                'kWunschlistePos',
                $oWunschlistePos->kWunschlistePos
            );
        }
        // Lösche alle Positionen mit $kWunschliste
        Shop::Container()->getDB()->delete('twunschlistepos', 'kWunschliste', $kWunschliste);
        // Lösche Wunschliste aus der DB
        Shop::Container()->getDB()->delete('twunschliste', 'kWunschliste', $kWunschliste);
        // Lösche Wunschliste aus der Session (falls Wunschliste = Standard)
        if (isset($_SESSION['Wunschliste']->kWunschliste)
            && (int)$_SESSION['Wunschliste']->kWunschliste === $kWunschliste
        ) {
            unset($_SESSION['Wunschliste']);
        }
        // Wenn die gelöschte Wunschliste nStandard = 1 war => neue setzen
        if ((int)$oWunschliste->nStandard === 1) {
            // Neue Wunschliste holen (falls vorhanden) und nStandard=1 neu setzen
            $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kKunde', $customer->getID());
            if (isset($oWunschliste->kWunschliste)) {
                Shop::Container()->getDB()->query(
                    'UPDATE twunschliste 
                        SET nStandard = 1 
                        WHERE kWunschliste = ' . (int)$oWunschliste->kWunschliste,
                    \DB\ReturnType::AFFECTED_ROWS
                );
                // Neue Standard Wunschliste in die Session laden
                $_SESSION['Wunschliste'] = new Wunschliste($oWunschliste->kWunschliste);
                $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();
            }
        }

        $hinweis = Shop::Lang()->get('wishlistDelete', 'messages');
    }

    return $hinweis;
}

/**
 * @param int $kWunschliste
 * @return string
 */
function wunschlisteAktualisieren(int $kWunschliste)
{
    $hinweis = '';
    if (isset($_POST['WunschlisteName']) && strlen($_POST['WunschlisteName']) > 0) {
        $cName = StringHandler::htmlentities(StringHandler::filterXSS(substr($_POST['WunschlisteName'], 0, 254)));
        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $kWunschliste, (object)['cName' => $cName]);
    }
    // aktualisiere Positionen
    $oWunschlistePos_arr = Shop::Container()->getDB()->selectAll(
        'twunschlistepos',
        'kWunschliste',
        $kWunschliste,
        'kWunschlistePos'
    );
    // Prüfen ab Positionen vorhanden
    if (count($oWunschlistePos_arr) > 0) {
        foreach ($oWunschlistePos_arr as $oWunschlistePos) {
            $kWunschlistePos = (int)$oWunschlistePos->kWunschlistePos;
            // Ist ein Kommentar vorhanden
            if (strlen($_POST['Kommentar_' . $kWunschlistePos]) > 0) {
                $cKommentar = substr($_POST['Kommentar_' . $kWunschlistePos], 0, 254);
                // Kommentar der Position updaten
                $_upd             = new stdClass();
                $_upd->cKommentar = StringHandler::htmlentities(
                    StringHandler::filterXSS(Shop::Container()->getDB()->escape($cKommentar))
                );
                Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $kWunschlistePos, $_upd);
            }
            // Ist eine Anzahl gesezt
            if ((int)$_POST['Anzahl_' . $kWunschlistePos] > 0) {
                $fAnzahl = (float)$_POST['Anzahl_' . $kWunschlistePos];
                // Anzahl der Position updaten
                $_upd          = new stdClass();
                $_upd->fAnzahl = $fAnzahl;
                Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $kWunschlistePos, $_upd);
            }
        }
        $hinweis = Shop::Lang()->get('wishlistUpdate', 'messages');
    }

    return $hinweis;
}

/**
 * @param int $kWunschliste
 * @return string
 */
function wunschlisteStandard(int $kWunschliste)
{
    $hinweis = '';
    if ($kWunschliste === 0) {
        return $hinweis;
    }
    // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
    $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
    if ($oWunschliste !== null && (int)$oWunschliste->kKunde === Session::Customer()->getID()) {
        // Wunschliste auf Standard setzen
        Shop::Container()->getDB()->update(
            'twunschliste',
            'kKunde',
            (int)$_SESSION['Kunde']->kKunde,
            (object)['nStandard' => 0]
        );
        Shop::Container()->getDB()->update(
            'twunschliste',
            'kWunschliste',
            $kWunschliste,
            (object)['nStandard' => 1]
        );
        // Session updaten
        unset($_SESSION['Wunschliste']);
        $_SESSION['Wunschliste'] = new Wunschliste($kWunschliste);
        $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();

        $hinweis = Shop::Lang()->get('wishlistStandard', 'messages');
    }

    return $hinweis;
}

/**
 * @param string $name
 * @return string
 */
function wunschlisteSpeichern($name)
{
    $hinweis = '';
    if ($_SESSION['Kunde']->kKunde > 0 && !empty($name)) {
        $CWunschliste            = new Wunschliste();
        $CWunschliste->cName     = $name;
        $CWunschliste->nStandard = 0;
        unset(
            $CWunschliste->CWunschlistePos_arr,
            $CWunschliste->oKunde,
            $CWunschliste->kWunschliste,
            $CWunschliste->dErstellt_DE
        );

        Shop::Container()->getDB()->insert('twunschliste', $CWunschliste);

        $hinweis = Shop::Lang()->get('wishlistAdd', 'messages');
    }

    return $hinweis;
}

/**
 * @param array $cEmail_arr
 * @param int   $kWunschliste
 * @return string
 */
function wunschlisteSenden(array $cEmail_arr, int $kWunschliste)
{
    $hinweis = '';
    // Wurden Emails übergeben?
    if (count($cEmail_arr) > 0) {
        $conf                = Shop::getSettings([CONF_GLOBAL]);
        $oMail               = new stdClass();
        $oMail->tkunde       = $_SESSION['Kunde'];
        $oMail->twunschliste = bauecPreis(new Wunschliste($kWunschliste));

        $oWunschlisteVersand                    = new stdClass();
        $oWunschlisteVersand->kWunschliste      = $kWunschliste;
        $oWunschlisteVersand->dZeit             = 'now()';
        $oWunschlisteVersand->nAnzahlEmpfaenger = min(
            count($cEmail_arr),
            (int)$conf['global']['global_wunschliste_max_email']
        );
        $oWunschlisteVersand->nAnzahlArtikel    = count($oMail->twunschliste->CWunschlistePos_arr);

        Shop::Container()->getDB()->insert('twunschlisteversand', $oWunschlisteVersand);

        $cValidEmail_arr = [];
        // Schleife mit Emails (versenden)
        for ($i = 0; $i < $oWunschlisteVersand->nAnzahlEmpfaenger; $i++) {
            // Email auf "Echtheit" prüfen
            $cEmail = StringHandler::filterXSS($cEmail_arr[$i]);
            if (!pruefeEmailblacklist($cEmail)) {
                $oMail->mail          = new stdClass();
                $oMail->mail->toEmail = $cEmail;
                $oMail->mail->toName  = $cEmail;
                // Emails senden
                sendeMail(MAILTEMPLATE_WUNSCHLISTE, $oMail);
            } else {
                $cValidEmail_arr[] = $cEmail;
            }
        }
        // Gabs Emails die nicht validiert wurden?
        if (count($cValidEmail_arr) > 0) {
            $hinweis = Shop::Lang()->get('novalidEmail', 'messages');
            foreach ($cValidEmail_arr as $cValidEmail) {
                $hinweis .= $cValidEmail . ', ';
            }
            $hinweis = substr($hinweis, 0, strlen($hinweis) - 2) . '<br />';
        }
        // Hat der benutzer mehr Emails angegeben als erlaubt sind?
        if (count($cEmail_arr) > (int)$conf['global']['global_wunschliste_max_email']) {
            $nZuviel = count($cEmail_arr) - (int)$conf['global']['global_wunschliste_max_email'];
            $hinweis .= '<br />';

            if (strpos($hinweis, Shop::Lang()->get('novalidEmail', 'messages')) === false) {
                $hinweis = Shop::Lang()->get('novalidEmail', 'messages');
            }

            for ($i = 0; $i < $nZuviel; $i++) {
                if (strpos($hinweis, $cEmail_arr[(count($cEmail_arr) - 1) - $i]) === false) {
                    if ($i > 0) {
                        $hinweis .= ', ' . $cEmail_arr[(count($cEmail_arr) - 1) - $i];
                    } else {
                        $hinweis .= $cEmail_arr[(count($cEmail_arr) - 1) - $i];
                    }
                }
            }

            $hinweis .= '<br />';
        }

        $hinweis .= Shop::Lang()->get('emailSeccessfullySend', 'messages');
    } else {
        // Keine Emails eingegeben
        $hinweis = Shop::Lang()->get('noEmail', 'messages');
    }

    return $hinweis;
}

/**
 * @param int $kWunschliste
 * @param int $kWunschlistePos
 * @return array|bool
 */
function gibEigenschaftenZuWunschliste(int $kWunschliste, int $kWunschlistePos)
{
    if ($kWunschliste > 0 && $kWunschlistePos > 0) {
        // $oEigenschaftwerte_arr anlegen
        $oEigenschaftwerte_arr          = [];
        $oWunschlistePosEigenschaft_arr = Shop::Container()->getDB()->selectAll(
            'twunschlisteposeigenschaft',
            'kWunschlistePos',
            $kWunschlistePos
        );
        foreach ($oWunschlistePosEigenschaft_arr as $oWunschlistePosEigenschaft) {
            $oEigenschaftwerte                       = new stdClass();
            $oEigenschaftwerte->kEigenschaftWert     = $oWunschlistePosEigenschaft->kEigenschaftWert;
            $oEigenschaftwerte->kEigenschaft         = $oWunschlistePosEigenschaft->kEigenschaft;
            $oEigenschaftwerte->cEigenschaftName     = $oWunschlistePosEigenschaft->cEigenschaftName;
            $oEigenschaftwerte->cEigenschaftWertName = $oWunschlistePosEigenschaft->cEigenschaftWertName;
            $oEigenschaftwerte->cFreifeldWert        = $oWunschlistePosEigenschaft->cFreifeldWert;

            $oEigenschaftwerte_arr[] = $oEigenschaftwerte;
        }

        return $oEigenschaftwerte_arr;
    }

    return false;
}

/**
 * @param int $kWunschlistePos
 * @return object|bool
 */
function giboWunschlistePos(int $kWunschlistePos)
{
    if ($kWunschlistePos > 0) {
        $oWunschlistePos = Shop::Container()->getDB()->select('twunschlistepos', 'kWunschlistePos', $kWunschlistePos);
        if (!empty($oWunschlistePos->kWunschliste)) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oWunschlistePos->kArtikel, Artikel::getDefaultOptions());

            if ($oArtikel->kArtikel > 0) {
                $oWunschlistePos->bKonfig = $oArtikel->bHasKonfig;
            }

            return $oWunschlistePos;
        }
    }

    return false;
}

/**
 * @param int    $kWunschliste
 * @param string $cURLID
 * @return bool|stdClass
 */
function giboWunschliste(int $kWunschliste = 0, string $cURLID = '')
{
    $oWunschliste = null;
    if ($kWunschliste > 0) {
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
    } elseif ($cURLID !== '') {
        $oWunschliste = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT * FROM twunschliste WHERE cURLID LIKE :id",
            ['id' => $cURLID],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }
    return (isset($oWunschliste->kWunschliste) && $oWunschliste->kWunschliste > 0)
        ? $oWunschliste
        : false;
}

/**
 * @param object $oWunschliste
 * @return mixed
 */
function bauecPreis($oWunschliste)
{
    // Wunschliste durchlaufen und cPreis setzen (Artikelanzahl mit eingerechnet)
    if (is_array($oWunschliste->CWunschlistePos_arr) && count($oWunschliste->CWunschlistePos_arr) > 0) {
        foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
            if (Session::CustomerGroup()->isMerchant()) {
                $fPreis = isset($oWunschlistePos->Artikel->Preise->fVKNetto)
                    ? (int)$oWunschlistePos->fAnzahl * $oWunschlistePos->Artikel->Preise->fVKNetto
                    : 0;
            } else {
                $fPreis = isset($oWunschlistePos->Artikel->Preise->fVKNetto)
                    ? (int)$oWunschlistePos->fAnzahl *
                        (
                            $oWunschlistePos->Artikel->Preise->fVKNetto *
                            (100 + $_SESSION['Steuersatz'][$oWunschlistePos->Artikel->kSteuerklasse]) / 100
                        )
                    : 0;
            }
            $oWunschlistePos->cPreis = Preise::getLocalizedPriceString($fPreis, Session::Currency());
        }
    }

    return $oWunschliste;
}

/**
 * @param int $nMSGCode
 * @return string
 */
function mappeWunschlisteMSG(int $nMSGCode)
{
    $cMSG = '';
    switch ($nMSGCode) {
        case 1:
            $cMSG = Shop::Lang()->get('basketAdded', 'messages');
            break;
        case 2:
            $cMSG = Shop::Lang()->get('basketAllAdded', 'messages');
            break;
        default:
            break;
    }

    return $cMSG;
}
