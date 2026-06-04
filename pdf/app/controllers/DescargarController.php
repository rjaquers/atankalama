<?php
class DescargarController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::check();
        $title = 'Descargar App — Chat Atankalama';
        $this->view('descargar/index', compact('title'));
    }
}
