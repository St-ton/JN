<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Mail\Hydrator\DefaultsHydrator;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Validator\MailValidator;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;

/**
 * Class Dummy
 * @package JTL\Cron\Job
 */
final class SendMailQueue extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        $settings  = Shopsetting::getInstance();
        $smarty    = new SmartyRenderer(new MailSmarty($this->db));
        $hydrator  = new DefaultsHydrator($smarty->getSmarty(), $this->db, $settings);
        $validator = new MailValidator($this->db, $settings->getAll());
        $mailer    = new Mailer(
            $hydrator,
            $smarty,
            $settings,
            $validator
        );
        $mailer->sendQueuedMails();


        return $this;
    }
}
