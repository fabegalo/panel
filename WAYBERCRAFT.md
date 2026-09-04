# WayberCraft Panel Fork

This repository is the WayberCraft-maintained fork of the Pterodactyl Panel.
The `upstream` Git remote points to `pterodactyl/panel`, and the `main` branch
contains the code that is built for WayberCraft environments.

## Public SFTP endpoints

Each Node can optionally advertise a customer-facing SFTP hostname and port
that differ from the address Wings uses internally:

- `public_sftp_host` falls back to the Node FQDN when empty.
- `public_sftp_port` falls back to the Wings SFTP port when empty.
- The public values are returned only in the client-facing `sftp_details`.
- The generated Wings configuration always keeps using `daemonSFTP`.

This separation lets HTTPS and WebSocket traffic remain behind Cloudflare
Tunnel while SFTP is forwarded through a TCP-capable public edge.

Run `php artisan migrate --force` during deployment before restarting the Panel
workers.

## Upstream updates

Fetch official changes from `upstream/1.0-develop`, fast-forward the local
`1.0-develop` mirror, and then merge that mirror into a feature branch targeting
`main`. Never push changes to the `upstream` remote.
