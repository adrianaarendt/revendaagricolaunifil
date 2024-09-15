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

// Processamento do formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];

    // Preparar e executar a inserção
    $stmt = $conn->prepare("INSERT INTO imoveis (usuario_id, titulo, descricao, endereco, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $usuario_id, $titulo, $descricao, $endereco, $cidade, $estado, $cep);

    if ($stmt->execute()) {
        $sucesso = "Imóvel cadastrado com sucesso!";
    } else {
        $erro = "Erro ao cadastrar imóvel: " . $stmt->error;
    }
}

// Obter imóveis cadastrados pelo usuário
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT * FROM imoveis WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado_imoveis = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastrar Imóvel - Revenda Agrícola</title>
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
        <ul class="list-group">
            <li class="list-group-item"><a href="home.php">Início</a></li>
            <li class="list-group-item"><a href="cadastrar_imovel.php">Meus Imóveis</a></li>
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
    <h2>Cadastrar Imóvel</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    ?>
    <form method="post" action="cadastrar_imovel.php">
        <input type="hidden" name="cadastrar" value="1">
        <div class="mb-3">
            <label class="form-label">Nome do Imóvel</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Endereço</label>
            <input type="text" name="endereco" class="form-control">
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-success">Cadastrar</button>
    </form>

    <hr>

    <h2>Meus Imóveis</h2>
    <?php if ($resultado_imoveis->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($imovel = $resultado_imoveis->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($imovel['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($imovel['cidade']); ?></td>
                        <td><?php echo htmlspecialchars($imovel['estado']); ?></td>
                        <td>
                            <a href="editar_imovel.php?id=<?php echo $imovel['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="excluir_imovel.php?id=<?php echo $imovel['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este imóvel?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não cadastrou nenhum imóvel.</p>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>