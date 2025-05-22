# 🚀 Guide d'utilisation de l'API d'authentification

Ce document explique comment utiliser l'API d'authentification de notre application, avec des exemples de requêtes utilisant Postman.

## 📋 Table des matières

1. [📝 Je peux m'inscrire](#1--inscription)  
2. [🔑 Je peux me connecter](#2--connexion)  
3. [👤 J'affiche mes informations](#3--consulter-son-profil)  
4. [✏️ Je peux modifier mes informations](#4--modifier-son-profil)  
5. [🔍 En tant qu’admin, j’ai la liste de tous les utilisateurs](#5--liste-des-utilisateurs-admin)  
6. [🚪 Je peux me déconnecter](#6--déconnexion)

## 1.📝 Inscription

### Endpoint

```
Méthode : POST
http://127.0.0.1:8000/api/register
```

### En-têtes (Headers)

```
Content-Type: application/json
```

### Corps de la requête (Body)

```json
{
    "lastname": "Dupont",
    "firstname": "Jean",
    "email": "jean.dupont@example.com",
    "password": "motdepasse123",
    "role": ["ROLE_USER"]
}
```

### Réponse attendue

En cas de succès, l'API renvoie un statut `201 Created` avec les informations de l'utilisateur créé (sans le mot de passe).

## 2. 🔑 Connexion

### Endpoint

```
Méthode : POST
http://127.0.0.1:8000/api/login
```

### En-têtes (Headers)

```
Content-Type: application/json
Accept: application/json
```

### Corps de la requête (Body)

```json
{
    "email": "jean.dupont@example.com",
    "password": "motdepasse123"
}
```

### Réponse attendue

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ......"
}
```

> ⚠️ **Important** : Conservez ce token, vous en aurez besoin pour toutes les requêtes authentifiées.

## 👤 3.Consulter son profil à l'utilisateur connecté

Cette fonctionnalité permet à l'utilisateur connecté de consulter ses propres informations.

Methode getCurrentUserProfile()=Retourner les informations de l'utilisateur actuellement connecté.
getUser() =récupère l'utilisateur connecté.

### Endpoint

```
Méthode : GET
 http://localhost:8000/api/user/profile
```

### En-têtes (Headers)

```
Authorization: Bearer [votre_token_jwt]
```

### Réponse attendue

```json
{
    "id": 1,
    "lastname": "Dupont",
    "firstname": "Jean",
    "email": "jean.dupont@example.com",
    "roles": ["ROLE_USER"]
}
```

## 4. ✏️ Modifier ses informations

Cette fonctionnalité permet à l'utilisateur connecté de modifier ses informations personnelles.
1. modifier userController.php
 -J'ai ajouté une nouvelle méthode updateUserProfile qui :Utilise la même route /api/user/profile mais avec les méthodes HTTP PUT et PATCH
 -Valide les données avant de les enregistrer
-Retourne les informations mises à jour
2. Test de l'API de profil avec Postman
RQ:
GET /api/user/profile    (pour afficher le profil)
PUT /api/user/profile    (pour mettre à jour le profil)

### Endpoint

```
Méthode : PUT
http://127.0.0.1:8000/api/user/profile
```

### En-têtes (Headers)

```
Content-Type: application/json
Authorization: Bearer [votre_token_jwt]
```

### Corps de la requête (Body)

```json
{
    "firstname": "Nouveau Prénom",
    "lastname": "Nouveau Nom",
    "email": "nouveau.email@exemple.com"
}
```

### Réponse attendue
```json

{
    "message": "Profil mis à jour avec succès",
    "user": {
        "id": 1,
        "firstname": "Nouveau Prénom",
        "lastname": "Nouveau Nom",
        "email": "nouveau.email@exemple.com",
        "role": ["ROLE_USER"]
    }
}
```
## 5. 🔍 En tant qu’admin j’ai la liste de tous les utilisateurs
Restriction d’accès :

- L’attribut #[IsGranted('ROLE_ADMIN')] garantit que seuls les utilisateurs ayant le rôle ROLE_ADMIN peuvent appeler cette route.

- Si un utilisateur non administrateur tente d'y accéder, une erreur 403 Access Denied sera renvoyée automatiquement.

- Récupération des utilisateurs :

Le contrôleur utilise le UserRepository pour appeler la méthode findAll() qui retourne tous les utilisateurs enregistrés dans la base de données.


### 📬 Endpoint
    Méthode : GET
    http://localhost:8000/api/users

### Corps de la requête (Body) qui a le role admin
  
    "email": "mm@example.com",
    "password": "1234"

### Réponse attendue
```json
{
    "users": [
        {
            "id": 1,
            "firstname": "Yasmine",
            "lastname": "Fourti",
            "email": "yasmine@example.com",
            "roles": [
                "ROLE_USER"
            ]
        },
        {
            "id": 2,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "roles": [
                "ROLE_USER"
            ]
        },......
 ```
- La liste des utilisateurs (/api/users - GET)
- L'affichage du profil utilisateur connecté (/api/user/profile - GET)
- La mise à jour du profil utilisateur (/api/user/profile - PUT/PATCH)
- L'affichage d'un utilisateur spécifique (/api/users/{id} - GET)
 ## 6. La modification des rôles/statut d'un utilisateur par un admin (admin seulement)
### 📬 Endpoint
Méthode: PUT
URL: http://localhost:8000/api/admin/users/2 (où 2 est l'ID de l'utilisateur)
Headers:
Content-Type: application/json
Authorization: Bearer [votre_token_jwt]
### Body (raw JSON):
```json
json{
  "roles": ["ROLE_USER", "ROLE_ADMIN"],
  "isActive": true
}
```
### Résultat attendu:
```json
json{
  "message": "Utilisateur mis à jour avec succès",
  "user": {
    "id": 2,
    "firstname": "Jane",
    "lastname": "Smith",
    "email": "jane@example.com",
    "roles": ["ROLE_USER", "ROLE_ADMIN"]
  }
}
```
 
## 7. supprimer un utilisateur (admin seulement)
### 7.1 Suppression logique (soft delete)
### 📬 Endpoint
Méthode: DELETE
URL: http://localhost:8000/api/admin/users/2 (où 2 est l'ID de l'utilisateur)
Headers:
Authorization: Bearer [votre_token_jwt]
### Résultat attendu:
```json
json{
  "message": "Utilisateur supprimé avec succès"
}
```
### 7.2 Suppression définitive (hard delete)
### 📬 Endpoint
Méthode: DELETE
URL: http://localhost:8000/api/admin/users/2?type=hard (où 2 est l'ID de l'utilisateur)
Headers:
Authorization: Bearer [votre_token_jwt]
### Résultat attendu:
```json
json{
  "message": "Utilisateur supprimé avec succès"
}
```

## 8.🚪 Je peux me déconnecter 
### 📬 Endpoint
    Méthode : POST
    http://localhost:8000/api/logout


### Réponse attendue
```json
    {
        "message": "Déconnexion réussie"
    }
```
## 7.🚪 entité  objectif
### creer un objectif
Méthode : POST
URL : http://localhost:8000/api/budget/goals
Body (JSON) :
```json
json{
  "title": "Épargne pour vacances",
  "targetAmount": 2000,
  "startDate": "2025-05-15T00:00:00+00:00",
  "endDate": "2025-12-31T00:00:00+00:00"
}
```
 ### Listez vos objectifs

Méthode : GET
URL : http://localhost:8000/api/budget/goals
Headers :

Authorization: Bearer votre_token_jwt
 ### Supprimez votre objectif

Méthode : DELETE
URL : http://localhost:8000/api/budget/goals/{id} (remplacez {id} par l'ID de votre objectif)
Headers :

Authorization: Bearer votre_token_jwt

 ### Met à jour un objectif existant
methode:PUT
http://localhost:8000/api/budget/goals/2

```json
{
  "title": "Nouvel objectif mis à jour",
  "targetAmount": 200000,
  "currentAmount": 1000,
  "startDate": "2025-05-15T00:00:00+00:00",
  "endDate": "2025-12-31T00:00:00+00:00"
}
```
## 8.  entité  Transaction
### Lister les transactions d’un objectif
Méthode : GET
URL : http://localhost:8000/api/budget/goals/{goalId}/transactions
(remplace {goalId} par l’ID d’un objectif existant)

Headers :

Authorization : Bearer <token>
### Créer une transaction pour un objectif
Méthode : POST
URL : http://localhost:8000/api/budget/goals/{goalId}/transactions

Headers :
Content-Type : application/json
Authorization : Bearer <token>
Body JSON
```json 
{
  "type": "recette",
  "amount": 150.00,
  "date": "2025-05-22",
  "description": "Salaire"
}
```
### Voir le détail d’une transaction
Méthode : GET
URL : http://localhost:8000/api/budget/transactions/{Id}
ID d’une transaction existante
### Modifier une transaction
Méthode : PUT

URL : http://localhost:8000/api/budget/transactions/{Id}

Body JSON
```json 
{
  "type": "dépense",
  "amount": 50.00,
  "date": "2025-05-23",
  "description": "Courses"
}
```
### Supprimer une transaction
Méthode : DELETE

URL : http://localhost:8000/api/budget/transactions/{Id}


## 9. entité  Categorie
### Création d’une catégorie
Méthode : POST
URL : http://localhost:8000/api/budget/categories
Headers :
Content-Type: application/json
Authorization: (token Bearer ou autre)
Body : raw JSON, exemple :
```json
{
  "nom": "Loisirs",
  "description": "Catégorie pour les dépenses loisirs"
}
```
### Récupèrer la liste des catégories disponibles
Méthode : GET
URL : http://localhost:8000/api/budget/categories
Headers :
Authorization: ajoute un token Bearer 
### Mettre à jour une catégorie
Méthode : PUT
URL : http://localhost:8000/api/budget/categories/1 

Headers :

Content-Type: application/json
Authorization
### Supprime une catégorie
Méthode : DELETE
URL : http://localhost:8000/api/budget/categories/1
Headers :
Authorization