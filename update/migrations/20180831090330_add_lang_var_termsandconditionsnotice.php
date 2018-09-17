<?php
/**
 * add_lang_var_termsandconditionsnotice
 *
 * @author mh
 * @created Fri, 31 Aug 2018 09:03:30 +0200
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
class Migration_20180831090330 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'add lang var termsAndConditionsNotice';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'termsAndConditionsNotice', 'Ich habe die <a href="%s" %s>AGB/Kundeninformationen</a> gelesen und erkläre mit dem Absenden der Bestellung mein Einverständnis.');
        $this->setLocalization('eng', 'checkout', 'termsAndConditionsNotice', 'I have read the <a href="%s" %s>General Terms and Conditions</a> and declare them being the basis of this contract.');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Die <a href="%s" %s>Widerrufsbelehrung</a> habe ich zur Kenntnis genommen.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our <a href="%s" %s>Instructions for cancellation.</a>');
    }

    public function down()
    {
        $this->removeLocalization('termsAndConditionsNotice');

        $this->setLocalization('ger', 'checkout', 'cancellationPolicyNotice', 'Bitte beachten Sie unsere #LINK_WRB#.');
        $this->setLocalization('eng', 'checkout', 'cancellationPolicyNotice', 'Please take note of our #LINK_WRB#.');
    }
}
