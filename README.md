# rest_mediatekdocuments — Atelier 2

> Le dépôt d'origine se trouve ici : https://github.com/CNED-SLAM/rest_mediatekdocuments  
> Il contient dans son readme la présentation complète de l'API d'origine.

## Présentation

Ce dépôt contient l'API REST PHP **rest_mediatekdocuments** enrichie dans le cadre de l'Atelier 2 du BTS SIO SLAM (CNED 2026). L'API est consommée par l'application C# MediaTekDocuments.

## API en ligne
**http://azizmediatek.atwebpages.com/**

## Nouvelles routes ajoutées

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | /document | Ajouter un document |
| PUT | /document/{id} | Modifier un document |
| DELETE | /document/{id} | Supprimer un document |
| GET | /exemplairellivredvd/{id} | Récupérer les exemplaires |
| PUT | /exemplairellivredvd/{id} | Modifier l'état d'un exemplaire |
| DELETE | /exemplairellivredvd/{id} | Supprimer un exemplaire |
| GET | /abonnement | Récupérer les abonnements |
| POST | /abonnement | Ajouter un abonnement |
| DELETE | /abonnement/{id} | Supprimer un abonnement |
| GET | /suivi | Récupérer les étapes de suivi |
| GET | /commandedocument/{id} | Récupérer les commandes |
| POST | /commandedocument | Ajouter une commande |
| PUT | /commandedocument/{id} | Modifier l'état d'une commande |
| DELETE | /commandedocument/{id} | Supprimer une commande |

## Mode opératoire — Installation en local

### Prérequis
- MAMP (Apache + MySQL)
- PHP 8.x
- Composer

### Installation
1. Cloner ce dépôt dans `/Applications/MAMP/htdocs/rest_mediatekdocuments/`
2. Installer les dépendances :
```bash
composer install
```
3. Copier `.env.example` en `.env` et renseigner les informations de connexion :
```
AUTHENTIFICATION=basic
AUTH_USER=admin
AUTH_PWD=adminpwd
BDD_LOGIN=root
BDD_PWD=root
BDD_BD=mediatek86
BDD_SERVER=localhost
BDD_PORT=8889
```
4. Importer le script SQL dans phpMyAdmin :
   - Ouvrir http://localhost:8888/phpMyAdmin
   - Créer la base `mediatek86`
   - Importer `mediatek86.sql` (disponible dans le dépôt MediaTekDocuments)

### Test avec Postman
Importer la collection Postman disponible dans le dépôt C# et configurer l'authentification Basic Auth :
- Username : `admin`
- Password : `adminpwd`

<img width="1035" height="612" alt="Screenshot 2026-04-02 at 16 53 55" src="https://github.com/user-attachments/assets/d7aba630-8705-4dad-ba85-c53d75f1f165" />


## Documentation technique
Les commentaires PHPDoc sont présents dans `src/MyAccessBDD.php`

## Dépôt de l'application C#
https://github.com/Simba-TRZ/MediaTekDocuments
