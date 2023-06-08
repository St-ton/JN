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
            , 'noItemsSelectedTitle' =>
                [
                    'ger' => 'Keine Artikel ausgewählt!',
                    'eng' => 'No products selected!'
                ]
            , 'noItemsSelectedText' =>
                [
                    'ger' => 'Sie müssen mindestens einen Artikel zum retournieren auswählen und einen Grund angeben.',
                    'eng' => 'You must select at least one product to return and provide a reason.'
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
                `wawiID` VARCHAR(255),
                `customerID` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `pickupAddressID` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `status` CHAR(2),
                `createDate` DATETIME NOT NULL,
                `lastModified` DATETIME,
                PRIMARY KEY (`id`),
                UNIQUE INDEX (`wawiID`),
                INDEX (`customerID`),
                INDEX (`pickupAddressID`),
                CONSTRAINT `fk_customerID`
                    FOREIGN KEY (`customerID`)
                        REFERENCES `tkunde`(`kKunde`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
            )
            COMMENT='RMA request created in shop or imported from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `rmapos` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `rmaID` INT(10) UNSIGNED NOT NULL,
                `shippingNoteID` INT(10) UNSIGNED NOT NULL,
                `shippingNotePosID` INT(10) NOT NULL,
                `orderPosID` INT(10) UNSIGNED NOT NULL,
                `productID` INT(10) UNSIGNED NOT NULL,
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
                INDEX (`rmaID`),
                INDEX (`shippingNoteID`),
                UNIQUE INDEX (`shippingNotePosID`),
                INDEX (`orderPosID`),
                INDEX (`productID`),
                INDEX (`status`),
                CONSTRAINT `fk_rmaID`
                    FOREIGN KEY (`rmaID`)
                        REFERENCES `rma`(`id`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                CONSTRAINT `fk_shippingNoteID`
                    FOREIGN KEY (`shippingNoteID`)
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
            "CREATE TABLE IF NOT EXISTS `pickupaddress` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `customerID` INT(10) UNSIGNED NOT NULL,
                `salutation` VARCHAR(20) NOT NULL,
                `firstName` VARCHAR(255) NOT NULL,
                `lastName` VARCHAR(255) NOT NULL,
                `academicTitle` VARCHAR(64) DEFAULT NULL,
                `companyName` VARCHAR(255) DEFAULT NULL,
                `companyAdditional` VARCHAR(255) DEFAULT NULL,
                `street` VARCHAR(255) NOT NULL,
                `houseNumber` VARCHAR(32) NOT NULL,
                `addressAdditional` VARCHAR(255) DEFAULT NULL,
                `postalCode` VARCHAR(20) NOT NULL,
                `city` VARCHAR(255) NOT NULL,
                `state` VARCHAR(255) NOT NULL,
                `country` VARCHAR(255) NOT NULL,
                `phone` VARCHAR(255) DEFAULT NULL,
                `mobilePhone` VARCHAR(255) DEFAULT NULL,
                `fax` VARCHAR(255) DEFAULT NULL,
                `mail` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX (`customerID`),
                CONSTRAINT `fk_customerID`
                    FOREIGN KEY (`customerID`)
                        REFERENCES `tkunde`(`kKunde`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                CONSTRAINT `fk_pickupAddressID`
                    FOREIGN KEY (`id`)
                        REFERENCES `rma`(`pickupAddressID`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
            )
            COMMENT='RMA positions created in shop user account or synced from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");
        
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `rmareasons` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `wawiID` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`wawiID`)
            )
            COMMENT='RMA reasons synced from WAWI.'
            DEFAULT CHARSET=utf8mb3
            COLLATE='utf8mb3_unicode_ci'
            ENGINE=InnoDB
        ");
        
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `rmareasonslang` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `reasonID` INT(10) UNSIGNED NOT NULL,
                `langID` INT(10) UNSIGNED NOT NULL,
                `title` VARCHAR(255),
                PRIMARY KEY (`id`),
                INDEX (`reasonID`),
                INDEX (`langID`),
                CONSTRAINT `fk_reasonID`
                    FOREIGN KEY (`reasonID`)
                        REFERENCES `rmareasons`(`id`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                CONSTRAINT `fk_langID`
                    FOREIGN KEY (`langID`)
                        REFERENCES `tsprache`(`kSprache`)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
            )
            COMMENT='Localized RMA reasons synced from WAWI.'
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
        $this->execute('DROP TABLE IF EXISTS rmareasonslang');
        $this->execute('DROP TABLE IF EXISTS rmareasons');
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
