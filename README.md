# Auto‑Body Booking System 🕛

A self‑hosted appointment booking platform tailored for WrapLab Wrap Shop.  
Allows customers to browse services, book appointments, and leave feedback; lets employees manage their schedules and tasks; and gives admins full control over services, employees, and users.

---

## 📋 Prerequisites

- **XAMPP** (Apache + PHP + MySQL)  
  Download & install: https://www.apachefriends.org/download.html

---

## 🔧 Setup & Installation

1. **Install XAMPP**  
   - Run the XAMPP installer and follow the prompts.  
   - In the XAMPP Control Panel, ensure **Apache** and **MySQL** modules are enabled and click **Start**.

2. **Clone or copy the project**  
   - Place the entire `Autobody-Booking-System` folder into your XAMPP `htdocs` directory.  
     ```bash
     C:\xampp\htdocs\Autobody-Booking-System
     ```

3. **Create the database**  
   - Open phpMyAdmin at http://localhost/phpmyadmin  
   - Create a new database named `autobody_db` (or whatever you prefer).  
   - Import the provided `autobody_db.sql` (or `database_setup.sql`) file from the project root to create all tables and seed data.

4. **Configure database connection**  
   - In `db_connect.php`, update the DSN, username, and password to match your setup:
     ```php
     <?php
     $pdo = new PDO(
       'mysql:host=127.0.0.1;dbname=autobody_db;charset=utf8mb4',
       'root',
       ''
     );
     ```
   - No further changes needed if you’re using default XAMPP credentials.

5. **Access the application**  
   - Navigate to:
     ```
     http://localhost/Autobody-Booking-System/index.php
     ```
   - You can now:
     - **Sign up** or **Log in** 
     - Log in as an **Employee** 
     - Log in as an **Admin** 
     - Browse the website as a **User**

---

## 🛠️ What’s Inside

- **Authentication**  
  - `signup.php` / `login.php` – user registration & login  
  - `update_password_customer.php`, `update_password_employee.php` – password updates

- **Dashboards**  
  - `customer.php` – booking, profile edit, feedback  
  - `employee.php` – tasks, schedule, availability  
  - `admin.php` – manage services, employees, coupons

- **Business logic & APIs**  
  - `book_service.php`, `cancel_appointment.php`, `reassign_appointment.php`  
  - `set_availability.php`, `update_status.php`, `submit_feedback.php`  
  - `services_api.php`, `appointments.php`, `appointment_details.php`

- **CRUD pages**  
  - `add_service.php` / `edit_service.php` / `delete_service.php`  
  - `add_employee.php` / `edit_employee.php` / `delete_employee.php`  
  - `add_coupon.php` / `edit_coupon.php` / `delete_coupon.php`

- **Assets**  
  - `styles.css` – site‑wide styles  
  - `main.js`    – interactivity (slot filtering, appointment loader)

- **Database schema**  
  - `autobody_db.sql`  

---

## 📖 User Guide

A comprehensive PDF user guide is available in our GitHub repository. It covers:

- Account creation & management  
- Browsing services & booking flow  
- Employee & admin workflows  

Download the **User Guide** here:  
👉 [UserGuide.pdf](https://raw.githubusercontent.com/Farhad-Alizada/Autobody-Booking-System/main/UserGuide.pdf)

---

## 🙏 Acknowledgments

Built as part of **CPSC 471 – Winter 2025**, University of Calgary.

---

## Contributors

- Harris Jan      harris.jan@ucalgary.ca  
- Farhad Alizada  farhad.alizada@ucalgary.ca  
- Bobby Brar      bobby.brar@ucalgary.ca
