<?php

if (isset($_GET['registro']) && $_GET['registro'] == "ok") {
    echo "<p style='color:green;'>Ya te registraste, puedes iniciar sesión</p>";
}

if (isset($_GET['error']) && $_GET['error'] == "no_user") {
    echo "<p style='color:red;'>No se encontró el usuario, regístrate primero</p>";
}
?>

<form action="validar.php" method="POST">
    <input type="email" name="correo" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar sesión</button>
</form>