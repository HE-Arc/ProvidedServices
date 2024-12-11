<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Inclusion des ressources Vue et CSS générées par Vite -->
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        #app {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <!-- Script pour injecter des données globales à VueJS -->
    <script>
        window.userRole = @json(auth()->user()->role);
        window.userId = @json(auth()->user()->id);
    </script>

    <!-- Conteneur Vue avec le Dashboard -->
    <div id="app">
        <dashboard-component></dashboard-component>
    </div>
</body>
</html>
