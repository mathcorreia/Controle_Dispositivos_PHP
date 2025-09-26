<?php
header("Content-Type: application/json; charset=UTF-8");
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO controle_manutencao (codigo_dispositivo, numero_dispositivo, manutencao_executada, data, responsavel) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Verifica se o prepare falhou
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao preparar a consulta: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("sssss", $_POST['codigo_dispositivo'], $_POST['numero_dispositivo'], $_POST['manutencao_executada'], $_POST['data'], $_POST['responsavel']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Manutenção registrada com sucesso!']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao registrar a manutenção: ' . $stmt->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}

$conn->close();
?>