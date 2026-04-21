<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html"); exit();
}
include "conexion.php";

// ── Detectar PK ────────────────────────────────────────────────────────────
$pk_col = 'id';
$cr = mysqli_query($conexion, "SHOW COLUMNS FROM solicitudes");
while ($c = mysqli_fetch_assoc($cr)) {
    if ($c['Key'] === 'PRI') { $pk_col = $c['Field']; break; }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: admin_solicitudes.php"); exit(); }

// ── Guardar cambios ────────────────────────────────────────────────────────
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estados = ['pendiente','en proceso','completado','cancelado'];
    $nuevo_estado = in_array($_POST['estado'], $estados) ? $_POST['estado'] : 'pendiente';
    $nombre   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $servicio = mysqli_real_escape_string($conexion, $_POST['servicio']);
    $tipo     = mysqli_real_escape_string($conexion, $_POST['tipo']);
    $desc     = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo   = mysqli_real_escape_string($conexion, $_POST['correo']);

    $sql = "UPDATE solicitudes SET
                estado='$nuevo_estado',
                Nombre='$nombre',
                servicio='$servicio',
                tipo='$tipo',
                `descripción`='$desc',
                telefono='$telefono',
                Correo='$correo'
            WHERE `$pk_col`='$id'";

    if (mysqli_query($conexion, $sql)) {
        $success = "✅ Solicitud actualizada correctamente.";
    } else {
        $error = "❌ Error: " . mysqli_error($conexion);
    }
}

// ── Cargar solicitud ───────────────────────────────────────────────────────
$row = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM solicitudes WHERE `$pk_col`='$id'"));
if (!$row) { header("Location: admin_solicitudes.php"); exit(); }

// Contadores sidebar
$total    = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes"))['t'];
$usuarios = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM usuarios WHERE rol='user'"))['t'];

$badge_map = [
    'pendiente'  => ['cls'=>'bg-yellow-400/15 text-yellow-400','label'=>'Pendiente'],
    'en proceso' => ['cls'=>'bg-blue-400/15 text-blue-400',   'label'=>'En Proceso'],
    'completado' => ['cls'=>'bg-green-400/15 text-green-400', 'label'=>'Completado'],
    'cancelado'  => ['cls'=>'bg-red-400/15 text-red-400',     'label'=>'Cancelado'],
];
$current_estado = strtolower(trim($row['estado'] ?? 'pendiente'));
$badge = $badge_map[$current_estado] ?? $badge_map['pendiente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestionar Solicitud #<?= $id ?> - NevekLey</title>
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

  .field-label { @apply block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide; }
  .field-input {
    width:100%; background:#111827; border:1px solid #374151;
    color:#e5e7eb; border-radius:.75rem; padding:.75rem 1rem;
    font-size:.875rem; outline:none; transition: border-color .2s, box-shadow .2s;
  }
  .field-input:focus { border-color:#ec4899; box-shadow:0 0 0 2px rgba(236,72,153,.25); }
  select.field-input { cursor:pointer; }

  .btn-save {
    background: linear-gradient(135deg,#ec4899,#9333ea);
    transition: opacity .2s, transform .15s;
  }
  .btn-save:hover { opacity:.88; transform:scale(1.02); }

  /* Status selector pills */
  .status-radio { display:none; }
  .status-label {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.45rem 1rem; border-radius:9999px; border:1.5px solid transparent;
    font-size:.75rem; font-weight:600; cursor:pointer;
    transition: all .2s;
  }
  .status-radio:checked + .status-label { border-color: currentColor; }

  /* Timeline */
  .step { position:relative; padding-left:2rem; }
  .step::before {
    content:''; position:absolute; left:.55rem; top:1.4rem;
    width:2px; height:100%; background:rgba(255,255,255,.06);
  }
  .step:last-child::before { display:none; }
  .step-dot {
    position:absolute; left:0; top:.25rem;
    width:1.1rem; height:1.1rem; border-radius:50%;
    border:2px solid; display:flex; align-items:center; justify-content:center;
  }
</style>
</head>
<body class="bg-gray-950 text-white flex min-h-screen">

<!-- SIDEBAR -->
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
      <a href="admin_solicitudes.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600/20 text-pink-400 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        Solicitudes
        <?php if ($total > 0): ?>
          <span class="ml-auto bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total ?></span>
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

<!-- MAIN -->
<main class="flex-1 ml-64 p-8 min-h-screen">

  <!-- Header -->
  <div class="flex items-center gap-4 mb-8 fade-up">
    <a href="admin_solicitudes.php" class="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition">
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 class="text-2xl font-extrabold">Gestionar <span class="text-pink-500">Solicitud #<?= $id ?></span></h1>
      <p class="text-gray-500 text-sm mt-0.5">Edita los datos y cambia el estado — se refleja inmediatamente</p>
    </div>
    <div class="ml-auto">
      <span class="<?= $badge['cls'] ?> px-3 py-1.5 text-xs font-bold rounded-full"><?= $badge['label'] ?></span>
    </div>
  </div>

  <!-- Alertas -->
  <?php if ($success): ?>
    <div class="mb-6 flex items-center gap-3 bg-green-900/20 border border-green-600/30 text-green-400 px-5 py-3 rounded-xl fade-up">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <?= $success ?>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="mb-6 flex items-center gap-3 bg-red-900/20 border border-red-600/30 text-red-400 px-5 py-3 rounded-xl fade-up">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <?= $error ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ── Formulario (izquierda 2/3) ── -->
    <div class="lg:col-span-2 fade-up delay-1">
      <form method="POST" class="bg-gray-900 border border-pink-600/20 rounded-xl p-7 space-y-6">

        <!-- Estado -->
        <div>
          <p class="field-label">Estado de la solicitud</p>
          <div class="flex flex-wrap gap-2 mt-1">

            <input type="radio" name="estado" id="s_pendiente" value="pendiente" class="status-radio"
              <?= $current_estado === 'pendiente' ? 'checked' : '' ?>>
            <label for="s_pendiente" class="status-label text-yellow-400">
              <span class="w-2 h-2 rounded-full bg-yellow-400"></span> Pendiente
            </label>

            <input type="radio" name="estado" id="s_proceso" value="en proceso" class="status-radio"
              <?= $current_estado === 'en proceso' ? 'checked' : '' ?>>
            <label for="s_proceso" class="status-label text-blue-400">
              <span class="w-2 h-2 rounded-full bg-blue-400"></span> En Proceso
            </label>

            <input type="radio" name="estado" id="s_completado" value="completado" class="status-radio"
              <?= $current_estado === 'completado' ? 'checked' : '' ?>>
            <label for="s_completado" class="status-label text-green-400">
              <span class="w-2 h-2 rounded-full bg-green-400"></span> Completado
            </label>

            <input type="radio" name="estado" id="s_cancelado" value="cancelado" class="status-radio"
              <?= $current_estado === 'cancelado' ? 'checked' : '' ?>>
            <label for="s_cancelado" class="status-label text-red-400">
              <span class="w-2 h-2 rounded-full bg-red-400"></span> Cancelado
            </label>

          </div>
        </div>

        <hr class="border-gray-800">

        <!-- Nombre + Correo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Nombre</label>
            <input type="text" name="nombre" class="field-input" value="<?= htmlspecialchars($row['Nombre']) ?>" required>
          </div>
          <div>
            <label class="field-label">Correo</label>
            <input type="email" name="correo" class="field-input" value="<?= htmlspecialchars($row['Correo']) ?>">
          </div>
        </div>

        <!-- Teléfono + Servicio -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Teléfono</label>
            <input type="text" name="telefono" class="field-input" value="<?= htmlspecialchars($row['telefono']) ?>">
          </div>
          <div>
            <label class="field-label">Servicio</label>
            <input type="text" name="servicio" class="field-input" value="<?= htmlspecialchars($row['servicio']) ?>">
          </div>
        </div>

        <!-- Tipo -->
        <div>
          <label class="field-label">Tipo</label>
          <input type="text" name="tipo" class="field-input" value="<?= htmlspecialchars($row['tipo']) ?>">
        </div>

        <!-- Descripción -->
        <div>
          <label class="field-label">Descripción</label>
          <textarea name="descripcion" rows="4" class="field-input resize-none"><?= htmlspecialchars($row['descripción'] ?? '') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="flex items-center gap-3 pt-2">
          <button type="submit" class="btn-save flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Guardar Cambios
          </button>
          <a href="admin_solicitudes.php" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-400 bg-gray-800 hover:bg-gray-700 hover:text-white transition">
            Cancelar
          </a>
        </div>

      </form>
    </div>

    <!-- ── Panel lateral (derecha 1/3) ── -->
    <div class="space-y-5 fade-up delay-2">

      <!-- Info de registro -->
      <div class="bg-gray-900 border border-pink-600/20 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-200 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Información del registro
        </h3>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">ID Solicitud</span>
            <span class="text-gray-200 font-mono">#<?= $id ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Fecha</span>
            <span class="text-gray-300 text-xs"><?= htmlspecialchars($row['Fecha']) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Estado actual</span>
            <span class="<?= $badge['cls'] ?> px-2 py-0.5 text-xs font-semibold rounded-full"><?= $badge['label'] ?></span>
          </div>
        </div>
      </div>

      <!-- Timeline de estados -->
      <div class="bg-gray-900 border border-pink-600/20 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-200 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
          Flujo de estados
        </h3>
        <?php
          $flujo = [
            ['val'=>'pendiente',  'color'=>'text-yellow-400 border-yellow-400', 'bg'=>'bg-yellow-400/10', 'label'=>'Pendiente',  'desc'=>'Solicitud recibida, en espera'],
            ['val'=>'en proceso', 'color'=>'text-blue-400 border-blue-400',     'bg'=>'bg-blue-400/10',   'label'=>'En Proceso', 'desc'=>'Trabajo en progreso'],
            ['val'=>'completado', 'color'=>'text-green-400 border-green-400',   'bg'=>'bg-green-400/10',  'label'=>'Completado', 'desc'=>'Servicio finalizado'],
            ['val'=>'cancelado',  'color'=>'text-red-400 border-red-400',       'bg'=>'bg-red-400/10',    'label'=>'Cancelado',  'desc'=>'Solicitud cancelada'],
          ];
          foreach ($flujo as $f):
            $active = ($current_estado === $f['val']);
        ?>
        <div class="step mb-4 <?= $active ? 'opacity-100' : 'opacity-40' ?>">
          <div class="step-dot <?= $f['color'] ?> <?= $active ? $f['bg'] : '' ?>">
            <?php if ($active): ?>
              <svg class="w-2.5 h-2.5 <?= $f['color'] ?>" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
            <?php endif; ?>
          </div>
          <div class="pl-1">
            <p class="text-xs font-semibold <?= $active ? $f['color'] : 'text-gray-400' ?>"><?= $f['label'] ?></p>
            <p class="text-[11px] text-gray-600"><?= $f['desc'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Enlace rápido -->
      <a href="admin_solicitudes.php"
         class="flex items-center gap-2 w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-xl text-sm text-gray-400 hover:text-pink-400 hover:border-pink-600/40 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        Ver todas las solicitudes
      </a>

    </div>
  </div>

</main>
</body>
</html>
