<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class DashboardController extends Controller
{
    public function index()
    {
        ob_end_clean();
        header('Location: ' . BASE_URL . '/index.php?route=univ/index');
        exit;
    }
}
