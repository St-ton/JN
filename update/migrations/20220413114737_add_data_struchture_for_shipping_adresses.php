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
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'account data', 'shippingAdress', 'Lieferadressen verwalten');
        $this->setLocalization('eng', 'account data', 'shippingAdress', 'Manage shipping addresses');

        $this->setLocalization('ger', 'account data', 'useAsDefaultShippingAdress', 'Als Standard Lieferadresse verwenden');
        $this->setLocalization('eng', 'account data', 'useAsDefaultShippingAdress', 'Use as default shipping address');

        $this->setLocalization('ger', 'account data', 'editAddres', 'Adresse bearbeiten');
        $this->setLocalization('eng', 'account data', 'editAddres', 'Edit address');

        $this->setLocalization('ger', 'account data', 'deleteAddres', 'Adresse löschen');
        $this->setLocalization('eng', 'account data', 'deleteAddres', 'Delete address');

        $this->setLocalization('ger', 'account data', 'saveAddress', 'Lieferadresse speichern');
        $this->setLocalization('eng', 'account data', 'saveAddress', 'Save shipping address');

        $this->setLocalization('ger', 'account data', 'updateAddress', 'Lieferadresse aktualisieren');
        $this->setLocalization('eng', 'account data', 'updateAddress', 'Update shipping address');

        $this->setLocalization('ger', 'account data', 'updateAddressBackToCheckout', 'Lieferadresse aktualisieren und zurück zum Checkout');
        $this->setLocalization('eng', 'account data', 'updateAddressBackToCheckout', 'Update shipping address and back to checkout');

        $this->setLocalization('ger', 'account data', 'editShippingAddress', 'Lieferadressen ändern');
        $this->setLocalization('eng', 'account data', 'editShippingAddress', 'Edit shipping address');

        $this->setLocalization('ger', 'account data', 'myShippingAdresses', 'Meine Lieferadressen');
        $this->setLocalization('eng', 'account data', 'myShippingAdresses', 'My shipping address');

        $this->setLocalization('ger', 'global', 'myShippingAdresses', 'Meine Lieferadressen');
        $this->setLocalization('eng', 'global', 'myShippingAdresses', 'My shipping address');

        $this->setLocalization('ger', 'account data', 'deleteAddressSuccessful', 'Lieferadresse wurde gelöscht');
        $this->setLocalization('eng', 'account data', 'deleteAddressSuccessful', 'Shipping address deleted');

        $this->setLocalization('ger', 'account data', 'updateAddressSuccessful', 'Lieferadresse wurde aktualisiert');
        $this->setLocalization('eng', 'account data', 'updateAddressSuccessful', 'Shipping address has been updated');

        $this->setLocalization('ger', 'account data', 'saveAddressSuccessful', 'Lieferadresse wurde gespeichert');
        $this->setLocalization('eng', 'account data', 'saveAddressSuccessful', 'Shipping address has been deleted');

        $this->setLocalization('ger', 'account data', 'checkoutSaveAsNewShippingAddressPreset', 'Diese Lieferadresse zu meine Vorlagen hinzufügen');
        $this->setLocalization('eng', 'account data', 'checkoutSaveAsNewShippingAddressPreset', 'Add this shipping address to my templates');

        $this->execute("CREATE TABLE `tlieferadressevorlage` (
                                `kLieferadresse` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `kKunde` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                                `cAnrede` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
                   SS             `cVorname` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
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
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "DROP TABLE tlieferadressevorlage;"
        );
    }
}
