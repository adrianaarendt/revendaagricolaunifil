<!-- Adriana Arendt - Unifil 15/09/2024 -->

<?php
session_start();

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'revenda_agricola');

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($id, $senha_hash);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($senha, $senha_hash)) {
                $_SESSION['usuario_id'] = $id;
                header("Location: home.php");
                exit();
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    } else {
        $erro = "Erro ao executar a consulta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Login - Revenda Agrícola</title>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS e Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <img style="width:300px" src="imagens/revenda_agricola.png"/>
        <h2 class="card-title text-center mb-4">Login</h2>
        <?php
        if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') {
            echo "<div class='alert alert-success'>Cadastro realizado com sucesso! Faça login.</div>";
        }
        if (isset($erro)) {
            echo "<div class='alert alert-danger'>$erro</div>";
        }
        ?>
        <form method="post" action="index.php">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">Entrar</button>
            </div>
            <div class="text-center mt-3">
                <a href="cadastro.php" class="link">Não tem uma conta? <strong>Cadastre-se</strong></a>
            </div>
        </form>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>