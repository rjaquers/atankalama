<?php
/**
 * Helpers específicos para el tablero Kanban
 */

if (!function_exists('semaforo_clase')) {
    function semaforo_clase(string $fecha): string {
        $diff = (strtotime($fecha) - time()) / 86400;
        if ($diff < 0)  return 'fecha-gris';
        if ($diff < 1)  return 'fecha-rojo';
        if ($diff <= 3) return 'fecha-amarillo';
        return 'fecha-verde';
    }
}

if (!function_exists('semaforo_icono')) {
    function semaforo_icono(string $fecha): string {
        $diff = (strtotime($fecha) - time()) / 86400;
        if ($diff < 0)  return 'bi-clock-history';
        if ($diff < 1)  return 'bi-exclamation-circle-fill';
        if ($diff <= 3) return 'bi-clock-fill';
        return 'bi-clock';
    }
}
