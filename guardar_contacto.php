<?php
include "conexion.php";

// Crear tabla si no existe
mysqli_query($conexion, "
    CREATE TABLE IF NOT EXISTS contactos (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        nombre      VARCHAR(150)  NOT NULL,
        email       VARCHAR(200)  NOT NULL,
        telefono    VARCHAR(30)   DEFAULT '',
        mensaje     TEXT          NOT NULL,
        leido       TINYINT(1)    NOT NULL DEFAULT 0,
        fecha       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$nombre   = isset($_POST['nombre'])   ? mysqli_real_escape_string($conexion, trim($_POST['nombre']))   : '';
$email    = isset($_POST['email'])    ? mysqli_real_escape_string($conexion, trim($_POST['email']))    : '';
$telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($conexion, trim($_POST['telefono'])) : '';
$mensaje  = isset($_POST['mensaje'])  ? mysqli_real_escape_string($conexion, trim($_POST['mensaje']))  : '';

if (!$nombre || !$email || !$mensaje) {
    header("Location: Contactos.HTML?error=campos");
    exit();
}

$sql = "INSERT INTO contactos (nombre, email, telefono, mensaje) VALUES ('$nombre','$email','$telefono','$mensaje')";

if (mysqli_query($conexion, $sql)) {
    header("Location: Contactos.HTML?enviado=ok");
} else {
    header("Location: Contactos.HTML?error=db");
}
exit();
?>
