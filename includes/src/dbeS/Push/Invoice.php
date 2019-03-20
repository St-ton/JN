<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;
use PaymentMethod;

/**
 * Class Invoice
 * @package JTL\dbeS\Push
 */
final class Invoice extends AbstractPush
{
    /**
     * @return array|string
     */
    public function getData()
    {
        if (!isset($_POST['kBestellung'], $_POST['kSprache'])) {
            return [];
        }
        $orderID = (int)$_POST['kBestellung'];
        $langID  = (int)$_POST['kSprache'];
        if ($orderID <= 0 || $langID <= 0) {
            return $this->pushError('Wrong params (kBestellung: ' . $orderID . ', kSprache: ' . $langID . ').');
        }
        $order = $this->db->query(
            'SELECT tbestellung.kBestellung, tbestellung.fGesamtsumme, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart
              ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = ' . $orderID . ' 
            LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if (!$order) {
            return $this->pushError('Keine Bestellung mit kBestellung ' . $orderID . ' gefunden!');
        }
        $paymentMethod = PaymentMethod::create($order->cModulId);
        if (!$paymentMethod) {
            return $this->pushError('Keine Bestellung mit kBestellung ' . $orderID . ' gefunden!');
        }
        $invoice = $paymentMethod->createInvoice($orderID, $langID);
        if (\is_object($invoice)) {
            if ($invoice->nType === 0 && \strlen($invoice->cInfo) === 0) {
                $invoice->cInfo = 'Funktion in Zahlungsmethode nicht implementiert';
            }
            return $this->createResponse(
                $order->kBestellung,
                ($invoice->nType === 0 ? 'FAILURE' : 'SUCCESS'),
                $invoice->cInfo
            );
        }

        return $this->pushError('Fehler beim Erstellen der Rechnung (kBestellung: ' . $order->kBestellung . ').');
    }

    /**
     * @param int    $orderID
     * @param string $type
     * @param string $comment
     * @return array
     */
    private function createResponse(int $orderID, $type, $comment): array
    {
        $res                               = ['tbestellung' => []];
        $res['tbestellung']['kBestellung'] = $orderID;
        $res['tbestellung']['cTyp']        = $type;
        $res['tbestellung']['cKommentar']  = \html_entity_decode(
            $comment,
            \ENT_COMPAT | \ENT_HTML401,
            'ISO-8859-1'
        ); // decode entities for jtl-wawi.
        // Entities are html-encoded since
        // https://gitlab.jtl-software.de/jtlshop/jtl-shop/commit/e81f7a93797d8e57d00a1705cc5f13191eee9ca1

        return $res;
    }

    /**
     * @param $message
     * @return array
     */
    private function pushError(string $message): array
    {
        $this->logger->error('Error @ invoice_xml: ' . $message);

        return $this->createResponse(0, 'FAILURE', $message);
    }
}
