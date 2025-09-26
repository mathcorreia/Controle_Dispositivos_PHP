<?php
include '../includes/db.php';

if (isset($_GET['relatorio'])) {
    if ($_GET['relatorio'] == 'lista_geral') {
        $result = $conn->query("SELECT * FROM cadastramento");
    }
}
?>