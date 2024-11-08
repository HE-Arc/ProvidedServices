<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <profile :user="{{ json_encode($user) }}" :auth-user-id="{{ json_encode($authUserId) }}"></profile>
    </div>
</body>
</html>