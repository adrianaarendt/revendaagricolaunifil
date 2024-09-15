<?php
// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome     = $_POST['nome'];
    $email    = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha    = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $telefone, $senha);

    if ($stmt->execute()) {
        header("Location: index.php?cadastro=sucesso");
        exit();
    } else {
        $erro = "Erro ao cadastrar: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastro - Revenda Agrícola</title>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (opcional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
    <img style="width:300px" src="imagens/revenda_agricola.png"/>
        <h2 class="card-title text-center mb-4">Cadastro</h2>
        <?php if (isset($erro)) { echo "<div class='alert alert-danger'>$erro</div>"; } ?>
        <form method="post" action="cadastro.php">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" placeholder="Seu nome completo" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" placeholder="(DD) 9XXXX-XXXX">
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Digite uma senha forte" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Cadastrar</button>
            </div>
            <div class="text-center mt-3">
                <a href="index.php" class="link">Já tem uma conta? <strong>Faça login</strong></a>
            </div>
        </form>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Validação de Formulário (Opcional) -->
</body>
</html>