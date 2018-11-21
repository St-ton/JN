<?php
/**
 * create store table
 *
 * @author aj
 * @created Mon, 17 Nov 2018 13:33:00 +0100
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
class Migration_20181117133300 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'create store table';

    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tstoreauth` (
                `access_token` varchar(100) NOT NULL,
                `refresh_token` varchar(100) NULL,
                `created_at` datetime NOT NULL,
                `expires_at` datetime NULL,
                PRIMARY KEY (`access_token`)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE `tstoreauth`");
    }
}
