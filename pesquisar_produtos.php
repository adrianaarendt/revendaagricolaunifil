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

// Obter categorias para o formulário de pesquisa
$resultado_categorias = $conn->query("SELECT id, nome FROM categorias");

// Processamento do formulário de pesquisa
$condicoes = [];
$parametros = [];
$tipos_param = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_produto = $_POST['nome_produto'];
    $categoria_id = $_POST['categoria_id'];
    
    if (!empty($nome_produto)) {
        $condicoes[] = "produtos.nome LIKE ?";
        $parametros[] = '%' . $nome_produto . '%';
        $tipos_param .= 's';
    }
    
    if (!empty($categoria_id)) {
        $condicoes[] = "produtos.categoria_id = ?";
        $parametros[] = $categoria_id;
        $tipos_param .= 'i';
    }
}

// Construir a consulta SQL com as condições
$sql = "SELECT produtos.*, categorias.nome AS categoria_nome, usuarios.nome AS vendedor_nome
        FROM produtos
        INNER JOIN categorias ON produtos.categoria_id = categorias.id
        INNER JOIN usuarios ON produtos.usuario_id = usuarios.id";

if (!empty($condicoes)) {
    $sql .= ' WHERE ' . implode(' AND ', $condicoes);
}

$stmt = $conn->prepare($sql);

// Vincular parâmetros, se houver
if (!empty($parametros)) {
    $stmt->bind_param($tipos_param, ...$parametros);
}

$stmt->execute();
$resultado_produtos = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Pesquisar Produtos - Revenda Agrícola</title>
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
    <h2>Pesquisar Produtos</h2>

    <form method="post" action="pesquisar_produtos.php" class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Nome do Produto</label>
            <input type="text" name="nome_produto" class="form-control" value="<?php echo isset($nome_produto) ? htmlspecialchars($nome_produto) : ''; ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Categoria</label>
            <select name="categoria_id" class="form-control">
                <option value="">Todas as Categorias</option>
                <?php while ($categoria = $resultado_categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php if (isset($categoria_id) && $categoria_id == $categoria['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($categoria['nome']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-success">Pesquisar</button>
        </div>
    </form>

    <?php if ($resultado_produtos->num_rows > 0): ?>
        <div class="row">
            <?php while ($produto = $resultado_produtos->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
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
                            <p class="card-text"><strong>Vendedor:</strong> <?php echo htmlspecialchars($produto['vendedor_nome']); ?></p>
                        </div>
                        <div class="card-footer">
                            <form method="post" action="adicionar_ao_carrinho.php">
                                <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Quantidade</label>
                                    <input type="number" name="quantidade" class="form-control" value="1" min="1" max="<?php echo $produto['quantidade']; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Adicionar ao Carrinho</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>