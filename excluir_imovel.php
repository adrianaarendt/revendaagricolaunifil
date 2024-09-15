<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o ID do imóvel foi fornecido
if (!isset($_GET['id'])) {
    header("Location: cadastrar_imovel.php");
    exit();
}

$id_imovel = $_GET['id'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o imóvel pertence ao usuário
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("DELETE FROM imoveis WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id_imovel, $usuario_id);

if ($stmt->execute()) {
    header("Location: cadastrar_imovel.php?excluido=1");
    exit();
} else {
    header("Location: cadastrar_imovel.php?erro_exclusao=1");
    exit();
}
?>