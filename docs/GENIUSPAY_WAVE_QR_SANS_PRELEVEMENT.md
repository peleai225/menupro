# Wave : scan QR sans prélèvement

Quand le client choisit Wave sur la page GeniusPay et scanne le QR code, aucun prélèvement ne se fait. Pistes et solutions.

---

## 1. Vérifier le mode : Sandbox vs Live

En **sandbox** (clés `pk_sandbox_` / `sk_sandbox_`), les paiements sont **simulés** : aucun argent n’est vraiment prélevé.

- Vérifier dans les logs `storage/logs/payments-*.log` : si vous voyez `"environment":"sandbox"`, c’est normal qu’il n’y ait pas de prélèvement.
- Pour un **vrai** prélèvement : utiliser les clés **live** (`pk_live_` / `sk_live_`) dans Super Admin > Paramètres > GeniusPay (ou dans les paramètres du restaurant), et mode **Production**.

Un message est maintenant écrit dans les logs en sandbox :  
`GeniusPay SANDBOX: aucun prélèvement réel. Passez en clés pk_live_/sk_live_ pour des vrais paiements.`

---

## 2. Tester le paiement push Wave (sans QR)

Si le **QR ne déclenche rien** même en live, vous pouvez tester le **paiement direct Wave** (demande de paiement dans l’app Wave, sans QR).

Dans le fichier **`.env`** :

```env
GENIUSPAY_DIRECT_WAVE=true
```

Puis recharger la config (ou redémarrer la file/workers si vous en avez).

- Le client doit renseigner un **numéro de téléphone valide** (format +225 ou 07…).
- Il sera redirigé directement vers Wave et recevra une **demande de paiement** dans l’app (push).
- Aucune page de choix GeniusPay, pas de QR : uniquement Wave.

Si le prélèvement fonctionne en direct Wave mais pas avec le QR, le blocage vient du flux QR côté GeniusPay/Wave.

---

## 3. Côté GeniusPay / compte marchand

- **Wave activé** : dans le [tableau de bord GeniusPay](https://pay.genius.ci/dashboard), vérifier que Wave est bien activé pour votre compte marchand.
- **Compte validé** : certains moyens (dont Wave) peuvent exiger une validation/KYC du compte.
- **Support** : si vous êtes en **live**, avec les bonnes clés, et que ni QR ni push ne prélèvent, contacter le support GeniusPay (support@geniuspay.ci) en indiquant une **référence de transaction** (ex. `MTX-xxxx`) et le symptôme : « scan QR Wave sans prélèvement ».

---

## 4. Récap

| Situation | Action |
|-----------|--------|
| Logs avec `"environment":"sandbox"` | Passer en clés **live** pour tester un vrai prélèvement. |
| Déjà en live, QR ne prélève pas | Tester avec `GENIUSPAY_DIRECT_WAVE=true` (paiement push). |
| Push Wave fonctionne, QR non | Problème flux QR : à faire remonter à GeniusPay. |
| Ni QR ni push en live | Vérifier activation Wave + statut compte, puis contacter GeniusPay avec une référence MTX. |
