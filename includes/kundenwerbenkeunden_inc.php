<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @return bool
 */
function pruefeEingabe(array $cPost_arr)
{
    $cVorname  = StringHandler::filterXSS($cPost_arr['cVorname']);
    $cNachname = StringHandler::filterXSS($cPost_arr['cNachname']);
    $cEmail    = StringHandler::filterXSS($cPost_arr['cEmail']);

    return (strlen($cVorname) > 0 && strlen($cNachname) > 0 && valid_email($cEmail));
}

/**
 * @param array $cPost_arr
 * @param array $Einstellungen
 * @return bool
 */
function setzeKwKinDB(array $cPost_arr, array $Einstellungen)
{
    if ($Einstellungen['kundenwerbenkunden']['kwk_nutzen'] !== 'Y') {
        return false;
    }
    $cVorname  = StringHandler::filterXSS($cPost_arr['cVorname']);
    $cNachname = StringHandler::filterXSS($cPost_arr['cNachname']);
    $cEmail    = StringHandler::filterXSS($cPost_arr['cEmail']);
    // Prüfe ob Email nicht schon bei einem Kunden vorhanden ist
    $oKunde = Shop::Container()->getDB()->select('tkunde', 'cMail', $cEmail);

    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        return false;
    }
    $oKwK = new KundenwerbenKunden($cEmail);
    if ((int)$oKwK->kKundenWerbenKunden > 0) {
        return false;
    }
    // Setze in tkundenwerbenkunden
    $oKwK->kKunde       = $_SESSION['Kunde']->kKunde;
    $oKwK->cVorname     = $cVorname;
    $oKwK->cNachname    = $cNachname;
    $oKwK->cEmail       = $cEmail;
    $oKwK->nRegistriert = 0;
    $oKwK->fGuthaben    = (float)$Einstellungen['kundenwerbenkunden']['kwk_neukundenguthaben'];
    $oKwK->dErstellt    = 'now()';
    $oKwK->insertDB();
    $oKwK->sendeEmailanNeukunde();

    return true;
}

/**
 * @param int   $kKunde
 * @param float $fGuthaben
 * @return bool
 */
function gibBestandskundeGutbaben(int $kKunde, $fGuthaben)
{
    if ($kKunde > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde 
                SET fGuthaben = fGuthaben + :bal 
                WHERE kKunde = :cid',
            [
                'bal' => (float)$fGuthaben,
                'cid' => $kKunde
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );

        return true;
    }

    return false;
}
