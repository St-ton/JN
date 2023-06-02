<?php declare(strict_types=1);
/**
 * Add tables and language variables for RMA functionality
 *
 * @author Tim Niko Tegtmeyer
 * @created Fri, 19 May 2023 12:08:34 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230519120834
 */
class Migration_20230519120834 extends Migration implements IMigration
{
    protected $author = 'Tim Niko Tegtmeyer';
    protected $description = 'Add tables and language variables for RMA functionality';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
        "CREATE TABLE IF NOT EXISTS `tretoure` (
                `kRetoure` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cRetoureWawi` VARCHAR(255),
                `kKunde` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `kLieferadresse` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `cStatus` CHAR(2),
                `dErstellt` DATETIME NOT NULL,
                PRIMARY KEY (`kRetoure`),
                KEY `kArtikel` (`cRetoureWawi`,`kKunde`,`kLieferadresse`,`cStatus`)
            )
            COMMENT='Retouren werden hier eingetragen'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tretourepos` (
                `kRetourePos` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `kRetoure` INT(10) UNSIGNED NOT NULL,
                `kBestellPos` INT(10) UNSIGNED,
                `kArtikel` INT(10) UNSIGNED,
                `cName` VARCHAR(255) NOT NULL DEFAULT '',
                `fPreisEinzelNetto` DOUBLE NOT NULL DEFAULT 0,
                `nAnzahl` DOUBLE(10,4) NOT NULL DEFAULT 0,
                `fMwSt` FLOAT(5,2),
                `cEinheit` VARCHAR(255),
                `fLagerbestandVorAbschluss` DOUBLE,
                `nLongestMinDelivery` INT(11) NOT NULL DEFAULT 0,
                `nLongestMaxDelivery` INT(1) NOT NULL DEFAULT 0,
                `cHinweis` VARCHAR(255),
                `cStatus` CHAR(2),
                `dErstellt` DATETIME NOT NULL,
                PRIMARY KEY (`kRetourePos`),
                KEY `kArtikel` (`kRetourePos`,`kRetoure`,`kArtikel`)
            )
            COMMENT='Retourenposition erstellt im Shop oder aus der WaWi übernommen.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");

        $newVars = new stdClass();
        $newVars->rma = [
            'statusRejected' =>
                [
                    'ger' => 'Abgelehnt',
                    'eng' => 'Rejected'
                ]
            , 'statusOpen' =>
                [
                    'ger' => 'Offen',
                    'eng' => 'Open'
                ]
            , 'statusAccepted' =>
                [
                    'ger' => 'Akzeptiert',
                    'eng' => 'Accepted'
                ]
            , 'statusProcessing' =>
                [
                    'ger' => 'In Bearbeitung',
                    'eng' => 'Processing'
                ]
            , 'statusCompleted' =>
                [
                    'ger' => 'Abgeschlossen',
                    'eng' => 'Completed'
                ]
            , 'showPositions' =>
                [
                    'ger' => 'Positionen anzeigen',
                    'eng' => 'Show positions'
                ]
            , 'createRetoure' =>
                [
                    'ger' => 'Retoure anlegen',
                    'eng' => 'Request RMA'
                ]
        ];
        $newVars->datatables = [
            'search' =>
                [
                    'ger' => 'Suche',
                    'eng' => 'Search'
                ]
            , 'lengthMenu' =>
                [
                    'ger' => '_MENU_ Einträge anzeigen',
                    'eng' => 'Show _MENU_ entries'
                ]
        ];
        foreach ($newVars as $sprachsektion => $arr) {
            foreach ($arr as $key => $values) {
                foreach ($values as $iso => $value) {
                    $this->setLocalization($iso, $sprachsektion, $key, $value);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS tretourepos');
        $this->execute('DROP TABLE IF EXISTS tretoure');

        $this->execute("DELETE FROM `tsprachwerte`
                WHERE `kSprachsektion` = 30 
                    AND cName IN (
                        'statusRejected',
                        'statusOpen',
                        'statusAccepted',
                        'statusProcessing',
                        'statusCompleted',
                        'showPositions'
                    )
                    AND bSystem = 1"
        );
        
        $newVars = [
            'search' =>
                [
                    'ger' => 'Adresssuche',
                    'eng' => 'Search address'
                ]
            , 'lengthMenu' =>
                [
                    'ger' => '_MENU_ Adressen anzeigen',
                    'eng' => 'Show _MENU_ addresses'
                ]
        ];
        foreach ($newVars as $key => $values) {
            foreach ($values as $iso => $value) {
                $this->setLocalization($iso, 'datatables', $key, $value);
            }
        }
    }
}
