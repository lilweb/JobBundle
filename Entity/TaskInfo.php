<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Représente une tache d'un job.
 *
 * @ORM\Entity(repositoryClass = "Lilweb\JobBundle\Repository\TaskInfoRepository")
 * @ORM\Table(name = "task_infos")
 */
class TaskInfo
{
    const TASK_WAITING = 0;
    const TASK_RUNNING = 1;
    const TASK_OVER    = 2;
    const TASK_FAIL    = 3;
    const TASK_DROPPED = 4;
    const TASK_SKIPPED = 5;

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
     *     name   = "task_name"
     * )
     */
    private $name;

    /**
     * @var string The task status.
     *
     * @see const variables.
     *
     * @ORM\Column(
     *     type = "integer",
     *     name = "task_status"
     * )
     */
    private $status;

    /**
     * @var \Lilweb\JobBundle\Entity\JobInfo
     *
     * @ORM\ManyToOne(
     *     targetEntity = "\Lilweb\JobBundle\Entity\JobInfo",
     *     inversedBy   = "taskInfos"
     * )
     *
     * @ORM\JoinColumn(
     *      name                 = "job_info_id",
     *      referencedColumnName = "id",
     *      onDelete             = "CASCADE"
     * )
     */
    private $jobInfo;

    /**
     * @var integer L'ordre de la tache dans le job. s
     *
     * @ORM\Column(
     *      name = "ordre",
     *      type = "integer"
     * )
     */
    private $ordre;

    /**
     * @var \DateTime The date the at which the task began.
     *
     * @ORM\Column(
     *      type     = "datetime",
     *      name     = "creation_date"
     * )
     */
    private $creationDate;

    /**
     * @var \DateTime The date
     *
     * @ORM\Column(
     *      type     = "datetime",
     *      name     = "last_update_date",
     *      nullable = true
     * )
     */
    private $lastStatusUpdateDate;

    /**
     * @var string Information message
     *
     * @ORM\Column(
     *     type     = "string",
     *     length   = 2000,
     *     nullable = true,
     *     name     = "info_msg"
     * )
     */
    private $infoMsg;
    
    /**
     * Initialisation de la date de création.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->status = TaskInfo::TASK_WAITING;
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!is_int($status) || !in_array($status, array(self::TASK_WAITING, self::TASK_RUNNING, self::TASK_FAIL, self::TASK_OVER, self::TASK_DROPPED))) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $this->setLastStatusUpdateDate(new \DateTime());
        $this->status = $status;
    }

    /**
     * @param int $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @return boolean
     */
    public function isTaskWaiting()
    {
        return $this->status === self::TASK_WAITING;
    }

    /**
     * @return boolean
     */
    public function isTaskRunning()
    {
        return $this->status === self::TASK_RUNNING;
    }

    /**
     * @return boolean
     */
    public function isTaskFailed()
    {
        return $this->status === self::TASK_FAIL;
    }

    /**
     * @return boolean
     */
    public function isTaskOver()
    {
        return $this->status === self::TASK_OVER;
    }

    /**
     * @return boolean
     */
    public function isTaskDropped()
    {
        return $this->status === self::TASK_DROPPED;
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
     * @param \DateTime $lastStatusUpdateDate
     */
    public function setLastStatusUpdateDate($lastStatusUpdateDate)
    {
        $this->lastStatusUpdateDate = $lastStatusUpdateDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastStatusUpdateDate()
    {
        return $this->lastStatusUpdateDate;
    }

    /**
     * @return \Lilweb\JobBundle\Entity\JobInfo|null
     */
    public function getJobInfo()
    {
        return $this->jobInfo;
    }

    /**
     * @param \Lilweb\JobBundle\Entity\JobInfo|null $jobInfo
     */
    public function setJobInfo(JobInfo $jobInfo = null)
    {
        $this->jobInfo = $jobInfo;

        if ($jobInfo !== null && !$jobInfo->getTaskInfos()->contains($this)) {
            $jobInfo->addTaskInfo($this);
        }
    }

    /**
     * @return string
     */
    public function getInfoMsg()
    {
        return $this->infoMsg;
    }

    /**
     * @param string $infoMsg
     */
    public function setInfoMsg($infoMsg)
    {
        $this->infoMsg = $infoMsg;
    }
}
