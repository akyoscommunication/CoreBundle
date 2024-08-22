<?php

namespace Akyos\CoreBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

// TODO => A revoir il y a trop de trucs qui ne fonctionnent plus.
#[AsCommand(
    name: 'app:bdd-import',
)]
class bddImport extends Command
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('')->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../../src/Dump/');
        $finder->sortByAccessedTime()->reverseSorting();

        foreach ($finder as $file) {
            $process = new Process('mysql -u ' . $this->connection->getParams()['user'] . ' -p ' . $this->connection->getParams()['dbname'] . ' < ' . $file->getPathname());
            break;
        }
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln([$process->getOutput()]);

        return 0;
    }
}
