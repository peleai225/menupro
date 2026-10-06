# Scripts vidéo — Formation restaurateurs & vendeuses de stand MenuPro

**Objectif de ce document :** servir de base pour produire, avec une IA (Claude), une série de courtes vidéos tutorielles format WhatsApp (30 à 90 secondes chacune) qui apprennent à une restauratrice, un restaurateur ou une vendeuse de stand à utiliser MenuPro — depuis l'inscription jusqu'à sa première vente, puis une vidéo par nouvelle fonctionnalité au fil du temps.

**Public cible :** personnes pas forcément à l'aise avec la technologie — vocabulaire simple, phrases courtes, une seule action à la fois par vidéo. Priorité au plan **Stand** (5 000 F/mois, le plus simple — pas de livraison, pas de gestion de stock, pas de statistiques), avec des points "pour aller plus loin" quand une fonctionnalité existe seulement en plan supérieur.

**Comment utiliser ce fichier :** chaque épisode ci-dessous est un script prêt à donner à Claude pour générer la vidéo (narration + ce qui doit apparaître à l'écran). Fournissez aussi une capture d'écran ou un enregistrement de la page concernée en plus du texte.

**Vérifié dans le code de l'application le 2026-10-06** — les champs, boutons et étapes décrits correspondent à l'état réel de MenuPro à cette date. Si l'interface change, remettre ce fichier à jour avant de tourner une nouvelle vidéo.

---

## Sommaire des épisodes

| # | Titre | Durée visée | Thème |
|---|-------|:---:|-------|
| 0 | Bienvenue sur MenuPro | 60s | Présentation générale |
| 1 | S'inscrire | 90s | Création du compte |
| 2 | L'attente de validation | 45s | Pourquoi ça prend un peu de temps |
| 3 | Se connecter | 30s | Retour sur l'appli |
| 4 | Découvrir son tableau de bord | 60s | Vue d'ensemble |
| 5 | Créer ses catégories | 60s | Organiser le menu |
| 6 | Ajouter son premier plat | 90s | Le cœur du menu |
| 7 | Les options sur un plat | 60s | Tailles, suppléments |
| 8 | Activer / désactiver un plat | 30s | Rupture de stock express |
| 9 | Compléter les informations du commerce | 60s | Nom, logo, adresse |
| 10 | Générer son QR code | 60s | Avant de vendre |
| 11 | Recevoir sa première commande | 90s | Le moment clé |
| 12 | Faire avancer une commande | 60s | Le cycle de la commande |
| 13 | Utiliser la caisse (POS) | 90s | Vente sur place |
| 14 | Voir l'historique des commandes | 45s | Retrouver une vente |
| 15 | Gérer son abonnement | 60s | Voir / changer de plan |
| 16 | Besoin d'aide | 30s | Contacter le support |
| — | Modèle "nouvelle fonctionnalité" | 45s | Gabarit réutilisable |

---

## Épisode 0 — Bienvenue sur MenuPro

**Ce qu'on voit à l'écran :** page d'accueil publique MenuPro, puis un restaurant exemple avec son menu en ligne.

**Script :**
> « MenuPro, c'est votre commerce, 100% digital. Un menu que vos clients voient sur leur téléphone, des commandes qui arrivent directement chez vous, et un paiement Mobile Money sans prise de tête. Dans cette série de vidéos, on va voir ensemble, étape par étape, comment mettre votre commerce en ligne. »

**Message clé :** rassurer — "pas besoin d'être un expert en informatique."

---

## Épisode 1 — S'inscrire

**Ce qu'on voit à l'écran :** la page `/inscription`, remplie à l'écran étape par étape.

**Script :**
> « Pour commencer, on va sur la page d'inscription. Trois choses à préparer : votre nom, votre numéro WhatsApp, et le nom de votre commerce.
> 1. Votre nom complet.
> 2. Votre numéro WhatsApp — c'est avec ça que vous vous connecterez, pas besoin d'email si vous n'en avez pas.
> 3. Un mot de passe que vous pouvez retenir facilement.
> 4. Le nom de votre commerce, et son type — restaurant, stand, maquis, kiosque...
> 5. Si vous avez un logo ou une photo, ajoutez-la — sinon pas de souci, vous pourrez le faire plus tard.
> 6. On coche la case des conditions, et on valide ! »

**Point important à dire clairement :** « Votre compte n'est pas encore actif tout de suite — on regarde ça dans la prochaine vidéo. »

---

## Épisode 2 — L'attente de validation

**Ce qu'on voit à l'écran :** l'écran "Inscription reçue — en attente de validation" qui apparaît juste après l'inscription.

**Script :**
> « Après votre inscription, vous voyez cet écran : "en attente de validation". C'est normal ! Chaque nouveau commerce est vérifié par l'équipe MenuPro avant de pouvoir commencer à vendre — ça évite les faux comptes et protège tout le monde.
> Dès que c'est validé, vous recevez une notification, et votre essai gratuit de 7 jours démarre à ce moment-là. Patience, ça ne prend pas longtemps ! »

**Message clé :** éviter la panique/confusion — ce n'est pas un bug, c'est volontaire et rassurant.

---

## Épisode 3 — Se connecter

**Ce qu'on voit à l'écran :** la page de connexion `/connexion`.

**Script :**
> « Une fois votre compte validé, pour vous connecter : votre numéro WhatsApp, votre mot de passe, et le bouton "Se connecter". Cochez "Se souvenir de moi" pour ne pas avoir à le refaire à chaque fois sur votre téléphone. »

---

## Épisode 4 — Découvrir son tableau de bord

**Ce qu'on voit à l'écran :** le dashboard restaurant après connexion (`/dashboard`).

**Script :**
> « Voici votre tableau de bord — la page que vous verrez à chaque connexion. En haut, vos chiffres du jour : commandes reçues, montant encaissé. En bas, un menu pour naviguer entre les différentes parties : vos Catégories, vos Plats, vos Commandes, votre QR Code, vos Paramètres.
> Sur téléphone, appuyez sur les trois barres en haut à gauche, ou utilisez la barre en bas de l'écran, pour vous déplacer. »

**Message clé :** orienter dans la navigation mobile (menu hamburger + barre du bas) avant d'aller plus loin.

---

## Épisode 5 — Créer ses catégories

**Ce qu'on voit à l'écran :** page Catégories, création d'une nouvelle catégorie.

**Script :**
> « Avant d'ajouter vos plats, on organise le menu en catégories — comme des tiroirs. Par exemple : "Plats", "Boissons", "Desserts".
> 1. Allez dans Catégories.
> 2. Appuyez sur "Nouvelle catégorie".
> 3. Donnez-lui un nom.
> 4. Choisissez si c'est pour la cuisine ou pour le bar.
> 5. Enregistrez.
> Recommencez pour chaque type de produit que vous vendez. Deux ou trois catégories suffisent pour démarrer. »

---

## Épisode 6 — Ajouter son premier plat

**Ce qu'on voit à l'écran :** page Plats, formulaire de création d'un plat.

**Script :**
> « Maintenant, le plus important : vos plats ! Dans Plats, appuyez sur "Nouveau plat".
> 1. Le nom du plat — soyez clair, c'est ce que le client voit en premier.
> 2. Une petite description, si vous voulez.
> 3. Le prix.
> 4. La catégorie — celle qu'on vient de créer.
> 5. Une photo ! Un plat avec une belle photo se vend beaucoup mieux qu'un plat sans photo.
> 6. Enregistrez.
> Faites ça pour chaque plat de votre menu. Prenez votre temps — vous pouvez toujours en ajouter plus tard. »

**Message clé :** insister sur l'importance de la photo (impact direct sur les ventes).

---

## Épisode 7 — Les options sur un plat

**Ce qu'on voit à l'écran :** formulaire plat, section "options" (ex. taille, suppléments).

**Script :**
> « Certains plats ont des choix — une taille, un supplément, un accompagnement. Dans la fiche du plat, ajoutez un groupe d'options, par exemple "Taille" avec "Petit / Moyen / Grand", chacun avec son propre prix si besoin.
> Pas obligatoire — seulement si votre plat a vraiment des variantes. »

---

## Épisode 8 — Activer / désactiver un plat

**Ce qu'on voit à l'écran :** liste des plats, bascule "disponible / indisponible".

**Script :**
> « Plus de poulet aujourd'hui ? Pas besoin de supprimer le plat ! Dans la liste de vos plats, désactivez-le d'un seul geste — il n'apparaîtra plus pour vos clients tant qu'il est désactivé. Dès que vous en avez de nouveau, réactivez-le. »

---

## Épisode 9 — Compléter les informations du commerce

**Ce qu'on voit à l'écran :** page Paramètres.

**Script :**
> « Dans Paramètres, complétez les infos de votre commerce : nom, petite description, téléphone, adresse, ville. Ajoutez votre logo et une photo de bannière si vous en avez — ça rend votre page beaucoup plus pro et donne confiance aux clients.
> Vous pouvez aussi choisir vos couleurs pour que votre page vous ressemble. »

---

## Épisode 10 — Générer son QR code

**Ce qu'on voit à l'écran :** page QR Code.

**Script :**
> « Dernière étape avant de vendre : votre QR code ! C'est lui que vos clients scannent pour voir votre menu et commander.
> Dans QR Code, téléchargez votre code, imprimez-le, et collez-le sur votre stand, vos tables, votre comptoir — bien visible.
> Vous pouvez aussi télécharger une version pour vos réseaux sociaux (Facebook, WhatsApp Status) pour que les gens commandent même de chez eux. »

**Message clé :** c'est l'étape "on est prêt à vendre" — bon moment pour une mini-célébration dans la vidéo.

---

## Épisode 11 — Recevoir sa première commande

**Ce qu'on voit à l'écran :** notification de nouvelle commande + page Commandes.

**Script :**
> « Quand un client commande, vous recevez une notification avec un son — même l'écran éteint, vous ne la raterez pas.
> Ouvrez l'appli, allez dans Commandes : vous voyez le nom du client, ce qu'il a commandé, et le montant. »

---

## Épisode 12 — Faire avancer une commande

**Ce qu'on voit à l'écran :** le détail d'une commande, changement de statut.

**Script :**
> « Chaque commande suit des étapes : reçue, en préparation, prête, puis livrée ou récupérée par le client.
> Ouvrez la commande et faites-la avancer d'un bouton à chaque étape — le client voit l'avancement en temps réel de son côté, ça le rassure et évite les questions ! »

---

## Épisode 13 — Utiliser la caisse (POS)

**Ce qu'on voit à l'écran :** page POS (caisse), prise d'une commande sur place.

**Script :**
> « Un client arrive directement chez vous, sans passer par son téléphone ? Utilisez la Caisse !
> Sélectionnez les plats, choisissez sur place / à emporter, entrez le nom du client, encaissez — en espèces ou en Mobile Money. C'est aussi simple qu'une caisse normale, mais tout reste enregistré dans MenuPro. »

---

## Épisode 14 — Voir l'historique des commandes

**Ce qu'on voit à l'écran :** page Commandes, filtre par date/statut.

**Script :**
> « Besoin de retrouver une commande d'hier ou de savoir combien vous avez vendu cette semaine ? Dans Commandes, utilisez la recherche et les filtres — par numéro, par nom de client, par date. »

---

## Épisode 15 — Gérer son abonnement

**Ce qu'on voit à l'écran :** page Abonnement.

**Script :**
> « Votre essai gratuit de 7 jours touche à sa fin ? Dans Abonnement, choisissez votre formule et payez en Mobile Money — Wave, Orange Money, MTN, Moov.
> Si votre commerce grandit, vous pouvez passer à un plan supérieur à tout moment pour débloquer la livraison, la gestion de stock, les statistiques, et plus encore. »

---

## Épisode 16 — Besoin d'aide

**Ce qu'on voit à l'écran :** bouton d'aide / contact.

**Script :**
> « Un souci, une question ? Ne restez pas bloqué·e seul·e — contactez le support MenuPro directement depuis l'appli ou par WhatsApp. On est là pour vous aider à réussir. »

---

## Gabarit réutilisable — « Nouvelle fonctionnalité »

À utiliser à chaque fois que MenuPro ajoute une fonctionnalité, pour prévenir les restaurateurs en vidéo courte, façon statut WhatsApp.

**Structure (45 secondes max) :**
1. **Accroche (5s)** — « Nouveau sur MenuPro ! »
2. **Le problème (10s)** — à quoi ça sert, en une phrase simple, concrète, liée au quotidien du restaurateur.
3. **La démonstration (20-25s)** — montrer l'action à l'écran, étape par étape, sans jargon.
4. **L'appel à l'action (5s)** — « Essayez-le dès maintenant dans [nom du menu] ! »

**Exemple à remplir :**
> « Nouveau sur MenuPro ! Vous en avez marre de dire non quand un plat n'est plus disponible ? Maintenant, désactivez un plat en un seul geste, directement depuis la liste de vos plats. [démonstration à l'écran]. Essayez-le dès maintenant dans l'onglet Plats ! »

---

## Notes de production

- **Langue :** français simple, phrases courtes, pas de jargon technique ("interface", "back-office", "dashboard" → préférer "tableau de bord", "appli", "page").
- **Rythme :** une seule action par vidéo. Ne jamais combiner deux épisodes en un seul tutoriel.
- **Captures d'écran :** utiliser de vraies captures de l'application (téléphone, pas desktop — notre public utilise majoritairement un téléphone) pour que Claude génère des vidéos fidèles à ce que verra réellement l'utilisatrice.
- **Fonctionnalités réservées aux plans supérieurs** (livraison, gestion de stock, statistiques, chambres d'hôtel, multi-espaces) : à traiter dans une série séparée, destinée aux restaurants en plan Pro/Business/Gold — ne pas les mélanger avec la série Stand pour ne pas perdre ou décourager les petites vendeuses.
- **À remettre à jour** si l'inscription, le tableau de bord, ou les étapes de création de plat changent dans l'application.
