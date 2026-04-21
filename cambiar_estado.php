<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] != "admin") {
    header("Location: plataforma.html");
    exit();
}

include "conexion.php";

$id     = mysqli_real_escape_string($conexion, $_POST['id']);
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);

$estados_validos = ['Pendiente', 'En proceso', 'Completado', 'Cancelado'];

if (in_array($estado, $estados_validos)) {
    $sql = "UPDATE solicitudes SET estado='$estado' WHERE id='$id'";
    mysqli_query($conexion, $sql);
}

header("Location: admin_solicitudes.php");
exit();
?>
