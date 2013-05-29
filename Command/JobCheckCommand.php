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

/**
 * Commande qui va plannifier les jobs.
 */
class JobCheckCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lilweb:job:check')
            ->setDescription('Lance les vérifications pour savoir si des jobs doivent être lancées. (cron et triggers)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('lilweb.trigger_manager')->checkAll();
        $this->getContainer()->get('lilweb.cron_scheduler')->checkAll();
    }
}
