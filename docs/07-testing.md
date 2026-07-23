# Tests et qualité

## Principe

Les tests suivent le risque métier. Une page qui s'affiche ne suffit pas : il
faut prouver l'autorisation, la transition, l'audit et l'effet attendu.

## Niveaux

- **Unitaires** : règles pures, références, montants et préconditions.
- **Feature Pest** : routes, validation, Policies, transactions et base.
- **Architecture Pest** : frontières de modules et conventions.
- **Intégration** : Redis, stockage, e-mail, paiement et outbox avec faux
  fournisseurs.
- **Parcours** : principales actions usager et agent dans un navigateur.
- **Manuels ciblés** : accessibilité, responsive, impression et connexions
  faibles.

## Cas critiques obligatoires

- inscription, vérification d'e-mail et récupération du mot de passe;
- interdiction d'accès au dossier d'un autre usager;
- cloisonnement entre organismes et territoires;
- chaque transition autorisée et interdite;
- demande et resoumission d'une correction;
- version immuable d'une procédure publiée;
- facture conservant le tarif d'origine;
- callback de paiement répété sans double encaissement;
- document invalide ou infecté mis en quarantaine;
- modification de résultat auditée;
- disponibilité et remise d'un titre;
- référence publique ne révélant aucune donnée personnelle;
- traduction française et anglaise des parcours essentiels.

## Commandes de contrôle

Une fois le projet initialisé :

```bash
composer test
./vendor/bin/pint --test
npm run build
```

Ajouter les contrôles de sécurité et frontend décidés dans le projet. Les tests
utilisent une base dédiée et ne dépendent jamais de l'ordre d'exécution.

## Definition of Done

Une tâche est terminée lorsque :

- les critères d'acceptation sont satisfaits;
- les tests nouveaux et existants passent;
- les autorisations refusent les accès hors périmètre;
- l'action sensible produit un audit;
- les textes existent en français et en anglais;
- chargement, vide et erreurs sont traités;
- mobile et clavier sont vérifiés;
- aucune information sensible n'apparaît dans les logs;
- migrations et variables d'environnement sont documentées;
- la documentation reflète le comportement final.

Pour les transitions, paiements, décisions et habilitations, tous les chemins
connus doivent être testés; un pourcentage global de couverture ne remplace pas
cette exigence.

