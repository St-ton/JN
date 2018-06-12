<?php
/**
 * Hook interface for captcha
 *
 * @author fp
 * @created Wed, 23 May 2018 09:27:32 +0200
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180523092732 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Hook interface for captcha';

    private $settingsMap = [
        'global_google_recaptcha_public'  => 'jtl_google_recaptcha_sitekey',
        'global_google_recaptcha_private' => 'jtl_google_recaptcha_secretkey',
    ];

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $spamMethod = (int)Shop::getConfigValue(CONF_GLOBAL, 'anti_spam_method');

        $this->setConfig('anti_spam_method', $spamMethod > 0 ? 'Y' : 'N', CONF_GLOBAL, 'Spamschutz-Methode', 'selectbox', 520, (object)[
            'cBeschreibung' => 'Soll der Spamschutz global aktiviert / deaktiviert werden?',
            'inputOptions'  => [
                'Y' => 'aktiviert',
                'N' => 'deaktiviert',
            ]
        ], true);

        $this->removeConfig('global_google_recaptcha_public');
        $this->removeConfig('global_google_recaptcha_private');

        $this->setLocalization('ger', 'global', 'captcha_enter_code', 'Sicherheitscode eingeben');
        $this->setLocalization('ger', 'global', 'captcha_reload', 'Captcha neu laden');
        $this->setLocalization('eng', 'global', 'captcha_enter_code', 'Enter security code');
        $this->setLocalization('eng', 'global', 'captcha_reload', 'Reload captcha');

        Shop::Cache()->flushTags(CACHING_GROUP_OPTION);
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $spamMethod = Shop::getConfigValue(CONF_GLOBAL, 'anti_spam_method');

        $this->setConfig('anti_spam_method', $spamMethod === 'Y' ? '7' : 'N', CONF_GLOBAL, 'Spamschutz-Methode', 'selectbox', 520, (object)[
            'cBeschreibung' => 'Die Art des Spamschutzes',
            'inputOptions'  => [
                'N' => 'keine',
                '1' => 'Captcha Sicherheitsstufe 1 (im Evo reCaptcha)',
                '2' => 'Captcha Sicherheitsstufe 2 (im Evo reCaptcha)',
                '3' => 'Captcha Sicherheitsstufe 3 (im Evo reCaptcha)',
                '4' => 'Rechenaufgabe (im Evo reCaptcha)',
                '5' => 'unsichtbarer Sicherheitstoken (im Evo reCaptcha)',
                '7' => 'Google reCaptcha',
            ]
        ], true);
        $this->setConfig('global_google_recaptcha_public', '', CONF_GLOBAL, 'Google reCAPTCHA Websiteschlüssel', 'text', 522, (object)[
            'cBeschreibung' => 'Sie müssen Ihre Domain auf https://www.google.com/recaptcha registrieren. Anschließend erhalten Sie von Google Ihren Website- und Geheimen Schlüssel.',
        ]);
        $this->setConfig('global_google_recaptcha_private', '', CONF_GLOBAL, 'Google reCAPTCHA Geheimer Schlüssel', 'text', 523, (object)[
            'cBeschreibung' => 'Sie müssen Ihre Domain auf https://www.google.com/recaptcha registrieren. Anschließend erhalten Sie von Google Ihren Website- und Geheimen Schlüssel.',
        ]);

        $this->removeLocalization('captcha_enter_code');
        $this->removeLocalization('captcha_reload');

        Shop::Cache()->flushTags(CACHING_GROUP_OPTION);
    }
}
