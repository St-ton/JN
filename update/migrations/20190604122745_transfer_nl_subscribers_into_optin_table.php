<?php
/**
 * transfer nl subscribers into optin table
 *
 * @author cr
 * @created Tue, 04 Jun 2019 12:27:45 +0200
 */

use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190604122745
 */
class Migration_20190604122745 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Transfer NL subscribers into optin table';

    public function up()
    {
        $nlSubscribers = $this->fetchAll('SELECT * FROM tnewsletterempfaenger WHERE nAktiv = 1');
        foreach ($nlSubscribers as $subscriber) {
            $refData = (new OptinRefData())
                ->setOptinClass(OptinNewsletter::class)
                ->setLanguageID(Shop::getLanguageID())
                ->setSalutation($subscriber->cAnrede)
                ->setFirstName($subscriber->cVorname)
                ->setLastName($subscriber->cNachname)
                ->setEmail($subscriber->cEmail)
                ->setCustomerID($subscriber->kKunde);

            $this->execute("
               INSERT INTO toptin(
                   kOptinCode,
                   kOptinClass,
                   cMail,
                   cRefData,
                   dCreated,
                   dActivated
               )
               VALUES(
                   '".$subscriber->cOptCode."',
                   '".quotemeta(OptinNewsletter::class)."',
                   '".$subscriber->cEmail."',
                   '".quotemeta(serialize($refData))."',
                   '".$subscriber->dEingetragen."',
                   NOW()
               )
               ON DUPLICATE KEY UPDATE
                    kOptinClass = kOptinClass,
                    cMail = cMail,
                    cRefData = cRefdata,
                    dCreated = NOW(),
                    dActivated = NOW()
           ");
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM toptin WHERE kOptinClass = '" . quotemeta(OptinNewsletter::class) . "'");
    }
}
