<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html");
    exit();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $icono       = trim($_POST['icono'] ?? 'default');

    if ($nombre === '' || $descripcion === '') {
        header("Location: admin_servicios.php?error=campos_vacios");
        exit();
    }

    $nombre      = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $icono       = mysqli_real_escape_string($conexion, $icono);

    $ok = mysqli_query($conexion, "INSERT INTO servicios (nombre, descripcion, icono) VALUES ('$nombre', '$descripcion', '$icono')");

    if ($ok) {
        header("Location: admin_servicios.php?ok=1");
    } else {
        header("Location: admin_servicios.php?error=db");
    }
    exit();
}

header("Location: admin_servicios.php");
exit();
