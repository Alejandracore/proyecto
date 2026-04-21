<?php
include "conexion.php";

// Servicios fijos siempre presentes
$servicios_fijos = [
    "Desarrollo Web y Apps",
    "Automatización de Negocios",
    "Análisis de Datos",
    "Marketing Digital",
    "Hosting",
];

// Servicios dinámicos desde la BD
$servicios_db = [];
$res = mysqli_query($conexion, "SELECT nombre FROM servicios WHERE activo=1 ORDER BY creado_en ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $servicios_db[] = $row['nombre'];
    }
}

$todos_los_servicios = array_merge($servicios_fijos, $servicios_db);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Servicio - NevekLey</title>
</head>

<body>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="min-h-screen bg-gray-900 py-6 flex flex-col justify-center sm:py-12">
        <div class="relative py-3 sm:max-w-xl sm:mx-auto">

            <!-- FONDO -->
            <div class="absolute inset-0 bg-gradient-to-r from-pink-600 to-transparent shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>

            <!-- CARD -->
            <div class="text-white relative px-4 py-10 bg-gray-800 shadow-lg sm:rounded-3xl sm:p-20">

                <div class="text-center pb-6">
                    <h1 class="text-3xl text-pink-400 font-bold">Solicita tu Servicio</h1>
                    <p class="text-gray-400">
                        Completa el formulario para solicitar el servicio que necesitas. Nuestro equipo se pondrá en
                        contacto contigo lo antes posible.
                    </p>
                </div>

                <form action="guardar_solicitud.php" method="POST" id="form-solicitud">
                    <!-- campo oculto para saber de dónde viene el usuario -->
                    <input type="hidden" name="from" id="campo-from" value="">

                    <input
                        class="shadow mb-4 appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Nombre" name="nombre" required>

                    <input
                        class="shadow mb-4 appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline"
                        type="email" placeholder="Email" name="correo" required>

                    <input
                        class="shadow mb-4 appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline"
                        type="number" placeholder="Telefono" name="telefono" required>

                    <!-- SELECT de servicios dinámico -->
                    <select
                        class="shadow mb-4 appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline"
                        name="servicio" required>
                        <option disabled selected>Seleccione un servicio</option>
                        <?php foreach ($todos_los_servicios as $srv): ?>
                            <option value="<?= htmlspecialchars($srv) ?>"><?= htmlspecialchars($srv) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select
                        class="shadow mb-4 appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline"
                        name="tipo" required>
                        <option disabled selected>Tipo de solicitud</option>
                        <option>Consulta</option>
                        <option>Pedido de servicio</option>
                        <option>Soporte</option>
                    </select>

                    <textarea
                        class="shadow mb-4 min-h-0 appearance-none border border-gray-600 rounded h-64 w-full py-2 px-3 bg-gray-800 text-white placeholder-white leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Escriba su solicitud aqui" name="descripcion" style="height: 121px;"
                        required></textarea>

                    <div class="flex justify-between">
                        <input
                            class="shadow bg-pink-600 hover:bg-pink-500 text-white font-bold py-2 px-4 rounded shadow-pink-600/40 focus:outline-none focus:shadow-outline"
                            type="submit" value="Enviar ➤">
                        <input
                            class="shadow bg-pink-600 hover:bg-pink-500 text-white font-bold py-2 px-4 rounded shadow-pink-600/40 focus:outline-none focus:shadow-outline"
                            type="reset" value="Limpiar">
                    </div>

                    <div class="pt-5 text-left">
                        <!-- Mensaje de éxito -->
                        <div id="alerta-ok" class="hidden mb-4 bg-green-800/40 border border-green-500/40 text-green-400 px-4 py-3 rounded-lg text-sm">
                            ✅ ¡Solicitud enviada correctamente! Te contactaremos pronto.
                        </div>

                        <a id="btn-volver" href="Inicio.HTML"
                            class="text-white text-sm hover:text-pink-400 transition duration-300 inline-block">
                            ← Volver
                        </a>
                        <script>
                          const p = new URLSearchParams(window.location.search);

                          if (p.get('from') === 'panel') {
                            document.getElementById('campo-from').value = 'panel';
                            document.getElementById('btn-volver').href = 'usuario.php';
                            document.getElementById('btn-volver').textContent = '← Volver al panel';
                          }

                          if (p.get('enviado') === 'ok') {
                            document.getElementById('alerta-ok').classList.remove('hidden');
                            document.getElementById('form-solicitud').style.display = 'none';
                          }
                        </script>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>
</html>
