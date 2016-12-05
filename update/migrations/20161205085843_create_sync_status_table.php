<?php
/**
 * Create sync status table
 *
 * @author fp
 * @created Mon, 05 Dec 2016 08:58:43 +0100
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
class Migration_20161205085843 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->execute(
            "CREATE TABLE tsyncstatus (
                id          INT(11)         NOT NULL AUTO_INCREMENT,
                syncfile    VARCHAR(128)    NOT NULL,
                started     DATETIME        NOT NULL,
                counter     INT(8)          NOT NULL DEFAULT 0,
                lastupdate  DATETIME        NOT NULL,
                finished    INT(1)          NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY idx_uq_syncfile_started (syncfile, started)
            )"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS tsyncstatus");
    }
}
