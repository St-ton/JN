<?php
/**
 * Add language variables for missing tax zone
 *
 * @author Falk Prüfer
 * @created Tue, 10 Oct 2017 16:06:27 +0200
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
class Migration_20171010160627 extends Migration implements IMigration
{
    protected $author      = 'FP';
    protected $description = 'Add language variables for missing tax zone';

    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'missingTaxZoneForDeliveryCountry', 'Ein Versand nach %s ist aktuell nicht m&ouml;glich, da keine g&uuml;ltige Steuerzone hinterlegt ist.');
        $this->setLocalization('eng', 'errorMessages', 'missingTaxZoneForDeliveryCountry', 'A shipment to %s is currently not possible because there is no assigned tax zone.');
    }

    public function down()
    {
        $this->removeLocalization('missingTaxZoneForDeliveryCountry');
    }
}
