<?php
/**
 * Add password check lang
 *
 * @author mh
 * @created Thu, 4 July 2019 15:49:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190704154900
 */
class Migration_20190704154900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add password check lang';

    public function up()
    {
        $this->setLocalization('ger', 'login', 'passwordTooShort', 'Das Passwort muss aus mindestens %s zeichen bestehen.');
        $this->setLocalization('eng', 'login', 'passwordTooShort', 'The password should have at least %s characters.');
        $this->setLocalization('ger', 'login', 'passwordIsWeak', 'Schwach; Versuchen Sie Buchstaben und Zahlen zu kombinieren.');
        $this->setLocalization('eng', 'login', 'passwordIsWeak', 'Weak; try combining letters & numbers.');
        $this->setLocalization('ger', 'login', 'passwordIsMedium', 'Medium; Versuchen Sie Spezialzeichen.');
        $this->setLocalization('eng', 'login', 'passwordIsMedium', 'Medium; try using special characters.');
        $this->setLocalization('ger', 'login', 'passwordIsStrong', 'Starkes Passwort.');
        $this->setLocalization('eng', 'login', 'passwordIsStrong', 'Strong password.');
        $this->setLocalization('ger', 'login', 'passwordhasUsername', 'Das Passwort enthält den Nutzernamen.');
        $this->setLocalization('eng', 'login', 'passwordhasUsername', 'The password contains your username.');
        $this->setLocalization('ger', 'login', 'typeYourPassword', 'Geben Sie ein Passwort ein.');
        $this->setLocalization('eng', 'login', 'typeYourPassword', 'Type your password.');

        $this->execute(
            "UPDATE teinstellungen
                SET cWert = GREATEST(cWert, 8)
                WHERE cName = 'kundenregistrierung_passwortlaenge'"
        );
    }

    public function down()
    {
        $this->removeLocalization('passwordTooShort');
        $this->removeLocalization('passwordIsWeak');
        $this->removeLocalization('passwordIsMedium');
        $this->removeLocalization('passwordIsStrong');
        $this->removeLocalization('passwordhasUsername');
        $this->removeLocalization('typeYourPassword');
    }
}
