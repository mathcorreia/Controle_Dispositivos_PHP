<?php
header("Content-Type: application/json; charset=UTF-8");
include '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Busca um dispositivo específico se o ID for fornecido
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM cadastramento WHERE id = ?");
            if ($stmt === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao preparar a consulta: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            // Busca todos os dispositivos
            $result = $conn->query("SELECT id, numero_dispositivo, tipo_material, localizacao, status FROM cadastramento");
            if ($result === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro na consulta: ' . $conn->error]);
                exit;
            }
            $data = [];
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        // Se um ID é enviado, atualiza o registro existente
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $sql = "UPDATE cadastramento SET controle=?, numero_dispositivo=?, tipo_material=?, revisao=?, pn=?, localizacao=?, operacao=?, setor=?, status=?, peso=?, observacao=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao preparar a atualização: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("sssssssssssi", $_POST['controle'], $_POST['numero_dispositivo'], $_POST['tipo_material'], $_POST['revisao'], $_POST['pn'], $_POST['localizacao'], $_POST['operacao'], $_POST['setor'], $_POST['status'], $_POST['peso'], $_POST['observacao'], $_POST['id']);
            $message = 'Dispositivo atualizado com sucesso!';
        } else {
            // Caso contrário, insere um novo registro
            $sql = "INSERT INTO cadastramento (controle, numero_dispositivo, tipo_material, revisao, pn, localizacao, operacao, setor, status, peso, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
             if ($stmt === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao preparar a inserção: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("sssssssssss", $_POST['controle'], $_POST['numero_dispositivo'], $_POST['tipo_material'], $_POST['revisao'], $_POST['pn'], $_POST['localizacao'], $_POST['operacao'], $_POST['setor'], $_POST['status'], $_POST['peso'], $_POST['observacao']);
            $message = 'Novo dispositivo cadastrado com sucesso!';
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => $message]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao executar a operação: ' . $stmt->error]);
        }
        break;

    case 'DELETE':
        // Pega o ID da URL (ex: /api/cadastramento.php?id=5)
        parse_str(file_get_contents("php://input"), $vars);
        $id = intval($vars['id']);
        
        $stmt = $conn->prepare("DELETE FROM cadastramento WHERE id = ?");
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao preparar a exclusão: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Dispositivo excluído com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir o dispositivo: ' . $stmt->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}

$conn->close();
?>