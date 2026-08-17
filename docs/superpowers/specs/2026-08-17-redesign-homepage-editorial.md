# Redesign Homepage MenuPro — Style Éditorial

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Refondre complètement `resources/views/pages/public/home.blade.php` avec un style éditorial premium — fond crème/blanc cassé, typographie Instrument Serif × Space Grotesk, accents orange, cartes bien disposées.

**Architecture:** Remplacement complet du fichier home.blade.php. Les 14 sections existantes sont conservées mais entièrement redessinées. Deux nouvelles polices Google Fonts chargées dans le layout. Les tokens de couleur Tailwind existants sont complétés par de nouvelles valeurs crème dans `app.css`.

**Tech Stack:** Laravel 11, Blade, Tailwind CSS v4, Alpine.js, Google Fonts (Instrument Serif + Space Grotesk)

---

## Global Constraints

- Fichier cible : `resources/views/pages/public/home.blade.php`
- Layout parent : `resources/views/components/layouts/app.blade.php` — les polices Google Fonts sont ajoutées ici
- CSS global : `resources/css/app.css` — nouvelles variables crème à ajouter dans `@theme`
- Toutes les données dynamiques existantes (restaurants, plans, heroImagesUrls, etc.) sont conservées — aucun changement côté contrôleur
- Alpine.js pour les interactions (carousel dots, FAQ accordion, témoignages slider)
- Les classes `.fu` / `.fu.in` (scroll reveal) sont conservées et appliquées sur toutes les sections
- Aucune dépendance JS externe (pas de GSAP, pas de Swiper) — Alpine.js + CSS uniquement
- Fond de page : `#FAF8F5` (crème chaud) — plus de `#080808`
- Couleur texte principale : `#1A1614` (quasi-noir chaud)
- Orange d'accent : `#D45E0C` (inchangé)

---

## Nouvelles variables CSS (`@theme` dans `app.css`)

```css
--color-cream-50:  #FDFCFA;
--color-cream-100: #FAF8F5;
--color-cream-200: #F2EDE6;
--color-cream-300: #E8E0D5;
--color-cream-400: #D4C8B8;
--color-warm-900:  #1A1614;
--color-warm-700:  #3D3330;
--color-warm-500:  #7C6F65;
--color-warm-300:  #B8AFA8;
```

---

## Nouvelles polices

Dans `resources/views/components/layouts/app.blade.php`, ajouter dans `<head>` :

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

Dans `app.css`, remplacer `--font-sans` et `--font-display` :

```css
--font-sans:    'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
--font-display: 'Instrument Serif', Georgia, serif;
```

Classe utilitaire à ajouter dans `app.css` :

```css
.font-display { font-family: var(--font-display); }
```

---

## Section 1 — Hero

**Layout :** Pleine largeur, fond `#FAF8F5`, `min-h-screen`, centré verticalement. Typographie seule — pas d'image.

**Structure HTML :**
```
<section> fond crème, min-h-screen, flex col centré
  <div> container max-w-5xl centré
    <span> badge "Nouveau · v2.0" pill orange léger
    <h1> Instrument Serif, text-6xl lg:text-8xl, leading-tight
         ligne 1 : "La plateforme"
         ligne 2 : mot "restaurants" en orange (#D45E0C)
         ligne 3 : "qui fait vendre."
    <p> Space Grotesk, text-xl, warm-500, max-w-2xl
    <div> 2 boutons côte à côte
          btn-primary orange "Démarrer gratuitement →"
          btn-outline warm "Voir la démo"
    <div> social proof : "Rejoignez X restaurants" + avatars empilés
  </div>
  <div> séparateur bas de hero : fine ligne crème-300 + flèche bas animée
</section>
```

**Typographie hero :**
- H1 : `font-display text-6xl lg:text-[5.5rem] xl:text-[7rem] font-normal leading-[1.05] tracking-tight text-warm-900`
- Mot-clé orange : `<em class="not-italic text-primary-500">`
- Sous-titre : `font-sans text-lg lg:text-xl text-warm-500 max-w-2xl mt-6`

---

## Section 2 — Logos Strip (social proof)

**Layout :** Fond blanc `#FFFFFF`, `py-12`, bordures haut/bas `cream-300`.

```
<section> fond blanc, bordures top/bottom cream-300
  <p> "Ils nous font confiance" Space Grotesk text-sm uppercase tracking-widest warm-400
  <div> marquee (overflow-hidden, animation marquee existante)
        logos restaurants (images depuis storage)
</section>
```

---

## Section 3 — Pour qui

**Layout :** Fond crème `#FAF8F5`, `py-24`. Titre centré en Instrument Serif. 3 cards en grille.

**Cards "Pour qui" :**
```css
/* fond blanc, border cream-300, rounded-2xl, p-8, shadow douce */
/* hover : -translate-y-2, shadow-elevated, border-primary-200 */
/* transition 300ms */
```

Structure d'une card :
```
<div> card blanche p-8
  <div> icône dans cercle — bg-primary-50, text-primary-500, w-14 h-14 rounded-2xl
  <h3> Instrument Serif text-2xl mt-6 text-warm-900
  <p>  Space Grotesk text-base text-warm-500 mt-3
  <a>  "En savoir plus →" text-primary-500 text-sm font-medium mt-4
```

---

## Section 4 — Features

**Layout :** Alternance fond `#FAF8F5` / `#F2EDE6`. Chaque feature = 2 colonnes (texte + visuel). 2 grandes features + 4 petites en grille.

**Grande feature (alternée gauche/droite) :**
```
<div> grid lg:grid-cols-2 gap-16 items-center py-24
  <div> texte
    <span> pill tag "Feature" bg-primary-50 text-primary-600
    <h2>  Instrument Serif text-4xl lg:text-5xl text-warm-900
    <p>   Space Grotesk text-warm-500 mt-4
    <ul>  liste de points avec check circle orange
  <div> visuel
    <div> card blanche rounded-3xl p-4 shadow-elevated
          screenshot ou mockup UI
```

**4 petites features :** grid `md:grid-cols-2 lg:grid-cols-4`, cards compactes `p-6`, icône + titre + description.

---

## Section 5 — Stats Strip

**Layout :** Fond `#1A1614` (quasi-noir chaud), `py-20`. Chiffres en Instrument Serif massif, blanc.

```
<section> bg-[#1A1614] py-20
  <div> grid grid-cols-2 lg:grid-cols-4 gap-8 text-center
    @foreach stat
      <div>
        <p> Instrument Serif text-6xl lg:text-7xl text-white font-normal
        <p> Space Grotesk text-sm uppercase tracking-widest text-white/50 mt-2
```

Chiffres : `500+` restaurants, `50 000+` commandes/mois, `4.8/5` satisfaction, `99.9%` uptime.

---

## Section 6 — How it works

**Layout :** Fond crème `#FAF8F5`, `py-24`. Titre centré. 3 étapes avec numéro, connexion visuelle et icône.

```
<div> grid lg:grid-cols-3 gap-8 relative
  <!-- ligne de connexion entre les steps via ::before ou border-t -->
  @foreach step (1, 2, 3)
    <div> text-center relative
      <div> numéro — w-14 h-14 rounded-full bg-primary-500 text-white
            Instrument Serif text-2xl font-normal
      <h3> Instrument Serif text-xl mt-6 text-warm-900
      <p>  Space Grotesk text-warm-500 mt-3 text-sm
```

---

## Section 7 — App Download

**Layout :** Fond `#F2EDE6`, `py-24`. Asymétrique : texte/boutons à gauche, phone mockup à droite.

```
<div> grid lg:grid-cols-2 gap-16 items-center
  <div> texte gauche
    <h2>  Instrument Serif text-4xl text-warm-900
    <p>   Space Grotesk text-warm-500
    <div> 2 boutons empilés (existants Android + iPhone)
          style mis à jour : cards blanches avec icon + label + sous-label
  <div> visuel droite
        phone mockup (img ou div stylisée) avec légère rotation -6deg
        shadow-elevated, flottant avec .animate-float
```

---

## Section 8 — Driver App

**Layout :** Fond `#FAF8F5`, `py-24`. Similaire à section 7 mais inversé (mockup gauche, texte droite). Section secondaire — traitement plus compact.

---

## Section 9 — Vidéos

**Layout :** Fond blanc, `py-24`. Titre centré. 2-3 cards vidéo avec thumbnail + overlay play.

```
<div> grid md:grid-cols-2 lg:grid-cols-3 gap-6
  <div> card vidéo — relative rounded-2xl overflow-hidden aspect-video
    <img> thumbnail
    <div> overlay hover bg-black/40
    <button> cercle play blanc centré
    <p>  titre vidéo en bas, Space Grotesk text-sm
```

---

## Section 10 — Témoignages

**Layout :** Fond `#F2EDE6`, `py-24`. Slider Alpine.js simple (3 cards visibles sur desktop, 1 sur mobile).

**Card témoignage :**
```
<div> card blanche p-8 rounded-2xl
  <p>  guillemets décoratifs Instrument Serif text-6xl text-primary-200
  <blockquote> Instrument Serif italic text-xl text-warm-700 mt-2
  <div> avatar rond + nom + restaurant Space Grotesk text-sm mt-6
```

---

## Section 11 — Restaurants

**Layout :** Fond `#FAF8F5`, `py-24`. Grille de cards restaurants avec hover reveal.

```
<div> grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4
  <div> card — relative aspect-square rounded-2xl overflow-hidden
    <img> logo/photo du restaurant object-cover
    <div> overlay hover bg-warm-900/80 flex items-center justify-center
          nom du restaurant Space Grotesk text-sm text-white
```

---

## Section 12 — Tarifs

**Layout :** Fond blanc, `py-24`. Titre centré. 4 cards en grille. Plan Pro mis en avant.

**Card pricing standard :**
```
<div> card blanche p-8 rounded-2xl border border-cream-300
  <p>  plan name Space Grotesk text-sm uppercase tracking-widest text-warm-500
  <p>  prix Instrument Serif text-5xl text-warm-900
       + "/mois" Space Grotesk text-base text-warm-400
  <ul> features avec check circle
  <a>  bouton "Choisir ce plan"
```

**Card Pro (highlight) :**
```
<div> card bg-primary-500 text-white p-8 rounded-2xl
     badge "Recommandé" pill blanc/translucide en haut
     prix en Instrument Serif text-white
     bouton "Choisir Pro" bg-white text-primary-500
```

---

## Section 13 — FAQ

**Layout :** Fond `#FAF8F5`, `py-24`, `max-w-3xl mx-auto`. Accordion Alpine.js.

```
<div x-data="{ open: null }">
  @foreach faq
  <div> border-b border-cream-300
    <button> @click="open = open === i ? null : i"
             flex justify-between items-center w-full py-5
             Space Grotesk text-base font-medium text-warm-900
             + chevron rotatif
    <div x-show="open === i" x-collapse>
         Space Grotesk text-warm-500 pb-5
```

---

## Section 14 — CTA Final

**Layout :** Fond `#1A1614`, `py-32`. Centré. Titre massif Instrument Serif, texte blanc/crème, bouton orange.

```
<section> bg-[#1A1614] py-32 text-center
  <h2>  Instrument Serif text-5xl lg:text-7xl text-[#FAF8F5] font-normal leading-tight
        "Prêt à faire décoller" <br> "votre restaurant ?"
  <p>   Space Grotesk text-lg text-white/50 mt-6
  <div> bouton "Créer mon compte gratuitement →"
        bg-primary-500 hover:bg-primary-600 text-white px-10 py-4 rounded-2xl
        font-semibold text-base transition
```

---

## Fichiers à modifier

| Fichier | Action |
|---------|--------|
| `resources/views/pages/public/home.blade.php` | Réécriture complète |
| `resources/views/components/layouts/app.blade.php` | Ajouter Google Fonts (Instrument Serif + Space Grotesk) |
| `resources/css/app.css` | Ajouter variables `cream-*` et `warm-*` dans `@theme`, mettre à jour `--font-sans` et `--font-display`, ajouter `.font-display` |

## Fichiers à NE PAS modifier

- Contrôleurs, routes, modèles — aucun changement back-end
- `resources/views/components/layouts/admin-super.blade.php` — le super-admin n'est pas concerné
- `resources/views/components/layouts/restaurant.blade.php` — idem

---

## Tests visuels attendus

- [ ] Hero : titre Instrument Serif visible et bien typographié sur mobile et desktop
- [ ] Section stats : fond sombre avec chiffres massifs blancs
- [ ] Cards "Pour qui" : hover élévation visible
- [ ] FAQ : accordion ouverture/fermeture fluide
- [ ] Témoignages : slider fonctionnel
- [ ] Tarifs : card Pro orange bien distincte des autres
- [ ] CTA final : fond sombre, bouton orange visible
- [ ] Scroll reveal `.fu.in` : toutes les sections s'animent à l'entrée dans le viewport
- [ ] Mobile responsive : toutes les grilles passent en 1 colonne sur mobile
