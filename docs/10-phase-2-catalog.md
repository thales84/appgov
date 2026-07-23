# Phase 2 — Catalogue configurable des démarches

Ce document décrit la tranche verticale livrée par la phase 2. Il fixe le
contrat à respecter avant de construire les formulaires et documents de la
phase 3.

## Parcours public

- `GET /services` affiche uniquement les services actifs dont l’organisme est
  actif et dont la version courante est publiée et effective.
- La recherche porte sur les noms et descriptions français et anglais.
- Le filtre de catégorie utilise un `public_id` opaque.
- `GET /services/{service}` affiche la définition publiée : étapes, champs,
  pièces, règles et tarifs.
- Les deux langues sont envoyées au client; le changement de langue ne demande
  pas une nouvelle requête.
- Une version retirée ne laisse jamais réapparaître automatiquement une ancienne
  version publiée.

Les noms de catégories, couleurs et contenus du seeder sont des données de
démonstration. Seul le design system est normatif.

## Définition versionnée

L’agrégat `procedure_versions` possède :

- des contenus publics français et anglais;
- une date d’effet stockée en UTC;
- les états `draft`, `under_review`, `published` et `retired`;
- des étapes ordonnées;
- des champs typés et des choix bilingues;
- des pièces obligatoires ou facultatives;
- des règles descriptives typées;
- des tarifs avec `amount_minor`, devise ISO 4217 et exposant d’unité mineure.

Les enfants portent leur propre `public_id`. Une version publiée n’est jamais
modifiée pour faire évoluer une règle ou un tarif : une nouvelle version est
clonée, reçoit une nouvelle date d’effet, puis suit à nouveau la revue.

## Cycle de publication

```text
draft
  → under_review
  ↘ draft avec motif de correction
  → published
  → retired
```

Une version ne peut partir en revue que si elle possède une date d’effet et au
moins une étape. Un champ à choix doit contenir des options françaises et
anglaises.

La publication vérifie :

- la permission `catalog.publish`;
- l’affectation active dans l’organisme propriétaire;
- l’état `under_review`;
- une date d’effet postérieure à la dernière version publiée;
- la séparation des fonctions : le créateur ne publie pas sa version.

Toute transition utilise une transaction, verrouille la version et écrit un
audit sans donnée personnelle. Les modifications ordinaires de l’agrégat sont
bloquées dès l’entrée en revue. Le retrait exige un motif et ne supprime aucune
donnée.

## Portail agent

Le catalogue est disponible sous `GET /admin/catalog`. Les requêtes sont
limitées aux organismes des affectations actives de l’agent.

Permissions :

| Permission | Usage |
|---|---|
| `catalog.view` | consulter le catalogue de son périmètre |
| `catalog.edit` | créer un service et modifier un brouillon |
| `catalog.submit_review` | envoyer un brouillon en revue |
| `catalog.publish` | retourner en correction ou publier une version d’un autre auteur |
| `catalog.retire` | retirer une version publiée avec motif |
| `catalog.categories.manage` | ajouter une catégorie globale avec une couleur autorisée |

Le rôle `catalog_administrator` édite et soumet. Le rôle
`platform_administrator` porte provisoirement la publication, le retrait et les
catégories. L’autorité camerounaise exacte doit remplacer cette attribution
technique avant la production.

## Début d’un dossier

`GET /account/services/{service}/start` exige un usager authentifié avec e-mail
vérifié. La confirmation appelle
`POST /account/services/{service}/applications`.

L’action :

1. verrouille le service;
2. résout à nouveau sa version publiée courante;
3. crée un dossier `draft`;
4. utilise une clé de brouillon unique pour rendre l’opération idempotente;
5. audite uniquement les références publiques utiles.

`GET /account/applications/{application}` passe par une Policy et refuse tout
autre usager. Aucun numéro de suivi n’est créé avant le dépôt. Les réponses,
participants, documents privés, accusé PDF et référence publique appartiennent
à la phase 3.

## Données de démonstration

`CatalogDemoSeeder` crée :

- les six familles visuelles prévues par le design system;
- un organisme nommé explicitement « administration de démonstration —
  non officielle »;
- un service `DEMO-DRIVING-LICENCE`;
- une version de permis publiée dont chaque règle, pièce, étape et tarif est
  marqué `DEMO`;
- un tarif de zéro XAF, sans prétendre connaître un montant officiel.

Le seeder ne crée aucun compte, mot de passe, territoire ou organisme supposé
officiel. Avant un pilote, toutes les catégories, règles, pièces, autorités,
bases réglementaires et tarifs doivent être validés puis publiés dans une
nouvelle version.

## Contrat pour la phase 3

La phase 3 doit :

- conserver le lien immuable vers `procedure_version_id`;
- valider chaque réponse avec la définition versionnée;
- effacer `draft_key` lorsque le dossier quitte réellement l’état `draft`;
- ne créer la référence de suivi qu’au dépôt;
- stocker les documents dans le stockage objet privé;
- compléter l’instantané nécessaire à la compréhension future du dossier;
- préserver la Policy propriétaire déjà appliquée à la lecture du brouillon.

