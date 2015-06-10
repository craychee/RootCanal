<?php

namespace Rootcanal\Console;

use Rootcanal\Config\Config;
use Rootcanal\Config\Finder;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Single command, responsible for running the application
 */
class Command extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('drupal:canal')
            ->setDefinition(array(
                    new InputOption(
                        'source',
                        's',
                        InputOption::VALUE_NONE,
                        'Path to source of the project'
                    ),
                    new InputOption(
                        'destination',
                        'd',
                        InputOption::VALUE_REQUIRED,
                        'Path to destination of the project',
                        'www'
                    ),
                    new InputOption(
                        'production',
                        'p',
                        InputOption::VALUE_NONE,
                        'Generate production artifact from source'
                    )
                ))
            ->setDescription('Build a canal between composer and a working Drupal Application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command specifications:

  <info>php %command.full_name%</info>

Will run generate a drupal root directory from vendor.

You can choose the name of the destination path by specifying a destination path:

  <info>php %command.full_name% --destination=docroot</info>

By default, modules, themes, and custom directories will be symlinked into a Drupal root.
You can instead copy all files and directories with:

  <info>php %command.full_name% --production</info>
EOF
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config(
            $input->getOption('production'),
            $input->getOption('destination'),
            '/sites/all/modules/%s',
            '/sites/all/themes/%s',
            '/sites/all/drush/%s',
            '/profiles/%s',
            '/sites/default/files',
            '/sites/default/files-private',
            '/sites/default/settings.php'
        );

        $finder = new Finder(
           # $input->getOption('source'),
            'fixture',
            $input->getOption('destination'),
            'cnf/files',
            'cnf/private',
            'cnf/settings.php'
        );
        print_r($finder->getName());

#        print_r($config);
#        print_r($finder);
    }
}
