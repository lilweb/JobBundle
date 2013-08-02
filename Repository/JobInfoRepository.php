<?php
/**
 * User: michiel
 * Date: 19/06/13
 * Time: 17:08
 */
namespace Lilweb\JobBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * Repository de gestion des entités.
 */
class JobInfoRepository extends EntityRepository
{
    /**
     * Retourne les jobs pour la journée en question.
     *
     * @param $annee
     * @param $mois
     * @param $jour
     *
     * @return ArrayCollection
     */
    public function findForDay($annee, $mois, $jour)
    {
        $debutJournee = new \DateTime();
        $debutJournee->setDate($annee, $mois, $jour);
        $debutJournee->setTime(0,0,0);

        $finJournee = new \DateTime();
        $finJournee->setDate($annee, $mois, $jour);
        $finJournee->setTime(23,59,59);

        return $this->createQueryBuilder('j')
            ->where('j.creationDate > :debut')
            ->andWhere('j.creationDate <= :fin')
            ->setParameter('debut', $debutJournee)
            ->setParameter('fin', $finJournee)
            ->getQuery()
            ->getResult();
    }
}