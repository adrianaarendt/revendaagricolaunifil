<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];

    // Conexão com o banco de dados
    $conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    // Verifica se o produto já está no carrinho
    $stmt = $conn->prepare("SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
    $stmt->bind_param("ii", $usuario_id, $produto_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($carrinho_id, $quantidade_existente);

    if ($stmt->num_rows > 0) {
        // Produto já está no carrinho, atualiza a quantidade
        $stmt->fetch();
        $nova_quantidade = $quantidade_existente + $quantidade;
        $stmt_update = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $nova_quantidade, $carrinho_id);
        $stmt_update->execute();
    } else {
        // Produto não está no carrinho, insere um novo registro
        $stmt_insert = $conn->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iii", $usuario_id, $produto_id, $quantidade);
        $stmt_insert->execute();
    }

    // Redireciona de volta para a página de pesquisa
    header("Location: pesquisar_produtos.php?adicionado=1");
    exit();
} else {
    // Se acessado diretamente, redireciona para a página de pesquisa
    header("Location: pesquisar_produtos.php");
    exit();
}
?>