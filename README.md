# Saviskar 2025 Registration Portal 🎓✨

A complete, full-stack registration and event management system built for **Saviskar** – the biggest annual college festival. This portal handles student sign-ups, team creations, event cart management, and secure email OTP verifications.

---

## 🌟 Key Features

- **Secure Authentication**: User sign-ups with email OTP verification powered by **AWS SES**.
- **Event Management**: Browse technical, non-technical, and cultural events.
- **Team Creation & Joining**: Users can create teams for group events or join existing ones using a unique Team Code.
- **Cart System**: Users can add multiple events to their cart and finalize their registrations in one go.
- **Faculty Dashboard**: Special access for faculty in-charges to manage students.

## 🛠️ Tech Stack

- **Frontend**: Vanilla HTML, CSS, JavaScript (Dynamic fetching and DOM manipulation).
- **Core API Backend**: **PHP** for robust business logic (Signup, Login, Teams, Events).
- **Email Service Backend**: **Node.js & Express** serving as an email microservice (AWS SES).
- **Database**: **MySQL** (Relational database for users, teams, and events).

---

## 🚀 Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing.

### 1. Prerequisites
- **XAMPP / MAMP / WAMP** (for PHP and MySQL).
- **Node.js** (v14 or higher).
- **AWS SES Account** (for sending OTPs).

### 2. Database Setup
1. Start your **MySQL** server (via XAMPP).
2. The system will **automatically create** the database (`saviskar_db`) and all required tables when you first hit the API! The `db.php` script handles automatic migrations.
3. Make sure your MySQL root user has no password (default for local XAMPP). If it does, update `saviskar_api/db.php`.

### 3. Environment Variables (.env)
Create a `.env` file inside the `backend/` folder and add your AWS SES credentials:
```env
AWS_REGION=your-aws-region
SES_SOURCE_EMAIL=your-verified-ses-email@example.com
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
```
*(Note: Use standard IAM credentials that have the `ses:SendRawEmail` permission.)*

### 4. Install Dependencies
Open a terminal, navigate to the `backend/` folder, and install the Node.js dependencies:
```bash
cd backend
npm install
```

### 5. Start the Servers
You need to run both the Node.js server (for OTPs and serving frontend) and the PHP server (for the Core API).

**Start the PHP API Server:**
Open a terminal in the root directory of the project and start the built-in PHP server:
```bash
php -S 127.0.0.1:8000
```

**Start the Node.js Backend:**
Open another terminal in the `backend/` folder:
```bash
node server.js
```

### 6. View the Site
Open your browser and navigate to:
👉 **http://localhost:3001**

---

## 🤝 How it works

1. The user visits `http://localhost:3001` (Served by Express in `server.js`).
2. When the user requests an OTP, the frontend calls the Node.js backend (`/api/send-otp`).
3. When the user submits the signup form, the frontend communicates with the PHP API running on port `8000` (`http://localhost:8000/saviskar_api/signup.php`).

---
*Built with ❤️ for Saviskar 2025.*
