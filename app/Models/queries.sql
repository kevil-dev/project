CREATE DATABASE IF NOT EXISTS inventory
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE inventory;

CREATE TABLE categories (
  id   TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

CREATE TABLE units (
  id   TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(20)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_units_name (name)
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
  ('Syrup'), ('Churna'), ('Tablet'), ('Capsule'),
  ('Oil'), ('Juice'), ('Powder'), ('Balm');

INSERT INTO units (name) VALUES
  ('ml'), ('g'), ('pcs'), ('bottle'), ('strip');

CREATE TABLE products (
  id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150)   NOT NULL,
  price       DECIMAL(10,2)  NOT NULL,
  category_id TINYINT UNSIGNED NOT NULL,
  unit_id     TINYINT UNSIGNED NOT NULL,
  image_path  VARCHAR(255)   NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO products (name, price, category_id, unit_id, image_path)
SELECT
  CONCAT(
    ELT(1 + (n % 10), 'Triphala','Ashwagandha','Tulsi','Neem','Amla',
                      'Brahmi','Giloy','Shatavari','Arjuna','Manjistha'),
    ' ',
    ELT(1 + (n % 8),  'Syrup','Churna','Tablet','Capsule',
                      'Oil','Juice','Powder','Balm'),
    ' ',
    ELT(1 + (n % 5),  '50','100','200','500','1000'),
    ELT(1 + (n % 2),  'ml','g')
  ) AS name,
  ROUND(10 + (n % 9990) + (n % 100) / 100, 2) AS price,
  1 + (n % 8)  AS category_id,
  1 + (n % 5)  AS unit_id,
  CONCAT('/uploads/pool/img_', LPAD(1 + (n % 100), 3, '0'), '.jpg') AS image_path
FROM (
  SELECT (d6.d*100000 + d5.d*10000 + d4.d*1000
        + d3.d*100   + d2.d*10    + d1.d + 1) AS n
  FROM (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d1,
       (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d2,
       (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d3,
       (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d4,
       (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d5,
       (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d6
) numbers;

ALTER TABLE products
  ADD INDEX idx_name          (name),
  ADD INDEX idx_cat_unit_name (category_id, unit_id, name),
  ADD INDEX idx_unit          (unit_id),
  ADD CONSTRAINT fk_products_category
      FOREIGN KEY (category_id) REFERENCES categories(id),
  ADD CONSTRAINT fk_products_unit
      FOREIGN KEY (unit_id)     REFERENCES units(id);

SHOW TABLES;

DESCRIBE products;

SHOW CREATE TABLE products;

SELECT COUNT(*) FROM products;

SELECT * FROM products LIMIT 10;

EXPLAIN SELECT id, name, price FROM products
WHERE name LIKE 'Tri%'
ORDER BY name, id
LIMIT 20;

EXPLAIN SELECT id, name, price FROM products
WHERE name LIKE '%rup%'
ORDER BY name, id
LIMIT 20;

EXPLAIN SELECT id, name, price FROM products
WHERE category_id = 1 AND unit_id = 1 AND name LIKE 'Tri%'
ORDER BY name, id
LIMIT 20;

SELECT id, name, price, category_id, unit_id, image_path
FROM products
WHERE name LIKE ?
  AND category_id = ?
  AND unit_id = ?
ORDER BY name, id
LIMIT 50;

SELECT id, name FROM categories;

SELECT id, name FROM units;