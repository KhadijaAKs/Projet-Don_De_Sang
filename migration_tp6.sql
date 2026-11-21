-- À exécuter UNIQUEMENT si vous avez déjà importé l'ancienne version de database.sql
USE don_sang;
ALTER TABLE utilisateurs
  ADD COLUMN actif TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
