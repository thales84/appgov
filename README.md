# AppGov Cameroun

AppGov est une plateforme web de dématérialisation des démarches administratives
camerounaises. Le permis de conduire est le premier parcours complet. Le même
socle doit ensuite accueillir les cartes de séjour, la naturalisation, les
diplômes et d'autres services publics.

## Principes

- Un seul compte usager par e-mail et mot de passe.
- Une démarche commence, se traite et s'archive numériquement.
- Les procédures, pièces, étapes et tarifs sont configurables et versionnés.
- L'usager voit l'état du dossier, la prochaine action et l'historique utile.
- Chaque action sensible est autorisée, horodatée et auditée.
- Français par défaut, anglais disponible dès le MVP.
- Interface mobile-first, accessible et adaptée aux connexions faibles.

## Stack

- PHP 8.3, Laravel 12
- Vue 3.5, Inertia 2, Vite 7
- Tailwind CSS 3.4
- MySQL 8, Redis
- Pest 4
- Spatie Permission, Spatie Activity Log
- Laravel Sanctum, Pulse, Sentry et DomPDF
- Laravel Fortify pour l’authentification web et le TOTP

La stack est alignée sur SchoolPro CM, mais le code métier scolaire et la
stratégie `school_id` ne doivent pas être copiés.

## Documentation

Un agent IA doit commencer par [AGENTS.md](AGENTS.md), puis lire :

1. [Vision produit](docs/00-vision.md)
2. [Décisions actées](docs/01-decisions.md)
3. [Architecture](docs/02-architecture.md)
4. [Modèle métier](docs/03-domain-model.md)
5. [Workflows](docs/04-workflows.md)
6. [Design system](docs/05-design-system.md)
7. [Sécurité et données](docs/06-security.md)
8. [Tests et qualité](docs/07-testing.md)
9. [Feuille de route](docs/08-roadmap.md)

## État

Les phases 0, 1 et 2 sont terminées :

- portail public et socle Laravel/Vue opérationnels;
- inscription usager, vérification d’e-mail et récupération du mot de passe;
- profil usager et niveaux de vérification d’identité;
- portail agent séparé avec TOTP obligatoire;
- organismes, unités, territoires, affectations, rôles et permissions;
- espaces public, usager, agent et administration en français et en anglais;
- contrôles d’accès croisés, audit de sécurité et tests automatisés.
- catalogue public bilingue avec recherche, catégories et fiches procédures;
- éditeur agent versionné pour étapes, champs, pièces, règles et tarifs;
- cycle brouillon, revue indépendante, publication, retrait et immutabilité;
- première procédure de permis entièrement marquée `DEMO`;
- création idempotente d’un brouillon usager depuis une version publiée.

La phase active suivante est la phase 3 : dossier, formulaire dynamique et
documents privés.

## Installation locale

Prérequis : PHP 8.3+, Composer, Node.js 22+, npm, MySQL 8 et Redis pour une
configuration proche de la production.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

Créer auparavant la base `appgov` et renseigner ses accès dans `.env`.

Pour travailler sans MySQL ni Redis, utiliser temporairement SQLite, le cache
base de données et la queue base de données. La production doit rester sur
MySQL 8 et Redis.

Lancer l'environnement de développement :

```bash
composer dev
```

Contrôles avant livraison :

```bash
composer test
./vendor/bin/pint --test
npm run build
```

Le contrat d’identité, la commande d’invitation agent et la matrice
d’autorisation sont détaillés dans
[docs/09-phase-1-identity-access.md](docs/09-phase-1-identity-access.md).

Le contrat du catalogue, son cycle de publication et les limites des données
`DEMO` sont détaillés dans
[docs/10-phase-2-catalog.md](docs/10-phase-2-catalog.md).
