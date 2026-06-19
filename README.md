# sistema-de-gestion-de-informes
Plataforma de gestión documental que permite a usuarios con distintos cargos generar informes técnicos estandarizados y enviarlos automáticamente a otros departamentos para su revisión.

# ⚙️ Lógica de Negocio del Sistema

El núcleo de este sistema está diseñado para modernizar y digitalizar la gestión de TI en la institución, sustituyendo el antiguo flujo tradicional de informes técnicos en papel impreso y llenado manual por un ecosistema centralizado, ágil y totalmente auditable.

El sistema rompe con el concepto del "ticket genérico" y lo reemplaza por **Informes Técnicos Digitalizados** con un flujo de validación simplificado, garantizando la eficiencia, el control institucional y la inmutabilidad de los datos una vez aprobados.

---

## 👥 1. Roles y Actores del Sistema

El sistema opera bajo un modelo de control de acceso basado en dos perfiles principales:

| Rol | Departamento | Acciones Permitidas |
| :--- | :--- | :--- |
| **Solicitante** | Cualquier departamento de la institución | Llenar y enviar informes técnicos bajo el formato oficial, y consultar el estado histórico de sus requerimientos. |
| **Técnico** | Soporte Técnico / TI | Visualizar la bandeja de entrada, evaluar informes pendientes, registrar la solución técnica, aprobar y congelar el documento. |

---

## 📋 2. Formato Único Estructurado (Plantilla Única)

Para garantizar la consistencia y facilitar la transición del papel a lo digital, el sistema erradica los reportes ambiguos utilizando una única plantilla digital estandarizada. Esta plantilla es un reflejo exacto del Informe Técnico oficial de la institución, estructurada de la siguiente manera:

*   **Encabezado Institucional Automático:** El sistema genera y enlaza de forma automática el número correlativo del informe, la fecha, y los perfiles exactos de quién emite (**DE**) y quién recibe (**PARA**).
*   **Cuerpo Técnico Fijo:** Todos los informes exigen los mismos bloques de información técnica que el personal ya conoce:
    *   *Descripción del Equipo:* Detalles físicos y administrativos (ej. Tipo de equipo, color, Número de Bien Nacional).
    *   *Diagnóstico / Problema:* Detalle exacto de la falla evaluada.
    *   *Observación:* Veredicto final de la coordinación de soporte (ej. "Desincorporar equipo").
*   **Validación Estricta:** La interfaz y el Backend validan que esta estructura única se cumpla a cabalidad. No se permite el envío del informe si faltan datos esenciales, obligando a entregar información técnica precisa desde el primer instante sin abrumar al usuario con múltiples opciones de formularios.

---

## 🔄 3. Ciclo de Vida del Informe (Flujo de Estados)

Todo informe técnico atraviesa un flujo de control estricto que asegura el orden institucional:

```mermaid
stateDiagram-v2
    [*] --> Pendiente: Solicitante envía el informe
    Pendiente --> Aprobado: Técnico registra solución y valida
    Pendiente --> Devuelto: Técnico solicita más información (Opcional)
    Devuelto --> Pendiente: Solicitante corrige y reenvía
    Aprobado --> [*]: Documento congelado e inmutable
