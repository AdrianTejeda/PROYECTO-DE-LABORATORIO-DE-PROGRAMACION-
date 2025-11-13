CREATE DATABASE IF NOT EXISTS horizonte2025
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `horizonte2025`;

-- Tabla: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: categorias
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: productos
CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nombre VARCHAR(200) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento INT NOT NULL DEFAULT 0,
  imagen VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  unidad VARCHAR(30) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario admin por defecto (password: admin123 - CAMBIAR)
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES
('Administrador', 'admin@horizonte.com', '$2y$10$V9DPvQbB9g7dY0q1oWm8OeYbLQ1mQ6aXy0w6mZ4ZlqB/Ngrx2g8w2', 'admin', 1);

-- Categorías ejemplo
INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Cemento y áridos', 'Bolsas, arena, piedra y mezclas', 1),
('Hierros y mallas', 'Varillas, mallas electrosoldadas y perfiles', 1),
('Ladrillos y bloques', 'Ladrillo hueco, block de hormigón', 1),
('Pinturas y revestimientos', 'Látex, impermeabilizantes y texturados', 1),
('Herramientas y accesorios', 'Manuales y eléctricas, consumibles', 1);

-- Productos ejemplo
INSERT INTO productos (categoria_id, nombre, descripcion, precio, descuento, imagen, activo, unidad) VALUES
((SELECT id FROM categorias WHERE nombre='Cemento y áridos' LIMIT 1), 'Cemento 50kg', 'Bolsa de 50 kg, uso general.', 12999, 10, NULL, 1, 'bolsa'),
((SELECT id FROM categorias WHERE nombre='Cemento y áridos' LIMIT 1), 'Arena fina m³', 'Arena seleccionada para revoques.', 25000, 0, NULL, 1, 'm³'),
((SELECT id FROM categorias WHERE nombre='Hierros y mallas' LIMIT 1), 'Hierro del 8 (12m)', 'Barra de acero ADN420 8mm.', 18990, 5, NULL, 1, 'barra'),
((SELECT id FROM categorias WHERE nombre='Hierros y mallas' LIMIT 1), 'Malla electrosoldada', 'Malla electrosoldada estándar.', 55990, 0, NULL, 1, 'plancha'),
((SELECT id FROM categorias WHERE nombre='Ladrillos y bloques' LIMIT 1), 'Ladrillo hueco 12x18x33', 'Ladrillo cerámico hueco.', 950, 0, NULL, 1, 'unidad'),
((SELECT id FROM categorias WHERE nombre='Ladrillos y bloques' LIMIT 1), 'Bloque 20x20x40', 'Para cerramientos y muros.', 1200, 0, NULL, 1, 'unidad'),
((SELECT id FROM categorias WHERE nombre='Pinturas y revestimientos' LIMIT 1), 'Látex interior 20L', 'Pintura acrílica interior.', 79990, 15, NULL, 1, 'balde'),
((SELECT id FROM categorias WHERE nombre='Pinturas y revestimientos' LIMIT 1), 'Impermeabilizante 20L', 'Membrana líquida.', 99990, 10, NULL, 1, 'balde'),
((SELECT id FROM categorias WHERE nombre='Herramientas y accesorios' LIMIT 1), 'Cinta métrica 5m', 'Traba automática, carcasa ABS.', 4990, 0, NULL, 1, 'unidad'),
((SELECT id FROM categorias WHERE nombre='Herramientas y accesorios' LIMIT 1), 'Amoladora 4 1/2"', 'Incluye disco de corte.', 79990, 5, NULL, 1, 'unidad');

-- ===== Ajustes para login por `usuario` (estilo Magic Pizzería) =====
-- Agregar columna `usuario` si no existe (ignorar errores si ya existe)
ALTER TABLE usuarios ADD COLUMN usuario VARCHAR(100) UNIQUE AFTER nombre;

-- Completar `usuario` si está vacío usando la parte local del email (opcional)
UPDATE usuarios SET usuario = COALESCE(NULLIF(usuario,''), SUBSTRING_INDEX(email,'@',1));

-- Crear admin Adrian con contraseña 1234 (texto plano para migración automática a hash en el primer login)
INSERT INTO usuarios (nombre, usuario, email, password, rol, activo)
VALUES ('Administrador', 'Adrian', 'adrian@horizonte.com', '1234', 'admin', 1);
