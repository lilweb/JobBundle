<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * TaskInfoRepository.
 */
class TaskInfoRepository extends EntityRepository
{
    /**
     * Gets the statistics of a given task (the number of execution, waiting ... for this task).
     *
     * @param $taskName
     * @return integer
     */
    public function getStatisticsForTask($taskName)
    {
        return $this
            ->createQueryBuilder('ti')
            ->select('ti.status as status, COUNT(ti.id) as nb')
            ->where('ti.name = :taskName')
            ->setParameter('taskName', $taskName)
            ->groupBy('ti.status')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Retourne la liste des taches en attente par ordre de priorité.
     *
     * @return mixed
     */
    public function getWaitingTasks()
    {
        return $this
            ->createQueryBuilder('ti')
            ->where('ti.status = :status')
            ->setParameter('status', TaskInfo::TASK_WAITING)
            ->orderBy('ti.executionDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre de taches de ce type en cours.
     *
     * @param $taskName
     *
     * @return integer
     */
    public function getNumberOfRunningTasks($taskName = null)
    {
        $qb = $this->createQueryBuilder('ti')
            ->select('COUNT(ti.id) as nb')
            ->where('ti.status = :taskStatus')
            ->setParameter('taskStatus', TaskInfo::TASK_WAITING);

        if ($taskName != null) {
            $qb
                ->andWhere('ti.name = :taskName')
                ->setParameter('taskName', $taskName);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Gets a taskInfo object that is waiting to be executed.
     *
     * @param string $taskName The name of the task.
     *
     * @return \Lilweb\JobBundle\Entity\TaskInfo|null
     */
    public function getTaskInfoToExecute($taskName)
    {
        return $this
            ->createQueryBuilder('ti')
            ->where('ti.name = :taskName')
            ->andWhere('ti.status = :status')
            ->setParameter('taskName', $taskName)
            ->setParameter('status', TaskInfo::TASK_WAITING)
            ->orderBy('ti.executionDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
