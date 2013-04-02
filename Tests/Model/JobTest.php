<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Lilweb\JobBundle\Model\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{
    /** @var \DOMElement */
    protected $domElement;

    /** @var \DOMElement */
    protected $taskElement;

    /** @var \Doctrine\Common\Collections\ArrayCollection */
    protected $tasks;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $document = new \DOMDocument();
        $this->domElement = $document->createElement('job');
        $this->taskElement = $document->createElement('task');
        $this->tasks = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->domElement);
        unset($this->tasks);
    }

    public function testConstructorWithValidDomElement()
    {
        $this->taskElement->setAttribute('name', 'import:csv');
        $this->domElement->setAttribute('name', 'besoin:all');
        $this->domElement->appendChild($this->taskElement);

        $this->tasks['import:csv'] = 'test';

        $job = new Job($this->domElement, $this->tasks);

        $this->assertEquals('besoin:all', $job->getName());
        $this->assertFalse($job->isSchedulable());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Attribut "name" manquant dans un job.
     */
    public function testConstructorWithMissingNameAttribute()
    {
        new Job($this->domElement, $this->tasks);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Un job doit contenir au moins une tache
     */
    public function testConstructorWithNoTasks()
    {
        $this->domElement->setAttribute('name', 'besoin:all');

        new Job($this->domElement, $this->tasks);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Attribut "name" manquant dans la tache d'un job
     */
    public function testConstructorWithInvalidTask()
    {
        $this->domElement->setAttribute('name', 'besoin:all');
        $this->domElement->appendChild($this->taskElement);

        new Job($this->domElement, $this->tasks);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Tache "task:non-existant" non définie.
     */
    public function testConstructorWithNonExistantTask()
    {
        $this->taskElement->setAttribute('name', 'task:non-existant');
        $this->domElement->setAttribute('name', 'besoin:all');
        $this->domElement->appendChild($this->taskElement);

        new Job($this->domElement, $this->tasks);
    }

    public function testSchedulable()
    {
        $this->taskElement->setAttribute('name', 'import:csv');
        $this->domElement->setAttribute('name', 'besoin:all');
        $this->domElement->setAttribute('schedulable', 'true');
        $this->domElement->appendChild($this->taskElement);

        $this->tasks['import:csv'] = 'test';

        $job = new Job($this->domElement, $this->tasks);

        $this->assertEquals('besoin:all', $job->getName());
        $this->assertTrue($job->isSchedulable());
    }
}