<?php

namespace Akyos\CoreBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class bddDump extends Command
{
	
	protected static $defaultName = 'app:bdd-dump';
	
	private $connection;
	
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		parent::__construct();
	}
	
	protected function configure()
	{
		$this->setDescription('')
			->setHelp('');
		
		$this->addArgument('name', InputArgument::OPTIONAL, 'Name of User');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$date = new \DateTime('now');
		$date = $date->format('dmYHi');
		
		$filename = $input->getArgument('name') . '_' . $this->connection->getParams()['dbname'] . '_' . $date . '.sql';
		
		$process = new Process('mysqldump -u ' . $this->connection->getParams()['user'] . ' -p ' . $this->connection->getParams()['dbname'] . ' > ' . __DIR__ . '/../../../src/Dump/' . $filename);
		$process->run();
		
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
		
		$output->writeln([
			"L'export c'est bien passé le fichier dump " . $filename . " a bien été crée"
		]);
		
		$process = new Process('git add ' . __DIR__ . '/../../../src/Dump/' . $filename);
		$process->run();
		
		$output->writeln([
			"Le fichier a été ajouté à git"
		]);
		
		
		return 0;
	}
	
}