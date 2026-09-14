# StoreZ — Complete Deployment Guide

Deploying StoreZ to the Hostinger VPS at `grameenhalchal.com`, alongside the
applications already running there.

Every command is written out. Each one says what it does, what you should see,
and what to do if it goes wrong. Work top to bottom and do not skip ahead —
later steps assume earlier ones succeeded.

**Time required:** about 60–90 minutes, plus DNS propagation (usually under an
hour, occasionally longer).

---

## Contents

| Part | What it covers | Touches shared infrastructure? |
|---|---|---|
| 0 | Before you start — conventions and the warning you will see | — |
| 1 | What StoreZ shares with your other apps | Read only |
| 2 | Pre-flight checks | Read only, plus three optional fixes |
| 3 | Close the PostgreSQL internet exposure | **Yes** — firewall + PostgreSQL |
| 4 | Create the database | Yes — PostgreSQL (additive) |
| 5 | Get the code onto the server | No |
| 6 | Configure the environment | No |
| 7 | First boot | No |
| 8 | Point the domain at the server (Namecheap) | No |
| 9 | Host nginx and HTTPS | **Yes** — nginx |
| 10 | Verify everything works | Read only |
| 11 | Stripe payments | No |
| 12 | Email | No |
| 13 | Day-to-day operations | No |
| 14 | Troubleshooting | — |

---

## Part 0 — Before you start

### What you need

- SSH access to the VPS as `root` (or a user with `sudo`).
- The Namecheap login for `grameenhalchal.com`.
- About an hour of uninterrupted time.

### Conventions in this guide

Commands prefixed `$` run **on the VPS**. Lines starting `#` are comments, not
commands. Placeholders look like `<this>` and must be replaced.

After most commands there is an **Expected output** block. If what you see
differs materially, stop and check Part 14 before continuing.

### A warning you will see constantly — it is harmless

Every `sudo -u postgres` command prints this when run from `/root`:

```
could not change directory to "/root": Permission denied
```

**The command still works.** `sudo -u postgres` switches to the `postgres` user
but stays in the current directory, and `postgres` cannot read root's home
folder. The warning is cosmetic.

To avoid seeing it, switch to a directory everyone can read before you start:

```bash
cd /tmp
```

Do that now. Parts 3 and 4 assume you are in `/tmp`. Part 5 onward will tell you
when to change directory.

### If you need to stop partway

Parts 1 and 10 are read-only, and so is Part 2 apart from three optional fixes
it may tell you to apply (adding swap, opening ports 80/443, installing
certbot). Parts 3, 4 and 9
change shared infrastructure and each has an explicit rollback. Parts 5–8 only
affect StoreZ's own files. It is safe to stop between any two parts.

---

## Part 1 — What StoreZ shares with your other apps

Your server runs WordPress (host nginx + host PHP-FPM), Next.js on port 3000,
Uvicorn on 8000, a Node EMS backend on 5588, and the racolearnhub Docker stack
on 8088. Several use the host PostgreSQL with their own databases.

Here is precisely what StoreZ touches.

| Resource | What StoreZ does | Effect on your other apps |
|---|---|---|
| **Ports** | Binds `127.0.0.1:8092` only | **None.** Verified free; nothing published publicly |
| **PHP + extensions** | PHP 8.4, GD, redis — all *inside* the container | **None.** This is why we containerised: the host needs no PHP for StoreZ, so WordPress's PHP version, extensions and `php.ini` are untouched |
| **MySQL / MariaDB** | Never contacted; `pdo_mysql` isn't compiled in | **None.** WordPress's database is unreachable from StoreZ |
| **PostgreSQL** | Own database, own non-superuser role | **Data fully isolated.** Connection pool and RAM are shared — see Risk 2 |
| **Redis** | Its own container, no published port | **None.** `racolearnhub-redis` is never touched |
| **Node / Python** | Only at image build time, inside the container | **None** |
| **nginx** | Adds one vhost file | Config is separate, but **the reload is shared** — see Risk 1 |
| **Disk / RAM** | ~940 MB image plus volumes | Shared — checked in Part 2 |

**Why a shared PostgreSQL is safe.** Your other apps already do exactly this:
one instance, separate databases. PostgreSQL isolates databases from one
another — a non-superuser role connected to `storez` cannot read the *data* in
any other database. Part 4 creates the role without superuser rights and revokes
the default public grant. Part 10.6 includes a test that proves this, and is
precise about the one thing the role can still see.

### Risk 1 — the nginx reload is shared

Adding StoreZ's vhost means reloading the nginx that also serves WordPress.
A broken config would affect every site. Three rules make this safe, and Part 9
follows all three:

- **Always run `sudo nginx -t` before reloading.** It validates *every* vhost,
  so a StoreZ mistake shows up there instead of taking sites down.
- **Use `reload`, never `restart`.** Reload is graceful — open connections
  finish. Restart drops every connection on the server.
- **Back up the config first**, so you can always return to a working state.

### Risk 2 — shared PostgreSQL capacity

StoreZ adds up to about 13 connections (12 PHP workers plus the scheduler) to a
pool your other apps also use. Part 2 checks there is room.

---

## Part 2 — Pre-flight checks

Every check here reads, it does not change. Three of them may tell you to apply
a fix — adding swap in 2.6, opening ports 80/443 in 2.10, installing certbot in
2.11 — and those are worth doing now rather than discovering the need half way
through Part 6 or Part 9. Run every check and note the answers; a couple decide
what you do in Part 3.

### 2.1 Confirm you are on the right server

```bash
hostname
```
**Expected:** `srv1198447`

### 2.2 Record the public IP address

```bash
curl -4 -s ifconfig.me; echo
```
**Expected:** four numbers, e.g. `72.60.108.22`.
**Write this down** — you need it for DNS in Part 8.

### 2.3 Confirm Docker is working

```bash
docker --version
docker compose version
docker ps
```
**Expected:** version strings, then a table listing `racolearnhub-app`,
`racolearnhub-redis`, `racolearnhub-db`.
**If `docker ps` says permission denied:** you are not root — prefix with `sudo`
or run `sudo usermod -aG docker $USER` and log out and back in.

### 2.4 Confirm port 8092 is free

```bash
ss -ltnp | grep -E ':8092\b' || echo "8092 is FREE"
```
**Expected:** `8092 is FREE`. Port 8090 being in use does not matter — StoreZ
never touches it.
**If 8092 is taken:** pick another free port. You will set it in Part 6 and
Part 9; nothing needs rebuilding.

### 2.5 Check PostgreSQL connection headroom

```bash
sudo -u postgres psql -c "SHOW max_connections;"
sudo -u postgres psql -c "SELECT count(*) AS in_use FROM pg_stat_activity;"
```
**Expected:** typically `100` and a small number.
**Do the arithmetic:** if `in_use + 13` is close to `max_connections`, reduce
`pm.max_children` in `docker/prod/php-fpm.conf` from 12 to something smaller
before Part 7. Do **not** raise `max_connections` — that needs a PostgreSQL
restart, which would drop your other apps' connections.

### 2.6 Check memory and disk

```bash
free -h
swapon --show
df -h /
docker system df
```
**Expected:** at least ~2 GB available memory and ~5 GB free disk.
StoreZ's ceiling is roughly (PHP workers × 512 MB) + 256 MB for Redis, and the
image is about 940 MB.

**If `swapon --show` prints nothing and available memory is under ~2.5 GB:** add
swap now, before the image build in Part 6.2. That build runs `composer install`,
`npm ci` and `npm run build` back to back, and the Node step is the memory peak.
Without headroom the kernel kills it and the build fails with exit code 137 (see
Part 14.1).

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```
**Expected:** `swapon --show` now lists `/swapfile`.

### 2.7 See who connects to PostgreSQL and from where

**This one decides Part 3.3.** Write the result down.

```bash
sudo -u postgres psql -c "
  SELECT datname, usename, client_addr, count(*)
  FROM pg_stat_activity WHERE client_addr IS NOT NULL
  GROUP BY 1,2,3 ORDER BY 1;"
```
**Expected:** a short table, or no rows.
**What matters:** the `client_addr` column. Are all the values `127.0.0.1`,
`::1`, or `172.x.x.x`? Or is there a public/LAN address?

### 2.8 List existing databases and roles

```bash
sudo -u postgres psql -c "\l"
sudo -u postgres psql -c "\du"
```
**Expected:** your other apps' databases. Confirm there is no `storez` database
or role yet. (If there is, you have run Part 4 before — skip it.)

### 2.9 Check whether PostgreSQL has been probed from the internet

```bash
sudo grep -ciE 'password authentication failed|no pg_hba.conf entry' \
    /var/log/postgresql/postgresql-*.log
```
**Expected:** ideally `0` or a small number.
**If it is in the hundreds or thousands:** the instance has been scanned from
the internet. Complete Part 3, then rotate every role password on the instance.

### 2.10 Look at the current firewall and nginx setup

```bash
sudo ufw status verbose
ls -la /etc/nginx/sites-enabled/
grep -rh "server_name" /etc/nginx/sites-enabled/ | sort -u
```
**Expected:** the ufw rules you saw before (including `5432/tcp ALLOW IN
Anywhere`), your enabled sites, and their domains.
**Check:** `grameenhalchal.com` must **not** already appear in the
`server_name` list. If it does, an existing vhost claims it — resolve that
before Part 9.

**Also check:** ports **80** and **443** must both be allowed through ufw. They
may appear as `80/tcp` and `443/tcp`, or bundled in an `Nginx Full` profile.
Let's Encrypt validates over port 80 in Part 9.4 and fails without it — and each
failure counts against a rate limit.

```bash
sudo ufw status | grep -E '\b(80|443)\b|Nginx' \
    || echo ">>> 80/443 NOT allowed - fix this before Part 9"
```
**If they are missing:**
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### 2.11 Confirm certbot is installed

Part 9.4 needs certbot and its nginx plugin. Discovering they are absent at that
point means stopping half way through Part 9, with DNS already pointed at this
server.

```bash
certbot --version
dpkg -l | grep -c python3-certbot-nginx
```
**Expected:** a version string such as `certbot 2.x.x`, then `1`.
**If either is missing:**
```bash
sudo apt update && sudo apt install -y certbot python3-certbot-nginx
```

---

## Part 3 — Close the PostgreSQL internet exposure

**Do this even if you never deploy StoreZ.** Your firewall currently allows
port 5432 from anywhere and PostgreSQL listens on all interfaces, so the
database login prompt is reachable from the public internet.

There are **two independent actions**. The first is always safe. The second
depends on what you saw in check 2.7.

### 3.1 Remove the firewall rule (always safe)

Nothing on this server needs PostgreSQL from the public internet — your apps
are all local, and StoreZ reaches it over Docker's internal bridge.

```bash
sudo ufw delete allow 5432/tcp
```
**Expected:** `Rule deleted` (possibly twice, for IPv4 and IPv6).

Confirm it is gone:

```bash
sudo ufw status | grep 5432 || echo "5432 no longer allowed through the firewall"
```
**Expected:** the "no longer allowed" message.

**Rollback**, if you ever need it back:
```bash
sudo ufw allow 5432/tcp
```

### 3.2 Verify your apps still work

```bash
sudo -u postgres psql -c "SELECT datname, count(*) FROM pg_stat_activity GROUP BY 1;"
```
Then load each of your sites in a browser. Everything local is unaffected by a
firewall rule, so this should all be normal.

### 3.3 Narrow the listener — only if check 2.7 allows

This is defense in depth. **It can break your other applications** if any of
them connect from an address you are about to exclude. Use your 2.7 result:

| What check 2.7 showed | What to do |
|---|---|
| Only `127.0.0.1`, `::1`, `172.x` addresses | Safe — continue with 3.3 |
| **Any** public or LAN address | **Stop. Skip 3.3 entirely.** Step 3.1 already closed the exposure. Investigate that client first |
| No rows at all | Apps may use unix sockets (unaffected by this setting) or connect only occasionally. Re-run 2.7 a few times over a day before deciding. **When unsure, skip 3.3** — 3.1 is the important part |

Skipping 3.3 is a perfectly good outcome. The firewall rule was the real
exposure.

> **On this server, skip 3.3.** `pg_hba.conf` contains
> `host beast_agent beast_agent 172.16.0.0/12 scram-sha-256` — another
> containerised app reaching this PostgreSQL over the Docker bridge — and
> `host all all 82.112.235.65/32 md5`, a public address. Narrowing
> `listen_addresses` risks breaking both. Step 3.1 already removed the internet
> exposure, which was the point.

If and only if the table says continue:

```bash
# Back up first
sudo cp /etc/postgresql/*/main/postgresql.conf /root/postgresql.conf.bak

# Restrict to localhost plus the Docker bridge
sudo sed -i "s/^#\?listen_addresses.*/listen_addresses = 'localhost,172.17.0.1'/" \
    /etc/postgresql/*/main/postgresql.conf

# Reload - never restart, which would drop your other apps' connections
sudo systemctl reload postgresql
```

Confirm:
```bash
ss -tulpn | grep 5432
```
**Expected:** `127.0.0.1:5432` and `172.17.0.1:5432`, no `0.0.0.0:5432`.

**Now test your other applications** — load every site, and check connections:
```bash
sudo -u postgres psql -c "SELECT datname, count(*) FROM pg_stat_activity GROUP BY 1;"
```

**Rollback if anything broke:**
```bash
sudo cp /root/postgresql.conf.bak /etc/postgresql/*/main/postgresql.conf
sudo systemctl reload postgresql
```

> **Also worth reviewing later:** ports **8000** (Uvicorn) and **5588** (Node
> EMS) are likewise open to the internet and bound to all interfaces. If they
> are only meant to be reached through nginx, they have the same exposure and
> the same fix. Outside this guide's scope.

---

## Part 4 — Create the database

Same pattern your other apps already use: a dedicated database with a dedicated
non-superuser role.

> **You may have already done 4.1 and 4.2.** If you ran them and saw the
> `could not change directory to "/root"` warning, they **succeeded** — verify
> with 4.4 and move on.

### 4.1 Create the role

```bash
cd /tmp
sudo -u postgres createuser --pwprompt storez
```

It prompts twice for a password. **Choose a strong one and save it** — it goes
into `.env` in Part 6.

If it also asks about superuser / createdb / createrole, answer **n** to all
three. StoreZ needs none of them, and refusing is what keeps it out of your
other databases.

**Expected:** no output (the directory warning is fine).
**If you see `role "storez" already exists`:** it is already created. Continue.

### 4.2 Create the database

```bash
sudo -u postgres createdb --owner=storez storez
```
**Expected:** no output.
**If you see `database "storez" already exists`:** already done. Continue.

### 4.3 Revoke the default public grant

```bash
sudo -u postgres psql -c "REVOKE ALL ON DATABASE storez FROM PUBLIC;"
```
**Expected:** `REVOKE`

### 4.4 Verify the role and database

```bash
sudo -u postgres psql -c "\du storez"
sudo -u postgres psql -l | grep storez
```
**Expected:** the `storez` role with an **empty** attributes column (no
"Superuser", no "Create DB"), and the `storez` database owned by `storez`.

**If the attributes column shows Superuser:** fix it, or the isolation
guarantee does not hold:
```bash
sudo -u postgres psql -c "ALTER ROLE storez NOSUPERUSER NOCREATEDB NOCREATEROLE;"
```

### 4.5 Confirm the Docker address range

One detail worth being clear about: the container reaches PostgreSQL from its
address on the **compose network** (`storez_default`), not from the `docker0`
bridge. Those are different subnets. Both sit inside `172.16.0.0/12`, which is
the range 4.6 uses, so a single rule covers either.

```bash
ip addr show docker0 | grep 'inet '
```
**Expected:** something like `inet 172.17.0.1/16` — inside `172.16.0.0/12`.

4.6 needs adjusting only if Docker on this host has been given a custom address
pool outside `172.16.0.0/12`, which is unusual. After Part 7 you can see the
container's real source subnet:

```bash
docker network inspect storez_default \
    -f '{{range .IPAM.Config}}{{.Subnet}}{{end}}'
```

### 4.6 Allow the container to connect

Find your config file:
```bash
ls /etc/postgresql/*/main/pg_hba.conf
```

**On this server it is PostgreSQL 14**, so:
```bash
sudo nano /etc/postgresql/14/main/pg_hba.conf
```

Add this line **at the bottom of the file**:

```
host    storez    storez    172.16.0.0/12    scram-sha-256
```

You will see an existing line in the same style:

```
host    beast_agent    beast_agent    172.16.0.0/12    scram-sha-256
```

That is another containerised app reaching this same PostgreSQL over the Docker
bridge — exactly what StoreZ is about to do. Put the `storez` line next to it.

**About placement.** PostgreSQL uses the *first matching* rule, so a broader
rule placed earlier can shadow a later one. On this server the only broad rule
is `host all all 82.112.235.65/32`, which matches a single public IP and
therefore cannot shadow a `172.16.0.0/12` rule. Appending at the bottom is
correct here. (On a server with a `host all all 0.0.0.0/0` line, the `storez`
rule would need to go *above* it.)

Save and exit (in nano: `Ctrl+O`, `Enter`, `Ctrl+X`).

This grants the `storez` role access to the `storez` database only, from the
Docker bridge only. It changes nothing about how your other apps authenticate.

> **Unrelated, but worth reviewing:** this file also contains
> `host all all 82.112.235.65/32 md5`, which lets **any role reach any
> database** from that one public IP, using the weaker `md5` method. If that is
> your own admin machine, fine. If you do not recognise the address,
> investigate it — it is a more direct route in than the open port you closed
> in Part 3. Changing it is outside this guide's scope.

### 4.7 Apply and verify

```bash
sudo systemctl reload postgresql
sudo systemctl status postgresql --no-pager | head -5
```
**Expected:** `active (exited)` or `active (running)`.

Confirm your other apps still work — load each site.

**If PostgreSQL fails to reload,** you have a syntax error in `pg_hba.conf`:
```bash
sudo tail -20 /var/log/postgresql/postgresql-*.log
```
Fix the line, or remove it and reload again.

---

## Part 5 — Get the code onto the server

### 5.1 Push your local changes first

The deployment files must be in the GitHub repository. **On your local
machine** (not the VPS):

```bash
cd /Users/sajidmahmud/Documents/storez
git status
```

You should see the modified and new deployment files. Commit and push them:

```bash
git add .
git commit -m "Add production Docker deployment"
git push origin main
```

### 5.2 Create the directory and clone

Back **on the VPS**:

```bash
sudo mkdir -p /var/www/storez
sudo chown "$USER":"$USER" /var/www/storez
git clone https://github.com/IgnatiousR/storez.git /var/www/storez
cd /var/www/storez
```
**Expected:** clone progress, ending in `done.`

> From here on, **all commands run from `/var/www/storez`** unless stated
> otherwise. The `docker compose` commands will not work from anywhere else.

**If the repository is private,** use a personal access token:
`git clone https://<username>:<token>@github.com/IgnatiousR/storez.git /var/www/storez`

### 5.3 Confirm the deployment files arrived

```bash
ls -la Dockerfile docker-compose.prod.yml .env.production.example DEPLOYMENT.md
ls -la docker/prod/
```
**Expected:** all present, and `docker/prod/` containing `entrypoint.sh`,
`nginx.conf`, `php.ini`, `php-fpm.conf`, `supervisord.conf`, and
`grameenhalchal.com.conf`.

This guide is in the repository too, so Parts 13 and 14 are readable on the
server itself — which is where you will want them.
**If any are missing:** the push in 5.1 did not include them. Check
`git log --oneline -3` on both machines.

---

## Part 6 — Configure the environment

### 6.1 Create your .env from the template

```bash
cd /var/www/storez
cp .env.production.example .env
```

**`.env` is never committed to git and never baked into the image.** It is read
from this directory each time the container starts, so you can change it and
restart without rebuilding.

### 6.2 Build the image

This takes 3–10 minutes the first time (it installs PHP extensions, Composer
packages and builds the frontend).

```bash
docker compose -f docker-compose.prod.yml build app
```
**Expected:** a long build log ending in something like
`naming to docker.io/library/storez-app:prod done`.
**If it fails:** see Part 14.1.

### 6.3 Generate the application key

```bash
docker compose -f docker-compose.prod.yml run --rm --no-deps app \
    php artisan key:generate --show
```
**Expected:** a single line like `base64:X5GD24DJnrpMq...`

**Copy that entire string including the `base64:` prefix.** Do not lose it —
changing `APP_KEY` later invalidates every session and every encrypted value in
the database.

### 6.4 Edit the configuration

```bash
nano .env
```

Fill in these four values:

| Setting | What to enter |
|---|---|
| `APP_KEY=` | the `base64:...` string from 6.3 |
| `DB_PASSWORD=` | the PostgreSQL password you chose in Part 4.1 |
| `ADMIN_PASSWORD=` | a strong password for your admin login |
| `RUN_SEEDERS=` | set to `true` — **first boot only** |

Also check `ADMIN_EMAIL` — it defaults to `admin@grameenhalchal.com` and
becomes your login username.

> **Get `ADMIN_EMAIL` right now, not later.** The seeder matches the admin user
> on this address (`database/seeders/DatabaseSeeder.php`). Changing it after the
> first seeded boot does not rename the account — it creates a *second* admin.
> Re-running the seeders also resets that user's password back to whatever
> `ADMIN_PASSWORD` says.

If port 8092 was taken in check 2.4, change `APP_BIND_PORT` here too.

Everything else is already correct. In particular, leave these alone:
`APP_URL` (drives every generated link and image URL), `DB_HOST`
(`host.docker.internal` is how the container reaches your host PostgreSQL), and
`CACHE_STORE=redis` (the scheduler requires it).

Save and exit: `Ctrl+O`, `Enter`, `Ctrl+X`.

### 6.5 Protect the file

It contains your database password:

```bash
chmod 600 .env
ls -la .env
```
**Expected:** `-rw-------`

### 6.6 Check for missing values

```bash
grep -E '^(APP_KEY|DB_PASSWORD|ADMIN_PASSWORD)=$' .env && \
    echo ">>> ONE OR MORE VALUES ARE STILL EMPTY - go back to 6.4" || \
    echo "All three secrets are filled in"
```
**Expected:** `All three secrets are filled in`

The fourth value, `RUN_SEEDERS`, is never blank, so that grep cannot catch it.
Check it on its own — it must be `true` for the first boot, and only the first:

```bash
grep RUN_SEEDERS .env
```
**Expected:** `RUN_SEEDERS=true`

---

## Part 7 — First boot

### 7.1 Start the stack

```bash
cd /var/www/storez
docker compose -f docker-compose.prod.yml up -d
```
**Expected:** `Container storez-redis Started` then `Container storez-app Started`.

### 7.2 Watch it start up

```bash
docker compose -f docker-compose.prod.yml logs -f app
```

You should see, in order:

```
[entrypoint] Preparing storage directories
[entrypoint] Linking public/storage -> storage/app/public
[entrypoint] Waiting for database at host.docker.internal:5432
[entrypoint] Database is reachable
[entrypoint] Caching config, views and events
[entrypoint] Running migrations
   INFO  Running migrations.
[entrypoint] Running seeders
   INFO  Seeding database.
[entrypoint] Seeding complete - set RUN_SEEDERS=false before the next deploy
[entrypoint] Starting supervisord (php-fpm, nginx, scheduler)
... success: php-fpm entered RUNNING state
... success: nginx entered RUNNING state
... success: scheduler entered RUNNING state
```

Seeding takes a few minutes — it converts ~83 demo images to WebP, 58 of them
product and variant photos.

Press `Ctrl+C` to stop following the log (this does **not** stop the container).

**If it stops at "Waiting for database":** see Part 14.2 — the usual cause is
the `pg_hba.conf` line from 4.6.

### 7.3 Turn seeding off

Now that the catalog and admin user exist:

```bash
sed -i 's/^RUN_SEEDERS=true/RUN_SEEDERS=false/' .env
grep RUN_SEEDERS .env
```
**Expected:** `RUN_SEEDERS=false`

(The seeders are idempotent, so re-running them would not duplicate data — this
just avoids slowing every future boot.)

Apply it:
```bash
docker compose -f docker-compose.prod.yml up -d
```

### 7.4 Confirm the container is healthy

```bash
docker compose -f docker-compose.prod.yml ps
```
**Expected:** `storez-app` showing `Up ... (healthy)` and `storez-redis`
showing `Up ... (healthy)`.

If it says `(health: starting)`, wait 60 seconds and check again.

### 7.5 Test it directly, before involving nginx

```bash
curl -fsS http://127.0.0.1:8092/up && echo " <- health endpoint OK"
curl -s -o /dev/null -w "homepage HTTP %{http_code}, %{size_download} bytes\n" \
    http://127.0.0.1:8092/
```
**Expected:** `{"status":"ok"...}` then `homepage HTTP 200, ~160000 bytes`.

A large byte count means the seeded catalog is rendering.

**This is an important checkpoint:** the application works. Everything from here
is about making it reachable from the internet. If this step fails, the problem
is inside the container — nothing on your host has changed yet.

---

## Part 8 — Point the domain at the server (Namecheap)

Do this **before** requesting a certificate. Let's Encrypt verifies ownership by
looking up your domain over public DNS, and failed attempts count against a rate
limit of 5 failures per hostname per hour.

### 8.1 Have your server IP ready

From check 2.2. If you did not write it down:

```bash
curl -4 -s ifconfig.me; echo
```

### 8.2 Open the DNS settings

1. Sign in at **namecheap.com**.
2. Left sidebar → **Domain List**.
3. Find `grameenhalchal.com` → click **Manage**.
4. On the **Domain** tab, check the **Nameservers** section reads
   **Namecheap BasicDNS**.
   - If it says **Custom DNS**, your records are managed elsewhere (e.g.
     Cloudflare) and this section does not apply — add the same two A records
     wherever that is.
5. Click the **Advanced DNS** tab.

### 8.3 Delete the parking records

Under **Host Records** you will likely see defaults Namecheap adds:

| Type | Host | Value |
|---|---|---|
| CNAME Record | `www` | `parkingpage.namecheap.com.` |
| URL Redirect Record | `@` | `http://www.grameenhalchal.com/` |

**Delete both** using the trash icon on the right.

> Leaving that `www` CNAME is the single most common reason the certificate
> fails for `www.grameenhalchal.com`.

### 8.4 Add two A records

Click **ADD NEW RECORD** twice:

| Type | Host | Value | TTL |
|---|---|---|---|
| A Record | `@` | *your server IP* | Automatic |
| A Record | `www` | *your server IP* | Automatic |

`@` means the bare domain. Enter the IP address only — no `http://`, no
trailing slash.

### 8.5 Save

Click the **green checkmark** at the right of each row. **Namecheap does not
autosave** — if you navigate away without clicking it, the record is lost.

You should end with exactly two A records and no CNAME for `www`.

### 8.6 Wait, then verify

Check from the VPS:

```bash
dig +short grameenhalchal.com @8.8.8.8
dig +short www.grameenhalchal.com @8.8.8.8
```
**Expected:** both return your server's IP.

**Both must return the correct IP before Part 9.3.** BasicDNS usually
propagates within an hour; the formal window is up to 48 hours. Re-run the
commands every few minutes.

**If they return nothing:** the records did not save, or propagation is still in
progress. Re-check the Advanced DNS page.
**If they return a different IP:** an old record is still present — look for a
leftover A or CNAME record for the same host.

---

## Part 9 — Host nginx and HTTPS

This is the only part that touches infrastructure shared with WordPress and
your other sites. Follow it in order.

### 9.1 Back up and survey

```bash
sudo tar czf /root/nginx-backup-$(date +%F).tar.gz \
    /etc/nginx/sites-available /etc/nginx/sites-enabled
ls -la /root/nginx-backup-*.tar.gz
```
**Expected:** the archive exists. This is your safety net for the rest of Part 9.

```bash
ls -la /etc/nginx/sites-enabled/
grep -rh "server_name" /etc/nginx/sites-enabled/ | sort -u
```
**Check:** `grameenhalchal.com` must not already appear. If it does, two vhosts
would claim the same name and nginx would ignore one — resolve that first.

### 9.2 Install the vhost

```bash
cd /var/www/storez

sudo cp docker/prod/grameenhalchal.com.conf \
        /etc/nginx/sites-available/grameenhalchal.com.conf

sudo ln -s /etc/nginx/sites-available/grameenhalchal.com.conf \
           /etc/nginx/sites-enabled/grameenhalchal.com.conf
```

If you changed the port in Part 6, change it here too:
```bash
sudo nano /etc/nginx/sites-available/grameenhalchal.com.conf
# edit the proxy_pass line: http://127.0.0.1:<your port>;
```

**Validate before reloading.** This checks *every* vhost on the server:

```bash
sudo nginx -t
```
**Expected:**
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

**Do not continue unless you see both lines.** If it reports an error, fix it —
or remove the symlink (`sudo rm /etc/nginx/sites-enabled/grameenhalchal.com.conf`)
and re-test — before going near `reload`.

Only now:
```bash
sudo systemctl reload nginx
```
**Expected:** no output. (Reload is graceful; `restart` would drop connections
across every site.)

### 9.3 Confirm HTTP works, and your other sites still do

```bash
curl -I http://grameenhalchal.com
```
**Expected:** `HTTP/1.1 200 OK`.

**Now load your other sites in a browser** — WordPress, the Next.js app, and so
on. They must all still work.

**Rollback if anything is wrong:**
```bash
sudo rm /etc/nginx/sites-enabled/grameenhalchal.com.conf
sudo nginx -t && sudo systemctl reload nginx
```

### 9.4 Get the HTTPS certificate

Only once Part 8.6 shows correct DNS **and** 9.3 returned 200.

```bash
sudo certbot --nginx -d grameenhalchal.com -d www.grameenhalchal.com
```

It will ask for:
- **An email address** — for expiry warnings.
- **Terms of service** — accept.
- **Redirect HTTP to HTTPS?** — choose **2 (Redirect)**.

**Expected:** `Congratulations! You have successfully enabled HTTPS`.

Certbot **rewrites your vhost in place** to add the certificate and the
redirect. Your 9.1 backup covers a bad rewrite.

**If certbot fails:** see Part 14.4. Do not retry blindly — repeated failures
hit the rate limit.

### 9.5 Confirm renewal is healthy

Certificates last 90 days and renew automatically. That renewal reloads nginx,
which now affects one more site — so check it works:

```bash
sudo certbot renew --dry-run
systemctl list-timers | grep certbot
```
**Expected:** `Congratulations, all simulated renewals succeeded`, and a timer
scheduled.

---

## Part 10 — Verify everything works

### 10.1 HTTPS and the redirect

```bash
curl -I https://grameenhalchal.com
curl -I http://grameenhalchal.com
```
**Expected:** `HTTP/2 200` for the first; `301` with a `location: https://...`
header for the second.

### 10.2 Confirm secure URLs are generated

```bash
curl -s https://grameenhalchal.com | grep -c 'http://grameenhalchal'
```
**Expected:** `0`

This proves the `TrustProxies` setting is reading `X-Forwarded-Proto` correctly.
Any other number means the app is emitting insecure links — check the
`proxy_set_header` lines in the vhost.

```bash
curl -s https://grameenhalchal.com | grep -c 'https://grameenhalchal'
```
**Expected:** a number in the dozens.

### 10.3 Confirm uploaded images are served

```bash
docker compose -f docker-compose.prod.yml exec app ls -la public/storage
```
**Expected:** a symlink `public/storage -> /var/www/html/storage/app/public`.

```bash
IMG=$(docker compose -f docker-compose.prod.yml exec -T app \
    sh -c 'find storage/app/public -name "*.webp" | head -1 | sed "s|storage/app/public/||"' | tr -d '\r')
echo "Testing: /storage/$IMG"
curl -s -o /dev/null -w "HTTP %{http_code}, type %{content_type}\n" \
    "https://grameenhalchal.com/storage/$IMG"
```
**Expected:** `HTTP 200, type image/webp`

### 10.4 Confirm the background scheduler

```bash
docker compose -f docker-compose.prod.yml exec app supervisorctl status
docker compose -f docker-compose.prod.yml exec app php artisan schedule:list
```
**Expected:** `nginx`, `php-fpm` and `scheduler` all `RUNNING`; the schedule
listing `content:sync-scheduled` running hourly.

### 10.5 Browser tests

1. Open `https://grameenhalchal.com` — padlock shown, **product images
   visible**.
2. Go to `/login`, sign in with your `ADMIN_EMAIL` and `ADMIN_PASSWORD`.
3. Go to `/admin` — the dashboard loads.
4. Go to **Admin → Media** and **upload an image**.

That last step is the most valuable single test: it exercises image
transcoding, the storage volume, the symlink and the upload size limit at once.

### 10.6 Confirm nothing else regressed

```bash
# Your other containers - uptime should be unchanged
docker ps

# All databases still present
sudo -u postgres psql -l

# Connection counts are sane now StoreZ has joined
sudo -u postgres psql -c "SELECT datname, count(*) FROM pg_stat_activity GROUP BY 1;"
```

**Prove the database isolation holds.** Be precise about what is being tested
here, because the obvious test gives the wrong answer.

PostgreSQL grants `CONNECT` on every database to `PUBLIC` by default, and any
connected role can read the system catalog. So the `storez` role *can* open a
connection to another app's database, and `\dt` there *will* list table names.
That is not a data leak, and it is not what protects you. What protects you is
table-level privilege: the role was granted nothing on those tables.

So test reading actual data. Pick any table name from another app's database:

```bash
PGPASSWORD='<your storez password>' psql -h 127.0.0.1 -U storez \
    -d <another-app-database> \
    -c 'SELECT * FROM <a-table-in-that-database> LIMIT 1;'
```
**Expected:** `ERROR: permission denied for table <a-table-in-that-database>` —
that is the correct, desired result.

**If it returns rows instead:** the role has more privilege than it should.
Re-run the `ALTER ROLE` from 4.4, then check what that table grants to `PUBLIC`.

**If it fails with `no pg_hba.conf entry for host "127.0.0.1"`:** the test never
ran, and that is evidence of nothing either way — your `pg_hba.conf` simply has
no local TCP rule for this role. Run it from the container instead, which goes
over the path the 4.6 rule governs:

```bash
docker compose -f docker-compose.prod.yml exec app php -r '
  try {
    $d = new PDO("pgsql:host=host.docker.internal;dbname=<another-app-database>",
        getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    var_dump($d->query("SELECT * FROM <a-table-in-that-database> LIMIT 1")->fetchAll());
  } catch (Throwable $e) { echo $e->getMessage(), "\n"; }'
```
**Expected:** a `permission denied for table ...` message.

**Optional hardening.** To stop `storez` connecting to the other databases at
all, revoke the default grant on each one:

```bash
sudo -u postgres psql -c "REVOKE CONNECT ON DATABASE <another-app-database> FROM PUBLIC;"
```
Only do this if you know which role each of those apps connects as. A database's
owner and any role holding an explicit grant are unaffected, but an app relying
on the `PUBLIC` grant would lose access — so test that app immediately after.

Finally, load every other site in a browser one more time.

---

## Part 11 — Stripe payments

Stripe keys are **not** environment variables — they are stored in the database
and managed through the admin interface.

1. Go to `https://grameenhalchal.com/admin/settings/payments`.
2. Enter your Stripe **secret key**, **publishable key** and **webhook signing
   secret**.
3. In the Stripe dashboard → **Developers → Webhooks → Add endpoint**, set:
   ```
   https://grameenhalchal.com/stripe/webhook
   ```
4. Copy the signing secret Stripe generates back into the admin page.

That route is already exempt from CSRF checks, so no further configuration is
needed.

---

## Part 12 — Email

**Email is currently disabled.** `MAIL_MAILER=log` means order confirmations and
password resets are written to the container log instead of being sent.

Nothing breaks, but customers receive nothing. To enable it, edit `.env`:

```bash
cd /var/www/storez
nano .env
```

```
MAIL_MAILER=smtp
MAIL_HOST=<your SMTP host>
MAIL_PORT=587
MAIL_USERNAME=<your SMTP username>
MAIL_PASSWORD=<your SMTP password>
MAIL_SCHEME=null
MAIL_FROM_ADDRESS="no-reply@grameenhalchal.com"
```

**`MAIL_SCHEME` accepts only two values, and `tls` is not one of them.**
`config/mail.php` passes it straight through to Symfony's mailer, which
recognises `smtp` and `smtps`; anything else throws `UnsupportedSchemeException`
on the first send.

| Your provider's port | What to set |
|---|---|
| 587 — STARTTLS, the common case | `MAIL_SCHEME=null`; encryption is negotiated automatically |
| 465 — implicit TLS | `MAIL_SCHEME=smtps` and `MAIL_PORT=465` |

```bash
docker compose -f docker-compose.prod.yml up -d
```

Test by triggering a password reset and checking it arrives.

---

## Part 13 — Day-to-day operations

All from `/var/www/storez`.

### Deploy an update

Two things to know before the first update. Migrations run automatically and
unattended, and there is no undo for a migration that drops or rewrites data.
And `--build` overwrites the `storez-app:prod` image in place, so a broken build
leaves nothing to fall back to. Both are handled by the first two commands below.

```bash
cd /var/www/storez

# 1. Back up the database - migrations will run without asking
sudo -u postgres pg_dump storez | sudo gzip > \
    /var/backups/storez/storez-db-predeploy-$(date +%F-%H%M).sql.gz

# 2. Keep the image that currently works
docker tag storez-app:prod storez-app:prev

# 3. Deploy
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml logs -f app
```

Expect a few seconds of downtime while the container recreates. Nothing here
touches the host or your other applications.

**Never run `php artisan optimize` or `php artisan route:cache`.** They are
standard Laravel deploy advice and they will take this site down — see the
Reference at the end for why, and for how to recover if one has been run.

### Roll back a bad deploy

If the new build is broken, put the previous image back:

```bash
cd /var/www/storez
docker tag storez-app:prev storez-app:prod
docker compose -f docker-compose.prod.yml up -d --no-build
```

That reverts the code in seconds. It does **not** revert migrations. If the bad
deploy changed the schema, restore the pre-deploy dump as well — see "Restore
from a backup" below.

### View logs

```bash
# Follow live
docker compose -f docker-compose.prod.yml logs -f app

# Last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100 app

# Errors only
docker compose -f docker-compose.prod.yml logs app | grep -i error
```

Logs are capped at 10 MB × 3 files per service, so they cannot fill your disk.

### Run artisan commands

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate:status
docker compose -f docker-compose.prod.yml exec app php artisan schedule:list
docker compose -f docker-compose.prod.yml exec app supervisorctl status
```

### Restart and stop

```bash
# Restart the app only
docker compose -f docker-compose.prod.yml restart app

# Stop everything (uploaded media is preserved)
docker compose -f docker-compose.prod.yml down

# Start again
docker compose -f docker-compose.prod.yml up -d
```

### Back up

Two things matter: the database and the uploaded media.

```bash
# One-time setup of a private backup directory
sudo mkdir -p /var/backups/storez
sudo chmod 700 /var/backups/storez
```

```bash
# Database
sudo -u postgres pg_dump storez | sudo gzip > \
    /var/backups/storez/storez-db-$(date +%F).sql.gz

# Uploaded media
sudo docker run --rm \
    -v storez_storez_public:/data \
    -v /var/backups/storez:/backup \
    alpine tar czf /backup/storez-media-$(date +%F).tar.gz -C /data .

sudo chmod 600 /var/backups/storez/*
ls -la /var/backups/storez/
```

> These files contain customer and order data. Keep them in
> `/var/backups/storez` with restrictive permissions — never in `/tmp` or any
> directory served by nginx.

`pg_dump storez` covers only StoreZ's database and does not interfere with any
existing backup of your other applications.

**Automate it** with a daily cron job at 3 AM:
```bash
sudo crontab -e
```
```
0 3 * * * sudo -u postgres pg_dump storez | gzip > /var/backups/storez/storez-db-$(date +\%F).sql.gz && find /var/backups/storez -name '*.gz' -mtime +14 -delete
```
(The `\%` escaping is required in crontab. This keeps 14 days of backups.)

### Restore from a backup

A `pg_dump` file contains no `DROP` statements. Replaying one into a database
that still holds tables fails on every `CREATE TABLE` and leaves you half
restored, so the database has to be recreated empty first.

**This permanently destroys the current contents of the `storez` database.
Confirm the backup file you are about to restore is the one you want, and if
there is any doubt, take a dump of the current state before you begin.**

```bash
cd /var/www/storez

# Stop the app so nothing writes during the restore
docker compose -f docker-compose.prod.yml down

# Recreate the database empty, with the same owner and the same public revoke
sudo -u postgres dropdb storez
sudo -u postgres createdb --owner=storez storez
sudo -u postgres psql -c "REVOKE ALL ON DATABASE storez FROM PUBLIC;"

# Restore
gunzip -c /var/backups/storez/storez-db-YYYY-MM-DD.sql.gz | \
    sudo -u postgres psql storez

docker compose -f docker-compose.prod.yml up -d
```

**To restore uploaded media as well**, replace the contents of the volume:

```bash
docker compose -f docker-compose.prod.yml down

sudo docker run --rm \
    -v storez_storez_public:/data \
    -v /var/backups/storez:/backup \
    alpine sh -c 'rm -rf /data/* && \
        tar xzf /backup/storez-media-YYYY-MM-DD.tar.gz -C /data'

docker compose -f docker-compose.prod.yml up -d
```

The entrypoint recreates the `public/storage` symlink and fixes ownership on
every boot, so nothing further is needed.

### Remove StoreZ completely

```bash
cd /var/www/storez
docker compose -f docker-compose.prod.yml down -v   # -v ALSO DELETES ALL MEDIA
sudo rm /etc/nginx/sites-enabled/grameenhalchal.com.conf
sudo nginx -t && sudo systemctl reload nginx
sudo -u postgres dropdb storez
sudo -u postgres dropuser storez
sudo rm -rf /var/www/storez
```
Nothing else on the server is affected. Back up first if you might want the data.

---

## Part 14 — Troubleshooting

### 14.1 The image fails to build

```bash
docker compose -f docker-compose.prod.yml build app 2>&1 | tail -40
```
- **"no space left on device"** → `df -h` then `docker system prune -a`
- **`Killed`, or exit code 137, usually during `npm run build`** → the build ran
  out of memory. Add swap (check 2.6) and retry. If the box cannot spare it,
  build the image on another machine and move it across:
  `docker save storez-app:prod | gzip > storez.tgz`, copy the file over, then
  `gunzip -c storez.tgz | docker load`
- **Network or timeout errors** → transient; retry
- **Composer or npm errors** → confirm `composer.lock` and `package-lock.json`
  were pushed in step 5.1

### 14.2 Stuck at "Waiting for database"

Most common problem. Work through in order:

```bash
# 1. Is PostgreSQL running?
sudo systemctl status postgresql --no-pager | head -5

# 2. Is the pg_hba.conf line present and correctly placed?
sudo grep -n "storez" /etc/postgresql/*/main/pg_hba.conf

# 3. Does the password in .env match the role's password?
grep DB_PASSWORD /var/www/storez/.env

# 4. Can you connect manually with those credentials?
#    Caveat: this goes over 127.0.0.1, which the rule from 4.6 does NOT cover.
#    "no pg_hba.conf entry for host 127.0.0.1" here is expected and tells you
#    nothing about the container - use 4b instead. Any other error is real.
PGPASSWORD='<password from .env>' psql -h 127.0.0.1 -U storez -d storez -c 'SELECT 1;'

# 4b. The authoritative test: the entrypoint's own probe, run from inside the
#     container, over the exact path the 4.6 rule governs.
cd /var/www/storez && docker compose -f docker-compose.prod.yml run --rm --no-deps app php -r '
  try {
    new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT")
        .";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    echo "connected\n";
  } catch (Throwable $e) { echo $e->getMessage(), "\n"; }'

# 5. Is PostgreSQL listening where the container can reach it?
ss -tulpn | grep 5432

# 6. What does PostgreSQL say it rejected?
sudo tail -30 /var/log/postgresql/postgresql-*.log
```

Usual causes: the `pg_hba.conf` line is missing, sits *below* a broader rule, or
the password does not match. After editing `pg_hba.conf`, run
`sudo systemctl reload postgresql`.

If you did Part 3.3 and the bridge subnet is not `172.17.x`, widen the rule to
match what `ip addr show docker0` reports.

### 14.3 502 Bad Gateway

nginx cannot reach the container.

```bash
docker compose -f docker-compose.prod.yml ps        # is it healthy?
curl -I http://127.0.0.1:8092/up                    # does it answer directly?
grep proxy_pass /etc/nginx/sites-available/grameenhalchal.com.conf
grep APP_BIND_PORT /var/www/storez/.env
```
The port in the vhost and in `.env` must match.

### 14.4 Certbot fails

```bash
# Does DNS resolve correctly yet?
dig +short grameenhalchal.com @8.8.8.8

# Is HTTP reachable?
curl -I http://grameenhalchal.com
```
- **"DNS problem: NXDOMAIN"** → records not saved or not propagated; back to 8.6
- **"Timeout during connect"** → ports 80/443 blocked; check `sudo ufw status`
- **"too many failed authorizations"** → rate limit hit; wait an hour, and fix
  DNS before retrying
- **`www` fails but the bare domain works** → the parking CNAME from 8.3 is
  still present

### 14.5 Images do not display

```bash
docker compose -f docker-compose.prod.yml exec app ls -la public/storage
docker compose -f docker-compose.prod.yml exec app sh -c 'find storage/app/public -name "*.webp" | wc -l'
grep APP_URL /var/www/storez/.env
```
`APP_URL` must be `https://grameenhalchal.com` — it is what builds image URLs.
If the symlink is missing, restart the container; the entrypoint recreates it.

### 14.6 Upload fails with "413 Request Entity Too Large"

The 20 MB limit is set in four places and all must agree:
```bash
grep client_max_body_size /etc/nginx/sites-available/grameenhalchal.com.conf
grep client_max_body_size /var/www/storez/docker/prod/nginx.conf
grep upload_max_filesize  /var/www/storez/docker/prod/php.ini
grep post_max_size        /var/www/storez/docker/prod/php.ini
```
`post_max_size` (24M) is the ceiling for the *entire* request — the file plus
every other form field — so it must stay above `upload_max_filesize`, not equal
to it. A 20 MB file in a form with other fields exceeds 20 MB in total.
Changing the container files requires a rebuild; changing the host vhost
requires `sudo nginx -t && sudo systemctl reload nginx`.

### 14.7 The site is slow or the server is short of memory

```bash
free -h
docker stats --no-stream
```
Reduce `pm.max_children` in `docker/prod/php-fpm.conf` from 12 to 6, then
rebuild. Each worker can use up to 512 MB.

### 14.8 Start over completely

```bash
cd /var/www/storez
docker compose -f docker-compose.prod.yml down -v
sudo -u postgres dropdb storez
sudo -u postgres createdb --owner=storez storez
sed -i 's/^RUN_SEEDERS=false/RUN_SEEDERS=true/' .env
docker compose -f docker-compose.prod.yml up -d --build
```
This wipes StoreZ's data and re-seeds. It does not touch your other apps.

---

## Reference — things that are the way they are on purpose

- **`route:cache` is never run.** `routes/web.php` registers 54 Livewire routes
  by passing component *instances*. Route caching serializes routes with
  `var_export()`, which requires `__set_state()` — Livewire components do not
  have it, so caching fails and leaves a file that makes every request fatal.
  Config, view and event caching are all still applied.

  **This also rules out `php artisan optimize`**, which calls `route:cache`
  internally. It is the command most Laravel runbooks tell you to run on deploy,
  and here it takes the site down. If one has already been run, delete the cache
  file and restart:
  ```bash
  docker compose -f docker-compose.prod.yml exec app \
      rm -f bootstrap/cache/routes-v7.php
  docker compose -f docker-compose.prod.yml restart app
  ```
  The entrypoint clears that file on every boot, so a plain restart normally
  fixes it on its own.

- **`CACHE_STORE` must be `redis` or `database`.** The hourly
  `content:sync-scheduled` job uses `->onOneServer()`, which needs an atomic
  lock. The `file` driver cannot provide one; the entrypoint refuses to start.

- **Do not set `FILESYSTEM_DISK=s3`.** The S3 adapter is not installed, and the
  media code targets the local `public` disk directly.

- **The queue runs inline (`sync`).** There are no queued jobs today. If any are
  added, uncomment the `queue` program in `docker/prod/supervisord.conf` and set
  `QUEUE_CONNECTION=redis`.

- **Upload limits are 20 MB** in four places: the host vhost, the container's
  nginx, and both `upload_max_filesize` and `post_max_size` (24M — the
  whole-request ceiling) in `docker/prod/php.ini`. Raising one alone has no
  effect.

- **Use `reload`, never `restart`, for host nginx and PostgreSQL.** Both are
  shared with your other applications. Reload is graceful; restart is not.

- **`.env` lives only on the server.** It is not in git and not in the image, so
  configuration changes need only a restart, never a rebuild.
