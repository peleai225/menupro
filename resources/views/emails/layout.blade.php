<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MenuPro')</title>
    <!--[if mso]>
    <style>table,td,div,p,span{font-family:Arial,sans-serif!important;}</style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;line-height:1.6;color:#1f2937;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;">
        <tr>
            <td align="center" style="padding:24px 16px;">
                <!-- Container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);padding:28px 32px;border-radius:12px 12px 0 0;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.3px;">
                                @yield('header', 'MenuPro')
                            </h1>
                            @hasSection('subtitle')
                            <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">
                                @yield('subtitle')
                            </p>
                            @endif
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background-color:#ffffff;padding:32px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    @hasSection('action_url')
                    <tr>
                        <td style="background-color:#ffffff;padding:0 32px 32px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;text-align:center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);border-radius:8px;">
                                        <a href="@yield('action_url')" target="_blank" style="display:inline-block;padding:14px 32px;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;letter-spacing:0.2px;">
                                            @yield('action_text', 'Voir les details')
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#fafafa;padding:24px 32px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 12px 12px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:13px;color:#6b7280;">
                                Cet email a ete envoye automatiquement par MenuPro.
                            </p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                &copy; {{ date('Y') }} MenuPro &mdash; La solution digitale pour votre restaurant
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
