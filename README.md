PS C:\xampp\htdocs\clinic_rdv> git remote -v
>>
origin  https://github.com/eya1610/clinic_rdv.git (fetch)
origin  https://github.com/eya1610/clinic_rdv.git (push)
PS C:\xampp\htdocs\clinic_rdv> git push -u origin main
remote: Repository not found.
fatal: repository 'https://github.com/eya1610/clinic_rdv.git/' not found
PS C:\xampp\htdocs\clinic_rdv> git remote set-url origin https://github.com/eya1610/medicare.git
PS C:\xampp\htdocs\clinic_rdv> git push -u origin main
fatal: unable to connect to cache daemon: Unknown error
Enumerating objects: 84, done.
Counting objects: 100% (84/84), done.
Delta compression using up to 8 threads
Compressing objects: 100% (81/81), done.
Writing objects: 100% (84/84), 68.18 KiB | 1.34 MiB/s, done.
Total 84 (delta 36), reused 0 (delta 0), pack-reused 0 (from 0)
remote: Resolving deltas: 100% (36/36), done.
To https://github.com/eya1610/medicare.git
 * [new branch]      main -> main
branch 'main' set up to track 'origin/main'.
PS C:\xampp\htdocs\clinic_rdv>
medicare/
├── admin/ # Interface administrateur
│ ├── dashboard.php
│ ├── stats.php
│ ├── calendar.php
│ ├── patients/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ ├── medecins/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ ├── rdv/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ ├── avis/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ ├── reclamations/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ ├── supprimer.php
│ │ └── traiter.php
│ └── export/
│ └── export_pdf.php
├── medecin/ # Interface médecin
│ ├── dashboard.php
│ ├── rdv/
│ │ ├── index.php
│ │ ├── modifier.php
│ │ └── export_pdf.php
│ └── compte/
│ ├── profil.php
│ ├── modifier.php
│ └── supprimer.php
├── patient/ # Interface patient
│ ├── dashboard.php
│ ├── rdv/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── annuler.php
│ ├── avis/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ ├── reclamations/
│ │ ├── index.php
│ │ ├── ajouter.php
│ │ ├── modifier.php
│ │ └── supprimer.php
│ └── compte/
│ ├── profil.php
│ ├── modifier.php
│ └── supprimer.php
├── config/ # Configuration
│ └── database.php
├── includes/ # Fichiers inclus
│ ├── header.php
│ ├── sidebar.php
│ └── footer.php
├── assets/ # Ressources statiques
│ ├── css/
│ │ └── style.css
│ └── js/
│ ├── main.js
│ └── chart.js
├── sql/ # Scripts SQL
│ └── database.sql
├── login.php
├── logout.php
├── test_bd.php
└── README.md

---

## 🚀 Installation

### 1. Prérequis

- **XAMPP**  (Apache + MySQL + PHP 8.2+)
- **Git** (optionnel)

### 2. Cloner le projet

```bash
git clone https://github.com/eya1610/medicare.git

# 1. Ouvrir phpMyAdmin
http://localhost/phpmyadmin

# 2. Créer une base de données
Nom : clinic_rdv
Interclassement : utf8mb4_general_ci

# 3. Importer le fichier SQL
sql/database.sql
4. Configurer la connexion
Modifier config/database.php :

php
private $host = 'localhost';
private $dbname = 'clinic_rdv';
private $username = 'root';
private $password = '';
5. Lancer l'application
bash
# Démarrer Apache et MySQL via XAMPP
# Accéder à l'application
http://localhost/medicare/login.php
