<?php

namespace Yauhenko\CronBundle\Command;

use Yauhenko\CronBundle\Service\CronService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CronCommand extends Command {

	protected ParameterBagInterface $params;

	public function __construct(ParameterBagInterface $params) {
		parent::__construct();
		$this->params = $params;
	}

	protected function configure() {
		$this->setName('cron:setup');
		$this->setDescription('Set cron');
		$this->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove crontab');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		$crontab = new CronService($this->params);
		$crontab->update($input->getOption('remove'));
		$crontab->save();
		$io->success('Crontab updated');
		return Command::SUCCESS;
	}

}
