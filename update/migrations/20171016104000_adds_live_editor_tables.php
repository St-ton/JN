<?php
/**
 * @author ms
 * @created Mon, 16 October 2017 10:40:00 +0200
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
class Migration_20171016104000 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("CREATE TABLE `teditorpage` (
                                  `kEditorPage` INT AUTO_INCREMENT,
                                  `cKey` VARCHAR(255) NOT NULL,
                                  `kKey` INT(10) UNSIGNED NOT NULL,
                                  `kSprache` TINYINT(3) UNSIGNED NOT NULL,
                                  `nEditorContent` LONGTEXT NULL,
                                  `cJSON` LONGTEXT NULL,
                                  PRIMARY KEY (`kEditorPage`),
                                  UNIQUE INDEX `PageID` (`cKey` ASC, `kKey` ASC, `kSprache` ASC));
        ");

        $this->execute("CREATE TABLE `teditorpagecontent` (
                                  `kEditorPageContent` INT AUTO_INCREMENT,
                                  `kEditorPage` INT UNSIGNED NOT NULL,
                                  `cAreaID` VARCHAR(255) NOT NULL,
                                  `cContent` LONGTEXT NULL,
                                  PRIMARY KEY (`kEditorPageContent`),
                                  UNIQUE INDEX `ContentID` (`kEditorPageContent` ASC, `kEditorPage` ASC, `cAreaID` ASC));
        ");

        $this->execute("CREATE TABLE `teditorportlets` (
                                  `kPortlet` INT(10) NOT NULL AUTO_INCREMENT,
                                  `kPlugin` INT(10) NOT NULL,
                                  `cTitle` VARCHAR(255) NOT NULL,
                                  `cClass` VARCHAR(255) NOT NULL,
                                  `cGroup` VARCHAR(255) NOT NULL,
                                  `bActive` TINYINT(1) NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`kPortlet`));");

        $this->execute("INSERT INTO `teditorportlets` (`kPlugin`, `cTitle`, `cClass`, `cGroup`, `bActive`) 
                                  VALUES ('0', 'Heading', 'Heading', 'Basic HTML', '1');");
        $this->execute("INSERT INTO `teditorportlets` (`kPlugin`, `cTitle`, `cClass`, `cGroup`, `bActive`) 
                                  VALUES ('0', 'Column', 'Column', 'Basic HTML', '1');");
    }

    public function down()
    {
        $this->execute("DROP TABLE `teditorpage`;");
        $this->execute("DROP TABLE `teditorpagecontent`;");
        $this->execute("DROP TABLE `teditorportlets`;");
    }
}
