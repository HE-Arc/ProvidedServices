# **Projet Web Laravel : ProvidedServices**

## **Description du projet**

Provided Services est une application web permettant à des clients de publier des annonces pour rechercher des prestataires. Les prestataires peuvent postuler aux annonces, et les clients peuvent gérer les candidatures en acceptant ou refusant les prestataires. Une fois un prestataire accepté, un email de confirmation lui est envoyé.

## **Technologies utilisées**

- **Backend** : PHP 8.1.x avec **Laravel**
- **Frontend** : Vue.js
- **Base de données** : MySQL (MariaDB avec XAMPP)
- **Serveur local** : XAMPP (Apache et MySQL)
- **Emails** : SMTP (Gmail)
- **Gestion des dépendances** : Composer et npm

## **Prérequis**

Assurez-vous d'avoir installé les éléments suivants sur votre machine :

1. [**PHP 8.1.x**](https://www.php.net/)
2. [**Composer**](https://getcomposer.org/)
3. [**Node.js** (et npm)](https://nodejs.org/)
4. [**XAMPP**](https://www.apachefriends.org/) (ou un serveur local similaire)


## **Installation**

### **1. Cloner le projet**

```bash
git clone https://github.com/HE-Arc/ProvidedServices.git
cd ProvidedServices
```

### **2. Configuration**

1. **Copier le fichier `.env.example` et le renommer `.env`**

```bash
cp .env.example .env
```

2. **Configurer les informations de la base de données et du mail** dans le fichier `.env` :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=provided-services
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=providedservicesapp@gmail.com
MAIL_PASSWORD="awff lzrs ylgn kjdv"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=providedservicesapp@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

3. **Lancer XAMPP** et démarrer Apache et MySQL.

### **3. Installer les dépendances**

```bash
composer install
npm install
```

### **4. Générer la clé d'application**

```bash
php artisan key:generate
```

### **5. Créer et peupler la base de données**

1. **Migrer les tables** :

```bash
php artisan migrate
```

2. **Insérer des données d'exemple** (optionnel mais utile) :

```bash
php artisan db:seed
```

### **6. Démarrer le serveur**

1. **Lancer le serveur Laravel** :

```bash
php artisan serve
```

2. **Compiler les assets Vue.js** :

```bash
npm run dev
```

---

### **Accès à l'application**

- Rendez-vous sur [http://127.0.0.1:8000](http://127.0.0.1:8000)

## **Commandes utiles**

### **Démarrer l'application**

- **Backend** : `php artisan serve`
- **Frontend** : `npm run dev`
- **Démarrer XAMPP** pour Apache et MySQL.

### **Première configuration**

- **Migrations et seeds** :

```bash
php artisan migrate
php artisan db:seed
```

### **Envoyer les emails**

Assurez-vous que les paramètres d'email sont correctement configurés dans le fichier `.env`.


## **Structure du projet**

- **Backend** : `app/Http/Controllers`  
   - Contient les contrôleurs comme `JobPostController` pour gérer les annonces et candidatures.

- **Frontend** : `resources/js`  
   - Contient les composants Vue.js (ex. `dashboard.vue` pour le tableau de bord).

- **Emails** : `app/Mail`  
   - Contient les templates d'emails comme `ApplicationAcceptedMail`.

- **Vues Blade** : `resources/views`  
   - Contient les vues pour les emails et le layout global.

---

## **Problèmes courants**

### **"php artisan serve" ne fonctionne pas**

- Assurez-vous que PHP est ajouté au **PATH**.
- Redémarrez votre terminal.

### **Erreur MySQL : "port déjà utilisé"**

- Changez le port MySQL dans `xampp\mysql\bin\my.ini` (ex. 3307).
- Adaptez également la configuration `.env` :

```plaintext
DB_PORT=3307
```

## **Contributeurs**

- **Annen Julien** - Développeur Backend/Frontend
- **Berthoud Simon** - Développeur Backend/Frontend
