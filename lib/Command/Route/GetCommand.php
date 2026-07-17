<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Command\Route;

use OC\AppConfig;
use OC\Core\Command\Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetCommand extends Base {
	public function __construct(
		private AppConfig $appConfig,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('smtp-router:route:get')
			->setDescription('Show SMTP routes')
			->addOption(
				'output',
				null,
				InputOption::VALUE_OPTIONAL,
				'Output format (plain, json or json_pretty, default is plain)',
				$this->defaultOutputFormat
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$raw = $this->appConfig->getValue('smtp_router', 'routes', '');
		$data = [];

		if (is_string($raw) && $raw !== '') {
			try {
				$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
				if (is_array($decoded)) {
					$data = $decoded;
				}
			} catch (\JsonException) {
				$data = ['error' => 'Invalid JSON stored in smtp_router/routes'];
			}
		}

		$this->writeArrayInOutputFormat($input, $output, $data);

		return 0;
	}
}
