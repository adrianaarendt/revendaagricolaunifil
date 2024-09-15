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

// Obter categorias para o formulário
$resultado_categorias = $conn->query("SELECT id, nome FROM categorias");

// Verifica se o produto pertence ao usuário
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id_produto, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    // Produto não encontrado ou não pertence ao usuário
    header("Location: cadastrar_produto.php");
    exit();
}

$produto = $resultado->fetch_assoc();

// Processamento do formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $categoria_id = $_POST['categoria_id'];

    // Atualizar os dados do produto
    $stmt = $conn->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, quantidade = ?, categoria_id = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ssdiiii", $nome, $descricao, $preco, $quantidade, $categoria_id, $id_produto, $usuario_id);

    if ($stmt->execute()) {
        // Processar exclusão de imagens
        if (isset($_POST['imagens_excluir'])) {
            foreach ($_POST['imagens_excluir'] as $imagem_id) {
                // Obter o caminho da imagem
                $stmt_img = $conn->prepare("SELECT caminho FROM imagens_produtos WHERE id = ? AND produto_id = ?");
                $stmt_img->bind_param("ii", $imagem_id, $id_produto);
                $stmt_img->execute();
                $resultado_img = $stmt_img->get_result();

                if ($resultado_img->num_rows > 0) {
                    $imagem = $resultado_img->fetch_assoc();
                    $caminhoImagem = 'uploads/' . $imagem['caminho'];

                    // Remover o arquivo do servidor
                    if (file_exists($caminhoImagem)) {
                        unlink($caminhoImagem);
                    }

                    // Remover o registro do banco de dados
                    $stmt_del = $conn->prepare("DELETE FROM imagens_produtos WHERE id = ? AND produto_id = ?");
                    $stmt_del->bind_param("ii", $imagem_id, $id_produto);
                    $stmt_del->execute();
                }
            }
        }

        // Processar upload de novas imagens
        if (isset($_FILES['novas_imagens'])) {
            $novasImagens = $_FILES['novas_imagens'];
            $totalNovasImagens = count($novasImagens['name']);
            $imagensPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

            // Verificar quantas imagens o produto já tem
            $stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM imagens_produtos WHERE produto_id = ?");
            $stmt_count->bind_param("i", $id_produto);
            $stmt_count->execute();
            $resultado_count = $stmt_count->get_result();
            $totalImagensAtuais = $resultado_count->fetch_assoc()['total'];

            // Calcular quantas imagens ainda podem ser adicionadas
            $espacoDisponivel = 5 - $totalImagensAtuais;

            if ($totalNovasImagens > $espacoDisponivel) {
                $erro = "Você pode adicionar no máximo $espacoDisponivel novas imagem(ns).";
            } else {
                for ($i = 0; $i < $totalNovasImagens; $i++) {
                    if ($novasImagens['error'][$i] == UPLOAD_ERR_OK) {
                        $tmp_name = $novasImagens['tmp_name'][$i];
                        $nome_original = basename($novasImagens['name'][$i]);
                        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

                        // Validar a extensão do arquivo
                        if (in_array($extensao, $imagensPermitidas)) {
                            // Verificar o tamanho do arquivo (por exemplo, máximo 2MB)
                            $tamanhoMaximo = 2 * 1024 * 1024; // 2 MB
                            if ($novasImagens['size'][$i] <= $tamanhoMaximo) {
                                // Verificar se o arquivo é uma imagem real
                                $check = getimagesize($tmp_name);
                                if ($check !== false) {
                                    // Sanitizar o nome do arquivo
                                    $nomeArquivo = uniqid('produto_' . $id_produto . '_') . '.' . $extensao;
                                    $caminhoDestino = 'uploads/' . $nomeArquivo;

                                    // Mover o arquivo para o destino
                                    if (move_uploaded_file($tmp_name, $caminhoDestino)) {
                                        // Inserir o caminho da imagem no banco de dados
                                        $stmt_img = $conn->prepare("INSERT INTO imagens_produtos (produto_id, caminho) VALUES (?, ?)");
                                        $stmt_img->bind_param("is", $id_produto, $nomeArquivo);
                                        $stmt_img->execute();
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
                        if ($novasImagens['error'][$i] != UPLOAD_ERR_NO_FILE) {
                            $erro = "Erro no upload da imagem $nome_original.";
                        }
                    }
                }
            }
        }

        // Atualizar a mensagem de sucesso, se necessário
        if (!isset($erro)) {
            $sucesso = "Produto atualizado com sucesso!";
            // Atualizar os dados do produto para refletir as alterações
            $produto['nome'] = $nome;
            $produto['descricao'] = $descricao;
            $produto['preco'] = $preco;
            $produto['quantidade'] = $quantidade;
            $produto['categoria_id'] = $categoria_id;
        }
    } else {
        $erro = "Erro ao atualizar produto: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Editar Produto - Revenda Agrícola</title>
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
        <a href="cadastrar_produto.php" class="btn btn-secondary">Voltar</a>
    </div>
</nav>

<!-- Conteúdo Principal -->
<div class="container mt-4">
    <h2>Editar Produto</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    ?>
    <form method="post" action="editar_produto.php?id=<?php echo $id_produto; ?>" enctype="multipart/form-data">
        <input type="hidden" name="editar" value="1">
        <div class="mb-3">
            <label class="form-label">Nome do Produto</label>
            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Preço (R$)</label>
            <input type="number" name="preco" class="form-control" step="0.01" value="<?php echo $produto['preco']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantidade</label>
            <input type="number" name="quantidade" class="form-control" value="<?php echo $produto['quantidade']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select name="categoria_id" class="form-control" required>
                <?php
                // Resetar o ponteiro do resultado das categorias
                $resultado_categorias->data_seek(0);
                while ($categoria = $resultado_categorias->fetch_assoc()):
                ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php if ($produto['categoria_id'] == $categoria['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($categoria['nome']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Adicionar Novas Imagens (até 5 imagens)</label>
            <input type="file" name="novas_imagens[]" class="form-control" accept="image/*" multiple>
        </div>
        <!-- JavaScript para limitar a seleção de imagens -->
        <script>
            const inputNovasImagens = document.querySelector('input[name="novas_imagens[]"]');
            inputNovasImagens.addEventListener('change', function() {
                if (this.files.length > 5) {
                    alert('Você pode selecionar no máximo 5 imagens.');
                    this.value = '';
                }
            });
        </script>
        <div class="mb-3">
            <label class="form-label">Imagens Atuais</label><br>
            <?php
            $stmt_imgs = $conn->prepare("SELECT id, caminho FROM imagens_produtos WHERE produto_id = ?");
            $stmt_imgs->bind_param("i", $id_produto);
            $stmt_imgs->execute();
            $resultado_imgs = $stmt_imgs->get_result();

            while ($imagem = $resultado_imgs->fetch_assoc()):
            ?>
                <div style="display:inline-block; position:relative; margin-right:10px;">
                    <img src="uploads/<?php echo $imagem['caminho']; ?>" alt="Imagem do Produto" style="width:80px; height:80px; object-fit:cover;">
                    <input type="checkbox" name="imagens_excluir[]" value="<?php echo $imagem['id']; ?>" style="position:absolute; top:5px; right:5px;">
                </div>
            <?php endwhile; ?>
        </div>
        <p>Marque as imagens que deseja excluir.</p>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>