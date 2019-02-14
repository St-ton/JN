<?php
/**
 * split cron intervals
 *
 * @author fm
 * @created Thu, 05 Jun 2018 12:20:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;

/**
 * Class Migration_20180705122000
 */
class Migration_20180705122000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Split cron intervals';

    public function up()
    {
        $statusMail = Shop::Container()->getDB()->query('SELECT * FROM tstatusemail', ReturnType::SINGLE_OBJECT);
        $updates    = [];
        if ($statusMail !== false) {
            foreach (Text::parseSSK($statusMail->cIntervall) as $interval) {
                $interval       = (int)$interval;
                $upd            = new stdClass();
                $upd->cEmail    = $statusMail->cEmail;
                $upd->nInterval = $interval;
                $upd->cInhalt   = $statusMail->cInhalt;
                $upd->nAktiv    = $statusMail->nAktiv;
                if ($interval === 1) {
                    $upd->dLastSent = $statusMail->dLetzterTagesVersand;
                } elseif ($interval === 7) {
                    $upd->dLastSent = $statusMail->dLetzterWochenVersand;
                } else {
                    $upd->dLastSent = $statusMail->dLetzterMonatsVersand;
                }
                $updates[] = $upd;
            }
        }
        $this->execute('TRUNCATE TABLE `tstatusemail`');
        $this->execute('ALTER TABLE `tstatusemail` 
            DROP COLUMN `cIntervall`,
            DROP COLUMN `dLetzterTagesVersand`,
            DROP COLUMN `dLetzterWochenVersand`,
            DROP COLUMN `dLetzterMonatsVersand`,
            ADD COLUMN `nInterval` INT NOT NULL,
            ADD COLUMN `dLastSent` DATETIME NULL DEFAULT NULL,
            ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT,
            ADD PRIMARY KEY (`id`)');
        foreach ($updates as $update) {
            Shop::Container()->getDB()->insert('tstatusemail', $update);
        }
    }

    public function down()
    {
    }
}
