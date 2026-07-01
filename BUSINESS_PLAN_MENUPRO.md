# MenuPro - Document Strategique Complet
## SaaS de digitalisation pour maquis & restaurants en Cote d'Ivoire

---

# 1. VISION & MISSION

**Vision** : Devenir la plateforme digitale #1 des maquis et restaurants en Afrique francophone.

**Mission** : Permettre a tout gerant de restaurant, meme sans competence technique, de digitaliser son activite en moins de 10 minutes : menu en ligne, commandes, paiement mobile, gestion du stock.

**Proposition de valeur unique** : MenuPro est le seul outil pense pour le maquis ivoirien - simple comme WhatsApp, adapte au paiement mobile (Wave, Orange Money, MTN via Jeko), avec une equipe terrain (agents Commando) qui fait l'acquisition en physique.

---

# 2. PRODUIT

## 2.1 Stack technique
| Composant | Technologie |
|---|---|
| Backend | Laravel 12 + PHP 8.3 |
| Frontend temps reel | Livewire 3 + Alpine.js |
| UI/CSS | Tailwind CSS |
| Base de donnees | MySQL |
| Paiement | Jeko Africa (Wave, Orange, MTN, Moov, Djamo, Carte) |
| Hebergement | Serveur dedie (tuxedo) |
| Domaine | menupro.ci |

## 2.2 Architecture fonctionnelle

```
CLIENTS (public)          RESTAURATEURS (backoffice)       ADMIN (super-admin)
   |                              |                              |
   v                              v                              v
Menu QR Code             Dashboard + Analytics            Gestion globale
Commander en ligne       Gestion commandes/POS            Restaurants
Payer (Jeko)             Stock journalier                 Agents Commando
Suivre commande          Ingredients + fournisseurs       Plans & abonnements
Laisser un avis          Livraisons + livreurs            Paiements & payouts
                         Depenses + rentabilite           Parametres systeme
                         Equipe (multi-employes)
                         Reservations
                         Codes promo
```

## 2.3 Modeles de donnees principaux (38 modeles)
- **Restaurant** : entite centrale multi-tenant
- **Dish** / **Category** : menu digital
- **Order** / **OrderItem** : commandes avec statuts (DRAFT > PAID > CONFIRMED > PREPARING > READY > COMPLETED)
- **Subscription** / **Plan** : abonnements SaaS
- **Ingredient** / **StockMovement** : gestion stock avancee
- **CommandoAgent** / **CommandoCommissionTransaction** : reseau d'agents terrain
- **Delivery** / **DeliveryDriver** : gestion livraisons
- **Expense** : suivi des depenses
- **Review** / **Reservation** / **PromoCode** : engagement client

## 2.4 Fonctionnalites par plan

### Plan ESSENTIEL - 15 000 FCFA/mois
*Cible : petits maquis, garba, restaurants de l'interieur qui demarrent*

| Fonctionnalite | Detail |
|---|---|
| Menu digital + QR Code | Jusqu'a 25 plats, 8 categories |
| Commandes en ligne | 200 commandes/mois max |
| Paiement mobile (Jeko) | Wave, Orange, MTN, Moov |
| Caisse (POS) | Prise de commande sur place |
| Gestion clients | Liste des clients + historique |
| Reservations | Gestion basique des tables |
| Avis clients | Collecte et affichage |
| 1 compte employe | Pour le caissier/serveur |
| Taxes & frais configurables | TVA, service fee |

**Ce qui est verrouille (visible mais bloque)** : Statistiques, Rapports, Codes Promo, Stock, Depenses, Livraison

### Plan PRO - 25 000 FCFA/mois
*Cible : restaurants etablis, maquis VIP, fast-food*

| En plus de Essentiel | Detail |
|---|---|
| 80 plats, 20 categories | Menu complet |
| 1 000 commandes/mois | Volume moyen |
| Statistiques & rapports | CA, tendances, pics d'activite |
| Stock journalier | Portions du jour par plat |
| Stock ingredients | Avec fournisseurs, alertes, mouvements |
| Depenses & rentabilite | Cout reel par plat |
| Codes promo | Campagnes de fidelisation |
| Livraison | Livreurs + suivi + zones |
| 3 comptes employes | Equipe complete |

### Plan BUSINESS - 45 000 FCFA/mois
*Cible : grandes chaines, restaurants premium, multi-sites*

| En plus de Pro | Detail |
|---|---|
| Plats illimites | Aucune restriction |
| Commandes illimitees | Volume tres eleve |
| 10 comptes employes | Grande equipe |
| Domaine personnalise | monrestaurant.ci |
| Support prioritaire | Reponse sous 2h |

---

# 3. MODELE ECONOMIQUE

## 3.1 Sources de revenus

| Source | Montant | Marge |
|---|---|---|
| Abonnements SaaS | 15k-45k FCFA/mois/restaurant | ~90% |
| Commission sur transactions | Configurable (actuellement 0%) | 100% |
| Supports QR physiques | Vente ponctuelle (rigide/autocollant) | ~60% |
| Domaines personnalises (Business) | Inclus dans le plan | -- |

## 3.2 Projections financieres (12 mois)

**Hypotheses** :
- 10 agents Commando actifs
- Objectif 10 restaurants/agent/mois (3 premiers mois), puis retention
- Taux de churn : 15%/mois (debut), 8%/mois (apres 6 mois)
- Mix plans : 60% Essentiel, 30% Pro, 10% Business

| Mois | Restaurants actifs | MRR (FCFA) | Cumul revenus |
|---|---|---|---|
| M1 | 30 | 525 000 | 525 000 |
| M3 | 80 | 1 400 000 | 3 850 000 |
| M6 | 150 | 2 625 000 | 11 500 000 |
| M12 | 280 | 4 900 000 | 38 000 000 |

**Revenu moyen par restaurant** : 17 500 FCFA/mois (mix plans)

## 3.3 Structure de couts

| Poste | Mensuel (FCFA) | Notes |
|---|---|---|
| Hebergement serveur | 50 000 | Scalable |
| Domaine + SSL | 5 000 | menupro.ci |
| SMS / notifications | 30 000 | Variable selon volume |
| Commissions agents Commando | Variable | Voir section 5 |
| Marketing digital | 100 000 | Facebook/Instagram ads |
| Developpement (maintenance) | 200 000 | Evolutions + bugs |
| **Total fixe** | **~385 000** | |

**Point mort** : ~22 restaurants Essentiel OU ~16 restaurants Pro

---

# 4. STRATEGIE D'ACQUISITION : LES AGENTS COMMANDO

## 4.1 Concept
Les agents Commando sont un reseau de commerciaux terrain independants qui demarchent physiquement les maquis et restaurants. Ils :
1. Presentent MenuPro au gerant
2. L'inscrivent via leur lien de parrainage (`menupro.ci/inscription?ref=UUID`)
3. L'accompagnent dans la prise en main (1ere config menu, QR code)
4. Touchent une commission quand le restaurant paie

## 4.2 Parcours agent

```
Inscription → Verification (CNI + photo) → Validation admin → Agent actif
                                                                    |
                                                          Badge ID : MP-XXX-XXXX
                                                          Lien parrainage unique
                                                          Carte agent (PDF generee)
```

## 4.3 Systeme de grades

| Grade | Condition | Avantages |
|---|---|---|
| **Rookie** | 0-5 restaurants | Commission standard |
| **Commando** | 6-20 restaurants | Badge orange, priorite support |
| **Elite** (Colonel Digital) | 21+ restaurants | Badge or, bonus mensuel, priorite payout |

## 4.4 Remuneration actuelle

| Evenement | Commission |
|---|---|
| 1er paiement d'un restaurant parraine | 5 000 FCFA |
| Renouvellements | Rien (0 FCFA) |
| Retrait | Sur demande, validation admin |

## 4.5 Remuneration RECOMMANDEE (modele recurrent)

| Evenement | Commission | Justification |
|---|---|---|
| 1er paiement | 5 000 FCFA | Bonus acquisition |
| Mois 2 a 6 (si restaurant actif) | 2 000 FCFA/mois | Incite a accompagner |
| Mois 7+ (si restaurant actif) | 1 000 FCFA/mois | Revenu passif, fidelise l'agent |
| Bonus trimestre (top 3 agents) | 25 000 FCFA | Competition saine |

**Impact financier** :
- Cout acquisition par restaurant : 5 000 F (M1) + 10 000 F (M2-6) + variable = ~15-20k sur 6 mois
- LTV restaurant Essentiel (12 mois, churn 10%) : 15k x 10 mois = 150 000 F
- **Ratio LTV/CAC = 7.5x** (excellent)

## 4.6 Flux de paiement agents

```
Commission gagnee → Solde agent (balance_cents)
                         |
         Demande retrait (min 10 000 FCFA)
                         |
         Admin valide → Paiement via Jeko/virement
                         |
         Transaction marquee WITHDRAWN
```

---

# 5. OPERATIONS QUOTIDIENNES

## 5.1 Pour le restaurateur (workflow type)

**Matin (6h-8h)** :
1. Ouvrir Dashboard → voir commandes du jour
2. Stock Journalier → definir portions dispo (ex: Attieke poisson = 50)
3. Le menu public affiche automatiquement "Disponible" / "Epuise"

**Journee** :
4. Commandes arrivent (notification)
5. Confirmer → Preparer → Pret → Livrer/Servir
6. Stock decremente automatiquement a chaque commande payee
7. Si annulation → stock restaure

**Soir** :
8. Consulter Statistiques (plan Pro+)
9. Voir depenses du jour (plan Pro+)
10. RAZ stock si besoin (pour recommencer le lendemain)

## 5.2 Pour l'admin (super-admin)

- Valider/rejeter agents Commando
- Surveiller MRR et churn
- Gerer les payouts agents
- Configurer parametres systeme (commission, gateway Jeko)
- Voir activite globale de la plateforme

---

# 6. ROADMAP PRODUIT

## Phase actuelle (DONE)
- [x] Menu digital + QR Code
- [x] Commandes en ligne + paiement Jeko
- [x] Caisse POS
- [x] Stock journalier (portions/plat)
- [x] Stock ingredients avance
- [x] Systeme agents Commando (inscription, commission, carte)
- [x] Multi-plan avec gating sidebar
- [x] Livraisons + livreurs
- [x] Depenses + rentabilite plat
- [x] Cuisine (affichage commandes)
- [x] PWA installable

## Phase 2 (Mois 1-3)
- [ ] Notifications push (commande recue, stock bas)
- [ ] Commission recurrente agents (modele degressif)
- [ ] Tableau de bord agent ameliore (performance, historique)
- [ ] Integration WhatsApp Business (notification commande au client)
- [ ] Mode offline PWA (caisse fonctionne sans internet)

## Phase 3 (Mois 4-6)
- [ ] App mobile native (Flutter) pour restaurateurs
- [ ] Marketplace multi-restaurants (ex: "commander a Cocody")
- [ ] Fidelite clients (points, recompenses)
- [ ] Multi-succursale (1 compte, plusieurs restaurants)

## Phase 4 (Mois 7-12)
- [ ] Expansion regionale (Senegal, Cameroun, Burkina)
- [ ] API ouverte pour integrations tierces
- [ ] Intelligence artificielle (prevision stock, menu recommande)
- [ ] Programme accelerateur pour agents (formation + certification)

---

# 7. EQUIPE CIBLE (Organigramme ideal)

```
                    CEO / Fondateur
                         |
        +----------------+----------------+
        |                |                |
   CTO / Dev       COO / Operations   CMO / Growth
        |                |                |
   1 Dev fullstack   1 Support client  1 Community manager
   (maintenance)     1 Admin agents    10-50 Agents Commando
                     (validation,       (terrain)
                      payouts)
```

**Phase 0 (maintenant)** : 1 personne fait tout (toi) + agents Commando
**Phase 1 (MRR > 2M)** : Recruter 1 support client + 1 community manager
**Phase 2 (MRR > 5M)** : CTO technique pour maintenance

---

# 8. INDICATEURS CLES (KPIs)

| KPI | Objectif M3 | Objectif M12 |
|---|---|---|
| MRR | 1 400 000 FCFA | 4 900 000 FCFA |
| Restaurants actifs | 80 | 280 |
| Churn mensuel | < 15% | < 8% |
| Agents actifs | 10 | 30 |
| Restaurants/agent/mois | 3 | 5 |
| Taux upgrade Essentiel→Pro | 15% | 25% |
| NPS (satisfaction) | > 7/10 | > 8/10 |
| Temps onboarding | < 15 min | < 10 min |

---

# 9. RISQUES & MITIGATIONS

| Risque | Impact | Mitigation |
|---|---|---|
| Churn eleve (restau abandonne) | Perte MRR | Stock journalier = habitude quotidienne, agent accompagne |
| Agents inactifs | Pas d'acquisition | Commission recurrente, gamification (grades), challenges |
| Panne Jeko | Plus de paiement | Cash on delivery en backup, monitoring |
| Copie par concurrent | Parts de marche | Execution rapide, reseau agents = moat |
| Internet instable | UX degradee | PWA offline, mode light |

---

# 10. COMMENT REMUNERER LES AGENTS - GUIDE PRATIQUE

## Situation actuelle dans le code

Le systeme est deja operationnel :
- `CommandoCommissionService` credite l'agent automatiquement au 1er paiement
- `CommandoCommissionTransaction` trace chaque commission
- `CommandoAgent.balance_cents` = solde disponible
- Super-admin peut voir et gerer les payouts

## Pour payer un agent aujourd'hui

1. **L'agent fait une demande de retrait** depuis son dashboard
2. **Tu recois la notification** dans le super-admin
3. **Tu valides** → le statut passe a "validated"
4. **Tu paies manuellement** (virement Wave/Orange) puis marques "withdrawn"

## Pour configurer la commission

Dans **Super Admin > Parametres** :
- `Commission 1er paiement` : 5 000 FCFA (modifiable)
- `Commission uniquement 1er paiement` : Oui/Non

## Recommandation pour industrialiser

1. Quand tu auras 10+ agents → automatiser les payouts via Jeko (batch mensuel)
2. Ajouter un seuil minimum de retrait (ex: 10 000 FCFA)
3. Mettre en place la commission recurrente (je peux l'implementer)

---

# 11. ACTIONS IMMEDIATES (cette semaine)

1. **Deployer** le gating par plan (deja pousse)
2. **Tester** le parcours client Essentiel : voit les onglets verrouilles, clique → page upgrade
3. **Activer 3-5 agents** Commando pour commencer l'acquisition
4. **Creer un groupe WhatsApp** agents pour le support terrain
5. **Preparer 1 video demo** de 2 minutes (screen recording du parcours restau)
6. **Fixer le prix QR physique** (suggestion : 5 000 FCFA le rigide, 2 000 FCFA l'autocollant)

---

*Document genere le 10 juin 2026 — MenuPro v1.0*
*Stack : Laravel 12 + Livewire 3 + Jeko Africa + 38 modeles + 246 routes + 217 fichiers PHP*
