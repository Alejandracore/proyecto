<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: plataforma.html");
    exit();
}
if ($_SESSION['rol'] == "admin") {
    header("Location: admin.php");
    exit();
}

include "conexion.php";

// Agregar columna 'estado' si aún no existe
$col_check = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes LIKE 'estado'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conexion,
        "ALTER TABLE solicitudes ADD COLUMN estado ENUM('pendiente','en proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente'"
    );
}

$id_usuario = $_SESSION['id'];
$total_sql  = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE id_Usuarios='$id_usuario'");
$total      = mysqli_fetch_assoc($total_sql)['total'];

// Contadores por estado
$cnt = [];
foreach (['pendiente','en proceso','completado','cancelado'] as $e) {
    $r = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT COUNT(*) as c FROM solicitudes WHERE id_Usuarios='$id_usuario' AND estado='$e'"
    ));
    $cnt[$e] = $r['c'];
}

$sql    = "SELECT * FROM solicitudes WHERE id_Usuarios='$id_usuario' ORDER BY Fecha DESC";
$result = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Panel - NevekLey</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }

  .nav-link { transition: background 0.2s, color 0.2s, padding-left 0.2s; }
  .nav-link:hover { padding-left: 1.25rem; }

  .stat-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(236,72,153,0.15);
  }

  .tr-hover { transition: background 0.15s; }
  .tr-hover:hover { background: rgba(236,72,153,0.06); }

  /* Glow border animado */
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
    background-size: 200%;
    animation: borderRotate 4s linear infinite;
  }
  @keyframes borderRotate {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* Online dot */
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

  /* Fade animations */
  .fade-up { animation: fadeUp 0.5s ease forwards; opacity: 0; transform: translateY(16px); }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }

  /* Empty state */
  .empty-icon { animation: float 3s ease-in-out infinite; }
  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
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

    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 px-2">Mi cuenta</p>

    <nav class="space-y-1">
      <a href="usuario.php"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Mis Solicitudes
        <?php if ($total > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total ?></span>
        <?php endif; ?>
      </a>

      <a href="solicitar_servicio.php?from=panel"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva Solicitud
      </a>

      <a href="servicios.php?from=panel"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
        </svg>
        Servicios
      </a>

      <a href="Contactos.HTML?from=panel"
         class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Contacto
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
        <p class="text-xs text-gray-500">Usuario</p>
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
      <h1 class="text-3xl font-extrabold">Hola, <span class="text-pink-500"><?= htmlspecialchars($_SESSION['nombre']) ?></span> 👋</h1>
      <p class="text-gray-400 mt-1 text-sm">Aquí puedes gestionar y hacer seguimiento de tus solicitudes</p>
    </div>
    <a href="solicitar_servicio.php?from=panel"
       class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-pink-600 to-pink-500 hover:from-pink-500 hover:to-pink-400 rounded-xl text-white text-sm font-semibold shadow-lg shadow-pink-600/30 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Nueva Solicitud
    </a>
  </div>

  <!-- Stat Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">

    <!-- Pendiente -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-4 fade-up delay-1">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs text-gray-500">Pendiente</p>
        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse"></span>
      </div>
      <p class="text-3xl font-extrabold text-yellow-400"><?= $cnt['pendiente'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-yellow-400/20">
        <div class="h-1 rounded-full bg-yellow-400" style="width:<?= $total>0 ? round($cnt['pendiente']/$total*100) : 0 ?>%"></div>
      </div>
    </div>

    <!-- En Proceso -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-4 fade-up delay-1">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs text-gray-500">En Proceso</p>
        <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-pulse"></span>
      </div>
      <p class="text-3xl font-extrabold text-blue-400"><?= $cnt['en proceso'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-blue-400/20">
        <div class="h-1 rounded-full bg-blue-400" style="width:<?= $total>0 ? round($cnt['en proceso']/$total*100) : 0 ?>%"></div>
      </div>
    </div>

    <!-- Completado -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-4 fade-up delay-2">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs text-gray-500">Completado</p>
        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
      </div>
      <p class="text-3xl font-extrabold text-green-400"><?= $cnt['completado'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-green-400/20">
        <div class="h-1 rounded-full bg-green-400" style="width:<?= $total>0 ? round($cnt['completado']/$total*100) : 0 ?>%"></div>
      </div>
    </div>

    <!-- Cancelado -->
    <div class="stat-card glow-border bg-gray-900 rounded-xl p-4 fade-up delay-2">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs text-gray-500">Cancelado</p>
        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
      </div>
      <p class="text-3xl font-extrabold text-red-400"><?= $cnt['cancelado'] ?></p>
      <div class="mt-2 h-1 rounded-full bg-red-400/20">
        <div class="h-1 rounded-full bg-red-400" style="width:<?= $total>0 ? round($cnt['cancelado']/$total*100) : 0 ?>%"></div>
      </div>
    </div>

  </div>

  <!-- Alerta de solicitud enviada -->
  <?php if (isset($_GET['solicitud']) && $_GET['solicitud'] == 'ok'): ?>
    <div class="mb-6 flex items-center gap-3 bg-green-900/20 border border-green-600/30 text-green-400 px-5 py-3 rounded-xl fade-up">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      ¡Solicitud enviada correctamente! Te contactaremos pronto.
    </div>
  <?php endif; ?>

  <!-- Tabla de solicitudes -->
  <div class="bg-gray-900 border border-pink-600/20 rounded-xl overflow-hidden shadow-xl fade-up delay-2">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
      <h2 class="font-semibold text-gray-200">Historial de Solicitudes</h2>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-950 text-gray-500 uppercase text-xs">
            <tr>
              <th class="px-6 py-3 text-left">#</th>
              <th class="px-6 py-3 text-left">Servicio</th>
              <th class="px-6 py-3 text-left">Tipo</th>
              <th class="px-6 py-3 text-left">Descripción</th>
              <th class="px-6 py-3 text-left">Teléfono</th>
              <th class="px-6 py-3 text-left">Fecha</th>
              <th class="px-6 py-3 text-left">Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php $n=1; while ($row = mysqli_fetch_assoc($result)): ?>
              <?php
                $estado_val = strtolower(trim($row['estado'] ?? 'pendiente'));
                $badge_map = [
                    'pendiente'  => [
                        'cls'  => 'bg-yellow-400/15 text-yellow-400',
                        'dot'  => 'bg-yellow-400 animate-pulse',
                        'label'=> 'Pendiente',
                        'icon' => '&#9200;'  // ⏰
                    ],
                    'en proceso' => [
                        'cls'  => 'bg-blue-400/15 text-blue-400',
                        'dot'  => 'bg-blue-400 animate-pulse',
                        'label'=> 'En Proceso',
                        'icon' => '&#9881;'  // ⚙
                    ],
                    'completado' => [
                        'cls'  => 'bg-green-400/15 text-green-400',
                        'dot'  => 'bg-green-400',
                        'label'=> 'Completado',
                        'icon' => '&#10003;'  // ✓
                    ],
                    'cancelado'  => [
                        'cls'  => 'bg-red-400/15 text-red-400',
                        'dot'  => 'bg-red-400',
                        'label'=> 'Cancelado',
                        'icon' => '&#10007;'  // ✗
                    ],
                ];
                $b = $badge_map[$estado_val] ?? $badge_map['pendiente'];
              ?>
              <tr class="tr-hover border-t border-gray-800">
                <td class="px-6 py-4 text-gray-600 text-xs"><?= $n++ ?></td>
                <td class="px-6 py-4 text-gray-200 font-medium"><?= htmlspecialchars($row['servicio']) ?></td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 bg-pink-600/15 text-pink-400 text-xs rounded-full font-medium">
                    <?= htmlspecialchars($row['tipo']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-400 max-w-xs truncate"><?= htmlspecialchars(substr($row['descripción'] ?? '', 0, 55)) ?>...</td>
                <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($row['telefono']) ?></td>
                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap"><?= htmlspecialchars($row['Fecha']) ?></td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $b['cls'] ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $b['dot'] ?>"></span>
                    <?= $b['label'] ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="empty-icon text-6xl mb-4">📋</div>
        <h3 class="text-gray-300 text-lg font-semibold mb-2">Aún no tienes solicitudes</h3>
        <p class="text-gray-500 text-sm mb-6">Empieza solicitando uno de nuestros servicios</p>
        <a href="solicitar_servicio.php?from=panel"
           class="px-6 py-2.5 bg-pink-600 hover:bg-pink-500 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-pink-600/30">
          Crear mi primera solicitud
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Banner CTA -->
  <div class="mt-8 bg-gradient-to-r from-pink-900/40 to-purple-900/40 border border-pink-700/30 rounded-xl p-6 flex items-center justify-between fade-up delay-3">
    <div>
      <h3 class="font-bold text-white text-lg">¿Necesitas ayuda?</h3>
      <p class="text-gray-400 text-sm mt-1">Nuestro equipo está listo para atenderte</p>
    </div>
    <a href="Contactos.HTML?from=panel"
       class="px-5 py-2.5 bg-white text-pink-600 font-semibold text-sm rounded-xl hover:bg-gray-100 transition shadow-md">
      Contáctanos
    </a>
  </div>

</main>

</body>
</html>
