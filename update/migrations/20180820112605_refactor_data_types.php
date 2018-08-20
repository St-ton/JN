<?php
/**
 * Refactor data types
 *
 * @author fp
 * @created Mon, 20 Aug 2018 11:26:05 +0200
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
class Migration_20180820112605 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Refactor data types for kKundengruppe';

    public function up()
    {
        $columns = $this->fetchAll(
            "SELECT TABLE_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND COLUMN_NAME = 'kKundengruppe'
                    AND DATA_TYPE = 'tinyint'
                    AND TABLE_NAME NOT LIKE 'xplugin_%'"
        );
        foreach ($columns as $column) {
            $alterSQL = /** @lang text */
                'ALTER TABLE ' . DB_NAME . '.`' . $column->TABLE_NAME . '` CHANGE `kKundengruppe` `kKundengruppe` INT'
                .(strpos($column->COLUMN_TYPE, 'unsigned') !== false ? ' UNSIGNED' : '')
                .($column->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                .($column->COLUMN_DEFAULT === null || $column->COLUMN_DEFAULT === 'NULL' ? ($column->IS_NULLABLE === 'YES' ? ' DEFAULT NULL' : '') : ' DEFAULT \'' . $column->COLUMN_DEFAULT . '\'');

            $this->execute($alterSQL);
        }
    }

    public function down()
    {
        // can not be undone...
    }
}
