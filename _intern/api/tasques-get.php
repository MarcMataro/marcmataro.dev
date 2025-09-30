<?php
// _intern/api/tasques-get.php
require_once __DIR__ . '/../../_classes/tasques.php';
require_once __DIR__ . '/../../_classes/connexio.php';

header('Content-Type: application/json');

// Paràmetres: data_inici, data_fi (YYYY-MM-DD)
$data_inici = $_GET['data_inici'] ?? null;
$data_fi = $_GET['data_fi'] ?? null;

if (!$data_inici || !$data_fi) {
    http_response_code(400);
    echo json_encode(['error' => 'Falten paràmetres data_inici o data_fi']);
    exit;
}

try {
    $conn = Connexio::getInstance()->getConnexio();
    $sql = "SELECT * FROM tasques_diaries WHERE data_tasca BETWEEN :data_inici AND :data_fi ORDER BY data_tasca, hora_inici, id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'data_inici' => $data_inici,
        'data_fi' => $data_fi
    ]);
    $tasques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['tasques' => $tasques]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Excepció: ' . $e->getMessage(),
        'debug' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'type' => get_class($e)
    ]);
}
