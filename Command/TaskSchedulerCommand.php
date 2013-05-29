<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * Commande qui exécuter des taches une par une.
 */
class TaskSchedulerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lilweb:job:execute')
            ->setDescription("Lance la prochaine tache en attente")
            ->addOption('with-checks', null, InputOption::VALUE_NONE, 'If set, it will also check cron expressions and the triggers.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // On vérifie si il faut aussi executer les checks.
        if ($input->getOption('with-checks')) {
            $this->getContainer()->get('lilweb.trigger_manager')->checkAll();
            $this->getContainer()->get('lilweb.cron_scheduler')->checkAll();
        }

        $this->getContainer()->get('lilweb.task_scheduler')->execute();
    }
}
