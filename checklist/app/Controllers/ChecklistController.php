<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Checklist;
use App\Middleware\AuthMiddleware;
use App\Core\Logger;
use App\Services\EmailService;

class ChecklistController extends Controller
{
    /**
     * Muestra la lista principal de checklists.
     *
     * Qué hace:
     * - Instancia el modelo Checklist.
     * - Obtiene todos los registros.
     * - Renderiza la vista checklists/index.
     *
     * @return void
     */
    public function index()
    {
        $model = new Checklist();
        $checklists = $model->all();
        $this->render('checklists/index', ['checklists' => $checklists]);
    }
    // Fin de la función index()

    /**
     * Muestra el formulario para crear un nuevo checklist.
     *
     * Qué hace:
     * - Obtiene todas las áreas activas para el selector.
     * - Renderiza la vista checklists/create.
     *
     * @return void
     */
    public function create()
    {
        $areaModel = new \App\Models\Area();
        $areas = $areaModel->all(true);
        $this->render('checklists/create', ['areas' => $areas]);
    }
    // Fin de la función create()

    /**
     * Procesa el guardado de un nuevo checklist y sus preguntas.
     *
     * Qué hace:
     * - Valida campos obligatorios.
     * - Crea la cabecera del checklist.
     * - Itera sobre el array de preguntas para registrarlas una a una.
     * - Registra el evento en el log.
     *
     * @param string $_POST['nombre'] Nombre del checklist.
     * @param string $_POST['area'] Área asignada.
     * @param array $_POST['preguntas'] Listado de preguntas con llaves: texto, tipo, grupo, min, max.
     *
     * @return void Retorna respuesta JSON con éxito o error.
     */
    public function store()
    {
        $nombre = $_POST['nombre'] ?? '';
        $area = $_POST['area'] ?? '';
        $modo = $_POST['modo'] ?? 'cerrado';
        $hotel = $_POST['hotel'] ?? 'Atankalama';
        $email = \AccesoBootstrap::email();
        $preguntas = $_POST['preguntas'] ?? [];

        if (empty($nombre) || empty($area)) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        $model = new Checklist();
        $resultado = $model->create($nombre, $area, $email, $modo, $hotel);
        $id    = $resultado['id'];
        $token = $resultado['token'];

        if ($id) {
            foreach ($preguntas as $index => $q) {
                $model->addQuestion(
                    $id,
                    $q['texto'],
                    $q['tipo'],
                    $q['min'] ?? null,
                    $q['max'] ?? null,
                    $index,
                    $q['grupo'] ?? null
                );
            }

            if ($modo === 'abierto' && $token) {
                $linkEncuesta = BASE_URL . '/encuesta/' . $token;
                EmailService::sendNuevaEncuesta($nombre, $area, $email, $linkEncuesta);
            }

            Logger::info('CHECKLIST', 'Nuevo checklist creado con preguntas', ['id' => $id, 'nombre' => $nombre, 'modo' => $modo], $email);
            return $this->json(['message' => 'Checklist creado correctamente', 'id' => $id]);
        }
        return $this->json(['error' => 'Error al crear el checklist'], 500);
    }
    // Fin de la función store()

    /**
     * Muestra el formulario de edición de un checklist.
     *
     * Qué hace:
     * - Busca el checklist por ID.
     * - Si no existe, redirecciona a la lista.
     * - Obtiene áreas para el selector.
     * - Renderiza la vista checklists/edit.
     *
     * @param int $id ID del checklist a editar.
     * @return void
     */
    public function edit($id)
    {
        $model = new Checklist();
        $checklist = $model->find($id);

        if (!$checklist) {
            $this->redirect('/checklists');
        }

        $areaModel = new \App\Models\Area();
        $areas = $areaModel->all(true);

        $this->render('checklists/edit', [
            'checklist' => $checklist,
            'areas' => $areas
        ]);
    }
    // Fin de la función edit()

    /**
     * Procesa la actualización de un checklist y re-sincroniza sus preguntas.
     *
     * Qué hace:
     * - Valida campos.
     * - Actualiza la cabecera.
     * - Borra las preguntas anteriores (clearQuestions).
     * - Re-inserta todas las preguntas en el nuevo orden/estado.
     *
     * @param int $id ID del checklist.
     * @param string $_POST['nombre'] Nuevo nombre.
     * @param string $_POST['area'] Nueva área.
     * @param array $_POST['preguntas'] Listado de preguntas actualizado.
     *
     * @return void JSON con éxito o error.
     */
    public function update($id)
    {
        $nombre = $_POST['nombre'] ?? '';
        $area = $_POST['area'] ?? '';
        $modo = $_POST['modo'] ?? 'cerrado';
        $hotel = $_POST['hotel'] ?? 'Atankalama';
        $preguntas = $_POST['preguntas'] ?? [];

        if (empty($nombre) || empty($area)) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        $model = new Checklist();
        if ($model->update($id, $nombre, $area, $modo, $hotel)) {
            // Re-sincronizar preguntas (enfoque simple: borrar y re-insertar)
            $model->clearQuestions($id);
            foreach ($preguntas as $index => $q) {
                $model->addQuestion(
                    $id,
                    $q['texto'],
                    $q['tipo'],
                    $q['min'] ?? null,
                    $q['max'] ?? null,
                    $index,
                    $q['grupo'] ?? null
                );
            }

            Logger::info('CHECKLIST', 'Checklist actualizado', ['id' => $id, 'nombre' => $nombre, 'modo' => $modo], \AccesoBootstrap::email());
            return $this->json(['message' => 'Checklist actualizado correctamente', 'redirect' => BASE_URL . '/checklists/editar/' . $id]);
        }

        return $this->json(['error' => 'Error al actualizar'], 500);
    }
    // Fin de la función update()

    /**
     * Añade una pregunta individual a un checklist.
     *
     * Qué hace:
     * - Recibe parámetros vía POST.
     * - Inserta la pregunta en la base de datos.
     *
     * @param int $_POST['checklist_id']
     * @param string $_POST['pregunta']
     * @param string $_POST['tipo_respuesta']
     * @param int|null $_POST['escala_min']
     * @param int|null $_POST['escala_max']
     * @param int $_POST['orden']
     * @param string|null $_POST['grupo']
     *
     * @return void JSON con éxito o error.
     */
    public function addQuestion()
    {
        $model = new Checklist();
        $success = $model->addQuestion(
            $_POST['checklist_id'],
            $_POST['pregunta'],
            $_POST['tipo_respuesta'],
            $_POST['escala_min'] ?? null,
            $_POST['escala_max'] ?? null,
            $_POST['orden'] ?? 0,
            $_POST['grupo'] ?? null
        );

        if ($success) {
            return $this->json(['message' => 'Pregunta añadida']);
        }
        return $this->json(['error' => 'Error'], 500);
    }
    // Fin de la función addQuestion()

    /**
     * Elimina lógicamente un checklist.
     *
     * Qué hace:
     * - Busca el registro.
     * - Cambia su estado a 'borrado'.
     * - Registra el evento en logs.
     *
     * @param int $id ID del checklist.
     * @return void JSON con éxito o error.
     */
    public function delete($id)
    {
        $model = new Checklist();
        $checklist = $model->find($id);

        if (!$checklist) {
            return $this->json(['error' => 'Checklist no encontrado'], 404);
        }

        if ($model->delete($id)) {
            Logger::info('CHECKLIST', 'Checklist eliminado', ['id' => $id, 'nombre' => $checklist['nombre']], \AccesoBootstrap::email());
            return $this->json(['message' => 'Checklist eliminado correctamente']);
        }

        return $this->json(['error' => 'Error al eliminar'], 500);
    }
    // Fin de la función delete()
}
