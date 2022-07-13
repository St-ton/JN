<?php declare(strict_types=1);
/**
 * add data struchture for shipping adresses
 *
 * @author rf
 * @created Wed, 13 Apr 2022 11:47:37 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220413114737
 */
class Migration_20220413114737 extends Migration implements IMigration
{
    protected $author = 'rf';
    protected $description = 'add data struchture for shipping adresses';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('INSERT INTO tsprachsektion (cName) VALUES ("datatables");');

        $this->setLocalization('ger', 'account data', 'shippingAddress', 'Lieferadressen verwalten');
        $this->setLocalization('eng', 'account data', 'shippingAddress', 'Manage shipping addresses');

        $this->setLocalization(
            'ger',
            'account data',
            'useAsDefaultShippingAddress',
            'Als Standardlieferadresse verwenden'
        );
        $this->setLocalization('eng', 'account data', 'useAsDefaultShippingAddress', 'Use as default shipping address');

        $this->setLocalization('ger', 'account data', 'editAddress', 'Adresse bearbeiten');
        $this->setLocalization('eng', 'account data', 'editAddress', 'Edit address');

        $this->setLocalization('ger', 'account data', 'deleteAddress', 'Adresse löschen');
        $this->setLocalization('eng', 'account data', 'deleteAddress', 'Delete address');

        $this->setLocalization('ger', 'account data', 'saveAddress', 'Lieferadresse speichern');
        $this->setLocalization('eng', 'account data', 'saveAddress', 'Save shipping address');

        $this->setLocalization('ger', 'account data', 'updateAddress', 'Lieferadresse aktualisieren');
        $this->setLocalization('eng', 'account data', 'updateAddress', 'Update shipping address');

        $this->setLocalization(
            'ger',
            'account data',
            'updateAddressBackToCheckout',
            'Lieferadresse aktualisieren und zurück zum Bestellvorgang'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'updateAddressBackToCheckout',
            'Update shipping address and back to checkout'
        );

        $this->setLocalization('ger', 'account data', 'editShippingAddress', 'Lieferadresse ändern');
        $this->setLocalization('eng', 'account data', 'editShippingAddress', 'Edit shipping address');

        $this->setLocalization('ger', 'account data', 'myShippingAddresses', 'Meine Lieferadressen');
        $this->setLocalization('eng', 'account data', 'myShippingAddresses', 'My shipping addresses');

        $this->setLocalization('ger', 'account data', 'deleteAddressSuccessful', 'Lieferadresse wurde gelöscht');
        $this->setLocalization('eng', 'account data', 'deleteAddressSuccessful', 'Shipping address has been deleted');

        $this->setLocalization('ger', 'account data', 'updateAddressSuccessful', 'Lieferadresse wurde aktualisiert');
        $this->setLocalization('eng', 'account data', 'updateAddressSuccessful', 'Shipping address has been updated');

        $this->setLocalization('ger', 'account data', 'saveAddressSuccessful', 'Lieferadresse wurde gespeichert');
        $this->setLocalization('eng', 'account data', 'saveAddressSuccessful', 'Shipping address has been saved');

        $this->setLocalization(
            'ger',
            'account data',
            'checkoutSaveAsNewShippingAddressPreset',
            'Diese Lieferadresse zu meinen Vorlagen hinzufügen'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'checkoutSaveAsNewShippingAddressPreset',
            'Add this shipping address to my templates'
        );

        $this->setLocalization('ger', 'account data', 'defaultShippingAddresses', 'Standardlieferadresse');
        $this->setLocalization('eng', 'account data', 'defaultShippingAddresses', 'Default shipping address');

        $this->setLocalization(
            'ger',
            'account data',
            'modalShippingAddressDeletionConfirmation',
            'Möchten Sie diese Lieferadresse wirklich löschen?'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'modalShippingAddressDeletionConfirmation',
            'Do you really want to delete this shipping address?'
        );


        $this->setLocalization('ger', 'global', 'myShippingAddresses', 'Meine Lieferadressen');
        $this->setLocalization('eng', 'global', 'myShippingAddresses', 'My shipping addresses');


        $this->setLocalization('ger', 'datatables', 'lengthMenu', '_MENU_ Zeilen anzeigen');
        $this->setLocalization('eng', 'datatables', 'lengthMenu', 'Show _MENU_ entries');

        $this->setLocalization('ger', 'datatables', 'info', '_START_ bis _END_ von _TOTAL_ Einträgen');
        $this->setLocalization('eng', 'datatables', 'info', 'Showing _START_ to _END_ of _TOTAL_ entries');

        $this->setLocalization('ger', 'datatables', 'infoEmpty', 'Keine Daten vorhanden');
        $this->setLocalization('eng', 'datatables', 'infoEmpty', 'Showing 0 to 0 of 0 entries');

        $this->setLocalization('ger', 'datatables', 'infoFiltered', '(gefiltert von _MAX_ Einträgen)');
        $this->setLocalization('eng', 'datatables', 'infoFiltered', '(filtered from _MAX_ total entries)');

        $this->setLocalization('ger', 'datatables', 'search', 'Suche:');
        $this->setLocalization('eng', 'datatables', 'search', 'Search:');

        $this->setLocalization('ger', 'datatables', 'zeroRecords', 'Keine passenden Einträge gefunden');
        $this->setLocalization('eng', 'datatables', 'zeroRecords', 'No matching records found');

        $this->setLocalization('ger', 'datatables', 'paginatefirst', 'Erste');
        $this->setLocalization('eng', 'datatables', 'paginatefirst', 'First');

        $this->setLocalization('ger', 'datatables', 'paginatelast', 'Letzte');
        $this->setLocalization('eng', 'datatables', 'paginatelast', 'Last');

        $this->setLocalization('ger', 'datatables', 'paginatenext', 'Nächste');
        $this->setLocalization('eng', 'datatables', 'paginatenext', 'Next');

        $this->setLocalization('ger', 'datatables', 'paginateprevious', 'Zurück');
        $this->setLocalization('eng', 'datatables', 'paginateprevious', 'Previous');


        $this->execute("CREATE TABLE IF NOT EXISTS `tlieferadressevorlage` (
                                `kLieferadresse` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `kKunde` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                                `cAnrede` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cVorname` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cNachname` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cTitel` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cFirma` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cZusatz` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cStrasse` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cHausnummer` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
                                `cAdressZusatz` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cPLZ` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cOrt` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cBundesland` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cLand` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                                `cTel` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cMobil` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cFax` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `cMail` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                `nIstStandardLieferadresse` INT(11) NULL DEFAULT '0',
                                PRIMARY KEY (`kLieferadresse`) USING BTREE,
                                INDEX `kKunde` (`kKunde`) USING BTREE
                            )
                            COMMENT='Beinhaltet veränderbare und löschbare Lieferadressenvorlagen.'
                            COLLATE='utf8_unicode_ci'
                            ENGINE=InnoDB
                            ROW_FORMAT=DYNAMIC;
                            ");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS tlieferadressevorlage');

        $this->removeLocalization('shippingAddress', 'account data');
        $this->removeLocalization('useAsDefaultShippingAddress', 'account data');
        $this->removeLocalization('editAddress', 'account data');
        $this->removeLocalization('deleteAddress', 'account data');
        $this->removeLocalization('saveAddress', 'account data');
        $this->removeLocalization('updateAddress', 'account data');
        $this->removeLocalization('updateAddressBackToCheckout', 'account data');
        $this->removeLocalization('editShippingAddress', 'account data');
        $this->removeLocalization('myShippingAddresses', 'account data');
        $this->removeLocalization('deleteAddressSuccessful', 'account data');
        $this->removeLocalization('updateAddressSuccessful', 'account data');
        $this->removeLocalization('saveAddressSuccessful', 'account data');
        $this->removeLocalization('checkoutSaveAsNewShippingAddressPreset', 'account data');
        $this->removeLocalization('defaultShippingAddresses', 'account data');
        $this->removeLocalization('myShippingAddresses', 'global');
        $this->removeLocalization('lengthMenu', 'datatables');
        $this->removeLocalization('info', 'datatables');
        $this->removeLocalization('infoEmpty', 'datatables');
        $this->removeLocalization('infoFiltered', 'datatables');
        $this->removeLocalization('search', 'datatables');
        $this->removeLocalization('zeroRecords', 'datatables');
        $this->removeLocalization('paginatefirst', 'datatables');
        $this->removeLocalization('paginatelast', 'datatables');
        $this->removeLocalization('paginatenext', 'datatables');
        $this->removeLocalization('paginateprevious', 'datatables');
    }
}
