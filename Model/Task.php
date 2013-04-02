<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Model;

use Doctrine\ORM\EntityManager;

use Lilweb\JobBundle\Entity\TaskInfo;


/**
 * Represents a task.
 */
class Task
{
    /** @var string The task service id. */
    private $serviceId;

    /** @var string The task name. */
    private $name;

    /** @var integer The maximum number of execution */
    private $maxParallelExecution;

    /** @var array The statistics */
    private $stats;

    /**
     * Constructor.
     *
     * @param \DOMElement $element The XML element.
     */
    public function __construct(\DOMElement $element)
    {
        if (!$element->hasAttribute('name') || !$element->hasAttribute('service-id')) {
            throw new \Exception('Attribut "name" ou "service-id" manquant dans une tache.');
        }

        $this->name = $element->getAttribute('name');
        $this->serviceId = $element->getAttribute('service-id');
        $this->maxParallelExecution = 0;

        $this->stats = array(
            TaskInfo::TASK_FAIL    => 0,
            TaskInfo::TASK_RUNNING => 0,
            TaskInfo::TASK_WAITING => 0,
            TaskInfo::TASK_OVER    => 0,
            TaskInfo::TASK_DROPPED => 0
        );

        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->nodeName === 'max-parallel-execution') {
                if (!$child->hasAttribute('value')) {
                    throw new \Exception('Attribut "value" manquant sur la balise "max-parallel-execution" de la tache "'.$this->name.'"');
                }

                $this->maxParallelExecution = intval($child->getAttribute('value'));
            } else if ($child instanceof \DOMElement) {
                throw new \Exception('Element "'.$child->nodeName.'" non connu pour la tache "'.$this->name.'"');
            }
        }
    }

    /**
     * Checks whether or not a task is executable.
     *
     * @return boolean
     */
    public function isExecutable(EntityManager $em)
    {
        // 1. Get the statistics
        $stats = $em
            ->getRepository('LilwebJobBundle:TaskInfo')
            ->getStatisticsForTask($this->name);

        foreach ($stats as $stat) {
            $this->stats[$stat['status']] = $stat['nb'];
        }

        // 2. If there is something to execute and we can execute it
        if ($this->stats[TaskInfo::TASK_WAITING] > 0 &&
            (!$this->stats[TaskInfo::TASK_RUNNING] || $this->isExecutableInParallel())) {
            return true;
        }

        return false;
    }

    /**
     * Checks if it is possible to execute the task once more.
     *
     * @return boolean
     */
    private function isExecutableInParallel()
    {
        return (
            $this->maxParallelExecution === -1
                ||
            $this->maxParallelExecution  <= $this->stats[TaskInfo::TASK_RUNNING] + 1
        );
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
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
