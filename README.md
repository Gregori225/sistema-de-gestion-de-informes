# sistema-de-gestion-de-informes
Plataforma de gestión documental que permite a usuarios con distintos cargos generar informes técnicos estandarizados y enviarlos automáticamente a otros departamentos para su revisión.

## ⚙️ Lógica de Trabajo del Sistema

El núcleo de este sistema está diseñado para modernizar y digitalizar la gestión de TI en la institución, **sustituyendo el antiguo flujo tradicional de informes técnicos en papel impreso y llenado manual** por un ecosistema centralizado, ágil y totalmente auditable. 

El sistema rompe con el concepto del "ticket genérico" y lo reemplaza por **Informes Técnicos Dinámicos** con un flujo de validación simplificado, garantizando la eficiencia, el control institucional y la inmutabilidad de los datos una vez aprobados.

---

### 👥 1. Roles y Actores del Sistema

El sistema opera bajo un modelo de control de acceso basado en dos perfiles principales:

| Rol | Departamento | Acciones Permitidas |
| :--- | :--- | :--- |
| **Solicitante** | Cualquier área de la institución | Seleccionar plantillas, llenar y enviar informes técnicos, y consultar el estado histórico de sus requerimientos. |
| **Técnico** | Soporte Técnico / TI | Visualizar la bandeja de entrada, evaluar informes pendientes, registrar la solución técnica, aprobar y congelar el documento. |

---

### 📋 2. Motor de Plantillas Dinámicas

Para erradicar los reportes incompletos o ambiguos que ocurrían en el formato de papel, el sistema utiliza formularios inteligentes basados en esquemas:
* El sistema almacena **Tipos de Plantillas** según la naturaleza del problema (ej. *Falla de Red*, *Avería de Hardware*, *Solicitud de Software*).
* Cada plantilla define sus propios **Campos Obligatorios** específicos (ej. Dirección IP, Código de Bien Nacional, Nombre del software).
* La interfaz de usuario se renderiza dinámicamente leyendo este esquema, y el **Backend valida estrictamente** que todos los campos requeridos estén presentes antes de permitir el envío, obligando al solicitante a entregar datos técnicos precisos desde el primer instante.

---

### 🔄 3. Ciclo de Vida del Informe (Flujo de Estados)

Todo informe técnico atraviesa un flujo de control estricto que asegura el orden institucional:

```mermaid
stateDiagram-v2
    [*] --> Pendiente: Solicitante envía el informe
    Pendiente --> Aprobado: Técnico registra solución y valida
    Pendiente --> Devuelto: Técnico solicita más información (Opcional)
    Devuelto --> Pendiente: Solicante corrige y reenvía
    Aprobado --> [*]: Documento congelado e inmutable
