<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';

$zipFile = $_FILES['data']['tmp_name'];
$return  = 3;
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;

    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $data = file_get_contents($xmlFile);
            $xml  = \JTL\XML::unserialize($data);
            if (strpos($xmlFile, 'del_globals.xml') !== false) {
                bearbeiteDeletes($xml);
            } elseif (strpos($xmlFile, 'globals.xml') !== false) {
                bearbeiteUpdates($xml);
            }
        }
    }
    Shop::Container()->getDB()->query(
        'UPDATE tglobals SET dLetzteAenderung = NOW()',
        \DB\ReturnType::DEFAULT
    );
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (is_array($xml['del_globals_wg']['kWarengruppe'])) {
        foreach ($xml['del_globals_wg']['kWarengruppe'] as $kWarengruppe) {
            if ((int)$kWarengruppe > 0) {
                loescheWarengruppe((int)$kWarengruppe);
            }
        }
    } elseif ((int)$xml['del_globals_wg']['kWarengruppe'] > 0) {
        loescheWarengruppe((int)$xml['del_globals_wg']['kWarengruppe']);
    }
}

/**
 * @param array $xml
 */
function bearbeiteUpdates($xml)
{
    if (isset($xml['globals']['tfirma'], $xml['globals']['tfirma attr']['kFirma'])
        && is_array($xml['globals']['tfirma'])
        && $xml['globals']['tfirma attr']['kFirma'] > 0
    ) {
        mappe($Firma, $xml['globals']['tfirma'], Mapper::getMapping('mFirma'));
        DBDelInsert('tfirma', [$Firma], 1);
    }
    $db = Shop::Container()->getDB();
    if (isset($xml['globals'])) {
        $languages = mapArray($xml['globals'], 'tsprache', Mapper::getMapping('mSprache'));
        $langCount = count($languages);
        for ($i = 0; $i < $langCount; $i++) {
            $languages[$i]->cStandard = $languages[$i]->cWawiStandard;
            unset($languages[$i]->cWawiStandard);
        }
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
        if (count($languages) > 0) {
            DBDelInsert('tsprache', $languages, 1);
        }

        XML2DB($xml['globals'], 'tlieferstatus', Mapper::getMapping('mLieferstatus'));
        XML2DB($xml['globals'], 'txsellgruppe', Mapper::getMapping('mXsellgruppe'));
        XML2DB($xml['globals'], 'teinheit', Mapper::getMapping('mEinheit'));
        XML2DB($xml['globals'], 'twaehrung', Mapper::getMapping('mWaehrung'));
        XML2DB($xml['globals'], 'tsteuerklasse', Mapper::getMapping('mSteuerklasse'));
        XML2DB($xml['globals'], 'tsteuersatz', Mapper::getMapping('mSteuersatz'));
        XML2DB($xml['globals'], 'tversandklasse', Mapper::getMapping('mVersandklasse'));

        if (isset($xml['globals']['tsteuerzone']) && is_array($xml['globals']['tsteuerzone'])) {
            $taxZones = mapArray($xml['globals'], 'tsteuerzone', Mapper::getMapping('mSteuerzone'));
            DBDelInsert('tsteuerzone', $taxZones, 1);
            $db->query('DELETE FROM tsteuerzoneland', \DB\ReturnType::DEFAULT);
            $taxCount = count($taxZones);
            for ($i = 0; $i < $taxCount; $i++) {
                if (count($taxZones) < 2) {
                    XML2DB($xml['globals']['tsteuerzone'], 'tsteuerzoneland', Mapper::getMapping('mSteuerzoneland'), 0);
                } else {
                    XML2DB(
                        $xml['globals']['tsteuerzone'][$i],
                        'tsteuerzoneland',
                        Mapper::getMapping('mSteuerzoneland'),
                        0
                    );
                }
            }
        }
        if (isset($xml['globals']['tkundengruppe']) && is_array($xml['globals']['tkundengruppe'])) {
            $customerGroups = mapArray($xml['globals'], 'tkundengruppe', Mapper::getMapping('mKundengruppe'));
            DBDelInsert('tkundengruppe', $customerGroups, 1);
            $db->query('TRUNCATE TABLE tkundengruppensprache', \DB\ReturnType::DEFAULT);
            $db->query('TRUNCATE TABLE tkundengruppenattribut', \DB\ReturnType::DEFAULT);
            $cgCount = count($customerGroups);
            for ($i = 0; $i < $cgCount; $i++) {
                if (count($customerGroups) < 2) {
                    XML2DB(
                        $xml['globals']['tkundengruppe'],
                        'tkundengruppensprache',
                        Mapper::getMapping('mKundengruppensprache'),
                        0
                    );
                    XML2DB(
                        $xml['globals']['tkundengruppe'],
                        'tkundengruppenattribut',
                        Mapper::getMapping('mKundengruppenattribut'),
                        0
                    );
                } else {
                    XML2DB(
                        $xml['globals']['tkundengruppe'][$i],
                        'tkundengruppensprache',
                        Mapper::getMapping('mKundengruppensprache'),
                        0
                    );
                    XML2DB(
                        $xml['globals']['tkundengruppe'][$i],
                        'tkundengruppenattribut',
                        Mapper::getMapping('mKundengruppenattribut'),
                        0
                    );
                }
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
        }
        if (isset($xml['globals']['twarenlager']) && is_array($xml['globals']['twarenlager'])) {
            $storages   = mapArray($xml['globals'], 'twarenlager', Mapper::getMapping('mWarenlager'));
            $visibility = $db->query(
                'SELECT kWarenlager, nAktiv FROM twarenlager WHERE nAktiv = 1',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            // Alle Einträge in twarenlager löschen - Wawi 1.0.1 sendet immer alle Warenlager.
            $db->query('DELETE FROM twarenlager WHERE 1', \DB\ReturnType::DEFAULT);
            DBUpdateInsert('twarenlager', $storages, 'kWarenlager');
            // Lagersichtbarkeit übertragen
            if (!empty($visibility)) {
                foreach ($visibility as $lager) {
                    $db->update('twarenlager', 'kWarenlager', $lager->kWarenlager, $lager);
                }
            }
        }
        if (isset($xml['globals']['tmasseinheit']) && is_array($xml['globals']['tmasseinheit'])) {
            $units = mapArray($xml['globals'], 'tmasseinheit', Mapper::getMapping('mMasseinheit'));
            foreach ($units as &$_me) {
                //hack?
                unset($_me->kBezugsMassEinheit);
            }
            unset($_me);
            DBDelInsert('tmasseinheit', $units, 1);
            $db->query('TRUNCATE TABLE tmasseinheitsprache', \DB\ReturnType::DEFAULT);
            $meCount = count($units);
            for ($i = 0; $i < $meCount; $i++) {
                if (count($units) < 2) {
                    XML2DB(
                        $xml['globals']['tmasseinheit'],
                        'tmasseinheitsprache',
                        Mapper::getMapping('mMasseinheitsprache'),
                        0
                    );
                } else {
                    XML2DB(
                        $xml['globals']['tmasseinheit'][$i],
                        'tmasseinheitsprache',
                        Mapper::getMapping('mMasseinheitsprache'),
                        0
                    );
                }
            }
        }
    }
    if (isset($xml['globals_wg']['tWarengruppe']) && is_array($xml['globals_wg']['tWarengruppe'])) {
        $groups = mapArray($xml['globals_wg'], 'tWarengruppe', Mapper::getMapping('mWarengruppe'));
        DBUpdateInsert('twarengruppe', $groups, 'kWarengruppe');
    }
}

/**
 * @param int $kWarengruppe
 */
function loescheWarengruppe(int $kWarengruppe)
{
    Shop::Container()->getDB()->delete('twarengruppe', 'kWarengruppe', $kWarengruppe);
    Shop::Container()->getLogService()->debug('Warengruppe geloescht: ' . $kWarengruppe);
}
