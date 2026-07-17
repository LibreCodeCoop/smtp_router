<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Config;

use OCA\SmtpRouter\Service\RouteResolver;

class RouteConfig extends \OC\Config {
	public function __construct(
		string $configDir,
		private RouteResolver $routeResolver,
	) {
		parent::__construct($configDir);
	}

	public function getValue($key, $default = null) {
		return $this->getRoutedValue($key, fn () => parent::getValue($key, $default), $default);
	}

	public function getSystemValue($key, $default = null) {
		return $this->getRoutedValue($key, fn () => parent::getSystemValue($key, $default), $default);
	}

	public function getSystemValueString($key, $default = ''): string {
		$value = $this->getRoutedValue($key, fn () => parent::getSystemValueString($key, $default), $default);
		return is_string($value) ? $value : (string) $value;
	}

	public function getSystemValueBool($key, $default = false): bool {
		$value = $this->getRoutedValue($key, fn () => parent::getSystemValueBool($key, $default), $default);
		return (bool) $value;
	}

	public function getSystemValueInt($key, $default = 0): int {
		$value = $this->getRoutedValue($key, fn () => parent::getSystemValueInt($key, $default), $default);
		return (int) $value;
	}

	public function getSystemValueArray($key, $default = []): array {
		$value = $this->getRoutedValue($key, fn () => parent::getSystemValueArray($key, $default), $default);
		return is_array($value) ? $value : $default;
	}

	private function getRoutedValue(string $key, \Closure $fallback, mixed $default): mixed {
		if (!$this->routeResolver->isMailKey($key)) {
			return $fallback();
		}

		$route = $this->routeResolver->resolve();
		if ($route === null) {
			return $fallback();
		}

		$value = $route[$key] ?? null;
		if ($value === null || $value === '') {
			return $fallback();
		}

		return $value;
	}
}
