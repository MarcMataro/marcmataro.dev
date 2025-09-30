<?php
// _intern/api/tasques-update.php
require_once __DIR__ . '/../../_classes/tasques.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../_classes/connexio.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Només s\'accepten POST']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['id']) || empty($data['titol']) || empty($data['data_tasca'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falten camps obligatoris: id, titol i data_tasca']);
    exit;
}

try {
    $tasca = new TascaDiaria($data);
    $conn = Connexio::getInstance()->getConnexio();
    $sql = "UPDATE tasques_diaries SET titol = :titol, descripcio = :descripcio, data_tasca = :data_tasca, hora_inici = :hora_inici, hora_fi = :hora_fi, prioritat = :prioritat, estat = :estat, categoria = :categoria, es_important = :es_important, es_urgent = :es_urgent WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'titol' => $tasca->titol,
        'descripcio' => $tasca->descripcio,
        'data_tasca' => $tasca->data_tasca,
        'hora_inici' => $tasca->hora_inici,
        'hora_fi' => $tasca->hora_fi,
        'prioritat' => $tasca->prioritat,
        'estat' => $tasca->estat,
        'categoria' => $tasca->categoria,
        'es_important' => $tasca->es_important ? 1 : 0,
        'es_urgent' => $tasca->es_urgent ? 1 : 0,
        'id' => $tasca->id
    ]);
    if ($result) {
        echo json_encode(['success' => true, 'tasca' => $tasca->toArray()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error en actualitzar la tasca', 'debug' => $stmt->errorInfo()]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepció: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
}
exit;
