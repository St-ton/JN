<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

class Migration_20220907115255 extends Migration implements IMigration
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function up()
    {
        $this->removeLocalization('incorrectEmail', 'global');
        $this->removeLocalization('newPasswortWasGenerated', 'forgot password');
        $this->setLocalization('ger', 'forgot password', 'newPasswordWasGenerated', 'Wir haben eine E-Mail mit einem Link zum Zurücksetzen Ihres Passworts an %s gesendet. Wenn Sie keine E-Mail erhalten haben: Überprüfen Sie bitte, ob sich die E-Mail in Ihrem Spamordner befindet oder ob der von Ihnen eingegebene Benutzername womöglich falsch ist.');
        $this->setLocalization('eng', 'forgot password', 'newPasswordWasGenerated', 'We have sent an email with a link to reset your password to %s. If you haven’t received the email: Please check whether the email went into the spam folder or whether you entered the wrong user name.');
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function down()
    {
        $this->setLocalization('ger', 'global', 'incorrectEmail', 'Es existiert kein Kunde mit der angegebenen E-Mail-Adresse. Bitte versuchen Sie es erneut.');
        $this->setLocalization('eng', 'global', 'incorrectEmail', 'There is no customer with the specified email address. Please try again.');
        $this->setLocalization('ger', 'forgot password', 'newPasswortWasGenerated', 'In wenigen Augenblicken erhalten Sie eine E-Mail mit weiteren Schritten zum Zurücksetzen Ihres Passworts.');
        $this->setLocalization('eng', 'forgot password', 'newPasswortWasGenerated', 'In a few minutes you will receive an email with further steps to reset your password.');
        $this->removeLocalization('newPasswordWasGenerated', 'forgot password');
    }
}
