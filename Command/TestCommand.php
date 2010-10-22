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
              , 'Change output formatter'
            ),
            new InputOption('--tags',           '-t'
              , InputOption::PARAMETER_REQUIRED
              , 'Only executes features or scenarios with specified tags'
            ),
            new InputOption('--no-colors',      null
              , InputOption::PARAMETER_NONE
              , 'No colors in output'
            ),
            new InputOption('--no-time',        null
              , InputOption::PARAMETER_NONE
              , 'No timer in output'
            ),
            new InputOption('--i18n',           null
              , InputOption::PARAMETER_REQUIRED
              , 'Print formatters output in particular language'
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
        if (null !== $input->getOption('tags')) {
            $container->getBehat_TagFilterService()->setTags($input->getOption('tags'));
        }
        if (null !== $input->getOption('format')) {
            $container->getBehat_OutputManagerService()->setFormatter($input->getOption('format'));
        }
        if (null !== $input->getOption('no-colors')) {
            $container->getBehat_OutputManagerService()->allowColors(!$input->getOption('no-colors'));
        }
        if (null !== $input->getOption('verbose')) {
            $container->getBehat_OutputManagerService()->beVerbose($input->getOption('verbose'));
        }
        if (null !== $input->getOption('i18n')) {
            $container->getBehat_OutputManagerService()->setLocale($input->getOption('i18n'));
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
