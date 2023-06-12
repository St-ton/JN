<?php declare(strict_types=1);
/**
 * add mandatory consent item
 *
 * @author dr
 * @created Thu, 01 Jun 2023 10:12:07 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230601101207
 */
class Migration_20230601101207 extends Migration implements IMigration
{
    protected $author = 'dr';
    protected $description = 'add mandatory consent item';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $id = $this->getDB()->query(
            "INSERT INTO tconsent (itemID, active) VALUES ('necessary', 0)",
            ReturnType::LAST_INSERTED_ID
        );

        $this->execute(
            'INSERT INTO tconsentlocalization 
                (consentID, languageID, privacyPolicy, description, purpose, name)
             VALUES (' . $id . ", 1, '',
                 'Technisch notwendige Cookies ermöglichen grundlegende Funktionen und sind für den einwandfreien   
                  Betrieb der Website erforderlich.',
                 '',
                 'Technisch notwendig')
         ");

        $this->execute(
            'INSERT INTO tconsentlocalization
                (consentID, languageID, privacyPolicy, description, purpose, name)
             VALUES (' . $id . ", 2, '',
                 'Strictly necessary cookies are those that enable the basic functions of a website. Without them, the
                  website will not work properly.',
                 '',
                 'Strictly necessary cookies')
         ");
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute("DELETE FROM tconsent WHERE itemID = 'necessary'");
    }
}
