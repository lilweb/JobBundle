<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Services;

use Monolog\Logger;

use Lilweb\JobBundle\Entity\TaskInfo;

/**
 * AbstractTaskService.
 */
abstract class AbstractTaskService
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Monolog\Logger */
    protected $logger;

    /**
     * Constructor.
     *
     * NB: The logger uses a specific channel called 'jobs'.
     *
     * @param \Lilweb\JobBundle\Services\EntityManager $em
     * @param \Monolog\Logger                               $logger
     */
    public function __construct($em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Executes a given task.
     * This method is responsible for:
     *  - processing and setting the correct status of a TaskInfo
     *  - logging everything that needs to be via the injected logger
     *
     * @param \Lilweb\JobBundle\Entity\TaskInfo $taskInfo
     */
    abstract public function execute(TaskInfo $taskInfo);

    /**
     * Changes the status of a TaskInfo.
     *
     * @param \Lilweb\JobBundle\Entity\TaskInfo $taskInfo
     * @param string $status
     */
    public function changeStatus(TaskInfo $taskInfo, $status)
    {
        $tmpStatus = $taskInfo->getStatus();

        if ($tmpStatus !== $status) {
            $taskInfo->setStatus($status);

            $this->em->persist($taskInfo);
            $this->em->flush();
        }
    }

    /**
     * Gets the last error message.
     *
     * @return string
     */
    public function getLastErrorMsg()
    {
        $error = error_get_last();

        return $error['message'];
    }
}