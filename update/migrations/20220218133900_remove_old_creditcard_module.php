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
        $this->execute(
            "DELETE tzahlungsart, tzahlungsartsprache, tversandartzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                LEFT JOIN tversandartzahlungsart
                    ON tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId = 'za_kreditkarte_jtl'"
        );
        $this->execute("UPDATE tzahlungsinfo SET cKartenNr = '', cCVV = ''");
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
