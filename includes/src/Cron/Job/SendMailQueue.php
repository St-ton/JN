<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Mail\Hydrator\DefaultsHydrator;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Validator\MailValidator;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use SmartyException;

/**
 * Class Dummy
 * @package JTL\Cron\Job
 */
final class SendMailQueue extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data): SendMailQueue
    {
        parent::hydrate($data);

        return $this;
    }

    /**
     * @inheritdoc
     * @throws SmartyException
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $maxJobLength = \ceil(((int)\ini_get('max_execution_time')) / 2);
        $jobStarted   = \time();

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
        while (\time() < ($jobStarted + $maxJobLength)) {
            $mailer->sendQueuedMails();
        }

        return $this;
    }
}
