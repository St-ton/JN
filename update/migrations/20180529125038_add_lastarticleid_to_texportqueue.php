<?php
/**
 * Add LastArticleID to texportqueue
 *
 * @author fp
 * @created Tue, 29 May 2018 12:50:38 +0200
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
class Migration_20180529125038 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add LastArticleID to texportqueue';

    public function up()
    {
        $this->execute(
            "ALTER TABLE `texportqueue` ADD COLUMN `nLastArticleID` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `nLimit_m`"
        );
        $this->execute(
            "ALTER TABLE `tjobqueue` ADD COLUMN `nLastArticleID` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `nLimitm`"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE `texportqueue` DROP COLUMN `nLastArticleID`"
        );
        $this->execute(
            "ALTER TABLE `tjobqueue` DROP COLUMN `nLastArticleID`"
        );
    }
}
