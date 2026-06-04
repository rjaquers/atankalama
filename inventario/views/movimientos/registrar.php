<h2>Registrar Movimiento</h2>
<form method="post" action="index.php?controller=movimiento&action=registrar">
    <label>Producto:
        <select name="producto_id">
            <?php foreach ($productos as $p): ?>
            <option value="<?= $p['producto_id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Tipo:
        <select name="tipo">
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>
    </label><br>
    <label>Cantidad: <input type="number" name="cantidad" required></label><br>
    <label>Responsable: <input type="text" name="responsable" required></label><br>
    <label>Justificación:<br><textarea name="justificacion" rows="3" cols="40"></textarea></label><br>
    <button type="submit">Registrar</button>
</form>