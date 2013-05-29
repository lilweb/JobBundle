<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 12:01
 */
namespace Lilweb\JobBundle\Services;

use Monolog\Logger;

use Cron;

use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * Planifie les jobs.
 */
class CronScheduler
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
     * @var JobManager Le manager des jobs.
     */
    private $jobManager;

    /**
     * Injection des dependances.
     */
    public function __construct(Logger $logger, JobResolver $resolver, JobManager $jobManager)
    {
        $this->logger = $logger;
        $this->jobResolver = $resolver;
        $this->jobManager = $jobManager;
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
                    $this->jobManager->addJob($job->getName(), $job->getParams(), 'cron');

                    $cpt++;
                }
            }
        }

        $this->logger->debug('Nombre de job planifiés: '.$cpt);
        $this->logger->debug('Fin de la plannification des jobs');
    }
}