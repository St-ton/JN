<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;

/**
 * Class DeliverySlips
 *
 * @package JTL\dbeS\Sync
 */
final class DeliverySlips extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML(true) as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            $fileName     = \pathinfo($file)['basename'];
            if ($fileName === 'lief.xml') {
                $this->handleInserts($xml);
            } elseif ($fileName === 'del_lief.xml') {
                $this->handleDeletes($xml);
            }
        }

        return null;
    }

    /**
     * @param object $xml
     */
    private function handleInserts($xml): void
    {
        foreach ($xml->tlieferschein as $item) {
            $deliverySlip = $this->mapper->map($item, 'mLieferschein');
            if ((int)$deliverySlip->kInetBestellung <= 0) {
                continue;
            }
            $deliverySlip->dErstellt = \date_format(\date_create($deliverySlip->dErstellt), 'U');
            $this->upsert('tlieferschein', [$deliverySlip], 'kLieferschein');

            foreach ($item->tlieferscheinpos as $xmlItem) {
                $sItem                = $this->mapper->map($xmlItem, 'mLieferscheinpos');
                $sItem->kLieferschein = $deliverySlip->kLieferschein;
                $this->upsert('tlieferscheinpos', [$sItem], 'kLieferscheinPos');

                foreach ($xmlItem->tlieferscheinposInfo as $info) {
                    $posInfo                   = $this->mapper->map($info, 'mLieferscheinposinfo');
                    $posInfo->kLieferscheinPos = $sItem->kLieferscheinPos;
                    $this->upsert('tlieferscheinposinfo', [$posInfo], 'kLieferscheinPosInfo');
                }
            }

            foreach ($item->tversand as $shipping) {
                $shipping                = $this->mapper->map($shipping, 'mVersand');
                $shipping->kLieferschein = $deliverySlip->kLieferschein;
                $shipping->dErstellt     = \date_format(\date_create($shipping->dErstellt), 'U');
                $this->upsert('tversand', [$shipping], 'kVersand');
            }
        }
    }

    /**
     * @param object $xml
     */
    private function handleDeletes($xml): void
    {
        $items = $xml->kLieferschein;
        if (!\is_array($items)) {
            $items = (array)$items;
        }
        foreach ($items as $id) {
            $id = (int)$id;
            $this->db->delete('tversand', 'kLieferschein', $id);
            $this->db->delete('tlieferschein', 'kLieferschein', $id);
            foreach ($this->db->selectAll(
                'tlieferscheinpos',
                'kLieferschein',
                $id,
                'kLieferscheinPos'
            ) as $item) {
                $this->db->delete(
                    'tlieferscheinpos',
                    'kLieferscheinPos',
                    (int)$item->kLieferscheinPos
                );
                $this->db->delete(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$item->kLieferscheinPos
                );
            }
        }
    }
}
