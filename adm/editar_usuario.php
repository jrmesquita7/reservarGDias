<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Verifica se o usuário logado é do grupo administrador
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario_logado = $stmt->fetch();

if (!$usuario_logado || $usuario_logado['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensagem = '';

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuário não encontrado.");
}

// Buscar grupos disponíveis
$grupos = $pdo->query("SELECT * FROM grupos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Atualizar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $id_grupo = $_POST['grupo'];

    $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, id_grupo = ? WHERE id = ?");
    if ($stmt->execute([$nome, $email, $id_grupo, $id])) {
        $mensagem = "✅ Usuário atualizado com sucesso.";
        $usuario['nome'] = $nome;
        $usuario['email'] = $email;
        $usuario['id_grupo'] = $id_grupo;
    } else {
        $mensagem = "❌ Erro ao atualizar usuário.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../assets/forms.css">
</head>
<body>
    <div class="container">
        <h2>Editar Usuário</h2>

        <?php if ($mensagem): ?>
            <div class="alert"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="grupo">Grupo:</label>
            <select name="grupo" id="grupo" required>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $g['id'] == $usuario['id_grupo'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Salvar</button>
        </form>

        <a href="usuarios.php" class="back-link">⬅ Voltar</a>
    </div>
</body>
</html>
