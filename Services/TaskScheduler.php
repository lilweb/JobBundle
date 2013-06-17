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
     * @var Logger Le logger.
     */
    private $logger;

    /**
     * Constructeur pour l'injection du container. L'injection du logger ne peut pas se faire via le container.
     * Pour la partie jobs, on utilise un logger spécifique. Il y a donc un tag associé au logger.
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

        // Récupérer les jobs en attente d'execution par ordre de priorité
        $waitingTasks =  $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('LilwebJobBundle:TaskInfo')
            ->getWaitingTasks();

        // On cherche la premiere tache qui est executable
        foreach ($waitingTasks as $waitingTask) {

            // On vérifie si y a déjà une tache en cours de ce type la.
            $currentlyRunning = $this->container
                ->get('doctrine.orm.entity_manager')
                ->getRepository('LilwebJobBundle:TaskInfo')
                ->getNumberOfRunningTasks();

            // TODO Gestion de la parallélisation
            if ($currentlyRunning == 0) {
                $this->runTask($waitingTask);
                $this->logger->debug('There are no waiting tasks');

                break;
            }
        }

        $this->logger->debug('Fin de l\'ordonancement');
    }

    /**
     * Execution de la tache passé en paramère avec planification de la tache suivante si nécessaire.
     *
     * @param $taskInfo
     * @throws \Exception
     */
    private function runTask(TaskInfo $taskInfo)
    {
        // Logging
        $this->logger->debug('Traitement de la tache: '.$taskInfo->getName());

        // Récupération du job.
        $jobConfiguration = $this->container
            ->get('lilweb.job_resolver')
            ->getJob($taskInfo->getJobInfo()->getName());

        // Récuperation de l'information de la tache.
        $taskConfiguration = $jobConfiguration->getTask($taskInfo->getName());

        // Call the service responsible to execute the task
        if (!$this->container->has($taskConfiguration->getServiceId())) {
            throw new \Exception(
                sprintf('Unknown service "%s" for task "%s"', $taskConfiguration->getServiceId(), $taskConfiguration->getName())
            );
        }

        // When running a job and it is its first task being executed
        if ($taskInfo->getJobInfo()->getTaskInfos()->count() === 1) {
            $taskInfo->getJobInfo()->setLastStatusUpdateDate(new \DateTime());
        }

        // Execution de la tache
        try {
            $taskInfo->setStatus(TaskInfo::TASK_RUNNING);
            $this->container->get('doctrine.orm.entity_manager')->flush();

            $this->container->get($taskConfiguration->getServiceId())->execute($taskInfo);

            $taskInfo->setStatus(TaskInfo::TASK_OVER);
            $this->container->get('doctrine.orm.entity_manager')->flush();
        } catch (\Exception $e) {
            $taskInfo->setStatus(TaskInfo::TASK_FAIL);
            $this->container->get('doctrine.orm.entity_manager')->flush();

            $taskInfo->setInfoMsg($e->getMessage());
            $this->logger->err('Exception: ' . $e->getMessage());
        }

        // Logging
        $this->logger->debug('Fin du traitement, status: '.$taskInfo->getStatus());

        // On ne crée la tache suivante que si la tache a réussi
        if ($taskInfo->getStatus() == TaskInfo::TASK_OVER) {

            // Planification de la tache suivante
            $nextTaskName = $jobConfiguration->getNextTaskName($taskInfo->getName());
            if ($nextTaskName != null) {
                $this->logger->debug('Création de la tache suivante : ' . $nextTaskName);

                // Création de la nouvelle tache.
                $nextTask = new TaskInfo();
                $nextTask->setJobInfo($taskInfo->getJobInfo());
                $nextTask->setName($nextTaskName);
                $nextTask->setStatus(TaskInfo::TASK_WAITING);

                // On enregistre la nouvelle tache
                $this->container->get('doctrine.orm.entity_manager')->persist($nextTask);
            }
        }

        // Flush bdd
        $this->container->get('doctrine.orm.entity_manager')->flush();
    }
}
