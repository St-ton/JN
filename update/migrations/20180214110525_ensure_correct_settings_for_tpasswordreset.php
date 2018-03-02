<?php
/**
 * ensure_correct_settings_for_tpasswordreset
 *
 * @author Martin Schophaus
 * @created Wed, 14 Feb 2018 11:05:25 +0100
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
class Migration_20180214110525 extends Migration implements IMigration
{
    protected $author = 'Martin Schophaus';
    protected $description = 'Ensure correct settings for tpasswordreset';

    public function up()
    {
        $this->execute("
            ALTER TABLE tpasswordreset CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
            ALTER TABLE tpasswordreset ENGINE = InnoDB;
        ");
    }

    public function down()
    {
    }
}