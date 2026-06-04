<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <title>Sistema de Cocina – Atankalama</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Bootstrap 5 -->
<!--    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css'-->
<!--          rel='stylesheet' crossorigin='anonymous'>-->

    <!-- Bootstrap CSS -->
    <link href='../includes/bootstrap.min.css' rel='stylesheet' >

    <!-- FontAwesome -->
    <link rel='stylesheet'
          href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>

    <!-- ==== ESTILOS KIOSKO ==== -->
    <style>
        body {
            background: #1f1f1f;
            color: #fff;
            font-size: 1.2rem;
        }

        .kiosko-container {
            margin-top: 80px;
        }

        .kiosko-input {
            font-size: 2.2rem;
            text-align: center;
            padding: 20px;
            height: 80px;
            border-radius: 20px;
        }

        .kiosko-button {
            font-size: 2rem;
            padding: 20px 40px;
            border-radius: 20px;
            margin-top: 20px;
        }

        .modal-content {
            background: #2b2b2b;
            color: #fff;
            border-radius: 20px;
            padding: 25px;
        }

        .service-title {
            font-size: 2rem;
            margin-top: 20px;
            font-weight: bold;
            color: #81dfff;
        }

        /* ==== RELOJ ==== */
        #clock-box {
            text-align: center;
            margin: 20px 0;
            color: #00eaff;
            font-family: Arial, sans-serif;
        }

        #clock-time {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 0 0 10px #00eaff, 0 0 20px #0099aa;
        }

        #clock-date {
            font-size: 1.5rem;
            margin-top: 5px;
            color: #ffffffcc;
        }

        /* ==== MENSAJE DESTACADO ==== */
        .kiosk-alert-click {
            font-size: 2.6rem;
            text-align: center;
            font-weight: bold;
            padding: 15px;
            border-radius: 25px;
            background: linear-gradient(135deg, #ffcc00, #ff9900);
            color: #1f1f1f !important;
            cursor: pointer;
            box-shadow: 0 0 25px rgba(255, 200, 0, 0.9);
            transition: transform .15s ease, box-shadow .2s ease;
            border: 1px red solid;
        }


        .kiosk-alert-click:hover {
            transform: scale(1.08);
            box-shadow: 0 0 40px rgba(255, 200, 0, 1);
        }

        .kiosk-alert-click2 {
            font-size: 2.4rem;
            font-weight: 700;
            text-align: center;
            padding: 18px;
            border-radius: 25px;

            background: linear-gradient(135deg, #5a0000, #a80000); /* rojo profundo */
            color: #ffffff !important;

            border: 3px solid #ffdddd; /* borde claro que resalta */
            box-shadow: 0 0 18px rgba(255, 80, 80, 0.85);

            cursor: not-allowed;
            opacity: 0.88;

            transition: all .2s ease-in-out;
        }

        /* efecto hover bloqueado */
        .kiosk-alert-click2:hover {
            transform: scale(0.98);
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.7);
            opacity: 1;
        }


        .click-icon {
            font-size: 4rem;
            display: block;
            margin-bottom: 10px;
            animation: icon-bounce .8s infinite alternate;
        }

        @keyframes icon-bounce {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-8px);
            }
        }
    </style>
</head>
<body>
