# Design system

## Direction

L'interface exprime une administration moderne : claire, stable, digne et
accessible. Elle évite les dégradés décoratifs, les effets de verre, les cartes
partout et les animations gratuites.

Le bleu structure l'expérience. Le rouge apporte une marque civique forte et
signale les actions critiques. Les couleurs de service orientent sans modifier
les règles sémantiques.

## Palette de marque

| Jeton | Couleur | Usage |
|---|---|---|
| `brand-blue-900` | `#0B3B75` | en-têtes, navigation, texte institutionnel |
| `brand-blue-600` | `#155EEF` | action principale, liens, focus |
| `brand-red-700` | `#B42318` | accent de marque, rejet, danger |
| `ink-900` | `#172033` | texte principal |
| `cloud-50` | `#F4F7FB` | arrière-plan |
| `white` | `#FFFFFF` | surfaces |

Le bouton principal est bleu. Le rouge n'est pas utilisé pour une action
positive ordinaire.

## Couleurs par famille de service

| Service | Couleur | Exemples |
|---|---|---|
| Mobilité | `#2563EB` | permis de conduire |
| Séjour et immigration | `#7C3AED` | carte de séjour |
| Nationalité | `#A15C00` | naturalisation |
| Éducation | `#047857` | diplôme, authentification |
| État civil | `#BE185D` | actes et certificats |
| Activité économique | `#0F766E` | autorisations et licences |

La couleur de service apparaît dans l'icône, un filet, un repère ou l'en-tête.
Elle ne recolore pas toute la page et ne remplace jamais les états.

## Couleurs d'état

| État | Couleur | Icône indicative |
|---|---|---|
| Brouillon | `#64748B` | crayon |
| Déposé | `#155EEF` | document envoyé |
| En instruction | `#A15C00` | loupe |
| Action requise | `#C2410C` | point d'exclamation |
| Approuvé | `#15803D` | coche |
| Rejeté | `#B42318` | croix |
| En production | `#6D28D9` | engrenage |
| Disponible | `#0F766E` | boîte ouverte |
| Remis | `#0B3B75` | main ou reçu |

Ne jamais transmettre une information uniquement par la couleur. Toujours
associer libellé, icône et phrase explicative.

## Typographie

- Titres : **Archivo**, poids 600 à 700.
- Texte et interface : **Source Sans 3**, poids 400 à 600.
- Références, montants tabulaires et données : **IBM Plex Mono**.

Les fontes sont auto-hébergées en WOFF2 avec leurs licences. Prévoir une pile de
secours système. Taille minimale du corps : 16 px.

## Signature visuelle : le rail du dossier

La fiche dossier est reconnaissable par un rail fonctionnel :

```text
● Dossier déposé        12 juillet
│
● Pièces contrôlées     14 juillet
│
◉ Examen en cours       étape actuelle
│
○ Décision              prochaine étape
│
○ Production
```

La marque actuelle est plus forte, la prochaine action est écrite en clair et
les étapes futures restent sobres. Sur mobile, le rail devient vertical; sur
grand écran, il peut devenir horizontal dans l'en-tête du dossier.

Un cartouche compact affiche la référence en police mono, l'administration
responsable et le dernier changement. Ce cartouche évoque un registre officiel
sans imiter un formulaire papier.

## Mise en page

- Portail public : recherche et catégories avant tout contenu promotionnel.
- Espace usager : largeur lisible, tâches prioritaires et dossiers récents.
- Back-office : densité plus élevée, filtres persistants et tableaux paginés.
- Espacement sur une grille de 4 px.
- Rayons contenus : 6 à 10 px; pas de pilules partout.
- Ombres rares; préférer bordures, contraste de surface et espacement.
- Une seule action principale visuelle par zone.

## Composants obligatoires

- `AppShell`, `PublicHeader`, `AgentSidebar`;
- `ServiceBadge`, `StatusBadge`;
- `ApplicationRail`, `NextActionCard`;
- `DataTable`, `FilterBar`, `Pagination`;
- `FormField`, `FileUploader`, `DocumentReview`;
- `EmptyState`, `ErrorState`, `PermissionDenied`;
- `ConfirmDialog`, `Toast`, `AuditTimeline`;
- `Money`, `PublicReference`, `DateTime`.

Tous les composants couvrent focus, désactivation, chargement et erreur.

## Rédaction

- Utiliser des verbes directs : « Enregistrer le brouillon », « Déposer le
  dossier », « Demander une correction ».
- Garder le même nom d'action dans le bouton et le message de succès.
- Expliquer une erreur et la façon de la corriger.
- Ne pas employer le jargon interne dans l'espace usager.
- Afficher les dates complètes et le fuseau lorsque l'ambiguïté est possible.

## Accessibilité et mouvement

- Cible minimale WCAG 2.2 AA, inspirée du RGAA.
- Focus clavier visible de 3 px.
- Zones tactiles d'au moins 44 × 44 px.
- Labels persistants; un placeholder n'est jamais le seul label.
- Messages d'erreur liés au champ et résumé en haut du formulaire.
- Respect de `prefers-reduced-motion`.
- Animations limitées à 150–200 ms pour expliquer un changement d'état.
- Aucun contenu critique caché derrière un survol.

