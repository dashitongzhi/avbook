# PHP CI

The workflow installs the committed `composer.lock`, including development
dependencies, and runs both suites in `phpunit.xml`. It does not resolve new
dependency versions or install a separate global PHPUnit.

## Environment

- GitHub-hosted `ubuntu-22.04`, with PHP installed explicitly by
  `shivammathur/setup-php@v2` (the runner's default PHP is not used).
- PHP 7.2 and 7.3, with Composer 2.2 LTS. These are legacy compatibility
  baselines for the locked Laravel 5.8.27 and PHPUnit 7.5.13 dependencies, not
  a claim that every PHP version allowed by `composer.json` has been tested.
  The lock also contains Carbon 2.20.0, requiring at least PHP 7.1.8.
- Composer 1 is obsolete; do not use it for new CI. Pinning Composer's 2.2
  series keeps the Composer runtime compatible with this legacy PHP matrix.
- The workflow lists the PHP extensions explicitly and checks the installed
  packages' platform requirements without ignoring PHP or extension constraints.
- `.env` is copied from `.env.example` before Composer's Laravel discovery
  scripts run, and a disposable application key is generated before testing.
  Array cache/session/mail and synchronous queues avoid external services.

The current suite has one trivial unit assertion and one homepage redirect
test. The redirect test checks the existing `/` -> `/censored?` behavior
without following the redirect, so these tests need neither MySQL nor a
running HTTP server. They do not test database-backed catalogue pages. Add a
database service and fixtures if later tests exercise those pages; do not
silently skip failing tests.

`package.json` and `package-lock.json` describe a separate Laravel Mix 4 /
Webpack asset build. The present tests do not render an asset-dependent page,
so Node/npm are not needed by this PHPUnit job. This workflow does not claim
to validate the frontend build.

## Reproduce locally

Use a clean checkout and PHP 7.2 or 7.3 with the extensions listed in
`.github/workflows/php-ci.yml`, Composer 2.2, Git and unzip available:

```bash
set -euo pipefail
export APP_ENV=testing CACHE_DRIVER=array SESSION_DRIVER=array
export QUEUE_CONNECTION=sync MAIL_DRIVER=array
cp .env.example .env
test -f composer.lock
composer validate --no-check-publish
composer install --prefer-dist --no-interaction --no-progress
composer check-platform-reqs
php artisan key:generate --ansi
vendor/bin/phpunit --configuration phpunit.xml
```

The copy command replaces `.env`; use a disposable checkout rather than an
existing deployment. Installation keeps Composer scripts enabled, so a failed
Laravel package discovery also fails CI. No step suppresses stderr, ignores
exit codes, uses `continue-on-error`, or falls back to a successful message.
An unavailable locked package is an installation failure to investigate, not
a reason to run `composer update` or ignore platform requirements in CI.

The workflow runs on pushes (including fork branches) and pull requests, using
only read access. A fork PR's first run can require upstream maintainer
approval under GitHub's Actions policy.

PHP 7.x, Laravel 5.8 and PHPUnit 7 are end-of-life software. This compatibility
workflow does not upgrade dependencies or establish a security audit gate.
Dependency/framework modernization needs a separate change.
