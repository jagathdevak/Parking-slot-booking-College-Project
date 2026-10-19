 <div align="center">

# PARKORA — Smart Parking Garage

### A PHP & MySQL Parking Management System

**Park easy. Leave happy.**

</div>

---

## 📌 About the Project

Parkora is a web-based parking garage management system developed using PHP and MySQL. It allows drivers to find available parking slots, register their vehicles, manage active parking sessions, and calculate parking charges when exiting.

The application also includes an administration dashboard for monitoring garage occupancy, revenue, parking activity, and parking slot availability.

## ✨ Features

### 🚘 Driver Features
- Driver registration and login
- Browse and search available parking slots
- View parking slot details and hourly rates
- Register vehicle number and vehicle type
- Start and manage parking sessions
- Automatic hourly parking charge calculation
- Complete parking sessions and release slots
- View parking history through the driver dashboard

### 🛠️ Admin Features
- Administrator login
- Garage management dashboard
- View occupancy and revenue statistics
- Monitor recent parking activity
- Add new parking slots
- Configure slot details, zones, floors, and hourly rates
- Manage parking availability

### 🅿️ Parking Management
- Multiple parking floors and zones
- Support for cars, bikes, SUVs, and EVs
- Available, occupied, and maintenance slot statuses
- Prevention of multiple active sessions for the same driver
- Prevention of duplicate active parking sessions for the same vehicle
- Database-backed parking records

## 🧰 Tech Stack

| Technology | Purpose |
|---|---|
| PHP | Backend logic and session management |
| MySQL | Database and parking records |
| HTML5 | Web page structure |
| CSS3 | Styling and responsive interface |
| JavaScript | Frontend interactions |
| XAMPP | Local development environment |
| Apache | Web server |

## 📂 Project Structure

```text
Parkora_Parking_Garage/
├── admin/
│   ├── add_slot.php
│   └── dashboard.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── img/
├── includes/
│   ├── header.php
│   └── footer.php
├── config.php
├── index.php
├── login.php
├── logout.php
├── admin_logout.php
├── register.php
├── user_login.php
├── user_dashboard.php
├── parking.php
├── park_vehicle.php
└── README.txt
```

## 🚀 Installation and Setup

### Prerequisites

- XAMPP
- PHP with PDO MySQL support
- MySQL or MariaDB
- A modern web browser

### 1. Download the Project

Clone the repository or download and extract the project folder into your XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\Parkora_Parking_Garage\
```

### 2. Start Apache and MySQL

Open the XAMPP Control Panel and start both services.

### 3. Configure the Database

The application creates the `parking_garage` database and its required tables automatically through `config.php`.

The default local database connection is configured for:

- Host: `127.0.0.1`
- Username: `root`
- Password: empty

If your local MySQL configuration differs, update the connection settings in `config.php`.

### 4. Launch Parkora

Open your browser and navigate to:

```text
http://localhost/Parkora_Parking_Garage/
```

The application automatically inserts sample accounts and parking slots when the corresponding tables are empty.

## 🔑 Demo Login Credentials

The following credentials are configured for the initial local database setup.

**Driver account**

```text
Email: driver@parkora.local
Password: password
```

**Admin account**

```text
Email: admin@parkora.local
Password: password
```

Change or remove demo credentials before any public or production deployment. These credentials are intended only for local demonstration.

## 🗄️ Database Design

Parkora uses four primary database tables:

| Table | Description |
|---|---|
| `users` | Driver account details and password hashes |
| `admins` | Administrator accounts |
| `parking_slots` | Slot codes, locations, vehicle types, rates, and availability |
| `parking_sessions` | Vehicle details, entry and exit times, charges, and session status |

The database tables are created automatically when the application connects successfully.

## 🔄 How It Works

1. **Register:** A driver creates an account or logs in.
2. **Find a slot:** The driver searches available parking spaces.
3. **Start parking:** The driver selects a slot and enters vehicle details.
4. **Track the session:** The active session appears in the driver's dashboard.
5. **Exit:** The system calculates the charge based on parking duration and the slot's hourly rate.
6. **Update availability:** The session is completed and the slot becomes available again.

## 🔐 Security and Limitations

- Passwords are stored using PHP password hashing.
- Prepared statements are used for database queries.
- The payment flow is for demonstration purposes only and does not process real payments.
- Demo accounts use publicly documented credentials and must not be used in production.
- Before production deployment, implement additional protections such as CSRF protection, secure session cookie settings, input validation, and stronger database access controls.

## 🎯 Project Objectives

- Build a dynamic parking management web application.
- Practice PHP backend development and MySQL integration.
- Implement user authentication and session management.
- Manage parking slot availability and vehicle sessions.
- Calculate parking charges based on time and hourly rates.
- Develop an administrative dashboard for garage operations.

## 🎓 Project Information

**Project Name:** Parkora — Smart Parking Garage

**Category:** Web Development

**Purpose:** Educational / College Project

---

<div align="center">

**PARKORA**  
*Smart parking. Simple management.*

</div>
