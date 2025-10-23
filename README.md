# {{YOUR PROJECT NAME}}

> 🚀 {{ Description about this project }}

- PHP 8.4
- Symfony 7.3
- API Platform 4.1
- PostgreSQL 17

---

## ⚠️ STARTER KIT SETUP INSTRUCTIONS - DELETE EVERYTHING BELOW THIS LINE AFTER SETUP ⚠️

---

## 🎯 Getting Started with This Starter Kit

**This is a starter template, not a production project. Follow these steps:**

### 1. Update Project Information

**README.md (this file):**
- Replace `{{YOUR PROJECT NAME}}` at the top with your actual project name
- Replace `{{ Description about this project }}` with your project description

### 2. Update Configuration Files

**composer.json:**
- Change database names in `reset-dev-db` and `reset-dev-fixtures` scripts
- Example: Replace `app_dev,app_test` with `yourproject_dev,yourproject_test`

**docker-compose.yml:**
- Update `POSTGRES_DB` from `app_dev` to `yourproject_dev`

**.env:**
- Update `DATABASE_URL` database name from `app_dev` to `yourproject_dev`
- Change `APP_SECRET` to a new random value
- Update all `!ChangeMe!` passwords

**phpunit.xml.dist:**
- Update `DATABASE_URL` from `app_test` to `yourproject_test`

**.circleci/config.yml:**
- Update `POSTGRES_DB` from `app_dev` to `yourproject_dev` in all jobs

### 3. Choose Authentication Method

This starter includes both 2FA and JWT authentication options.

**Using 2FA (Default):**
- Already configured, no changes needed
- Remove the JWT comments from `composer.json`

**Using JWT:**
- In `composer.json`: uncomment `lexik/jwt-authentication-bundle` and remove `scheb/2fa-bundle`
- In `.env`: uncomment the JWT configuration section
- Generate JWT keys (see below)
- Run `composer install`

**Generating JWT keys:**
```bash
$ mkdir -p config/jwt
$ openssl genrsa -out config/jwt/private.pem 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
# Update JWT_PASSPHRASE in .env if you used one
```

### 4. Optional Services

**Redis:**
If you need Redis for caching/queues, uncomment the `redis` service in `docker-compose.yml` and update `.env` messenger transport.

### 5. Finish Setup

```bash
$ composer install
```

### 6. Delete This Section

**Once setup is complete, DELETE EVERYTHING FROM THE "⚠️ STARTER KIT SETUP INSTRUCTIONS" LINE ABOVE down to this point.**

Keep everything below the next horizontal line - that's your actual project README.

---

## ⚠️ DELETE EVERYTHING ABOVE THIS LINE AFTER SETUP ⚠️

---

## Local Setup

```bash
# Start the DB (PostgreSQL) inside Docker
$ docker-compose up -d

# Run Symfony locally
$ composer install
$ symfony server:start

# Prepare the database
$ composer reset-dev-db
```

## Testing

Setup:

```bash
$ composer reset-test-db
```

Static analysis (PHPStan):

```bash
$ composer phpstan
```

Unit and functional tests (PHPUnit):

```bash
$ composer phpunit
# Or target individual classes:
$ composer phpunit -- --filter '\\YourTest'
# Or individual tests:
$ composer phpunit -- --filter 'testYourMethod()'
$ composer phpunit -- --filter '\\YourTest::testYourMethod()'
```

Run all tests and analysis:

```bash
$ composer test
```

## Deploying

### Message Queue

For production deployments, you must run the messenger worker:

```bash
$ bin/console messenger:consume async --limit=50 --time-limit=3600
```

This should be managed via Supervisor or similar process manager.

### Cron Schedule

Add your scheduled tasks here as your project grows.

Example:
- `bin/console app:your:command` - every hour
