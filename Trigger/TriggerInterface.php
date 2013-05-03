<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 09:52
 */
namespace Lilweb\JobBundle\Trigger;

/**
 * Un trigger est une classe/service qui determine si un job doit être lancé.
 */
interface TriggerInterface
{
    /**
     * Vérifie si un job doit être lancée.
     *
     * @return boolean
     */
    public function checkCondition();

    /**
     * Retourne un tableau avec les différents parametres pour le job.
     *
     * @return array
     */
    public function getParameters();
}