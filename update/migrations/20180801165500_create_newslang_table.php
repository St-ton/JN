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
        $db             = Shop::Container()->getDB();
        $allNewsEntries = $db->query('SELECT * FROM tnews', \DB\ReturnType::ARRAY_OF_OBJECTS);

        $this->execute("CREATE TABLE `tnewssprache` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `kNews` INT(10) UNSIGNED NOT NULL,
              `languageID` INT NOT NULL,
              `languageCode` VARCHAR(5) NOT NULL,
              `title` VARCHAR(255) DEFAULT NULL,
              `content` LONGTEXT,
              `preview` LONGTEXT,
              `previewImage` VARCHAR(255) NOT NULL DEFAULT '',
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

        foreach ($allNewsEntries as $newsEntry) {
            $new                  = new stdClass();
            $new->kNews           = (int)$newsEntry->kNews;
            $new->languageID      = (int)$newsEntry->kSprache;
            $new->languageCode    = Shop::Lang()->_getIsoFromLangID($new->languageID)->cISO ?? 'ger';
            $new->title           = $newsEntry->cBetreff;
            $new->content         = $newsEntry->cText;
            $new->preview         = $newsEntry->cVorschauText;
            $new->previewImage    = $newsEntry->cPreviewImage;
            $new->metaTitle       = $newsEntry->cMetaTitle;
            $new->metaKeywords    = $newsEntry->cMetaKeywords;
            $new->metaDescription = $newsEntry->cMetaDescription;
            $db->insert('tnewssprache', $new);
        }

    }

    public function down()
    {
        $this->execute('DROP TABLE tnewssprache');
    }
}
