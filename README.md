# Bus Fare Management System

The Bus Fare Management System is a PHP and MySQL web application for managing fare collection and operational records for a bus transport organization. It combines passenger account/card workflows with role-specific tools for fare collection, balance loading, bus assignments, inspections, remittance, and reporting.

This repository is an academic/portfolio codebase. It should be run with synthetic data only; no production database or real passenger identity documents are included.

## Main workflows and roles

- **User/passenger:** register an account, use an account number or RFID/NFC identifier, view transactions and recent trips, convert points, submit feedback, and view bus-location information.
- **Conductor:** select an assigned bus, collect cash or RFID/NFC fares, view transaction and load records, and work with route/direction data.
- **Cashier:** load account balances, review collections, process remittances, and export transaction/remittance reports.
- **Inspector:** record inspections and review inspection history.
- **Admin:** manage users and employee accounts, activate or disable accounts, transfer account funds, configure routes/fares/buses, review operational data, and manage public feature content.
- **Superadmin:** access the broader administrative and operational views provided by the application.

Authorization is enforced server-side for the security-sensitive action endpoints covered by the current cleanup. Some older page controllers still use their original inline role checks; see **Known limitations**.

## Technology

- PHP with MySQLi and PDO
- MySQL or MariaDB
- HTML, CSS, JavaScript, Bootstrap, jQuery, SweetAlert2, and ApexCharts
- Leaflet with OpenStreetMap tiles for route/location views
- PHPMailer for SMTP email
- FPDF for PDF reports
- PhpSpreadsheet for spreadsheet exports
- Android JavaScript bridges for supported NFC/card-reader and receipt-printer deployments
- `mike42/escpos-php` is declared in Composer for printer-related integration work

The application uses a traditional server-rendered PHP structure. The public landing page is [index.php](index.php), and the application code is under `NewRam/`.

## Local setup

### Prerequisites

- PHP 8.x recommended
- MySQL or MariaDB
- Composer
- PHP extensions used by the application: `mysqli`, `pdo_mysql`, `curl`, `fileinfo`, and the extensions required by PhpSpreadsheet/FPDF in your environment
- A local web server such as Apache, with URL rewriting not required by the current code

### Installation

1. Clone the repository into your local web-server directory.
2. Install the Composer-managed libraries:

   ```bash
   composer install --working-dir=NewRam/libraries
   ```

3. Copy `.env.example` to `.env` and replace every placeholder with local development values. Do not commit `.env`.
4. Create a local database and import a compatible schema/data set. A canonical SQL schema is not currently included in this repository, so an existing development schema or a separately prepared synthetic schema is required.
5. Before using the application, back up that database and apply `NewRam/database/migrations/20260904_modern_password_and_reset_columns.sql`. This widens password/reset-code storage for modern hashes and makes reset expiry nullable.
6. Configure the web server so the repository root is reachable and the application is available at the path expected by its `/NewRam/...` links.
7. Open the repository-root `index.php` through the web server.

## Environment configuration

The application reads process environment variables and, for local development, optionally loads a repository-root `.env` file.

| Variable | Purpose |
| --- | --- |
| `APP_ENV` | Use `development` locally and `production` in deployments. Production suppresses browser error details. |
| `APP_TIMEZONE` | PHP/database-facing application timezone. |
| `APP_BASE_URL` | Base URL used in account and activation messages. |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Database connection settings. |
| `DB_TIME_ZONE` | MySQL session time zone offset or a named zone installed in the database, for example `+08:00`. |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, `SMTP_USERNAME`, `SMTP_PASSWORD` | SMTP transport settings. |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Sender identity for account and password-reset email. |
| `SMS_API_URL`, `SMS_API_KEY`, `SMS_SENDER_NAME` | Optional SMS provider settings used by fare notifications. |

`.env.example` contains placeholders only. Use separate, least-privilege credentials for each environment.

## Database notes

The code expects tables for user accounts, buses and assignments, routes/fares, transactions, passenger/load logs, remittance, inspections, feedback, and public feature content. Because the repository does not yet contain a reviewed schema migration or sanitized seed, database setup cannot currently be reproduced from the repository alone.

The `useracc.password` column must be at least `VARCHAR(255)` (or an equivalent text type) for `password_hash()` output. The application continues to verify legacy MD5 records during login and upgrades them after a successful login only when the column is wide enough. Apply the included pre-deployment migration before creating or resetting passwords; do not assume an existing deployment already has compatible `password`, `otp`, or `otp_expiry` columns.

Before publishing a demo deployment, add a reviewed schema and clearly fictional seed data. Do not export or sanitize a real operational database in place; build a synthetic data set deliberately.

## Card readers and printers

RFID/NFC fields can accept keyboard-style reader input. Some conductor and cashier screens also call JavaScript interfaces such as `AndroidPrinter`; those features require the matching Android WebView/native bridge and compatible hardware.

PDF and spreadsheet exports are implemented separately from physical receipt printing. The legacy `print_receipt.php` path references a printer helper that is not present in this repository, so that path requires integration work and device-specific testing before it can be considered operational. Keep printer names, network addresses, and device credentials in local configuration rather than source control.

## Testing and debugging

There is no automated test suite in the repository yet. The obsolete live SMS and printer test endpoints were removed during the public-portfolio cleanup. Recommended manual coverage includes:

- login and legacy-password upgrade for every role;
- registration, activation email, password reset, and temporary-password delivery;
- role-denial checks for administrative, financial, export, and user-data endpoints;
- cash and RFID/NFC fare transactions;
- balance loading, account transfer/disable, remittance, and report exports;
- route updates, bus assignment, location, and inspection workflows;
- valid/invalid/oversized feature-image uploads; and
- Android reader/printer behavior on the intended hardware.

Set `APP_ENV=development` only in a local environment when diagnosing errors. Server logs should be kept outside version control.

## Known limitations

- A canonical database schema and synthetic seed are not yet included.
- The codebase contains duplicated role-specific pages and older controllers with inconsistent validation patterns. The highest-risk action endpoints have been guarded, but a complete controller-by-controller authorization and CSRF migration is still recommended.
- Password reset uses an emailed one-time code. Rate limiting, attempt counters, and a fully stateful reset-token workflow remain future work.
- Newly uploaded public feature images are type/size checked, randomly named, and stored in a script-disabled directory. Production should additionally enforce equivalent web-server rules and may re-encode images before storage.
- Some output-escaping, SQL-query consistency, security-header, and error-handling work remains in legacy pages.
- Android NFC/printer integration is deployment-specific and cannot be validated in a normal desktop PHP environment.
- Production same-origin checks depend on browsers or embedded clients sending a valid `Origin` or same-site `Referer` header. Verify Android WebView POST/AJAX flows on the intended device before deployment; do not disable the check globally to accommodate a client that omits both headers.
- Composer/manual dependencies are intentionally retained in this phase and need a separate compatibility and vulnerability-review pass.

## Security and portfolio use

Use fictional people, contact details, account numbers, routes, transactions, and balances in screenshots or demonstrations. Rotate any credential that has ever appeared in repository history, even if the current files now use environment variables. Removing a secret from the current branch does not remove it from existing Git history.
