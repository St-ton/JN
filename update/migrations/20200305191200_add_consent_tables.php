<?php declare(strict_types=1);

/**
 * @author fm
 * @created Thu, 05 Mar 2020 19:12:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200305191200
 */
class Migration_20200305191200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'add consent tables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `tconsent` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `itemID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `pluginID` int(11) NOT NULL DEFAULT 0,
          `active` tinyint(4) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        );
        $this->execute('CREATE TABLE IF NOT EXISTS `tconsentlocalization` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `consentID` int(11) NOT NULL,
          `languageID` int(11) NOT NULL,
          `tos` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `description` text COLLATE utf8_unicode_ci NOT NULL,
          `purpose` text COLLATE utf8_unicode_ci NOT NULL,
          `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`),
          KEY `fk_consent_id` (`consentID`),
          CONSTRAINT `fk_consent_id` FOREIGN KEY (`consentID`) REFERENCES `tconsent` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS tconsentlocalization');
        $this->execute('DROP TABLE IF EXISTS tconsent');
    }
}
