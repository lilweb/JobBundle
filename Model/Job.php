<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Lilweb\JobBundle\Model\Task;

/**
 * Job model.
 */
class Job
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $tasks;

    /**
     * @var string The job name.
     */
    private $name;

    /**
     * @var string The CRON expression for the schedule of the job.
     */
    private $schedule;

    /**
     * @var array An array of parameters for the job
     */
    private $params;

    /**
     * Constructor.
     *
     * @param \DOMElement $element The XML element.
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks
     * @throws \Exception
     */
    public function __construct(\DOMElement $element, ArrayCollection $tasks)
    {
        if (!$element->hasAttribute('name')) {
            throw new \Exception('Attribut "name" manquant dans un job.');
        }

        $taskElements = $element->getElementsByTagName('task');
        if (!$taskElements->length) {
            throw new \Exception('Un job doit contenir au moins une tache');
        }

        $this->name = $element->getAttribute('name');
        $this->tasks = new ArrayCollection();
        $this->params = array();

        // Gestion des parametres d'un job
        $paramElements = $element->getElementsByTagName('param');
        foreach ($paramElements as $paramElement) {
            if (!$paramElement->hasAttribute('name')) {
                throw new \Exception('Attribut "name" manquant dans un parametre');
            }

            if (!$paramElement->hasAttribute('value')) {
                throw new \Exception('Attribut "value" manquant dans un parametre');
            }

            $paramName = $paramElement->getAttribute('name');
            $paramValue = $paramElement->getAttribute('value');

            $this->params[$paramName] = $paramValue;
        }

        // Gestion du schedule
        if ($element->hasAttribute('schedule')) {
            $this->schedule = $element->getAttribute('schedule');
        }

        foreach ($taskElements as $taskElement) {
            if ($taskElement instanceof \DOMElement) {
                if (!$taskElement->hasAttribute('name')) {
                    throw new \Exception('Attribut "name" manquant dans la tache d\'un job');
                }

                $taskName = $taskElement->getAttribute('name');
                if ($tasks->get($taskName) === null) {
                    throw new \Exception('Tache "'.$taskName.'" non définie.');
                }

                $this->tasks[] = $tasks->get($taskName);
            }
        }
    }

    /**
     * Adds a task.
     *
     * @param integer                      $offset The position of the task
     * @param \Lilweb\JobBundle\Model\Task $task   The task to add.
     * @throws \Exception
     */
    public function addTaskAtOffset($offset, Task $task)
    {
        if ($this->tasks->containsKey($offset)) {
            throw new \Exception('ERROR: The offset '.$offset.' is already used by the task '.$task->getId());
        }

        $this->tasks->set($offset, $task);
    }

    /**
     * @param string $taskName
     *
     * @return string|null
     */
    public function getNextTaskName($taskName)
    {
        $found = false;

        foreach ($this->tasks as $task) {
            if ($taskName === $task->getName()) {
                $found = true;
            } else if ($found) {
                return $task->getName();
            }
        }

        return null;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}