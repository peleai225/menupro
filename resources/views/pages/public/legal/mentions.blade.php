<x-layouts.public title="Mentions légales" description="Mentions légales de MenuPro - informations sur l'éditeur, l'hébergeur et les droits de propriété intellectuelle.">
    <div class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl font-bold text-neutral-900 mb-8">Mentions légales</h1>
            <p class="text-neutral-500 mb-8">Dernière mise à jour : {{ date('d/m/Y') }}</p>

            <div class="prose prose-lg max-w-none">
                <h2>1. Éditeur du site</h2>
                <p>
                    Le site MenuPro est édité par PeleAI, agence spécialisée dans le développement de solutions digitales.
                </p>
                <ul>
                    <li><strong>Raison sociale :</strong> PeleAI</li>
                    <li><strong>Site web :</strong> <a href="https://peleai.online" target="_blank" rel="noopener">peleai.online</a></li>
                    <li><strong>Pays :</strong> Côte d'Ivoire</li>
                    <li><strong>Email de contact :</strong> <a href="mailto:contact@menupro.ci">contact@menupro.ci</a></li>
                </ul>

                <h2>2. Hébergement</h2>
                <p>
                    Le site est hébergé par TPEcloud.
                </p>

                <h2>3. Propriété intellectuelle</h2>
                <p>
                    L'ensemble des éléments constituant le site MenuPro (textes, graphismes, logiciels, images, vidéos, sons, plans, logos, marques, bases de données, etc.)
                    est protégé par les lois en vigueur en matière de propriété intellectuelle.
                </p>
                <p>
                    Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site,
                    quel que soit le moyen ou le procédé utilisé, est interdite sauf autorisation écrite préalable de PeleAI.
                </p>

                <h2>4. Données personnelles</h2>
                <p>
                    Les informations recueillies sur ce site sont traitées conformément à notre
                    <a href="{{ route('privacy') }}">politique de confidentialité</a>.
                    Conformément à la réglementation applicable, vous disposez d'un droit d'accès, de rectification, de suppression
                    et d'opposition aux données personnelles vous concernant.
                </p>
                <p>
                    Pour exercer ces droits, vous pouvez nous contacter via notre
                    <a href="{{ route('contact') }}">formulaire de contact</a> ou par email.
                </p>

                <h2>5. Cookies</h2>
                <p>
                    Le site MenuPro utilise des cookies pour améliorer l'expérience utilisateur.
                    Pour plus d'informations sur l'utilisation des cookies, veuillez consulter notre
                    <a href="{{ route('privacy') }}">politique de confidentialité</a>.
                </p>

                <h2>6. Limitation de responsabilité</h2>
                <p>
                    MenuPro s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site.
                    Toutefois, MenuPro ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition.
                </p>
                <p>
                    MenuPro décline toute responsabilité pour tout dommage résultant d'une intrusion frauduleuse d'un tiers
                    ayant entraîné une modification des informations mises à disposition sur le site.
                </p>

                <h2>7. Droit applicable</h2>
                <p>
                    Les présentes mentions légales sont soumises au droit ivoirien.
                    En cas de litige, les tribunaux d'Abidjan seront seuls compétents.
                </p>
            </div>

            <div class="mt-12 pt-8 border-t border-neutral-200">
                <a href="{{ url('/') }}" class="text-primary-500 hover:text-primary-600 transition-colors font-medium">
                    &larr; Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
