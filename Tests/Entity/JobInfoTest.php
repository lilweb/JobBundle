<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\Entity;

use Lilweb\JobBundle\Entity\JobInfo;
use Lilweb\JobBundle\Entity\TaskInfo;

class JobInfoTest extends \PHPUnit_Framework_TestCase
{
    protected $jobInfo;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jobInfo = new JobInfo();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->jobInfo);
    }

    public function testTaskInfoRelationBehavesCorrectly()
    {
        $taskInfo = new TaskInfo();

        $this->jobInfo->addTaskInfo($taskInfo);

        $this->assertCount(1, $this->jobInfo->getTaskInfos());
        $this->assertSame($this->jobInfo, $taskInfo->getJobInfo());
    }
}