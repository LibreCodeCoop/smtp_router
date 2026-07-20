<?php

declare(strict_types=1);

namespace OCA\SmtpRouter\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	public function __construct(
		private IURLGenerator $urlGenerator,
		private IL10N $l10n,
	) {
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'places/settings.svg');
	}

	public function getID(): string {
		return 'smtp_router';
	}

	public function getName(): string {
		return $this->l10n->t('SMTP Router');
	}

	public function getPriority(): int {
		return 10;
	}
}
