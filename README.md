# Employee Record Tracker (PHP & MySQL CRUD Application)

A responsive web-based **Employee Record Tracker** built with **PHP, MySQL, HTML5, CSS3, and Bootstrap 5.3** in fulfillment of the **Web Systems Technologies** Final Project Guidelines.

---

## Project Structure

- `db_connect.php` — MySQL database connection script (pre-configured for XAMPP + ready-to-fill template for InfinityFree)
- `database.sql` — SQL schema (`employees` table with 11 fields, primary key, and 6 sample records)
- `index.php` — **SELECT** operation: Dashboard KPI summary cards, search/filter bar, and responsive Bootstrap 5 table
- `add_employee.php` — **INSERT** operation: Form to add a new employee record with validation
- `edit_employee.php` — **UPDATE** operation: Form to edit and update an existing employee record
- `view_employee.php` — **SELECT** single record: Detailed employee profile view
- `delete_employee.php` — **DELETE** operation: Removes an employee record from the database
- `assets/css/style.css` — Custom stylesheet enhancing Bootstrap 5 components

---

## Database Schema (`employees` Table)

| Field | Data Type | Key / Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT(11)` | `PRIMARY KEY`, `AUTO_INCREMENT` | Unique record identifier |
| `employee_code` | `VARCHAR(20)` | `UNIQUE`, `NOT NULL` | Employee ID code (e.g., `EMP-2026-001`) |
| `full_name` | `VARCHAR(100)` | `NOT NULL` | Full name of employee |
| `email` | `VARCHAR(100)` | `NOT NULL` | Work email address |
| `phone` | `VARCHAR(25)` | `NOT NULL` | Contact phone number |
| `department` | `VARCHAR(60)` | `NOT NULL` | Assigned department |
| `position` | `VARCHAR(80)` | `NOT NULL` | Job title / position |
| `salary` | `DECIMAL(10,2)` | `NOT NULL` | Monthly salary in PHP |
| `hire_date` | `DATE` | `NOT NULL` | Date hired |
| `status` | `ENUM(...)` | `NOT NULL`, Default `'Active'` | `Active`, `On Leave`, `Probation`, or `Resigned` |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Record creation timestamp |

---

## Local Setup Using XAMPP

1. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Copy this project folder into `C:\xampp\htdocs\employee-record-tracker`.
3. Open your browser and visit `http://localhost/employee-record-tracker/`
   - *Note:* `db_connect.php` automatically creates the `employee_tracker_db` database and `employees` table on localhost! You can click **Load Sample Records** right from the dashboard, or import `database.sql` manually in `http://localhost/phpmyadmin`.

---

## InfinityFree Deployment Steps

1. Log in to [InfinityFree](https://www.infinityfree.com/) and create a new **Hosting Account**.
2. Go to **Control Panel -> MySQL Databases** and create a new database (e.g., `if0_xxxxxxx_employee_db`).
3. Copy your **MySQL Hostname**, **MySQL Username**, **MySQL Password**, and **MySQL Database Name**.
4. Open `db_connect.php`, uncomment Section 2 (**INFINITYFREE HOSTING CREDENTIALS**), and paste your InfinityFree database credentials.
5. Open **phpMyAdmin** for your new database in InfinityFree, click the **Import** tab, and upload `database.sql`.
6. Open **File Manager** in InfinityFree, navigate into the `htdocs/` folder, and upload all files from this project (`index.php`, `db_connect.php`, `add_employee.php`, `edit_employee.php`, `view_employee.php`, `delete_employee.php`, and the `assets/` folder).
7. Visit your live InfinityFree URL to test all CRUD operations.
