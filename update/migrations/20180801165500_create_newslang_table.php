<?php
/**
 * Create news lang table
 */

class Migration_20180801165500 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Create news language table';

    public function up()
    {
        $db = Shop::Container()->getDB();

        $this->execute("CREATE TABLE `tnewssprache` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `kNews` INT(10) UNSIGNED NOT NULL,
              `languageID` INT NOT NULL,
              `languageCode` VARCHAR(5) NOT NULL,
              `title` VARCHAR(255) DEFAULT NULL,
              `content` LONGTEXT,
              `preview` LONGTEXT,
              `metaTitle` VARCHAR(255) NOT NULL DEFAULT '',
              `metaKeywords` VARCHAR(255) NOT NULL DEFAULT '',
              `metaDescription` VARCHAR(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              CONSTRAINT `fk_newsID`
                  FOREIGN KEY (`kNews`)
                  REFERENCES `tnews` (`kNews`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        $this->execute("CREATE TABLE `tnewskategoriesprache` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `kNewsKategorie` INT(10) UNSIGNED NOT NULL,
              `languageID` INT NOT NULL,
              `languageCode` VARCHAR(5) NOT NULL,
              `name` VARCHAR(255) DEFAULT NULL,
              `description` TEXT,
              `metaTitle` VARCHAR(255) NOT NULL DEFAULT '',
              `metaDescription` VARCHAR(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              CONSTRAINT `fk_newscatID`
                  FOREIGN KEY (`kNewsKategorie`)
                  REFERENCES `tnewskategorie` (`kNewsKategorie`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        $allNewsEntries = $db->query('SELECT * FROM tnews', \DB\ReturnType::ARRAY_OF_OBJECTS);
        foreach ($allNewsEntries as $newsEntry) {
            $new                  = new stdClass();
            $new->kNews           = (int)$newsEntry->kNews;
            $new->languageID      = (int)$newsEntry->kSprache;
            $new->languageCode    = Shop::Lang()->_getIsoFromLangID($new->languageID)->cISO ?? 'ger';
            $new->title           = $newsEntry->cBetreff;
            $new->content         = $newsEntry->cText;
            $new->preview         = $newsEntry->cVorschauText;
            $new->metaTitle       = $newsEntry->cMetaTitle;
            $new->metaKeywords    = $newsEntry->cMetaKeywords;
            $new->metaDescription = $newsEntry->cMetaDescription;
            $db->insert('tnewssprache', $new);
        }
        $allNewsCategories = $db->query('SELECT * FROM tnewskategorie', \DB\ReturnType::ARRAY_OF_OBJECTS);
        foreach ($allNewsCategories as $newsCategory) {
            $new                  = new stdClass();
            $new->kNewsKategorie  = (int)$newsCategory->kNewsKategorie;
            $new->languageID      = (int)$newsCategory->kSprache;
            $new->languageCode    = Shop::Lang()->_getIsoFromLangID($new->languageID)->cISO ?? 'ger';
            $new->name            = $newsCategory->cName;
            $new->description     = $newsCategory->cBeschreibung;
            $new->metaTitle       = $newsCategory->cMetaTitle;
            $new->metaDescription = $newsCategory->cMetaDescription;
            $db->insert('tnewskategoriesprache', $new);
        }
        $this->execute('ALTER TABLE tnews 
            DROP COLUMN kSprache, 
            DROP COLUMN cBetreff, 
            DROP COLUMN cText, 
            DROP COLUMN cVorschauText, 
            DROP COLUMN cMetaDescription, 
            DROP COLUMN cMetaKeywords,
            DROP COLUMN cMetaTitle,
            DROP COLUMN cSeo'
        );
        $this->execute('ALTER TABLE tnewskategorie
            DROP COLUMN kSprache, 
            DROP COLUMN cSeo, 
            DROP COLUMN cName, 
            DROP COLUMN cBeschreibung, 
            DROP COLUMN cMetaTitle, 
            DROP COLUMN cMetaDescription'
        );
    }

    public function down()
    {
        $this->execute('DROP TABLE tnewssprache');
    }
}
