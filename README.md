# 🚀 Guide d'utilisation de l'API d'authentification

Ce document explique comment utiliser l'API d'authentification de notre application, avec des exemples de requêtes utilisant Postman.

## 📋 Table des matières

- Je peux m'inscrire
- Je peux me connecter
- J'affiche mes infos (afficher les informations de l'utilisateur connecté))
- Je peux modifier mes infos
- En tant qu’admin j’ai la liste de tous les utilisateurs
- Je peux me déconnecter

## 📝 Inscription

### Endpoint

```
POST http://localhost:8000/api/register
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

## 🔑 Connexion

### Endpoint

```
POST http://localhost:8000/api/login
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

## 👤 Consulter son profil

Cette fonctionnalité permet à l'utilisateur connecté de consulter ses propres informations.

### Endpoint

```
GET http://localhost:8000/api/user/profile
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

## ✏️ Modifier ses informations

Cette fonctionnalité permet à l'utilisateur connecté de modifier ses informations personnelles.
modifier userController.php
Test de l'API de profil avec Postman

### Endpoint

```
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
    "lastname": "Dupont",
    "firstname": "Pierre",
    "email": "pierre.dupont@example.com"
}
```

### Réponse attendue

L'API renvoie un statut `200 OK` avec les informations mises à jour de l'utilisateur.
