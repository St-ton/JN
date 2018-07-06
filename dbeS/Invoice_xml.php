<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

if (auth()) {
    $zipFile = checkFile();
    if (isset($_POST['kBestellung'], $_POST['dRechnungErstellt'], $_POST['kSprache'])) {
        handleData($_POST['kBestellung'], $_POST['dRechnungErstellt'], $_POST['kSprache']);
    } else {
        pushError("Invoice Auth: POST-Parameter konnten nicht verarbeitet werden " .
            "(kBestellung: {$_POST['kBestellung']}, dRechnungErstellt: {$_POST['dRechnungErstellt']}, kSprache: {$_POST['kSprache']}).");
    }
} else {
    pushError("Invoice Auth: Anmeldung fehlgeschlagen.");
}

/**
 * @param int    $kBestellung
 * @param string $dRechnungErstellt
 * @param int    $kSprache
 */
function handleData(int $kBestellung, $dRechnungErstellt, int $kSprache)
{
    if ($kBestellung > 0 && $kSprache > 0) {
        $oBestellung = Shop::Container()->getDB()->query(
            "SELECT tbestellung.kBestellung, tbestellung.fGesamtsumme, tzahlungsart.cModulId
                FROM tbestellung
                LEFT JOIN tzahlungsart
                  ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tbestellung.kBestellung = " . $kBestellung . " 
                LIMIT 1",
            \DB\ReturnType::SINGLE_OBJECT
        );

        if ($oBestellung) {
            $oPaymentMethod = PaymentMethod::create($oBestellung->cModulId);
            if ($oPaymentMethod) {
                $oInvoice = $oPaymentMethod->createInvoice($kBestellung, $kSprache);
                if (is_object($oInvoice)) {
                    // response xml
                    if ($oInvoice->nType == 0 && strlen($oInvoice->cInfo) === 0) {
                        $oInvoice->cInfo = 'Funktion in Zahlungsmethode nicht implementiert';
                    }

                    $cResponse = createResponse($oBestellung->kBestellung, ($oInvoice->nType == 0 ? 'FAILURE' : 'SUCCESS'), $oInvoice->cInfo);
                    zipRedirect(time() . '.jtl', $cResponse);
                    exit;
                }
                // could not create invoice
                pushError("Invoice handleData: Fehler beim Erstellen der Rechnung (kBestellung: {$oBestellung->kBestellung}).");
            } else {
                // payment method does not exist
                pushError("Invoice handleData: Für die Zahlungsart {$oPaymentMethod->cName} kann keine Rechnung erstellt werden (kBestellung: {$oBestellung->kBestellung}).");
            }
        } else {
            // no order found
            pushError("Invoice handleData: Keine Bestellung mit kBestellung {$kBestellung} gefunden!");
        }
    } else {
        pushError("Invoice handleData: Fehlerhafte Parameter (kBestellung: {$kBestellung}, kSprache: {$kSprache}).");
    }
}

/**
 * @param int    $kBestellung
 * @param string $cTyp
 * @param string $cComment
 * @return array
 */
function createResponse($kBestellung, $cTyp, $cComment)
{
    $aResponse                               = ['tbestellung' => []];
    $aResponse['tbestellung']['kBestellung'] = $kBestellung;
    $aResponse['tbestellung']['cTyp']        = $cTyp;
    $aResponse['tbestellung']['cKommentar']  = html_entity_decode(
        $cComment,
        ENT_COMPAT | ENT_HTML401, 'ISO-8859-1'
    ); // decode entities for jtl-wawi.
    //Entities are html-encoded since https://gitlab.jtl-software.de/jtlshop/jtl-shop/commit/e81f7a93797d8e57d00a1705cc5f13191eee9ca1

    return $aResponse;
}

/**
 * @param string $cMessage
 */
function pushError($cMessage)
{
    Shop::Container()->getLogService()->error('Error @ invoice_xml: ' . $cMessage);
    $aResponse = createResponse(0, 'FAILURE', $cMessage);
    zipRedirect(time() . '.jtl', $aResponse);
    exit;
}
