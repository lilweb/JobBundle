<?php
/**
 * Author: Michiel Missotten
 * Date: 07/05/13
 * Time: 16:04
 */
namespace Lilweb\JobBundle\Services;

use Doctrine\ORM\EntityManager;

use Lilweb\JobBundle\Entity\JobInfo;
use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * Class JobManager
 * @package Lilweb\JobBundle\Services
 */
class JobManager
{
    /**
     * @var JobResolver
     */
    private $jobResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Permets de lancer le job avec les informations/parametres.
     *
     * @param $jobName
     * @param array $params
     * @param string $launcher
     * @throws \Exception
     * @internal param \Lilweb\JobBundle\Services\Job $job
     */
    public function addJob($jobName, $params = array(), $launcher = '')
    {
        $job = $this->jobResolver->getJob($jobName);
        if ($job == null) {
            throw new \Exception("Le job '" . $jobName. "' n'a pu être trouvé!");
        }

        // Création du job dans un premier temps.
        $jobInfo = new JobInfo();
        $jobInfo->setName($job->getName());
        $jobInfo->setJobRunner($launcher);
        $jobInfo->setExecutionDate(new \DateTime());
        $jobInfo->setLastStatusUpdateDate(new \DateTime());

        // Injection des paramètres.
        foreach ($params as $paramName => $paramValue) {
            $jobInfo->setParameter($paramName, $paramValue);
        }

        $this->em->persist($jobInfo);

        // Création des taches du job
        $nbTaches = 0;
        foreach ($job->getTasks() as $task) {
            $taskInfo = new TaskInfo();
            $taskInfo->setName($task->getName());
            $taskInfo->setStatus(TaskInfo::TASK_WAITING);
            $taskInfo->setJobInfo($jobInfo);
            $taskInfo->setOrdre($nbTaches++);

            $this->em->persist($taskInfo);
        }

        $this->em->flush();
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @param \Lilweb\JobBundle\Services\JobResolver $jobResolver
     */
    public function setJobResolver($jobResolver)
    {
        $this->jobResolver = $jobResolver;
    }
}