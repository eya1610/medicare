## 📋 Description

**MediCare** est une application web complète de gestion des rendez-vous médicaux. Elle permet aux administrateurs, médecins et patients de gérer efficacement les consultations, les avis et les réclamations dans un environnement sécurisé et intuitif.

L'application a été développée avec **PHP 8.2**, **MySQL** et **PDO** pour une sécurité optimale contre les injections SQL.

---

## ✨ Fonctionnalités

### 👑 Administrateur
- **Tableau de bord** : Vue d'ensemble de l'activité avec indicateurs KPI
- **Gestion des patients** : Ajouter, modifier, supprimer et rechercher
- **Gestion des médecins** : Ajouter, modifier, supprimer et rechercher
- **Gestion des rendez-vous** : CRUD complet avec filtres avancés
- **Statistiques** : Graphiques et indicateurs de performance
- **Calendrier** : Vue mensuelle interactive
- **Avis** : Modération des avis patients
- **Réclamations** : Traitement et suivi des réclamations
- **Export PDF** : Génération de rapports

### 👨‍⚕️ Médecin
- **Tableau de bord** : Vue personnalisée
- **Agenda** : Calendrier interactif
- **Gestion des rendez-vous** : Consultation et modification
- **Fiche patient** : Historique et notes médicales
- **Export PDF** : Liste des rendez-vous

### 🧑 Patient
- **Tableau de bord** : Vue personnalisée
- **Prendre un rendez-vous** : Sélection du médecin et créneau
- **Mes rendez-vous** : Consultation et annulation
- **Avis** : Donner et gérer ses avis
- **Réclamations** : Soumettre et suivre

---

## 🛠️ Technologies utilisées

| Technologie | Version | Utilisation |
|-------------|---------|-------------|
| **PHP** | 8.2 | Backend, logique métier |
| **MySQL** | 8.0 | Base de données |
| **PDO** | - | Connexion sécurisée à la BD |
| **HTML5** | - | Structure des pages |
| **CSS3** | - | Style et design responsive |
| **JavaScript** | ES6 | Interactivité et AJAX |
| **Chart.js** | 4.4 | Graphiques statistiques |
| **Dompdf** | 2.0 | Export PDF |

---

## 🗂️ Structure du projet
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
