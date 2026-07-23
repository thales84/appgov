# Modèle métier

## Agrégats principaux

### Identité et organisation

- `users` : connexion et état du compte.
- `citizen_profiles` : identité déclarée et niveau de vérification.
- `organizations` : ministère, organisme ou partenaire public.
- `administrative_units` : direction, délégation, centre ou guichet.
- `territories` : région, département et commune.
- rôles et permissions : habilitations techniques et métier.

L'e-mail est un identifiant de connexion modifiable, jamais la clé principale.

### Catalogue

- `service_categories` : mobilité, séjour, nationalité, éducation.
- `services` : permis de conduire, carte de séjour, naturalisation.
- `procedure_versions` : version immuable et période d'effet.
- `procedure_steps` : étapes ordonnées et règles de passage.
- `form_fields` : champs configurables.
- `document_requirements` : pièces obligatoires ou conditionnelles.
- `fee_schedules` : tarifs versionnés et base réglementaire.

Une version possède les états `draft`, `under_review`, `published` ou `retired`.
Seule une version publiée peut recevoir de nouveaux dossiers.

### Dossier

- `applications` : demande d'un usager pour une version de procédure.
- `application_participants` : demandeur, bénéficiaire, parent ou mandataire.
- `application_steps` : exécution des étapes.
- `application_events` : historique fonctionnel.
- `assignments` : unité et agent responsables.
- `messages` : échanges sécurisés.

Le dossier conserve un instantané des libellés et règles indispensables à sa
compréhension future.

### Documents

- `submitted_documents` : métadonnées, empreinte et emplacement privé.
- `document_reviews` : résultat du contrôle, auteur, date et motif.
- `generated_documents` : accusés, reçus et décisions générées.

Un document fourni a un état distinct : `pending`, `valid`, `invalid`,
`expired`, `replaced` ou `quarantined`.

### Rendez-vous et examens

- `locations` : centres et capacités.
- `appointment_slots` : plages disponibles.
- `appointments` : réservation et présence.
- `exam_types` : théorie, pratique ou autre épreuve configurable.
- `exam_sessions` : session organisée dans un centre.
- `exam_attempts` : tentative, résultat, décision et examinateur.

Les règles de réussite appartiennent à la version de procédure.

### Finance

- `invoices` : somme exigible pour un dossier.
- `invoice_lines` : instantané des tarifs appliqués.
- `payment_transactions` : tentative auprès d'un fournisseur.
- `payments` : règlement confirmé et rapproché.
- `refunds` : remboursement autorisé et tracé.

États de paiement : `pending`, `processing`, `paid`, `failed`, `cancelled`,
`refunded` et `partially_refunded`.

### Décision et titre

- `decisions` : acceptation, rejet, ajournement ou classement.
- `issued_titles` : permis ou document attribué.
- `production_batches` : lots de production.
- `title_movements` : production, transfert, réception et remise.
- `delivery_proofs` : preuve de retrait ou remise.

Le numéro du dossier, la référence de paiement et le numéro du titre sont
toujours distincts.

## Conventions

- Tables et colonnes en anglais, `snake_case`.
- Codes stables en anglais; libellés en français et anglais.
- Dates stockées en UTC.
- Argent en entier `amount_minor` et devise `currency`.
- Données centrales relationnelles; JSON seulement pour schémas, règles et
  instantanés versionnés.
- Aucune donnée personnelle dans une référence publique.
- Pas de suppression physique des dossiers, décisions, paiements ou audits.

## Formulaires configurables

Utiliser un modèle hybride :

- identité, affectation, argent et états dans des colonnes structurées;
- définition des champs dans `form_fields`;
- réponses variables dans un document JSON validé par la version du formulaire;
- copie des valeurs nécessaires au reporting dans des colonnes dédiées.

Ne pas créer une table EAV universelle difficile à valider et interroger.

## Permis de conduire

Le module ajoute :

- `driving_license_categories`;
- `driving_schools`, si elles participent au parcours;
- `medical_assessments`;
- types d'épreuves et tentatives;
- résultat final;
- données nécessaires à la production du permis.

Les catégories et critères exacts restent des données administratives à valider,
pas des constantes supposées.

