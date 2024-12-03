<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    @vite(['resources/js/app.js', 'resources/css/app.css']) <!-- Assure-toi d'inclure le JS et le CSS -->
</head>
<body>
    <script>
        window.userRole = @json(auth()->user()->role);
        window.userId = @json(auth()->user()->id);
    </script>
    <div id="app">
        <dashboard-component></dashboard-component>
    </div>
</body>
</html>
