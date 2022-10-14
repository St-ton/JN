<?php declare(strict_types=1);
/**
 * Create index for newsletter
 *
 * @author fp
 * @created Fri, 14 Oct 2022 13:25:27 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221014132527
 */
class Migration_20221014132527 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Create index for newsletter';

    /**
     * @inheritDoc
     */
    public function up()
    {
        if ($this->db->getSingleObject("SHOW INDEX FROM toptin WHERE KEY_NAME = 'idx_cMail'") === null) {
            $this->db->executeQuery('ALTER TABLE toptin ADD INDEX idx_cMail (cMail)');
        }
        if ($this->db->getSingleObject(
            "SHOW INDEX FROM tnewsletterempfaengerhistory WHERE KEY_NAME = 'idx_cEmail_cAktion'"
        ) === null) {
            $this->db->executeQuery('ALTER TABLE tnewsletterempfaengerhistory ADD INDEX idx_cEmail_cAktion (cEmail, cAktion)');
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if ($this->db->getSingleObject("SHOW INDEX FROM toptin WHERE KEY_NAME = 'idx_cMail'") !== null) {
            $this->db->executeQuery('ALTER TABLE toptin DROP INDEX idx_cMail');
        }
        if ($this->db->getSingleObject(
                "SHOW INDEX FROM tnewsletterempfaengerhistory WHERE KEY_NAME = 'idx_cEmail_cAktion'"
            ) !== null) {
            $this->db->executeQuery('ALTER TABLE tnewsletterempfaengerhistory DROP INDEX idx_cEmail_cAktion');
        }
    }
}
