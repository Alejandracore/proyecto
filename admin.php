<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html");
    exit();
}

include "conexion.php";

$total    = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes"))['total'];
$usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios WHERE rol='user'"))['total'];
// Mensajes no leídos (si la tabla existe)
$no_leidos = 0;
if (mysqli_query($conexion, "SHOW TABLES LIKE 'contactos'") && mysqli_num_rows(mysqli_query($conexion, "SHOW TABLES LIKE 'contactos'")) > 0) {
    $no_leidos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM contactos WHERE leido=0"))['t'];
}

// Agregar columna 'estado' si aún no existe
$col_check = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes LIKE 'estado'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conexion,
        "ALTER TABLE solicitudes ADD COLUMN estado ENUM('pendiente','en proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente'"
    );
}

// Últimas 5 solicitudes
$ultimas = mysqli_query($conexion, "SELECT * FROM solicitudes ORDER BY Fecha DESC LIMIT 5");

// Detectar nombre real de la columna PK
$pk_col = 'id';
$cols_res = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes");
while ($c = mysqli_fetch_assoc($cols_res)) {
    if ($c['Key'] === 'PRI') { $pk_col = $c['Field']; break; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - NevekLey</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }

  /* Sidebar glow */
  .sidebar-logo { text-shadow: 0 0 20px rgba(236,72,153,0.5); }

  /* Cards */
  .stat-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(236,72,153,0.2);
  }

  /* Sidebar links */
  .nav-link {
    transition: background 0.2s, color 0.2s, padding-left 0.2s;
  }
  .nav-link:hover {
    padding-left: 1.25rem;
  }

  /* Animated gradient border */
  .glow-border {
    position: relative;
  }
  .glow-border::before {
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
  }
  @keyframes borderRotate {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* Ping dot */
  .ping-dot::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 8px; height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite;
  }
  @keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
  }

  /* Row hover */
  .tr-hover { transition: background 0.15s; }
  .tr-hover:hover { background: rgba(236,72,153,0.06); }

  /* Fade in */
  .fade-up {
    animation: fadeUp 0.5s ease forwards;
    opacity: 0; transform: translateY(16px);
  }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }
</style>
</head>

<body class="bg-gray-950 text-white flex min-h-screen">

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="w-64 bg-gray-900 border-r border-pink-600/20 flex flex-col justify-between fixed h-full z-40">

  <div class="p-6">
    <!-- Logo -->
    <div class="flex items-center gap-2 mb-10">
      <img src="logo.png" class="w-9 h-9 rounded-lg object-contain" alt="Logo">
      <span class="text-xl font-bold sidebar-logo">Neve<span class="text-pink-500">Kley</span></span>
    </div>

    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 px-2">Principal</p>

    <nav class="space-y-1">
      <a href="admin.php" id="nav-dashboard"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Dashboard
      </a>

      <a href="admin_solicitudes.php" id="nav-solicitudes"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
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

      <a href="admin_servicios.php"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Servicios
      </a>
    </nav>
  </div>

  <!-- User info -->
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
      <h1 class="text-3xl font-extrabold text-white">Panel de <span class="text-pink-500">Administración</span></h1>
      <p class="text-gray-400 mt-1 text-sm">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>. Aquí está el resumen del sistema.</p>
    </div>
    <div class="text-right text-sm text-gray-500">
      <?= date('d/m/Y H:i') ?>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">

    <!-- Total Solicitudes -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-6 fade-up delay-1">
      <div class="flex items-center justify-between mb-4">
        <p class="text-gray-400 text-sm font-medium">Total Solicitudes</p>
        <div class="w-10 h-10 rounded-lg bg-pink-600/20 flex items-center justify-center">
          <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
          </svg>
        </div>
      </div>
      <p class="text-5xl font-extrabold text-pink-400"><?= $total ?></p>
      <p class="text-xs text-gray-500 mt-2">solicitudes registradas</p>
    </div>

    <!-- Usuarios Registrados -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-6 fade-up delay-2">
      <div class="flex items-center justify-between mb-4">
        <p class="text-gray-400 text-sm font-medium">Usuarios Registrados</p>
        <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
          <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
      </div>
      <p class="text-5xl font-extrabold text-purple-400"><?= $usuarios ?></p>
      <p class="text-xs text-gray-500 mt-2">usuarios en el sistema</p>
    </div>

  </div>

  <!-- Últimas solicitudes -->
  <div class="bg-gray-900 border border-pink-600/20 rounded-xl shadow-xl overflow-hidden fade-up delay-3">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
      <h2 class="font-semibold text-gray-200">Últimas solicitudes</h2>
      <a href="admin_solicitudes.php" class="text-pink-400 text-sm hover:text-pink-300 transition">Ver todas →</a>
    </div>

    <table class="w-full text-sm">
      <thead class="bg-gray-950 text-gray-500 uppercase text-xs">
        <tr>
          <th class="px-6 py-3 text-left">Nombre</th>
          <th class="px-6 py-3 text-left">Servicio</th>
          <th class="px-6 py-3 text-left">Tipo</th>
          <th class="px-6 py-3 text-left">Fecha</th>
          <th class="px-6 py-3 text-left">Estado</th>
          <th class="px-6 py-3 text-center">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($ultimas) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($ultimas)): ?>
            <?php
              $estado_val = $row['estado'] ?? 'pendiente';
              $badge_map  = [
                  'pendiente'  => ['cls' => 'bg-yellow-400/15 text-yellow-400', 'label' => 'Pendiente'],
                  'en proceso' => ['cls' => 'bg-blue-400/15 text-blue-400',   'label' => 'En Proceso'],
                  'completado' => ['cls' => 'bg-green-400/15 text-green-400', 'label' => 'Completado'],
                  'cancelado'  => ['cls' => 'bg-red-400/15 text-red-400',     'label' => 'Cancelado'],
              ];
              $badge = $badge_map[$estado_val] ?? ['cls' => 'bg-yellow-400/15 text-yellow-400', 'label' => ucfirst($estado_val)];
            ?>
            <tr class="tr-hover border-t border-gray-800">
              <td class="px-6 py-4 text-gray-200 font-medium"><?= htmlspecialchars($row['Nombre']) ?></td>
              <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($row['servicio']) ?></td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 bg-pink-600/15 text-pink-400 text-xs rounded-full">
                  <?= htmlspecialchars($row['tipo']) ?>
                </span>
              </td>
              <td class="px-6 py-4 text-gray-500 text-xs"><?= htmlspecialchars($row['Fecha']) ?></td>
              <td class="px-6 py-4">
                <span class="<?= $badge['cls'] ?> px-3 py-1 text-xs font-semibold rounded-full">
                  <?= $badge['label'] ?>
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <a href="gestionar_solicitud.php?id=<?= $row[$pk_col] ?>"
                   style="background:linear-gradient(135deg,#ec4899,#9333ea)"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white rounded-lg hover:opacity-80 transition">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <td colspan="6" class="text-center py-12 text-gray-600">
              No hay solicitudes todavía
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

</body>
</html>
