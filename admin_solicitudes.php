<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html");
    exit();
}

include "conexion.php";

// ── Agregar columna 'estado' si aún no existe ──────────────────────────────
$col_check = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes LIKE 'estado'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conexion,
        "ALTER TABLE solicitudes ADD COLUMN estado ENUM('pendiente','en proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente'"
    );
}

// ── Contadores para el sidebar ─────────────────────────────────────────────
$total    = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes"))['total'];
$usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE rol='user'"))['total'];
$no_leidos = 0;
if (mysqli_num_rows(mysqli_query($conexion, "SHOW TABLES LIKE 'contactos'")) > 0) {
    $no_leidos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM contactos WHERE leido=0"))['t'];
}

// ── Filtro de estado (opcional) ────────────────────────────────────────────
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$where = '';
if (in_array($filtro_estado, ['pendiente','en proceso','completado','cancelado'])) {
    $fe = mysqli_real_escape_string($conexion, $filtro_estado);
    $where = "WHERE estado = '$fe'";
}

// Detectar nombre real de la columna PK
$pk_col = 'id';
$cols_res = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes");
while ($c = mysqli_fetch_assoc($cols_res)) {
    if ($c['Key'] === 'PRI') { $pk_col = $c['Field']; break; }
}

$result = mysqli_query($conexion, "SELECT * FROM solicitudes $where ORDER BY Fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Solicitudes - NevekLey Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }

  /* Sidebar */
  .nav-link { transition: background 0.2s, color 0.2s, padding-left 0.2s; }
  .nav-link:hover { padding-left: 1.25rem; }

  /* Ping avatar */
  .ping-dot { position: relative; }
  .ping-dot::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 8px; height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite;
  }
  @keyframes ping { 75%, 100% { transform: scale(2); opacity: 0; } }

  /* Animations */
  .fade-up { animation: fadeUp 0.5s ease forwards; opacity: 0; transform: translateY(14px); }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay: 0.08s; }
  .delay-2 { animation-delay: 0.16s; }
  .delay-3 { animation-delay: 0.24s; }

  /* Table rows */
  .tr-hover { transition: background 0.15s; }
  .tr-hover:hover { background: rgba(236,72,153,0.06); }

  /* Search */
  .search-input:focus { box-shadow: 0 0 0 2px rgba(236,72,153,0.4); }

  /* Status badges */
  .badge-pendiente   { background: rgba(234,179,8,0.15);  color: #facc15; }
  .badge-en-proceso  { background: rgba(59,130,246,0.15); color: #60a5fa; }
  .badge-completado  { background: rgba(34,197,94,0.15);  color: #4ade80; }
  .badge-cancelado   { background: rgba(239,68,68,0.15);  color: #f87171; }

  /* Filter pills */
  .pill { transition: all 0.2s; }
  .pill:hover { transform: translateY(-1px); }
  .pill-active { box-shadow: 0 0 0 2px rgba(236,72,153,0.5); }

  /* Manage button */
  .btn-gestionar {
    background: linear-gradient(135deg, #ec4899, #9333ea);
    transition: opacity 0.2s, transform 0.2s;
  }
  .btn-gestionar:hover { opacity: 0.85; transform: scale(1.04); }

  /* Glow border card */
  .glow-card {
    position: relative;
    border: 1px solid rgba(236,72,153,0.2);
  }
  .glow-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 0.75rem;
    padding: 1px;
    background: linear-gradient(135deg, #ec4899, #9333ea, #ec4899);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    animation: borderRotate 4s linear infinite;
    background-size: 200% 200%;
    pointer-events: none;
  }
  @keyframes borderRotate {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
</style>
</head>

<body class="bg-gray-950 text-white flex min-h-screen">

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="w-64 bg-gray-900 border-r border-pink-600/20 flex flex-col justify-between fixed h-full z-40">

  <div class="p-6">
    <!-- Logo -->
    <div class="flex items-center gap-2 mb-10">
      <img src="logo.png" class="w-9 h-9 rounded-lg object-contain" alt="Logo">
      <span class="text-xl font-bold">Neve<span class="text-pink-500">Kley</span></span>
    </div>

    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 px-2">Principal</p>

    <nav class="space-y-1">
      <a href="admin.php"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Dashboard
      </a>

      <a href="admin_solicitudes.php"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
        </svg>
        Solicitudes
        <?php if ($total > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total ?></span>
        <?php endif; ?>
      </a>

      <a href="admin_mensajes.php"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Mensajes
        <?php if ($no_leidos > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $no_leidos ?></span>
        <?php endif; ?>
      </a>
    </nav>
  </div>

  <!-- User info -->
  <div class="p-5 border-t border-gray-800">
    <div class="flex items-center gap-3 mb-4">
      <div class="ping-dot relative">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center text-sm font-bold">
          <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
        </div>
      </div>
      <div>
        <p class="text-sm font-semibold text-gray-200"><?= htmlspecialchars($_SESSION['nombre']) ?></p>
        <p class="text-xs text-gray-500">Administrador</p>
      </div>
    </div>
    <a href="logout.php"
       class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-900/20">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Cerrar Sesión
    </a>
  </div>
</aside>

<!-- ═══════════ MAIN ═══════════ -->
<main class="flex-1 ml-64 p-8 min-h-screen">

  <!-- Header -->
  <div class="flex items-center justify-between mb-8 fade-up">
    <div>
      <h1 class="text-3xl font-extrabold">Gestión de <span class="text-pink-500">Solicitudes</span></h1>
      <p class="text-gray-400 mt-1 text-sm">
        Total: <span class="text-pink-400 font-semibold"><?= $total ?></span> solicitudes ·
        <span class="text-purple-400 font-semibold"><?= $usuarios ?></span> usuarios
      </p>
    </div>
    <div class="text-right text-sm text-gray-500"><?= date('d/m/Y H:i') ?></div>
  </div>

  <!-- ── Resumen de estados ── -->
  <?php
    $est_counts = [];
    foreach (['pendiente','en proceso','completado','cancelado'] as $e) {
        $r = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as c FROM solicitudes WHERE estado='$e'"));
        $est_counts[$e] = $r['c'];
    }
  ?>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 fade-up delay-1">
    <!-- Pendiente -->
    <a href="?estado=pendiente" class="glow-card bg-gray-900 rounded-xl p-4 pill <?= $filtro_estado==='pendiente' ? 'pill-active' : '' ?>">
      <p class="text-xs text-gray-500 mb-1">Pendiente</p>
      <p class="text-2xl font-bold text-yellow-400"><?= $est_counts['pendiente'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-yellow-400/30"><div class="h-1 rounded-full bg-yellow-400" style="width:<?= $total>0 ? round($est_counts['pendiente']/$total*100) : 0 ?>%"></div></div>
    </a>
    <!-- En proceso -->
    <a href="?estado=en+proceso" class="glow-card bg-gray-900 rounded-xl p-4 pill <?= $filtro_estado==='en proceso' ? 'pill-active' : '' ?>">
      <p class="text-xs text-gray-500 mb-1">En Proceso</p>
      <p class="text-2xl font-bold text-blue-400"><?= $est_counts['en proceso'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-blue-400/30"><div class="h-1 rounded-full bg-blue-400" style="width:<?= $total>0 ? round($est_counts['en proceso']/$total*100) : 0 ?>%"></div></div>
    </a>
    <!-- Completado -->
    <a href="?estado=completado" class="glow-card bg-gray-900 rounded-xl p-4 pill <?= $filtro_estado==='completado' ? 'pill-active' : '' ?>">
      <p class="text-xs text-gray-500 mb-1">Completado</p>
      <p class="text-2xl font-bold text-green-400"><?= $est_counts['completado'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-green-400/30"><div class="h-1 rounded-full bg-green-400" style="width:<?= $total>0 ? round($est_counts['completado']/$total*100) : 0 ?>%"></div></div>
    </a>
    <!-- Cancelado -->
    <a href="?estado=cancelado" class="glow-card bg-gray-900 rounded-xl p-4 pill <?= $filtro_estado==='cancelado' ? 'pill-active' : '' ?>">
      <p class="text-xs text-gray-500 mb-1">Cancelado</p>
      <p class="text-2xl font-bold text-red-400"><?= $est_counts['cancelado'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-red-400/30"><div class="h-1 rounded-full bg-red-400" style="width:<?= $total>0 ? round($est_counts['cancelado']/$total*100) : 0 ?>%"></div></div>
    </a>
  </div>

  <!-- ── Barra de herramientas ── -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-5 fade-up delay-2">
    <input
      id="buscador"
      type="text"
      placeholder="🔍 Buscar por nombre, servicio o tipo..."
      oninput="filtrarTabla()"
      class="search-input flex-1 max-w-md bg-gray-900 border border-gray-700 text-gray-200 placeholder-gray-500 text-sm rounded-xl px-4 py-3 outline-none transition"
    >
    <?php if ($filtro_estado): ?>
      <a href="admin_solicitudes.php"
         class="text-xs px-4 py-2 rounded-lg bg-gray-800 text-gray-400 hover:text-pink-400 hover:bg-gray-700 transition flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpiar filtro
      </a>
    <?php endif; ?>
    <span class="ml-auto text-xs text-gray-600 hidden sm:block">
      Mostrando <?= mysqli_num_rows($result) ?> de <?= $total ?> solicitudes
    </span>
  </div>

  <!-- ── Tabla ── -->
  <div class="bg-gray-900 border border-pink-600/20 rounded-xl overflow-hidden shadow-xl fade-up delay-3">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="tabla-solicitudes">
        <thead class="bg-gray-950 text-gray-500 uppercase text-xs">
          <tr>
            <th class="px-5 py-4 text-left">#</th>
            <th class="px-5 py-4 text-left">Nombre</th>
            <th class="px-5 py-4 text-left">Servicio</th>
            <th class="px-5 py-4 text-left">Tipo</th>
            <th class="px-5 py-4 text-left">Fecha</th>
            <th class="px-5 py-4 text-left">Estado</th>
            <th class="px-5 py-4 text-center">Acción</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $n=1; while ($row = mysqli_fetch_assoc($result)): ?>

              <?php
                // Badge de estado
                $estado_val = $row['estado'] ?? 'pendiente';
                $badge_map  = [
                    'pendiente'  => ['cls' => 'badge-pendiente',  'label' => 'Pendiente'],
                    'en proceso' => ['cls' => 'badge-en-proceso', 'label' => 'En Proceso'],
                    'completado' => ['cls' => 'badge-completado', 'label' => 'Completado'],
                    'cancelado'  => ['cls' => 'badge-cancelado',  'label' => 'Cancelado'],
                ];
                $badge = $badge_map[$estado_val] ?? ['cls' => 'badge-pendiente', 'label' => ucfirst($estado_val)];
              ?>

              <tr class="tr-hover border-t border-gray-800" data-id="<?= $row[$pk_col] ?>">
                <td class="px-5 py-4 text-gray-600 text-xs"><?= $n++ ?></td>

                <!-- Nombre -->
                <td class="px-5 py-4 text-gray-200 font-medium">
                  <?= htmlspecialchars($row['Nombre']) ?>
                </td>

                <!-- Servicio -->
                <td class="px-5 py-4 text-gray-300">
                  <?= htmlspecialchars($row['servicio']) ?>
                </td>

                <!-- Tipo -->
                <td class="px-5 py-4">
                  <span class="px-2 py-1 bg-pink-600/15 text-pink-400 text-xs rounded-full font-medium">
                    <?= htmlspecialchars($row['tipo']) ?>
                  </span>
                </td>

                <!-- Fecha -->
                <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                  <?= htmlspecialchars($row['Fecha']) ?>
                </td>

                <!-- Estado -->
                <td class="px-5 py-4">
                  <span class="<?= $badge['cls'] ?> px-3 py-1 text-xs font-semibold rounded-full">
                    <?= $badge['label'] ?>
                  </span>
                </td>

                <!-- Botón Gestionar -->
                <td class="px-5 py-4 text-center">
                  <a href="gestionar_solicitud.php?id=<?= $row[$pk_col] ?>"
                     class="btn-gestionar inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white rounded-lg">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Gestionar
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-16 text-gray-600">
                <div class="flex flex-col items-center gap-3">
                  <svg class="w-10 h-10 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                  </svg>
                  <span>No hay solicitudes<?= $filtro_estado ? " con estado \"$filtro_estado\"" : '' ?></span>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script>
function filtrarTabla() {
  const q    = document.getElementById('buscador').value.toLowerCase();
  const rows = document.querySelectorAll('#tbody tr');
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
}
</script>

</body>
</html>