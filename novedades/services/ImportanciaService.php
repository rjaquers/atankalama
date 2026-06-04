<?php
/**
 * ===================================================
 * Servicio: ImportanciaService
 * Proyecto: Hotel Atankalama – Sistema de Novedades
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * Calcular nivel de importancia sugerido en base a reglas
 * por área y palabras clave encontradas en el detalle.
 */

class ImportanciaService
{
    /**
     * Calcula el nivel de importancia sugerido.
     *
     * @param string $detalle Texto completo de la novedad
     * @param string $area Área seleccionada (texto exacto guardado en BD)
     * @return array Retorna:
     *               - nivel_sugerido (int)
     *               - score_calculado (int)
     *               - detalle_calculo (json string)
     */
    public function calcular($detalle, $area)
    {
        $score = 1;
        $detalleCalculo = array(
            'area' => null,
            'palabras' => array(),
            'score_base' => 1
        );

        $detalleNormalizado = $this->normalizarTexto($detalle);

        // === REGLAS POR ÁREA ===
        switch ($area) {

            case 'Mantenimiento':
                $score += 2;
                $detalleCalculo['area'] = 'Mantenimiento (+2)';
                break;

            case 'TI':
                $score += 2;
                $detalleCalculo['area'] = 'TI (+2)';
                break;

            case 'Recepción':
                $score += 1;
                $detalleCalculo['area'] = 'Recepción (+1)';
                break;

            case 'Housekeeping':
                $score += 1;
                $detalleCalculo['area'] = 'Housekeeping (+1)';
                break;

            default:
                $detalleCalculo['area'] = 'Otros (+0)';
                break;
        }

        // === REGLAS POR PALABRAS CLAVE ===
        $reglas = array(
            'corte' => 4,
            'electricidad' => 3,
            'agua' => 3,
            'gas' => 4,
            'incendio' => 8,
            'accidente' => 7,
            'denuncia' => 6,
            'fuga' => 3,
            'demanda' => 6,
            'inspeccion' => 3
        );

        foreach ($reglas as $palabra => $puntaje) {
            if (strpos($detalleNormalizado, $palabra) !== false) {
                $score += $puntaje;
                $detalleCalculo['palabras'][] = $palabra . ' (+' . $puntaje . ')';
            }
        }

        $nivelSugerido = ($score > 10) ? 10 : $score;

        $detalleCalculo['score_total'] = $score;
        $detalleCalculo['nivel_sugerido'] = $nivelSugerido;

        return array(
            'nivel_sugerido' => $nivelSugerido,
            'score_calculado' => $score,
            'detalle_calculo' => json_encode($detalleCalculo)
        );
    }
    // Fin de la función calcular()


    /**
     * Normaliza texto: minúsculas y sin tildes.
     *
     * @param string $texto
     * @return string
     */
    private function normalizarTexto($texto)
    {
        $texto = mb_strtolower($texto, 'UTF-8');

        $buscar = array('á','é','í','ó','ú','ñ');
        $reemplazar = array('a','e','i','o','u','n');

        return str_replace($buscar, $reemplazar, $texto);
    }
    // Fin de la función normalizarTexto()
}
