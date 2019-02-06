<?php
/**
 * associate UstId-settings
 *
 * @author Clemens Rudolph
 * @created Tue, 05 Feb 2019 14:59:48 +0100
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
class Migration_20190205145948 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'associate UstId-settings';

    public function up()
    {
        $this->execute('UPDATE teinstellungenconf SET nSort = 415 WHERE kEinstellungenConf = 6');
    }

    public function down()
    {
        $this->execute('UPDATE teinstellungenconf SET nSort = 140 WHERE kEinstellungenConf = 6');
    }
}
