<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;

/**
 * Class Data
 * @package JTL\dbeS\Sync
 */
final class Data extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'ack_verfuegbarkeitsbenachrichtigungen.xml') !== false) {
                $this->handleAvailabilityMessages($xml);
            } elseif (\strpos($file, 'ack_uploadqueue.xml') !== false) {
                $this->handleUploadQueueAck($xml);
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleAvailabilityMessages(array $xml): void
    {
        if (!isset($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
            return;
        }
        if (!\is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])
            && (int)$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] > 0
        ) {
            $xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] =
                [$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']];
        }
        if (\is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
            foreach ($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] as $msg) {
                $msg = (int)$msg;
                if ($msg > 0) {
                    $this->db->update(
                        'tverfuegbarkeitsbenachrichtigung',
                        'kVerfuegbarkeitsbenachrichtigung',
                        $msg,
                        (object)['cAbgeholt' => 'Y']
                    );
                }
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleUploadQueueAck(array $xml): void
    {
        if (\is_array($xml['ack_uploadqueue']['kuploadqueue'])) {
            foreach ($xml['ack_uploadqueue']['kuploadqueue'] as $kUploadqueue) {
                $kUploadqueue = (int)$kUploadqueue;
                if ($kUploadqueue > 0) {
                    $this->db->delete('tuploadqueue', 'kUploadqueue', $kUploadqueue);
                }
            }
        } elseif ((int)$xml['ack_uploadqueue']['kuploadqueue'] > 0) {
            $this->db->delete(
                'tuploadqueue',
                'kUploadqueue',
                (int)$xml['ack_uploadqueue']['kuploadqueue']
            );
        }
    }
}
