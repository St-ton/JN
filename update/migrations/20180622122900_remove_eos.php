<?php
/**
 * remove eos payment method
 *
 * @author fm
 * @created Fri, 22 Jun 2018 12:29:00 +0200
 */

/**
 * Class Migration_20180622122900
 */
class Migration_20180622122900 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Remove EOS payment method';

    public function up()
    {
        $this->execute("DELETE FROM teinstellungen WHERE cModulId LIKE 'za_eos_%'");
        $this->execute("DELETE FROM teinstellungenconf WHERE cModulId LIKE 'za_eos_%'");
        $this->execute("DELETE FROM tzahlungsart WHERE cModulId LIKE 'za_eos_%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'eos%'");
    }

    public function down()
    {
    }
}
