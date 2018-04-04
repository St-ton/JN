<?php
/**
 * Create column nMehrfachauswahl for tmerkmal
 *
 * @author Felix Moche
 * @created Thu, 11 Mai 2017 15:34:00 +0200
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
class Migration_20170511153400 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = /** @lang text */
        'Create column nMehrfachauswahl in tmerkmal';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tmerkmal ADD COLUMN nMehrfachauswahl TINYINT NOT NULL DEFAULT 0"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE tmerkmal DROP COLUMN nMehrfachauswahl"
        );
    }
}
