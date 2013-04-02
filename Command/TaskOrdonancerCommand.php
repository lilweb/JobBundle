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

use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * Commande qui exécuter des taches une par une.
 */
class TaskOrdonancerCommand extends ContainerAwareCommand
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
            ->setName('lilweb:tasks:execute')
            ->setDescription('Execute une tache.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('logger');
        $this->logger->debug('Début de l\'ordonancement');

        $jobResolver = $this->getContainer()->get('lilweb.job_resolver');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tasks = $jobResolver->getTasks();

        // Go through all tasks to know whether or not one can be executed
        foreach ($tasks as $task) {
            if ($task->isExecutable($em)) {
                $this->logger->debug('Traitement de la tache: '.$task->getName());

                // Get task to execute
                $taskInfo = $em
                    ->getRepository('LilwebJobBundle:TaskInfo')
                    ->getTaskInfoToExecute($task->getName());
                $jobInfo = $taskInfo->getJobInfo();

                // When running a job and it is its first task being executed
                //     -> set the execution date of the job
                if ($jobInfo === null || $jobInfo->getTaskInfos()->count() === 1) {
                    $jobInfo->setExecutionDate(new \DateTime());
                }

                // Call the service responsible to execute the task
                if (!$this->getContainer()->has($task->getServiceId())) {
                    throw new \Exception('Unknown service "'.$task->getServiceId().'" for task "'.$task->getName().'"');
                }

                try {
                    $this->getContainer()->get($task->getServiceId())->execute($taskInfo);
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
