<?php
/**
 * add_missing_initial_data_for_some_boxes
 *
 * @author mh
 * @created Tue, 20 Nov 2018 10:41:26 +0100
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
class Migration_20181120104126 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add missing initial data for some boxes';

    public function up()
    {
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (40, 'left', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (40, 'bottom', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (41, 'left', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (41, 'bottom', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (42, 'left', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (42, 'bottom', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (38, 'left', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (38, 'bottom', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (36, 'left', 1)");
        $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES (36, 'bottom', 1)");

        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (123, 36, 0, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (123, 38, 0, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (123, 40, 0, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (123, 41, 0, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (123, 42, 0, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (124, 36, 5, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (124, 38, 5, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (124, 40, 5, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (124, 41, 5, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (124, 42, 5, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (107, 36, 21, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (107, 38, 21, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (107, 40, 21, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (107, 41, 21, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (107, 42, 21, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (108, 36, 22, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (108, 38, 22, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (108, 40, 22, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (108, 41, 22, 1, '')");
        $this->execute("INSERT IGNORE INTO `tboxensichtbar` (`kBox`, `kSeite`, `nSort`, `bAktiv`, `cFilter`) VALUES (108, 42, 22, 1, '')");
    }

    public function down()
    {
    }
}