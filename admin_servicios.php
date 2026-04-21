<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html"); exit();
}
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

// Eliminar servicio
if (isset($_GET['eliminar'])) {
    $eid = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM servicios WHERE id=$eid");
    header("Location: admin_servicios.php?ok=eliminado"); exit();
}

$servicios = mysqli_query($conexion, "SELECT * FROM servicios ORDER BY creado_en DESC");

$total    = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes"))['total'];
$no_leidos = 0;
if (mysqli_num_rows(mysqli_query($conexion, "SHOW TABLES LIKE 'contactos'")) > 0)
    $no_leidos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM contactos WHERE leido=0"))['t'];

$iconos = [
    'default'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    'web'       => 'M16 18l6-6-6-6M8 6l-6 6 6 6',
    'datos'     => 'M3 3v18h18M9 17V9m4 8V5m4 12v-6',
    'marketing' => 'M11 5l9-2v14l-9-2v-10zM11 5l-6 3v8l6 3',
    'hosting'   => 'M4 6h16M4 12h16M4 18h16',
    'auto'      => 'M9.75 3a1.5 1.5 0 014.5 0v1.05a6.978 6.978 0 012.25.94l.74-.74a1.5 1.5 0 112.12 2.12l-.74.74A6.978 6.978 0 0121 9.75H21a1.5 1.5 0 110 3h-1.05a6.978 6.978 0 01-.94 2.25l.74.74a1.5 1.5 0 11-2.12 2.12l-.74-.74a6.978 6.978 0 01-2.25.94V21a1.5 1.5 0 11-3 0v-1.05a6.978 6.978 0 01-2.25-.94l-.74.74a1.5 1.5 0 11-2.12-2.12l.74-.74A6.978 6.978 0 013 12.75H3a1.5 1.5 0 110-3h1.05a6.978 6.978 0 01.94-2.25l-.74-.74a1.5 1.5 0 112.12-2.12l.74.74A6.978 6.978 0 019.75 4.05V3z',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestionar Servicios - NevekLey</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .sidebar-logo { text-shadow: 0 0 20px rgba(236,72,153,0.5); }
  .nav-link { transition: background 0.2s, color 0.2s, padding-left 0.2s; }
  .nav-link:hover { padding-left: 1.25rem; }
  .ping-dot::after { content:''; position:absolute; top:0; right:0; width:8px; height:8px; background:#22c55e; border-radius:50%; animation:ping 1.5s cubic-bezier(0,0,0.2,1) infinite; }
  @keyframes ping { 75%,100%{ transform:scale(2); opacity:0; } }
  .fade-up { animation:fadeUp 0.5s ease forwards; opacity:0; transform:translateY(16px); }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
  .card-serv { transition: transform .2s, box-shadow .2s; }
  .card-serv:hover { transform:translateY(-3px); box-shadow:0 12px 30px rgba(236,72,153,.18); }
</style>
</head>
<body class="bg-gray-950 text-white flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-gray-900 border-r border-pink-600/20 flex flex-col justify-between fixed h-full z-40">
  <div class="p-6">
    <div class="flex items-center gap-2 mb-10">
      <img src="logo.png" class="w-9 h-9 rounded-lg object-contain" alt="Logo">
      <span class="text-xl font-bold sidebar-logo">Neve<span class="text-pink-500">Kley</span></span>
    </div>
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 px-2">Principal</p>
    <nav class="space-y-1">
      <a href="admin.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        Dashboard
      </a>
      <a href="admin_solicitudes.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Solicitudes
        <?php if ($total > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total ?></span>
        <?php endif; ?>
      </a>
      <a href="admin_mensajes.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Mensajes
        <?php if ($no_leidos > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $no_leidos ?></span>
        <?php endif; ?>
      </a>
      <a href="admin_servicios.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Servicios
      </a>
    </nav>
  </div>
  <div class="p-5 border-t border-gray-800">
    <div class="flex items-center gap-3 mb-4">
      <div class="relative ping-dot">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center text-sm font-bold">
          <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
        </div>
      </div>
      <div>
        <p class="text-sm font-semibold text-gray-200"><?= htmlspecialchars($_SESSION['nombre']) ?></p>
        <p class="text-xs text-gray-500">Administrador</p>
      </div>
    </div>
    <a href="logout.php" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-900/20">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Cerrar Sesión
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="flex-1 ml-64 p-8 min-h-screen">

  <div class="flex items-center justify-between mb-8 fade-up">
    <div>
      <h1 class="text-3xl font-extrabold text-white">Gestionar <span class="text-pink-500">Servicios</span></h1>
      <p class="text-gray-400 mt-1 text-sm">Agrega o elimina servicios que se muestran en la página pública.</p>
    </div>
  </div>

  <!-- Alertas -->
  <?php if (isset($_GET['ok'])): ?>
    <div id="alerta" class="mb-6 flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 px-5 py-3 rounded-xl">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <?= $_GET['ok'] === 'eliminado' ? 'Servicio eliminado correctamente.' : 'Servicio agregado correctamente.' ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div id="alerta" class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 px-5 py-3 rounded-xl">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      <?= $_GET['error'] === 'campos_vacios' ? 'Por favor completa todos los campos.' : 'Error al guardar en la base de datos.' ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Formulario -->
    <div class="lg:col-span-1">
      <div class="bg-gray-900 border border-pink-600/20 rounded-2xl p-6 fade-up">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
          <span class="w-8 h-8 rounded-lg bg-pink-600/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          </span>
          Nuevo Servicio
        </h2>
        <form action="guardar_servicio.php" method="POST" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1 font-medium">Nombre del servicio *</label>
            <input type="text" name="nombre" required placeholder="Ej: Diseño Gráfico"
              class="w-full bg-gray-800 border border-gray-700 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 rounded-xl px-4 py-2.5 text-sm text-white outline-none transition">
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1 font-medium">Descripción *</label>
            <textarea name="descripcion" required rows="4" placeholder="Describe brevemente el servicio..."
              class="w-full bg-gray-800 border border-gray-700 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 rounded-xl px-4 py-2.5 text-sm text-white outline-none transition resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1 font-medium">Ícono</label>
            <select name="icono"
              class="w-full bg-gray-800 border border-gray-700 focus:border-pink-500 rounded-xl px-4 py-2.5 text-sm text-white outline-none transition">
              <option value="web">💻 Desarrollo Web / Apps</option>
              <option value="auto">⚙️ Automatización</option>
              <option value="datos">📊 Análisis de Datos</option>
              <option value="marketing">📣 Marketing Digital</option>
              <option value="hosting">🌐 Hosting</option>
              <option value="default" selected>✨ Genérico</option>
            </select>
          </div>
          <button type="submit"
            class="w-full py-3 rounded-xl font-semibold text-sm text-white transition hover:opacity-90 hover:scale-[1.02]"
            style="background:linear-gradient(135deg,#ec4899,#9333ea)">
            ➕ Agregar Servicio
          </button>
        </form>
      </div>
    </div>

    <!-- Lista de servicios -->
    <div class="lg:col-span-2 space-y-4 fade-up">
      <h2 class="text-lg font-bold text-white mb-2">Servicios activos <span class="text-gray-500 text-sm font-normal">(se muestran en servicios.php)</span></h2>

      <?php if (mysqli_num_rows($servicios) === 0): ?>
        <div class="bg-gray-900 border border-dashed border-gray-700 rounded-2xl p-10 text-center text-gray-500">
          <p class="text-4xl mb-3">📋</p>
          <p>No hay servicios personalizados todavía.<br>Agrega uno con el formulario.</p>
        </div>
      <?php else: ?>
        <?php while ($s = mysqli_fetch_assoc($servicios)): ?>
          <div class="card-serv bg-gray-900 border border-pink-600/10 rounded-2xl p-5 flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
              <div class="w-11 h-11 rounded-xl bg-pink-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6 text-white">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="<?= htmlspecialchars($iconos[$s['icono']] ?? $iconos['default']) ?>"/>
                </svg>
              </div>
              <div>
                <p class="font-semibold text-white"><?= htmlspecialchars($s['nombre']) ?></p>
                <p class="text-gray-400 text-sm mt-0.5"><?= htmlspecialchars($s['descripcion']) ?></p>
                <p class="text-gray-600 text-xs mt-2">Agregado: <?= date('d/m/Y H:i', strtotime($s['creado_en'])) ?></p>
              </div>
            </div>
            <a href="admin_servicios.php?eliminar=<?= $s['id'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar este servicio?')"
               class="shrink-0 text-red-400 hover:text-red-300 hover:bg-red-900/20 rounded-lg p-2 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
// Auto-ocultar alerta
const alerta = document.getElementById('alerta');
if (alerta) setTimeout(() => alerta.style.opacity = '0', 3000);
</script>
</body>
</html>
