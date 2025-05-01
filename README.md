Etape1: Je peux m'inscrire
Etape 2: Je peux me connecter
D'abord, configurons l'authentification dans votre application Symfony. " security.yaml"
Ensuite, nous créerons un contrôleur pour gérer la connexion. "securityController.yaml"
Enfin, je vous montrerai comment tester cela avec Postman. 
##test de connexion avec postman 
# Configuration de la requête dans Postman

URL: http://localhost:8000/api/login
Méthode: POST

Headers:
- Content-Type: application/json
- Accept: application/json

Body (raw - JSON):
{
    "email": "votre_email@exemple.com",
    "password": "votre_mot_de_passe"
}

# Exemple de réponse réussie
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MjA1NzY5MDAsImV4cCI6MTYyMDU4MDUwMCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidm90cmVfZW1haWxAZXhlbXBsZS5jb20ifQ.xyz..."
}

Conseil de débogage :
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