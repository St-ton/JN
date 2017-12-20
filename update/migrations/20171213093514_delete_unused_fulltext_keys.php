<?php
/**
 * Delete unused fulltext keys
 *
 * @author fp
 * @created Wed, 13 Dec 2017 09:35:14 +0100
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
class Migration_20171213093514 extends Migration implements IMigration
{
    protected $author      = 'fpr';
    protected $description = 'Delete unused fulltext keys';

    public function up()
    {
        foreach (['tartikel', 'tartikelsprache'] as $table) {
            $oKeys = $this->fetchAll(
                "SHOW INDEX FROM `{$table}` 
                    WHERE Index_type = 'FULLTEXT' 
	                    AND Column_name IN ('cBeschreibung', 'cKurzBeschreibung')
                        AND Key_name != 'idx_{$table}_fulltext'"
            );
            if (is_array($oKeys)) {
                foreach ($oKeys as $key) {
                    $this->execute("ALTER TABLE $table DROP KEY {$key->Key_name}");
                }
            }
        }
    }

    public function down()
    {
        foreach (['tartikel', 'tartikelsprache'] as $table) {
            foreach (['cBeschreibung', 'cKurzBeschreibung'] as $fieldName) {
                $this->execute(
                    "ALTER TABLE `{$table}`
	                    ADD FULLTEXT KEY `{$fieldName}` (`{$fieldName}`)"
                );
            }
        }
    }
}
