(function () {
	'use strict';

	function byId(id) {
		return document.getElementById(id);
	}

	function setFieldValue(id, value) {
		var el = byId(id);
		if (!el) {
			return;
		}

		if (el.type === 'checkbox') {
			el.checked = value === true || value === 1 || value === '1' || value === 'true';
			return;
		}

		el.value = value == null ? '' : String(value);
	}

	function openDialog(routeKey, routeData, title) {
		setFieldValue('smtp-router-route-key', routeKey);
		setFieldValue('smtp-router-route-label', routeKey);
		setFieldValue('smtp-router-mail-smtpmode', routeData.mail_smtpmode || 'smtp');
		setFieldValue('smtp-router-mail-smtphost', routeData.mail_smtphost || '');
		setFieldValue('smtp-router-mail-smtpport', routeData.mail_smtpport || '587');
		setFieldValue('smtp-router-mail-smtpsecure', routeData.mail_smtpsecure || '');
		setFieldValue('smtp-router-mail-smtpauth', routeData.mail_smtpauth || false);
		setFieldValue('smtp-router-mail-smtpname', routeData.mail_smtpname || '');
		setFieldValue('smtp-router-mail-smtppassword', routeData.mail_smtppassword || '');
		setFieldValue('smtp-router-mail-from-address', routeData.mail_from_address || '');
		setFieldValue('smtp-router-mail-domain', routeData.mail_domain || '');
		setFieldValue('smtp-router-mail-sendmailmode', routeData.mail_sendmailmode || 'smtp');
		setFieldValue('smtp-router-mail-smtpauthtype', routeData.mail_smtpauthtype || 'LOGIN');

		var dialogTitle = byId('smtp-router-dialog-title');
		if (dialogTitle) {
			dialogTitle.textContent = title || 'Configure SMTP';
		}

		var dialog = byId('smtp-router-dialog');
		if (dialog && typeof dialog.showModal === 'function') {
			dialog.showModal();
		}
	}

	function debounce(fn, wait) {
		var timeout = null;
		return function () {
			var args = arguments;
			var context = this;
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				fn.apply(context, args);
			}, wait);
		};
	}

	function parseGroupResults(payload) {
		if (!payload) {
			return [];
		}

		if (Array.isArray(payload.groups)) {
			return payload.groups;
		}

		if (payload.data && Array.isArray(payload.data.groups)) {
			return payload.data.groups;
		}

		if (Array.isArray(payload.data)) {
			return payload.data;
		}

		return [];
	}

	function populateGroupSelect(select, groups, selectedValue) {
		if (!select) {
			return;
		}

		while (select.firstChild) {
			select.removeChild(select.firstChild);
		}

		var defaultOption = document.createElement('option');
		defaultOption.value = 'default';
		defaultOption.textContent = 'Default fallback';
		select.appendChild(defaultOption);

		var foundSelected = false;
		groups.forEach(function (group) {
			if (!group || !group.id) {
				return;
			}

			var option = document.createElement('option');
			option.value = group.id;
			option.textContent = (group.displayName || group.id) + ' (' + group.id + ')';
			select.appendChild(option);

			if (group.id === selectedValue) {
				foundSelected = true;
			}
		});

		if (selectedValue && selectedValue !== 'default') {
			select.value = foundSelected ? selectedValue : 'default';
		} else {
			select.value = selectedValue || 'default';
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var dialog = byId('smtp-router-dialog');
		var cancel = byId('smtp-router-cancel');
		var newButton = byId('smtp-router-new-button');
		var groupSelect = byId('smtp-router-group-select');
		var groupSearch = byId('smtp-router-group-search');
		var settingsRoot = byId('smtp_router_settings');
		var routes = {};
		var searchUrl = settingsRoot ? settingsRoot.getAttribute('data-smtp-router-search-url') : '';

		if (settingsRoot) {
			var rawRoutes = settingsRoot.getAttribute('data-smtp-router-routes');
			if (rawRoutes) {
				try {
					routes = JSON.parse(rawRoutes);
				} catch (error) {
					routes = {};
				}
			}
		}

		if (cancel && dialog) {
			cancel.addEventListener('click', function () {
				dialog.close();
			});
		}

		if (newButton && groupSelect) {
			newButton.addEventListener('click', function () {
				var routeKey = groupSelect.value || 'default';
				openDialog(routeKey, routes[routeKey] || {}, 'Configure SMTP');
			});
		}

		var runSearch = debounce(function () {
			if (!groupSearch || !groupSelect || !searchUrl) {
				return;
			}

			var query = groupSearch.value || '';
			var url = new URL(searchUrl, window.location.origin);
			url.searchParams.set('query', query);

			fetch(url.toString(), {
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
				},
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					var selectedValue = groupSelect.value || 'default';
					populateGroupSelect(groupSelect, parseGroupResults(payload), selectedValue);
				})
				.catch(function () {
				});
		}, 250);

		if (groupSearch) {
			groupSearch.addEventListener('input', runSearch);
			runSearch();
		}

		document.querySelectorAll('[data-smtp-router-edit]').forEach(function (button) {
			button.addEventListener('click', function () {
				var routeKey = button.getAttribute('data-smtp-router-edit') || 'default';
				var routeData = {};
				var raw = button.getAttribute('data-smtp-router-route');
				if (raw) {
					try {
						routeData = JSON.parse(raw);
					} catch (error) {
						routeData = {};
					}
				}
				openDialog(routeKey, routeData, button.getAttribute('data-smtp-router-title') || 'Configure SMTP');
			});
		});
	});
})();
