<?php
$conexion = mysqli_connect("localhost", "root", "", "nevekley");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>