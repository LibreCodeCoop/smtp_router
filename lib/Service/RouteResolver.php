<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Service;

use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

class RouteResolver {
	public function __construct(
		private IRequest $request,
		private IGroupManager $groupManager,
		private IUserSession $userSession,
		private RouteService $routeService,
	) {
	}

	public function isMailKey(string $key): bool {
		return str_starts_with($key, 'mail_');
	}

	public function resolve(): ?array {
		$routes = $this->routeService->getRoutes();
		if ($routes === []) {
			return null;
		}

		$hostKey = $this->getHostCompanyKey();
		if ($hostKey !== null && isset($routes[$hostKey])) {
			return $routes[$hostKey];
		}

		$user = $this->userSession->getUser();
		if ($user !== null) {
			foreach ($this->groupManager->getUserGroupIds($user) as $groupId) {
				if (isset($routes[$groupId])) {
					return $routes[$groupId];
				}
			}
		}

		return $routes['default'] ?? null;
	}

	private function getHostCompanyKey(): ?string {
		$host = $this->request->getServerHost();
		if ($host === '') {
			return null;
		}

		[$subdomain] = explode('.', $host);
		if ($subdomain === '') {
			return null;
		}

		$group = $this->groupManager->get($subdomain);
		if ($group !== null) {
			return $subdomain;
		}

		return null;
	}
}
