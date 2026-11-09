# Small E-commerce Management System

A small multi-shop e-commerce system.

- **Admin** manages shops, the shared product catalog, and shop owner accounts.
- **Shop owners** pick products from the shared catalog (or create their own) and set the price, details, and thumbnail for their own shop.
- **Customers** (no login) browse shops and see only the products assigned to a shop.

Built with Laravel 13, Inertia.js, Vue 3, TypeScript, Tailwind CSS, MySQL, and Spatie Laravel Permission.

---

## 1. Requirements

Install these first:

| Tool          | Version          |
| ------------- | ---------------- |
| PHP           | 8.3 or newer     |
| Composer      | 2.x              |
| Node.js + npm | Node 20 or newer |
| MySQL         | 8 or newer       |

> Using **Laragon** (Windows)? It already ships with PHP, Composer, Node and MySQL. Just start Laragon.

All commands below work the same in any terminal (PowerShell, CMD, Git Bash, macOS/Linux). Run them from the project folder.

---

## 2. Setup (step by step)

### Step 1: Get the code

```
git clone <public-repository-url>
cd csn-ecommerce
```

### Step 2: Create an empty database

Create a MySQL database named **`csn_ecommerce`**. You can do this in HeidiSQL / phpMyAdmin, or with:

```
mysql -u root -e "CREATE DATABASE csn_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

### Step 3: Install PHP packages

```
composer install
```

### Step 4: Create the `.env` file

Copy the example file, then generate the app key.

Windows (PowerShell / CMD):

```
copy .env.example .env
```

macOS / Linux / Git Bash:

```
cp .env.example .env
```

Then:

```
php artisan key:generate
```

The default database settings in `.env` are:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csn_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

If your MySQL username or password is different, edit these lines in `.env`.

### Step 5: Install frontend packages

```
npm install
```

### Step 6: Create tables and demo data

```
php artisan migrate --seed
```

This creates all tables, the admin and shop owner accounts, demo shops, products, and thumbnails.

### Step 7: Make uploaded images visible

```
php artisan storage:link
```

### Step 8: Build the frontend

```
npm run build
```

### Step 9: Start the app

```
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser.

> For development with hot reload, run `npm run dev` in a second terminal instead of `npm run build`.

---

## 3. Demo accounts

Created by the seeder. Login page: http://127.0.0.1:8000/login

| Role       | Email                | Password   |
| ---------- | -------------------- | ---------- |
| Admin      | `admin@example.com`  | `password` |
| Shop owner | `owner@example.com`  | `password` |
| Shop owner | `owner2@example.com` | `password` |
| Shop owner | `owner3@example.com` | `password` |

Demo data created by the seeder:

- 4 shops: Demo Online Shop, Tech Hub, Fashion Point, Fresh Mart
- 15 shared catalog products
- Shop listings with prices and product photos
- 3 catalog products that are **not** listed in any shop (customers must never see these)

> These credentials are for local testing only. Change them and set `APP_DEBUG=false` before any public deployment.

---

## 4. How to try each role

### Customer (no login)

1. Open the home page: http://127.0.0.1:8000
2. Click a shop to see its products with the shop's own name, description, price, and thumbnail.
3. Products not assigned to any shop never appear.

### Admin (`admin@example.com`)

1. Log in and use the sidebar.
2. **Users**: create and manage shop owner accounts.
3. **Shops**: create, edit, delete shops and assign an owner.
4. **Products**: create, edit, delete products in the shared catalog.

### Shop owner (`owner@example.com`)

1. Log in and open **My shops** in the sidebar.
2. **Browse catalog**: add an existing catalog product to your shop.
3. **Create new product**: it is added to your shop **and** becomes available in the shared catalog for other owners.
4. **Edit a listing**: change the price, details, and thumbnail (these apply only to your shop).
5. An owner can only access their own shops.

---

## 5. How the data is organised

- `products`: the **shared catalog** (created by admin or shop owners).
- `shop_products`: a product **listed in a shop**, with that shop's own price, details, and thumbnail.
- Removing a product from a shop does not delete it from the catalog.
- The same product cannot be added to the same shop twice.

---

## 6. Useful commands

| Command                               | What it does                                                                                    |
| ------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `php artisan migrate:fresh --seed`    | Reset the database and re-create demo data                                                      |
| `php artisan app:install-permissions` | Re-sync roles and permissions without resetting the database (already done by `migrate --seed`) |
| `php artisan test`                    | Run automated tests (uses in-memory SQLite, your MySQL data is safe)                            |
| `npm run build`                       | Build the frontend for production                                                               |
| `npm run types:check`                 | TypeScript type check                                                                           |
| `npm run check`                       | Lint / format checks                                                                            |

---

## 7. Troubleshooting

| Problem                             | Fix                                          |
| ----------------------------------- | -------------------------------------------- |
| `SQLSTATE ... Unknown database`     | Create the `csn_ecommerce` database (Step 2) |
| `Access denied for user`            | Fix `DB_USERNAME` / `DB_PASSWORD` in `.env`  |
| Page is blank or styles are missing | Run `npm run build` (or `npm run dev`)       |
| Product images do not show          | Run `php artisan storage:link`               |
| `No application encryption key`     | Run `php artisan key:generate`               |
