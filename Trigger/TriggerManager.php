<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 14:29
 */
namespace Lilweb\JobBundle\Trigger;

use Doctrine\Common\Util\Debug;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

use Lilweb\JobBundle\Entity\TaskInfo;
use Lilweb\JobBundle\Entity\JobInfo;
use Lilweb\JobBundle\Trigger\TriggerInterface;

/**
 * Manager des différents triggers.
 */
class TriggerManager
{
    /**
     * @var Container Le service container pour l'injection des triggers
     */
    private $container;

    /**
     * @var Logger Le logger.
     */
    private $logger;

    /**
     * Injection du service container.
     *
     * @param Container $container
     * @param \Monolog\Logger $logger
     */
    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Vérifie tout les triggers.
     */
    public function checkAll()
    {
        $resolver = $this->container->get('lilweb.job_resolver');
        $triggers = $resolver->getTriggers();

        foreach ($triggers as $trigger) {
            $triggerService = $this->container->get($trigger->getIdService());
            if (!$triggerService instanceof TriggerInterface) {
                throw new \Exception("Trigger have to implement TriggerInterface!");
            }

            if ($triggerService->checkCondition()) {
                $job = $resolver->getJob($trigger->getJobName());

                $this->logger->debug('Plannification du job: '.$job->getName() + " par le trigger : " + $trigger->getName());
                $task = $job->getTasks()->first();

                $taskInfo = new TaskInfo();
                $taskInfo->setName($task->getName());
                $taskInfo->setStatus(TaskInfo::TASK_WAITING);

                $jobInfo = new JobInfo();
                $jobInfo->setJobRunner('cron');
                $jobInfo->setName($job->getName());
                $jobInfo->addParameters($triggerService->getParameters());
                $jobInfo->addTaskInfo($taskInfo);
                $jobInfo->setLastStatusUpdateDate(new \DateTime());

                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($jobInfo);
                $em->flush();
            }
        }
    }
}