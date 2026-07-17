<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\AppInfo;

use OCA\SmtpRouter\Config\RouteConfig;
use OCA\SmtpRouter\Service\RouteResolver;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'smtp_router';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerService(RouteResolver::class, function ($c) {
			return new RouteResolver(
				$c->get(\OCP\IRequest::class),
				$c->get(\OCP\IGroupManager::class),
				$c->get(\OCP\IUserSession::class),
				$c->get(\OC\AppConfig::class),
			);
		});

		$context->registerService(\OCP\IConfig::class, function ($c) {
			return new RouteConfig(
				\OC::$configDir,
				$c->get(RouteResolver::class),
			);
		});
	}

	public function boot(IBootContext $context): void {
	}
}
