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
        return $this->createQueryBuilder('ti')
            ->where('ti.status = :status')
            ->andWhere('ti.ordre = (SELECT count(ti2.id) FROM Lilweb\JobBundle\Entity\TaskInfo ti2 where ti.jobInfo = ti2.jobInfo AND ti2.status = 2 AND ti2.ordre < ti.ordre)')
            ->setParameter('status', TaskInfo::TASK_WAITING)
            ->orderBy('ti.id', 'ASC')
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
            ->setParameter('taskStatus', TaskInfo::TASK_RUNNING);

        if ($taskName != null) {
            $qb
                ->andWhere('ti.name = :taskName')
                ->setParameter('taskName', $taskName);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
