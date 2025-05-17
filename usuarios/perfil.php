<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/db.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($nova_senha) ){
        $mensagem = "❌ A nova senha não pode estar vazia.";
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = "❌ A senha deve ter pelo menos 6 caracteres.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $mensagem = "❌ As senhas não coincidem.";
    } else {
        // Recomendado: usar password_hash() na prática real
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        if ($stmt->execute([$senha_hash, $_SESSION['usuario_id']])) {
            $mensagem = "✅ Senha atualizada com sucesso!";
        } else {
            $mensagem = "❌ Erro ao atualizar a senha. Tente novamente.";
        }
    }
}

$usuario = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$usuario->execute([$_SESSION['usuario_id']]);
$dados = $usuario->fetch();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/perfil.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Perfil do Usuário</title>
</head>
<body>
<?php include '../includes/menu.php'; ?>
    <div class="container">
        <h2>Perfil do Usuário</h2>

        <?php if ($mensagem): ?>
            <div class="alert <?= strpos($mensagem, '❌') !== false ? 'error' : 'success' ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <div class="profile-info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($dados['nome']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($dados['email']) ?></p>
        </div>

        <form method="POST" id="passwordForm">
            <label for="nova_senha">Nova Senha:</label>
            <input type="password" name="nova_senha" id="nova_senha" required minlength="6">

            <label for="confirmar_senha">Confirmar Nova Senha:</label>
            <input type="password" name="confirmar_senha" id="confirmar_senha" required minlength="6">

            <button type="submit" id="submitBtn">Atualizar Senha</button>
        </form>

        <a href="../menu.php" class="back-link">⬅ Voltar ao menu</a>
    </div>

    <script>
        // Melhoria: Adicionar loading no botão durante o submit
        document.getElementById('passwordForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="button-loading"></span>';
        });

        // Validação em tempo real das senhas
        const novaSenha = document.getElementById('nova_senha');
        const confirmarSenha = document.getElementById('confirmar_senha');
        
        function validatePasswords() {
            if (novaSenha.value && confirmarSenha.value && novaSenha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não coincidem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        }
        
        novaSenha.addEventListener('input', validatePasswords);
        confirmarSenha.addEventListener('input', validatePasswords);
    </script>
</body>
</html>