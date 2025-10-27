-- =====================================================
-- 🚀 SaveEat MVP - Full Clean Schema + Sample Data
-- =====================================================

-- 1️⃣ Drop and recreate the database
DROP DATABASE IF EXISTS saveeat;
CREATE DATABASE saveeat CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE saveeat;

-- 2️⃣ Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 3️⃣ USERS TABLE
-- =====================================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','vendor','consumer') NOT NULL DEFAULT 'consumer',
  status ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4️⃣ VENDORS TABLE
-- =====================================================
CREATE TABLE vendors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  business_name VARCHAR(150) DEFAULT '',
  location VARCHAR(200) DEFAULT '',
  contact_phone VARCHAR(50) DEFAULT '',
  logo_path VARCHAR(255) DEFAULT NULL,
  approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_vendor_user (user_id),
  CONSTRAINT fk_vendor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5️⃣ CATEGORIES TABLE
-- =====================================================
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6️⃣ FOOD ITEMS TABLE
-- =====================================================
CREATE TABLE food_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendor_id INT NOT NULL,
  category_id INT DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  discount_percent INT DEFAULT 0,
  expiry_date DATE NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  image_path VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive','expired') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_food_vendor (vendor_id),
  INDEX idx_food_category (category_id),
  CONSTRAINT fk_food_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
  CONSTRAINT fk_food_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7️⃣ ORDERS TABLE
-- =====================================================
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  consumer_id INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid','cancelled','completed') NOT NULL DEFAULT 'paid',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_orders_consumer (consumer_id),
  CONSTRAINT fk_orders_consumer FOREIGN KEY (consumer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 8️⃣ ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  food_item_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  discount_percent INT NOT NULL DEFAULT 0,
  line_total DECIMAL(10,2) NOT NULL,
  INDEX idx_oi_order (order_id),
  INDEX idx_oi_food (food_item_id),
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_oi_food FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 9️⃣ AUDIT LOGS TABLE
-- =====================================================
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action VARCHAR(255) NOT NULL,
  entity_type VARCHAR(50) DEFAULT NULL,
  entity_id INT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_audit_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 🔟 SEED DATA
-- =====================================================

-- Admin, Vendor, Consumer Users
INSERT INTO users (name, email, password_hash, role, status, created_at, updated_at) VALUES
('Admin User', 'admin@saveeat.local', '$2y$10$hA1kK1Y5GvFQY7v9wAq3KOPKp5M3o0T6B0z5x1w8zFjv3d7Z8Oehu', 'admin', 'active', NOW(), NOW()),
('Vendor User', 'vendor@saveeat.local', '$2y$10$hA1kK1Y5GvFQY7v9wAq3KOPKp5M3o0T6B0z5x1w8zFjv3d7Z8Oehu', 'vendor', 'active', NOW(), NOW()),
('Consumer User', 'consumer@saveeat.local', '$2y$10$hA1kK1Y5GvFQY7v9wAq3KOPKp5M3o0T6B0z5x1w8zFjv3d7Z8Oehu', 'consumer', 'active', NOW(), NOW());

-- Vendor details
INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, created_at, updated_at)
VALUES (2, 'Kelvins Kitchen', 'Nairobi, Kenya', '+254712345678', 1, NOW(), NOW());

-- Categories
INSERT INTO categories (name, description, created_at, updated_at) VALUES
('Pizza', 'Delicious Italian pizzas', NOW(), NOW()),
('Burgers', 'Classic and gourmet burgers', NOW(), NOW()),
('Drinks', 'Refreshing beverages', NOW(), NOW()),
('Desserts', 'Sweet treats', NOW(), NOW());

-- Food items
INSERT INTO food_items (vendor_id, category_id, name, description, price, discount_percent, expiry_date, stock, image_path, status, created_at, updated_at) VALUES
(1, 1, 'Margherita Pizza', 'Classic cheese pizza', 8.99, 10, '2025-12-31', 20, 'public/uploads/pizza.jpg', 'active', NOW(), NOW()),
(1, 2, 'Beef Burger', 'Juicy beef burger', 6.49, 0, '2025-12-31', 15, 'public/uploads/burger.jpg', 'active', NOW(), NOW()),
(1, 3, 'Cola', 'Refreshing soft drink', 1.99, 0, '2025-12-31', 50, 'public/uploads/cola.jpg', 'active', NOW(), NOW()),
(1, 4, 'Chocolate Cake', 'Rich chocolate dessert', 4.99, 5, '2025-12-31', 10, 'public/uploads/cake.jpg', 'active', NOW(), NOW());

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Done message
SELECT '✅ SaveEat database successfully rebuilt with sample data.' AS status_message;
