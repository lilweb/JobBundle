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
    /** @var \Doctrine\Common\Collections\ArrayCollection */
    private $tasks;

    /** @var string The job name. */
    private $name;

    /** @var boolean  */
    private $schedule;

    /**
     * Constructor.
     *
     * @param \DOMElement $element The XML element.
     */
    public function __construct(\DOMElement $element, ArrayCollection $tasks)
    {
        if (!$element->hasAttribute('name')) {
            throw new \Exception('Attribut "name" manquant dans un job.');
        }

        $elements = $element->getElementsByTagName('task');
        if (!$elements->length) {
            throw new \Exception('Un job doit contenir au moins une tache');
        }

        $this->name = $element->getAttribute('name');
        $this->schedulable = false;
        $this->tasks = new ArrayCollection();

        if ($element->hasAttribute('schedule')) {

            $this->schedulable = true;
        }

        foreach ($elements as $el) {
            if ($el instanceof \DOMElement) {
                if (!$el->hasAttribute('name')) {
                    throw new \Exception('Attribut "name" manquant dans la tache d\'un job');
                }

                $taskName = $el->getAttribute('name');
                if ($tasks->get($taskName) === null) {
                    throw new \Exception('Tache "'.$taskName.'" non définie.');
                }

                $this->tasks[] = $tasks->get($taskName);
            }
        }
    }

    /**
     * Adds a tasks.
     *
     * @param integer                           $offset The position of the task
     * @param \Lilweb\JobBundle\Model\Task $task   The task to add.
     */
    public function addTaskAtOffset($offset, Task $task)
    {
        if ($this->tasks->containsKey($offset)) {
            throw new \Exception('ERROR: The offset '.$offset.' is already used by the task '.$task->getId());
        }

        $this->tasks->set($offset, $task);
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
     * @return boolean
     */
    public function isSchedulable()
    {
        return $this->schedulable;
    }
}