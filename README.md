# SMTP Router

This companion app routes Nextcloud mail configuration by company or group.

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

## Notes

- This is still a dynamic config override, not a full mailer rewrite.
- If Nextcloud stops reading SMTP values through system config at send time, the approach will need a deeper mailer integration.
