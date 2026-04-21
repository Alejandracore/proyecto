<?php
session_start();
include "conexion.php";

$id_usuario  = isset($_SESSION['id']) ? mysqli_real_escape_string($conexion, $_SESSION['id']) : null;
$nombre      = mysqli_real_escape_string($conexion, $_POST['nombre']);
$correo      = mysqli_real_escape_string($conexion, $_POST['correo']);
$telefono    = mysqli_real_escape_string($conexion, $_POST['telefono']);
$servicio    = mysqli_real_escape_string($conexion, $_POST['servicio']);
$tipo        = mysqli_real_escape_string($conexion, $_POST['tipo']);
$descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
$fecha       = date("Y-m-d H:i:s");

$sql = "INSERT INTO solicitudes 
(id_Usuarios, Nombre, Correo, telefono, servicio, tipo, descripción, Fecha)
VALUES 
('$id_usuario', '$nombre', '$correo', '$telefono', '$servicio', '$tipo', '$descripcion', '$fecha')";

$from = isset($_POST['from']) && $_POST['from'] === 'panel' ? 'panel' : '';

if (mysqli_query($conexion, $sql)) {
    if ($from === 'panel') {
        header("Location: solicitar_servicio.php?enviado=ok&from=panel");
    } else {
        header("Location: usuario.php?solicitud=ok");
    }
    exit();
} else {
    echo "Error al enviar solicitud: " . mysqli_error($conexion);
}
?>