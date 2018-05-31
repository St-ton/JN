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
        $sprachen               = gibAlleSprachen();
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
        $this->execute("INSERT INTO tlinkgroupassociations (`linkID`, `linkGroupID`) 
            (SELECT tlink.kLink, tlink.kLinkgruppe 
                FROM tlink 
                JOIN tlinkgruppe
                    ON tlinkgruppe.kLinkgruppe = tlink.kLinkgruppe)");
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
