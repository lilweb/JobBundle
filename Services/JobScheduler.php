<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 12:01
 */
namespace Lilweb\JobBundle\Services;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

use Cron;

use Lilweb\JobBundle\Entity\TaskInfo;
use Lilweb\JobBundle\Entity\JobInfo;

/**
 * Planifie les jobs.
 */
class JobScheduler
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var JobResolver Le resolver des jobs
     */
    private $jobResolver;

    /**
     * @var EntityManager L'injection de l'entity manager.
     */
    private $em;

    /**
     * Injection des dependances.
     */
    public function __construct(Logger $logger, JobResolver $resolver, EntityManager $manager)
    {
        $this->logger = $logger;
        $this->jobResolver = $resolver;
        $this->em = $manager;
    }

    /**
     * Planifie tout les jobs.
     */
    public function checkAll()
    {
        // For each job, create a JobInfo and TaskInfo row
        $this->logger->debug('Début de la vérification du lancement des jobs');

        $cpt = 0;
        $jobs = $this->jobResolver->getJobs();

        foreach ($jobs as $job) {
            if ($job->getSchedule() != '') {
                $cron = Cron\CronExpression::factory($job->getSchedule());

                if ($cron->isDue()) {
                    $this->logger->debug('CRON match - plannification du job: '.$job->getName());
                    $task = $job->getTasks()->first();

                    $taskInfo = new TaskInfo();
                    $taskInfo->setName($task->getName());
                    $taskInfo->setStatus(TaskInfo::TASK_WAITING);

                    $jobInfo = new JobInfo();
                    $jobInfo->setJobRunner('cron');
                    $jobInfo->setName($job->getName());
                    $jobInfo->addTaskInfo($taskInfo);
                    $jobInfo->setLastStatusUpdateDate(new \DateTime());

                    $this->em->persist($jobInfo);
                    $cpt++;
                }
            }
        }

        $this->em->flush();
        $this->logger->debug('Nombre de job planifiés: '.$cpt);
        $this->logger->debug('Fin de la plannification des jobs');
    }
}