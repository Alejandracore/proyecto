<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html"); exit();
}
include "conexion.php";

// Crear tabla si no existe
mysqli_query($conexion, "
    CREATE TABLE IF NOT EXISTS contactos (
        id       INT AUTO_INCREMENT PRIMARY KEY,
        nombre   VARCHAR(150) NOT NULL,
        email    VARCHAR(200) NOT NULL,
        telefono VARCHAR(30)  DEFAULT '',
        mensaje  TEXT         NOT NULL,
        leido    TINYINT(1)   NOT NULL DEFAULT 0,
        fecha    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Marcar como leído al abrir
if (isset($_GET['leer'])) {
    $lid = (int)$_GET['leer'];
    mysqli_query($conexion, "UPDATE contactos SET leido=1 WHERE id=$lid");
    header("Location: admin_mensajes.php"); exit();
}

// Eliminar mensaje
if (isset($_GET['eliminar'])) {
    $eid = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM contactos WHERE id=$eid");
    header("Location: admin_mensajes.php"); exit();
}

// Contadores sidebar
$total_sol  = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes"))['t'];
$no_leidos  = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM contactos WHERE leido=0"))['t'];
$total_msg  = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM contactos"))['t'];

// Filtro
$filtro = isset($_GET['filtro']) && $_GET['filtro'] === 'noleidos' ? 'noleidos' : 'todos';
$where  = $filtro === 'noleidos' ? 'WHERE leido=0' : '';
$mensajes = mysqli_query($conexion, "SELECT * FROM contactos $where ORDER BY fecha DESC");

// Mensaje seleccionado
$selected = null;
if (isset($_GET['ver'])) {
    $vid = (int)$_GET['ver'];
    $selected = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM contactos WHERE id=$vid"));
    if ($selected && !$selected['leido']) {
        mysqli_query($conexion, "UPDATE contactos SET leido=1 WHERE id=$vid");
        $selected['leido'] = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mensajes de Contacto - NevekLey Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .nav-link { transition: background 0.2s, color 0.2s, padding-left 0.2s; }
  .nav-link:hover { padding-left: 1.25rem; }
  .ping-dot { position: relative; }
  .ping-dot::after {
    content:''; position:absolute; top:0; right:0;
    width:8px; height:8px; background:#22c55e; border-radius:50%;
    animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite;
  }
  @keyframes ping { 75%,100%{ transform:scale(2); opacity:0; } }
  .fade-up { animation: fadeUp 0.45s ease forwards; opacity:0; transform:translateY(14px); }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay:.08s; } .delay-2 { animation-delay:.16s; }

  .msg-row { transition: background 0.15s; cursor:pointer; border-left: 3px solid transparent; }
  .msg-row:hover { background: rgba(236,72,153,0.05); }
  .msg-row.unread { border-left-color: #ec4899; }
  .msg-row.active  { background: rgba(236,72,153,0.1); border-left-color: #ec4899; }

  .reply-btn {
    background: linear-gradient(135deg, #ec4899, #9333ea);
    transition: opacity .2s, transform .15s;
  }
  .reply-btn:hover { opacity:.85; transform:scale(1.02); }

  .glow-card { border:1px solid rgba(236,72,153,0.2); }
</style>
</head>
<body class="bg-gray-950 text-white flex min-h-screen">

<!-- ═══ SIDEBAR ═══ -->
<aside class="w-64 bg-gray-900 border-r border-pink-600/20 flex flex-col justify-between fixed h-full z-40">
  <div class="p-6">
    <div class="flex items-center gap-2 mb-10">
      <img src="logo.png" class="w-9 h-9 rounded-lg object-contain" alt="Logo">
      <span class="text-xl font-bold">Neve<span class="text-pink-500">Kley</span></span>
    </div>
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 px-2">Principal</p>
    <nav class="space-y-1">
      <a href="admin.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        Dashboard
      </a>
      <a href="admin_solicitudes.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-pink-400 hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        Solicitudes
        <?php if ($total_sol > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total_sol ?></span>
        <?php endif; ?>
      </a>
      <a href="admin_mensajes.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Mensajes
        <?php if ($no_leidos > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $no_leidos ?></span>
        <?php endif; ?>
      </a>
    </nav>
  </div>
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
    <a href="logout.php" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 transition px-2 py-1 rounded hover:bg-red-900/20">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Cerrar Sesión
    </a>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="flex-1 ml-64 p-8 min-h-screen">

  <!-- Header -->
  <div class="flex items-center justify-between mb-8 fade-up">
    <div>
      <h1 class="text-3xl font-extrabold">Mensajes de <span class="text-pink-500">Contacto</span></h1>
      <p class="text-gray-400 mt-1 text-sm">
        <span class="text-pink-400 font-semibold"><?= $total_msg ?></span> total ·
        <span class="text-yellow-400 font-semibold"><?= $no_leidos ?></span> sin leer
      </p>
    </div>
    <div class="flex gap-2">
      <a href="?filtro=todos"
         class="px-4 py-2 rounded-lg text-xs font-semibold transition <?= $filtro==='todos' ? 'bg-pink-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-pink-400' ?>">
        Todos
      </a>
      <a href="?filtro=noleidos"
         class="px-4 py-2 rounded-lg text-xs font-semibold transition <?= $filtro==='noleidos' ? 'bg-pink-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-pink-400' ?>">
        No leídos <?php if ($no_leidos): ?><span class="ml-1 bg-white/20 px-1.5 rounded-full"><?= $no_leidos ?></span><?php endif; ?>
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 fade-up delay-1">

    <!-- ── Lista de mensajes (2/5) ── -->
    <div class="lg:col-span-2 glow-card bg-gray-900 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-800 text-xs font-semibold text-gray-500 uppercase tracking-wide">
        Bandeja de entrada
      </div>

      <?php if (mysqli_num_rows($mensajes) === 0): ?>
        <div class="flex flex-col items-center justify-center py-16 text-center px-4">
          <div class="text-5xl mb-3">📭</div>
          <p class="text-gray-500 text-sm">No hay mensajes<?= $filtro==='noleidos' ? ' sin leer' : '' ?></p>
        </div>
      <?php else: ?>
        <div class="divide-y divide-gray-800 overflow-y-auto max-h-[calc(100vh-220px)]">
          <?php while ($msg = mysqli_fetch_assoc($mensajes)): ?>
            <?php
              $is_active  = $selected && $selected['id'] == $msg['id'];
              $is_unread  = !$msg['leido'];
              $row_class  = 'msg-row px-4 py-4' . ($is_active ? ' active' : '') . ($is_unread ? ' unread' : '');
            ?>
            <a href="?ver=<?= $msg['id'] ?><?= $filtro==='noleidos'?'&filtro=noleidos':'' ?>"
               class="<?= $row_class ?> block">
              <div class="flex items-start gap-3">
                <!-- Avatar inicial -->
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                  <?= strtoupper(substr($msg['nombre'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold <?= $is_unread ? 'text-white' : 'text-gray-400' ?> truncate">
                      <?= htmlspecialchars($msg['nombre']) ?>
                    </p>
                    <?php if ($is_unread): ?>
                      <span class="w-2 h-2 rounded-full bg-pink-500 flex-shrink-0"></span>
                    <?php endif; ?>
                  </div>
                  <p class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($msg['email']) ?></p>
                  <p class="text-xs text-gray-600 truncate mt-1"><?= htmlspecialchars(substr($msg['mensaje'], 0, 50)) ?>...</p>
                  <p class="text-[10px] text-gray-700 mt-1"><?= date('d/m/Y H:i', strtotime($msg['fecha'])) ?></p>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ── Detalle del mensaje (3/5) ── -->
    <div class="lg:col-span-3">
      <?php if ($selected): ?>
        <div class="glow-card bg-gray-900 rounded-xl p-6 fade-up delay-2">

          <!-- Cabecera del mensaje -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center text-base font-bold">
                <?= strtoupper(substr($selected['nombre'], 0, 1)) ?>
              </div>
              <div>
                <p class="font-bold text-white text-base"><?= htmlspecialchars($selected['nombre']) ?></p>
                <p class="text-sm text-gray-400"><?= htmlspecialchars($selected['email']) ?></p>
                <?php if ($selected['telefono']): ?>
                  <p class="text-xs text-gray-500 mt-0.5">📞 <?= htmlspecialchars($selected['telefono']) ?></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600">
              <p><?= date('d/m/Y', strtotime($selected['fecha'])) ?></p>
              <p><?= date('H:i', strtotime($selected['fecha'])) ?></p>
              <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $selected['leido'] ? 'bg-gray-800 text-gray-500' : 'bg-pink-600/20 text-pink-400' ?>">
                <?= $selected['leido'] ? 'Leído' : 'Nuevo' ?>
              </span>
            </div>
          </div>

          <!-- Cuerpo del mensaje -->
          <div class="bg-gray-950 border border-gray-800 rounded-xl p-5 mb-6">
            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($selected['mensaje']) ?></p>
          </div>

          <!-- Acciones -->
          <div class="flex flex-wrap items-center gap-3">

            <!-- Responder por correo (abre cliente de email) -->
            <a href="mailto:<?= htmlspecialchars($selected['email']) ?>?subject=Re: Mensaje de NevekLey&body=Hola <?= urlencode($selected['nombre']) ?>,%0A%0AEn respuesta a tu mensaje:%0A%0A<?= urlencode($selected['mensaje']) ?>%0A%0A----%0AEquipo NevekLey"
               class="reply-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
              </svg>
              Responder por Email
            </a>

            <!-- Marcar como no leído -->
            <?php if ($selected['leido']): ?>
              <a href="?leer=<?= $selected['id'] ?>&reset=1<?= $filtro==='noleidos'?'&filtro=noleidos':'' ?>"
                 class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-400 bg-gray-800 hover:bg-gray-700 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/></svg>
                Marcar no leído
              </a>
            <?php endif; ?>

            <!-- Eliminar -->
            <a href="?eliminar=<?= $selected['id'] ?>"
               onclick="return confirm('¿Eliminar este mensaje permanentemente?')"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-red-400 bg-red-900/15 hover:bg-red-900/30 transition ml-auto">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Eliminar
            </a>

          </div>
        </div>

      <?php else: ?>

        <!-- Estado vacío (sin selección) -->
        <div class="glow-card bg-gray-900 rounded-xl flex flex-col items-center justify-center py-24 text-center px-6">
          <div class="w-16 h-16 rounded-2xl bg-pink-600/10 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-pink-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <p class="text-gray-500 text-sm">Selecciona un mensaje para leerlo</p>
        </div>

      <?php endif; ?>
    </div>

  </div>

</main>
</body>
</html>
