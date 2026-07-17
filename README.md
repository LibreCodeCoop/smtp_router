# SMTP Router

This companion app routes Nextcloud mail configuration by company or group.

## Installation

Install the app in your Nextcloud `apps-extra` directory and enable it:

```bash
occ app:enable smtp_router
```

If you deploy apps from git, keep the repository checked out beside your other custom apps and make sure Nextcloud can load it from the apps directory.

## Goal

- Keep `custom_domain` focused on visual customization and trusted domains.
- Select different SMTP settings based on the current subdomain or the user's group membership.

## How it works

The app decorates Nextcloud config reads for keys that start with `mail_`.
When Nextcloud asks for `mail_smtphost`, `mail_smtpname`, `mail_smtppassword`, `mail_domain`, and related keys, the app returns the values from the active route.

Route selection order:

1. current subdomain if it matches a group
2. current user's group membership
3. `default`

## Suggested setup

Use this app together with `custom_domain`:

- `custom_domain` maps each company subdomain to a group and trusted domain
- `smtp_router` picks the active SMTP profile for that company or group

If a user belongs to a company group, mail sent during that request will use the matching route.
If the request host matches a company subdomain, that route takes priority.

## Configure routes

Store a JSON document in the app config key `smtp_router/routes`.

Example:

```json
{
  "default": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "smtp-default.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "default-user",
    "mail_smtppassword": "secret",
    "mail_domain": "example.com"
  },
  "empresa-a": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "smtp-a.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "empresa-a",
    "mail_smtppassword": "secret-a",
    "mail_domain": "empresa-a.com"
  },
  "empresa-b": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "smtp-b.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "empresa-b",
    "mail_smtppassword": "secret-b",
    "mail_domain": "empresa-b.com"
  }
}
```

Write the config with:

```bash
occ smtp-router:route:set '{
  "default": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "mailcow.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "nextcloud-default",
    "mail_smtppassword": "change-me",
    "mail_domain": "example.com"
  },
  "empresa-a": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "mailcow.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "empresa-a",
    "mail_smtppassword": "change-me-a",
    "mail_domain": "empresa-a.com"
  },
  "empresa-b": {
    "mail_smtpmode": "smtp",
    "mail_smtphost": "mailcow.example.com:587",
    "mail_smtpsecure": "tls",
    "mail_smtpauth": true,
    "mail_smtpname": "empresa-b",
    "mail_smtppassword": "change-me-b",
    "mail_domain": "empresa-b.com"
  }
}'
```

Read it back with:

```bash
occ smtp-router:route:get
```

## Mailcow

For a Mailcow-backed deployment, the practical pattern is:

1. Nextcloud sends mail to Mailcow as the SMTP endpoint.
2. Mailcow accepts the authenticated SMTP session.
3. Mailcow routes the final delivery according to its own relay or transport rules.

Keep one route as `default` so background jobs and generic system mail always have a fallback.

## Notes

- This is still a dynamic config override, not a full mailer rewrite.
- If Nextcloud stops reading SMTP values through system config at send time, the approach will need a deeper mailer integration.

## Validation

After setting routes, test from the Nextcloud host:

```bash
occ smtp-router:route:get --output json_pretty
```

Then trigger a notification or password reset flow from the matching company context and confirm the SMTP session reaches the expected Mailcow account or relay.
