# Laravel Blog Platform

A full-featured blog platform built with Laravel and PostgreSQL. Supports role-based access control, an admin panel, comments, tags, SEO management, and multilingual content.

---

## Features

- **Authentication** — register, login, logout
- **Roles & Permissions** — built-in `admin` and `user` roles, with the ability to create custom roles through the admin panel
- **Admin Panel** — manage articles, news, users, roles, tags, comments, and SEO settings
- **Comments** — users can leave comments on articles and news
- **Tags** — articles and news can be tagged
- **SEO** — meta tags and sitemap support per page/post
- **Filtering** — filter articles and news by title in the admin panel and client side
- **Multilingual support** — static UI translations via `lang/` files,
  content translations stored in the database (`articles_translations` table)

---

## Tech Stack

| Layer          | Technology             |
|----------------|------------------------|
| Backend        | PHP 8.3 / Laravel 12   |
| Web server     | Nginx 1.27             |
| Database       | PostgreSQL 16          |
| Session        | Redis 7.2 of file      |
| Frontend       | Blade templates        |
| Infrastructure | Docker (separate repo) |
| Architecture   | Modular structure via [nwidart/laravel-modules](https://nwidart.com/laravel-modules) |

---

## Architecture

The application follows a modular structure using [nwidart/laravel-modules](https://nwidart.com/laravel-modules).  
Each feature (Articles, News, SEO, Users, etc.) is isolated in its own module under the `Modules/` directory.

## Project Structure

This repository contains only the Laravel application code.  
Docker configuration lives in a separate repository: [docker_for_blog](https://github.com/Vuviy/docker_for_blog)

```
laravel_blog_platform/   ← this repo (Laravel app)
docker_for_blog/         ← separate repo (Docker + docker-compose)
  ├── docker/
  ├── app/               ← clone this repo here
  ├── docker-compose.yml             
  └── .env
```

---

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose installed
- Git

### Installation

**1. Clone the Docker repository**

```bash
git clone https://github.com/Vuviy/docker_for_blog.git
cd docker_for_blog
```

**2. Clone this repository into the `app/` folder**

```bash
git clone https://github.com/Vuviy/laravel_blog_platform.git app
```

**3. Copy the environment files**

```bash
cp app/.env.example app/.env
cp .env.example .env
```

**4. Configure `.env`**

Open `app/.env` and update the database credentials to match your Docker setup:

```env
DB_CONNECTION=pgsql
DB_HOST=blog_db
DB_PORT=5432
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=root
```

`.env`:

```env
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=root

PGADMIN_DEFAULT_EMAIL=set_your_email
PGADMIN_DEFAULT_PASSWORD=set_your_pass
```

**5. Start Docker containers**

```bash
docker compose up -d
```

**6. Install dependencies**

```bash
docker compose exec blog_php composer install
```

**7. Generate app key**

```bash
docker compose exec blog_php php artisan key:generate
```

**8. Run migrations and seeders**

```bash
docker compose exec blog_php php artisan migrate
```
**9. If you have problem with access in directory**

```
docker compose exec blog_php chmod -R 775 storage bootstrap/cache
```

**10. Open in browser**

```
http://localhost
```

**11. pgAdmin (optional)**

PostgreSQL GUI is available at `http://localhost:8000`

Login with credentials from your `.env`:

| Field    | Value                    |
|----------|--------------------------|
| Email    | `PGADMIN_DEFAULT_EMAIL`  |
| Password | `PGADMIN_DEFAULT_PASSWORD` |

---

## Default Credentials

You must use artisan command for creating admin user:

```bash
docker compose exec blog_php php artisan admin:create
```

This command create two roles (admin, user) and admin with credentials:

| Role  | Email | Password |
|-------|---|---|
| admin | admin@admin.com | password |


---

## Running Tests

```bash
docker compose exec blog_php php artisan test
```

---

## License

This project is open-source and available under the [MIT license](LICENSE).
