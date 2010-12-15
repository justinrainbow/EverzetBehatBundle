<?php

namespace Bundle\Everzet\BehatBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Features\FeaturesContainer;

use Symfony\Bundle\FrameworkBundle\Command\Command;

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract Test Command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class TestCommand extends Command
{
    /**
     * Return default command definitions for test. 
     * 
     * @return  array   arguments & options
     */
    protected function getTestDefinitions()
    {
        return array(
            new InputOption('--format',         '-f'
              , InputOption::PARAMETER_REQUIRED
              , 'How to format features (Default: pretty). Available formats is pretty, progress, html.'
            ),
            new InputOption('--out',            '-o'
              , InputOption::PARAMETER_REQUIRED
              , 'Write output to a file/directory instead of STDOUT.'
            ),
            new InputOption('--name',           '-N' 
              , InputOption::PARAMETER_REQUIRED
              , 'Only execute the feature elements (features or scenarios) which match part of the given name.'
            ),
            new InputOption('--tags',           '-t'
              , InputOption::PARAMETER_REQUIRED
              , 'Only execute the features or scenarios with tags matching expression.'
            ),
            new InputOption('--i18n',           '-i'
              , InputOption::PARAMETER_REQUIRED
              , 'Print formatters output in particular language.'
            ),
            new InputOption('--no-colors',      '-C'
              , InputOption::PARAMETER_NONE
              , 'Do not use ANSI color in the output.'
            ),
            new InputOption('--no-time',        '-T'
              , InputOption::PARAMETER_NONE
              , 'Hide time statistics in output.'
            ),
        );
    }

    /**
     * Configure container by provided input. 
     * 
     * @param   Container       $container  container instance
     * @param   InputInterface  $input      input instance
     */
    protected function configureTestContainer($container, InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('name')) {
            $container->get('behat.name_filter')->setFilterString($input->getOption('name'));
        }
        if (null !== $input->getOption('tags')) {
            $container->get('behat.tag_filter')->setFilterString($input->getOption('tags'));
        }
        if (null !== $input->getOption('format')) {
            $container->get('behat.output_manager')->setFormatter($input->getOption('format'));
        }
        if (null !== $input->getOption('out')) {
            $container->get('behat.output_manager')->setOutputPath($input->getOption('out'));
        }
        if (null !== $input->getOption('no-colors')) {
            $container->get('behat.output_manager')->showColors(!$input->getOption('no-colors'));
        }
        if (null !== $input->getOption('verbose')) {
            $container->get('behat.output_manager')->beVerbose($input->getOption('verbose'));
        }
        if (null !== $input->getOption('i18n')) {
            $container->get('behat.output_manager')->setLocale($input->getOption('i18n'));
        }
        if (null !== $input->getOption('no-time')) {
            $container->get('behat.output_manager')->showTimer(!$input->getOption('no-time'));
        }

        // Set output service
        $this->container->get('behat.output_manager')->setOutput($output);
    }

    /**
     * Prepare FeaturesContainer.
     *
     * Find feature, hooks & environment resources. 
     * 
     * @param   IteratorAggregate|array $features       array of feature files
     * @param   string                  $featuresPath   features base path
     *
     * @return  FeaturesContainer
     */
    protected function prepareFeaturesContainer($features, $featuresPath)
    {
        $hooksPath      = array($featuresPath . '/support/hooks.php', __DIR__ . '/../Resources/features/support/hooks.php');
        $stepsPaths     = array($featuresPath . '/steps', __DIR__ . '/../Resources/features/steps');

        // Setup environment builder
        $this->container->get('behat.environment_builder')->addEnvironmentFile($featuresPath . '/support/env.php');

        // Add hooks files paths to container resources list
        $hooksContainer = $this->container->get('behat.hooks_container');
        foreach ($hooksPath as $path) {
            if (is_file($path)) {
                $hooksContainer->addResource('php', $path);
            }
        }

        // Add features paths to container resources list
        $featuresContainer = $this->container->get('behat.features_container');
        foreach ($features as $feature) {
            $featuresContainer->addResource('gherkin', $feature);
        }

        // Add definitions files to container resources list
        $definitionsContainer = $this->container->get('behat.definitions_container');
        foreach ($stepsPaths as $stepsPath) {
            if (is_dir($stepsPath)) {
                foreach ($this->findDefinitionResources($stepsPath) as $path) {
                    $definitionsContainer->addResource('php', $path);
                }
            }
        }

        return $featuresContainer;
    }

    /**
     * Run All Features in Container. 
     * 
     * @param   FeaturesContainer   $featuresContainer  features container to run
     *
     * @return  integer                                 return code
     */
    protected function runFeatures(FeaturesContainer $featuresContainer)
    {
        // Notify suite.run.before event & start timer
        $this->container->get('behat.event_dispatcher')->notify(new Event($this->container, 'suite.run.before'));
        $this->container->get('behat.statistics_collector')->startTimer();

        // Run features
        $result = 0;
        foreach ($featuresContainer->getFeatures() as $feature) {
            $tester = $this->container->get('behat.feature_tester');
            $result = max($result, $feature->accept($tester));
        }

        // Notify suite.run.after event
        $this->container->get('behat.statistics_collector')->finishTimer();
        $this->container->get('behat.event_dispatcher')->notify(new Event($this->container, 'suite.run.after'));

        // Return exit code
        return intval(0 < $result);
    }

    /**
     * Find features files in specified path.
     *
     * @param string $featuresPath feature file or path
     *
     * @return mixed files iterator
     */
    protected function findFeatureResources($featuresPath)
    {
        if (is_file($featuresPath)) {
            $paths = array($featuresPath);
        } elseif (is_dir($featuresPath)) {
            $finder = new Finder();
            $paths = $finder->files()->name('*.feature')->in($featuresPath);
        } else {
            throw new \InvalidArgumentException(sprintf('Provide correct feature(s) path. "%s" given', $featuresPath));
        }

        return $paths;
    }

    /**
     * Find definitions files in specified path.
     *
     * @param string $stepsPath steps files path
     *
     * @return mixed files iterator
     */
    protected function findDefinitionResources($stepsPath)
    {
        $finder = new Finder();
        $paths = $finder->files()->name('*.php')->in($stepsPath);

        return $paths;
    }
}
