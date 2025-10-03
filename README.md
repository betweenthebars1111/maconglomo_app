# maconglomo_app
A pharmaceutical management software designed to streamline inventory, client records, and employee management for efficient and secure operations.

Got it 👍 That’s a very important detail — XAMPP and Laragon both run Apache/MySQL on the same ports, so they’ll conflict. We should definitely add a **pre-install note** about uninstalling or stopping XAMPP first.

Here’s the updated **installation guide (with the XAMPP warning included):**

---

# 🚀 Project Installation Guide (Windows with Laragon)

## ⚠️ Before You Start

* If you are currently using **XAMPP** or another local server, uninstall it or stop its services.

  > Apache/MySQL from XAMPP will conflict with Laragon since they use the same ports (80 and 3306 by default).
* Restart your computer after uninstalling XAMPP to ensure no background processes are left running.

---

## 1. Install Laragon

1. Download Laragon from 👉 [https://laragon.org/download/](https://laragon.org/download/).
2. Run the installer and follow the setup wizard.

   * Default install path is usually `C:\laragon`.
3. Open **Laragon** and click **Start All** (it will start Apache & MySQL by default).

---

## 2. Set Up the Project Folder

1. Go to your Laragon `www` directory (default: `C:\laragon\www`).
2. Copy your project folder (e.g. `maconglomo_app`) into `www`.

   * Path example: `C:\laragon\www\maconglomo_app`.

---

## 3. Import the Database

1. Open Laragon, right-click → **MySQL → phpMyAdmin**.
2. Login with:

   * **Username:** `root`
   * **Password:** (leave empty by default in Laragon).
3. Create a new database (name it `maconglomo_db`).
4. Go to **Import**, choose `maconglomo_db.sql`, and import it.

   * This will create all your tables and schema.

---

## 4. Configure Database Connection

Edit `config/database.php` and set the credentials:

```php
$host = '127.0.0.1';
$db   = 'maconglomo_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
```

---

## 5. Install Composer

1. Download Composer from 👉 [https://getcomposer.org/download/](https://getcomposer.org/download/).
2. Run the installer (it should detect Laragon’s PHP automatically).
3. Verify installation:

   ```bash
   composer -V
   ```

---

## 6. Install Project Dependencies

1. Open a terminal and go to your project folder:

   ```bash
   cd C:\laragon\www\maconglomo_app
   ```
2. Run:

   ```bash
   composer install
   ```

   * This will generate the `vendor/` folder and install libraries (e.g. Dompdf/TCPDF/mPDF for PDF support).

---

## 7. Run the Project

1. Make sure Laragon is running.
2. Open your browser and visit:

   ```
   http://localhost/maconglomo_app
   ```
3. The project should now run 🎉

---

👉 Do you want me to **turn this into a clean `README.md`** (with headings, code blocks, and step numbers) so you can just drop it into your GitHub repo?
