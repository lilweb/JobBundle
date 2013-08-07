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
        $this->logger->debug('Début de l\'ordonancement');

        // Récupérer les jobs en attente d'execution par ordre de priorité
        $waitingTasks =  $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('LilwebJobBundle:TaskInfo')
            ->getWaitingTasks();

        $this->logger->debug('Taches en attente : ' .  count($waitingTasks));

        // On cherche la premiere tache qui est executable
        foreach ($waitingTasks as $waitingTask) {

            $this->logger->debug('Testing task : #' . $waitingTask->getId());

            // Check if all previous tasks are done
            $allDone = $this->container
                ->get('doctrine.orm.entity_manager')
                ->getRepository('LilwebJobBundle:TaskInfo')
                ->arePreviousTasksDone($waitingTask);

            if (!$allDone) {
                $this->logger->debug('Abandon task : #' . $waitingTask->getId() . " - previous tasks waiting");
            } else {

                // On vérifie si y a déjà une tache en cours de ce type la.
                $currentlyRunning = $this->container
                    ->get('doctrine.orm.entity_manager')
                    ->getRepository('LilwebJobBundle:TaskInfo')
                    ->getNumberOfRunningTasks($waitingTask->getName());

                if ($currentlyRunning == 0) {
                    $this->runTask($waitingTask);

                    break;
                }

                $this->logger->debug('Abandon task : #' . $waitingTask->getId() . " - currently running " . $waitingTask->getName());
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
        $this->container->get('doctrine.orm.entity_manager')->flush();
    }
}
