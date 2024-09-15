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
$stmt = $conn->prepare("SELECT * FROM imoveis WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id_imovel, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    // Imóvel não encontrado ou não pertence ao usuário
    header("Location: cadastrar_imovel.php");
    exit();
}

$imovel = $resultado->fetch_assoc();

// Processamento do formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];

    // Atualizar os dados do imóvel
    $stmt = $conn->prepare("UPDATE imoveis SET titulo = ?, descricao = ?, endereco = ?, cidade = ?, estado = ?, cep = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ssssssii", $titulo, $descricao, $endereco, $cidade, $estado, $cep, $id_imovel, $usuario_id);

    if ($stmt->execute()) {
        $sucesso = "Imóvel atualizado com sucesso!";
        // Atualizar os dados do imóvel para refletir as alterações
        $imovel['titulo'] = $titulo;
        $imovel['descricao'] = $descricao;
        $imovel['endereco'] = $endereco;
        $imovel['cidade'] = $cidade;
        $imovel['estado'] = $estado;
        $imovel['cep'] = $cep;
    } else {
        $erro = "Erro ao atualizar imóvel: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Editar Imóvel - Revenda Agrícola</title>
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
        <a href="cadastrar_imovel.php" class="btn btn-secondary">Voltar</a>
    </div>
</nav>

<!-- Conteúdo Principal -->
<div class="container mt-4">
    <h2>Editar Imóvel</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    ?>
    <form method="post" action="editar_imovel.php?id=<?php echo $id_imovel; ?>">
        <input type="hidden" name="editar" value="1">
        <div class="mb-3">
            <label class="form-label">Título do Imóvel</label>
            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($imovel['titulo']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"><?php echo htmlspecialchars($imovel['descricao']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Endereço</label>
            <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($imovel['endereco']); ?>">
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($imovel['cidade']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control" value="<?php echo htmlspecialchars($imovel['estado']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control" value="<?php echo htmlspecialchars($imovel['cep']); ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>