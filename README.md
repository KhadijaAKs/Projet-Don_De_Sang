# Mini-projet : Gestion de Don de Sang (PHP natif + PDO + Bootstrap 5)

## Installation (Ubuntu 24.04, voir TP1 et TP5)
1. `sudo apt install php php-mysql mariadb-server`
2. Importer la base : `sudo mysql < database.sql`
3. Adapter les identifiants dans `config/connexion.php`
   (si besoin : `CREATE USER 'don'@'localhost' IDENTIFIED BY 'motdepasse'; GRANT ALL ON don_sang.* TO 'don'@'localhost';`)
4. Lancer le serveur intégré : `php -S localhost:8000` depuis le dossier du projet
5. Ouvrir `http://localhost:8000/setup.php` UNE fois (crée les 3 comptes), puis supprimer `setup.php`
6. Se connecter sur `http://localhost:8000/login.php`

## Comptes de test
| Rôle | Utilisateur | Mot de passe |
|---|---|---|
| ADMIN | admin | Admin@2026 |
| MEDECIN | medecin | Medecin@2026 |
| SECRETAIRE | secretaire | Secret@2026 |

## Scénario de test
1. secretaire : ajouter un donneur, enregistrer un don (statut EN STOCK)
2. medecin : saisir les tests -> don VALIDE (ou REJETÉ si un test est positif)
3. admin : Transfusions -> utiliser le don -> UTILISÉ
4. Tableau de bord : stock et alertes mis à jour

## Si vous aviez déjà importé la première version
Exécuter `sudo mysql < migration_tp6.sql` (ajoute `actif`, `created_at`, `updated_at`), puis mettre à jour les fichiers.

## Authentification et gestion des utilisateurs (TP6)
- `login.php` : requête préparée, `password_verify`, compte **actif** exigé, message d'erreur générique.
- `inscription.php` : crée un compte SECRETAIRE **inactif** (l'administrateur l'active).
- `logout.php` : session + cookie de session détruits.
- `admin.php`, `utilisateurs.php`, `utilisateur-creer.php`, `utilisateur-role.php`, `utilisateur-statut.php`, `utilisateur-supprimer.php` : réservés à l'ADMIN (`protegerAdmin()`), mot de passe 8 caractères min., garde-fous (pas d'auto-suppression ni d'auto-désactivation, toujours un ADMIN actif).
- `protegerPage()` revérifie le compte en base à chaque requête : un compte désactivé est déconnecté immédiatement.
