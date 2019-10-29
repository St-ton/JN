<?php
/**
 * remove_tkuponneukunde_backup
 *
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0.0
 * @author fp
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191029125000
 */
class Migration_20191029125000 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove tkuponneukunde_backup';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $tables = $this->execute("SHOW TABLES LIKE 'tkuponkunde_backup'");

        if (count($tables) > 0) {
            $backupData = $this->fetchOne(
                'SELECT COUNT(*) cntBack
                    FROM (SELECT tkuponkunde_backup.kKupon,
                            SHA2(LOWER(tkuponkunde_backup.cMail), 256) AS cMail
                            FROM tkuponkunde_backup
                            INNER JOIN tkupon
                                    ON tkupon.kKupon = tkuponkunde_backup.kKupon
                            WHERE tkuponkunde_backup.cMail != \'\'
                            GROUP BY tkuponkunde_backup.cMail, tkuponkunde_backup.kKupon) back
                    LEFT JOIN tkuponkunde ON tkuponkunde.kKupon = back.kKupon
                             AND tkuponkunde.cMail = back.cMail'
            );

            if ((int)$backupData->cntBack === 0) {
                $this->execute('DROP TABLE tkuponkunde_backup');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // nothing to do...
    }
}
