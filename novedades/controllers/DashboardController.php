<?php
require_once __DIR__.'/../models/DashboardModel.php';

class DashboardController {

    /**
     * Convierte filas (hotel, dimensión, total) en estructura para Chart.js grouped bars.
     * $topN > 0 limita las etiquetas al top N por suma global.
     */
    private function pivotarPorHotel(array $raw, string $dimKey, int $topN = 0): array {
        $hoteles      = [];
        $labelTotales = [];   // label => suma global (para ordenar)
        $matrix       = [];   // hotel => [label => total]

        foreach ($raw as $row) {
            $hotel = (string) ($row['hotel']     ?? 'Otro');
            $label = (string) ($row[$dimKey]     ?? 'No definido');
            $total = (int)     $row['total'];

            if (!in_array($hotel, $hoteles, true)) {
                $hoteles[] = $hotel;
            }
            $matrix[$hotel][$label]  = ($matrix[$hotel][$label] ?? 0) + $total;
            $labelTotales[$label]    = ($labelTotales[$label]   ?? 0) + $total;
        }

        // Ordenar etiquetas por total global descendente
        arsort($labelTotales);
        $labels = array_keys($labelTotales);
        if ($topN > 0) {
            $labels = array_slice($labels, 0, $topN);
        }

        // Colores alineados con los KPI cards del dashboard
        $hotelColors = ['#0d6efd', '#dc3545', '#198754', '#ffc107'];

        $datasets = [];
        foreach ($hoteles as $i => $hotel) {
            $data = [];
            foreach ($labels as $label) {
                $data[] = $matrix[$hotel][$label] ?? 0;
            }
            $datasets[] = [
                'label'           => $hotel,
                'data'            => $data,
                'backgroundColor' => $hotelColors[$i % count($hotelColors)],
                'borderRadius'    => 4,
            ];
        }

        return [
            'labels'   => array_values($labels),
            'datasets' => $datasets,
        ];
    }

    public function index() {
        $model = new DashboardModel();

        // Tramos rápidos de tiempo
        $tramo = $_GET['tramo'] ?? '';
        $hoy   = date('Y-m-d');

        if ($tramo === '7d') {
            $start = date('Y-m-d', strtotime('-6 days'));
            $end   = $hoy;
        } elseif ($tramo === '10d') {
            $start = date('Y-m-d', strtotime('-9 days'));
            $end   = $hoy;
        } elseif ($tramo === 'mes-pasado') {
            $start = date('Y-m-01', strtotime('first day of last month'));
            $end   = date('Y-m-t',  strtotime('last day of last month'));
        } else {
            $start = $_GET['start'] ?? date('Y-m-01');
            $end   = $_GET['end']   ?? date('Y-m-t');
            $tramo = '';
        }

        // KPIs principales
        $total      = $model->getTotalNovedades($start, $end);
        $pendientes = $model->getPendientesSeguimiento($start, $end);
        $criticas   = $model->getImportanciaCritica($start, $end);

        // Estadísticas por hotel (cards de resumen)
        $estadisticasPorHotel = $model->getEstadisticasDetalladasPorHotel($start, $end);
        $topAreasPorHotel     = [];
        foreach ($estadisticasPorHotel as $h) {
            $topAreasPorHotel[$h['hotel']] = $model->getTopAreasPorHotel($start, $end, $h['hotel']);
        }

        // Gráficos comparativos por hotel
        $tipoPorHotel  = $this->pivotarPorHotel($model->getRawTipoPorHotel($start, $end),  'tipo_novedad');
        $areaPorHotel  = $this->pivotarPorHotel($model->getRawAreaPorHotel($start, $end),  'area', 7);
        $critPorHotel  = $this->pivotarPorHotel($model->getRawCriticidadPorHotel($start, $end), 'rango');

        // Ranking y recientes
        $topRegistradores = $model->getTopRegistradores($start, $end);
        $recientes        = $model->getRecientes($start, $end, 5);

        require_once __DIR__.'/../views/dashboard/index.php';
    }
}
