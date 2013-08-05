<?php
/**
 * User: Geoffrey Brier
 * Date: 18/03/13
 * Time: 14:32
 */
namespace Lilweb\JobBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface,
    Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration for the job extension.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('lilweb_job');
        $rootNode
            ->children()
                ->scalarNode('job_file')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('columns')
                    ->prototype('scalar')
                ->end()
            ->end();

        return $treeBuilder;
    }
}