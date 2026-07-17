<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Controller;

use OCA\SmtpRouter\AppInfo\Application;
use OCA\SmtpRouter\Service\RouteService;
use OCP\AppFramework\Attribute\AdminRequired;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class AdminController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private RouteService $routeService,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function index(): TemplateResponse {
		$this->loadAssets();

		return new TemplateResponse(
			Application::APP_ID,
			'admin',
			$this->buildViewData(),
		);
	}

	#[AdminRequired]
	public function saveRoute(): RedirectResponse {
		$routeKey = trim((string) $this->request->getParam('route_key', ''));
		if ($routeKey === '') {
			return $this->redirectBack('Select a group or default route first.', 'error');
		}
		if (!$this->routeService->isValidRouteKey($routeKey)) {
			return $this->redirectBack('Unknown group selected.', 'error');
		}

		$route = [
			'mail_smtpmode' => (string) $this->request->getParam('mail_smtpmode', 'smtp'),
			'mail_smtphost' => (string) $this->request->getParam('mail_smtphost', ''),
			'mail_smtpport' => (string) $this->request->getParam('mail_smtpport', '587'),
			'mail_smtpsecure' => (string) $this->request->getParam('mail_smtpsecure', ''),
			'mail_smtpauth' => $this->request->getParam('mail_smtpauth', '0'),
			'mail_smtpname' => (string) $this->request->getParam('mail_smtpname', ''),
			'mail_smtppassword' => (string) $this->request->getParam('mail_smtppassword', ''),
			'mail_domain' => (string) $this->request->getParam('mail_domain', ''),
			'mail_from_address' => (string) $this->request->getParam('mail_from_address', ''),
			'mail_sendmailmode' => (string) $this->request->getParam('mail_sendmailmode', 'smtp'),
			'mail_smtpauthtype' => (string) $this->request->getParam('mail_smtpauthtype', 'LOGIN'),
		];

		if ($route['mail_smtpmode'] === 'smtp' && trim($route['mail_smtphost']) === '') {
			return $this->redirectBack('SMTP server address is required for SMTP mode.', 'error');
		}

		if ($route['mail_smtpport'] !== '' && !ctype_digit($route['mail_smtpport'])) {
			return $this->redirectBack('SMTP port must contain only digits.', 'error');
		}

		$this->routeService->saveRoute($routeKey, $route);
		return $this->redirectBack('SMTP profile saved.', 'success');
	}

	#[AdminRequired]
	public function deleteRoute(): RedirectResponse {
		$routeKey = trim((string) $this->request->getParam('route_key', ''));
		if ($routeKey === '') {
			return $this->redirectBack('Select a route to delete.', 'error');
		}
		if ($routeKey === 'default') {
			return $this->redirectBack('The default route cannot be deleted.', 'error');
		}
		if (!$this->routeService->isValidRouteKey($routeKey)) {
			return $this->redirectBack('Unknown group selected.', 'error');
		}

		$this->routeService->deleteRoute($routeKey);
		return $this->redirectBack('SMTP profile removed.', 'success');
	}

	private function loadAssets(): void {
		Util::addStyle(Application::APP_ID, 'admin');
		Util::addScript(Application::APP_ID, 'admin');
	}

	/**
	 * @return array<string, mixed>
	 */
	private function buildViewData(): array {
		$routes = $this->routeService->getRoutes();
		$groups = $this->routeService->listGroups();
		$groupNames = [];
		foreach ($groups as $group) {
			$groupNames[$group['id']] = $group['displayName'];
		}

		return [
			'groups' => $groups,
			'routes' => $routes,
			'groupNames' => $groupNames,
			'requesttoken' => \OC::$server->getCsrfTokenManager()->getToken()->getEncryptedValue(),
			'adminPageUrl' => \OC::$server->getURLGenerator()->linkToRoute(Application::APP_ID . '.admin.index'),
		];
	}

	private function redirectBack(string $message, string $type): RedirectResponse {
		$referer = (string) $this->request->getHeader('referer');
		$query = http_build_query([
			'msg' => $message,
			'type' => $type,
		]);

		if ($referer !== '') {
			$separator = str_contains($referer, '?') ? '&' : '?';
			return new RedirectResponse($referer . $separator . $query);
		}

		return new RedirectResponse(\OC::$server->getURLGenerator()->linkToRoute(
			Application::APP_ID . '.admin.index',
			[
				'msg' => $message,
				'type' => $type,
			]
		));
	}
}
