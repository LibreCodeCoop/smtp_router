<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Command\Route;

use OC\AppConfig;
use OC\Core\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCommand extends Base {
	public function __construct(
		private AppConfig $appConfig,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('smtp-router:route:set')
			->setDescription('Store SMTP routes as JSON')
			->addArgument(
				'json',
				InputArgument::REQUIRED,
				'JSON document containing default and per-group mail_* settings.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$json = trim((string) $input->getArgument('json'));

		try {
			$decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
			if (!is_array($decoded)) {
				throw new \InvalidArgumentException('The JSON document must decode to an array.');
			}
		} catch (\JsonException|\InvalidArgumentException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return 1;
		}

		$this->appConfig->setValue('smtp_router', 'routes', $json);
		$output->writeln('<info>SMTP routes updated</info>');

		return 0;
	}
}
