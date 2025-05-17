<?php
session_start();
require_once '../config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Verifica se o usuário logado é do grupo Administrador
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario || $usuario['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

// Buscar grupos
$grupos = $pdo->query("SELECT * FROM grupos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha']; // sem hash, conforme solicitado
    $id_grupo = $_POST['grupo'];

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, id_grupo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha, $id_grupo]);
        $mensagem = "✅ Usuário cadastrado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "❌ Erro ao cadastrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
    <link rel="stylesheet" href="../assets/forms.css">
</head>
<body>
<div class="container">
    <h2>Cadastrar Novo Usuário</h2>

    <?php if ($mensagem): ?>
        <div class="alert"> <?= $mensagem ?> </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required>

        <label for="grupo">Grupo:</label>
        <select name="grupo" required>
            <?php foreach ($grupos as $g): ?>
                <option value="<?= $g['id'] ?>"><?= $g['nome'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Cadastrar</button>
    </form>

    <a href="usuarios.php" class="back-link">⬅ Voltar</a>
</div>
</body>
</html>
