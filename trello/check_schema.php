<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'app/core/Model.php';
require_once 'app/models/TableroModel.php';

$model = new TableroModel();
echo $model->debugEsquema('trell_tarjetas');
