<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Service;

use OC\AppConfig;
use OCP\IGroup;
use OCP\IGroupManager;

class RouteService {
	private const APP_ID = 'smtp_router';

	/**
	 * @param array<string, mixed> $route
	 */
	private const ROUTE_KEYS = [
		'mail_smtpmode',
		'mail_smtphost',
		'mail_smtpport',
		'mail_smtpsecure',
		'mail_smtpauth',
		'mail_smtpname',
		'mail_smtppassword',
		'mail_domain',
		'mail_from_address',
		'mail_sendmailmode',
		'mail_smtpauthtype',
	];

	public function __construct(
		private AppConfig $appConfig,
		private IGroupManager $groupManager,
	) {
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getRoutes(): array {
		$raw = $this->appConfig->getValue(self::APP_ID, 'routes', '');
		if (!is_string($raw) || trim($raw) === '') {
			return [];
		}

		try {
			$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			return [];
		}

		if (!is_array($decoded)) {
			return [];
		}

		$routes = [];
		foreach ($decoded as $routeKey => $route) {
			if (!is_string($routeKey) || !is_array($route)) {
				continue;
			}
			$routes[$routeKey] = $this->normalizeRoute($route);
		}

		return $routes;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getRoute(string $routeKey): array {
		$routes = $this->getRoutes();
		return $routes[$routeKey] ?? [];
	}

	/**
	 * @param array<string, mixed> $route
	 */
	public function saveRoute(string $routeKey, array $route): void {
		$routes = $this->getRoutes();
		$routes[$routeKey] = $this->normalizeRoute($route);
		$this->persistRoutes($routes);
	}

	public function deleteRoute(string $routeKey): void {
		$routes = $this->getRoutes();
		unset($routes[$routeKey]);
		$this->persistRoutes($routes);
	}

	public function isValidRouteKey(string $routeKey): bool {
		return $routeKey === 'default' || $this->groupManager->groupExists($routeKey);
	}

	/**
	 * @return list<array{id: string, displayName: string}>
	 */
	public function searchGroups(string $query, int $limit = 20): array {
		$query = trim($query);
		$limit = max(1, $limit);

		$groups = [];
		foreach ($this->groupManager->search($query, $limit, 0) as $group) {
			if (!$group instanceof IGroup) {
				continue;
			}

			$groups[] = [
				'id' => $group->getGID(),
				'displayName' => $group->getDisplayName(),
			];
		}

		usort($groups, static fn (array $left, array $right): int => strnatcasecmp($left['displayName'], $right['displayName']));
		return $groups;
	}

	/**
	 * @param list<string> $groupIds
	 * @return array<string, string>
	 */
	public function getGroupDisplayNames(array $groupIds): array {
		$names = [];
		foreach ($groupIds as $groupId) {
			$group = $this->groupManager->get($groupId);
			$names[$groupId] = $group?->getDisplayName() ?? $groupId;
		}

		return $names;
	}

	/**
	 * @param array<string, mixed> $route
	 * @return array<string, mixed>
	 */
	public function normalizeRoute(array $route): array {
		$normalized = [
			'mail_smtpmode' => 'smtp',
			'mail_smtphost' => '',
			'mail_smtpport' => '587',
			'mail_smtpsecure' => '',
			'mail_smtpauth' => true,
			'mail_smtpname' => '',
			'mail_smtppassword' => '',
			'mail_domain' => '',
			'mail_from_address' => '',
			'mail_sendmailmode' => 'smtp',
			'mail_smtpauthtype' => 'LOGIN',
		];

		foreach (self::ROUTE_KEYS as $key) {
			if (!array_key_exists($key, $route)) {
				continue;
			}

			$value = $route[$key];
			if ($key === 'mail_smtpauth') {
				$normalized[$key] = $this->toBool($value);
				continue;
			}

			$normalized[$key] = is_scalar($value) ? (string) $value : '';
		}

		if ($normalized['mail_smtpmode'] === '') {
			$normalized['mail_smtpmode'] = 'smtp';
		}

		if ($normalized['mail_smtpport'] === '') {
			$normalized['mail_smtpport'] = '587';
		}

		if ($normalized['mail_sendmailmode'] === '') {
			$normalized['mail_sendmailmode'] = 'smtp';
		}

		if ($normalized['mail_smtpauthtype'] === '') {
			$normalized['mail_smtpauthtype'] = 'LOGIN';
		}

		return $normalized;
	}

	/**
	 * @param array<string, array<string, mixed>> $routes
	 */
	private function persistRoutes(array $routes): void {
		$json = json_encode($routes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$this->appConfig->setValue(self::APP_ID, 'routes', $json === false ? '{}' : $json);
	}

	private function toBool(mixed $value): bool {
		if (is_bool($value)) {
			return $value;
		}

		if (is_int($value)) {
			return $value === 1;
		}

		if (is_string($value)) {
			return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
		}

		return false;
	}
}
