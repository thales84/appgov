# Phase 1 — Identité, accès et design applicatif

Ce document décrit le contrat livré par la phase 1. Il sert de guide aux agents
IA qui construiront le catalogue et les dossiers sans affaiblir le socle
d’authentification.

## Parcours usager

1. `GET /register` présente l’inscription publique.
2. `POST /register` crée toujours un compte `citizen`, même si une valeur
   `account_type` malveillante est envoyée.
3. Un `citizen_profile` est créé dans la même transaction.
4. Le compte est connecté, mais `/account` reste inaccessible tant que l’e-mail
   n’est pas vérifié.
5. La vérification fait passer le niveau de `unverified` à `email_verified`.
6. Un profil complété avec un téléphone passe à `identity_declared`.
7. `identity_checked` est réservé à un futur contrôle par une autorité habilitée.

Une modification de l’e-mail annule sa vérification, abaisse le niveau
d’identité, avertit l’ancienne adresse et envoie un nouveau lien à la nouvelle.

## Parcours agent

L’agent ne peut pas s’inscrire publiquement. L’invitation opérationnelle utilise :

```bash
php artisan appgov:provision-agent \
  "Nom de l'agent" \
  agent@administration.example \
  CODE_ORGANISME \
  --role=agent_caseworker \
  --unit=CODE_UNITE \
  --territory=CODE_TERRITOIRE
```

Les codes de l’exemple sont des paramètres à remplacer par des données validées;
ils ne constituent pas un référentiel officiel.

La commande exige un organisme actif et au moins un rôle existant. Elle :

- crée un mot de passe aléatoire qui n’est jamais affiché;
- envoie un lien temporaire de définition du mot de passe;
- envoie la vérification de l’e-mail;
- crée l’affectation organisme/unité/territoire;
- trace le provisionnement sans enregistrer de secret.

Le portail agent est `GET /agent/login`. Un compte usager y est refusé, même
avec un mot de passe valide. Réciproquement, un agent est refusé par le portail
usager.

Après la première connexion, l’agent est dirigé vers `/agent/security`. Le
tableau de bord reste bloqué jusqu’à la confirmation d’un secret TOTP. Les codes
de récupération sont affichés uniquement après cette confirmation.

## Autorisation

Un accès agent métier exige simultanément :

```text
compte actif
  + type agent
  + e-mail vérifié
  + permission platform.access.agent
  + affectation active
  + TOTP confirmé
```

Les rôles décrivent ce que l’agent peut faire. `agent_assignments` décrit où il
peut le faire. Les requêtes des phases suivantes doivent appliquer les deux
dimensions; une permission seule ne suffit jamais.

Rôles de base :

- `agent_reception`;
- `agent_caseworker`;
- `agent_examiner`;
- `agent_finance`;
- `agent_production`;
- `agent_delivery`;
- `agent_supervisor`;
- `catalog_administrator`;
- `auditor`;
- `platform_administrator`.

Le seeder crée uniquement les rôles et permissions. Il ne crée aucun compte,
mot de passe, organisme ou territoire supposé officiel.

## Tables et identifiants

- `users` : identifiant public ULID, type et état du compte, secrets TOTP
  chiffrés par Laravel;
- `citizen_profiles` : identité déclarée, langue et niveau de vérification;
- `organizations` : organisme responsable;
- `administrative_units` : direction, délégation, centre ou guichet;
- `territories` : hiérarchie territoriale;
- `agent_assignments` : périmètre actif d’un agent;
- tables Spatie : rôles et permissions;
- `activity_log` : événements de sécurité sans mot de passe, jeton ou secret.

Les identifiants numériques restent internes. Toute future URL métier utilise
le `public_id`.

## Interfaces livrées

- portail public et en-tête connecté;
- écrans inscription, connexion, oubli et réinitialisation du mot de passe;
- vérification de l’e-mail et défi TOTP;
- tableau de bord et profil usager;
- tableau de bord et sécurité agent;
- squelette d’administration;
- composants `AuthLayout`, `AppShell`, `AgentShell`, `FormField`,
  `StatusBadge` et `EmptyState`;
- textes français et anglais centralisés dans `resources/js/locales`.

## Sécurité et exploitation

- Argon2id est le pilote de hachage par défaut;
- les connexions et défis TOTP sont limités en débit;
- les sessions sont régénérées après connexion et actions sensibles;
- les comptes suspendus ne peuvent pas s’authentifier;
- les réponses ajoutent les en-têtes anti-framing, MIME, permissions navigateur
  et isolation d’origine;
- `APP_CSP_ENABLED=true` active la CSP en staging/production après validation
  des domaines externes; elle reste désactivée avec le serveur Vite local;
- les pages HTML interdites reçoivent une réponse Inertia 403 dédiée;
- les changements de profil, mot de passe, e-mail et TOTP sont audités sans
  valeur sensible.

Avant production, l’autorité doit encore valider le fournisseur d’e-mail, le
référentiel des organismes et territoires, la politique de support et la
topologie HTTPS.

## Contrat pour la phase 2

Le catalogue public peut lire les services sans compte. Commencer un dossier
devra utiliser les middlewares `auth`, `verified` et `account.type:citizen`.
L’administration du catalogue utilise le portail agent, le TOTP, une affectation
active et les permissions granulaires `catalog.view`, `catalog.edit`,
`catalog.submit_review`, `catalog.publish`, `catalog.retire` et
`catalog.categories.manage`. `catalog.manage` reste un marqueur de compatibilité,
mais ne suffit pas seul à autoriser une mutation.
