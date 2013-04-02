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
            ->orderBy('ti.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
