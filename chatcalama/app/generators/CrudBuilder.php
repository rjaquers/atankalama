<?php
class CrudBuilder
{
    /**
     * Generador simple: crea archivos base para un módulo CRUD.
     * Para producción: extender para introspección de columnas.
     */
    public function build($module)
    {
        $module = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
        if (!$module) return false;

        $ControllerName = ucfirst($module) . "Controller";
        $ModelName      = ucfirst($module) . "Model";

        $controller = HEADER . "<?php\n"
            . "class $ControllerName extends Controller {\n"
            . "  public function index(){ AuthMiddleware::check(); \$this->view('modules/$module/index'); }\n"
            . "}\n";

        $model = HEADER . "<?php\n"
            . "class $ModelName extends Model {\n"
            . "  // TODO: implementar CRUD SQL\n"
            . "}\n";

        @file_put_contents(APP_PATH . "/controllers/$ControllerName.php", $controller);
        @file_put_contents(APP_PATH . "/models/$ModelName.php", $model);

        // View base
        $viewPath = VIEW_PATH . "/modules/$module";
        if (!is_dir($viewPath)) @mkdir($viewPath, 0777, true);

        $view = "<?php require VIEW_PATH . '/layouts/header.php'; ?>\n"
              . "<h3>Módulo: " . htmlspecialchars($module) . "</h3>\n"
              . "<p>Placeholder CRUD.</p>\n"
              . "<?php require VIEW_PATH . '/layouts/footer.php'; ?>\n";

        @file_put_contents($viewPath . "/index.php", $view);

        return true;
    }
}
