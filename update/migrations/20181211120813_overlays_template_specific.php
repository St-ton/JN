<?php
/**
 * overlays_template_specific
 *
 * @author mh
 * @created Tue, 11 Dec 2018 12:08:13 +0100
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
class Migration_20181211120813 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'make overlays template specific';

    public function up()
    {
        $this->execute('ALTER TABLE `tsuchspecialoverlaysprache`
                          ADD COLUMN `cTemplate` VARCHAR(255) NOT NULL AFTER `kSprache`,
                          DROP PRIMARY KEY,
                          ADD PRIMARY KEY (`kSuchspecialOverlay`, `kSprache`, `cTemplate`)');
        $this->execute("UPDATE `tsuchspecialoverlaysprache` SET `cTemplate` = 'default'");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tsuchspecialoverlaysprache` WHERE `cTemplate` != 'default'");
        $this->execute('ALTER TABLE `tsuchspecialoverlaysprache`
                           DROP COLUMN `cTemplate`,
                           DROP PRIMARY KEY,
                           ADD PRIMARY KEY (`kSuchspecialOverlay`, `kSprache`)');
    }
}
