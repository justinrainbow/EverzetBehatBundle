<?php

namespace Bundle\Everzet\BehatBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Symfony\Bundle\FrameworkBundle\Command\Command;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Finder\Finder;

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Bundle Test Command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestBundleCommand extends TestCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Tests specified bundle features')

            ->setDefinition(array_merge(array(
                new InputArgument('namespace', InputArgument::REQUIRED, 'The bundle namespace'),
            ), $this->getTestDefinitions()))
            ->setName('behat:test:bundle')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureTestContainer($this->container, $input);

        if (!preg_match('/Bundle$/', $namespace = $input->getArgument('namespace'))) {
            throw new \InvalidArgumentException('The namespace must end with Bundle.');
        }

        $basePath = null;
        foreach ($this->container->getKernelService()->getBundles() as $bundle)
        {
            $tmp = str_replace('\\', '/', get_class($bundle));
            $bundleNamespace = str_replace('/', '\\', dirname($tmp));
            if ($namespace === $bundleNamespace) {
                $basePath = realpath($bundle->getPath());
                break;
            }
        }

        if (null === $basePath) {
            throw new \InvalidArgumentException(
                sprintf("Unable to test bundle (%s is not a defined namespace).", $namespace)
            );
        }

        $featuresPath   = $basePath . '/Tests/Features';
        $hooksPath      = array($featuresPath . '/support/hooks.php', __DIR__ . '/../Resources/features/support/hooks.php');
        $stepsPaths     = array($featuresPath . '/steps', __DIR__ . '/../Resources/features/steps');

        // Set output service
        $this->container->get('behat.output_manager')->setOutput($output);

        // Add hooks files paths to container resources list
        $hooksContainer = $this->container->get('behat.hooks_container');
        foreach ($hooksPath as $path) {
            if (is_file($path)) {
                $hooksContainer->addResource('php', $path);
            }
        }

        // Add features paths to container resources list
        $featuresContainer = $this->container->get('behat.features_container');
        foreach ($this->findFeatureResources($featuresPath) as $path) {
            $featuresContainer->addResource('gherkin', $path);
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
}
