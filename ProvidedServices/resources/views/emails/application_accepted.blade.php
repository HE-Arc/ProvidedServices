<!DOCTYPE html>
<html>
<head>
    <title>Candidature Acceptée</title>
</head>
<body>
    <h1>Bonjour {{ $application->provider->first_name }},</h1>
    <p>Votre candidature pour l'annonce "{{ $application->jobPost->title }}" a été acceptée.</p>
    <p>Veuillez contacter le client pour plus de détails.</p>
    <p>Merci,</p>
    <p>L'équipe de notre plateforme.</p>
</body>
</html>
