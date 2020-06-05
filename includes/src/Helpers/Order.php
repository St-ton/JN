<?php

namespace JTL\Helpers;

use JTL\Cart\CartHelper;
use JTL\Catalog\Currency;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Rechnungsadresse;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Order
 * @package JTL\Helpers
 */
class Order extends CartHelper
{
    /**
     * @var Bestellung
     */
    protected $object;

    /**
     * @param Bestellung $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @param int $decimals
     * @return stdClass
     */
    public function getTotal(int $decimals = 0): stdClass
    {
        $order           = $this->getObject();
        $info            = new stdClass();
        $info->type      = self::GROSS;
        $info->currency  = $order->Waehrung;
        $info->article   = [0, 0];
        $info->shipping  = [0, 0];
        $info->discount  = [0, 0];
        $info->surcharge = [0, 0];
        $info->total     = [0, 0];
        $info->items     = [];
        foreach ($order->Positionen as $orderItem) {
            $amountItem = $orderItem->fPreisEinzelNetto;
            if ((!isset($orderItem->Artikel->kVaterArtikel) || (int)$orderItem->Artikel->kVaterArtikel === 0)
                && GeneralObject::isCountable('WarenkorbPosEigenschaftArr', $orderItem)
            ) {
                foreach ($orderItem->WarenkorbPosEigenschaftArr as $attr) {
                    if ($attr->fAufpreis !== 0) {
                        $amountItem += $attr->fAufpreis;
                    }
                }
            }
            $amount      = $amountItem; /* $order->fWaehrungsFaktor;*/
            $amountGross = $amount + ($amount * $orderItem->fMwSt / 100);
            // floating-point precission bug
            $amountGross = (float)(string)$amountGross;

            switch ((int)$orderItem->nPosTyp) {
                case \C_WARENKORBPOS_TYP_ARTIKEL:
                    $item = (object)[
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    $item->name = \html_entity_decode($orderItem->cName);

                    $item->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ((int)$orderItem->nAnzahl != $orderItem->nAnzahl) {
                        $item->amount[self::NET]   *= $orderItem->nAnzahl;
                        $item->amount[self::GROSS] *= $orderItem->nAnzahl;

                        $item->name = \sprintf(
                            '%g %s %s',
                            (float)$orderItem->nAnzahl,
                            $orderItem->Artikel->cEinheit ?: 'x',
                            $item->name
                        );
                    } else {
                        $item->quantity = (int)$orderItem->nAnzahl;
                    }

                    $info->article[self::NET]   += $item->amount[self::NET] * $item->quantity;
                    $info->article[self::GROSS] += $item->amount[self::GROSS] * $item->quantity;

                    $info->items[] = $item;
                    break;

                case \C_WARENKORBPOS_TYP_VERSANDPOS:
                case \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case \C_WARENKORBPOS_TYP_VERPACKUNG:
                case \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG:
                    $info->shipping[self::NET]   += $amount * $orderItem->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $orderItem->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_KUPON:
                case \C_WARENKORBPOS_TYP_GUTSCHEIN:
                case \C_WARENKORBPOS_TYP_NEUKUNDENKUPON:
                    $info->discount[self::NET]   += $amount * $orderItem->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $orderItem->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_ZAHLUNGSART:
                case \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR:
                    $info->surcharge[self::NET]   += $amount * $orderItem->nAnzahl;
                    $info->surcharge[self::GROSS] += $amountGross * $orderItem->nAnzahl;
                    break;
            }
        }

        if ($order->fGuthaben != 0) {
            $amountGross = $order->fGuthaben;
            $amount      = $amountGross;

            $info->discount[self::NET]   += $amount;
            $info->discount[self::GROSS] += $amountGross;
        }

        // positive discount
        $info->discount[self::NET]   *= -1;
        $info->discount[self::GROSS] *= -1;

        $info->total[self::NET]   = $order->fGesamtsummeNetto;
        $info->total[self::GROSS] = $order->fGesamtsumme;

        $formatter = static function ($prop) use ($decimals) {
            return [
                self::NET   => \number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => \number_format($prop[self::GROSS], $decimals, '.', ''),
            ];
        };

        if ($decimals > 0) {
            $info->article   = $formatter($info->article);
            $info->shipping  = $formatter($info->shipping);
            $info->discount  = $formatter($info->discount);
            $info->surcharge = $formatter($info->surcharge);
            $info->total     = $formatter($info->total);

            foreach ($info->items as &$item) {
                $item->amount = $formatter($item->amount);
            }
        }

        return $info;
    }

    /**
     * @return Bestellung
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return Lieferadresse|Rechnungsadresse
     */
    public function getShippingAddress()
    {
        if ((int)$this->object->kLieferadresse > 0 && \is_object($this->object->Lieferadresse)) {
            return $this->object->Lieferadresse;
        }

        return $this->getBillingAddress();
    }

    /**
     * @return Rechnungsadresse|null
     */
    public function getBillingAddress(): ?Rechnungsadresse
    {
        return $this->object->oRechnungsadresse;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): ?Customer
    {
        return $this->object->oKunde;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->object->Waehrung;
    }

    /**
     * @return string iso
     */
    public function getLanguage(): string
    {
        return Shop::Lang()->getIsoFromLangID($this->object->kSprache);
    }

    /**
     * @return string
     */
    public function getInvoiceID()
    {
        return $this->object->cBestellNr;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return (int)$this->object->kBestellung;
    }

    /**
     * @param int $customerID
     * @return object|null
     * @since 5.0.0
     */
    public static function getLastOrderRefIDs(int $customerID): ?object
    {
        $order = Shop::Container()->getDB()->queryPrepared(
            'SELECT kBestellung, kWarenkorb, kLieferadresse, kRechnungsadresse, kZahlungsart, kVersandart
                FROM tbestellung
                WHERE kKunde = :customerID
                ORDER BY dErstellt DESC
                LIMIT 1',
            ['customerID' => $customerID],
            ReturnType::SINGLE_OBJECT
        );

        return \is_object($order)
            ? (object)[
                'kBestellung'       => (int)$order->kBestellung,
                'kWarenkorb'        => (int)$order->kWarenkorb,
                'kLieferadresse'    => (int)$order->kLieferadresse,
                'kRechnungsadresse' => (int)$order->kRechnungsadresse,
                'kZahlungsart'      => (int)$order->kZahlungsart,
                'kVersandart'       => (int)$order->kVersandart,
            ]
            : (object)[
                'kBestellung'       => 0,
                'kWarenkorb'        => 0,
                'kLieferadresse'    => 0,
                'kRechnungsadresse' => 0,
                'kZahlungsart'      => 0,
                'kVersandart'       => 0,
            ];
    }
}
