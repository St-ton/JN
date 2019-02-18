<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;

/**
 * Class Data
 * @package JTL\dbeS\Push
 */
final class Data extends AbstractPush
{
    private const LIMIT_UPLOADQUEUE = 100;

    private const LIMIT_AVAILABILITY_MSGS = 100;

    /**
     * @return array|string
     */
    public function getData()
    {
        $xml     = [];
        $current = $this->db->query(
            "SELECT *
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cAbgeholt = 'N'
            LIMIT " . self::LIMIT_AVAILABILITY_MSGS,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $count   = \count($current);
        if ($count === 0) {
            return $xml;
        }

        $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] = $count;
        for ($i = 0; $i < $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl']; $i++) {
            $current[$i . ' attr'] = $this->buildAttributes($current[$i]);
            $this->db->query(
                "UPDATE tverfuegbarkeitsbenachrichtigung
                SET cAbgeholt = 'Y'
                WHERE kVerfuegbarkeitsbenachrichtigung = " .
                (int)$current[$i . ' attr']['kVerfuegbarkeitsbenachrichtigung'],
                ReturnType::DEFAULT
            );
        }
        $xml['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'] = $current;

        $xml['queueddata']['uploadqueue']['tuploadqueue'] = $this->db->query(
            'SELECT *
            FROM tuploadqueue
            LIMIT ' . self::LIMIT_UPLOADQUEUE,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );

        $xml['tuploadqueue attr']['anzahl'] = \count($xml['queueddata']['uploadqueue']['tuploadqueue']);
        for ($i = 0; $i < $xml['tuploadqueue attr']['anzahl']; $i++) {
            $xml['queueddata']['uploadqueue']['tuploadqueue'][$i . ' attr'] =
                $this->buildAttributes($xml['queueddata']['uploadqueue']['tuploadqueue'][$i]);
        }

        return $xml;
    }
}
