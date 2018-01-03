<?php
/**
 * Rename the settings-menu entries "Einstellungen" into proper names
 *
 * @author Clemens Rudolph
 * @created Fri, 27 Oct 2017 11:16:17 +0200
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
class Migration_20171027111617 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Rename the settings-menu entries "Einstellungen" into proper names';

    public function up()
    {
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Formulareinstellungen" WHERE `cRecht` = "SETTINGS_CUSTOMERFORM_VIEW"');
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Emaileinstellungen" WHERE `cRecht` = "SETTINGS_EMAILS_VIEW"');
    }

    public function down()
    {
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Einstellungen" WHERE `cRecht` = "SETTINGS_CUSTOMERFORM_VIEW"');
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Einstellungen" WHERE `cRecht` = "SETTINGS_EMAILS_VIEW"');
    }
}
