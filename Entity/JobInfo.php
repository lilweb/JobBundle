<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass = "Lilweb\JobBundle\Repository\JobInfoRepository")
 * @ORM\Table(name = "job_infos")
 */
class JobInfo
{
    /**
     * @ORM\Id
     * @ORM\Column(type = "integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string Name of the task.
     *
     * @ORM\Column(
     *     type   = "string",
     *     length = 255,
     *     name   = "job_name"
     * )
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity  = "\Lilweb\JobBundle\Entity\TaskInfo",
     *      mappedBy      = "jobInfo",
     *      cascade       = { "all" },
     *      orphanRemoval = true
     * )
     *
     * @ORM\OrderBy({"ordre" = "ASC"})
     */
    private $taskInfos;

    /**
     * @var string Name of the person who's run the job.
     *
     * @ORM\Column(
     *      name   = "job_runner",
     *      type   = "string",
     *      length = 255
     * )
     */
    private $jobRunner;

    /**
     * @var \DateTime La date de création du job.
     *
     * @ORM\Column(
     *      name = "creation_date",
     *      type = "datetime"
     * )
     */
    private $creationDate;

    /**
     * @var String Tableau sérialisé qui contient les différents parametres du job.
     *
     * @ORM\Column(
     *      name     = "parameters",
     *      type     = "string",
     *      length   = 1000,
     *      nullable = true
     * )
     */
    private $parameters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->taskInfos = new ArrayCollection();
        $this->creationDate = new \DateTime();
    }

    /**
     * On récupère le statut de la dernière tache executé.
     */
    public function getLastStatus()
    {
        $statut = 0;
        foreach ($this->taskInfos as $taskInfo) {
            $statut = $taskInfo->getStatus();
        }

        return $statut;
    }

    /**
     * Retourne le statut global du job.
     */
    public function getGlobalStatus()
    {
        $nombreTermine = 0;
        foreach ($this->taskInfos as $taskInfo) {

            // En cours
            if ($taskInfo->getStatus() == TaskInfo::TASK_RUNNING) {
                return TaskInfo::TASK_RUNNING;
            }

            // En echec
            if ($taskInfo->getStatus() == TaskInfo::TASK_FAIL) {
                return TaskInfo::TASK_FAIL;
            }

            // Abandonné.
            if ($taskInfo->getStatus() == TaskInfo::TASK_DROPPED) {
                return TaskInfo::TASK_DROPPED;
            }

            if ($taskInfo->getStatus() == TaskInfo::TASK_OVER || $taskInfo->getStatus() == TaskInfo::TASK_SKIPPED) {
                $nombreTermine++;
            }
        }

        if ($nombreTermine == 0) {
            return TaskInfo::TASK_WAITING;
        } else if ($nombreTermine == count($this->taskInfos)) {
            return TaskInfo::TASK_OVER;
        } else {
            return TaskInfo::TASK_RUNNING;
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name The job name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTaskInfos()
    {
        return $this->taskInfos;
    }

    /**
     * @param array|\Doctrine\Common\Collections\ArrayCollection $taskInfos
     */
    public function setTaskInfos($taskInfos)
    {
        $this->taskInfos->clear();

        foreach ($taskInfos as $taskInfo) {
            $this->addTaskInfo($taskInfo);
        }
    }

    /**
     * @param \Lilweb\JobBundle\Entity\TaskInfo $taskInfo
     */
    public function addTaskInfo(TaskInfo $taskInfo)
    {
        $this->taskInfos[] = $taskInfo;

        if ($taskInfo->getJobInfo() !== $this) {
            $taskInfo->setJobInfo($this);
        }
    }

    /**
     * @return string
     */
    public function getJobRunner()
    {
        return $this->jobRunner;
    }

    /**
     * @param string $jobRunner
     */
    public function setJobRunner($jobRunner)
    {
        $this->jobRunner = $jobRunner;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Encode et décode les parametres d'un job.
     *
     * @param $params
     * @return string
     */
    public function encodeParameters($params)
    {
        $encoded = '';
        foreach ($params as $name => $value) {
            $encoded .= $name . '=' . $value . ';';
        }

        return substr($encoded, 0, -1);;
    }

    /**
     * Encode et decode les parametres d'un job.
     *
     * @param $params
     * @return array
     */
    public function decodeParameters($params)
    {
        if ($params == '') {
            return array();
        }

        $result = array();
        $dataArray = explode(';', $params);
        foreach ($dataArray as $keyValue) {
            list($key, $value) = explode('=', $keyValue);
            $result[$key] = $value;
        }

        return $result;
    }


    /**
     * Retourne la value du parametre si elle existe.
     *
     * @param $name
     * @return String
     */
    public function getParameter($name)
    {
        $parameters = $this->decodeParameters($this->parameters);
        if (isset($parameters[$name])) {
            return $parameters[$name];
        }

        return null;
    }

    /**
     * Définit la valeur d'un paramètre.
     */
    public function setParameter($name, $value)
    {
        $parameters = $this->decodeParameters($this->parameters);
        $parameters[$name] = $value;
        $this->parameters = $this->encodeParameters($parameters);
    }

    /**
     * Ajoute ou remplace les paramètres du job par les parametres.
     *
     * @param $parameters
     */
    public function addParameters($parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->decodeParameters($this->parameters);
    }
}