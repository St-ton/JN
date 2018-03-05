<?php
/**
 * Convert encrypted data to utf-8
 *
 * @author Falk Prüfer
 * @created Tue, 09 Jan 2018 10:46:08 +0100
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180109104608 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Convert encrypted data to utf-8';
    protected $properties  = [
        'tkunde'            => ['kKunde', 'cNachname', 'cFirma', 'cZusatz', 'cStrasse'],
        'tzahlungsinfo'     => ['kZahlungsInfo', 'cBankName', 'cKartenNr', 'cCVV', 'cKontoNr', 'cBLZ', 'cIBAN', 'cBIC', 'cInhaber', 'cVerwendungszweck'],
        'tkundenkontodaten' => ['kKundenKontodaten', 'cBankName', 'nKonto', 'cBLZ', 'cIBAN', 'cBIC', 'cInhaber'],
    ];

    public function up()
    {
        foreach ($this->properties as $tableName => $propNames) {
            $keyName = array_shift($propNames);
            $dataSet = $this->fetchAll(
                "SELECT $keyName, " . implode(', ', $propNames) .
                "   FROM $tableName"
            );

            foreach ($dataSet as $dataObj) {
                foreach ($propNames as $propName) {
                    $dataObj->$propName = entschluesselXTEA($dataObj->$propName);
                    if (!StringHandler::is_utf8($dataObj->$propName)) {
                        $dataObj->$propName = StringHandler::convertUTF8($dataObj->$propName);
                    }
                    $dataObj->$propName = verschluesselXTEA($dataObj->$propName);
                }

                Shop::Container()->getDB()->update($tableName, $keyName, $dataObj->$keyName, $dataObj);
            }
        }
    }

    public function down()
    {
        foreach ($this->properties as $tableName => $propNames) {
            $keyName = array_shift($propNames);
            $dataSet = $this->fetchAll(
                "SELECT $keyName, " . implode(', ', $propNames) .
                "   FROM $tableName"
            );

            foreach ($dataSet as $dataObj) {
                foreach ($propNames as $propName) {
                    $dataObj->$propName = entschluesselXTEA($dataObj->$propName);
                    if (StringHandler::is_utf8($dataObj->$propName)) {
                        $dataObj->$propName = StringHandler::convertISO($dataObj->$propName);
                    }
                    $dataObj->$propName = verschluesselXTEA($dataObj->$propName);
                }

                Shop::Container()->getDB()->update($tableName, $keyName, $dataObj->$keyName, $dataObj);
            }
        }
    }
}
