<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class ShopConfig
 * @package JTL\Backend\Wizard\Steps
 */
final class ShopConfig extends AbstractStep
{
    /**
     * ShopConfig constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('Onlineshop-Einstellungen'));
        $this->setID(1);

        $question = new Question($db);
        $question->setID(1);
        $question->setText(__('Names des Onlineshops'));
        $question->setValue(Shop::getSettingValue(\CONF_GLOBAL, 'global_shopname'));
        $question->setType(QuestionType::TEXT);
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('global_shopname', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(2);
        $question->setText(__('Master-Email-Adresse'));
        $question->setType(QuestionType::EMAIL);
        $question->setValue(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender_name'));
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('email_master_absender_name', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(3);
        $question->setIsRequired(false);
        $question->setValue(true);
        $question->setText(__('Sichere Voreinstellungen aktiv?'));
        $question->setType(QuestionType::BOOL);
        $question->setOnSave(function (QuestionInterface $question) {
            if ($question->getValue() === true) {
                $question->updateConfig('kaufabwicklung_ssl_nutzen', 'P');
                $question->updateConfig('email_smtp_verschluesselung', 'tls');
                $question->updateConfig('email_methode', 'smtp');
                $question->updateConfig('global_cookie_secure', 'Y');
                $question->updateConfig('global_cookie_httponly', 'Y');
            } else {
                $question->updateConfig('kaufabwicklung_ssl_nutzen', 'N');
                $question->updateConfig('email_smtp_verschluesselung', '');
//                $question->updateConfig('email_methode', 'mail');
                $question->updateConfig('global_cookie_secure', 'S');
                $question->updateConfig('global_cookie_httponly', 'S');
            }
        });
        $this->addQuestion($question);
    }
}
