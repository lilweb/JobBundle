<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Model;

/**
 * Represents a task.
 */
class Task
{
    /**
     * @var string The task service id.
     */
    private $serviceId;

    /**
     * @var string The task name.
     */
    private $name;

    /**
     * @var integer The maximum number of execution
     */
    private $maxParallelExecution;

    /**
     * Constructor.
     *
     * @param \DOMElement $element The XML element.
     * @throws \Exception
     */
    public function __construct(\DOMElement $element)
    {
        if (!$element->hasAttribute('name') || !$element->hasAttribute('service-id')) {
            throw new \Exception('Attribut "name" ou "service-id" manquant dans une tache.');
        }

        $this->name = $element->getAttribute('name');
        $this->serviceId = $element->getAttribute('service-id');
        $this->maxParallelExecution = 1;

        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->nodeName === 'max-parallel-execution') {
                if (!$child->hasAttribute('value')) {
                    throw new \Exception('Attribut "value" manquant sur la balise "max-parallel-execution" de la tache "'.$this->name.'"');
                }

                $this->maxParallelExecution = intval($child->getAttribute('value'));
            } else if ($child instanceof \DOMElement) {
                throw new \Exception('Element "'.$child->nodeName.'" non connu pour la tache "'.$this->name.'"');
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
