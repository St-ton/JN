<?php
/**
 * Add backend logging option
 */

/**
 * Class Migration_20180319103900
 */
class Migration_20180319103900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add backend logging option';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'admin_login_logger_mode',
            '1',
            CONF_GLOBAL,
            'Adminloginversuche loggen?',
            'selectbox',
            1503,
            (object) [
                'cBeschreibung' => 'Sollen Backend-Loginversuche geloggt werden?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, in Datenbank',
                    '2' => 'Ja, in Textdatei',
                    '3' => 'Ja, in Datenbank und Textdatei'
                ]
            ]
        );
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->removeConfig('admin_login_logger_mode');
    }
}
