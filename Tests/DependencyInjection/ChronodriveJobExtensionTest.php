<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\MonologBundle\DependencyInjection\MonologExtension;

use Lilweb\JobBundle\DependencyInjection\LilwebJobExtension;

class LilwebJobExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('job_file', 'Tests/Fixtures/jobs/jobs.xml');
        $this->container->registerExtension(new MonologExtension());
        $this->container->registerExtension(new LilwebJobExtension());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->container);
    }

    public function testJobResolverServiceWithConfiguration()
    {
        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/../Fixtures/config/'));
        $loader->load('jobs.yml');
        $loader->load('monolog.yml');

        $this->container->compile();

        $jobResolver = $this->container->get('lilweb.job_resolver');

        $this->assertCount(1, $jobResolver->getJobs());
        $this->assertCount(2, $jobResolver->getTasks());
    }
}