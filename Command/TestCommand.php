<?php

namespace Bundle\Everzet\BehatBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Finder\Finder;
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
    protected function configureTestContainer($container, InputInterface $input)
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
