<?php
/**
 * Add backend logging option
 */

use Monolog\Logger;

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
            'listbox',
            1503,
            (object) [
                'cBeschreibung' => 'Sollen Backend-Loginversuche geloggt werden?',
                'inputOptions'  => [
                    AdminLoginConfig::CONFIG_DB   => 'in Datenbank',
                    AdminLoginConfig::CONFIG_FILE => 'in Textdatei'
                ]
            ]
        );
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::ALERT . " WHERE nLevel = 1");
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::INFO . " WHERE nLevel = 2");
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::DEBUG . " WHERE nLevel = 4");
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->removeConfig('admin_login_logger_mode');
    }
}
