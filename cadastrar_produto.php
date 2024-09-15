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

// Obter categorias para o formulário
$resultado_categorias = $conn->query("SELECT id, nome FROM categorias");

// Processamento do formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $categoria_id = $_POST['categoria_id'];

    // Processamento do upload das imagens
    $imagens = $_FILES['imagens'];
    $totalImagens = count($imagens['name']);
    $imagensPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $imagensEnviadas = [];

    // Verificar se o usuário enviou no máximo 5 imagens
    if ($totalImagens > 5) {
        $erro = "Você pode enviar no máximo 5 imagens.";
    } else {
        // Inserir o produto no banco de dados
        $stmt = $conn->prepare("INSERT INTO produtos (usuario_id, categoria_id, nome, descricao, preco, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdi", $usuario_id, $categoria_id, $nome, $descricao, $preco, $quantidade);

        if ($stmt->execute()) {
            $produto_id = $stmt->insert_id;

            // Processar cada imagem
            for ($i = 0; $i < $totalImagens; $i++) {
                if ($imagens['error'][$i] == UPLOAD_ERR_OK) {
                    $tmp_name = $imagens['tmp_name'][$i];
                    $nome_original = basename($imagens['name'][$i]);
                    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

                    // Validar a extensão do arquivo
                    if (in_array($extensao, $imagensPermitidas)) {
                        // Verificar o tamanho do arquivo (por exemplo, máximo 2MB)
                        $tamanhoMaximo = 2 * 1024 * 1024; // 2 MB
                        if ($imagens['size'][$i] <= $tamanhoMaximo) {
                            // Verificar se o arquivo é uma imagem real
                            $check = getimagesize($tmp_name);
                            if ($check !== false) {
                                // Sanitizar o nome do arquivo
                                $nomeArquivo = uniqid('produto_' . $produto_id . '_') . '.' . $extensao;
                                $caminhoDestino = 'uploads/' . $nomeArquivo;

                                // Mover o arquivo para o destino
                                if (move_uploaded_file($tmp_name, $caminhoDestino)) {
                                    // Inserir o caminho da imagem no banco de dados
                                    $stmt_img = $conn->prepare("INSERT INTO imagens_produtos (produto_id, caminho) VALUES (?, ?)");
                                    $stmt_img->bind_param("is", $produto_id, $nomeArquivo);
                                    $stmt_img->execute();
                                    $imagensEnviadas[] = $nomeArquivo;
                                } else {
                                    $erro = "Erro ao mover a imagem $nome_original.";
                                }
                            } else {
                                $erro = "O arquivo $nome_original não é uma imagem válida.";
                            }
                        } else {
                            $erro = "A imagem $nome_original excede o tamanho máximo permitido de 2 MB.";
                        }
                    } else {
                        $erro = "Extensão de arquivo não permitida para a imagem $nome_original.";
                    }
                } else {
                    if ($imagens['error'][$i] != UPLOAD_ERR_NO_FILE) {
                        $erro = "Erro no upload da imagem $nome_original.";
                    }
                }
            }

            if (!isset($erro)) {
                $sucesso = "Produto cadastrado com sucesso!";
            }
        } else {
            $erro = "Erro ao cadastrar produto: " . $stmt->error;
        }
    }
}

// Obter produtos cadastrados pelo usuário
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT produtos.*, categorias.nome AS categoria_nome FROM produtos INNER JOIN categorias ON produtos.categoria_id = categorias.id WHERE produtos.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado_produtos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastrar Produto - Revenda Agrícola</title>
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
            <li class="list-group-item"><a href="cadastrar_produto.php">Meus Produtos</a></li>
            <li class="list-group-item"><a href="pesquisar_produtos.php">Pesquisar Produtos</a></li>
            <li class="list-group-item"><a href="carrinho.php">Meu Carrinho</a></li>
            <li class="list-group-item"><a href="editar_perfil.php">Editar Perfil</a></li>
            <li class="list-group-item"><a href="logout.php">Sair</a></li>
        </ul>
    </div>
</div>

<!-- Conteúdo Principal -->
<div class="container mt-4">
    <h2>Cadastrar Produto</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    // Exibir mensagem de exclusão bem-sucedida
    if (isset($_GET['excluido'])) {
        echo "<div class='alert alert-success'>Produto excluído com sucesso!</div>";
    }
    // Exibir mensagem de erro na exclusão
    if (isset($_GET['erro_exclusao'])) {
        echo "<div class='alert alert-danger'>Erro ao excluir o produto.</div>";
    }
    ?>
    <form method="post" action="cadastrar_produto.php" enctype="multipart/form-data">
        <input type="hidden" name="cadastrar" value="1">
        <div class="mb-3">
            <label class="form-label">Nome do Produto</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Preço (R$)</label>
            <input type="number" name="preco" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantidade</label>
            <input type="number" name="quantidade" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select name="categoria_id" class="form-control" required>
                <option value="">Selecione uma categoria</option>
                <?php while ($categoria = $resultado_categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nome']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagens do Produto (até 5 imagens)</label>
            <input type="file" name="imagens[]" class="form-control" accept="image/*" multiple>
        </div>
        <button type="submit" class="btn btn-success">Cadastrar</button>
    </form>

    <hr>

    <h2>Meus Produtos</h2>
    <?php if ($resultado_produtos->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço (R$)</th>
                    <th>Quantidade</th>
                    <th>Imagens</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($produto = $resultado_produtos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                        <td><?php echo htmlspecialchars($produto['categoria_nome']); ?></td>
                        <td><?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                        <td><?php echo $produto['quantidade']; ?></td>
                        <td>
                            <?php
                            // Obter as imagens do produto
                            $stmt_imgs = $conn->prepare("SELECT caminho FROM imagens_produtos WHERE produto_id = ?");
                            $stmt_imgs->bind_param("i", $produto['id']);
                            $stmt_imgs->execute();
                            $resultado_imgs = $stmt_imgs->get_result();

                            while ($imagem = $resultado_imgs->fetch_assoc()):
                            ?>
                                <img src="uploads/<?php echo $imagem['caminho']; ?>" alt="Imagem do Produto" style="width:50px; height:50px; object-fit:cover;">
                            <?php endwhile; ?>
                        </td>
                        <td>
                            <a href="editar_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="excluir_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não cadastrou nenhum produto.</p>
    <?php endif; ?>
</div>

<!-- JavaScript para limitar a seleção de imagens -->
<script>
    const inputImagens = document.querySelector('input[name="imagens[]"]');
    inputImagens.addEventListener('change', function() {
        if (this.files.length > 5) {
            alert('Você pode selecionar no máximo 5 imagens.');
            this.value = '';
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>