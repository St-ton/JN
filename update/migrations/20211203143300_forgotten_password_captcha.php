<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211203143300
 */
class Migration_20211203143300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = /** @lang text */ 'Create option for spam protection on reset password page';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'configgroup_' . CONF_KUNDEN . '_forgot_password',
            'Passwort vergessen',
            CONF_KUNDEN,
            'Passwort vergessen',
            null,
            600,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'forgot_password_captcha',
            'N',
            CONF_KUNDEN,
            'Spamschutz aktivieren',
            'selectbox',
            601,
            (object)[
                'cBeschreibung' => '',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('configgroup_' . CONF_KUNDEN . '_forgot_password');
        $this->removeConfig('forgot_password_captcha');
    }
}
