-- Base de datos: licitaciones_db
-- Prueba Técnica Desarrollador FullStack Junior (Suplos)

CREATE DATABASE IF NOT EXISTS licitaciones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE licitaciones_db;

-- 1. Tabla: actividades (Catálogo de bienes y servicios UNSPSC)
DROP TABLE IF EXISTS ofertas_documentos;
DROP TABLE IF EXISTS ofertas;
DROP TABLE IF EXISTS actividades;

CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_segmento INT NOT NULL,
    Segmento VARCHAR(200) NOT NULL,
    codigo_familia INT NOT NULL,
    familia VARCHAR(200) NOT NULL,
    codigo_clase INT NOT NULL,
    clase VARCHAR(200) NOT NULL,
    codigo_producto INT NOT NULL,
    producto VARCHAR(200) NOT NULL,
    INDEX idx_producto (producto(150)),
    INDEX idx_segmento (Segmento(100)),
    INDEX idx_codigo_producto (codigo_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla: ofertas
CREATE TABLE ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consecutivo VARCHAR(50) NOT NULL UNIQUE,
    objeto VARCHAR(150) NOT NULL,
    descripcion VARCHAR(400) NOT NULL,
    moneda VARCHAR(3) NOT NULL, -- 'COP', 'USD', 'EUR'
    presupuesto DECIMAL(15,2) NOT NULL,
    actividad_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    fecha_cierre DATE NOT NULL,
    hora_cierre TIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Publicada',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ofertas_objeto (objeto(100)),
    INDEX idx_ofertas_fechas (fecha_inicio, fecha_cierre),
    CONSTRAINT fk_ofertas_actividad FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla: ofertas_documentos
CREATE TABLE ofertas_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    licitacion_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documentos_oferta FOREIGN KEY (licitacion_id) REFERENCES ofertas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
