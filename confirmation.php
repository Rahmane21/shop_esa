<?php
// confirmation.php — Page de confirmation de commande
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

// Récupérer la commande (appartient bien à ce client)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: index.php');
    exit;
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.nom, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Commande confirmée</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:22px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:8px 15px; border-radius:5px; margin-left:8px; }

        .container { max-width:650px; margin:35px auto; padding:0 20px; }

        /* Succès */
        .succes-banner {
            background:white; border-radius:16px;
            box-shadow:0 2px 16px rgba(0,0,0,0.1);
            padding:35px; text-align:center; margin-bottom:22px;
        }
        .succes-icon { font-size:4rem; margin-bottom:12px; }
        .succes-banner h1 { font-size:24px; color:#2e7d32; margin-bottom:8px; }
        .succes-banner p { color:#666; font-size:15px; }
        .num-cmd {
            display:inline-block; background:#e8f5e9; color:#2e7d32;
            padding:6px 16px; border-radius:20px; font-weight:bold;
            font-size:15px; margin-top:12px;
        }

        /* Étapes */
        .etapes { display:flex; gap:0; margin-bottom:22px; }
        .etape { flex:1; text-align:center; padding:10px; font-size:13px; border-bottom:3px solid #eee; color:#999; }
        .etape.done { color:#4caf50; border-bottom-color:#4caf50; }

        /* Récapitulatif */
        .card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:22px; margin-bottom:16px; }
        .card h2 { font-size:16px; color:#333; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f0f0f0; }

        .info-ligne { display:flex; justify-content:space-between; padding:8px 0; font-size:14px; border-bottom:1px solid #f5f5f5; }
        .info-ligne:last-child { border-bottom:none; }
        .info-label { color:#888; }
        .info-val { color:#333; font-weight:500; }

        .article-ligne { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f5f5f5; font-size:14px; }
        .article-ligne:last-child { border-bottom:none; }

        .total-ligne { display:flex; justify-content:space-between; padding:12px 0 0; font-size:17px; font-weight:bold; color:#1a73e8; border-top:2px solid #1a73e8; margin-top:8px; }

        /* Boutons */
        .btns { display:flex; gap:12px; flex-wrap:wrap; }
        .btn-primary { flex:1; background:#1a73e8; color:white; border:none; padding:13px; border-radius:8px; font-size:15px; font-weight:bold; cursor:pointer; text-decoration:none; text-align:center; }
        .btn-primary:hover { background:#1557b0; }
        .btn-outline { flex:1; background:white; color:#1a73e8; border:1px solid #1a73e8; padding:13px; border-radius:8px; font-size:15px; text-decoration:none; text-align:center; }
        .btn-outline:hover { background:#e8f0fe; }

        /* Badge statut */
        .badge-attente { background:#fff3e0; color:#e65100; padding:3px 10px; border-radius:10px; font-size:12px; font-weight:bold; }
    </style>
</head>
<body>

<nav>
    <div class="logo">🛍️ ShopESA</div>
    <div>
        <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
        <a href="index.php">🏠 Accueil</a>
        <a href="mes_commandes.php">📦 Mes commandes</a>
    </div>
</nav>

<div class="container">

    <!-- Étapes -->
    <div class="etapes">
        <div class="etape done">✅ 1. Panier</div>
        <div class="etape done">✅ 2. Livraison</div>
        <div class="etape done">✅ 3. Confirmation</div>
    </div>

    <!-- Bannière succès -->
    <div class="succes-banner">
        <div class="succes-icon">🎉</div>
        <h1>Commande confirmée !</h1>
        <p>Merci pour votre achat. Votre commande a bien été enregistrée.</p>
        <div class="num-cmd">Commande #<?= $commande['id'] ?></div>
    </div>

    <!-- Infos livraison -->
    <div class="card">
        <h2>📦 Détails de la commande</h2>
        <div class="info-ligne">
            <span class="info-label">Numéro</span>
            <span class="info-val">#<?= $commande['id'] ?></span>
        </div>
        <div class="info-ligne">
            <span class="info-label">Date</span>
            <span class="info-val"><?= date('d/m/Y à H:i', strtotime($commande['created_at'])) ?></span>
        </div>
        <div class="info-ligne">
            <span class="info-label">Adresse</span>
            <span class="info-val"><?= htmlspecialchars($commande['adresse']) ?></span>
        </div>
        <div class="info-ligne">
            <span class="info-label">Statut</span>
            <span class="badge-attente">⏳ En attente</span>
        </div>
    </div>

    <!-- Articles commandés -->
    <div class="card">
        <h2>🛒 Articles commandés</h2>
        <?php foreach ($articles as $a): ?>
        <div class="article-ligne">
            <div>
                <strong><?= htmlspecialchars($a['nom']) ?></strong>
                <span style="color:#888; font-size:12px; margin-left:8px">× <?= $a['quantite'] ?></span>
            </div>
            <span style="font-weight:bold; color:#1a73e8;">
                <?= number_format($a['prix_unit'] * $a['quantite'], 0, ',', ' ') ?> FCFA
            </span>
        </div>
        <?php endforeach; ?>
        <div class="total-ligne">
            <span>Total payé</span>
            <span><?= number_format($commande['total'], 0, ',', ' ') ?> FCFA</span>
        </div>
    </div>

    <!-- Boutons -->
    <div class="btns">
        <a href="mes_commandes.php" class="btn-outline">📋 Mes commandes</a>
        <a href="index.php" class="btn-primary">🛍️ Continuer mes achats</a>
    </div>

</div>
</body>
</html>