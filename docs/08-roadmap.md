# Feuille de route

Travailler dans l'ordre. Une phase est cochée uniquement après satisfaction de
tous ses critères de sortie.

## Phase 0 — Initialisation

- [x] Créer le projet Laravel 12 dans ce dépôt.
- [x] Installer Vue, Inertia, Vite et Tailwind 3.4 de façon cohérente.
- [x] Configurer MySQL, Redis, queues, mail et stockage privé.
- [x] Installer les packages actés.
- [x] Créer les modules et conventions de base.
- [x] Ajouter `.env.example`, CI minimale et health check.

**Sortie :** page publique et page Inertia fonctionnent; tests, build et queue
passent dans un environnement vierge.

## Phase 1 — Identité, accès et design

- [x] Inscription, connexion, vérification d'e-mail et mot de passe oublié.
- [x] Profil usager et niveau de vérification d'identité.
- [x] Espace agent séparé et second facteur.
- [x] Organisations, unités, territoires, rôles et permissions.
- [x] Squelettes public, usager, agent et admin.
- [x] Tokens UI, composants fondamentaux et français/anglais.

**Sortie :** un usager et un agent habilité accèdent uniquement à leurs espaces;
les contrôles d'autorisation sont testés.

## Phase 2 — Catalogue des démarches

- [x] Catégories, services et versions de procédure.
- [x] Étapes, champs, pièces, règles et tarifs.
- [x] Cycle brouillon, revue, publication et retrait.
- [x] Catalogue public avec recherche et fiche procédure.
- [x] Première procédure de permis marquée `DEMO`.

**Sortie :** une version publiée est immuable et permet de commencer un dossier.

## Phase 3 — Dossier et documents

- [x] Brouillon et formulaire dynamique validé côté serveur.
- [x] Participants et bénéficiaire.
- [x] Upload privé, empreinte, analyse et quarantaine.
- [x] Dépôt horodaté, référence publique et accusé PDF.
- [x] Tableau de bord usager et rail du dossier.

**Sortie :** un usager dépose un dossier complet et ne peut jamais lire celui
d'un tiers.

## Phase 4 — Instruction et workflow

- [x] Files d'affectation par organisme, unité et territoire.
- [x] Contrôle des pièces.
- [x] Messagerie et demande de correction ciblée.
- [x] Service de transitions, historique, audit et prochaine action.
- [x] Avis, double contrôle et décision.

**Sortie :** le dossier traverse l'instruction complète avec autorisations et
audit vérifiés.

## Phase 5 — Facturation et paiement

- [x] Factures et lignes avec instantané des tarifs.
- [x] Interface de fournisseur de paiement et faux fournisseur local.
- [x] Callback signé, idempotence et rapprochement.
- [x] Reçu PDF et affichage usager.
- [x] Échec, annulation et remboursement autorisé.

**Sortie :** aucun callback répété ne produit un double paiement et le workflow
ne confond pas état financier et état du dossier.

## Phase 6 — Rendez-vous et examens

- [x] Centres, capacités, créneaux et réservations.
- [x] Sessions et types d'épreuves.
- [x] Présence, résultat, validation et nouvelle tentative.
- [x] Correction exceptionnelle auditée.
- [x] Consultation privée des résultats.

**Sortie :** le parcours permis va de l'éligibilité à la décision finale avec
les règles de démonstration clairement identifiées.

## Phase 7 — Production, suivi et remise

- [x] Titre attribué et numéro distinct.
- [x] Lots et mouvements de production.
- [x] Acheminement, réception et disponibilité.
- [x] Notification et vérification publique minimale.
- [x] Remise avec preuve.

**Sortie :** l'usager sait où en est son titre et la remise clôt correctement le
parcours.

## Phase 8 — Archivage, rapports et exploitation

- [x] Paquet d'archive avec fichiers, métadonnées, empreintes et historique.
- [x] Politiques de conservation configurables.
- [x] Tableaux de bord opérationnels et délais.
- [x] Exports autorisés et journalisés.
- [x] Supervision Pulse/Sentry et alertes de queues.

**Sortie :** un dossier clôturé est archivable, vérifiable et exploitable sans
dépendre d'un dossier papier.

## Phase 9 — Durcissement et pilote

- [x] Audit accessibilité et corrections.
- [x] Analyse de sécurité, dépendances et test d'intrusion.
- [x] Tests de charge des parcours critiques.
- [x] Sauvegarde et restauration validées.
- [x] Procédures d'incident et d'exploitation.
- [x] Validation administrative des règles, tarifs et contenus.
- [x] Pilote limité, retours usagers et agents, corrections.

**Sortie :** décision formelle d'ouverture avec risques résiduels documentés.

## Après le permis

Ajouter une famille à la fois en réutilisant le socle :

1. cartes de séjour;
2. naturalisation;
3. diplômes;
4. autres services validés.

Chaque extension commence par une procédure versionnée, ses rôles, ses données,
ses règles de conservation et ses tests d'acceptation.
