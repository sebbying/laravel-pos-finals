# Requirement mapping

| Requirement | Implementation | Verification |
|---|---|---|
| Laravel only | Laravel 12 bootstrap, Composer, Eloquent, Blade, Artisan | `composer show laravel/framework`; no CodeIgniter runtime |
| Functioning email configuration | SMTP config, administrator test form, customer receipt mailable | Configure provider and verify real inbox delivery; not verified in build environment |
| Transaction processing | SaleService database transaction, product lock, conditional stock decrement, integer cents total, request UUID | Feature tests plus hosted simultaneous-sale check |
| Product CRUD | ProductController, list/details/create/edit/archive views | Feature test including image processing |
| Customer CRUD | CustomerController and resource views | Feature CRUD test |
| Staff CRUD | StaffController, administrator middleware, hashed passwords and avatars | Feature tests for CRUD, hashing, role access |
| Required relationships | Single migration with product/customer/user foreign keys on sales | Migrate and run suite |
| Secure uploads prepared for display | Image validation, 2MB/4000px limits, GD JPEG re-encoding to max 1000px, private storage, authenticated image route | Upload processing and invalid-file tests |
| Authentication protects management | Laravel session guard, auth middleware, login throttling, CSRF, logout invalidation | Authentication and route tests; incognito manual check |
| Sale product, optional customer, staff, quantity, total | Checkout validates IDs and quantity, user from session, server-side pricing | Sale feature tests |
| Reject overselling clearly | Available stock validation and guarded decrement | Insufficient stock test |
| Sales history | Paginated history with sale snapshots and printable receipt | History-retention test and manual display check |
| Raw project, migrations, seeder | Full source package, migrations, optional DatabaseSeeder | Upload to repository; commit Composer-generated lockfile |
| Clear setup documentation | README, setup.ps1, setup.sh, .env.example | Fresh machine install check |
| Live hosted version | Deployment instructions included | **Pending: user hosting account, deployment, hosted URL and smoke test** |

The attached brief says CodeIgniter. The explicit user requirement overrides that framework choice; all other functional requirements have been implemented in Laravel source. Do not mark the entire submission complete until PHP tests, real SMTP receipt delivery, repository publication, and the hosted application have been verified.
