<?php

namespace Bundle\Everzet\BehatBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->configureTestContainer($this->container, $input, $output);

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

        // Find features path
        $featuresPath = $basePath . '/Tests/Features';

        // Prepare features container
        $featuresContainer = $this->prepareFeaturesContainer($this->findFeatureResources($featuresPath), $featuresPath);

        return $this->runFeatures($featuresContainer);
    }
}
