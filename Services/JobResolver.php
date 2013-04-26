<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Container;
use Monolog\Logger;

use Lilweb\JobBundle\Model\Task;
use Lilweb\JobBundle\Model\Job;
use Lilweb\JobBundle\Model\Trigger;

/**
 * The JobResolver resolves jobs & tasks given a configuration file.
 */
class JobResolver
{
    const CANNOT_LOAD_FILE = 'Chargement du fichier jobs.xml impossible';
    const MISSING_TASKS_NODE = 'Le noeud "tasks" n\'existe pas';
    const MISSING_JOBS_NODE = 'Le noeud "jobs" n\'existe pas';
    const EMPTY_TASKS_NODE = 'Le noeud "tasks" est vide';
    const EMPTY_JOBS_NODE = 'Le noeud "jobs" est vide';

    /**
     * @var string The job description file.
     */
    private $file;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The jobs found
     */
    private $jobs;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The tasks found
     */
    private $tasks;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The triggers found
     */
    private $triggers;

    /**
     * Constructor.
     */
    public function __construct(Logger $logger, $file)
    {
        $this->logger = $logger;
        $this->file = $file;

        $this->tasks = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->triggers = new ArrayCollection();

        $this->load();
    }

    /**
     * Loads the job description file.
     */
    private function load()
    {
        try {
            // Chargement du document
            $dom = new \DOMDocument();

            try {
                $dom->load($this->file);
            } catch (\Exception $e) {
                throw new \Exception(self::CANNOT_LOAD_FILE);
            }

            // Vérification qu'un noeud 'tasks' existe
            $tasksNode = $dom->getElementsByTagName('tasks');
            if (!$tasksNode->length) {
                throw new \Exception(self::MISSING_TASKS_NODE);
            }

            // Vérification qu'un noeud 'jobs' existe
            $jobsNode = $dom->getElementsByTagName('jobs');
            if (!$jobsNode->length) {
                throw new \Exception(self::MISSING_JOBS_NODE);
            }

            // Chargements des taches et des jobs.
            $this->loadTasks($tasksNode->item(0)->childNodes);
            $this->loadJobs($jobsNode->item(0)->childNodes);

            // Si un noeud triggers est définie, alors charger les triggers
            $triggersNode = $dom->getElementsByTagName('triggers');
            if ($triggersNode->length != 0) {
                $this->loadTriggers($triggersNode->item(0)->childNodes);
            }

        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
            throw $e;
        }
    }

    /**
     * Chargement de la liste des taches à partir d'un noeud XML.
     *
     * @param \DOMNodeList $elements
     *
     * @throws \Exception
     */
    private function loadTasks(\DOMNodeList $elements)
    {
        if (!$elements->length) {
            throw new \Exception(self::EMPTY_TASKS_NODE);
        }

        foreach ($elements as $element) {
            if ($element instanceof \DOMElement) {
                $task = new Task($element);

                $this->tasks[$element->getAttribute('name')] = $task;
            }
        }

        if ($this->tasks->isEmpty()) {
            throw new \Exception(self::EMPTY_TASKS_NODE);
        }
    }

    /**
     * @param \DOMNodeList $elements
     *
     * @throws \Exception
     */
    private function loadJobs(\DOMNodeList $elements)
    {
        if (!$elements->length) {
            throw new \Exception(self::EMPTY_JOBS_NODE);
        }

        foreach ($elements as $element) {
            if ($element instanceof \DOMElement) {
                $task = new Job($element, $this->tasks);

                $this->jobs[$element->getAttribute('name')] = $task;
            }
        }

        if ($this->jobs->isEmpty()) {
            throw new \Exception(self::EMPTY_JOBS_NODE);
        }
    }

    /**
     * Analyse le noeud XML pour construire la liste des triggers.
     *
     * @param \DOMNodeList $elements
     */
    public function loadTriggers($elements)
    {
        if (!$elements->length) {
            return;
        }

        foreach ($elements as $element) {
            if ($element instanceof \DOMElement) {
                $trigger = new Trigger();
                $trigger->setName($element->getAttribute('name'));
                $trigger->setIdService($element->getAttribute('id-service'));
                $trigger->setTriggers($element->getAttribute('triggers'));

                $this->triggers[$trigger->getName()] = $trigger;
            }
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param string $jobName
     *
     * @return \Lilweb\JobBundle\Model\Job|null
     */
    public function getJob($jobName)
    {
        if ($this->jobs->containsKey($jobName)) {
            return $this->jobs[$jobName];
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTriggers()
    {
        return $this->triggers;
    }
}