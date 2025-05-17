<?php
session_start();
require_once '../config/db.php';

// Exibe erros (para desenvolvimento – remova em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Verifica se é do grupo Administrador
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario_logado = $stmt->fetch();

if (!$usuario_logado || $usuario_logado['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = intval($_GET['id']);

// Impede que o administrador exclua a si mesmo
if ($id === $_SESSION['usuario_id']) {
    echo "<script>alert('Você não pode excluir a si mesmo.'); window.location='usuarios.php';</script>";
    exit;
}

// Executa a exclusão
try {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: usuarios.php");
    exit;
} catch (PDOException $e) {
    echo "Erro ao excluir usuário: " . $e->getMessage();
}
?>
