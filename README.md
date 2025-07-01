# Finance Manager System

A comprehensive personal finance management application built with PHP backend and modern frontend framework.

## 🚀 Quick Start

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache, MySQL, PHP)
- [Node.js](https://nodejs.org/) (v14 or higher)
- npm (comes with Node.js)

### Installation

1. **Install XAMPP**
   ```bash
   # Download from https://www.apachefriends.org/
   # Install following the setup wizard
   ```

2. **Clone/Move Project**
   ```bash
   # Move your project to XAMPP htdocs folder
   # Windows: C:\xampp\htdocs\finance-manager
   # Mac: /Applications/XAMPP/htdocs/finance-manager
   ```

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

### Database Setup

1. **Access phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Create Database**
   
   Click "SQL" tab and execute this script:

   ```sql
   -- Create the finance_manager database
   CREATE DATABASE IF NOT EXISTS `finance_manager` 
   CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;

   -- Use the database
   USE `finance_manager`;

   -- Create users table
   CREATE TABLE `users` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
     `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `created_at` datetime DEFAULT current_timestamp(),
     `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     PRIMARY KEY (`id`),
     UNIQUE KEY `username` (`username`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

   -- Create transactions table
   CREATE TABLE `transactions` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `amount` decimal(10,2) NOT NULL,
     `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
     `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
     `date` date NOT NULL,
     `user_id` int(11) NOT NULL,
     `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
     `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     PRIMARY KEY (`id`),
     KEY `user_id` (`user_id`),
     CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

### Running the Application

#### Backend Server

```bash
# Navigate to backend directory
cd backend

# Start PHP development server
php -S localhost:8000 -t public
```

#### Frontend Server

```bash
# Open new terminal
# Navigate to frontend directory
cd frontend

# Install dependencies (first time only)
npm install

# Start development server
npm run serve
```

### Access Application

Open your browser and navigate to:
```
http://localhost:8080
```

## 📁 Project Structure

```
finance-manager/
├── backend/
│   ├── public/
│   ├── src/
│   └── [PHP files]
├── frontend/
│   ├── src/
│   ├── public/
│   └── package.json
└── README.md
```

## 🔧 Configuration

### Backend Configuration
- **Server**: `http://localhost:8000`
- **Database**: MySQL via XAMPP
- **API Endpoints**: RESTful API structure

### Frontend Configuration
- **Development Server**: `http://localhost:8080`
- **API Base URL**: `http://localhost:8000`

## 🗃️ Database Schema

### Users Table
| Column | Type | Description |
|--------|------|-------------|
| id | int(11) | Primary key, auto-increment |
| username | varchar(50) | Unique username |
| password | varchar(255) | Hashed password |
| created_at | datetime | Account creation timestamp |
| updated_at | datetime | Last update timestamp |

### Transactions Table
| Column | Type | Description |
|--------|------|-------------|
| id | int(11) | Primary key, auto-increment |
| description | varchar(255) | Transaction description |
| amount | decimal(10,2) | Transaction amount |
| category | varchar(100) | Transaction category |
| type | enum | 'income' or 'expense' |
| date | date | Transaction date |
| user_id | int(11) | Foreign key to users table |
| created_at | timestamp | Record creation timestamp |
| updated_at | timestamp | Last update timestamp |

## 🚨 Troubleshooting

### Common Issues

**Backend server won't start**
```bash
# Try different port if 8000 is busy
php -S localhost:8001 -t public
```

**Frontend server won't start**
```bash
# Clear npm cache
npm cache clean --force
npm install
npm run serve
```

**Database connection error**
- Ensure MySQL is running in XAMPP
- Check database name is `finance_manager`
- Verify database credentials

**XAMPP services won't start**
- Run XAMPP as Administrator
- Check if ports 80 (Apache) and 3306 (MySQL) are free
- Check XAMPP error logs

## 📋 Development Commands

### Backend
```bash
# Start server
php -S localhost:8000 -t public

# Run with different port
php -S localhost:8001 -t public
```

### Frontend
```bash
# Install dependencies
npm install

# Start development server
npm run serve

# Build for production
npm run build

# Run tests
npm run test
```

## 🔒 Security Notes

- Passwords are hashed before storage
- Foreign key constraints maintain data integrity
- Input validation implemented on both frontend and backend
- CORS configured for development environment

## 🛠️ Tech Stack

**Backend**
- PHP 7.4+
- MySQL
- RESTful API architecture

**Frontend**
- HTML5/CSS3/JavaScript
- Modern JavaScript framework (Vue.js/React)
- Responsive design
- AJAX for API communication

**Development Tools**
- XAMPP (Apache, MySQL, PHP)
- Node.js & npm
- phpMyAdmin for database management

## 📄 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | User authentication |
| POST | `/api/register` | User registration |
| GET | `/api/transactions` | Get user transactions |
| POST | `/api/transactions` | Create new transaction |
| PUT | `/api/transactions/{id}` | Update transaction |
| DELETE | `/api/transactions/{id}` | Delete transaction |

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

If you encounter any issues:

1. Check this README for troubleshooting steps
2. Verify all prerequisites are installed
3. Ensure XAMPP services are running
4. Check browser console and terminal for error messages

## 🔄 Shutdown Procedure

To properly stop the application:

1. **Stop Frontend**: `Ctrl+C` in frontend terminal
2. **Stop Backend**: `Ctrl+C` in backend terminal  
3. **Stop XAMPP**: Click "Stop" for Apache and MySQL in XAMPP Control Panel

---

**Made with ❤️ for personal finance management**