<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o ID do produto foi fornecido
if (!isset($_GET['id'])) {
    header("Location: cadastrar_produto.php");
    exit();
}

$id_produto = $_GET['id'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o produto pertence ao usuário
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("DELETE FROM produtos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id_produto, $usuario_id);

if ($stmt->execute()) {
    header("Location: cadastrar_produto.php?excluido=1");
    exit();
} else {
    header("Location: cadastrar_produto.php?erro_exclusao=1");
    exit();
}
?>