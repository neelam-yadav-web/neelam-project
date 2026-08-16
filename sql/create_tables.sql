-- SQL schema for the fast-food demo

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(512) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(200) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address TEXT NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Sample menu rows
INSERT INTO menu_items (name, description, price, image) VALUES
('Classic Burger','Juicy beef patty, cheese, lettuce & tomato.',199.00,'https://source.unsplash.com/600x400/?burger'),
('Crispy Fries','Golden shoestring fries with seasoning.',79.00,'https://source.unsplash.com/600x400/?fries'),
('Chicken Nuggets','Crispy bite-sized chicken pieces.',129.00,'https://source.unsplash.com/600x400/?chicken'),
('Veggie Wrap','Fresh veggies in a spiced wrap.',149.00,'https://source.unsplash.com/600x400/?wrap');
