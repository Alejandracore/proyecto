<form action="guardar_solicitud.php" method="POST">

    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="email" name="correo" placeholder="Correo" required>
    <input type="text" name="telefono" placeholder="Teléfono" required>

    <select name="servicio">
        <option>Desarrollo Web y Apps</option>
        <option>Automatización de Negocios</option>
        <option>Análisis de Datos</option>
        <option>Marketing Digital</option>
        <option>Servicios de Hosting</option>
    </select>

    <select name="tipo">
        <option>Consulta</option>
        <option>Soporte</option>
        <option>Pedido</option>
    </select>

    <textarea name="descripcion" placeholder="Descripción"></textarea>

    <button type="submit">Enviar solicitud</button>

</form>