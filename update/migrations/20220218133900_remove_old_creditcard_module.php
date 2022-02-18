<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220218133900
 */
class Migration_20220218133900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove old creditcard payment module';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $pmid = $this->getDB()->getSingleInt(
            'SELECT kZahlungsart
                FROM tzahlungsart
                WHERE cModulId = :pmid',
            'kZahlungsart',
            ['pmid' => 'za_kreditkarte_jtl']
        );
        if ($pmid > 0) {
            $this->getDB()->queryPrepared(
                'DELETE FROM
                    tzahlungsart
                    WHERE kZahlungsart = :pmid',
                ['pmid' => $pmid]
            );
            $this->getDB()->queryPrepared(
                'DELETE FROM
                    tzahlungsartsprache
                    WHERE kZahlungsart = :pmid',
                ['pmid' => $pmid]
            );
        }
        $this->removeConfig('zahlungsart_kreditkarte_max');
        $this->removeConfig('zahlungsart_kreditkarte_min');
        $this->removeConfig('zahlungsart_kreditkarte_min_bestellungen');
        $this->removeConfig('configgroup_100_credit_card');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
