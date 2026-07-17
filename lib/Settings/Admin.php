<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Settings;

use OCA\SmtpRouter\AppInfo\Application;
use OCA\SmtpRouter\Service\RouteService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct(
		private RouteService $routeService,
	) {
	}

	public function getForm(): TemplateResponse {
		Util::addStyle(Application::APP_ID, 'admin');
		Util::addScript(Application::APP_ID, 'admin');

		return new TemplateResponse(Application::APP_ID, 'admin', $this->buildViewData());
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 10;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function buildViewData(): array {
		$routes = $this->routeService->getRoutes();
		$groupNames = $this->routeService->getGroupDisplayNames(array_keys($routes));

		return [
			'routes' => $routes,
			'groupNames' => $groupNames,
			'requesttoken' => \OC::$server->getCsrfTokenManager()->getToken()->getEncryptedValue(),
			'adminPageUrl' => \OC::$server->getURLGenerator()->linkToRoute(Application::APP_ID . '.admin.index'),
			'searchUrl' => \OC::$server->getURLGenerator()->linkToRoute(Application::APP_ID . '.admin.searchGroups'),
		];
	}
}
