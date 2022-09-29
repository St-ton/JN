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
        $this->execute("INSERT INTO tsprachsektion (cName) VALUES ('datatables')");

        $this->setLocalization('ger', 'account data', 'shippingAddress', 'Lieferadressen verwalten');
        $this->setLocalization('eng', 'account data', 'shippingAddress', 'Managing shipping addresses');

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

        $this->setLocalization('ger', 'account data', 'newShippingAddress', 'Lieferadresse neu anlegen');
        $this->setLocalization('eng', 'account data', 'newShippingAddress', 'Add as new shipping address');

        $this->setLocalization(
            'ger',
            'account data',
            'checkoutSaveAsNewShippingAddressPreset',
            'Lieferadresse zu meinen bestehenden Lieferadressen hinzufügen'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'checkoutSaveAsNewShippingAddressPreset',
            'Add shipping address to my existing shipping addresses'
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


        $this->setLocalization('ger', 'datatables', 'lengthMenu', '_MENU_ Adressen anzeigen');
        $this->setLocalization('eng', 'datatables', 'lengthMenu', 'Show _MENU_ addresses');

        $this->setLocalization('ger', 'datatables', 'info', 'Eintrag _START_ – _END_ von insgesamt _TOTAL_ Einträgen');
        $this->setLocalization('eng', 'datatables', 'info', 'Entries _START_ – _END_ of a total of _TOTAL_ entries');

        $this->setLocalization('ger', 'datatables', 'infoEmpty', 'Keine Daten vorhanden');
        $this->setLocalization('eng', 'datatables', 'infoEmpty', 'No data available');

        $this->setLocalization('ger', 'datatables', 'infoFiltered', '(gefiltert von _MAX_ Einträgen)');
        $this->setLocalization('eng', 'datatables', 'infoFiltered', '(filtered from _MAX_ total entries)');

        $this->setLocalization('ger', 'datatables', 'search', 'Adresssuche');
        $this->setLocalization('eng', 'datatables', 'search', 'Address search');

        $this->setLocalization('ger', 'datatables', 'zeroRecords', 'Keine passenden Einträge gefunden');
        $this->setLocalization('eng', 'datatables', 'zeroRecords', 'No matching records found');

        $this->setLocalization('ger', 'datatables', 'paginatefirst', 'Erste');
        $this->setLocalization('eng', 'datatables', 'paginatefirst', 'First');

        $this->setLocalization('ger', 'datatables', 'paginatelast', 'Letzte');
        $this->setLocalization('eng', 'datatables', 'paginatelast', 'Last');

        $this->setLocalization('ger', 'datatables', 'paginatenext', 'Nächste');
        $this->setLocalization('eng', 'datatables', 'paginatenext', 'Next');

        $this->setLocalization('ger', 'datatables', 'paginateprevious', 'Vorherige');
        $this->setLocalization('eng', 'datatables', 'paginateprevious', 'Previous');

        $this->setLocalization('ger', 'global', 'showMore', 'Mehr anzeigen');
        $this->setLocalization('eng', 'global', 'showMore', 'Show more');

        $this->setLocalization('ger', 'account data', 'setAsStandard', 'Als Standard festlegen');
        $this->setLocalization('eng', 'account data', 'setAsStandard', 'Set as default');


        $this->execute("CREATE TABLE IF NOT EXISTS `tlieferadressevorlage` (
                                `kLieferadresse` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `kKunde` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                                `cAnrede` VARCHAR(20) NOT NULL DEFAULT '',
                                `cVorname` VARCHAR(255) NOT NULL DEFAULT '',
                                `cNachname` VARCHAR(255) NOT NULL DEFAULT '',
                                `cTitel` VARCHAR(64) NULL,
                                `cFirma` VARCHAR(255) NULL,
                                `cZusatz` VARCHAR(255) NULL,
                                `cStrasse` VARCHAR(255) NOT NULL DEFAULT '',
                                `cHausnummer` VARCHAR(32) NOT NULL DEFAULT '',
                                `cAdressZusatz` VARCHAR(255) NULL,
                                `cPLZ` VARCHAR(20) NOT NULL DEFAULT '',
                                `cOrt` VARCHAR(255) NOT NULL DEFAULT '',
                                `cBundesland` VARCHAR(255) NOT NULL DEFAULT '',
                                `cLand` VARCHAR(255) NOT NULL DEFAULT '',
                                `cTel` VARCHAR(255) NULL,
                                `cMobil` VARCHAR(255) NULL,
                                `cFax` VARCHAR(255) NULL,
                                `cMail` VARCHAR(255) NULL,
                                `nIstStandardLieferadresse` INT(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY (`kLieferadresse`),
                                INDEX `idx_kKunde` (`kKunde`, `nIstStandardLieferadresse`)
                            )
                            COMMENT='Beinhaltet veränderbare und löschbare Lieferadressenvorlagen.'
                            CHARSET=utf8
                            COLLATE='utf8_unicode_ci'
                            ENGINE=InnoDB
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
        $this->removeLocalization('showMore', 'global');
        $this->removeLocalization('setAsStandard', 'account data');

        $this->execute("DELETE FROM tsprachsektion WHERE cName = 'datatables'");
    }
}
