# 📧 Guide de Configuration Mail - MenuPro

**Date :** 16 janvier 2026

## 🎯 Objectif

Configurer l'envoi d'emails pour :
- ✅ Vérification d'email (lien de confirmation)
- ✅ Réinitialisation de mot de passe
- ✅ Notifications de commandes
- ✅ Notifications d'abonnement
- ✅ Notifications de réservations

---

## ⚙️ Configuration dans `.env`

### Option 1 : Gmail (Recommandé pour développement/test)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@menupro.ci
MAIL_FROM_NAME="MenuPro"
```

**⚠️ Important pour Gmail :**
1. Activez l'**authentification à 2 facteurs** sur votre compte Gmail
2. Générez un **mot de passe d'application** :
   - Allez sur : https://myaccount.google.com/apppasswords
   - Sélectionnez "Mail" et "Autre (nom personnalisé)"
   - Entrez "MenuPro" comme nom
   - Copiez le mot de passe généré (16 caractères)
   - Utilisez ce mot de passe dans `MAIL_PASSWORD` (pas votre mot de passe Gmail normal)

---

### Option 2 : SMTP Professionnel (Production)

#### Avec Mailgun (Recommandé)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@votre-domaine.mailgun.org
MAIL_PASSWORD=votre-cle-api-mailgun
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="MenuPro"
```

**Configuration Mailgun :**
1. Créez un compte sur https://www.mailgun.com
2. Vérifiez votre domaine
3. Récupérez les identifiants SMTP dans le dashboard
4. Utilisez le domaine vérifié dans `MAIL_FROM_ADDRESS`

#### Avec SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=votre-cle-api-sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.com
MAIL_FROM_NAME="MenuPro"
```

---

### Option 3 : SMTP Local (Laragon/Development)

Si vous utilisez Laragon avec un serveur mail local :

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@menupro.local
MAIL_FROM_NAME="MenuPro"
```

**Note :** Les emails seront capturés par le serveur mail local et ne seront pas réellement envoyés.

---

### Option 4 : Log (Développement - Emails dans les logs)

Pour tester sans envoyer d'emails réels :

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@menupro.ci
MAIL_FROM_NAME="MenuPro"
```

Les emails seront écrits dans `storage/logs/laravel.log`

---

## 🧪 Tester la Configuration

### Méthode 1 : Commande Artisan

Créez une commande de test :

```bash
php artisan make:command TestEmail
```

Puis dans `app/Console/Commands/TestEmail.php` :

```php
public function handle()
{
    \Illuminate\Support\Facades\Mail::raw('Test email MenuPro', function ($message) {
        $message->to('votre-email@test.com')
                ->subject('Test Email MenuPro');
    });
    
    $this->info('Email envoyé ! Vérifiez votre boîte mail.');
}
```

Exécutez : `php artisan test:email`

### Méthode 2 : Via Tinker

```bash
php artisan tinker
```

Puis :

```php
Mail::raw('Test', function($m) {
    $m->to('votre-email@test.com')->subject('Test');
});
```

---

## 📋 Vérification de la Configuration

### 1. Vérifier les variables d'environnement

```bash
php artisan config:clear
php artisan config:cache
```

### 2. Vérifier le fichier de configuration

Le fichier `config/mail.php` utilise les variables `.env`. Vérifiez que tout est correct :

```php
'default' => env('MAIL_MAILER', 'log'),
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', 'Example'),
],
```

---

## 🔧 Configuration dans les Paramètres Super Admin

Vous pouvez aussi configurer le SMTP depuis l'interface Super Admin :

1. Connectez-vous en tant que Super Admin
2. Allez dans **Paramètres** → **Emails**
3. Remplissez les champs SMTP :
   - Serveur SMTP
   - Port
   - Email expéditeur
   - Nom expéditeur
   - Mot de passe

Ces paramètres sont stockés dans `SystemSetting` et peuvent être utilisés dynamiquement.

---

## 📝 Emails Envoyés par le Système

### 1. Vérification d'Email
- **Quand :** Après inscription
- **Contenu :** Lien de vérification
- **Route :** `/email/verifier/{id}/{hash}`

### 2. Réinitialisation de Mot de Passe
- **Quand :** Demande de réinitialisation
- **Contenu :** Lien de réinitialisation
- **Route :** `/reinitialiser-mot-de-passe/{token}`

### 3. Nouvelle Commande
- **Quand :** Nouvelle commande reçue
- **Contenu :** Détails de la commande
- **Destinataire :** Restaurant

### 4. Réservation
- **Quand :** Nouvelle réservation
- **Contenu :** Détails de la réservation
- **Destinataires :** Restaurant et client

### 5. Abonnement
- **Quand :** Expiration proche, expiration, validation
- **Contenu :** Informations d'abonnement
- **Destinataire :** Restaurant

---

## ⚠️ Problèmes Courants

### Les emails ne partent pas

1. **Vérifiez les logs :**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifiez la configuration :**
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

3. **Testez avec log :**
   ```env
   MAIL_MAILER=log
   ```
   Puis vérifiez `storage/logs/laravel.log`

### Erreur "Connection refused"

- Vérifiez que le port est correct (587 pour TLS, 465 pour SSL)
- Vérifiez que le firewall n'bloque pas le port
- Pour Gmail, assurez-vous d'utiliser un mot de passe d'application

### Erreur "Authentication failed"

- Vérifiez `MAIL_USERNAME` et `MAIL_PASSWORD`
- Pour Gmail, utilisez un mot de passe d'application, pas votre mot de passe normal
- Vérifiez que l'authentification à 2 facteurs est activée (Gmail)

### Les emails arrivent en spam

- Utilisez un domaine vérifié (Mailgun, SendGrid)
- Configurez SPF et DKIM pour votre domaine
- Utilisez un nom d'expéditeur cohérent
- Évitez les mots déclencheurs de spam

---

## 🚀 Recommandations Production

1. **Utilisez un service SMTP professionnel** (Mailgun, SendGrid, AWS SES)
2. **Vérifiez votre domaine** avec SPF et DKIM
3. **Utilisez un domaine dédié** pour les emails (ex: noreply@menupro.ci)
4. **Configurez les queues** pour l'envoi asynchrone :
   ```env
   QUEUE_CONNECTION=database
   ```
   Puis : `php artisan queue:work`

5. **Surveillez les taux de délivrabilité**
6. **Configurez les webhooks** pour suivre les bounces et plaintes

---

## ✅ Checklist de Configuration

- [ ] Variables `.env` configurées
- [ ] Configuration testée avec `php artisan test:email`
- [ ] Email de vérification fonctionne
- [ ] Email de réinitialisation fonctionne
- [ ] Notifications de commandes fonctionnent
- [ ] Domain vérifié (production)
- [ ] SPF/DKIM configurés (production)
- [ ] Queues configurées (production)

---

**Configuration terminée !** 🎉

