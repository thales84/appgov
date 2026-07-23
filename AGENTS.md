# Instructions pour les agents IA

Ces instructions s'appliquent à tout le dépôt.

## Mission

Construire AppGov comme une plateforme publique fiable, configurable et
auditable. Livrer une tranche verticale fonctionnelle à la fois, du portail
public jusqu'au traitement agent, sans inventer de règle administrative.

## Lecture obligatoire

Avant toute modification :

1. Lire `README.md`.
2. Lire `docs/01-decisions.md`.
3. Identifier la première phase incomplète dans `docs/08-roadmap.md`.
4. Lire les documents métier, sécurité, UI et tests concernés.
5. Inspecter le code et les changements existants avant de proposer une action.

Priorité des sources :

1. Demande explicite de l'utilisateur.
2. `docs/01-decisions.md`.
3. Règles métier validées dans `docs/03-domain-model.md` et
   `docs/04-workflows.md`.
4. Autres documents du dépôt.

En cas de contradiction, ne pas deviner. Documenter le conflit et demander une
décision si elle change le produit, la sécurité ou une règle légale.

## Règles de travail

- Le code, les classes, tables, routes et tests sont nommés en anglais.
- Les textes visibles passent par `vue-i18n`; aucun texte métier n'est dispersé
  dans les composants.
- Le français est la langue par défaut et chaque nouveau texte possède une
  traduction anglaise.
- Les contrôleurs restent minces. La logique vit dans des actions ou services
  métier testables.
- Toute entrée HTTP passe par une validation dédiée.
- Toute action est protégée par une Policy ou une permission explicite.
- Toute transition de dossier passe par le service de workflow. Ne jamais
  modifier directement un statut depuis un contrôleur ou un composant.
- Une opération touchant plusieurs tables utilise une transaction.
- Les tâches asynchrones et callbacks externes sont idempotents.
- Les événements externes fiables utilisent une transactional outbox.
- Les références publiques utilisent un identifiant opaque; aucun identifiant
  séquentiel interne n'est exposé.
- Les dates sont stockées en UTC et affichées dans le fuseau
  `Europe/Paris`.
- Les montants sont des entiers avec un code devise ISO 4217. Aucun calcul
  financier en nombre flottant.
- Les documents sont privés. La base ne conserve que leurs métadonnées et leur
  emplacement dans le stockage objet.
- Aucun secret, mot de passe, jeton, document réel ou donnée personnelle de
  production ne doit entrer dans Git, les logs ou les fixtures.

## Règles métier non négociables

- Ne jamais coder en dur un tarif officiel, une liste de pièces ou un délai
  réglementaire. Utiliser une définition versionnée.
- Une version publiée est immuable. Une évolution crée une nouvelle version
  avec une date d'effet.
- Une facture conserve un instantané du tarif appliqué.
- Les états de paiement, de dossier, de document et de production restent
  séparés.
- Un dossier, un paiement, une décision ou une entrée d'audit ne sont jamais
  supprimés physiquement par une fonction normale.
- Les données de démonstration portent clairement la mention `DEMO` et ne sont
  jamais présentées comme des règles françaises officielles.
- Les motifs détaillés, résultats et données personnelles ne sont visibles
  qu'après authentification et autorisation.

## Méthode d'implémentation

Pour chaque tâche :

1. Reformuler le résultat attendu et les critères d'acceptation.
2. Repérer le module propriétaire et ses dépendances autorisées.
3. Écrire ou mettre à jour les tests du comportement critique.
4. Implémenter la plus petite tranche verticale complète.
5. Vérifier permissions, audit, traductions, responsive et états vides/erreurs.
6. Exécuter les contrôles de `docs/07-testing.md`.
7. Mettre à jour la documentation si une décision ou un contrat change.
8. Cocher une phase de la feuille de route uniquement lorsque tous ses critères
   de sortie sont satisfaits.

Ne pas lancer plusieurs modules incomplets en parallèle. Terminer et stabiliser
la tranche active avant d'élargir le périmètre.

## Base de données

- Les migrations sont additives et réversibles tant que possible.
- Ne jamais modifier une migration déjà utilisée dans un environnement partagé.
- Définir clés étrangères, index, contraintes d'unicité et règles de nullité.
- Éviter l'EAV générique. Utiliser des colonnes relationnelles pour les données
  centrales et du JSON versionné uniquement pour les formulaires configurables,
  règles et instantanés.
- Les requêtes agent sont toujours limitées par organisation, unité
  administrative, territoire et habilitation.
- Vérifier les problèmes N+1 et paginer toutes les listes potentiellement
  volumineuses.

## Frontend

- Respecter `docs/05-design-system.md`; ne pas inventer de nouvelles couleurs.
- Le bleu reste dominant. Le rouge n'est jamais le seul signal d'une erreur.
- La couleur d'un service ne remplace pas les couleurs sémantiques des statuts.
- Chaque statut contient un libellé, une icône et une explication.
- Tout écran couvre chargement, vide, succès, erreur et absence d'autorisation.
- Navigation clavier, focus visible, contraste AA et réduction des animations
  sont obligatoires.
- Optimiser les pages pour les téléphones et les connexions faibles.

## Dépendances et réutilisation

- Ne pas copier le code métier, `.env`, `vendor` ou `node_modules` de SchoolPro.
- Réutiliser uniquement la stack et les conventions techniques pertinentes.
- Toute nouvelle dépendance doit résoudre un besoin réel, être maintenue et être
  notée dans `docs/01-decisions.md`.
- Pour Tailwind 3.4, utiliser son intégration PostCSS cohérente; ne pas mélanger
  les plugins Tailwind 4 présents dans certains manifestes historiques.

## Fin de tâche

Le compte rendu doit indiquer :

- le résultat obtenu;
- les fichiers importants modifiés;
- les migrations ou variables d'environnement ajoutées;
- les tests exécutés et leur résultat;
- les risques ou validations administratives restant ouverts.

Ne pas déclarer une tâche terminée si un contrôle critique est ignoré.

