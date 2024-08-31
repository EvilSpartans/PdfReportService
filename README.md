
# Symfony PDF Report Microservice

Ce projet Symfony permet de générer des rapports PDF basés sur des templates définis, ainsi que de gérer des clés API pour sécuriser les accès. Le projet inclut des commandes pour ajouter, lister et supprimer des clés API, ainsi que pour nettoyer les répertoires où les fichiers PDF sont stockés.

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- Symfony CLI
- Une base de données (SQLite est utilisée par défaut)
- wkhtmltopdf et wkhtmltoimage installés sur votre machine

## Installation

1. Clonez le dépôt :

   ```bash
   git clone https://github.com/EvilSpartans/PdfReportService.git
   cd PdfReportService
   ```

2. Installez les dépendances :

   ```bash
   composer install
   ```

3. Configurez vos variables d'environnement :

   ```bash
   cp .env .env.local
   ```

4. Initialisez la base de données :

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:schema:update --force
   ```

## Commandes Disponibles

### Ajouter une clé API

Pour ajouter une nouvelle clé API, utilisez la commande suivante :

```bash
php bin/console app:add-api-key "NomClient" "cle-api"
```

- **NomClient** : Le nom du client associé à la clé API.
- **cle-api** : La clé API à attribuer au client.

### Lister les clés API

Pour lister toutes les clés API disponibles, utilisez la commande suivante :

```bash
php bin/console app:list-api-keys
```

Cette commande affichera un tableau contenant le nom du client, la clé API et la date de création.

### Supprimer une clé API

Pour supprimer une clé API en fonction du nom du client, utilisez la commande suivante :

```bash
php bin/console app:delete-api-key "NomClient"
```

- **NomClient** : Le nom du client associé à la clé API que vous souhaitez supprimer.

### Nettoyer les répertoires de téléchargement

Pour vider les répertoires `charts`, `invoices`, `payslips`, et `salesReports` sous `uploads`, utilisez la commande suivante :

```bash
php bin/console app:clear-uploads
```

Cette commande supprimera tous les fichiers dans ces sous-dossiers.

## Génération de PDF

Le microservice prend en charge la génération de trois types de documents PDF : Facture, Bulletin de Paie, et Rapport de Ventes. Chaque type de document nécessite un format spécifique de données.

### Générer une Facture

Pour générer une facture, envoyez une requête POST à `/report` avec les données suivantes :

**URL :** `/report`

**Méthode :** `POST`

**Exemple de données JSON :**

```json
{
  "templateId": 1,
  "title": "Facture Juin 2024",
  "invoiceNumber": "INV-2024-001",
  "date": "2024-06-15",
  "clientName": "Client ABC",
  "items": [
    { "name": "Produit 1", "price": 100 },
    { "name": "Service 2", "price": 200 }
  ],
  "total": 300
}
```

### Générer un Bulletin de Paie

Pour générer un bulletin de paie, envoyez une requête POST à `/report` avec les données suivantes :

**URL :** `/report`

**Méthode :** `POST`

**Exemple de données JSON :**

```json
{
  "templateId": 2,
  "title": "Bulletin de Paie Juin 2024",
  "employeeName": "John Doe",
  "payPeriod": "Juin 2024",
  "employerName": "Entreprise XYZ",
  "paymentDate": "2024-06-30",
  "baseSalary": 3000,
  "bonus": 500,
  "socialSecurity": 400,
  "taxes": 600,
  "netSalary": 2500
}
```

### Générer un Rapport de Ventes

Pour générer un rapport de ventes, envoyez une requête POST à `/report` avec les données suivantes :

**URL :** `/report`

**Méthode :** `POST`

**Exemple de données JSON :**

```json
{
  "templateId": 3,
  "title": "Rapport de Ventes Juin 2024",
  "period": "Juin 2024",
  "sales": [
    { "productName": "Produit A", "quantity": 50, "unitPrice": 20, "revenue": 1000 },
    { "productName": "Produit B", "quantity": 30, "unitPrice": 30, "revenue": 900 },
    { "productName": "Service C", "quantity": 20, "unitPrice": 50, "revenue": 1000 }
  ],
  "totalSales": 2900,
  "totalProducts": 100,
  "chartUrl": "/uploads/charts/nom-du-graphique.png"
}
```

## Sécurité

Les routes qui génèrent les PDF sont sécurisées avec des clés API. Assurez-vous de passer la clé API correcte dans l'en-tête de la requête :

```http
X-API-KEY: votre-cle-api
```

## Conclusion

Ce microservice est conçu pour être facilement extensible et sécurisé. Il vous permet de gérer les clés API, de générer divers types de documents PDF, et de maintenir votre environnement propre avec les commandes fournies.
