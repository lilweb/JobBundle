<?php
/**
 * User: michiel
 * Date: 19/06/13
 * Time: 17:08
 */
namespace Lilweb\JobBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository de gestion des entités.
 */
class JobInfoRepository extends EntityRepository
{
    /**
     * Recherche les jobs_infos pour cette journée.
     *
     * @param $year
     * @param $month
     * @param $day
     *
     * @return array
     */
    public function findByDay($year, $month, $day)
    {
        $dateDebut = \DateTime::createFromFormat('U', mktime(0, 0, 0, $month, $day, $year));
        $dateFin = \DateTime::createFromFormat('U', mktime(23, 59, 59, $month, $day, $year));

        return $this->createQueryBuilder('j')
            ->where('j.creationDate between :dateDebut and :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('j.creationDate', 'desc')
            ->getQuery()
            ->getResult();
    }
}
