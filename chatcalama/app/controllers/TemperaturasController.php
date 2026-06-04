<?php
class TemperaturasController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::check();
        $title = 'Temperaturas';
        $this->view('temperaturas/index', compact('title'));
    }
}
