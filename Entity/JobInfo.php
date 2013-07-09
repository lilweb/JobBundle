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
     *     targetEntity = "\Lilweb\JobBundle\Entity\TaskInfo",
     *     mappedBy     = "jobInfo",
     *     cascade      = { "persist", "remove" }
     * )
     *
     * @ORM\OrderBy({"executionDate" = "ASC"})
     */
    private $taskInfos;

    /**
     * @var string Name of the person who's run the job.
     *
     * @ORM\Column(
     *     type   = "string",
     *     length = 255,
     *     name   = "job_runner"
     * )
     */
    private $jobRunner;

    /**
     * @var \DateTime The date the at which the task began.
     *
     * @ORM\Column(
     *     type     = "datetime",
     *     nullable = true,
     *     name     = "execution_date"
     * )
     */
    private $executionDate;

    /**
     * @var \DateTime The date
     *
     * @ORM\Column(
     *     type     = "datetime",
     *     name     = "creation_date"
     * )
     */
    private $creationDate;

    /**
     * @var \DateTime The date
     *
     * @ORM\Column(
     *     type     = "datetime",
     *     name     = "last_status_update_date"
     * )
     */
    private $lastStatusUpdateDate;

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
     * @return \DateTime
     */
    public function getExecutionDate()
    {
        return $this->executionDate;
    }

    /**
     * @param \DateTime $executionDate
     */
    public function setExecutionDate(\DateTime $executionDate)
    {
        $this->executionDate = $executionDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastStatusUpdateDate()
    {
        return $this->lastStatusUpdateDate;
    }

    /**
     * @param \DateTime $lastStatusUpdateDate
     */
    public function setLastStatusUpdateDate(\DateTime $lastStatusUpdateDate)
    {
        $this->lastStatusUpdateDate = $lastStatusUpdateDate;
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
}