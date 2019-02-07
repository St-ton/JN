<?php
/**
 * lang_for_new_alerts
 *
 * @author mh
 * @created Thu, 17 Jan 2019 09:31:18 +0100
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190117093118 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'language vars for new alerts';

    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'statusOrderNotFound', 'Keine passende Bestellung gefunden.');
        $this->setLocalization('eng', 'errorMessages', 'statusOrderNotFound', 'No matching order found.');

        $this->setLocalization('ger', 'errorMessages', 'uidNotFound', 'Keine uid gefunden.');
        $this->setLocalization('eng', 'errorMessages', 'uidNotFound', 'Uid not found.');

        $this->setLocalization('ger', 'messages', 'accountDeleted', 'Kundenkonto erfolgreich gelöscht.');
        $this->setLocalization('eng', 'messages', 'accountDeleted', 'Account successfully deleted.');

        $this->setLocalization('ger', 'errorMessages', 'cartPersRemoved', 'Der Artikel "%s" konnte nicht in den Warenkorb übernommen werden.');
        $this->setLocalization('eng', 'errorMessages', 'cartPersRemoved', 'The product "%s" could not be added to the cart.');

        $this->setLocalization('ger', 'messages', 'continueAfterActivation', 'Sie können mit dem Bestellprozess fortfahren wenn Ihr Kundenkonto freigeschaltet wurde.');
        $this->setLocalization('eng', 'messages', 'continueAfterActivation', 'You can continue with your order after your account has been activated.');
    }

    public function down()
    {
        $this->removeLocalization('statusOrderNotFound');
        $this->removeLocalization('uidNotFound');
        $this->removeLocalization('accountDeleted');
        $this->removeLocalization('cartPersRemoved');
        $this->removeLocalization('continueAfterActivation');
    }
}
