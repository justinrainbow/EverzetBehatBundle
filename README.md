Provides Behat BDD support for your Symfony2 project.
See [Behat official site](http://everzet.com/Behat) for more info.

## Features

- Support latest Symfony2 release (PR3)
- Fully integrates with Symfony2 project
- Fully tested with Behat itself
- Covers basic functional testing needs
- Beautifull bundle testing possibilities

## Installation

### Add Everzet\BehatBundle to your src/Bundle dir

    git submodule add git://github.com/everzet/EverzetBehatBundle.git src/Bundle/Everzet/BehatBundle

### Install Translation component

Behat depends on Symfony Translation Component, that comes after PR3 release. So, download latest Symfony2 version from
GitHub and copy `src/Symfony/Component/Translation` directory inside your local `src/vendor/symfony/src/Symfony/Component/`.

### Put Behat inside vendors folder

    git submodule add git://github.com/everzet/Behat src/vendor/Behat

### Add Behat namespace to autoload

    // src/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Everzet\\Behat' => $vendorDir . '/Behat/src',
        // ...
    ));

### Add EverzetBehatBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\Everzet\BehatBundle\EverzetBehatBundle(),
            // ...
        );
    }

### Add behat configuration into your config

    # app/config/config.yml
    behat.config: ~

### Configuration parameters

EverzetBehatBundle have configuration alias:

- `behat.config` is core configurator of BehatBundle. Specify default formatter parameters and output options here.

For example, by default Behat uses *pretty* formatter. If you want to always use *progress* formatter instead of
specifying `-f ...` option everytime, add this to your config:

    # app/config/config.yml
    behat.config:
      formatter: progress

Other options is `locale` and `colors`

## Write features

Put your features inside your `Bundle/Tests/Features/` directory, steps inside `Bundle/Tests/Features/steps`, hooks and
env.php inside `Bundle/Tests/Features/support`.

### Core steps

EverzetBehatBundle comes bundled with core steps. Look at them inside Bundle's `BehatBundle/Resources/features` folder. Also,
you can view how to use them by looking at `BehatBundle/Tests/Features/*` core BehatBundle tests.

## Command line

EverzetBehatBundle provides some very useful CLI commands for running your features.

### Run bundle tests

This command runs all features inside single bundle:

    php app/console behat:test:bundle Application\\HelloBundle

to run HelloBundle application tests.

### Run single feature

This command runs single specified feature:

    php app/console behat:test:feature src/Application/HelloBundle/Tests/Features/SingleFeature.feature

### Options

EverzetBehatBundle supports all options, that Behat itself supports, including:

- `--formatter` or `-f`: switch formatter (default ones is *progress* & *pretty*)
- `--no-colors`: turn-off colors in formatter
- `--i18n ...`: output formatter locale
- `--tags ...`: filter features/scenarios by tag

## CREDITS

List of developers who contributed:

- Konstantin Kudryashov (ever.zet@gmail.com)

