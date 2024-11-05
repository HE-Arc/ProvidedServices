<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <profile :user="{{ json_encode($user) }}">></profile>
    </div>
</body>
</html>