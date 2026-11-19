---
name: menupro-design
description: Système de design du site public MenuPro (vitrine, tarifs, FAQ, inscription, connexion) et règles anti « site amateur ». À charger avant toute modification visuelle des pages publiques MenuPro (classes vt-).
---

# Design MenuPro (vitrine)

MenuPro est un **SaaS** pour commerçants ivoiriens (maquis, restaurants, stands, boutiques) des villes de l'intérieur.
Lecteur type : un patron sur un Android d'entrée de gamme, souvent dehors, connexion lente.
Mission de la page : lui faire **créer son commerce** (essai gratuit), en montrant le **produit**, pas des promesses.

## Idée signature : le ticket de commande
L'objet central de la journée d'un commerçant MenuPro est la commande qui arrive toute seule (fini les appels).
Le visuel fort du site est donc une **file de tickets de commande** (bord perforé, opérateur Mobile Money, montant, « Accepter »).
Une seule animation au chargement (les tickets arrivent), rien d'autre ne bouge. `prefers-reduced-motion` respecté.

## Jetons
| Rôle | Valeur |
|---|---|
| Orange marque (boutons, signal « nouveau ») | `--color-mp-orange` #F97316, texte **noir** dessus |
| Orange lisible sur blanc (liens) | `--color-mp-ink` #C2410C |
| Encre | `--color-mp-black` #000 |
| Texte secondaire | `--color-mp-text` #45403B |
| Surface de section | `--vt-surface` #F5F5F4 |
| Filet | `--color-mp-line` #E8DFD6 |
| Payé / ouvert | #15803D |

Typo : **Bricolage Grotesque** (titres, gros chiffres, `vt-display`) + **DM Sans** (texte). Pas d'autre police.
Échelle : h1 clamp(2.5rem→5rem), h2 clamp(2rem→3.25rem), corps 17 px, lignes < 70 caractères.
Rayons : 28 px pour les grands panneaux, 16 px cartes, 12 px boutons, pilules seulement pour les statuts.

## Interdits (ça fait amateur)
- Ombres décalées « néo-brutalistes » (`shadow-[6px_6px_0…]`), contours noirs 2 px partout.
- Cartes toutes identiques encadrées ; icônes décoratives au trait sans fonction.
- Étiquettes en MAJUSCULES, sur-titres au-dessus de chaque titre, « → » dans les boutons.
- Un seul mot coloré dans un titre ; dégradés décoratifs.
- Numéros 1/2/3 si le contenu n'est pas une vraie séquence.
- Texte gris sur fond noir sans vérifier le contraste (≥ 4,5:1).
- Photos de banque d'images hétérogènes : même cadrage (4/3), mêmes rayons, sujet clair.
- Fausses promesses (N°1, 99,9 %, faux témoignages).

## Contrôle avant livraison
1. Capture mobile 390 px et bureau 1440 px (`chrome --headless=new --screenshot`), sur la prod si la base locale est éteinte.
2. Critiquer : un seul élément mémorable ? retirer un accessoire.
3. `php artisan test --filter=Vitrine` puis `npm run build`.
