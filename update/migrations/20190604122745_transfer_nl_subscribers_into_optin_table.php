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
use JTL\Shop;

/**
 * Class Migration_20190604122745
 */
class Migration_20190604122745 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Transfer NL subscribers into optin table';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $nlSubscribers = $this->getDB()->getObjects('SELECT * FROM tnewsletterempfaenger WHERE nAktiv = 1');
        foreach ($nlSubscribers as $subscriber) {
            $languageID = (int)($nlSubscribers->kSprache ?? 0);
            if ($languageID === 0) {
                $languageID = Shop::getLanguageID();
            }

            $refData = (new OptinRefData())
                ->setOptinClass(OptinNewsletter::class)
                ->setLanguageID($languageID)
                ->setSalutation($subscriber->cAnrede ?? '')
                ->setFirstName($subscriber->cVorname ?? '')
                ->setLastName($subscriber->cNachname ?? '')
                ->setEmail($subscriber->cEmail ?? '')
                ->setCustomerID((int)$subscriber->kKunde);

            $this->getDB()->queryPrepared(
                'INSERT INTO toptin(
                    kOptinCode,
                    kOptinClass,
                    cMail,
                    cRefData,
                    dCreated,
                    dActivated
                )
                VALUES(
                    :optCode,
                    :optinNewsletter,
                    :email,
                    :refData,
                    :eingetragen,
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                     kOptinClass = kOptinClass,
                     cMail = cMail,
                     cRefData = cRefdata,
                     dCreated = NOW(),
                     dActivated = NOW()',
                [
                    'optCode'         => $subscriber->cOptCode,
                    'optinNewsletter' => quotemeta(OptinNewsletter::class),
                    'email'           => $subscriber->cEmail,
                    'refData'         => quotemeta(serialize($refData)),
                    'eingetragen'     => $subscriber->dEingetragen
                ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("DELETE FROM toptin WHERE kOptinClass = '" . quotemeta(OptinNewsletter::class) . "'");
    }
}
