<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processamento da remoção de itens do carrinho
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remover'])) {
    $carrinho_id = $_POST['carrinho_id'];
    $usuario_id = $_SESSION['usuario_id'];

    // Verifica se o item pertence ao usuário
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $carrinho_id, $usuario_id);

    if ($stmt->execute()) {
        $sucesso = "Item removido do carrinho com sucesso!";
    } else {
        $erro = "Erro ao remover item do carrinho.";
    }
}

// Obter itens do carrinho do usuário
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("
    SELECT carrinho.id AS carrinho_id, carrinho.quantidade, produtos.nome AS produto_nome, produtos.preco, usuarios.nome AS vendedor_nome, usuarios.telefone AS vendedor_telefone
    FROM carrinho
    INNER JOIN produtos ON carrinho.produto_id = produtos.id
    INNER JOIN usuarios ON produtos.usuario_id = usuarios.id
    WHERE carrinho.usuario_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado_carrinho = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Meu Carrinho - Revenda Agrícola</title>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS e Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-light bg-light">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><img style="width:200px" src="imagens/revenda_agricola.png"/></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<!-- Menu Lateral -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="menuLateralLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><a href="home.php">Início</a></li>
            <li class="list-group-item"><a href="cadastrar_imovel.php">Cadastrar Imóvel</a></li>
            <li class="list-group-item"><a href="cadastrar_produto.php">Cadastrar Produto</a></li>
            <li class="list-group-item"><a href="pesquisar_produtos.php">Pesquisar Produtos</a></li>
            <li class="list-group-item"><a href="carrinho.php">Meu Carrinho</a></li>
            <li class="list-group-item"><a href="editar_perfil.php">Editar Perfil</a></li>
            <li class="list-group-item"><a href="logout.php">Sair</a></li>
        </ul>
    </div>
</div>

<!-- Conteúdo Principal -->
<div class="container mt-4">
    <h2>Meu Carrinho</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    ?>

    <?php if ($resultado_carrinho->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Subtotal (R$)</th>
                    <th>Vendedor</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                while ($item = $resultado_carrinho->fetch_assoc()):
                    $subtotal = $item['preco'] * $item['quantidade'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td><?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($item['vendedor_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['vendedor_telefone']); ?></td>
                    <td>
                        <form method="post" action="carrinho.php">
                            <input type="hidden" name="carrinho_id" value="<?php echo $item['carrinho_id']; ?>">
                            <input type="hidden" name="remover" value="1">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover este item do carrinho?')">Remover</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h4>Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></h4>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
        <a href="pesquisar_produtos.php" class="btn btn-success">Começar a Comprar</a>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>