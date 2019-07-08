<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\GeneralObject;

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
        $source = $xml['del_globals_wg']['kWarengruppe'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $groupID) {
            $this->deleteProductTypeGroup($groupID);
        }
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $source = $xml['globals'] ?? null;
        if (isset($source['tfirma'], $source['tfirma attr']['kFirma'])
            && \is_array($source['tfirma'])
            && $source['tfirma attr']['kFirma'] > 0
        ) {
            $this->mapper->mapObject($company, $source['tfirma'], 'mFirma');
            $this->dbDelInsert('tfirma', [$company], 1);
        }
        if ($source !== null) {
            $languages = $this->mapper->mapArray($source, 'tsprache', 'mSprache');
            $langCount = \count($languages);
            for ($i = 0; $i < $langCount; $i++) {
                $languages[$i]->cStandard = $languages[$i]->cWawiStandard;
                unset($languages[$i]->cWawiStandard);
            }
            $this->cache->flushTags([\CACHING_GROUP_LANGUAGE]);
            if (\count($languages) > 0) {
                $this->dbDelInsert('tsprache', $languages, 1);
            }
            $this->xml2db($source, 'tlieferstatus', 'mLieferstatus');
            $this->xml2db($source, 'txsellgruppe', 'mXsellgruppe');
            $this->xml2db($source, 'teinheit', 'mEinheit');
            $this->xml2db($source, 'twaehrung', 'mWaehrung');
            $this->xml2db($source, 'tsteuerklasse', 'mSteuerklasse');
            $this->xml2db($source, 'tsteuersatz', 'mSteuersatz');
            $this->xml2db($source, 'tversandklasse', 'mVersandklasse');
            if (GeneralObject::isCountable('tsteuerzone', $source)) {
                $taxZones = $this->mapper->mapArray($source, 'tsteuerzone', 'mSteuerzone');
                $this->dbDelInsert('tsteuerzone', $taxZones, 1);
                $this->db->query('DELETE FROM tsteuerzoneland', ReturnType::DEFAULT);
                $taxCount = \count($taxZones);
                for ($i = 0; $i < $taxCount; $i++) {
                    if ($taxCount < 2) {
                        $this->xml2db($source['tsteuerzone'], 'tsteuerzoneland', 'mSteuerzoneland', 0);
                    } else {
                        $this->xml2db($source['tsteuerzone'][$i], 'tsteuerzoneland', 'mSteuerzoneland', 0);
                    }
                }
            }
            if (GeneralObject::isCountable('tkundengruppe', $source)) {
                $customerGroups = $this->mapper->mapArray($source, 'tkundengruppe', 'mKundengruppe');
                $this->dbDelInsert('tkundengruppe', $customerGroups, 1);
                $this->db->query('TRUNCATE TABLE tkundengruppensprache', ReturnType::DEFAULT);
                $this->db->query('TRUNCATE TABLE tkundengruppenattribut', ReturnType::DEFAULT);
                $cgCount = \count($customerGroups);
                for ($i = 0; $i < $cgCount; $i++) {
                    if ($cgCount < 2) {
                        $this->xml2db($source['tkundengruppe'], 'tkundengruppensprache', 'mKundengruppensprache', 0);
                        $this->xml2db($source['tkundengruppe'], 'tkundengruppenattribut', 'mKundengruppenattribut', 0);
                    } else {
                        $this->xml2db(
                            $source['tkundengruppe'][$i],
                            'tkundengruppensprache',
                            'mKundengruppensprache',
                            0
                        );
                        $this->xml2db(
                            $source['tkundengruppe'][$i],
                            'tkundengruppenattribut',
                            'mKundengruppenattribut',
                            0
                        );
                    }
                }
                $this->cache->flushTags([\CACHING_GROUP_ARTICLE, \CACHING_GROUP_CATEGORY]);
            }
            if (GeneralObject::isCountable('twarenlager', $source)) {
                $storages   = $this->mapper->mapArray($source, 'twarenlager', 'mWarenlager');
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
            if (GeneralObject::isCountable('tmasseinheit', $source)) {
                $units = $this->mapper->mapArray($source, 'tmasseinheit', 'mMasseinheit');
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
                        $this->xml2db($source['tmasseinheit'], 'tmasseinheitsprache', 'mMasseinheitsprache', 0);
                    } else {
                        $this->xml2db($source['tmasseinheit'][$i], 'tmasseinheitsprache', 'mMasseinheitsprache', 0);
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
        if (GeneralObject::isCountable($table, $xml)) {
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
