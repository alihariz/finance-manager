# Finance Manager System

A comprehensive personal finance management application built with a PHP Slim backend and a modern Vue.js frontend.

---

## 🚀 Quick Start

### Branching Strategy

- **main**: Production branch. All API URLs point to the live backend (e.g., DuckDNS or your custom domain, HTTPS).
- **localhost**: Development branch. All API URLs point to your local backend (`http://localhost:8000/api`).

---

## 🏭 Production Setup (`main` branch)

### Backend (DigitalOcean, Apache, HTTPS)

1. **Clone the repository on your server:**
   ```bash
   git clone https://github.com/yourusername/finance-manager.git
   cd finance-manager/backend
   composer install --no-dev
   ```

2. **Set up your MySQL database** (see schema below).

3. **Configure Apache:**
   - Set `DocumentRoot` to `/var/www/html/finance-manager/backend/public`
   - Enable `.htaccess` and mod_rewrite.

4. **Set up your domain** (e.g., DuckDNS) and point it to your server's IP.

5. **Enable HTTPS:**
   ```bash
   sudo apt install certbot python3-certbot-apache -y
   sudo certbot --apache
   ```
   - Enter your domain when prompted.

6. **API Base URL:**  
   ```
   https://your-domain.duckdns.org/api
   ```

### Frontend (Netlify)

1. **Update API URLs in the frontend to use your production backend domain.**
2. **Build and deploy to Netlify:**
   ```bash
   cd frontend
   npm install
   npm run build
   ```
   - Deploy the `dist` folder to Netlify.

3. **Live URLs:**
   - Frontend: `https://your-frontend.netlify.app`
   - Backend: `https://your-domain.duckdns.org/api`

---

## 💻 Local Development Setup (`localhost` branch)

### Backend

1. **Switch to the localhost branch:**
   ```bash
   git checkout localhost
   ```

2. **Start MySQL (e.g., via XAMPP) and create the database:**
   - Use the schema below.

3. **Start the backend server:**
   ```bash
   cd backend
   php -S localhost:8000 -t public
   ```

### Frontend

1. **Ensure API URLs in the frontend point to `http://localhost:8000/api`.**
2. **Start the frontend dev server:**
   ```bash
   cd frontend
   npm install
   npm run serve
   ```
3. **Access the app at:**  
   ```
   http://localhost:8080
   ```

---

## 🗄️ Database Schema

```sql
CREATE DATABASE IF NOT EXISTS `finance_manager` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `finance_manager`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 📋 API Endpoints

| Method | Endpoint                       | Description              |
|--------|------------------------------- |--------------------------|
| POST   | `/api/auth/login`              | User authentication      |
| POST   | `/api/auth/register`           | User registration        |
| GET    | `/api/transactions`            | Get user transactions    |
| POST   | `/api/transactions`            | Create transaction       |
| PUT    | `/api/transactions/{id}`       | Update transaction       |
| DELETE | `/api/transactions/{id}`       | Delete transaction       |

---

## 📝 Notes

- **Production:** All URLs and configs point to your live server and domain.
- **Localhost:** All URLs and configs point to `localhost:8000` for backend and `localhost:8080` for frontend.
- **Switch branches as needed for development or deployment.**

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes
4. Push to your branch
5. Open a Pull Request

---

**Made with ❤️ for personal finance management**