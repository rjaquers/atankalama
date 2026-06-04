<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Aprobación - <?= htmlspecialchars($enroll['nombre']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            font-family: 'Montserrat', sans-serif;
        }

        .certificate-container {
            width: 950px;
            height: 650px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            box-sizing: border-box;
            border: 15px solid #1a3c5a; /* Color corporativo oscuro */
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-image: url('https://www.transparenttextures.com/patterns/pinstripe.png');
        }

        .inner-border {
            border: 2px solid #c5a059; /* Dorado */
            height: 100%;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }

        .logo {
            max-height: 80px;
            max-width: 250px;
            width: auto;
            margin-bottom: 15px;
            object-fit: contain;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: #1a3c5a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .award-text {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }

        .recipient-name {
            font-family: 'Dancing Script', cursive;
            font-size: 52px;
            color: #c5a059;
            margin: 15px 0;
            border-bottom: 1px solid #eee;
            display: inline-block;
            min-width: 500px;
            line-height: 1.1;
        }

        .course-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 10px 0;
            line-height: 1.2;
        }

        .details {
            font-size: 14px;
            color: #777;
            max-width: 700px;
            margin: 10px auto;
            line-height: 1.4;
        }

        .validity-info {
            background-color: #fcf8e3;
            border: 1px solid #faebcc;
            color: #8a6d3b;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 10px;
            font-weight: bold;
        }

        .footer {
            margin-top: 35px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
        }

        .signature-box {
            width: 220px;
            border-top: 1px solid #aaa;
            padding-top: 8px;
            font-size: 12px;
            color: #555;
        }

        .date {
            position: absolute;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            color: #999;
        }

        /* Botón de impresión oculto al imprimir */
        .no-print {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 30px;
        }

        .btn-print {
            background-color: #1a3c5a;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s;
        }

        .btn-print:hover {
            background-color: #2c527a;
        }

        @media print {
            .no-print { display: none; }
            body { background-color: white; padding: 0; margin: 0; }
            .certificate-container { 
                margin: 0; 
                box-shadow: none; 
                border-width: 12px;
                width: 100%;
                height: 100vh;
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="certificate-container">
        <div class="inner-border">
            <img src="https://www.atankalama.com/public/uploads/logoHotelAtankalama.png" alt="Logo Hotel Atankalama" class="logo">
            
            <div class="award-text">OTORGA EL PRESENTE</div>
            <h1>Certificado</h1>
            <div class="award-text">DE APROBACIÓN A:</div>

            <div class="recipient-name">
                <?= htmlspecialchars($alumno) ?>
            </div>

            <div class="details">
                Por haber completado satisfactoriamente los requisitos académicos y evaluaciones del curso:
            </div>

            <div class="course-name">
                "<?= htmlspecialchars($enroll['nombre']) ?>"
            </div>

            <div class="details">
                Obteniendo el reconocimiento por su dedicación y cumplimiento de los estándares de excelencia de nuestra institución.
            </div>

            <div class="validity-info">
                <i class="fa-solid fa-calendar-check"></i> Certificación válida por 6 meses 
                (Vence: <?= date('d/m/Y', strtotime($enroll['fecha_aprobacion'] . ' + 6 months')) ?>)
            </div>

            <div class="footer">
                <div class="signature-box">
                    <strong>Dirección General</strong><br>
                    Hotel Atankalama
                </div>
                <div class="signature-box">
                    <strong>Departamento de RRHH</strong><br>
                    Gestión de Capacitación
                </div>
            </div>

            <div class="date">
                Expedido el <?= date('d', strtotime($enroll['fecha_aprobacion'])) ?> de 
                <?php 
                    $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
                    echo $meses[date('n', strtotime($enroll['fecha_aprobacion'])) - 1];
                ?> de <?= date('Y', strtotime($enroll['fecha_aprobacion'])) ?>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir o Guardar como PDF
        </button>
        <p style="color: #666; font-size: 14px;">(Para mejores resultados, habilita "Gráficos de fondo" en las opciones de impresión)</p>
    </div>

</body>
</html>
