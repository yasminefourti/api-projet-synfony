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
## 6.🚪 Je peux me déconnecter 
### 📬 Endpoint
    Méthode : POST
    http://localhost:8000/api/logout


### Réponse attendue
```json
    {
        "message": "Déconnexion réussie"
    }
```
## 7.🚪 créer un objectif
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