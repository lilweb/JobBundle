<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\Model;

use Lilweb\JobBundle\Model\Task;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    /** @var \DOMElement */
    protected $domElement;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $document = new \DOMDocument();
        $this->domElement = $document->createElement('task');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->domElement);
    }

    public function testConstructorWithValidDomElement()
    {
        $this->domElement->setAttribute('service-id', 'import_csv');
        $this->domElement->setAttribute('name', 'import:csv');

        $task = new Task($this->domElement);

        $this->assertEquals('import_csv', $task->getServiceId());
        $this->assertEquals('import:csv', $task->getName());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Attribut "name" ou "service-id" manquant dans une tache.
     */
    public function testConstructorWithMissingServiceIdAttribute()
    {
        $this->domElement->setAttribute('name', 'import:csv');

        new Task($this->domElement);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Attribut "name" ou "service-id" manquant dans une tache.
     */
    public function testConstructorWithMissingNameAttribute()
    {
        $this->domElement->setAttribute('service-id', 'import_csv');

        new Task($this->domElement);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Attribut "value" manquant sur la balise "max-parallel-execution" de la tache "import:csv"
     */
    public function testConstructorWithMissingValueForParallelExecution()
    {
        $childElement = new \DOMElement('max-parallel-execution');

        $this->domElement->setAttribute('service-id', 'import_csv');
        $this->domElement->setAttribute('name', 'import:csv');
        $this->domElement->appendChild($childElement);

        new Task($this->domElement);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Element "max-1234" non connu pour la tache "import:csv"
     */
    public function testConstructorWithUnknownChildElement()
    {
        $childElement = new \DOMElement('max-1234');

        $this->domElement->setAttribute('service-id', 'import_csv');
        $this->domElement->setAttribute('name', 'import:csv');
        $this->domElement->appendChild($childElement);

        new Task($this->domElement);
    }
}