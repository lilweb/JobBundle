<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 11:50
 */
namespace Lilweb\JobBundle\Services;

use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

use Lilweb\JobBundle\Entity\TaskInfo;
use Lilweb\JobBundle\Entity\JobInfo;

/**
 * C'est cette classe qui s'occupe de l'ordonnancement des différentes taches.
 */
class TaskScheduler
{
    /**
     * @var Container Le service container.
     */
    private $container;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructeur pour l'injection du container.
     */
    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Lance l'execution.
     */
    public function execute()
    {
        $this->logger = $this->container->get('logger');
        $this->logger->debug('Début de l\'ordonancement');

        $jobResolver = $this->container->get('lilweb.job_resolver');
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Check the triggers & CRON expressions
        $this->container->get('lilweb.trigger_manager')->checkAll();
        $this->container->get('lilweb.cron_scheduler')->checkAll();

        // Go through all tasks to know whether or not one can be executed
        $tasks = $jobResolver->getTasks();
        foreach ($tasks as $task) {
            if ($task->isExecutable($em)) {
                $this->logger->debug('Traitement de la tache: '.$task->getName());

                // Get task to execute
                $taskInfo = $em
                    ->getRepository('LilwebJobBundle:TaskInfo')
                    ->getTaskInfoToExecute($task->getName());
                $jobInfo = $taskInfo->getJobInfo();

                // When running a job and it is its first task being executed
                if ($jobInfo === null || $jobInfo->getTaskInfos()->count() === 1) {
                    $jobInfo->setLastStatusUpdateDate(new \DateTime());
                }

                // Call the service responsible to execute the task
                if (!$this->container->has($task->getServiceId())) {
                    throw new \Exception('Unknown service "'.$task->getServiceId().'" for task "'.$task->getName().'"');
                }

                try {
                    $this->container->get($task->getServiceId())->execute($taskInfo);
                } catch (\Exception $e) {
                    $taskInfo->setStatus(TaskInfo::TASK_FAIL);
                    $taskInfo->setInfoMsg($e->getMessage());
                    $this->logger->err('Exception: ' . $e->getMessage());
                }

                $this->logger->debug('Fin du traitement, status: '.$taskInfo->getStatus());

                // When running a job, add the next task if possible
                if ($jobInfo !== null && $taskInfo->isTaskOver()) {
                    // Check if it was not the last task to execute for the job
                    $job = $jobResolver->getJob($jobInfo->getName());
                    $name = $job->getNextTaskName($task->getName());

                    if ($name !== null) {
                        $this->logger->debug('Création d\'une nouvelle tache: '.$task->getName());

                        $nextTaskInfo = new TaskInfo();
                        $nextTaskInfo->setName($name);
                        $nextTaskInfo->setStatus(TaskInfo::TASK_WAITING);

                        $jobInfo->addTaskInfo($nextTaskInfo);
                    }
                }

                $em->persist($jobInfo);
                $em->flush();

                break;
            }
        }

        $this->logger->debug('Fin de l\'ordonancement');
    }
}