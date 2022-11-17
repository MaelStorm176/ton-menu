
# Ton Menu

Site web développé pour notre projet de fin de 4e année Ingénierie du Web.
Ton Menu met à disposition des centaines de recettes, ingrédients ainsi qu'un générateur complet de menus.

Une partie "réseau social" est aussi présente permettant à des chefs aussi bien professionnels que débutants de proposer leurs recettes et leurs menus.



## Installation

Veillez à utiliser docker pour l'intégralité du projet

#### Installation et lancement du projet
```bash
  make start
  make install
```

#### Construction de la base de données
```bash
  make build-db
```

#### Build des assets
```bash
  make npm-build
```
OU
```bash
  make npm-dev
```
OU
```bash
  make npm-watch
```
#### Importation des données de Marmiton (optionnel)
```bash
  make marmiton
```
## API Reference
Pour chacune des requêtes suivantes un token de type **Bearer Token** doit être envoyé dans le header de la requete.

#### Documentation

```http
  GET /api
```

#### Retrouver des recettes

```http
  GET /api/recipes
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `name` | `string` | **Optionnal**. Nom de la recette |
| `type` | `string` | **Optionnal**. entree ou plat ou dessert |
| `page` | `integer` | **Optionnal**. page de résultat |

#### Retrouver une recette spécifique

```http
  GET /api/recipes/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `integer` | **Required**. Identifiant de la recette |

#### Retrouver une recette aléatoire

```http
  GET /api/recipes/random/${type}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `type`      | `string` | **Required**. entree ou plat ou dessert |


#### Générer des menus

```http
  GET /api/recipes/menus/${nombre}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `nombre`      | `integer` | **Required**. nombre de menus à générer - 1 menu est composé de 3 recettes (entrée + plat + dessert) |



## Variables d'environnement

Pour utiliser ce projet dans les meilleures conditions vous aurez besoin d'initialiser certaines variables d'environnement dans le fichier .env ou .env.local

`DB_USER` : utilisateur de la database

`DB_PASSWORD` : mdp de la database

`DB_ADRESS` : adresse de la database

`DB_PORT` : port de la database

`DB_NAME` : nom de la database

`MAILER_DSN` : serveur smtp

`DATABASE_URL` : url de la database avec version de mysql utilisée
## Features

- Création / Modification / Suppression de recettes
- Scrapping de Marmiton afin d'importer des recettes
- Filtrage complet des recettes
- Générateur de menus
- Générateur de recettes en fonctions d'ingrédients
- 4 Roles utilisateur (User, Chief, Premium, Admin)
- Système de paiement Stripe
- Api fonctionnelle


## Tech Stack

**Client:** Twig, JQuery, Bootstrap, WebPack Encore, template (https://bootstrapmade.com/restaurantly-restaurant-template/)

**Server:** PHP, Symfony

**Database:** MySQL


## Demo

[TonMenu](https://tonmenu.osc-fr1.scalingo.io/)


## Auteurs

- [@Mael Jamin](https://github.com/MaelStorm176)
- [@Raphaël Bessonnier](https://github.com/ThePrimesBros)
- [@Ulysse MF](https://github.com/ThePrimesBros)

## License

[Apache-2.0](https://choosealicense.com/licenses/apache-2.0/)

