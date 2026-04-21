<?php
include "conexion.php";

// Crear tabla si no existe
mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    icono VARCHAR(50) DEFAULT 'default',
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$servicios = mysqli_query($conexion, "SELECT * FROM servicios WHERE activo=1 ORDER BY creado_en ASC");

$iconos_path = [
    'web'       => 'M16 18l6-6-6-6M8 6l-6 6 6 6',
    'auto'      => 'M9.75 3a1.5 1.5 0 014.5 0v1.05a6.978 6.978 0 012.25.94l.74-.74a1.5 1.5 0 112.12 2.12l-.74.74A6.978 6.978 0 0121 9.75H21a1.5 1.5 0 110 3h-1.05a6.978 6.978 0 01-.94 2.25l.74.74a1.5 1.5 0 11-2.12 2.12l-.74-.74a6.978 6.978 0 01-2.25.94V21a1.5 1.5 0 11-3 0v-1.05a6.978 6.978 0 01-2.25-.94l-.74.74a1.5 1.5 0 11-2.12-2.12l.74-.74A6.978 6.978 0 013 12.75H3a1.5 1.5 0 110-3h1.05a6.978 6.978 0 01.94-2.25l-.74-.74a1.5 1.5 0 112.12-2.12l.74.74A6.978 6.978 0 019.75 4.05V3z',
    'datos'     => 'M3 3v18h18M9 17V9m4 8V5m4 12v-6',
    'marketing' => 'M11 5l9-2v14l-9-2v-10zM11 5l-6 3v8l6 3',
    'hosting'   => 'M4 6h16M4 12h16M4 18h16',
    'default'   => 'M13 10V3L4 14h7v7l9-11h-7z',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - NevekLey</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    <section>
        <div class="relative items-center w-full px-5 py-12 mx-auto md:px-12 lg:px-24 max-w-7xl">

            <h1 class="text-3xl font-bold text-center text-pink-400 mb-12">
                Nuestros Servicios
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Servicios fijos -->
                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Desarrollo Web y Apps</h2>
                    <p class="text-gray-400">Creamos páginas web y aplicaciones modernas adaptadas a tus necesidades.</p>
                </div>

                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3a1.5 1.5 0 014.5 0v1.05a6.978 6.978 0 012.25.94l.74-.74a1.5 1.5 0 112.12 2.12l-.74.74A6.978 6.978 0 0121 9.75H21a1.5 1.5 0 110 3h-1.05a6.978 6.978 0 01-.94 2.25l.74.74a1.5 1.5 0 11-2.12 2.12l-.74-.74a6.978 6.978 0 01-2.25.94V21a1.5 1.5 0 11-3 0v-1.05a6.978 6.978 0 01-2.25-.94l-.74.74a1.5 1.5 0 11-2.12-2.12l.74-.74A6.978 6.978 0 013 12.75H3a1.5 1.5 0 110-3h1.05a6.978 6.978 0 01.94-2.25l-.74-.74a1.5 1.5 0 112.12-2.12l.74.74A6.978 6.978 0 019.75 4.05V3z"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Automatización de Negocios</h2>
                    <p class="text-gray-400">Optimizamos procesos para hacer tu negocio más eficiente y rentable.</p>
                </div>

                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M9 17V9m4 8V5m4 12v-6"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Análisis de Datos</h2>
                    <p class="text-gray-400">Transformamos datos en información útil para la toma de decisiones.</p>
                </div>

                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5l9-2v14l-9-2v-10zM11 5l-6 3v8l6 3"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Marketing Digital</h2>
                    <p class="text-gray-400">Impulsamos tu marca en internet con estrategias efectivas.</p>
                </div>

                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Servicios de Hosting</h2>
                    <p class="text-gray-400">Ofrecemos alojamiento seguro y rápido para tus proyectos web.</p>
                </div>

                <!-- Servicios dinámicos agregados por el admin (ANTES del Próximamente) -->
                <?php while ($s = mysqli_fetch_assoc($servicios)): ?>
                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg hover:shadow-pink-600/40 transition">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="<?= htmlspecialchars($iconos_path[$s['icono']] ?? $iconos_path['default']) ?>"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2"><?= htmlspecialchars($s['nombre']) ?></h2>
                    <p class="text-gray-400"><?= htmlspecialchars($s['descripcion']) ?></p>
                </div>
                <?php endwhile; ?>

                <!-- Próximamente — siempre al final -->
                <div class="p-6 bg-gray-800 rounded-2xl shadow-lg border-2 border-dashed border-pink-500 hover:shadow-pink-600/40 transition opacity-80">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-600 mb-4 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-semibold text-pink-400 mb-2">Próximamente</h2>
                    <p class="text-gray-400">Estamos trabajando en nuevos servicios que estarán disponibles muy pronto.</p>
                </div>

            </div>

            <div class="mt-16 mb-16 flex flex-col sm:flex-row justify-center gap-4">
                <a id="btn-volver" href="Inicio.HTML"
                    class="bg-gray-700 hover:bg-gray-600 px-8 py-3 rounded-lg font-semibold transition shadow-lg hover:scale-105">
                    ← Volver
                </a>
                <a id="btn-solicitar" href="solicitar_servicio.php"
                    class="bg-pink-600 hover:bg-pink-700 px-8 py-3 rounded-lg font-semibold transition shadow-lg shadow-pink-600/40 hover:scale-105">
                    Solicitar servicio
                </a>
                <script>
                  const params = new URLSearchParams(window.location.search);
                  if (params.get('from') === 'panel') {
                    document.getElementById('btn-volver').href = 'usuario.php';
                    document.getElementById('btn-volver').textContent = '← Volver al panel';
                    document.getElementById('btn-solicitar').href = 'solicitar_servicio.php?from=panel';
                  }
                </script>
            </div>
        </div>
    </section>

</body>
</html>
