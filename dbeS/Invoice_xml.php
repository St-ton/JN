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
        pushError('Invoice Auth: POST-Parameter konnten nicht verarbeitet werden ' .
            '(kBestellung: ' . $_POST['kBestellung'] . ', dRechnungErstellt: ' .
            $_POST['dRechnungErstellt'] . ', kSprache: ' . $_POST['kSprache'] . ').');
    }
} else {
    pushError('Invoice Auth: Anmeldung fehlgeschlagen.');
}

/**
 * @param int    $kBestellung
 * @param string $dRechnungErstellt
 * @param int    $kSprache
 */
function handleData(int $kBestellung, $dRechnungErstellt, int $kSprache)
{
    if ($kBestellung <= 0 || $kSprache <= 0) {
        pushError('Fehlerhafte Parameter (kBestellung: ' . $kBestellung . ', kSprache: ' . $kSprache . ').');
        return;
    }
    $order = Shop::Container()->getDB()->query(
        'SELECT tbestellung.kBestellung, tbestellung.fGesamtsumme, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart
              ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = ' . $kBestellung . ' 
            LIMIT 1',
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (!$order) {
        pushError('Keine Bestellung mit kBestellung ' . $kBestellung . ' gefunden!');
        return;
    }
    $paymentMethod = PaymentMethod::create($order->cModulId);
    if (!$paymentMethod) {
        pushError('Keine Bestellung mit kBestellung ' . $kBestellung . ' gefunden!');
        return;
    }
    $invoice = $paymentMethod->createInvoice($kBestellung, $kSprache);
    if (is_object($invoice)) {
        if ($invoice->nType === 0 && strlen($invoice->cInfo) === 0) {
            $invoice->cInfo = 'Funktion in Zahlungsmethode nicht implementiert';
        }
        $response = createResponse(
            $order->kBestellung,
            ($invoice->nType === 0 ? 'FAILURE' : 'SUCCESS'),
            $invoice->cInfo
        );
        zipRedirect(time() . '.jtl', $response);
        exit;
    }
    pushError('Fehler beim Erstellen der Rechnung (kBestellung: ' . $order->kBestellung . ').');
}

/**
 * @param int    $kBestellung
 * @param string $cTyp
 * @param string $cComment
 * @return array
 */
function createResponse(int $kBestellung, $cTyp, $cComment)
{
    $aResponse                               = ['tbestellung' => []];
    $aResponse['tbestellung']['kBestellung'] = $kBestellung;
    $aResponse['tbestellung']['cTyp']        = $cTyp;
    $aResponse['tbestellung']['cKommentar']  = html_entity_decode(
        $cComment,
        ENT_COMPAT | ENT_HTML401,
        'ISO-8859-1'
    ); // decode entities for jtl-wawi.
    // Entities are html-encoded since
    // https://gitlab.jtl-software.de/jtlshop/jtl-shop/commit/e81f7a93797d8e57d00a1705cc5f13191eee9ca1

    return $aResponse;
}

/**
 * @param string $message
 */
function pushError($message)
{
    Shop::Container()->getLogService()->error('Error @ invoice_xml: ' . $message);
    $aResponse = createResponse(0, 'FAILURE', $message);
    zipRedirect(time() . '.jtl', $aResponse);
    exit;
}
