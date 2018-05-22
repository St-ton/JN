<?php
/**
 * new link group associations
 *
 * @author fm
 * @created Thu, 22 May 2018 10:50:00 +0200
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
class Migration_20180522105000 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("ALTER TABLE `tlinkgruppe` CHANGE COLUMN `kLinkgruppe` `kLinkgruppe` INT NOT NULL AUTO_INCREMENT;");
        $this->execute("CREATE TABLE `tlinkgroupassociations` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `linkID` INT NOT NULL,
              `linkGroupID` INT NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `fk_tlinkgroupassociations_1_idx` (`linkID` ASC),
              CONSTRAINT `fk_tlinkGroupID`
                  FOREIGN KEY (`linkGroupID`)
                  REFERENCES `tlinkgruppe` (`kLinkgruppe`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE,
              CONSTRAINT `fk_tlinkID`
                  FOREIGN KEY (`linkID`)
                  REFERENCES `tlink` (`kLink`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE)"
        );
        $duplicates = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tlink
                GROUP BY klink
                HAVING COUNT(*) > 1",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($duplicates as $duplicate) {
            Shop::Container()->getDB()->delete('tlink', 'kLink', $duplicate->kLink);
            unset($duplicate->kLink);
            Shop::Container()->getDB()->insert('tlink', $duplicate);
        }
        $this->execute("INSERT INTO tlinkgroupassociations (`linkID`, `linkGroupID`) (SELECT kLink, kLinkgruppe FROM tlink)");
        $this->execute("ALTER TABLE `tlink` 
            DROP COLUMN `kLinkgruppe`,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`kLink`),
            DROP INDEX `kLinkgruppe`");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tlink` ADD COLUMN `kLinkgruppe` TINYINT(3) UNSIGNED NOT NULL;");
        $assoc = Shop::Container()->getDB()->query(
            "SELECT linkID, linkGroupID 
                FROM tlinkgroupassociations",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($assoc as $item) {
            $upd = new stdClass();
            $upd->kLinkgruppe = $item->linkGroupID;
            Shop::Container()->getDB()->update('tlink', 'kLink', $item->linkID, $upd);
        }
        $this->execute("DROP TABLE tlinkgroupassociations");
        $this->execute("ALTER TABLE `tlinkgruppe` CHANGE COLUMN `kLinkgruppe` `kLinkgruppe` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT");

    }
}
