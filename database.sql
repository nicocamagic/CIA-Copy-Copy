-- =============================================
-- CIA - Compañía de Arte y Vida
-- Estructura de Base de Datos MySQL
-- =============================================

-- Crear base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS cia_arte_vida_nueva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cia_arte_vida_nueva;

-- =============================================
-- TABLA: publications (Talleres y Shows)
-- =============================================
CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page ENUM('talleres', 'shows') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80',
    price VARCHAR(100) DEFAULT '',
    duration VARCHAR(100) DEFAULT '',
    date DATE NOT NULL,
    tags JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page (page),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: subscribers (Suscripciones newsletter)
-- =============================================
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: contact_messages (Mensajes de contacto)
-- =============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: gallery_images (Galería de Fotos)
-- =============================================
CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(500) NOT NULL,
    title VARCHAR(255) DEFAULT '',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATOS DE EJEMPLO (Opcional - para pruebas)
-- =============================================
-- INSERT INTO publications (page, title, description, image, price, duration, date, tags) VALUES
-- ('talleres', 'Taller de Teatro Básico', 'Aprende los fundamentos del teatro con profesionales.', 'https://images.unsplash.com/photo-1547153760-18fc86324498?w=600&q=80', '$1500', '2 hs', '2026-02-15', '["teatro", "principiantes"]'),
-- ('shows', 'Noche de Flamenco', 'Una noche mágica con los mejores artistas de flamenco.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=600&q=80', '$2500', '3 hs', '2026-03-01', '["danza", "música"]');
