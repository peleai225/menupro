<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouveau message de contact</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #D45E0C 0%, #b84e0a 100%); padding: 30px; border-radius: 12px 12px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Nouveau message de contact</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #e5e5e5; border-top: none; border-radius: 0 0 12px 12px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5; font-weight: bold; width: 120px;">Type :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5;">
                    @switch($data['type'])
                        @case('general')
                            Question générale
                            @break
                        @case('support')
                            Support technique
                            @break
                        @case('partnership')
                            Partenariat
                            @break
                        @case('demo')
                            Demande de démo
                            @break
                        @default
                            {{ $data['type'] }}
                    @endswitch
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5; font-weight: bold;">Nom :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5;">{{ $data['name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5; font-weight: bold;">Email :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5;">
                    <a href="mailto:{{ $data['email'] }}" style="color: #D45E0C;">{{ $data['email'] }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5; font-weight: bold;">Sujet :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e5e5;">{{ $data['subject'] }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <strong>Message :</strong>
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 10px; border: 1px solid #e5e5e5;">
                {!! nl2br(e($data['message'])) !!}
            </div>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e5e5; font-size: 12px; color: #666;">
            <p>Ce message a été envoyé depuis le formulaire de contact de MenuPro.</p>
            <p>Pour répondre, cliquez sur "Répondre" - l'email sera envoyé directement à {{ $data['email'] }}.</p>
        </div>
    </div>
</body>
</html>
