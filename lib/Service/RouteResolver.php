<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Service;

use OC\AppConfig;
use OCP\Files\NotFoundException;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

class RouteResolver {
	private const APP_ID = 'smtp_router';

	public function __construct(
		private IRequest $request,
		private IGroupManager $groupManager,
		private IUserSession $userSession,
		private AppConfig $appConfig,
	) {
	}

	public function isMailKey(string $key): bool {
		return str_starts_with($key, 'mail_');
	}

	public function resolve(): ?array {
		$routes = $this->getRoutes();
		if ($routes === []) {
			return null;
		}

		$keys = [];
		$hostKey = $this->getHostCompanyKey();
		if ($hostKey !== null) {
			$keys[] = $hostKey;
		}

		$userKey = $this->getUserCompanyKey();
		if ($userKey !== null && !in_array($userKey, $keys, true)) {
			$keys[] = $userKey;
		}

		$keys[] = 'default';

		foreach ($keys as $key) {
			if (isset($routes[$key]) && is_array($routes[$key])) {
				return $routes[$key];
			}
		}

		return null;
	}

	private function getRoutes(): array {
		$raw = $this->appConfig->getValue(self::APP_ID, 'routes', '');
		if (!is_string($raw) || trim($raw) === '') {
			return [];
		}

		try {
			$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			return [];
		}

		return is_array($decoded) ? $decoded : [];
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

	private function getUserCompanyKey(): ?string {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return null;
		}

		foreach ($this->groupManager->getUserGroupIds($user) as $groupId) {
			if ($this->groupManager->groupExists($groupId)) {
				return $groupId;
			}
		}

		return null;
	}
}
