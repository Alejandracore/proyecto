<?php
include "conexion.php";

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// por defecto todos los registrados son usuarios normales
$rol = "user";

$sql = "INSERT INTO usuarios (Nombre, Correo, password, rol)
        VALUES ('$nombre', '$correo', '$password', '$rol')";

if (mysqli_query($conexion, $sql)) {
    header("Location: plataforma.html?registro=ok");
} else {
    echo "Error al registrar: " . mysqli_error($conexion);
}
?>