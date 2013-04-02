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

use Lilweb\JobBundle\Entity\JobInfo;
use Lilwebs\JobBundle\Entity\TaskInfo;

/**
 * Commande qui va plannifier les jobs.
 */
class JobSchedulerCommand extends ContainerAwareCommand
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lilweb:job:scheduler')
            ->setDescription('Plannifie les jobs.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // For each job, create a JobInfo and TaskInfo row
        $this->logger = $this->getContainer()->get('logger');
        $this->logger->debug('Début de la plannification des jobs');
        $cpt = 0;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $jobs = $this->getContainer()->get('lilweb.job_resolver')->getJobs();

        foreach ($jobs as $job) {
            if ($job->isSchedulable()) {
                $this->logger->debug('Plannification du job: '.$job->getName());
                $task = $job->getTasks()->first();

                $taskInfo = new TaskInfo();
                $taskInfo->setName($task->getName());
                $taskInfo->setStatus(TaskInfo::TASK_WAITING);

                $jobInfo = new JobInfo();
                $jobInfo->setJobRunner('cron');
                $jobInfo->setName($job->getName());
                $jobInfo->addTaskInfo($taskInfo);
                $jobInfo->setLastStatusUpdateDate(new \DateTime());

                $em->persist($jobInfo);
                $cpt++;
            }
        }

        $em->flush();
        $this->logger->debug('Nombre de job planifiés: '.$cpt);
        $this->logger->debug('Fin de la plannification des jobs');
    }
}
