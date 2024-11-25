<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Profil</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <profile :user="{{ json_encode($user) }}" :auth-user-id="{{ json_encode($authUserId) }}"></profile>
    </div>
</body>
</html>