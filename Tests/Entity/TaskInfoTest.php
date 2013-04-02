<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\Entity;

use Lilweb\JobBundle\Entity\JobInfo;
use Lilweb\JobBundle\Entity\TaskInfo;

class TaskInfoTest extends \PHPUnit_Framework_TestCase
{
    protected $taskInfo;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->taskInfo = new TaskInfo();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->taskInfo);
    }

    public function testJobInfoRelationBehavesCorrectly()
    {
        $jobInfo = new JobInfo();

        $this->taskInfo->setJobInfo($jobInfo);
        $this->assertCount(1, $jobInfo->getTaskInfos());
        $this->assertSame($this->taskInfo->getJobInfo(), $jobInfo);
        $this->assertSame($this->taskInfo, $jobInfo->getTaskInfos()->first());
    }

    public function testJobInfoWithNoJobInfo()
    {
        $this->taskInfo->setJobInfo();

        $this->assertNull($this->taskInfo->getJobInfo());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid status
     */
    public function testSetStatusThrowExceptionOnUnsupportedStatus()
    {
        $this->taskInfo->setStatus('Unknown status');
    }

    public function testSetStatusWithWaitingStatus()
    {
        $this->taskInfo->setStatus(TaskInfo::TASK_WAITING);

        $this->assertEquals(TaskInfo::TASK_WAITING, $this->taskInfo->getStatus());
        $this->assertTrue($this->taskInfo->isTaskWaiting());
        $this->assertNotNull($this->taskInfo->getLastStatusUpdateDate());
    }

    public function testStatusWithRunningStatus()
    {
        $this->taskInfo->setStatus(TaskInfo::TASK_RUNNING);

        $this->assertEquals(TaskInfo::TASK_RUNNING, $this->taskInfo->getStatus());
        $this->assertTrue($this->taskInfo->isTaskRunning());
        $this->assertNotNull($this->taskInfo->getLastStatusUpdateDate());
        $this->assertNotNull($this->taskInfo->getExecutionDate());
    }

    public function testStatusWithOverStatus()
    {
        $this->taskInfo->setStatus(TaskInfo::TASK_OVER);

        $this->assertEquals(TaskInfo::TASK_OVER, $this->taskInfo->getStatus());
        $this->assertTrue($this->taskInfo->isTaskOver());
        $this->assertNotNull($this->taskInfo->getLastStatusUpdateDate());
    }

    public function testStatusWithFailStatus()
    {
        $this->taskInfo->setStatus(TaskInfo::TASK_FAIL);

        $this->assertEquals(TaskInfo::TASK_FAIL, $this->taskInfo->getStatus());
        $this->assertTrue($this->taskInfo->isTaskFailed());
        $this->assertNotNull($this->taskInfo->getLastStatusUpdateDate());
    }

    public function testStatusWithDropStatus()
    {
        $this->taskInfo->setStatus(TaskInfo::TASK_DROPPED);

        $this->assertEquals(TaskInfo::TASK_DROPPED, $this->taskInfo->getStatus());
        $this->assertTrue($this->taskInfo->isTaskDropped());
        $this->assertNotNull($this->taskInfo->getLastStatusUpdateDate());
    }
}