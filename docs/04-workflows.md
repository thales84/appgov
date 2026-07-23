# Workflows et états

## Cycle public d'un dossier

Les états de haut niveau sont stables :

```text
draft
→ submitted
→ under_review
↔ correction_requested
→ processing
→ decision_pending
→ approved | rejected
→ in_production
→ available
→ delivered
→ closed
```

Des états facultatifs tels que `awaiting_payment`, `scheduled`, `cancelled` ou
`on_hold` sont appliqués par le moteur de procédure. Toutes les démarches
n'utilisent pas toutes les étapes.

## Règles de transition

Une transition :

1. vérifie l'état actuel;
2. vérifie les permissions et la séparation des fonctions;
3. vérifie les préconditions de la version de procédure;
4. enregistre motif et données utiles;
5. change l'état dans une transaction;
6. crée un événement de dossier et une entrée d'audit;
7. publie les effets asynchrones via l'outbox;
8. calcule la prochaine action visible.

Une transition échoue entièrement si une étape transactionnelle échoue.

## Corrections

- L'agent sélectionne les champs ou pièces à corriger et donne un motif clair.
- Le dossier devient `correction_requested`.
- Seules les zones autorisées redeviennent modifiables.
- L'usager soumet la correction; l'ancienne version reste traçable.
- Le dossier retourne dans la file d'instruction compétente.

## Paiement

Le dossier ne devient pas « payé ». Une facture et un paiement ont leurs propres
états.

```text
invoice issued
→ transaction initiated
→ provider callback received
→ callback verified
→ payment reconciled
→ workflow condition satisfied
```

Un callback répété avec la même clé ne crée jamais deux paiements.

## Examen

```text
eligible
→ scheduled
→ present | absent
→ result_recorded
→ result_validated
→ passed | failed | cancelled
```

Une correction de résultat exige une permission renforcée, un motif et la
conservation de la valeur précédente.

## Production et remise

```text
approved
→ queued_for_production
→ produced
→ quality_checked
→ dispatched
→ received_at_location
→ available
→ delivered
```

La remise enregistre l'agent, la date, le lieu et une preuve autorisée.

## Vue usager

Pour chaque dossier, afficher :

- libellé compréhensible de l'état;
- date du dernier changement;
- étape actuelle;
- prochaine action et échéance éventuelle;
- pièces ou paiements concernés;
- centre ou service responsable;
- historique utile;
- canal d'assistance.

La page publique avec référence n'affiche qu'un état minimal. Les résultats,
motifs détaillés et données personnelles exigent une connexion autorisée.

## Numéro de suivi

Format indicatif :

```text
CM-PDC-2026-7K9M4X-Q
```

Il contient un code service, une année, une partie aléatoire et un caractère de
contrôle. Il ne contient ni nom, date de naissance, région précise ni identifiant
séquentiel.

