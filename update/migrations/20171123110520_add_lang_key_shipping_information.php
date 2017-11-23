<?php
/**
 * add_lang_key_shipping_information
 *
 * @author Mirko
 * @created Thu, 23 Nov 2017 11:05:20 +0100
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
class Migration_20171123110520 extends Migration implements IMigration
{
    protected $author = 'Mirko';
    protected $description = 'add_lang_key_shipping_information';

    public function up()
    {
        $this->setLocalization('ger', 'basket', 'shippingInformationSpecific', 'Zzgl. <a href="%1$s" class="shipment popup">Versandkosten</a> ab %2$s bei Lieferung nach %3$s');
        $this->setLocalization('eng', 'basket', 'shippingInformationSpecific', 'Plus <a href="%1$s" class="shipment popup">shipping costs</a> starting from %2$s for delivery to %3$s');
        $this->setLocalization('ger', 'basket', 'shippingInformation', 'Zzgl. <a href="%1$s" class="shipment popup">Versandkosten</a>');
        $this->setLocalization('eng', 'basket', 'shippingInformation', 'Plus <a href="%1$s" class="shipment popup">shipping costs</a>');
    }

    public function down()
    {
        $this->removeLocalization('shippingInformationSpecific');
        $this->removeLocalization('shippingInformation');
    }
}