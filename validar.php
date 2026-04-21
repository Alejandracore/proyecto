<?php
session_start();
include "conexion.php";

$correo = $_POST['correo'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE Correo='$correo'";
$result = mysqli_query($conexion, $sql);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: plataforma.html?error=no_user");
    exit();
}

if (password_verify($password, $user['password'])) {

    $_SESSION['id'] = $user['id_Usuarios'];
    $_SESSION['nombre'] = $user['Nombre'];
    $_SESSION['rol'] = $user['rol'];

    if ($user['rol'] == "admin") {
        header("Location: admin.php");
    } else {
        header("Location: usuario.php");
    }

} else {
    header("Location: plataforma.html?error=pass");
    exit();
}
?>