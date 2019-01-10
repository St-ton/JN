<?php
/**
 * add_lang_invalid_url
 *
 * @author mh
 * @created Tue, 28 Aug 2018 13:05:42 +0200
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
class Migration_20180828130542 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang variable invalidURL';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'invalidURL', 'Bitte geben Sie eine valide URL ein.');
        $this->setLocalization('eng', 'global', 'invalidURL', 'Please enter a valid url.');
    }

    public function down()
    {
        $this->removeLocalization('invalidURL');
    }
}
