<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Emag\SynchronizedBundle\Decorator;
use Emag\SynchronizedBundle\Driver\File;
use Emag\SynchronizedBundle\Lock;
use Emag\SynchronizedBundle\Tests\Stubs\TestEventDispatcher;
use Emag\SynchronizedBundle\Tests\Stubs\TestService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileLockCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('test:file');
        $this->addOption('seconds', 's', InputOption::VALUE_OPTIONAL, 'seconds to sleep', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $decorator = new Decorator(new TestService(), 'test_service',  new TestEventDispatcher());
        $lock = new Lock();
        $lock->setDriver(new File(new \Symfony\Component\Filesystem\Filesystem(), 'lock'))->setMethod('sleep');
        $decorator->addLock($lock);
        $decorator->sleep((int)$input->getOption('seconds'));
        $output->write('done');
    }

}

$console = new Application();
$console->add(new FileLockCommand());
$console->run();
