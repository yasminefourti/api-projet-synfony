

## 📝 Étapes principales

### ✅ Étape 1 : Je peux m'inscrire  
### ✅ Étape 2 : Je peux me connecter  

D'abord, configurons l'authentification dans votre application Symfony :  
**Fichier concerné** : `security.yaml`  

Ensuite, nous créerons un contrôleur pour gérer la connexion :  
**Fichier concerné** : `SecurityController.php`  

Enfin, je vous montrerai comment tester cela avec **Postman**.

---

## 🔍 Test de connexion avec Postman

### 🔧 Configuration de la requête dans Postman

- **URL** : `http://localhost:8000/api/login`  
- **Méthode** : `POST`

#### 📨 Headers :
- `Content-Type: application/json`  
- `Accept: application/json`

#### 🧾 Body (raw - JSON) :
```json
{
  "email": "votre_email@exemple.com",
  "password": "votre_mot_de_passe"
}


### Exemple de réponse réussie
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ......"
}


## Conseil de débogage :
# Problèmes courants et solutions

## 1. Vérifier l'installation du bundle JWT
```bash
# Vérifier que le bundle est bien installé
composer require lexik/jwt-authentication-bundle
```

## 2. Vérifier les routes
```bash
# Liste toutes les routes de l'application
php bin/console debug:router
```

## 3. Vérifier la configuration de sécurité
```bash
# Affiche la configuration de sécurité actuelle
php bin/console debug:config security
```

## 4. Vérifier les firewalls actifs
```bash
# Affiche les informations de sécurité
php bin/console debug:security
```

## 5. Activer les logs détaillés
# Dans config/packages/dev/monolog.yaml, ajoutez:
```yaml
monolog:
    handlers:
        security:
            type: stream
            path: "%kernel.logs_dir%/security.log"
            level: debug
            channels: [security]
```

## 6. Erreurs courantes:
- "JWT Token not found": Vérifiez que vous envoyez bien le header Authorization
- "Invalid credentials": Vérifiez email/mot de passe
- "Invalid JWT Token": Le token est expiré ou mal formé

## 7. Vérifier l'état de l'utilisateur en base de données
```sql
-- Vérifiez que l'utilisateur existe avec ces informations
SELECT * FROM user WHERE email = 'votre_email@exemple.com';

Etape 3:J'affiche mes infos(afficher les informations de l'utilisateur connecté)
modifier userController.php
Test de l'API de profil avec Postman
Requête pour obtenir le profil utilisateur
- Méthode: GET
- URL: http://127.0.0.1:8000/api/user/profile
- Headers:
  - Authorization: Bearer eyJ0eXAiOiJKV1..........
Etape 4: Je peux modifier mes infos