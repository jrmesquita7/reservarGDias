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

// Buscar dados do laboratório
$stmt = $pdo->prepare("SELECT * FROM laboratorios WHERE id = ?");
$stmt->execute([$id]);
$laboratorio = $stmt->fetch();

if (!$laboratorio) {
    die("Laboratório não encontrado.");
}

// Atualizar nome, descrição e status do laboratório
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE laboratorios SET nome = ?, descricao = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$nome, $descricao, $status, $id])) {
        $mensagem = "✅ Laboratório atualizado com sucesso.";
        $laboratorio['nome'] = $nome;
        $laboratorio['descricao'] = $descricao;
        $laboratorio['status'] = $status;
    } else {
        $mensagem = "❌ Erro ao atualizar laboratório.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Laboratório</title>
    <link rel="stylesheet" href="../assets/forms.css">
</head>
<body>
    <div class="container">
        <h2>Editar Laboratório</h2>

        <?php if ($mensagem): ?>
            <div class="alert"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($laboratorio['nome']) ?>" required>

            <label for="descricao">Descrição:</label>
            <textarea name="descricao" id="descricao" required><?= htmlspecialchars($laboratorio['descricao']) ?></textarea>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="Disponível" <?= $laboratorio['status'] === 'Disponível' ? 'selected' : '' ?>>Disponível</option>
                <option value="Indisponível" <?= $laboratorio['status'] === 'Indisponível' ? 'selected' : '' ?>>Indisponível</option>
            </select>

            <button type="submit">Salvar</button>
        </form>

        <a href="usuarios.php" class="back-link">⬅ Voltar</a>
    </div>
</body>
</html>
