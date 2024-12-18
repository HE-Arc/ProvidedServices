<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature Accepté</title>
</head>
<body>
    <h1>Bonjour {{ $provider->first_name }},</h1>
    <p>Votre candidature pour l'annonce "{{ $jobPost->title }}" a été acceptée.</p>
    <p>Veuillez contacter le client pour plus de détails.</p>
    <p>Merci,</p>
    <p>L'équipe de notre plateforme.</p>
</body>
</html>
