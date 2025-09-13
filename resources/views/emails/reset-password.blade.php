@component('mail::message')
# Réinitialisez votre mot de passe

Bonjour {{ $user->name }},

Vous recevez cet email car nous avons reçu une demande de réinitialisation du mot de passe pour votre compte {{ $appName }}.

@component('mail::button', ['url' => $url])
Choisir un nouveau mot de passe
@endcomponent

Ce lien expirera bientôt pour votre sécurité. Si vous n'avez pas demandé cette action, aucune autre démarche n'est nécessaire.

Besoin d'aide ? Écrivez-nous à {{ $supportEmail }}.

Merci,
L'équipe {{ $appName }}
@endcomponent
