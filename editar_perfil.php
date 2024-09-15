<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter os dados atuais do usuário
$stmt = $conn->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    // Usuário não encontrado (situação improvável)
    header("Location: login.php");
    exit();
}

$usuario = $resultado->fetch_assoc();

// Processamento do formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validar campos obrigatórios
    if (empty($nome) || empty($email) || empty($telefone)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Atualizar os dados do usuário
        $atualizar_senha = false;

        // Verificar se o usuário quer alterar a senha
        if (!empty($senha_atual) || !empty($nova_senha) || !empty($confirmar_senha)) {
            // Verificar se a senha atual está correta
            $stmt_senha = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt_senha->bind_param("i", $usuario_id);
            $stmt_senha->execute();
            $resultado_senha = $stmt_senha->get_result();
            $dados_senha = $resultado_senha->fetch_assoc();

            if (password_verify($senha_atual, $dados_senha['senha'])) {
                // Verificar se a nova senha e a confirmação coincidem
                if ($nova_senha === $confirmar_senha) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $atualizar_senha = true;
                } else {
                    $erro = "A nova senha e a confirmação não coincidem.";
                }
            } else {
                $erro = "A senha atual está incorreta.";
            }
        }

        // Se não houver erros, prosseguir com a atualização
        if (!isset($erro)) {
            if ($atualizar_senha) {
                $stmt_update = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, senha = ? WHERE id = ?");
                $stmt_update->bind_param("ssssi", $nome, $email, $telefone, $senha_hash, $usuario_id);
            } else {
                $stmt_update = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
                $stmt_update->bind_param("sssi", $nome, $email, $telefone, $usuario_id);
            }

            if ($stmt_update->execute()) {
                $sucesso = "Dados atualizados com sucesso!";
                // Atualizar os dados na sessão, se necessário
                $_SESSION['usuario_nome'] = $nome;
                // Atualizar os dados exibidos no formulário
                $usuario['nome'] = $nome;
                $usuario['email'] = $email;
                $usuario['telefone'] = $telefone;
            } else {
                $erro = "Erro ao atualizar os dados: " . $stmt_update->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Editar Perfil - Revenda Agrícola</title>
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
        <a href="home.php" class="btn btn-secondary">Voltar</a>
    </div>
</nav>

<!-- Conteúdo Principal -->
<div class="container mt-4">
    <h2>Editar Perfil</h2>
    <?php
    if (isset($sucesso)) {
        echo "<div class='alert alert-success'>$sucesso</div>";
    }
    if (isset($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    }
    ?>
    <form method="post" action="editar_perfil.php">
        <input type="hidden" name="editar" value="1">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required>
        </div>
        <hr>
        <h4>Alterar Senha (Opcional)</h4>
        <div class="mb-3">
            <label class="form-label">Senha Atual</label>
            <input type="password" name="senha_atual" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Nova Senha</label>
            <input type="password" name="nova_senha" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar Nova Senha</label>
            <input type="password" name="confirmar_senha" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>