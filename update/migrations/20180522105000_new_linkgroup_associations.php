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
        $this->execute("ALTER TABLE `tlinkgruppe` CHANGE COLUMN `kLinkgruppe` `kLinkgruppe` INT UNSIGNED NOT NULL AUTO_INCREMENT, ENGINE=InnoDB;");
        $this->execute("ALTER TABLE `tlink` CHANGE COLUMN `kLink` `kLink` INT UNSIGNED NOT NULL AUTO_INCREMENT, ENGINE=InnoDB;");
        $missingLanguageEntries = Shop::Container()->getDB()->query(
            "SELECT tlink.*, tseo.* 
                FROM tlink
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                WHERE kLink NOT IN (SELECT kLink FROM tlinksprache)",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $missingLanguageEntries = \Functional\group($missingLanguageEntries, function ($e) {
            return $e->kLink;
        });
        $sprachen               = Sprache::getAllLanguages();
        foreach ($missingLanguageEntries as $linkID => $links) {
            $linkData           = \Functional\first($links);
            $linkSprache        = new stdClass();
            $linkSprache->kLink = $linkID;
            foreach ($sprachen as $sprache) {
                $match = \Functional\first($links, function ($e) use ($sprache) {
                    return (int)$e->kSprache === $sprache->kSprache;
                });
                if ($match === null) {
                    // no seo entry exists for this language ID
                    $linkSprache->cName       = $linkData->cName;
                    $linkSprache->cSeo        = $linkData->cName;
                } else {
                    $linkSprache->cSeo        = $match->cSeo;
                    $linkSprache->cName       = $match->cName;
                }
                $linkSprache->cISOSprache = $sprache->cISO;
                $linkSprache->cTitle      = '';
                $linkSprache->cContent    = '';
                $linkSprache->cMetaTitle  = '';
                $linkSprache->cSeo        = getSeo($linkSprache->cSeo);
                Shop::Container()->getDB()->insert('tlinksprache', $linkSprache);
            }
        }
        $missingSeo = Shop::Container()->getDB()->query(
            "SELECT tlink.*, tlinksprache.*, tsprache.kSprache 
                FROM tlink
                JOIN tlinksprache
                    ON tlinksprache.kLink = tlink.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlink.kLink 
                WHERE tseo.cSeo IS NULL",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($missingSeo as $item) {
            $oSeo           = new stdClass();
            $oSeo->cSeo     = checkSeo($item->cSeo);
            $oSeo->kKey     = $item->kLink;
            $oSeo->cKey     = 'kLink';
            $oSeo->kSprache = $item->kSprache;
            Shop::Container()->getDB()->insert('tseo', $oSeo);
        }
        $missingLinkGroupLanguages = Shop::Container()->getDB()->query(
            "SELECT tlinkgruppe.* 
                FROM tlinkgruppe
                LEFT JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkGruppe = tlinkgruppesprache.kLinkgruppe
                WHERE tlinkgruppesprache.kLinkgruppe IS NULL",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($missingLinkGroupLanguages as $missingLinkGroupLanguage) {
            foreach ($sprachen as $sprache) {
                $lang              = new stdClass();
                $lang->kLinkgruppe = $missingLinkGroupLanguage->kLinkgruppe;
                $lang->cName       = $missingLinkGroupLanguage->cName;
                $lang->cISOSprache = $sprache->cISO;
                Shop::Container()->getDB()->insert('tlinkgruppesprache', $lang);
            }
        }
        $this->execute("CREATE TABLE `tlinkgroupassociations` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `linkID` INT UNSIGNED NOT NULL,
              `linkGroupID` INT UNSIGNED NOT NULL,
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
                  ON UPDATE CASCADE
              ) ENGINE=InnoDB COLLATE utf8_unicode_ci"
        );
        $duplicates = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tlink
                GROUP BY klink
                HAVING COUNT(*) > 1",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $oldIDs     = [];
        foreach ($duplicates as $duplicate) {
            $oldParent = (int)$duplicate->kLink;
            Shop::Container()->getDB()->delete('tlink', 'kLink', $duplicate->kLink);
            unset($duplicate->kLink);
            $newID              = Shop::Container()->getDB()->insert('tlink', $duplicate);
            $oldIDs[$oldParent] = $newID;
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tlink SET kVaterLink = :parent WHERE kVaterLink = :oldParent',
                ['parent' => $newID, 'oldParent' => $oldParent],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
        foreach ($oldIDs as $oldID => $newID) {
            $res = Shop::Container()->getDB()->queryPrepared(
                'UPDATE tlink SET kVaterLink = :parent WHERE kVaterLink = :oldParent',
                ['parent' => $newID, 'oldParent' => $oldID],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
        $this->execute("INSERT INTO tlinkgroupassociations (`linkID`, `linkGroupID`) 
            (SELECT tlink.kLink, tlink.kLinkgruppe 
                FROM tlink 
                JOIN tlinkgruppe
                    ON tlinkgruppe.kLinkgruppe = tlink.kLinkgruppe)");
        $this->execute("ALTER TABLE `tlink` 
            DROP COLUMN `kLinkgruppe`,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`kLink`)");
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
        $this->execute("ALTER TABLE tlink ADD INDEX `kLinkgruppe` (`kLinkgruppe`)");
        $this->execute("ALTER TABLE tlink CHANGE COLUMN `kLink` `kLink` INT NOT NULL");
        $this->execute("ALTER TABLE tlink DROP PRIMARY KEY");
        $this->execute("ALTER TABLE tlink ADD PRIMARY KEY (`kLink`,`kLinkgruppe`)");
        $this->execute("ALTER TABLE tlink CHANGE COLUMN `kLink` `kLink` INT NOT NULL AUTO_INCREMENT");
    }
}
