<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter estatísticas
// Número de produtos cadastrados pelo usuário
$stmt_produtos = $conn->prepare("SELECT COUNT(*) AS total FROM produtos WHERE usuario_id = ?");
$stmt_produtos->bind_param("i", $usuario_id);
$stmt_produtos->execute();
$resultado_produtos = $stmt_produtos->get_result();
$total_produtos = $resultado_produtos->fetch_assoc()['total'];

// Número de imóveis cadastrados pelo usuário
$stmt_imoveis = $conn->prepare("SELECT COUNT(*) AS total FROM imoveis WHERE usuario_id = ?");
$stmt_imoveis->bind_param("i", $usuario_id);
$stmt_imoveis->execute();
$resultado_imoveis = $stmt_imoveis->get_result();
$total_imoveis = $resultado_imoveis->fetch_assoc()['total'];

// Obter produtos recentes
$stmt_recentes = $conn->prepare("SELECT produtos.*, categorias.nome AS categoria_nome FROM produtos INNER JOIN categorias ON produtos.categoria_id = categorias.id ORDER BY produtos.id DESC LIMIT 6");
$stmt_recentes->execute();
$resultado_recentes = $stmt_recentes->get_result();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Home - Revenda Agrícola</title>
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
    <h2>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>

    <!-- Estatísticas -->
    <div class="row mt-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Meus Produtos</h5>
                    <p class="card-text" style="font-size: 2em;"><?php echo $total_produtos; ?></p>
                </div>
                <div class="card-footer">
                    <a href="cadastrar_produto.php" class="text-white">Ver Produtos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">Meus Imóveis</h5>
                    <p class="card-text" style="font-size: 2em;"><?php echo $total_imoveis; ?></p>
                </div>
                <div class="card-footer">
                    <a href="cadastrar_imovel.php" class="text-white">Ver Imóveis <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <!-- Você pode adicionar mais cards de estatísticas aqui -->
    </div>

    <!-- Produtos Recentes -->
    <h3>Produtos Recentes</h3>
    <?php if ($resultado_recentes->num_rows > 0): ?>
        <div class="row">
            <?php while ($produto = $resultado_recentes->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php
                        // Obter a primeira imagem do produto
                        $stmt_imgs = $conn->prepare("SELECT caminho FROM imagens_produtos WHERE produto_id = ? LIMIT 1");
                        $stmt_imgs->bind_param("i", $produto['id']);
                        $stmt_imgs->execute();
                        $resultado_imgs = $stmt_imgs->get_result();
                        if ($imagem = $resultado_imgs->fetch_assoc()):
                        ?>
                            <img src="uploads/<?php echo $imagem['caminho']; ?>" class="card-img-top" alt="Imagem do Produto">
                        <?php else: ?>
                            <img src="imagens/placeholder.png" class="card-img-top" alt="Sem Imagem">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                            <p class="card-text"><strong>Preço:</strong> R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="pesquisar_produtos.php" class="btn btn-primary w-100">Ver Mais</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
</div>

<!-- Bootstrap JS e Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Icons do Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.js"></script>

</body>
</html>