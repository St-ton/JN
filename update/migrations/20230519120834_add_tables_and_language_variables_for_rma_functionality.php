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
    
    private function getLangData(): object
    {
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
            , 'maxAnzahlTitle' =>
                [
                    'ger' => 'Maximale Anzahl erreicht!',
                    'eng' => 'Maximum quantity reached!'
                ]
            , 'maxAnzahlText' =>
                [
                    'ger' => 'Sie können nicht mehr Artikel retournieren, als Sie bestellt haben.',
                    'eng' => 'You cannot return more items than you ordered.'
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
        return $newVars;
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
        "CREATE TABLE IF NOT EXISTS `rma` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `wawiId` VARCHAR(255),
                `customerId` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `shippingAddressId` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `status` CHAR(2),
                `createDate` DATETIME NOT NULL,
                `lastModified` DATETIME,
                PRIMARY KEY (`id`),
                UNIQUE INDEX (`wawiId`),
                INDEX (`customerId`),
                INDEX (`shippingAddressId`)
            )
            COMMENT='RMA request created in shop or imported from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `rmapos` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `rmaId` INT(10) UNSIGNED NOT NULL,
                `shippingNoteId` INT(10) UNSIGNED NOT NULL,
                `shippingNotePosId` INT(10) NOT NULL,
                `orderPosId` INT(10) UNSIGNED NOT NULL,
                `productId` INT(10) UNSIGNED NOT NULL,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `unitPriceNet` DOUBLE NOT NULL DEFAULT 0,
                `quantity` DOUBLE(10,4) NOT NULL DEFAULT 0,
                `vat` FLOAT(5,2),
                `unit` VARCHAR(255),
                `stockBeforePurchase` DOUBLE,
                `longestMinDelivery` INT(11) NOT NULL DEFAULT 0,
                `longestMaxDelivery` INT(1) NOT NULL DEFAULT 0,
                `comment` VARCHAR(255),
                `status` CHAR(2),
                `history` MEDIUMTEXT COMMENT 'JSON encoded history of status changes',
                `createDate` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`rmaId`),
                INDEX (`shippingNoteId`),
                UNIQUE INDEX (`shippingNotePosId`),
                INDEX (`orderPosId`),
                INDEX (`productId`),
                INDEX (`status`),
                CONSTRAINT `fk_rmaId`
                    FOREIGN KEY (`rmaId`)
                        REFERENCES `rma`(`id`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                CONSTRAINT `fk_shippingNoteId`
                    FOREIGN KEY (`shippingNoteId`)
                        REFERENCES `tlieferscheinpos`(`kLieferscheinPos`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
            )
            COMMENT='RMA positions created in shop user account or synced from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");
        
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `rmapos` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `rmaId` INT(10) UNSIGNED NOT NULL,
                `createDate` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`rmaId`),
                CONSTRAINT `fk_rmaId`
                    FOREIGN KEY (`rmaId`)
                        REFERENCES `rma`(`id`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
            )
            COMMENT='RMA positions created in shop user account or synced from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");
        
        $this->execute('DROP TABLE IF EXISTS trma');
        $this->execute('DROP TABLE IF EXISTS trmaartikel');
        $this->execute('DROP TABLE IF EXISTS trmagrund');
        $this->execute('DROP TABLE IF EXISTS trmastatus');
        
        $newVars = $this->getLangData();
        
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
        $this->execute('DROP TABLE IF EXISTS rmapos');
        $this->execute('DROP TABLE IF EXISTS rma');
        
        $newVars = $this->getLangData();
        
        foreach ($newVars as $sprachsektion => $arr) {
            foreach ($arr as $key => $values) {
                $this->removeLocalization($key, $sprachsektion);
            }
        }
        
        // These language variables already exists from a previous migration and need to be overwritten
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
