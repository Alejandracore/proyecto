<?php
session_start();

// 🔒 solo admin entra
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Nevekley</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
body {
    font-family: 'Inter', sans-serif;
}
</style>
</head>

<body class="bg-gray-900 text-white flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-gray-950 border-r border-pink-600/20 flex flex-col justify-between">

    <!-- LOGO -->
    <div class="p-6">
        <h1 class="text-pink-400 text-2xl font-bold">Nevekley</h1>
        <p class="text-gray-400 text-xs">ADMIN</p>

        <!-- NAV -->
        <nav class="mt-10 space-y-3">

            <a href="admin.php"
               class="block px-4 py-3 rounded bg-gradient-to-r from-pink-600 to-pink-400 text-white shadow-lg shadow-pink-600/30">
                Dashboard
            </a>

            <a href="admin_solicitudes.php"
               class="block px-4 py-3 rounded bg-gray-800 hover:bg-gray-700 text-gray-300">
                Solicitudes
            </a>

        </nav>
    </div>

    <!-- USER -->
    <div class="p-6 border-t border-gray-800">
        <p class="text-sm text-gray-300">
            <?= $_SESSION['nombre'] ?>
        </p>

        <p class="text-xs text-gray-500 mb-3">
            admin@sistema.com
        </p>

        <a href="logout.php"
           class="text-red-400 text-sm hover:text-red-300">
            Cerrar Sesión
        </a>
    </div>

</aside>

<!-- MAIN -->
<main class="flex-1 p-8">

    <!-- HEADER -->
    <div>
        <h1 class="text-3xl font-bold text-pink-400">Gestión de Solicitudes</h1>
        <p class="text-gray-400 mt-1">
            Administra todas las solicitudes de los usuarios
        </p>
    </div>

    <!-- FILTROS -->
    <div class="flex gap-3 mt-6 flex-wrap">

        <button class="px-4 py-2 rounded bg-pink-600 shadow-md shadow-pink-600/40">
            Todos
        </button>

        <button class="px-4 py-2 rounded bg-gray-800 hover:bg-gray-700">
            Pendiente
        </button>

        <button class="px-4 py-2 rounded bg-gray-800 hover:bg-gray-700">
            En proceso
        </button>

        <button class="px-4 py-2 rounded bg-gray-800 hover:bg-gray-700">
            Completado
        </button>

        <button class="px-4 py-2 rounded bg-gray-800 hover:bg-gray-700">
            Cancelado
        </button>

    </div>

    <!-- CONTENT BOX -->
    <div class="mt-8 bg-gray-800 border border-pink-600/30 rounded-xl p-6 shadow-lg shadow-pink-600/10">

        <p class="text-center text-gray-400">
            No hay solicitudes con ese filtro
        </p>

    </div>

</main>

</body>
</html>