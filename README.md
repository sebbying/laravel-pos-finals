# Counterpoint POS — Laravel only

IT0049 complete point-of-sale project. The attached brief refers to CodeIgniter; this implementation intentionally uses **Laravel 12 with Blade** to follow the explicit Laravel-only requirement. Requires PHP 8.2 or 8.3+, Composer 2, GD, mbstring, fileinfo, PDO SQLite (or PDO MySQL), OpenSSL, DOM/XML, tokenizer, ctype, and session. Use 64-bit PHP. No Node build, CDN, or CodeIgniter files are required.

## Delivery status — read first

The application source, migrations, seeder, setup scripts, and 18 feature tests are included. This package was created in an environment without PHP/Composer and without access to GitHub or dependency downloads. **Laravel tests and real SMTP delivery have not been executed here.** Run the checks below before submitting. No hosted site has been created and the linked GitHub repository has not been modified or inspected. The hosted URL and verified email delivery remain required submission steps.

`vendor/` is intentionally excluded. Composer generates `composer.lock` on the first install because dependencies could not be resolved here. Commit that generated lockfile after installation and passing tests; it pins the exact versions used for your submission.

## 1. Run on Windows / XAMPP

1. Extract the ZIP to a **new folder**, for example `C:\xampp\htdocs\laravel-pos-midterm`. Do not copy these files over your old Laravel project. Do not reuse an old `.env`, `vendor`, database, or users table.
2. Install Composer 2 if needed. In its installer select `C:\xampp\php\php.exe`.
3. Open `C:\xampp\php\php.ini`. Enable `extension=gd`, `extension=pdo_sqlite`, `extension=sqlite3`, `extension=mbstring`, `extension=fileinfo`, `extension=openssl`, and `extension=zip` where present. Do not add duplicate entries. Run `php --ini` to confirm which file the CLI uses.
4. Open PowerShell in the extracted project folder. Run:

   ```powershell
   Set-ExecutionPolicy -Scope Process Bypass
   .\setup.ps1
   ```

   This adds XAMPP PHP to the current process PATH, installs dependencies, copies `.env.example` only if missing, creates an empty SQLite database only if missing, generates an application key only if absent, migrates, and asks you to create an administrator. Choose your own password of 10–72 characters. Password input is intentionally hidden.
5. Start the app:

   ```powershell
   $env:Path = 'C:\xampp\php;' + $env:Path
   php artisan serve
   ```

6. Open **http://127.0.0.1:8000**. Log in with the username and password you entered. There is no default password. Apache and MySQL do **not** need to run when using this SQLite setup.
7. Optional demo products/customer: `php artisan db:seed`. Demo customer email is a placeholder; replace it with your real test recipient before testing email.

If PowerShell cannot run the script, execute the steps manually:

```powershell
$env:Path = 'C:\xampp\php;' + $env:Path
composer install
Copy-Item .env.example .env
New-Item database/database.sqlite -ItemType File
php artisan key:generate
php artisan migrate
php artisan pos:install
php artisan serve
```

The manual copy/create/key commands above are for a **new installation only**. Keep the app key and database on subsequent runs. If `pos:install` says users already exist, setup is already complete; sign in using the account you created. It deliberately does not replace existing users or reset their passwords.

Linux/macOS: install the listed PHP extensions and Composer, then run `sh setup.sh` and `php artisan serve`.

## 2. Configure actual email delivery

Log mode is enabled initially for local development. **Log mode does not deliver email and does not satisfy a real email demonstration.** Customer receipt sending is blocked until SMTP is selected.

Use SMTP credentials from your email provider or a test inbox provider. Example for Gmail with a Google account that supports App Passwords:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-address@gmail.com
MAIL_PASSWORD="your-16-character-app-password"
MAIL_FROM_ADDRESS=your-address@gmail.com
MAIL_FROM_NAME="Counterpoint POS"
```

Use an **App Password**, not your ordinary Google password. Your account must permit App Passwords; otherwise use another SMTP provider. For a provider requiring implicit TLS on port 465 use `MAIL_SCHEME=smtps` and `MAIL_PORT=465`. Port 587 uses SMTP with negotiated STARTTLS. Use the provider's exact host, credentials, and verified sender address.

After editing `.env`:

```powershell
php artisan config:clear
```

1. Sign in as administrator → **Email setup**.
2. Verify `Mailer: smtp` and the sender address.
3. Enter your own real recipient email → Send test.
4. Confirm the email arrives in Inbox or Spam. The success banner means the SMTP server accepted the message, not that inbox delivery was verified.
5. Create a customer with that real email → record a sale for that customer → **Email receipt**.
6. Confirm the receipt contains the right item, quantity, amount, staff name, and date.

Receipt sending is a separate explicit action after the sale commits. A delivery failure cannot undo inventory or create a duplicate sale; retry the receipt from the saved transaction. Walk-in sales have no email receipt. The address and receipt details are saved at checkout so later customer/product edits do not rewrite past receipts. The administration page never exposes SMTP passwords. Synchronous SMTP has a 15-second connection timeout and requires no queue worker.

## 3. Use the system

- **Products:** add, list, view, edit, archive; upload a product image. Prices allow at most two decimal places. Archived products cannot be sold.
- **Customers:** create, list, view, update, delete. Email is required. A customer is optional at checkout.
- **Staff (administrators):** create, list, view, update, delete, upload avatars, set role, and change passwords. A blank edit password preserves the current hash. You cannot delete or demote your own active administrator account.
- **Record sale:** choose one product (the supplied schema specifies one product per sale), optional customer, and a positive integer quantity. Unit price, staff, and total are calculated on the server. A checkout token prevents duplicate submission. Excess stock requests are rejected without recording a sale.
- **Sales history:** view immutable sale snapshots and printable receipts, with product, customer, staff, quantity, price, and date.
- All management and image routes require an authenticated staff session. Staff can manage products, customers, and sales; only administrators manage staff and email settings. No public registration.

Deletes use Laravel soft deletion to preserve the required sales foreign keys. Deleted customers and users disappear from active lists and cannot be selected for new sales; deleted staff cannot authenticate. Products use the expressly permitted archive behavior. Historical sales are read-only because they represent posted transactions; complete CRUD is implemented for all three required management entities.

## 4. Validate before submission

```powershell
php artisan test
php artisan route:list --except-vendor
php artisan view:cache
php artisan view:clear
composer audit
```

The automated suite uses an isolated, in-memory SQLite database, faked images and mail, and cannot affect your application database. It covers page rendering, CRUD, access control, password hashing, image re-encoding, money totals, stock deduction, insufficient stock, invalid quantity, duplicate checkout, archived products, history retention, and receipt success/failure behavior. Mail fakes do not verify SMTP connectivity. GitHub Actions runs PHP syntax checks and the suite on push.

Manually verify:

1. Open a private/incognito window and enter `/products`, `/staff`, `/customers`, and `/sales`: each redirects to login.
2. Log in. Create a product at PHP 12.35 with stock 10, a customer, and a staff member with avatar. View/edit each record.
3. Sell 3 units to the customer: total PHP 37.05, stock 7. Check the receipt and sales history.
4. Attempt to sell 8 more units: validation error, stock remains 7, no extra transaction.
5. Sell all 7 units: stock zero and product disappears from checkout choices. Reloading the old checkout and submitting again must not record another sale.
6. Edit a sold product's name/price, then archive it; delete the customer/staff used for a sale (use a separate administrator for staff deletion). Check that sales history retains the original values.
7. Try a `.php` upload and an oversized image: rejected. Confirm real JPEG/PNG/WebP uploads display after save.
8. Check the SMTP test and receipt in a real recipient inbox. Log out, then sign in as the staff account: staff administration must be denied.
9. In production MySQL, test simultaneous sales from two different browser sessions against the same last unit: at most one sale succeeds. Automated SQLite tests are sequential and do not establish MySQL concurrency behavior.
10. On your final hosted URL, repeat CRUD, upload, sale, oversell, logout, and email checks.

## 5. Optional MySQL configuration

SQLite is the easiest local path and avoids existing broken tables. For XAMPP MySQL or hosted MySQL, create a **new, empty database**, for example `laravel_pos_midterm_new`. Do not point this project at your old `laravel_pos` database with the damaged users table.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_pos_midterm_new
DB_USERNAME=root
DB_PASSWORD=
```

A default local XAMPP root account commonly has an empty password, but use the credentials configured on your own server. Start MySQL, enable `pdo_mysql`, then:

```powershell
php artisan config:clear
php artisan migrate
php artisan pos:install
```

Never use `migrate:fresh` on a database containing records you need. MySQL migrations request InnoDB for transactional support. New migrations should be added for future schema changes rather than editing an already deployed migration.

## 6. Upload to your GitHub repository

The linked repository could not be accessed from the build environment. No existing code was read or overwritten. Safest workflow for an existing repository:

1. Clone `https://github.com/sebbying/MIDTERM-PROJECT-THE-COMPLETE-POINT-OF-SALE-SYSTEM` to your computer or open it in GitHub Desktop.
2. Create a branch named `laravel-pos-midterm`.
3. Put this project's raw files in a new `laravel-pos/` folder inside that repository. This preserves old files for review. After review you can replace/move old project contents as appropriate; do not publish a Laravel-only submission while still presenting old CodeIgniter source as the active application.
4. Install dependencies and run the checks above in that folder. Include the generated `composer.lock`. Keep `.env`, `vendor`, database files, uploaded customer images, and logs excluded.
5. Commit and push the branch; review it before merging. Make the repository README clearly point to the Laravel folder and add the tested hosted URL.

For an empty/new local repository, from this project folder:

```powershell
git init
git add .
git commit -m "Build Laravel POS with CRUD, sales and SMTP receipts"
git branch -M main
git remote add origin https://github.com/sebbying/MIDTERM-PROJECT-THE-COMPLETE-POINT-OF-SALE-SYSTEM.git
git push -u origin main
```

Use that sequence only if the remote is empty. If Git rejects a push because remote commits exist, clone/use a branch as above. Do not force-push.

## 7. Hosting / deployment

Use PHP hosting with PHP 8.2+, the listed extensions, Composer or an ability to upload built `vendor/`, a configurable document root, writable Laravel storage directories, and outbound SMTP. GitHub Pages cannot run PHP/Laravel.

1. Upload the application outside the public web directory; point the domain document root to **the project's `public/` directory only**. Keep `.env`, database, storage, application code, and vendor outside the public document root.
2. Run `composer install --no-dev --optimize-autoloader` using your committed lockfile. If the host has no Composer, run it locally with a PHP version compatible with the host, then upload `vendor/` too.
3. Create a production `.env` with the real domain, database credentials, and SMTP provider:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-real-domain.example
   APP_KEY=base64:YOUR_GENERATED_KEY
   SESSION_SECURE_COOKIE=true
   DB_CONNECTION=mysql
   DB_HOST=YOUR_HOST_DATABASE_SERVER
   DB_PORT=3306
   DB_DATABASE=YOUR_DATABASE_NAME
   DB_USERNAME=YOUR_DATABASE_USER
   DB_PASSWORD="YOUR_DATABASE_PASSWORD"
   MAIL_MAILER=smtp
   MAIL_SCHEME=smtp
   MAIL_HOST=YOUR_SMTP_HOST
   MAIL_PORT=587
   MAIL_USERNAME=YOUR_SMTP_USERNAME
   MAIL_PASSWORD="YOUR_SMTP_PASSWORD"
   MAIL_FROM_ADDRESS=YOUR_VERIFIED_SENDER
   MAIL_FROM_NAME="Counterpoint POS"
   ```

4. Generate a key with `php artisan key:generate` for a new installation. Preserve that key on subsequent deployments.
5. Ensure `storage/` and `bootstrap/cache/` are writable by the PHP process; do not use global `777` permissions.
6. Run `php artisan migrate --force`, `php artisan pos:install`, `php artisan config:cache`, and `php artisan view:cache`. Uploads are served through authenticated `/media/...` routes, so no storage symlink is needed.
7. Enable HTTPS. Test login, CRUD, uploads, sales, stock rejection, and a real receipt email from the hosted site.
8. Add the hosted URL to the repository README and submit both URLs.

If the host has no SSH/console, prepare an empty MySQL database locally with migrations and the installer, export it using phpMyAdmin, and import it into the host database. Upload a locally generated production key and dependencies; do not upload local config caches, existing sessions, logs, or test customer data. Never create a publicly accessible migration/reset script. Check the host's current SMTP and document-root restrictions before choosing it; a host that blocks outbound SMTP or exposes the full project cannot meet this submission as configured.

## Architecture and security

Routes and auth middleware → resource controllers → Eloquent models; `SaleService` owns inventory and sale persistence in one transaction; `ImageService` decodes and re-encodes uploads as JPEG with random filenames and a 1000px maximum edge; `SaleReceipt` owns receipt email rendering. Database foreign keys link products, customers, staff, and sales. Additional columns support soft deletion, roles, Laravel sessions/auth conventions, receipt snapshots, and checkout idempotency.

All mutation forms include CSRF protection, PUT/DELETE forms use method spoofing, passwords are hashed through Laravel's hashed cast, Blade escapes displayed data, Eloquent binds SQL values, login/email endpoints are rate limited, and image paths cannot be supplied as arbitrary filesystem paths. Images are stored on a private disk and returned only to authenticated staff. Decimal prices are converted to integer cents for transaction multiplication; final totals are not taken from the browser. Row locking plus an atomic conditional decrement protects stock on transactional MySQL. Sales and price snapshots are permanent audit records.

## References

- Laravel 12 database transactions and migrations: https://laravel.com/docs/12.x/database and https://laravel.com/docs/12.x/migrations
- Laravel query locking: https://laravel.com/docs/12.x/queries#pessimistic-locking
- Laravel mail configuration: https://laravel.com/docs/12.x/mail

Prepared with AI assistance. Review the code, run the tests, and disclose the assistance according to your course policy.
