# Vidéo motion design MenuPro — Ivoire Tech Forum 2026

Durée cible : 2 min 25 · Format : 16:9, 1920×1080, 30 i/s · Langue : français
Style : explainer SaaS « premium » (façon Stripe / Linear / Notion) — fonds sombres profonds, accents orange MenuPro, maquettes 3D de téléphones et d'ordinateurs, typographie cinétique, transitions en « match cut ».

---

## 1. Direction artistique

| Élément | Choix |
|---|---|
| Couleurs | Fond `#0B0B0F` (presque noir) → dégradé radial `#1A1208` · Accent `#F97316` (orange MenuPro) · Secondaire `#FDBA74` · Blanc cassé `#F5F5F4` pour le texte · Vert succès `#22C55E` pour les validations |
| Typo | Titres : **Poppins ExtraBold** (ou Clash Display) · Texte : **Inter Medium** · Chiffres : **Inter Tight Bold**, chasse fixe |
| Courbes d'animation | Toujours en *ease* fort : Easy Ease avec influence 80–90 % (graph editor « S » prononcé), jamais linéaire. Durée type d'une entrée : 12–18 images. |
| Mouvements de caméra | Caméra 3D lente et continue (push-in de 5–8 % par plan), léger *parallax* entre arrière-plan, maquette et textes |
| Profondeur | Ombres portées longues et douces sous les appareils, léger flou de profondeur sur l'arrière-plan, grain fin (2–3 %) pour un rendu « film » |
| Transitions | Match cuts (un élément devient le suivant), *whip pan* avec flou directionnel, *mask reveal* en cercle depuis un bouton cliqué |
| Icônes | Style ligne 2 px arrondies (Lucide / Phosphor), animées par *trim paths* |
| Sound design | Musique : corporate électro-afro douce, 100–110 BPM (chercher « afro corporate », « tech afrobeat » dans une banque libre de droits) · Bruitages : *whoosh* sur les transitions, *pop*/*tick* sur les apparitions d'UI, *ding* de notification, son de paiement validé |

**Règle d'or** : chaque écran montré doit être une **vraie capture de MenuPro** (voir section 4), placée dans une maquette d'appareil. Les animations habillent le produit, elles ne le remplacent pas.

---

## 2. Script plan par plan

> VO = voix off (texte exact à générer avec ElevenLabs, section 3). Les temps sont indicatifs ; caler les animations sur la voix une fois générée.

### Scène 1 — Le problème (0:00 – 0:22)

| Temps | Visuel & animation | VO |
|---|---|---|
| 0:00 – 0:03 | Noir. Une horloge en ligne fine se dessine (*trim path*), aiguilles qui tournent vite vers **12:00**. Texte cinétique : **« Midi. »** puis **« Daloa. »** (lettres qui montent une à une, *stagger* 2 images). Ambiance sonore : brouhaha de maquis en fond. | « Midi, dans un maquis de Daloa. » |
| 0:03 – 0:08 | Illustration flat d'un maquis vu de dessus : tables, clients représentés par des pastilles. Au-dessus de chaque table, une bulle « ⏳ » qui grossit. Une feuille de commande papier s'envole et se déchire. | « Les clients attendent. Les commandes se perdent. » |
| 0:08 – 0:12 | Un cahier de caisse s'ouvre, les chiffres sont raturés puis se brouillent en points d'interrogation qui tombent. | « Et le soir, impossible de savoir ce qu'on a vraiment gagné. » |
| 0:12 – 0:22 | Dézoom : la carte de la Côte d'Ivoire se dessine en lignes orange. Des dizaines de points (restaurants) s'allument partout… mais seul **Abidjan** est relié par des lignes lumineuses (livraison) ; Bouaké, Daloa, Korhogo, San-Pédro restent gris et isolés. | « En Côte d'Ivoire, des milliers de restaurants, de maquis et de vendeuses fonctionnent encore au cahier. Et hors d'Abidjan, commander en ligne… c'est presque impossible. » |

### Scène 2 — La révélation (0:22 – 0:32)

| Temps | Visuel & animation | VO |
|---|---|---|
| 0:22 – 0:26 | Tous les points gris de la carte se rassemblent en un seul point orange au centre (*magnetic pull*), qui pulse puis explose en lumière → **logo MenuPro** qui se construit (formes qui s'assemblent, rotation 3D légère, reflet qui balaie le logo). Bruitage : *whoosh* grave + *impact*. | « Voici MenuPro. » |
| 0:26 – 0:32 | Sous le logo, la phrase s'écrit mot par mot. Derrière, trois maquettes glissent en éventail et s'alignent en perspective : téléphone client, ordinateur restaurant, téléphone livreur (**captures C1, R1, L1**). | « La plateforme qui digitalise la restauration ivoirienne. Du stand de rue au restaurant d'hôtel. » |

### Scène 3 — Le client commande (0:32 – 0:55)

| Temps | Visuel & animation | VO |
|---|---|---|
| 0:32 – 0:38 | Match cut : le logo devient un **QR code** posé sur une table (chevalet 3D). Un téléphone entre dans le cadre, le QR est « aspiré » dans l'écran ; ligne de scan laser qui balaie. | « Sur la table, un simple QR code. Le client scanne, » |
| 0:38 – 0:45 | Gros plan téléphone : **capture C2** (menu du restaurant) qui défile en douceur (animer la capture en *position Y*, avec masque dans l'écran). Les cartes de plats « pop » une à une. Un doigt (cercle blanc semi-transparent) tape « Ajouter ». Badge panier qui rebondit **+1**. | « découvre le menu en photos, et commande directement depuis son téléphone. Sans application à installer. » |
| 0:45 – 0:55 | **Capture C3** (choix du paiement). Trois logos s'alignent et rebondissent : Wave, Mobile Money, Espèces. Tap sur Wave → cercle de chargement → coche verte qui se dessine + bruitage « paiement validé ». | « Il paie comme il veut : Wave, Mobile Money… ou en espèces. » |

### Scène 4 — En cuisine (0:55 – 1:08)

| Temps | Visuel & animation | VO |
|---|---|---|
| 0:55 – 1:00 | La coche verte s'envole en particule lumineuse qui traverse l'écran (trajectoire courbe, traînée orange) et entre dans une tablette posée en cuisine. | « En une seconde, la commande arrive en cuisine. » |
| 1:00 – 1:08 | **Capture R2** (écran cuisine), ou R2b (Kanban des commandes). Une nouvelle carte de commande glisse dans la colonne « Nouvelles » avec un *flash* orange + son de notification. La carte se déplace ensuite vers « En préparation » puis « Prête » (animer des duplicatas de la carte par-dessus la capture). | « Le cuisinier la voit, la prépare, la valide. Fini les erreurs, fini l'attente. » |

### Scène 5 — La gestion pour le patron (1:08 – 1:30)

| Temps | Visuel & animation | VO |
|---|---|---|
| 1:08 – 1:12 | Transition *whip pan* vers un ordinateur portable 3D. | « Et pendant ce temps, MenuPro travaille pour le gérant. » |
| 1:12 – 1:18 | **Capture R3** (stock / ingrédients). Une jauge d'ingrédient (ex. « Poulet ») descend d'un cran en *ease* ; un petit « −1 » flotte et disparaît. | « Chaque vente déduit automatiquement les ingrédients du stock. La caisse est claire. » |
| 1:18 – 1:30 | **Capture R4** (tableau de bord / statistiques). Les chiffres clés comptent de 0 à leur valeur (*counter* animé), le graphique se dessine de gauche à droite, une étiquette « ⭐ Plat le plus rentable » vient se poser sur **capture R5** (rentabilité par plat). | « Et le tableau de bord montre, en temps réel, le chiffre du jour et le plat le plus rentable. » |

### Scène 6 — La livraison (1:30 – 1:55)

| Temps | Visuel & animation | VO |
|---|---|---|
| 1:30 – 1:38 | Carte stylisée de la ville (rues en lignes fines, fond sombre). Le restaurant = pin orange. Autour, des icônes de motos (livreurs). Un cercle de recherche part du restaurant et s'agrandit (*radar pulse*) ; il touche la moto la plus proche qui s'illumine. | « Le client préfère se faire livrer ? MenuPro localise par GPS le livreur disponible le plus proche… » |
| 1:38 – 1:44 | Zoom dans le téléphone du livreur : **capture L2** (course disponible). Notification push qui tombe du haut avec vibration de l'appareil (secousse de 3 images). Tap sur « Accepter ». | « …et lui envoie la course instantanément. » |
| 1:44 – 1:55 | Écran partagé : à gauche, le trajet de la moto se dessine sur la carte (*trim path* animé le long des rues) ; à droite, **capture C4** (suivi de commande côté client) avec les étapes qui se cochent. Arrivée : maison qui s'allume + coche verte. Sur le téléphone livreur, un compteur de gains « +1 000 F » s'incrémente. | « Le client suit sa commande en direct, jusqu'à sa porte. Le livreur, lui, gagne un revenu. » |

### Scène 7 — Pensé pour la Côte d'Ivoire (1:55 – 2:05)

| Temps | Visuel & animation | VO |
|---|---|---|
| 1:55 – 2:05 | Un téléphone d'entrée de gamme au centre. Trois badges s'animent autour, en orbite puis se fixant : **« Aucune installation »**, **« Smartphone d'entrée de gamme »**, **« Connexion faible »** (icône de réseau 1 barre qui reste verte). | « Tout fonctionne dans le navigateur, sur un smartphone d'entrée de gamme, même avec une connexion faible. » |

### Scène 8 — Traction et vision (2:05 – 2:25)

| Temps | Visuel & animation | VO |
|---|---|---|
| 2:05 – 2:13 | Retour à la carte de la Côte d'Ivoire de la scène 1 — cette fois les villes s'allument **en orange** et se relient entre elles. Trois compteurs animés apparaissent : **[X] restaurants & stands · [X] livreurs · [X] commandes** (⚠️ vrais chiffres). | « MenuPro, c'est déjà des restaurants, des stands et des livreurs actifs, dans plusieurs villes de Côte d'Ivoire. » |
| 2:13 – 2:20 | Montage rapide (6 images par plan) de vignettes : une vendeuse de stand, un maquis, un restaurant d'hôtel, un livreur — chacune avec un téléphone MenuPro. | « Notre ambition : que chaque restaurant, chaque maquis, chaque vendeuse, partout en Côte d'Ivoire, ait accès aux mêmes outils que les grandes enseignes. » |
| 2:20 – 2:25 | Tout se rassemble vers le centre → logo MenuPro final, signature en dessous, puis **menupro.ci**. Musique qui se résout sur un accord final. | « MenuPro. Le numérique au service de la restauration ivoirienne. » |

---

## 3. Voix off — texte à coller dans ElevenLabs

Réglages conseillés : voix française masculine ou féminine, chaleureuse et posée (type « narrateur documentaire »), modèle **Eleven Multilingual v2** · Stability ≈ 45 % · Similarity ≈ 75 % · Style ≈ 20 % · Speaker boost activé.
Générer **scène par scène** (un paragraphe = un fichier) : plus facile à caler au montage et à regénérer si une phrase sonne mal. Les « … » créent une pause naturelle.

```
Midi, dans un maquis de Daloa. Les clients attendent. Les commandes se perdent. Et le soir, impossible de savoir ce qu'on a vraiment gagné.

En Côte d'Ivoire, des milliers de restaurants, de maquis et de vendeuses fonctionnent encore au cahier. Et hors d'Abidjan, commander en ligne… reste un luxe.

Voici Ménu Pro.

La plateforme qui digitalise la restauration ivoirienne. Du restaurant d'hôtel… jusqu'à la vendeuse de garba au coin de la rue, avec une offre pensée pour elle.

Sur la table, un simple Q-R code. Le client scanne, découvre le menu en photos, et commande directement depuis son téléphone. Sans application à installer.

Il paie comme il veut : Ouève, Mobile Money… ou en espèces.

En quelques secondes, la commande arrive en cuisine. Le cuisinier la voit, la prépare, la valide. Fini les erreurs, fini l'attente.

Et pendant ce temps, Ménu Pro travaille pour le gérant. Chaque plat préparé met à jour le stock automatiquement. La caisse est claire. Et le tableau de bord montre, en temps réel, le chiffre du jour et le plat le plus rentable.

Le client préfère se faire livrer ? Ménu Pro localise par G-P-S les livreurs disponibles les plus proches… et leur envoie la course en quelques instants.

Le client suit sa commande en direct, jusqu'à sa porte. Et le livreur, lui, gagne un revenu.

Tout fonctionne dans le navigateur, sur un smartphone d'entrée de gamme, même avec une connexion faible.

Aujourd'hui, Ménu Pro, c'est déjà [trente-cinq] restaurants et stands, [douze] livreurs et [plus de mille] commandes, dans [trois] villes de Côte d'Ivoire.

Notre prochaine étape : déployer Ménu Pro dans [dix] nouvelles villes, pour que chaque restaurant, chaque maquis, chaque vendeuse ait accès aux mêmes outils que les grandes enseignes.

Ménu Pro. Le numérique au service de la restauration ivoirienne.
```

**Remplacer chaque [crochet] par le vrai chiffre, écrit en lettres** (le jury peut vérifier). Sans chiffres : supprimer tout le paragraphe « Aujourd'hui… ».

Écritures phonétiques volontaires : « Ménu Pro » (MenuPro), « Q-R code », « Ouève » (Wave), « G-P-S ». À l'écran, garder l'orthographe normale.

**Fin face caméra — à dire vous-même (pas ElevenLabs), filmé au téléphone, 6–8 s, nom et titre en bas de l'écran :**

> « Je suis [Prénom Nom], fondateur de MenuPro. Nous cherchons des partenaires pour digitaliser toute la restauration ivoirienne. Rejoignez-nous ! »

---

## 4. Captures d'écran à préparer

Captures **propres** : un vrai restaurant avec de belles photos de plats, données réalistes (pas « test », pas de prix à 1 F), barre d'état du téléphone nette (batterie pleine, heure 12:00). Exporter en PNG pleine résolution. Pour les écrans qui défilent, faire un **enregistrement d'écran** de 5–10 s en plus de la capture.

| Code | Écran | Où | Appareil |
|---|---|---|---|
| C1 | Accueil de l'app client (liste des restaurants) | app.menupro.ci | Téléphone (portrait) |
| C2 | Menu d'un restaurant avec photos de plats (défilement) | menupro.ci/r/{slug-du-restaurant} | Téléphone |
| C3 | Choix du mode de paiement (Wave / Mobile Money / espèces) | étape paiement de la commande | Téléphone |
| C4 | Suivi de commande en direct (étapes) | page de suivi après commande | Téléphone |
| R1 | Tableau de bord restaurant | menupro.ci/dashboard | Ordinateur |
| R2 | Écran cuisine (commandes en temps réel) — idéal pour la scène 4 | menupro.ci/cuisine/{token} (lien généré dans le tableau de bord) | Tablette (paysage) |
| R2b | Commandes en Kanban (Nouvelles / En préparation / Prête) | menupro.ci/dashboard/commandes/kanban | Ordinateur |
| R3 | Stock des ingrédients | menupro.ci/dashboard/stock/ingredients | Ordinateur |
| R4 | Statistiques (chiffre d'affaires, graphique) | menupro.ci/dashboard/statistiques | Ordinateur |
| R5 | Rentabilité par plat | menupro.ci/dashboard/rentabilite | Ordinateur |
| R6 | (bonus) Caisse / POS | menupro.ci/dashboard/pos | Tablette |
| R7 | (bonus) QR codes des tables | menupro.ci/dashboard/qr-code | Ordinateur |
| L1 | Tableau de bord livreur (en ligne, gains) | driver.menupro.ci | Téléphone |
| L2 | Course disponible avec bouton Accepter | driver.menupro.ci → Courses | Téléphone |
| L3 | (bonus) Course active avec étapes | driver.menupro.ci → course en cours | Téléphone |
| V1 | (bonus) Photos réelles : un maquis, une vendeuse de stand, un livreur (avec leur accord) | terrain | Photo |

Astuce ordinateur : capturer en 1920×1080 avec le zoom navigateur à 110–125 % pour des textes lisibles dans la vidéo.

---

## 5. Fabrication

**Option pro — After Effects**
1. Maquettes : packs gratuits de *device mockups* 3D (iPhone/Android, MacBook) → remplacer l'écran par la capture (précomposition + *Corner Pin* ou calque 3D).
2. Plugins utiles : **Motion Tools Pro** (gratuit, alignements/easing), **Flow** (courbes d'animation), **Deep Glow** (lueurs orange), **Saber** (traînées lumineuses de la scène 4).
3. Typographie cinétique : animer par *Text Animator* (Position Y + Opacité, *range* par caractère, décalage 2 images).
4. Rendu : H.264, 1080p, 30 i/s, débit 15–20 Mb/s.

**Option rapide — Canva + CapCut**
1. Canva : modèles « App promo » / « SaaS explainer » en 16:9, cadres de téléphone, animations « Montée », « Fondu », « Pop ».
2. CapCut : assembler les scènes exportées de Canva, poser la voix ElevenLabs et la musique, ajouter les bruitages et le *keyframe* zoom sur les captures.

**Publication** : YouTube en « Non répertoriée » (le lien fonctionne sans autorisation et n'expire pas) → coller le lien dans la question 25.
