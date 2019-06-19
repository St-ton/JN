<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;

/**
 * Class Globals
 * @package JTL\dbeS\Sync
 */
final class Globals extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_globals.xml') !== false) {
                $this->handleDeletes($xml);
            } elseif (\strpos($file, 'globals.xml') !== false) {
                $this->handleInserts($xml);
            }
        }
        $this->db->query(
            'UPDATE tglobals SET dLetzteAenderung = NOW()',
            ReturnType::DEFAULT
        );

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        if (\is_array($xml['del_globals_wg']['kWarengruppe'])) {
            foreach ($xml['del_globals_wg']['kWarengruppe'] as $kWarengruppe) {
                if ((int)$kWarengruppe > 0) {
                    $this->deleteProductTypeGroup((int)$kWarengruppe);
                }
            }
        } elseif ((int)$xml['del_globals_wg']['kWarengruppe'] > 0) {
            $this->deleteProductTypeGroup((int)$xml['del_globals_wg']['kWarengruppe']);
        }
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        if (isset($xml['globals']['tfirma'], $xml['globals']['tfirma attr']['kFirma'])
            && \is_array($xml['globals']['tfirma'])
            && $xml['globals']['tfirma attr']['kFirma'] > 0
        ) {
            $this->mapper->mapObject($Firma, $xml['globals']['tfirma'], 'mFirma');
            $this->dbDelInsert('tfirma', [$Firma], 1);
        }
        if (isset($xml['globals'])) {
            $languages = $this->mapper->mapArray($xml['globals'], 'tsprache', 'mSprache');
            $langCount = \count($languages);
            for ($i = 0; $i < $langCount; $i++) {
                $languages[$i]->cStandard = $languages[$i]->cWawiStandard;
                unset($languages[$i]->cWawiStandard);
            }
            $this->cache->flushTags([\CACHING_GROUP_LANGUAGE]);
            if (\count($languages) > 0) {
                $this->dbDelInsert('tsprache', $languages, 1);
            }

            $this->xml2db($xml['globals'], 'tlieferstatus', 'mLieferstatus');
            $this->xml2db($xml['globals'], 'txsellgruppe', 'mXsellgruppe');
            $this->xml2db($xml['globals'], 'teinheit', 'mEinheit');
            $this->xml2db($xml['globals'], 'twaehrung', 'mWaehrung');
            $this->xml2db($xml['globals'], 'tsteuerklasse', 'mSteuerklasse');
            $this->xml2db($xml['globals'], 'tsteuersatz', 'mSteuersatz');
            $this->xml2db($xml['globals'], 'tversandklasse', 'mVersandklasse');

            if (isset($xml['globals']['tsteuerzone']) && \is_array($xml['globals']['tsteuerzone'])) {
                $taxZones = $this->mapper->mapArray($xml['globals'], 'tsteuerzone', 'mSteuerzone');
                $this->dbDelInsert('tsteuerzone', $taxZones, 1);
                $this->db->query('DELETE FROM tsteuerzoneland', ReturnType::DEFAULT);
                $taxCount = \count($taxZones);
                for ($i = 0; $i < $taxCount; $i++) {
                    if ($taxCount < 2) {
                        $this->xml2db($xml['globals']['tsteuerzone'], 'tsteuerzoneland', 'mSteuerzoneland', 0);
                    } else {
                        $this->xml2db($xml['globals']['tsteuerzone'][$i], 'tsteuerzoneland', 'mSteuerzoneland', 0);
                    }
                }
            }
            if (isset($xml['globals']['tkundengruppe']) && \is_array($xml['globals']['tkundengruppe'])) {
                $customerGroups = $this->mapper->mapArray($xml['globals'], 'tkundengruppe', 'mKundengruppe');
                $this->dbDelInsert('tkundengruppe', $customerGroups, 1);
                $this->db->query('TRUNCATE TABLE tkundengruppensprache', ReturnType::DEFAULT);
                $this->db->query('TRUNCATE TABLE tkundengruppenattribut', ReturnType::DEFAULT);
                $cgCount = \count($customerGroups);
                for ($i = 0; $i < $cgCount; $i++) {
                    if ($cgCount < 2) {
                        $this->xml2db(
                            $xml['globals']['tkundengruppe'],
                            'tkundengruppensprache',
                            'mKundengruppensprache',
                            0
                        );
                        $this->xml2db(
                            $xml['globals']['tkundengruppe'],
                            'tkundengruppenattribut',
                            'mKundengruppenattribut',
                            0
                        );
                    } else {
                        $this->xml2db(
                            $xml['globals']['tkundengruppe'][$i],
                            'tkundengruppensprache',
                            'mKundengruppensprache',
                            0
                        );
                        $this->xml2db(
                            $xml['globals']['tkundengruppe'][$i],
                            'tkundengruppenattribut',
                            'mKundengruppenattribut',
                            0
                        );
                    }
                }
                $this->cache->flushTags([\CACHING_GROUP_ARTICLE, \CACHING_GROUP_CATEGORY]);
            }
            if (isset($xml['globals']['twarenlager']) && \is_array($xml['globals']['twarenlager'])) {
                $storages   = $this->mapper->mapArray($xml['globals'], 'twarenlager', 'mWarenlager');
                $visibility = $this->db->query(
                    'SELECT kWarenlager, nAktiv FROM twarenlager WHERE nAktiv = 1',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                // Alle Einträge in twarenlager löschen - Wawi 1.0.1 sendet immer alle Warenlager.
                $this->db->query('DELETE FROM twarenlager WHERE 1', ReturnType::DEFAULT);
                $this->upsert('twarenlager', $storages, 'kWarenlager');
                // Lagersichtbarkeit übertragen
                if (!empty($visibility)) {
                    foreach ($visibility as $lager) {
                        $this->db->update('twarenlager', 'kWarenlager', $lager->kWarenlager, $lager);
                    }
                }
            }
            if (isset($xml['globals']['tmasseinheit']) && \is_array($xml['globals']['tmasseinheit'])) {
                $units = $this->mapper->mapArray($xml['globals'], 'tmasseinheit', 'mMasseinheit');
                foreach ($units as &$_me) {
                    //hack?
                    unset($_me->kBezugsMassEinheit);
                }
                unset($_me);
                $this->dbDelInsert('tmasseinheit', $units, 1);
                $this->db->query('TRUNCATE TABLE tmasseinheitsprache', ReturnType::DEFAULT);
                $meCount = \count($units);
                for ($i = 0; $i < $meCount; $i++) {
                    if ($meCount < 2) {
                        $this->xml2db(
                            $xml['globals']['tmasseinheit'],
                            'tmasseinheitsprache',
                            'mMasseinheitsprache',
                            0
                        );
                    } else {
                        $this->xml2db(
                            $xml['globals']['tmasseinheit'][$i],
                            'tmasseinheitsprache',
                            'mMasseinheitsprache',
                            0
                        );
                    }
                }
            }
        }
        if (isset($xml['globals_wg']['tWarengruppe']) && \is_array($xml['globals_wg']['tWarengruppe'])) {
            $groups = $this->mapper->mapArray($xml['globals_wg'], 'tWarengruppe', 'mWarengruppe');
            $this->upsert('twarengruppe', $groups, 'kWarengruppe');
        }
    }

    /**
     * @param int $id
     */
    private function deleteProductTypeGroup(int $id): void
    {
        $this->db->delete('twarengruppe', 'kWarengruppe', $id);
        $this->logger->debug('Warengruppe geloescht: ' . $id);
    }

    /**
     * @param array  $xml
     * @param string $table
     * @param string $toMap
     * @param int    $del
     */
    private function xml2db($xml, $table, $toMap, $del = 1): void
    {
        if (isset($xml[$table]) && \is_array($xml[$table])) {
            $objects = $this->mapper->mapArray($xml, $table, $toMap);
            $this->dbDelInsert($table, $objects, $del);
        }
    }

    /**
     * @param string   $tablename
     * @param array    $objects
     * @param int|bool $del
     */
    private function dbDelInsert($tablename, $objects, $del): void
    {
        if (!\is_array($objects)) {
            return;
        }
        if ($del) {
            $this->db->query('DELETE FROM ' . $tablename, ReturnType::DEFAULT);
        }
        foreach ($objects as $object) {
            //hack? unset arrays/objects that would result in nicedb exceptions
            foreach (\get_object_vars($object) as $key => $var) {
                if (\is_array($var) || \is_object($var)) {
                    unset($object->$key);
                }
            }
            $key = $this->db->insert($tablename, $object);
            if (!$key) {
                $this->logger->error(__METHOD__ . ' failed: ' . $tablename . ', data: ' . \print_r($object, true));
            }
        }
    }
}
