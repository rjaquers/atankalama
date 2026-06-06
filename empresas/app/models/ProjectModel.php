<?php
/**
 * ProjectModel - Atankalama Empresas
 * Gestiona proyectos y asignaciones dinámicas de personal
 */
class ProjectModel extends Model
{
    /**
     * Obtiene todos los proyectos de la empresa
     */
    public function getProjectsByCompany($company_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM doc_projects WHERE company_id = ? AND active = 1 ORDER BY name ASC");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    /**
     * Crea una nueva asignación de proyecto para un empleado
     */
    public function assignEmployee($data)
    {
        // Al asignar uno nuevo, podríamos cerrar el anterior si fuera necesario
        // Pero mantengamos la lógica simple por ahora: registrar periodo
        $sql = "INSERT INTO emp_project_assignments (company_id, project_id, employee_rut, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['company_id'],
            $data['project_id'],
            $data['employee_rut'],
            $data['start_date'],
            $data['end_date'] ?? null
        ]);
    }

    /**
     * Obtiene el proyecto asignado a un RUT en una fecha específica
     */
    public function getProjectByRutAndDate($company_id, $rut, $date)
    {
        $sql = "SELECT p.name 
                FROM emp_project_assignments a
                JOIN doc_projects p ON a.project_id = p.id
                WHERE a.company_id = ? AND a.employee_rut = ? 
                AND ? >= a.start_date 
                AND (? <= a.end_date OR a.end_date IS NULL)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id, $rut, $date, $date]);
        $res = $stmt->fetch();
        return $res ? $res['name'] : null;
    }
}
