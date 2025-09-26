<?php
header("Content-Type: application/json; charset=UTF-8");
include '../includes/db.php';

// Verifica se os parâmetros necessários foram enviados
if (!isset($_GET['tipo']) || !isset($_GET['termo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros "tipo" e "termo" são obrigatórios.']);
    exit;
}

$tipo = $_GET['tipo'];
$termo = '%' . $_GET['termo'] . '%'; // Usa o operador LIKE para buscas parciais

if ($tipo === 'local') {
    // Consulta por localização
    $sql = "SELECT id, numero_dispositivo, tipo_material, operacao, setor, status FROM cadastramento WHERE localizacao LIKE ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao preparar a consulta de local: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $termo);
    
} elseif ($tipo === 'acessorios') {
    // Consulta de acessórios por número do dispositivo
    $sql = "SELECT parafusos_1, quantidade_parafuso_1, comprimento_rosca_1, 
                   parafusos_2, quantidade_parafuso_2, comprimento_rosca_2,
                   parafusos_3, quantidade_parafuso_3, comprimento_rosca_3,
                   pinos_1, quantidade_pino_1, compr_pino_1,
                   pinos_2, quantidade_pino_2, compr_pino_2,
                   porca_t_1, quantidade_porca_t_1,
                   porca_t_2, quantidade_porca_t_2
            FROM cadastramento WHERE numero_dispositivo LIKE ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao preparar a consulta de acessórios: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $termo);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de consulta inválido. Use "local" ou "acessorios".']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$data = [];

if ($tipo === 'local') {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else { // para acessórios, esperamos apenas um resultado
    $data = $result->fetch_assoc();
}

echo json_encode($data);

$conn->close();
?>