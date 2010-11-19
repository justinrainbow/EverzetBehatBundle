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
 * Test Single Feature Command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestFeatureCommand extends TestCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Tests specified feature')

            ->setDefinition(array_merge(array(
                new InputArgument('feature', InputArgument::REQUIRED, 'The feature file'),
            ), $this->getTestDefinitions()))
            ->setName('behat:test:feature')
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

        if (!is_file($feature = realpath($input->getArgument('feature')))) {
            throw new \InvalidArgumentException(sprintf('Feature file %s doesn\'t exists', $feature));
        }

        // Find features path
        $featuresPath = dirname($feature);

        // Prepare features container
        $featuresContainer = $this->prepareFeaturesContainer(array($feature), $featuresPath);

        return $this->runFeatures($featuresContainer);
    }
}
