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
    private $jobName;

    /**
     * Construction d'un trigger à partir du noeud xml
     */
    public function __construct(\DOMElement $element)
    {
        if (!$element->hasAttribute('name') && $element->getAttribute('name') == "") {
            throw new \Exception('Attribut "name" manquant dans un trigger.');
        }

        if (!$element->hasAttribute('service-id') && $element->getAttribute('service-id') == "") {
            throw new \Exception('Attribut "id-service" manquant dans un trigger.');
        }

        if (!$element->hasAttribute('job-name') && $element->getAttribute('job-name') == "") {
            throw new \Exception('Attribut "job-name" manquant dans un trigger.');
        }

        $this->name = $element->getAttribute('name');
        $this->idService = $element->getAttribute('service-id');
        $this->jobName = $element->getAttribute('job-name');
    }


    /**
     * @return string
     */
    public function getIdService()
    {
        return $this->idService;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }
}