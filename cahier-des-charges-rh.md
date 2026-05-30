# Cahier des Charges — Back-Office RH Laravel
> Gestion des employés, congés & documents PDF

---

## 1. Présentation du projet

**Nom du projet :** HRBoard  
**Type :** Outil interne back-office  
**Stack :** Laravel (back + vues Blade), MySQL, génération PDF  
**Objectif :** Permettre à une équipe RH de gérer les employés, leurs contrats, leurs congés, et de générer des documents officiels en PDF.

---

## 2. Rôles & Permissions

| Rôle | Description |
|---|---|
| **Super Admin** | Accès total, gestion des utilisateurs et des rôles |
| **RH (Manager)** | Gère les employés, valide les congés, génère les PDF |
| **Employé** | Consulte son profil, pose des demandes de congé |

---

## 3. Modules fonctionnels

---

### 3.1 Authentification

- Connexion par email + mot de passe
- Déconnexion
- Réinitialisation de mot de passe par email
- Chaque utilisateur est rattaché à un rôle (Super Admin / RH / Employé)
- Les pages sont protégées par rôle (middleware)

---

### 3.2 Gestion des Employés

**Fiche employé — Informations à saisir :**

- Nom, prénom
- Email professionnel
- Téléphone
- Date de naissance
- Date d'embauche
- Poste / intitulé du poste
- Département / service
- Type de contrat (CDI, CDD, Stage, Alternance, Freelance)
- Salaire brut mensuel
- Statut (Actif / Inactif / En préavis)
- Photo de profil (upload)

**Actions disponibles :**

- Créer un employé
- Modifier un employé
- Archiver un employé (soft delete — l'employé n'est pas supprimé définitivement)
- Lister tous les employés avec filtres (département, statut, type de contrat)
- Recherche par nom / prénom / email
- Voir le profil complet d'un employé

---

### 3.3 Gestion des Congés

**Types de congés :**

- Congés payés
- RTT
- Congé maladie
- Congé sans solde
- Congé exceptionnel (mariage, naissance, décès...)

**Cycle d'une demande :**

```
Employé soumet → En attente → RH valide ou refuse → Employé notifié
```

**Informations d'une demande :**

- Employé concerné
- Type de congé
- Date de début
- Date de fin
- Nombre de jours calculé automatiquement (hors week-ends)
- Motif (optionnel)
- Statut : En attente / Approuvé / Refusé
- Commentaire RH (en cas de refus)

**Règles métier :**

- Un employé ne peut pas avoir deux demandes approuvées sur les mêmes dates
- Le solde de congés payés est calculé (ex: 2,5 jours/mois)
- Alerte si le solde est insuffisant

**Vues :**

- Calendrier des congés (vue mensuelle) — visible par les RH
- Liste des demandes en attente pour les RH
- Historique des congés par employé

---

### 3.4 Gestion des Départements

- CRUD complet : créer, modifier, supprimer un département
- Nom du département
- Responsable du département (lié à un employé)
- Nombre d'employés affiché automatiquement

---

### 3.5 Tableau de bord (Dashboard)

**Visible par les RH / Super Admin :**

- Nombre total d'employés actifs
- Nombre de demandes de congé en attente
- Employés en congé aujourd'hui
- Répartition par département (graphique)
- Prochaines dates d'anniversaire d'embauche (dans les 30 jours)

**Visible par l'employé :**

- Son solde de congés restants
- Ses demandes en cours
- Ses prochains congés approuvés

---

### 3.6 Génération de PDF

Les PDF sont générés à la demande depuis l'interface. Ils utilisent un template propre avec logo, en-tête et pied de page.

#### 📄 Fiche employé
Récapitulatif complet de l'employé : identité, poste, contrat, département.

#### 📄 Attestation de travail
Document officiel certifiant qu'un employé travaille dans l'entreprise.  
Contenu : nom, prénom, poste, date d'embauche, type de contrat, mention légale.

#### 📄 Bulletin de congé
Récapitulatif d'une demande de congé approuvée : employé, dates, type, durée, validation RH.

#### 📄 Liste des employés par département
Export PDF tabulaire de tous les employés d'un département avec leurs informations clés.

#### 📄 Rapport mensuel des congés
Tableau récapitulatif du mois : qui a posé, quel type, combien de jours. Signé RH.

---

### 3.7 Notifications (Internes)

- Notification à l'employé quand sa demande est approuvée ou refusée
- Notification au RH quand une nouvelle demande est soumise
- Badge sur l'icône notification dans le header
- Les notifications sont marquées comme "lues"

---

## 4. Structure des pages

```
/login                          Connexion
/dashboard                      Tableau de bord

/employees                      Liste des employés
/employees/create               Créer un employé
/employees/{id}                 Voir le profil
/employees/{id}/edit            Modifier
/employees/{id}/pdf             Télécharger la fiche PDF

/leaves                         Liste toutes les demandes (RH)
/leaves/create                  Soumettre une demande (Employé)
/leaves/{id}                    Détail d'une demande
/leaves/{id}/approve            Approuver (RH)
/leaves/{id}/reject             Refuser (RH)

/departments                    Liste des départements
/departments/create             Créer un département
/departments/{id}/edit          Modifier

/reports/monthly-pdf            Rapport mensuel PDF
/reports/department/{id}/pdf    Liste PDF par département

/profile                        Mon profil (Employé)
/notifications                  Mes notifications
```

---

## 5. Modèle de données (entités)

### `users`
| Champ | Type |
|---|---|
| id | bigint PK |
| name | string |
| email | string unique |
| password | string |
| role | enum: super_admin, hr, employee |
| created_at / updated_at | timestamps |

### `employees`
| Champ | Type |
|---|---|
| id | bigint PK |
| user_id | FK → users |
| first_name | string |
| last_name | string |
| phone | string nullable |
| birthdate | date nullable |
| hire_date | date |
| job_title | string |
| department_id | FK → departments |
| contract_type | enum: cdi, cdd, stage, alternance, freelance |
| gross_salary | decimal nullable |
| status | enum: active, inactive, notice |
| photo | string nullable |
| deleted_at | timestamp (soft delete) |
| created_at / updated_at | timestamps |

### `departments`
| Champ | Type |
|---|---|
| id | bigint PK |
| name | string |
| manager_id | FK → employees nullable |
| created_at / updated_at | timestamps |

### `leaves`
| Champ | Type |
|---|---|
| id | bigint PK |
| employee_id | FK → employees |
| type | enum: paid, rtt, sick, unpaid, exceptional |
| start_date | date |
| end_date | date |
| days_count | integer (calculé) |
| reason | text nullable |
| status | enum: pending, approved, rejected |
| hr_comment | text nullable |
| approved_by | FK → users nullable |
| created_at / updated_at | timestamps |

### `notifications`
| Champ | Type |
|---|---|
| id | bigint PK |
| user_id | FK → users |
| message | string |
| type | string (leave_approved, leave_rejected, ...) |
| read_at | timestamp nullable |
| created_at / updated_at | timestamps |

---

## 6. Règles techniques

- Framework : **Laravel 11+**
- Vues : **Blade** (ou Livewire pour les parties interactives)
- Génération PDF : **Laravel-DomPDF** (`barryvdh/laravel-dompdf`)
- Authentification : **Laravel Breeze** ou **Jetstream**
- Base de données : **MySQL 8**
- Fichiers uploadés : stockés dans `storage/app/public`
- Validation : Form Requests Laravel
- Soft Delete sur les employés
- Factories + Seeders pour les données de test

---

## 7. Livrables attendus

- [ ] Migrations complètes
- [ ] Seeders avec données de démo (1 RH, 10 employés, 2 départements, congés variés)
- [ ] CRUD Employés complet
- [ ] CRUD Congés avec workflow de validation
- [ ] CRUD Départements
- [ ] Dashboard avec indicateurs
- [ ] 5 templates PDF propres et téléchargeables
- [ ] Système de notifications internes
- [ ] Gestion des rôles et middleware
- [ ] Interface responsive

---

## 8. Ce qui est hors scope (v1)

- Import/export Excel des employés
- Gestion de la paie
- Signature électronique des PDF
- Application mobile
- API REST externe
- Multi-entreprise (multi-tenant)
