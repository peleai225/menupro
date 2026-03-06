# MenuPro — Le projet va-t-il tenir avec le trafic ? (MySQL)

## En résumé

**Oui, le projet peut tenir** avec un trafic normal à élevé et MySQL, à condition que la configuration serveur et les bonnes pratiques soient en place. MySQL est adapté à un SaaS de cette taille.

---

## Ce qui est déjà bien en place

### 1. **Queue (tâches en arrière-plan)**
- Les **notifications** (emails : rejet agent, retrait payé, essai, etc.) implémentent `ShouldQueue` → les envois d’emails ne bloquent pas la requête.
- Les **jobs** (expiration essai, rappels abonnement, nettoyage, alertes stock) sont aussi en file.
- Avec `QUEUE_CONNECTION=database`, les jobs sont stockés en base et traités par un worker.

**À faire en production :** un **worker** doit tourner en continu, sinon les emails et jobs ne partent pas :
```bash
php artisan queue:work --tries=3
```
(ou via Supervisor pour redémarrage automatique.)

### 2. **Cache et config**
- `.env.example` prévoit `CACHE_STORE=database` → adapté à un hébergement multi-processus.
- En prod, les commandes `config:cache`, `route:cache`, `view:cache` réduisent la charge (déjà documentées dans vos checklists).

### 3. **Base de données**
- Les migrations définissent des **index** sur les tables importantes (restaurants, orders, users, etc.).
- Laravel utilise une connexion MySQL par requête ; avec un pool MySQL correct côté serveur, ça tient bien.

### 4. **Optimisations déjà faites**
- Onglets en Alpine.js (pas de requête Livewire à chaque clic) pour limiter la charge inutile.

---

## Points à vérifier pour que ça tienne

### 1. **Worker de queue**
Sans worker, les emails et jobs s’accumulent et ne sont pas exécutés. **Indispensable** en production.

### 2. **Session**
- `.env.example` : `SESSION_DRIVER=file`. Avec beaucoup de connexions simultanées, le disque peut devenir un goulot.
- Recommandation si le trafic monte : **`SESSION_DRIVER=database`** (table `sessions`), plus adapté quand il y a du trafic.

### 3. **MySQL**
- Version **MySQL 8** (ou MariaDB 10.3+) recommandée.
- Ajuster `max_connections` et la RAM selon l’offre de l’hébergeur.
- Sauvegardes régulières (quotidiennes au minimum).

### 4. **Hébergement**
- PHP 8.2+ (OPcache activé).
- Assez de RAM pour PHP (ex. 256–512 Mo par worker si vous en lancez plusieurs).

---

## Si le trafic augmente encore

- **Cache du menu public** : mettre en cache (ex. `Cache::remember`) la liste des plats/catégories par restaurant pendant 1–5 minutes pour réduire les requêtes sur les pages les plus vues.
- **Redis** (optionnel) : pour `CACHE_STORE` et `QUEUE_CONNECTION`, Redis est plus performant que la base pour cache et files d’attente ; à envisager si vous dépassez un trafic “normal”.
- **CDN / assets** : images et JS/CSS servis via CDN pour alléger le serveur.

---

## Conclusion

- **Trafic “normal” à “assez élevé”** : oui, le projet peut tenir avec MySQL si le worker tourne, les caches Laravel sont activés et la session est bien configurée (idéalement `database` si beaucoup d’utilisateurs connectés).
- **Très gros trafic** : il faudra ajouter cache (menu, etc.), éventuellement Redis, et surveiller MySQL (connexions, requêtes lentes).

En résumé : **oui, avec MySQL et une config correcte (worker + caches + session éventuellement en DB), vous avez de la marge.**
