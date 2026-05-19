# MillyGram 🟣

> A lightweight social media web application built with pure PHP and MySQL — no frameworks, no libraries, just clean code.

---

## Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [How It Works](#how-it-works)
- [Database Schema](#database-schema)
- [PHP Functions Reference](#php-functions-reference)
- [Validation Rules](#validation-rules)
- [Author](#author)

---

## About the Project

MillyGram is a full-stack social media demo application where users can register, verify their account, log in, publish posts, and view their profile. The project was built without any PHP frameworks to demonstrate understanding of core PHP concepts such as sessions, form handling, password hashing, and database interaction.

---

## Features

| Feature | Description |
|---|---|
| 📝 Sign Up | Register with name, surname, email and password |
| ✉️ Email Verification | 6-digit code sent after registration to verify account |
| 🔑 Sign In | Login with email and password |
| 📰 Posts Feed | View all posts from all users, newest first |
| ✏️ Create Post | Publish a post up to 255 characters |
| 🗑️ Delete Post | Delete your own posts |
| 👤 Profile Page | View your personal account information |
| 🚪 Logout | Securely destroy session and redirect to login |

---

## Tech Stack

- **Backend:** PHP 8+ (procedural style)
- **Database:** MySQL via MySQLi extension
- **Frontend:** HTML5, CSS3 (custom design, no frameworks)
- **Fonts:** Google Fonts — Cormorant Garamond + Inter
- **Security:** `password_hash()` / `password_verify()` with BCRYPT
- **Version Control:** Git & GitHub

---

## Project Structure

```
MillyGram/
│
├── actions/
│   ├── broker.php          # Central handler for ALL POST form submissions
│   └── logout.php          # Destroys session and redirects to index
│
├── assets/
│   └── style.css           # Global stylesheet
│
├── config/
│   └── db.php              # Database connection (MySQLi)
│
├── includes/
│   └── dbQuery.php         # All reusable database functions
│
├── pages/
│   ├── posts.php           # Posts feed — view, create, delete posts
│   ├── profile.php         # User profile page
│   └── verification.php    # Email verification page
│
└── index.php               # Entry point — Sign In / Sign Up forms
```

---

## Getting Started

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP / WAMP / MAMP recommended for local development)

### Installation

**1. Clone the repository:**
```bash
git clone https://github.com/millyDev-bit/social-media-demo.git
cd social-media-demo
```

**2. Create the database:**

Open phpMyAdmin (or MySQL CLI) and create a database named `terminals`, then run this SQL:

```sql
CREATE DATABASE terminals;
USE terminals;

CREATE TABLE `idusers` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `name`      VARCHAR(50)  NOT NULL,
  `surname`   VARCHAR(50)  NOT NULL,
  `login`     VARCHAR(100) NOT NULL UNIQUE,
  `password`  VARCHAR(255) NOT NULL,
  `verifcode` VARCHAR(255) NOT NULL,
  `verified`  TINYINT(1)   DEFAULT 0
);

CREATE TABLE `post` (
  `id`      INT AUTO_INCREMENT PRIMARY KEY,
  `userid`  INT          NOT NULL,
  `content` VARCHAR(255) NOT NULL,
  `date`    DATE         NOT NULL,
  FOREIGN KEY (`userid`) REFERENCES `idusers`(`id`) ON DELETE CASCADE
);
```

**3. Configure the database connection** in `config/db.php`:
```php
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "terminals";
```

**4. Start your local server** (e.g. XAMPP) and open the project in your browser:
```
http://localhost/social-media-demo/
```

---

## How It Works

### Authentication Flow

```
User fills Sign Up form (index.php)
        ↓
Form submits POST → actions/broker.php
        ↓
broker.php validates input (regex checks)
        ↓
Password is hashed with password_hash() + BCRYPT
A 6-digit code is generated and hashed
User is saved to DB with verified = 0
        ↓
User is redirected to pages/verification.php
        ↓
User enters the code → broker.php verifies it
with password_verify()
        ↓
verified = 1 is set in DB
User is redirected to index.php to log in
```

### Session Management

- After successful login, user data is stored in `$_SESSION["user"]`
- All protected pages check `isset($_SESSION["user"])` at the top
- Logout destroys the session via `session_destroy()`

---

## Database Schema

### Table: `idusers`

| Column | Type | Description |
|---|---|---|
| id | INT | Auto-increment primary key |
| name | VARCHAR(50) | First name |
| surname | VARCHAR(50) | Last name |
| login | VARCHAR(100) | Email address (unique) |
| password | VARCHAR(255) | Bcrypt hashed password |
| verifcode | VARCHAR(255) | Bcrypt hashed verification code |
| verified | TINYINT(1) | 0 = not verified, 1 = verified |

### Table: `post`

| Column | Type | Description |
|---|---|---|
| id | INT | Auto-increment primary key |
| userid | INT | Foreign key → idusers.id |
| content | VARCHAR(255) | Post text content |
| date | DATE | Date of creation |

---

## PHP Functions Reference

All database functions are located in `includes/dbQuery.php`.

---

### `findIt($tb, $focus)`
Fetches all rows from a table, selecting specific columns.

```php
findIt("idusers", "login")
// SELECT login FROM `idusers`
// Returns: array of all logins
```

**Used for:** checking if an email is already registered during Sign Up.

---

### `findItWhere($tb, $focus, $where, $value)`
Fetches rows from a table with a WHERE condition.

```php
findItWhere("idusers", "id, login, password, verified", "login", "user@email.com")
// SELECT id, login, password, verified FROM `idusers` WHERE `login`='user@email.com'
// Returns: array with matched user row
```

**Used for:** loading user data on login, fetching verification code, loading profile info.

---

### `insertUser($tb, $vals)`
Inserts a new user into the database.

```php
$data = [
    "name"      => "Milena",
    "surname"   => "Dev",
    "login"     => "milena@email.com",
    "password"  => password_hash("Secret123", PASSWORD_BCRYPT),
    "verifcode" => password_hash("482910", PASSWORD_BCRYPT),
    "verified"  => 0
];
insertUser("idusers", $data);
```

**Used for:** registering a new user during Sign Up.

---

### `insertPost($userid, $content)`
Inserts a new post linked to a user.

```php
insertPost(1, "Hello MillyGram!");
// INSERT INTO `post` (userid, content, date) VALUES (1, 'Hello MillyGram!', '2026-05-19')
```

**Used for:** publishing a post from the Posts page.

---

### `getAllPosts()`
Fetches all posts with author name and surname using a JOIN.

```php
getAllPosts();
// SELECT p.id, p.content, p.date, p.userid, u.name, u.surname
// FROM post p JOIN idusers u ON p.userid = u.id
// ORDER BY p.id DESC
// Returns: array of all posts newest first
```

**Used for:** rendering the posts feed on `pages/posts.php`.

---

### `getPostsByUser($userid)`
Fetches posts belonging to a specific user.

```php
getPostsByUser(1);
// Returns: array of posts by user with id = 1
```

**Used for:** future profile posts section.

---

### `deletePost($postId, $userId)`
Deletes a post only if it belongs to the current user (double safety check).

```php
deletePost(5, 1);
// DELETE FROM `post` WHERE `id`=5 AND `userid`=1
```

**Used for:** delete button on posts feed — users can only delete their own posts.

---

## Validation Rules

All validation is done in `actions/broker.php` using PHP regex constants.

| Field | Rule | Regex |
|---|---|---|
| Name / Surname | Letters only, 2–50 characters | `/^[a-zA-Zа-яА-ЯёЁ\s\-]{2,50}$/u` |
| Email (login) | Must be a valid email format | `/^[^\s@]+@[^\s@]+\.[^\s@]+$/` |
| Password | Min 8 chars, at least 1 uppercase letter and 1 digit | `/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/` |
| Verification code | Exactly 6 digits | `/^\d{6}$/` |

---

## Author

Made by **Milena** — [github.com/millyDev-bit](https://github.com/millyDev-bit)
