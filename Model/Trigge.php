<?php
/**
 * Author: Michiel Missotten
 * Date: 26/04/13
 * Time: 11:39
 */
namespace Lilweb\JobBundle\Model;

/**
 * Représente un trigger.
 */
class Trigger 
{
    /**
     * @var string Le nom du trigger.
     */
    private $name;

    /**
     * @var string Le service correspondant au trigger.
     */
    private $idService;

    /**
     * @var string Le nom du job correspondant au trigger.
     */
    private $triggers;

    /**
     * @param string $idService
     */
    public function setIdService($idService)
    {
        $this->idService = $idService;
    }

    /**
     * @return string
     */
    public function getIdService()
    {
        return $this->idService;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $triggers
     */
    public function setTriggers($triggers)
    {
        $this->triggers = $triggers;
    }

    /**
     * @return string
     */
    public function getTriggers()
    {
        return $this->triggers;
    }
}