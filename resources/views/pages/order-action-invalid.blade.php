<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $expired ? 'Lien expiré' : 'Lien invalide' }} — MenuPro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #f5f5f4; color: #1c1917; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { background: #fff; border-radius: 16px; padding: 28px 20px; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .big { font-size: 44px; }
        h1 { font-size: 20px; font-weight: 900; margin: 8px 0; }
        p { color: #57534e; line-height: 1.5; }
        a { display: block; margin-top: 18px; padding: 14px; border-radius: 14px; background: #ea580c; color: #fff; font-weight: 900; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="big">{{ $expired ? '⌛' : '🔒' }}</div>
        <h1>{{ $expired ? 'Ce lien a expiré' : "Ce lien n'est pas valide" }}</h1>
        <p>
            {{ $expired
                ? 'Par sécurité, les liens des notifications ne sont valables que 30 minutes.'
                : 'Le lien est incomplet ou a été modifié.' }}
            Retrouvez la commande dans votre tableau de bord.
        </p>
        <a href="{{ route('restaurant.orders') }}">Ouvrir mes commandes</a>
    </div>
</body>
</html>
