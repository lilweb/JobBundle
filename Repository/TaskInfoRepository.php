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
            ->join('ti.jobInfo', 'j')
            ->where('ti.status = :status')
            ->setParameter('status', TaskInfo::TASK_WAITING)
            ->orderBy('j.creationDate')
            ->addOrderBy('ti.ordre')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne vrai si les taches précédentes de ce job ont été faites.
     *
     * @return boolean
     */
    public function arePreviousTasksDone($taskInfo)
    {
        $nb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as nb')
            ->where('t.jobInfo = :jobInfo')
            ->andWhere('(t.status = :OVER OR t.status = :SKIPPED)')
            ->andWhere('t.ordre < :ordre')
            ->setParameter('OVER', TaskInfo::TASK_OVER)
            ->setParameter('SKIPPED', TaskInfo::TASK_SKIPPED)
            ->setParameter('ordre', $taskInfo->getOrdre())
            ->setParameter('jobInfo', $taskInfo->getJobInfo()->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return ($nb == $taskInfo->getOrdre());
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
