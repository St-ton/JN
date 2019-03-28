<?php
/**
 * new link group associations
 *
 * @author fm
 * @created Thu, 22 May 2018 10:50:00 +0200
 */

use JTL\DB\ReturnType;
use JTL\Helpers\Seo;
use JTL\Sprache;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180522105000
 */
class Migration_20180522105000 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute('ALTER TABLE `tlinkgruppe` CHANGE COLUMN `kLinkgruppe` `kLinkgruppe` INT UNSIGNED NOT NULL AUTO_INCREMENT, ENGINE=InnoDB;');
        $this->execute('ALTER TABLE `tlink` CHANGE COLUMN `kLink` `kLink` INT UNSIGNED NOT NULL AUTO_INCREMENT, ENGINE=InnoDB;');
        $missingLanguageEntries = $this->getDB()->query(
            "SELECT tlink.*, tseo.* 
                FROM tlink
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                WHERE kLink NOT IN (SELECT kLink FROM tlinksprache)",
            ReturnType::ARRAY_OF_OBJECTS
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
                    $linkSprache->cName = $linkData->cName;
                    $linkSprache->cSeo  = $linkData->cName;
                } else {
                    $linkSprache->cSeo  = $match->cSeo;
                    $linkSprache->cName = $match->cName;
                }
                $linkSprache->cISOSprache = $sprache->cISO;
                $linkSprache->cTitle      = '';
                $linkSprache->cContent    = '';
                $linkSprache->cMetaTitle  = '';
                $linkSprache->cSeo        = Seo::getSeo($linkSprache->cSeo);
                $this->getDB()->insert('tlinksprache', $linkSprache);
            }
        }
        $missingSeo = $this->getDB()->query(
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
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($missingSeo as $item) {
            $oSeo           = new stdClass();
            $oSeo->cSeo     = Seo::checkSeo($item->cSeo);
            $oSeo->kKey     = $item->kLink;
            $oSeo->cKey     = 'kLink';
            $oSeo->kSprache = $item->kSprache;
            $this->getDB()->insert('tseo', $oSeo);
        }
        $missingLinkGroupLanguages = $this->getDB()->query(
            'SELECT tlinkgruppe.* 
                FROM tlinkgruppe
                LEFT JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkGruppe = tlinkgruppesprache.kLinkgruppe
                WHERE tlinkgruppesprache.kLinkgruppe IS NULL',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($missingLinkGroupLanguages as $missingLinkGroupLanguage) {
            foreach ($sprachen as $sprache) {
                $lang              = new stdClass();
                $lang->kLinkgruppe = $missingLinkGroupLanguage->kLinkgruppe;
                $lang->cName       = $missingLinkGroupLanguage->cName;
                $lang->cISOSprache = $sprache->cISO;
                $this->getDB()->insert('tlinkgruppesprache', $lang);
            }
        }
        $this->execute('CREATE TABLE `tlinkgroupassociations` (
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
              ) ENGINE=InnoDB COLLATE utf8_unicode_ci');
        $duplicates = $this->getDB()->query(
            'SELECT *
                FROM tlink
                GROUP BY klink
                HAVING COUNT(*) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $oldIDs     = [];
        foreach ($duplicates as $duplicate) {
            $oldParent = (int)$duplicate->kLink;
            $this->getDB()->delete('tlink', 'kLink', $duplicate->kLink);
            unset($duplicate->kLink);
            $newID              = $this->getDB()->insert('tlink', $duplicate);
            $oldIDs[$oldParent] = $newID;
            $this->getDB()->queryPrepared(
                'UPDATE tlink SET kVaterLink = :parent WHERE kVaterLink = :oldParent',
                ['parent' => $newID, 'oldParent' => $oldParent],
                ReturnType::DEFAULT
            );
        }
        foreach ($oldIDs as $oldID => $newID) {
            $this->getDB()->queryPrepared(
                'UPDATE tlink SET kVaterLink = :parent WHERE kVaterLink = :oldParent',
                ['parent' => $newID, 'oldParent' => $oldID],
                ReturnType::DEFAULT
            );
        }
        $this->execute('INSERT INTO tlinkgroupassociations (`linkID`, `linkGroupID`) 
            (SELECT tlink.kLink, tlink.kLinkgruppe 
                FROM tlink 
                JOIN tlinkgruppe
                    ON tlinkgruppe.kLinkgruppe = tlink.kLinkgruppe)');
        $externalLinks = $this->getDB()->query(
            "SELECT * FROM tlink
                WHERE tlink.cURL IS NOT NULL 
                  AND tlink.cURL != ''",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($externalLinks as $externalLink) {
            $this->getDB()->update(
                'tlinksprache',
                'kLink',
                $externalLink->kLink,
                (object)['cSeo' => $externalLink->cURL]
            );
        }
        $this->execute('ALTER TABLE `tlink` 
            DROP COLUMN `kLinkgruppe`,
            DROP COLUMN `cURL`,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`kLink`)');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `tlink` ADD COLUMN `kLinkgruppe` TINYINT(3) UNSIGNED NOT NULL;');
        $this->execute('ALTER TABLE `tlink` ADD COLUMN `cURL` VARCHAR(255) DEFAULT NULL;');
        $assoc = $this->getDB()->query(
            'SELECT linkID, linkGroupID 
                FROM tlinkgroupassociations',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($assoc as $item) {
            $this->getDB()->update(
                'tlink',
                'kLink',
                $item->linkID,
                (object)['kLinkgruppe' => $item->linkGroupID]
            );
        }
        $external = $this->getDB()->query(
            'SELECT tlink.kLink, tlinksprache.cSeo
                FROM tlink 
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                WHERE tlink.nLinkart = 2
                GROUP BY tlink.kLink',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($external as $item) {
            $this->getDB()->update(
                'tlink',
                'kLink',
                $item->kLink,
                (object)['cURL' => $item->cSeo]
            );
        }
        $this->execute('DROP TABLE tlinkgroupassociations');
        $this->execute('ALTER TABLE `tlinkgruppe` CHANGE COLUMN `kLinkgruppe` `kLinkgruppe` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->execute('ALTER TABLE tlink ADD INDEX `kLinkgruppe` (`kLinkgruppe`)');
        $this->execute('ALTER TABLE tlink CHANGE COLUMN `kLink` `kLink` INT NOT NULL');
        $this->execute('ALTER TABLE tlink DROP PRIMARY KEY');
        $this->execute('ALTER TABLE tlink ADD PRIMARY KEY (`kLink`,`kLinkgruppe`)');
        $this->execute('ALTER TABLE tlink CHANGE COLUMN `kLink` `kLink` INT NOT NULL AUTO_INCREMENT');
    }
}
