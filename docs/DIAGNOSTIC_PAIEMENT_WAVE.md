# Diagnostic : scan QR / Wave sans prélèvement

Vous scannez le QR code (ou utilisez le lien Wave direct) mais **aucun prélèvement** n’est effectué sur votre compte Wave. Voici ce qui se passe et quoi faire.

---

## Ce que fait MenuPro (côté app)

1. **Création du paiement**  
   MenuPro appelle l’API GeniusPay avec le montant, votre téléphone, etc. → GeniusPay renvoie une référence (ex. `MTX-xxx`) et une URL (page GeniusPay ou lien Wave direct).

2. **Redirection**  
   Vous êtes envoyé vers cette URL (page avec QR ou directement vers Wave).

3. **Côté Wave / GeniusPay**  
   C’est **Wave et GeniusPay** qui gèrent :
   - l’affichage du QR ou de la demande de paiement dans l’app Wave,
   - le débit sur votre compte Wave quand vous validez.

4. **Retour sur MenuPro**  
   Après paiement (ou annulation), vous êtes renvoyé sur notre « page succès » ou « annulation ». MenuPro interroge alors l’API GeniusPay pour savoir si le paiement est réussi et met à jour la commande en conséquence.

Donc : **le prélèvement lui‑même dépend uniquement de Wave et GeniusPay**. Si rien n’est débité, le blocage est **côté Wave/GeniusPay**, pas dans le code MenuPro.

---

## Problème que vous rencontrez

**Symptôme :**  
Vous scannez le QR (ou ouvrez le lien Wave), mais **aucun prélèvement** n’est fait sur votre compte Wave.

**Cause probable :**  
Le flux de paiement ne va pas jusqu’au bout **chez GeniusPay/Wave** (validation, timeout, refus, bug, ou configuration compte marchand). MenuPro ne peut pas « forcer » un prélèvement ; il ne fait que créer la session et vérifier le statut ensuite.

---

## Vérifications côté MenuPro (déjà en place)

- Paiement créé en **live** (logs avec `"environment":"live"` et référence `MTX-xxx`).
- Avec `GENIUSPAY_DIRECT_WAVE=true`, vous êtes bien redirigé vers **pay.wave.com** (paiement direct Wave, sans passer par la page GeniusPay avec QR).
- Au retour sur la page succès, MenuPro appelle l’API GeniusPay pour vérifier le statut et marquer la commande payée si le paiement est bien « completed/success/paid ».

Si après un paiement réussi sur Wave vous revenez sur la page succès et que la commande reste « non payée », les logs `payments-YYYY-MM-DD.log` contiennent maintenant une ligne **« GeniusPay return (success URL): verification result »** avec `status` et `paid` : cela indique ce que GeniusPay nous renvoie (ex. `pending` = pas encore réglé côté eux).

---

## Que faire concrètement

1. **Refaire un test en notant la référence**
   - Faites un paiement test (QR ou lien Wave).
   - Notez la **référence** affichée (ex. `MTX-1ZX0JMVTRT` ou celle dans l’URL / l’email de confirmation).

2. **Vérifier les logs au retour**
   - Après avoir été redirigé vers la page succès de MenuPro, ouvrez `storage/logs/payments-2026-03-05.log` (ou la date du jour).
   - Cherchez la ligne **« GeniusPay return (success URL): verification result »** pour cette commande.
   - Si vous voyez `"paid":false` et `"status":"pending"` (ou `"failed"`), cela confirme que **GeniusPay n’a pas enregistré de paiement réussi** → le problème est bien côté Wave/GeniusPay.

3. **Contacter le support GeniusPay**
   - **Email :** support@geniuspay.ci (ou le contact indiqué sur pay.genius.ci).
   - Indiquez :
     - que vous utilisez l’API paiement (création + redirection vers Wave / QR),
     - le symptôme : **« Scan du QR code Wave (ou paiement direct Wave) mais aucun prélèvement sur le compte Wave »**,
     - une **référence de transaction** (ex. `MTX-xxxx`) et la date/heure du test.
   - Ils pourront vérifier côté compte marchand, statut de la transaction et configuration Wave.

4. **Côté compte marchand GeniusPay**
   - Vérifiez dans le [tableau de bord GeniusPay](https://pay.genius.ci/dashboard) que :
     - Wave est bien activé pour votre compte,
     - le compte est validé (KYC si demandé),
     - les transactions de test apparaissent et dans quel statut (pending, failed, etc.).

---

## Récapitulatif

| Élément | Où ça se passe |
|--------|-----------------|
| Création de la session de paiement | MenuPro → GeniusPay ✅ |
| Redirection vers Wave / page QR | GeniusPay ✅ |
| **Prélèvement sur le compte Wave** | **Wave / GeniusPay** ❌ (c’est ici que ça bloque) |
| Mise à jour « commande payée » dans MenuPro | MenuPro après vérification du statut GeniusPay ✅ |

**En résumé :** le problème que vous rencontrez (« je scanne le QR mais pas de prélèvement ») vient du **flux côté Wave/GeniusPay**. Il faut faire vérifier par GeniusPay pourquoi le paiement ne se finalise pas (référence MTX en main + logs de vérification si besoin).
