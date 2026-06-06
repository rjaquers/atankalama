<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }

        td {
            padding: 6px;
            border: 1px solid #ccc;
        }

        img {
            width: 180px;
            margin: 6px;
            border: 1px solid #999;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h2>Registro de Temperatura</h2>
<table>
    <tr>
        <td><strong>Fecha:</strong></td>
        <td><?=htmlspecialchars($registro['fecha_hora'])?></td>
    </tr>
    <tr>
        <td><strong>Nombre:</strong></td>
        <td><?=htmlspecialchars($registro['nombre'])?></td>
    </tr>
    <tr>
        <td><strong>Hotel:</strong></td>
        <td><?=htmlspecialchars($registro['hotel'])?></td>
    </tr>
    <tr>
        <td><strong>Temperatura:</strong></td>
        <td><?=htmlspecialchars($registro['temperatura'])?> °C</td>
    </tr>
</table>

<h3>Fotografías registradas</h3>
<?php
if (! empty($registro['fotos'])):
    $fotos = explode(',', $registro['fotos']);
    foreach ($fotos as $foto):
        $foto = trim($foto);
        if ($foto !== ''):
            $rutaCompleta = 'https://www.atankalama.com/temp/'.ltrim($foto, '/');
            echo '<img src="'.htmlspecialchars($rutaCompleta).'" alt="foto">';
        endif;
    endforeach;
else:
    echo '<p>No hay fotos asociadas.</p>';
endif;
?>
</body>
</html>
