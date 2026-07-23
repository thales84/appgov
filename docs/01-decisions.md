# Décisions actées

Ce fichier évite aux agents de rediscuter les fondations à chaque tâche. Toute
modification importante ajoute une décision datée avec son motif.

| ID | Décision | Statut |
|---|---|---|
| ADR-001 | Utiliser PHP 8.3 et Laravel 12. | Adoptée |
| ADR-002 | Utiliser Vue 3.5, Inertia 2, Vite 7 et Tailwind 3.4. | Adoptée |
| ADR-003 | Utiliser MySQL 8 et Redis. | Adoptée |
| ADR-004 | Construire un monolithe modulaire avant d'envisager des microservices. | Adoptée |
| ADR-005 | Authentifier les usagers par e-mail et mot de passe avec vérification de l'e-mail. | Adoptée |
| ADR-006 | Séparer les accès usager et agent; le second facteur est obligatoire pour les agents. | Adoptée |
| ADR-007 | Utiliser une base partagée avec cloisonnement par organisation, unité, territoire et permissions, sans `school_id`. | Adoptée |
| ADR-008 | Le permis de conduire est le premier parcours vertical. | Adoptée |
| ADR-009 | Toutes les procédures et tous les tarifs sont configurables, versionnés et publiés après validation. | Adoptée |
| ADR-010 | Les fichiers sont conservés dans un stockage objet privé, pas dans MySQL. | Adoptée |
| ADR-011 | Le bleu est la couleur dominante; le rouge est l'accent civique et critique. | Adoptée |
| ADR-012 | Chaque famille de service possède une couleur secondaire stable, distincte des couleurs de statut. | Adoptée |
| ADR-013 | Le français est la langue par défaut et l'anglais est livré dès le MVP. | Adoptée |
| ADR-014 | Les données internes utilisent des clés techniques; les références publiques sont opaques et non prévisibles. | Adoptée |
| ADR-015 | Le code est en anglais et les contenus visibles sont traduits. | Adoptée |
| ADR-016 | Auto-héberger Archivo, Source Sans 3 et IBM Plex Mono avec les paquets Fontsource. | Adoptée |
| ADR-017 | Utiliser Laravel Fortify 1.37 pour l’authentification web sans imposer son interface; les passkeys restent désactivées tant que leur besoin n’est pas validé. | Adoptée |
| ADR-018 | Conserver une table `users` commune avec un type de compte `citizen` ou `agent`, tout en séparant strictement les portails, routes et contrôles serveur. | Adoptée |
| ADR-019 | Autoriser l’auto-inscription uniquement aux usagers; un agent est invité par une administration, reçoit un lien temporaire pour définir son mot de passe et doit activer le TOTP avant tout accès métier. | Adoptée |
| ADR-020 | Rattacher chaque service à un organisme responsable et séparer les permissions d’édition, de soumission, de publication et de retrait du catalogue; l’auteur d’une version ne peut pas la publier lui-même. | Adoptée |
| ADR-021 | Créer un brouillon de dossier idempotent dès la phase catalogue afin de prouver qu’une version publiée accepte de nouveaux dossiers; le formulaire dynamique, le dépôt et la référence de suivi restent en phase 3. | Adoptée |

## Décisions encore ouvertes

- Hébergement gouvernemental exact et topologie de production.
- Prestataire d'envoi d'e-mails et éventuel canal SMS.
- Fournisseur de paiement et contrat de rapprochement.
- Solution antivirus des documents.
- Système d'archivage électronique à valeur probante.
- Référentiel officiel des personnes, territoires et organismes.
- Autorité habilitée à publier chaque procédure et chaque tarif.
- Outil E2E navigateur définitif si les tests Pest ne couvrent pas le besoin.

Une décision ouverte doit être isolée derrière une interface ou un adaptateur.
Elle ne doit pas bloquer les modules indépendants.
