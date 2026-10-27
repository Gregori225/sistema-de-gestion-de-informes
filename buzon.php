<?php
session_start();

if (empty($_SESSION["id"])) {
  header("location: login.php");
  exit();
}

require_once("config/config.php");

$user_id = $_SESSION["id"];

// Función auxiliar para renderizar los badges de estado correctamente
function obtenerBadgeEstado($estado)
{
  switch ($estado) {
    case 'Pendiente':
      return '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>';
    case 'Leido':
      return '<span class="badge bg-info text-dark"><i class="fa-solid fa-eye me-1"></i> Leído</span>';
    case 'En Proceso':
      return '<span class="badge bg-primary"><i class="fa-solid fa-spinner me-1"></i> En Proceso</span>';
    case 'Resuelto':
      return '<span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Resuelto</span>';
    case 'Cancelado':
      return '<span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Cancelado</span>';
    default:
      return '<span class="badge bg-secondary">' . htmlspecialchars($estado) . '</span>';
  }
}

try {
  // 1. INFORMES RECIBIDOS (Donde el usuario logueado es el TÉCNICO / RECEPTOR)
  $sql_recibidos = "SELECT i.*, u.nombre AS remitente_nombre, u.cargo AS remitente_cargo
                    FROM public.informes_tecnicos i
                    LEFT JOIN public.usuarios u ON i.id_solicitante = u.id
                    WHERE i.id_tecnico = :user_id
                    ORDER BY i.fecha DESC";
  $stmt_recibidos = $conexion->prepare($sql_recibidos);
  $stmt_recibidos->execute([':user_id' => $user_id]);
  $recibidos = $stmt_recibidos->fetchAll(PDO::FETCH_ASSOC);

  // 2. INFORMES ENVIADOS (Donde el usuario logueado es el SOLICITANTE / REMITENTE)
  $sql_enviados = "SELECT i.*, u.nombre AS destinatario_nombre, u.cargo AS destinatario_cargo
                   FROM public.informes_tecnicos i
                   LEFT JOIN public.usuarios u ON i.id_tecnico = u.id
                   WHERE i.id_solicitante = :user_id
                   ORDER BY i.fecha DESC";
  $stmt_enviados = $conexion->prepare($sql_enviados);
  $stmt_enviados->execute([':user_id' => $user_id]);
  $enviados = $stmt_enviados->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error en buzon: " . $e->getMessage());
  $recibidos = [];
  $enviados = [];
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buzón de Informes - Fundasalud</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./css/home.css">
  <link rel="stylesheet" href="./css/dashboard.css">
  <link rel="stylesheet" href="./css/buzon.css">
</head>

<body>

  <div class="d-flex min-vh-100">
    <!-- MENÚ LATERAL -->
    <?php include("includes/sidebar.php"); ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="col-md-9 col-lg-10 main-content main-content-col p-4">
      <h2 class="mb-4"><i class="fa-solid fa-inbox me-2"></i> Buzón de Informes Técnicos</h2>

      <!-- Pestañas (Nav Tabs) -->
      <ul class="nav custom-tabs nav-tabs mb-4" id="buzonTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="recibidos-tab" data-bs-toggle="tab" data-bs-target="#recibidos" type="button" role="tab" aria-controls="recibidos" aria-selected="true">
            <i class="fa-solid fa-download me-2"></i> Recibidos
            <span class="badge bg-danger ms-2">
              <?= count(array_filter($recibidos, fn($i) => $i['estado'] === 'Pendiente')) ?>
            </span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="enviados-tab" data-bs-toggle="tab" data-bs-target="#enviados" type="button" role="tab" aria-controls="enviados" aria-selected="false">
            <i class="fa-solid fa-paper-plane me-2"></i> Enviados
          </button>
        </li>
      </ul>

      <div class="tab-content" id="buzonTabContent">
        <!-- PANEL 1: RECIBIDOS -->
        <div class="tab-pane fade show active" id="recibidos" role="tabpanel" aria-labelledby="recibidos-tab">
          <div class="table-responsive bg-white rounded shadow-sm p-3">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>N° Informe</th>
                  <th>Remitente (DE)</th>
                  <th>Descripción Equipo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($recibidos)): ?>
                  <?php foreach ($recibidos as $row): ?>
                    <tr class="<?= $row['estado'] === 'Pendiente' ? 'fw-bold table-light' : '' ?>">
                      <td>N° <?= htmlspecialchars((string)$row['id']) ?></td>
                      <td>
                        <?= htmlspecialchars($row['remitente_nombre'] ?? 'N/A') ?><br>
                        <small class="text-muted"><?= htmlspecialchars($row['remitente_cargo'] ?? '') ?></small>
                      </td>
                      <td><?= htmlspecialchars(mb_strimwidth($row['descripcion_equipo'] ?? '', 0, 40, "...")) ?></td>
                      <td><?= !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : 'N/A' ?></td>
                      <td>
                        <?= obtenerBadgeEstado($row['estado']) ?>
                      </td>
                      <td>
                        <a href="ver_informe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                          <i class="fa-solid fa-eye me-1"></i> Abrir
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">No tienes informes recibidos.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- PANEL 2: ENVIADOS -->
        <div class="tab-pane fade" id="enviados" role="tabpanel" aria-labelledby="enviados-tab">
          <div class="table-responsive bg-white rounded shadow-sm p-3">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>N° Informe</th>
                  <th>Destinatario (PARA)</th>
                  <th>Descripción Equipo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($enviados)): ?>
                  <?php foreach ($enviados as $row): ?>
                    <tr>
                      <td>N° <?= htmlspecialchars((string)$row['id']) ?></td>
                      <td>
                        <?= htmlspecialchars($row['destinatario_nombre'] ?? 'N/A') ?><br>
                        <small class="text-muted"><?= htmlspecialchars($row['destinatario_cargo'] ?? '') ?></small>
                      </td>
                      <td><?= htmlspecialchars(mb_strimwidth($row['descripcion_equipo'] ?? '', 0, 40, "...")) ?></td>
                      <td><?= !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : 'N/A' ?></td>
                      <td>
                        <?= obtenerBadgeEstado($row['estado']) ?>
                      </td>
                      <td>
                        <a href="ver_informe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">
                          <i class="fa-solid fa-eye me-1"></i> Ver
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">No has generado informes aún.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>