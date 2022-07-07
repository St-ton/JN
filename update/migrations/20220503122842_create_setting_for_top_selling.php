<?php declare(strict_types=1);
/**
 * Create setting for top selling
 *
 * @author fp
 * @created Tue, 03 May 2022 12:28:42 +0200
 */

use JTL\Cron\JobInterface;
use JTL\Cron\Type;
use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Cron\Admin\Controller;

/**
 * Class Migration_20220503122842
 */
class Migration_20220503122842 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Create setting for top selling';

    /**
     * @inheritDoc
     */
    public function up()
    {
        if ($this->fetchOne("SHOW INDEX FROM tbestellung WHERE KEY_NAME = 'idx_dErstellt_WK'")) {
            $this->execute('DROP INDEX idx_dErstellt_WK ON tbestellung');
        }
        $this->execute('ALTER TABLE tbestellung ADD KEY idx_dErstellt_WK (dErstellt, cStatus, kWarenkorb)');
        $this->setConfig(
            'global_bestseller_tage',
            90,
            1,
            'Maximale Anzahl Tage für Bestseller',
            'number',
            286,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, welcher zurückliegende Zeitraum (in Tagen) '
                    . 'für die Ermittlung der Bestseller berücksichtigt werden soll.',
            ]
        );

        /** @var Controller $controller */
        $controller = Shop::Container()->get(Controller::class);
        $controller->addQueueEntry([
            'type'      => Type::TOPSELLER,
            'frequency' => '24',
            'time'      => '01:00',
            'date'      => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        /** @var Controller $controller */
        $controller = Shop::Container()->get(Controller::class);
        $crons      = array_filter($controller->getJobs(), static function (JobInterface $job) {
            return $job->getType() === Type::TOPSELLER;
        });
        if (count($crons) > 0) {
            $cron = array_shift($crons);
            $controller->deleteQueueEntry($cron->getCronID());
        }

        $this->removeConfig('global_bestseller_tage');
        if ($this->fetchOne("SHOW INDEX FROM tbestellung WHERE KEY_NAME = 'idx_dErstellt_WK'")) {
            $this->execute('DROP INDEX idx_dErstellt_WK ON tbestellung');
        }
    }
}
