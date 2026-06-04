<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de Novedades</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }
    </style>
</head>
<body>

<h1>Reporte de Novedades</h1>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Hotel</th>
            <th>Área</th>
            <th>Recepcionista</th>
            <th>Detalle</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($novedades as $n): ?>
            <tr>
                <td><?= date('d-m-Y H:i', strtotime($n['fecha_registro'])) ?></td>
                <td><?= htmlspecialchars($n['hotel']) ?></td>
                <td><?= htmlspecialchars($n['area']) ?></td>
                <td><?= htmlspecialchars($n['recepcionista_nombre'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($n['detalle'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    window.onload = function () {
        window.print();
    };
</script>


</body>
</html>
