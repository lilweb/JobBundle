<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\Tests\Services;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\MonologBundle\DependencyInjection\MonologExtension;

use Lilweb\JobBundle\DependencyInjection\LilwebJobExtension;

/**
 * @todo Fix monolog output logging
 */
class JobResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
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

    private function loadJobConfig($file)
    {
        $this->container->setParameter('job_file', $file);

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/../Fixtures/config/'));
        $loader->load('jobs.yml');
        $loader->load('monolog.yml');

        $this->container->compile();
        $this->container->get('logger')->pushHandler(
                $this->container->get('monolog.handler.job_handler')
        );
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Chargement du fichier jobs.xml impossible
     */
    public function testLoadWithNonExistantFile()
    {
        $this->loadJobConfig('idonotexist.xml');
        $logger = $this->container->get('logger');

        $this->container->get('lilweb.job_resolver');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Le noeud "tasks" n'existe pas
     */
    public function testLoadWithNoTasksNode()
    {
        $this->loadJobConfig('Tests/Fixtures/jobs/jobs_no_tasks_node.xml');

        $this->container->get('lilweb.job_resolver');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Le noeud "jobs" n'existe pas
     */
    public function testLoadWithNoJobsNode()
    {
        $this->loadJobConfig('Tests/Fixtures/jobs/jobs_no_jobs_node.xml');

        $this->container->get('lilweb.job_resolver');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Le noeud "tasks" est vide
     */
    public function testLoadTasksWithEmptyTasksNode()
    {
        $this->loadJobConfig('Tests/Fixtures/jobs/jobs_empty_tasks_node.xml');

        $this->container->get('lilweb.job_resolver');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Le noeud "jobs" est vide
     */
    public function testLoadJobsWithEmptyJobsNode()
    {
        $this->loadJobConfig('Tests/Fixtures/jobs/jobs_empty_jobs_node.xml');

        $this->container->get('lilweb.job_resolver');
    }

    public function testLoadBuildsCorrectlyJobsAndTasks()
    {
        $this->loadJobConfig('Tests/Fixtures/jobs/jobs.xml');

        $jobResolver = $this->container->get('lilweb.job_resolver');
        $job = $jobResolver->getJob('besoin:all');

        $this->assertCount(1, $jobResolver->getJobs());
        $this->assertCount(2, $jobResolver->getTasks());
        $this->assertCount(2, $job->getTasks());
    }
}