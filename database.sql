-- Base de données : don de sang (TP5 - Activité 2)
CREATE DATABASE IF NOT EXISTS don_sang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE don_sang;

CREATE TABLE donneurs (
  id_donneur INT AUTO_INCREMENT PRIMARY KEY,
  cin CHAR(8) NOT NULL UNIQUE,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  date_naissance DATE NOT NULL,
  sexe ENUM('M','F') NOT NULL,
  groupe_sanguin ENUM('A','B','AB','O') NOT NULL,
  rhesus ENUM('+','-') NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  date_inscription DATE NOT NULL DEFAULT (CURRENT_DATE)
);

CREATE TABLE centres_collecte (
  id_centre INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  adresse VARCHAR(255) NOT NULL,
  telephone VARCHAR(20)
);

CREATE TABLE utilisateurs (
  id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
  nom_complet VARCHAR(150) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,           -- haché avec password_hash()
  role ENUM('ADMIN','MEDECIN','SECRETAIRE') NOT NULL,
  id_centre INT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,           -- 1 = compte actif, 0 = désactivé (TP6)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre) ON DELETE SET NULL
);

CREATE TABLE dons (
  id_don INT AUTO_INCREMENT PRIMARY KEY,
  id_donneur INT NOT NULL,
  id_centre INT NOT NULL,
  date_don DATE NOT NULL,
  quantite_ml INT NOT NULL DEFAULT 450,
  statut ENUM('EN STOCK','VALIDE','REJETÉ','UTILISÉ') NOT NULL DEFAULT 'EN STOCK',
  FOREIGN KEY (id_donneur) REFERENCES donneurs(id_donneur),
  FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre)
);

CREATE TABLE tests_don (
  id_test INT AUTO_INCREMENT PRIMARY KEY,
  id_don INT NOT NULL UNIQUE,
  id_utilisateur INT NULL,
  date_test DATE NOT NULL,
  vih ENUM('NEGATIF','POSITIF') NOT NULL,
  hepatite_b ENUM('NEGATIF','POSITIF') NOT NULL,
  hepatite_c ENUM('NEGATIF','POSITIF') NOT NULL,
  est_conforme TINYINT(1) NOT NULL,
  remarques VARCHAR(255) NULL,
  FOREIGN KEY (id_don) REFERENCES dons(id_don),
  FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE SET NULL
);

CREATE TABLE transfusions (
  id_transfusion INT AUTO_INCREMENT PRIMARY KEY,
  id_don INT NOT NULL UNIQUE,
  hopital_recepteur VARCHAR(150) NOT NULL,
  date_transfusion DATE NOT NULL,
  FOREIGN KEY (id_don) REFERENCES dons(id_don)
);

CREATE TABLE besoins (
  id_besoin INT AUTO_INCREMENT PRIMARY KEY,
  groupe_sanguin VARCHAR(3) NOT NULL UNIQUE,     -- ex : 'O-', 'AB+'
  niveau_alerte ENUM('URGENT','CRITIQUE','NORMAL') NOT NULL DEFAULT 'NORMAL'
);

-- Données de test
INSERT INTO centres_collecte (nom, ville, adresse, telephone) VALUES
('Centre Régional de Transfusion Sanguine', 'Sousse', 'Avenue Hédi Chaker', '73000000'),
('Centre de Collecte Monastir', 'Monastir', 'Rue de la Santé', '73111111');

INSERT INTO besoins (groupe_sanguin, niveau_alerte) VALUES
('A+','NORMAL'),('A-','URGENT'),('B+','NORMAL'),('B-','CRITIQUE'),
('AB+','NORMAL'),('AB-','NORMAL'),('O+','NORMAL'),('O-','CRITIQUE');

INSERT INTO donneurs (cin, nom, prenom, date_naissance, sexe, groupe_sanguin, rhesus, telephone, ville) VALUES
('09876543','Ben Salah','Ali','1995-04-12','M','O','-','98123456','Sousse'),
('08765432','Trabelsi','Sarra','1992-09-30','F','A','+','97234567','Monastir'),
('07654321','Khediri','Ahmed','1998-01-20','M','B','+','96345678','Sousse'),
('06543210','Gharbi','Nour','2000-06-05','F','AB','-','95456789','Mahdia');

INSERT INTO dons (id_donneur, id_centre, date_don, quantite_ml, statut) VALUES
(1,1,'2026-09-10',450,'VALIDE'),
(2,1,'2026-09-12',450,'VALIDE'),
(3,2,'2026-11-18',450,'EN STOCK'),
(4,1,'2026-11-19',450,'EN STOCK');

INSERT INTO tests_don (id_don, id_utilisateur, date_test, vih, hepatite_b, hepatite_c, est_conforme) VALUES
(1,NULL,'2026-09-11','NEGATIF','NEGATIF','NEGATIF',1),
(2,NULL,'2026-09-13','NEGATIF','NEGATIF','NEGATIF',1);
-- Les utilisateurs sont créés par setup.php (mots de passe hachés)
