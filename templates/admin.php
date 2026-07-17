<?php
/** @var array $_ */
$groups = $_['groups'] ?? [];
$routes = $_['routes'] ?? [];
$groupNames = $_['groupNames'] ?? [];
$requesttoken = $_['requesttoken'] ?? '';
$adminPageUrl = $_['adminPageUrl'] ?? '';
$defaultRoute = $routes['default'] ?? [];
$companyRoutes = array_filter($routes, static fn ($key): bool => $key !== 'default', ARRAY_FILTER_USE_KEY);
$currentMsg = (string)($_GET['msg'] ?? '');
$currentType = (string)($_GET['type'] ?? 'info');
?>

<div class="section smtp-router-section" id="smtp_router_settings" data-smtp-router-routes="<?php p(json_encode($routes, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)); ?>">
	<h2>SMTP Router</h2>
	<p class="settings-hint">
		Configure one SMTP profile per company group. The selected group can use a dedicated sender, host and password.
	</p>

	<?php if ($currentMsg !== ''): ?>
		<div class="smtp-router-notice <?php p($currentType); ?>">
			<?php p($currentMsg); ?>
		</div>
	<?php endif; ?>

	<div class="smtp-router-toolbar">
		<label for="smtp-router-group-select">Group or company</label>
		<select id="smtp-router-group-select">
			<option value="default">Default fallback</option>
			<?php foreach ($groups as $group): ?>
				<option value="<?php p($group['id']); ?>"><?php p($group['displayName'] . ' (' . $group['id'] . ')'); ?></option>
			<?php endforeach; ?>
		</select>
		<button type="button" class="button primary" id="smtp-router-new-button">Configure SMTP</button>
		<?php if ($adminPageUrl !== ''): ?>
			<a class="button" href="<?php p($adminPageUrl); ?>" target="_blank" rel="noreferrer noopener">Open full page</a>
		<?php endif; ?>
	</div>

	<h3>Default route</h3>
	<div class="smtp-router-card">
		<div class="smtp-router-card-header">
			<strong>default</strong>
			<div class="smtp-router-actions">
				<button
					type="button"
					class="button"
					data-smtp-router-edit="default"
					data-smtp-router-title="Edit default SMTP"
					data-smtp-router-route="<?php p(json_encode($defaultRoute, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)); ?>"
				>Edit</button>
			</div>
		</div>
		<div class="smtp-router-card-body">
			<p><strong>Host:</strong> <?php p((string)($defaultRoute['mail_smtphost'] ?? '')); ?></p>
			<p><strong>Port:</strong> <?php p((string)($defaultRoute['mail_smtpport'] ?? '587')); ?></p>
			<p><strong>Security:</strong> <?php p((string)($defaultRoute['mail_smtpsecure'] ?? '')); ?></p>
			<p><strong>User:</strong> <?php p((string)($defaultRoute['mail_smtpname'] ?? '')); ?></p>
			<p><strong>Domain:</strong> <?php p((string)($defaultRoute['mail_domain'] ?? '')); ?></p>
		</div>
	</div>

	<h3>Configured company routes</h3>
	<table class="smtp-router-table">
		<thead>
			<tr>
				<th>Group</th>
				<th>Host</th>
				<th>Port</th>
				<th>Auth</th>
				<th>Domain</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($routes as $routeKey => $route): ?>
				<?php if ($routeKey === 'default') { continue; } ?>
				<tr>
					<td>
						<strong><?php p($groupNames[$routeKey] ?? $routeKey); ?></strong>
						<div class="smtp-router-subtitle"><?php p($routeKey); ?></div>
					</td>
					<td><?php p((string)($route['mail_smtphost'] ?? '')); ?></td>
					<td><?php p((string)($route['mail_smtpport'] ?? '587')); ?></td>
					<td><?php p(!empty($route['mail_smtpauth']) ? 'Yes' : 'No'); ?></td>
					<td><?php p((string)($route['mail_domain'] ?? '')); ?></td>
					<td>
						<div class="smtp-router-actions">
							<button
								type="button"
								class="button"
								data-smtp-router-edit="<?php p($routeKey); ?>"
								data-smtp-router-title="<?php p('Edit SMTP for ' . ($groupNames[$routeKey] ?? $routeKey)); ?>"
								data-smtp-router-route="<?php p(json_encode($route, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)); ?>"
							>Edit</button>
							<form method="post" action="<?php p(\OC::$server->getURLGenerator()->linkToRoute('smtp_router.admin.deleteRoute')); ?>" class="smtp-router-inline-form">
								<input type="hidden" name="requesttoken" value="<?php p($requesttoken); ?>">
								<input type="hidden" name="route_key" value="<?php p($routeKey); ?>">
								<button type="submit" class="button danger">Remove</button>
							</form>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (count($companyRoutes) === 0): ?>
				<tr>
					<td colspan="6">No company routes configured yet.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<dialog id="smtp-router-dialog" class="smtp-router-dialog">
	<form method="post" action="<?php p(\OC::$server->getURLGenerator()->linkToRoute('smtp_router.admin.saveRoute')); ?>" id="smtp-router-form">
		<input type="hidden" name="requesttoken" value="<?php p($requesttoken); ?>">
		<input type="hidden" name="route_key" id="smtp-router-route-key" value="default">
		<h3 id="smtp-router-dialog-title">Configure SMTP</h3>
		<p class="settings-hint">The values stored here override the global Nextcloud mail settings for this group or company.</p>

		<div class="smtp-router-grid">
			<label>
				<span>Group key</span>
				<input type="text" id="smtp-router-route-label" readonly>
			</label>
			<label>
				<span>SMTP mode</span>
				<select name="mail_smtpmode" id="smtp-router-mail-smtpmode">
					<option value="smtp">SMTP</option>
					<option value="sendmail">Sendmail</option>
				</select>
			</label>
			<label>
				<span>Server address</span>
				<input type="text" name="mail_smtphost" id="smtp-router-mail-smtphost" placeholder="smtp.example.com">
			</label>
			<label>
				<span>Port</span>
				<input type="text" name="mail_smtpport" id="smtp-router-mail-smtpport" placeholder="587">
			</label>
			<label>
				<span>Encryption</span>
				<select name="mail_smtpsecure" id="smtp-router-mail-smtpsecure">
					<option value="">None/STARTTLS</option>
					<option value="ssl">SSL</option>
				</select>
			</label>
			<label>
				<span>Authentication</span>
				<select name="mail_smtpauthtype" id="smtp-router-mail-smtpauthtype">
					<option value="LOGIN">Login</option>
				</select>
			</label>
			<label class="smtp-router-checkbox">
				<input type="checkbox" name="mail_smtpauth" id="smtp-router-mail-smtpauth" value="1">
				<span>Authentication required</span>
			</label>
			<label>
				<span>SMTP login</span>
				<input type="text" name="mail_smtpname" id="smtp-router-mail-smtpname" autocomplete="off">
			</label>
			<label>
				<span>SMTP password</span>
				<input type="password" name="mail_smtppassword" id="smtp-router-mail-smtppassword" autocomplete="off">
			</label>
			<label>
				<span>From address</span>
				<input type="text" name="mail_from_address" id="smtp-router-mail-from-address" placeholder="noreply">
			</label>
			<label>
				<span>From domain</span>
				<input type="text" name="mail_domain" id="smtp-router-mail-domain" placeholder="example.com">
			</label>
			<label>
				<span>Sendmail mode</span>
				<select name="mail_sendmailmode" id="smtp-router-mail-sendmailmode">
					<option value="smtp">smtp (-bs)</option>
					<option value="pipe">pipe (-t -i)</option>
				</select>
			</label>
		</div>

		<div class="smtp-router-dialog-actions">
			<button type="button" class="button" id="smtp-router-cancel">Cancel</button>
			<button type="submit" class="button primary">Save</button>
		</div>
	</form>
</dialog>
