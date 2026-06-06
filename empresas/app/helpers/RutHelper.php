<?php
/**
 * Helper para manejo de RUT y privacidad
 */
class RutHelper
{
    /**
     * Enmascara un RUT: 12.345.678-9 -> 12.***.***-9
     * @param string $rut
     * @return string
     */
    public static function mask($rut)
    {
        if (empty($rut)) return '';

        // Limpiar el rut de puntos y guion para procesar
        $clean = preg_replace('/[^0-9kK]/', '', $rut);
        
        if (strlen($clean) < 3) return $rut;

        $dv = substr($clean, -1);
        $body = substr($clean, 0, -1);
        
        // Tomamos los primeros 2 dígitos
        $prefix = substr($body, 0, 2);
        
        // El resto se convierte en asteriscos
        return $prefix . ".***.***-" . $dv;
    }

    /**
     * Formatea un RUT completo si fuera necesario (sin máscara)
     */
    public static function format($rut)
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        if (strlen($rut) < 2) return $rut;
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);
        return number_format($numero, 0, '', '.') . '-' . $dv;
    }
}
