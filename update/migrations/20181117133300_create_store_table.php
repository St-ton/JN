<?php
/**
 * create store table
 *
 * @author aj
 * @created Mon, 17 Nov 2018 13:33:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

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
            "CREATE TABLE `tstoreauth` (
                `auth_code` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
                `access_token` text COLLATE utf8_unicode_ci,
                `created_at` datetime NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE `tstoreauth`");
    }
}
