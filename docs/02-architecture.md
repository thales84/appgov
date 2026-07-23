# Architecture technique

## Style

AppGov est un monolithe modulaire Laravel. Il produit les pages Vue avec Inertia
et expose des API versionnées uniquement pour les intégrations nécessaires.
Cette forme réduit la complexité opérationnelle tout en maintenant des frontières
métier claires.

## Stack exacte

| Besoin | Choix |
|---|---|
| Runtime | PHP 8.3 |
| Framework | Laravel 12 |
| Frontend | Vue 3.5 + Inertia 2 |
| Styles | Tailwind CSS 3.4 via PostCSS |
| Bundler | Vite 7 |
| Données | MySQL 8 |
| Cache, sessions, queues | Redis |
| Auth API | Laravel Sanctum |
| Permissions | `spatie/laravel-permission` |
| Audit métier | `spatie/laravel-activitylog` |
| PDF | `barryvdh/laravel-dompdf` |
| Observabilité | Laravel Pulse + Sentry |
| Tests | Pest 4 |

## Modules

```text
app/Domain/
├── Identity
├── Organizations
├── Catalog
├── Applications
├── Documents
├── Payments
├── Appointments
├── Examinations
├── Decisions
├── Issuance
├── Notifications
├── Reporting
└── Audit
```

Chaque module peut contenir `Actions`, `Data`, `Enums`, `Events`, `Jobs`,
`Models`, `Policies`, `Queries` et `Services`. Ne créer un sous-dossier que
lorsqu'il contient réellement du code.

## Dépendances autorisées

```text
Identity ─┐
Organizations ─┼─> Catalog ─> Applications
              │                  ├─> Documents
              │                  ├─> Payments
              │                  ├─> Appointments ─> Examinations
              │                  ├─> Decisions ─> Issuance
              │                  └─> Notifications
              └────────────────────> Audit / Reporting
```

- `Audit` et `Notifications` réagissent aux événements; ils ne pilotent pas le
  métier.
- Aucun module métier ne dépend d'un composant Vue.
- Aucun accès direct aux tables d'un autre module depuis un contrôleur.
- Éviter les dépendances circulaires; communiquer par contrats ou événements.

## Couches

- **HTTP** : routes, Form Requests, contrôleurs minces, Resources.
- **Application** : actions de cas d'usage et transactions.
- **Domaine** : règles, transitions, permissions et événements.
- **Infrastructure** : e-mail, stockage, paiement, antivirus, PDF et API externes.
- **UI** : pages Inertia et composants Vue sans règle métier critique.

## Routes

```text
/                         portail public
/services                 catalogue public
/account/*                espace usager authentifié
/agent/*                  back-office agent
/admin/*                  configuration et supervision
/verify/{reference}       vérification publique minimale
/api/v1/*                 intégrations autorisées
```

Utiliser des noms de routes. Les URL ne doivent pas contenir d'identifiant
séquentiel interne.

## Données et performances

- Clés primaires internes adaptées à MySQL; `public_id` ULID pour les ressources
  exposées.
- Référence de suivi séparée, aléatoire et avec caractère de contrôle.
- Indexer états, dates, affectations, références et clés étrangères.
- Paginer les listes et charger explicitement les relations nécessaires.
- Les tableaux de bord utilisent des requêtes de lecture dédiées.
- Redis porte sessions, cache, limitation de débit et files de tâches.
- Les tâches lourdes passent en queue avec délai, tentatives et traitement
  d'échec explicites.

## Fiabilité

- Transactional outbox pour paiement, notification et intégrations.
- Clé d'idempotence pour les callbacks et commandes externes.
- Verrouillage ou contrôle de version pour empêcher deux décisions concurrentes.
- Health checks pour base, Redis, stockage, queue et fournisseurs externes.
- Sauvegardes chiffrées et tests réguliers de restauration.

## Environnements

`local`, `testing`, `staging` et `production` ont des secrets et données séparés.
Le fichier `.env.example` documente les variables sans valeur sensible. Aucun
agent ne copie le `.env` de SchoolPro.

