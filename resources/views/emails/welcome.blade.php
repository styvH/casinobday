@component('mail::message')
# Bienvenue, {{ $user->name }} 👋

Merci d'avoir rejoint {{ $appName }}.

- Profitez de jeux immersifs et d'une expérience premium.
- Gagnez des récompenses et grimpez dans le classement.

@component('mail::button', ['url' => $loginUrl])
Se connecter
@endcomponent

Si vous n'êtes pas à l'origine de cette création de compte, ignorez simplement cet email.

Merci,
L'équipe {{ $appName }}
@endcomponent
