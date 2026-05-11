# UniLearn - University Learning Management System

A comprehensive LMS application built with Symfony 6.4, featuring user management and modular course organization.

## 🎓 Features Implemented

### ✅ Authentication & Security
- Login system with form authentication
- Password hashing using Symfony PasswordHasher
- Remember me functionality
- CSRF protection
- Role-based access control (ADMIN, TEACHER, STUDENT, PARTNER)

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Docker (for the database container)

### Installation Steps

```bash
# 1. Install dependencies
composer install

# 2. Start the database container
docker compose up -d

# 3. Configure database (if needed)
# Edit .env file with database connection:
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app"

# 4. Run database migrations
php bin/console doctrine:migrations:migrate

# 5. Create admin user
php bin/console app:create-admin

# 6. Clear cache
php bin/console cache:clear

# 7. Start the server
symfony serve
```

### Database Configuration

| Setting       | Value        |
|---------------|--------------|
| Database Name | app          |
| Username      | app          |
| Password      | !ChangeMe!   |

### Access the Application
- **URL:** http://localhost:8000/login
- **Email:** admin@unilearn.com
- **Password:** admin123

## 🔑 Default Admin Account

```
Email: admin@unilearn.com
Password: admin123
Role: ADMIN
```

## 👥 Create Access Users (Admin + 2 Students + Teacher + Business Partner)

Run this command to create/update a ready-to-test access set:

```bash
php bin/console app:create-access-users
```

It creates these accounts:

| Email | Password | Role |
|-------|----------|------|
| admin@unilearn.com | admin123 | ADMIN |
| student1@unilearn.com | student123 | STUDENT |
| student2@unilearn.com | student123 | STUDENT |
| teacher@unilearn.com | teacher123 | TEACHER |
| partner@unilearn.com | partner123 | BUSINESS_PARTNER |

## 🎨 User Interface

### Bootstrap 5 Components Used
- Navbar with dropdowns
- Cards for dashboard widgets
- Forms with validation
- Tables for user listing
- Alerts for flash messages
- Badges for status indicators
- Icons (Bootstrap Icons)

## 📤 File Upload

Profile pictures are stored in:
```
public/uploads/profiles/
```

Accepted formats: JPG, JPEG, PNG, GIF (max 2MB)

## 🛠️ Development Commands

```bash
# Clear cache
php bin/console cache:clear

# Reload autoload & clear cache
composer dump-autoload && php bin/console cache:clear

# Create migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Create controller
php bin/console make:controller

# Create entity
php bin/console make:entity

# List routes
php bin/console debug:router
```

## 📊 Database Commands

```bash
# View users
docker exec unilearn-database-1 mysql -uapp -p'!ChangeMe!' -D app -e "SELECT * FROM user;"

# Reset database (careful!)
php bin/console doctrine:schema:drop --force
php bin/console doctrine:migrations:migrate
```

## 🐛 Troubleshooting

### Login Issues
- Verify database connection
- Check if user exists and is active
- Clear cache: `php bin/console cache:clear`

### File Upload Issues
- Ensure `public/uploads/profiles/` directory exists and is writable
- Check file size and type restrictions

### Migration Issues

**Check migration status:**
```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:list
```

**Validate schema before migrating:**
```bash
php bin/console doctrine:schema:validate
```

**If you get DUPLICATE COLUMN / TABLE ERROR:**

Sometimes migration is marked as NOT executed but the DB already contains the changes. In that case, mark it as executed instead:
```bash
php bin/console doctrine:migrations:version <migration_number> --add
```

**Execute a specific migration:**
```bash
php bin/console doctrine:migrations:execute <migration_number> --up
```

### Docker Issues

**Reset Docker volume (if database is corrupted):**
```bash
docker-compose down -v
docker-compose up -d
```

### Complete Database Reset

If the database is in a bad state, you can reset everything:
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**Alternative: Force schema update and sync migrations:**
```bash
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:version --add --all
```

## 📄 License

This project is built for educational purposes.

---

## 📰 Recent Updates

### ✅ Forum/Community Improvements (Feb 16, 2026)

**Fixed Bugs:**
- ✓ Scroll position now preserved on page refresh
- ✓ Redirects to newly posted reply with smooth scroll
- ✓ Fixed pagination anchor navigation
- ✓ Added Previous/Next buttons to pagination

**New Features:**
- ✓ Smooth scrolling with highlight animation
- ✓ Loading states on form submission
- ✓ Improved hover effects and transitions
- ✓ Better mobile responsiveness

See [FORUM_IMPROVEMENTS.md](FORUM_IMPROVEMENTS.md) for detailed documentation.

---

**Built with Symfony 6.4 | Bootstrap 5 | MySQL 8**

### Check if Database is Synced with Entities 
php bin/console doctrine:schema:validate

# Classroom Availability Finder

Classroom Availability Finder is a Symfony 6.4 MVC web application for uploading university timetable PDFs, extracting room bookings, detecting conflicts, and checking room availability by date and time.

## Tech Stack

- Symfony 6.4
- Twig
- Bootstrap 5
- Doctrine ORM
- MySQL
- `smalot/pdfparser` for text-based PDF extraction

## Important note

The parser is built for selectable-text PDFs first. If a timetable is scanned or rasterized, OCR will be needed later.

## Install

1. Configure your database in `.env` or `.env.local`.

```dotenv
DATABASE_URL="mysql://app:password@127.0.0.1:3306/classroom_availability_finder?serverVersion=8.0.32&charset=utf8mb4"
```

2. Install PHP dependencies.

```bash
composer install
```

If you are starting from a fresh Symfony skeleton, the feature depends on these packages:

```bash
composer require doctrine/doctrine-migrations-bundle doctrine/orm smalot/pdfparser symfony/form symfony/twig-bundle symfony/validator
```

3. Create the database and run migrations.

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. Start the application.

```bash
symfony serve
```

## Main routes

- `/timetable/upload` Upload a timetable PDF
- `/timetable/{id}` Timetable details
- `/availability` Search availability
- `/availability/results` Availability results
- `/availability/day/{date}` Day view
- `/rooms` Room list
- `/rooms/{id}` Room details
- `/conflicts` Conflict list

## Composer commands used by this feature

The repository already includes the required packages in `composer.json`, so no new package install is necessary for this code drop. If you need to rebuild dependencies on a new machine, run `composer install` first.

## How the parser works

1. Each PDF page is read page by page.
2. The parser looks for `Emploi du Temps` to infer the group name.
3. It detects the week range, such as `26/04/2026 - 02/05/2026`.
4. It scans day labels like `Lundi 27/04` and extracts course, room, and time blocks.
5. It ignores rooms marked `En ligne`.
6. If a room field contains multiple rooms separated by commas, each room is stored as a separate booking.

If extraction is uncertain, the source page is preserved for debugging in the stored booking records.

## Testing with a timetable PDF

1. Go to `/timetable/upload`.
2. Upload a selectable-text timetable PDF.
3. Open the saved timetable details page.
4. Use `/availability` to pick a date and time slot.
5. Use `/availability/day/{date}` to inspect the standard teaching slots.
6. Check `/rooms` for the detected room universe.
7. Check `/conflicts` for overlapping room bookings.

## Results warning

The app always shows this warning in the classroom finder pages:

> Results are based only on rooms detected in the uploaded PDF unless a master room list is provided.


