# 📘 SchedulinkXM

SchedulinkXM is a web-based platform for managing university exam scheduling. It allows administrators to handle hall allocations, lecturers, payments, reports, and more from a centralized dashboard.

---

## 🚀 Tech Stack

- **Backend:** PHP
- **Frontend:** HTML5, CSS3, JavaScript
- **Database:** MySQL



## 🔧 How to Set Up SchedulinkXM

### 🖥️ 1. **Install Required Software**

Make sure you have the following installed:

* **XAMPP** (or WAMP/LAMP): Includes Apache, MySQL, PHP.

  * Download: [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)

---

### 📁 2. **Extract the Project Files**

1. Extract `SchedulinkXM-main.zip` if not already done.
2. Move the folder to your web server’s root directory:

   * **XAMPP (Windows):**

     ```
     C:\xampp\htdocs\SchedulinkXM
     ```
   * **LAMP/WAMP (Linux/macOS):**

     ```
     /var/www/html/SchedulinkXM
     ```

---

### 🗄️ 3. **Set Up the Database**

1. Start **Apache** and **MySQL** via XAMPP/WAMP control panel.
2. Open **phpMyAdmin** by visiting:

   ```
   http://localhost/phpmyadmin
   ```
3. Create a new database:

   ```
   Name: schedulink
   ```
4. Import the SQL file:

   * Click on the **schedulink** database.
   * Go to the **Import** tab.
   * Choose the file: `shedulink.sql` (found in the project root).
   * Click **Go**.

---

### ⚙️ 4. **Configure Database Connection**

Edit the following files and update them with your MySQL credentials (usually root with no password on XAMPP):

* **`config.php`**
* **`includes/db_connect.php`**

Example:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'schedulink';
```

  
### 🌐 5. **Run the Application**

In your browser, visit:

```
http://localhost/SchedulinkXM
```

You should see the login or homepage of the app.

---


