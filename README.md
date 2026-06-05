# sistema-de-gestion-de-informes
Plataforma de gestión documental que permite a usuarios con distintos cargos generar informes técnicos estandarizados y enviarlos automáticamente a otros departamentos para su revisión.

## 🧠 Lógica de Negocio (Business Logic)

El sistema opera bajo un modelo de **Control de Acceso Basado en Roles (RBAC)** y una **Máquina de Estados simplificada** que gestiona el ciclo de vida de cada informe técnico. Toda la validación de permisos y transiciones de estado se procesa estrictamente en el servidor, garantizando la integridad de los datos independientemente de la interfaz de usuario.

---

### 1. Roles y Permisos

El acceso a las funcionalidades está ligado al rol y al departamento del usuario autenticado:

*   **Rol: Técnico (Creador)**
    *   Puede redactar, guardar y editar informes en estado `Borrador` o `Rechazado`.
    *   Puede enviar informes dirigidos a **cualquier departamento de la empresa** (incluido su propio departamento).
    *   **Restricción de Visibilidad:** Solo puede ver los informes que él mismo ha creado. No tiene acceso a bandejas de entrada de otros departamentos.
    *   **Restricción de Edición:** Pierde *absolutamente todos* los permisos de edición en el momento en que el estado cambia a `Enviado` o `Aprobado`.

*   **Rol: Supervisor / Jefe (Revisor)**
    *   **Bandeja de Entrada (Gestión):** Ve todos los informes pendientes donde su departamento es el destino (`WHERE id_departamento_destino = departamento_usuario`).
    *   **Bandeja de Salida (Auditoría):** Puede consultar en *solo lectura* los informes que los técnicos de **su propio departamento** han enviado a otras áreas (para fines de control y seguimiento).
    *   Puede cambiar el estado de un informe recibido a `Aprobado` o `Rechazado`.
    *   **Restricción de Integridad:** No puede modificar el contenido original (título, cuerpo, adjuntos) redactado por el técnico. Su única acción de escritura permitida es el cambio de estado y la redacción de **observaciones obligatorias** en caso de rechazo.

---

### 2. Ciclo de Vida del Informe (Máquina de Estados)

El flujo está diseñado para ser predecible y evitar cambios de estado accidentales. Se controla mediante la columna `estado` en la base de datos:

```mermaid
stateDiagram-v2
    [*] --> Borrador: Crear informe
    Borrador --> Enviado: Técnico envía al depto. destino
    Enviado --> Aprobado: Supervisor valida (Fin del ciclo)
    Enviado --> Rechazado: Supervisor solicita correcciones
    Rechazado --> Borrador: Técnico corrige y reenvía
    Aprobado --> [*]: Documento inmutable y finalizado
