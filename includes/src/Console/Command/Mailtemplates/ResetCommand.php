<?php declare(strict_types=1);

namespace JTL\Console\Command\Mailtemplates;

use JTL\Console\Command\Command;
use JTL\Router\Controller\Backend\EmailTemplateController;
use JTL\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResetCommand
 * @package JTL\Console\Command\Mailtemplates
 */
class ResetCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('mailtemplates:reset')
            ->setDescription('reset all mailtemplates');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db        = Shop::Container()->getDB();
        $io        = $this->getIO();
        $templates = $db->getObjects('SELECT DISTINCT kEmailVorlage FROM temailvorlagesprache');
        $count     = 0;
        foreach ($templates as $template) {
            EmailTemplateController::resetTemplate((int)$template->kEmailVorlage, $db);
            $count++;
        }
        $io->writeln('<info>' . $count. ' templates have been reset.</info>');

        return Command::SUCCESS;
    }
}
