
<?php
// ajouter_panier.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

// Vérifier que le produit existe et est en stock
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
$stmt->execute([$product_id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    $_SESSION['flash_error'] = "Produit indisponible ou en rupture de stock.";
    header('Location: index.php');
    exit;
}

// Vérifier si déjà dans le panier
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$existant = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existant) {
    $nouvelle_qte = $existant['quantite'] + 1;
    if ($nouvelle_qte > $produit['stock']) {
        $_SESSION['flash_error'] = "Stock insuffisant.";
        header('Location: index.php');
        exit;
    }
    $stmt = $pdo->prepare("UPDATE cart SET quantite = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$nouvelle_qte, $user_id, $product_id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantite) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $product_id]);
}

$_SESSION['flash_success'] = "\"" . htmlspecialchars($produit['nom']) . "\" ajouté au panier !";
header('Location: index.php');
exit;
