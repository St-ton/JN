<?php declare(strict_types=1);

namespace JTL\Console\Command\Mailtemplates;

use JTL\Console\Command\Command;
use JTL\Router\Controller\Backend\EmailTemplateController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResetCommand
 * @package JTL\Console\Command\Mailtemplates
 */
class ResetCommand extends Command
{
    protected static $defaultDescription = 'reset all mailtemplates';

    protected static $defaultName = 'mailtemplates:reset';

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = $this->getIO();
        $templates = $this->db->getObjects('SELECT DISTINCT kEmailVorlage FROM temailvorlagesprache');
        $count     = 0;
        foreach ($templates as $template) {
            EmailTemplateController::resetTemplate((int)$template->kEmailVorlage, $this->db);
            $count++;
        }
        $io->writeln('<info>' . $count . ' templates have been reset.</info>');

        return Command::SUCCESS;
    }
}
