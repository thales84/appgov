# Sécurité, confidentialité et données

## Authentification usager

- E-mail unique et vérifié avant tout dépôt.
- Mot de passe haché avec Argon2id; jamais stocké ou journalisé en clair.
- Réinitialisation par jeton temporaire à usage unique.
- Rotation de session après connexion et action sensible.
- Cookies `Secure`, `HttpOnly` et `SameSite` en production.
- Limitation des tentatives par compte et adresse réseau.
- Notification lors du changement d'e-mail ou de mot de passe.

Le niveau de compte reste distinct du niveau d'identité :

```text
unverified → email_verified → identity_declared → identity_checked
```

## Accès agent

- Portail et authentification séparés des usagers.
- Second facteur obligatoire.
- Principe du moindre privilège.
- Rôles métier séparés : accueil, instructeur, examinateur, finance, production,
  remise, superviseur, administrateur de catalogue et auditeur.
- Habilitations limitées par organisation, unité, territoire et procédure.
- Les rôles incompatibles ne peuvent pas valider leurs propres opérations.

## Autorisation

Chaque lecture et mutation passe par une Policy. Masquer un bouton ne remplace
jamais une autorisation serveur. Tester explicitement :

- propriétaire du dossier;
- mandat ou qualité de représentant;
- organisation et unité de l'agent;
- territoire autorisé;
- rôle et permission;
- état permettant l'action;
- séparation des fonctions.

## Documents

- Stockage objet privé et chiffrement au repos.
- Téléchargement par URL courte signée après autorisation.
- Contrôle extension, MIME réel, taille et contenu.
- Renommage serveur; ne jamais utiliser le nom fourni comme chemin.
- Empreinte SHA-256, analyse antivirus et quarantaine.
- Prévisualisation isolée; jamais d'exécution de contenu actif.
- Journaliser dépôt, consultation sensible, remplacement et suppression légale.

## Application

- Protection CSRF des sessions web.
- Échappement par défaut; aucun HTML non fiable.
- CSP stricte, en-têtes de sécurité et HTTPS obligatoire.
- Validation serveur de toutes les entrées.
- Requêtes paramétrées via Eloquent ou Query Builder.
- Limitation de débit sur connexion, vérification publique, upload et callbacks.
- Messages d'erreur sans détails techniques ni existence d'un autre compte.

## Paiements

- Vérifier signature, montant, devise, bénéficiaire et référence du callback.
- Conserver la charge brute de preuve de façon protégée si le contrat l'exige.
- Utiliser une clé d'idempotence.
- Rapprocher les paiements avant de débloquer une étape critique.
- Une correction manuelle exige permission, motif, double contrôle et audit.
- Ne jamais stocker de données de carte bancaire.

## Audit

L'audit contient acteur, action, cible, date UTC, adresse technique pertinente,
ancienne et nouvelle valeur autorisées, motif et identifiant de corrélation.

Ne pas placer dans l'audit :

- mot de passe ou jeton;
- document complet;
- donnée biométrique brute;
- secret fournisseur;
- information personnelle non nécessaire.

Les audits sont consultables par des rôles dédiés et protégés de l'altération.

## Cycle de vie

- Définir une durée de conservation par catégorie documentaire.
- Bloquer la suppression lorsqu'un recours, litige ou contrôle est actif.
- Archiver avec métadonnées, empreintes, signatures et historique.
- Détruire de manière contrôlée à l'échéance autorisée.
- Documenter accès, rectification, export et incident de données.

Les durées et bases juridiques doivent être validées par les autorités
camerounaises avant la production.

## Exploitation

- Secrets dans un gestionnaire dédié.
- Journaux centralisés et alertes Sentry sans données sensibles.
- Sauvegardes chiffrées, rétention séparée et restauration testée.
- Dépendances corrigées et analysées régulièrement.
- Revue de sécurité avant pilote puis avant ouverture nationale.
- Procédure documentée de détection, confinement, correction et notification
  d'incident.

