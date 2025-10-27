# Course Creation App

![Laravel](https://img.shields.io/badge/Laravel-11+-red.svg?style=flat&logo=laravel) ![PHP](https://img.shields.io/badge/PHP-8.1+-8892BF.svg?style=flat&logo=php) ![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg?style=flat&logo=mysql) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3+-7952B3.svg?style=flat&logo=bootstrap)

A Laravel-based application for creating structured online courses with a dynamic nested form. Users can build courses containing modules and various content types (text, links, images, videos). This project demonstrates hierarchical data modeling, file uploads, form validation, and JavaScript-driven UI enhancements.

**Project Task Overview**: Developed as part of a course creation system, this app resolves common Laravel challenges like migration errors (e.g., custom primary keys, foreign key constraints), validation for nested arrays, and large file uploads. It includes Tinker-based testing for inserts and relationships.

**Current Version**: 1.0.0 (as of October 28, 2025)  
**Author**: Shah Abdul Mazid  
**License**: MIT

## Features

- **Course Builder**: Create courses with title, description, category, and feature video (MP4/AVI/MOV, up to 10MB).
- **Hierarchical Structure**: Courses → Modules (with order) → Contents (text, links, images up to 5MB, videos up to 10MB).
- **Dynamic Nested Form**: Vanilla JS for adding/removing modules/contents; type-specific fields toggle (e.g., textarea for text, file input for media).
- **Advanced Validation**: Nested rules with `required_if` for content types; handles empty strings/nulls gracefully.
- **File Handling**: Secure uploads to public storage; transactions for atomic saves (rollback on failures).
- **Data Integrity**: Foreign keys with cascade deletes; eager loading to prevent N+1 queries.
- **Debug & Testing**: Built-in Tinker scripts for hierarchy verification; logging for errors/uploads.
- **Error Resilience**: Input persistence on validation fails; user-friendly messages (e.g., "Text is required for text-type content").

## Tech Stack

- **Backend**: Laravel 11+ (PHP 8.1+), Eloquent ORM (relationships: hasMany/belongsTo).
- **Database**: MySQL 8.0+ (tables: courses, modules, contents; configurable for SQLite).
- **Frontend**: Blade views, Bootstrap 5 (styling), Vanilla JS (no frameworks for simplicity).
- **Storage**: Laravel Filesystem (public disk; media/videos folders).
- **Tools**: Artisan (migrations, tinker), Composer, NPM (optional for assets).

## Installation

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL 8.0+ (or SQLite)
- Node.js & NPM (optional for custom assets)

### Step 1: Clone & Setup Dependencies

```bash
git clone <your-repo-url> coursecreation
cd coursecreation
composer install
```

### Step 2: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env`:
- Database: `DB_CONNECTION=mysql`, `DB_DATABASE=coursecreation`, `DB_USERNAME=root`, `DB_PASSWORD=`.
- Storage: `FILESYSTEM_DISK=public`.
- Debug: `APP_DEBUG=true`.

### Step 3: Database & Storage Setup

```bash
php artisan migrate  # Creates courses, modules, contents tables
php artisan storage:link  # Symlink for public uploads
```

**Migration Notes**: If errors occur (e.g., "course_id does not exist"), rollback (`php artisan migrate:rollback --step=1`) and use standard `$table->id()` for primary keys.

### Step 4: Assets (Optional)

Using CDN for Bootstrap/JS; for local:
```bash
npm install
npm run dev
```

### Step 5: Launch

```bash
php artisan serve
```

Access: `http://127.0.0.1:8000/create-course`.

## Usage

### Building a Course

1. **Form Access**: GET `/create-course` (renders Blade view with dynamic form).
2. **Course Details**: Fill title (required), description/category (optional), upload feature video.
3. **Add Modules**: Start with Module 1; click "+ Add Module" for more (each has title & order).
4. **Add Contents**: Per module, click "+ Add Content"; select type:
   - **Text**: Required textarea (string, max 65KB).
   - **Link**: Required URL input (validated).
   - **Image**: Required file (JPEG/PNG/GIF, max 5MB).
   - **Video**: Required file (MP4/AVI/MOV, max 10MB).
5. **Submit**: POST `/create-course` → Validates, saves atomically, redirects to `/courses/{id}`.
6. **View**: GET `/courses/{id}` displays full hierarchy (e.g., Course > Modules > Contents).

### Key Routes (web.php)

```php
Route::get('/create-course', [CourseController::class, 'create'])->name('courses.create');
Route::post('/create-course', [CourseController::class, 'store'])->name('courses.store');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
```

## Project Task Implementation Highlights

- **Migrations**: Fixed `BadMethodCallException` by using `$table->id()`; added foreign keys with `constrained()->onDelete('cascade')`.
- **Models**: Fillable arrays; relationships (e.g., `Course::hasMany(Module::class)`).
- **Controller**: `CourseController@store` uses `Validator::make` for nested rules, `DB::transaction` for saves, file storage via `store('media', 'public')`.
- **View**: `create.blade.php` with JS for dynamic fields (add/remove, type toggles); error display via `@errors`.
- **Validation Fixes**: `nullable` + `required_if` for content fields; safeguards against non-string inputs.
- **Uploads**: Handle large POSTs (`php.ini: post_max_size=50M`); transactions prevent partial saves.
- **Testing**: Tinker for inserts (`Course::create([...])`); manual verification of hierarchy.

## Testing

### Feature Tests

```bash
php artisan test
```

Example in `tests/Feature/CourseCreationTest.php`:
```php
public function test_course_with_nested_content()
{
    $response = $this->post(route('courses.store'), [
        'title' => 'Test Course',
        'modules' => [
            [
                'title' => 'Module 1',
                'contents' => [
                    ['type' => 'text', 'text' => 'Hello World']
                ]
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('courses', ['title' => 'Test Course']);
    $this->assertDatabaseHas('contents', ['data' => 'Hello World']);
}
```

### Tinker Verification

```bash
php artisan tinker
```
```php
use App\Models\Course;
$course = Course::create(['title' => 'Tinker Course']);
$module = $course->modules()->create(['title' => 'Tinker Module', 'order' => 1]);
$module->contents()->create(['type' => 'text', 'data' => 'Test Data', 'order' => 1]);
Course::with('modules.contents')->find($course->id)->toArray();  // Dump hierarchy
```

## Configuration & Customization

- **Migrations**: Edit `database/migrations/` for extras (e.g., `$table->softDeletes()`).
- **Models**: `app/Models/`—add scopes (e.g., `Module::orderBy('order')`).
- **Views**: `resources/views/create.blade.php` (form); `course/show.blade.php` (display).
- **Validation**: Extend in `CourseController` (e.g., add price field).
- **PHP.ini Tweaks**: For uploads: `upload_max_filesize=50M`, `max_execution_time=300`; restart server.
- **Extend**: Add auth (`php artisan make:auth`), API routes, or queues for uploads.

## Troubleshooting

| Issue | Solution |
|-------|----------|
| **"Modules field required"** | Check form names (`modules[0][title]`); ensure JS runs (no console errors). |
| **Upload 413 Error** | Set `php.ini`: `post_max_size=50M`; restart `php artisan serve`. |
| **"Must be a string" Validation** | Use `nullable` in rules; ensure JS textareas default to `''`. |
| **Files Not Visible** | Run `php artisan storage:link`; access via `asset('storage/media/file.jpg')`. |
| **Migration Fail (e.g., course_id)** | Use `$table->id()`; rollback: `php artisan migrate:rollback --step=1`. |
| **Empty Hierarchy** | Verify relationships in models; use Tinker to test inserts. |

Logs: `tail -f storage/logs/laravel.log`.

## Contributing

1. Fork the repo.
2. Branch: `git checkout -b feature/your-task`.
3. Commit: `git commit -m "Add your-task"`.
4. Push: `git push origin feature/your-task`.
5. PR: Open against `main`.

Focus: Security, tests (`php artisan test`), docs.

## License

MIT License—see [LICENSE](LICENSE).

## Acknowledgments

- [Laravel Framework](https://laravel.com) for core magic.
- Bootstrap for responsive UI.
- Community fixes for migrations/validation (e.g., foreign key constraints).

---

*Last Updated: October 28, 2025*  
*Project Task Credits: Built during Laravel course development by Shah Abdul Mazid.*  
*Issues? [GitHub Issues](https://github.com/yourusername/coursecreation/issues).*

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/81e366c7-3611-4146-8813-15ddb97f7731" />

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/6408e745-e5e6-45be-b6c9-5eb67d4ae407" />

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/5eb93a95-1626-4835-b40d-f96797e78eb4" />

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/f3e7c416-f83a-4b36-b9ba-71d39c44003c" />

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/e2d78f22-dc0a-4907-ab75-f85d1be8bc2f" />

<img width="2560" height="1600" alt="image" src="https://github.com/user-attachments/assets/2604cb72-aaad-4e19-b2bb-aead7c340aba" />








