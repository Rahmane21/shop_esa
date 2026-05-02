<?php
// mes_commandes.php — Historique des commandes du client
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Récupérer toutes les commandes du client
$stmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles de chaque commande
$details = [];
foreach ($commandes as $cmd) {
    $stmt2 = $pdo->prepare("
        SELECT oi.*, p.nom, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt2->execute([$cmd['id']]);
    $details[$cmd['id']] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Mes Commandes</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:22px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:8px 15px; border-radius:5px; margin-left:8px; }
        nav a:hover { background:rgba(255,255,255,0.3); }

        .flash { margin:15px 30px; padding:12px 18px; border-radius:8px; font-size:14px; }
        .flash.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }

        .container { max-width:900px; margin:25px auto; padding:0 20px; }
        h1 { font-size:22px; color:#333; margin-bottom:20px; }

        /* VIDE */
        .vide { background:white; border-radius:12px; padding:60px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
        .vide .icon { font-size:50px; margin-bottom:15px; }
        .vide p { color:#888; margin-bottom:20px; }
        .vide a { background:#1a73e8; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; }

        /* COMMANDE CARD */
        .commande-card {
            background:white; border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
            margin-bottom:20px; overflow:hidden;
        }

        /* EN-TÊTE COMMANDE */
        .commande-header {
            display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:10px;
            padding:16px 20px;
            background:#f8f9fa; border-bottom:1px solid #eee;
        }
        .commande-header .infos { display:flex; gap:20px; flex-wrap:wrap; align-items:center; }
        .commande-header .num { font-weight:bold; color:#333; font-size:15px; }
        .commande-header .date { color:#888; font-size:13px; }
        .commande-header .total { font-weight:bold; color:#1a73e8; font-size:15px; }

        /* BADGES STATUT */
        .badge {
            display:inline-block; padding:5px 12px;
            border-radius:20px; font-size:12px; font-weight:bold;
        }
        .badge-attente  { background:#fff3e0; color:#e65100; }
        .badge-expediee { background:#e3f2fd; color:#1565c0; }
        .badge-livree   { background:#e8f5e9; color:#2e7d32; }
        .badge-annulee  { background:#ffebee; color:#c62828; }

        /* DÉTAIL ARTICLES */
        .commande-body { padding:16px 20px; }
        .article {
            display:flex; align-items:center; gap:12px;
            padding:8px 0; border-bottom:1px solid #f5f5f5;
            font-size:14px;
        }
        .article:last-child { border-bottom:none; }
        .article-img {
            width:45px; height:45px; border-radius:7px;
            object-fit:cover; flex-shrink:0;
        }
        .article-ph {
            width:45px; height:45px; border-radius:7px;
            background:#e3f0ff; display:flex;
            align-items:center; justify-content:center;
            font-size:18px; flex-shrink:0;
        }
        .article-nom { flex:1; color:#333; font-weight:500; }
        .article-qte { color:#888; font-size:13px; }
        .article-prix { font-weight:bold; color:#1a73e8; min-width:100px; text-align:right; }

        /* ADRESSE */
        .commande-adresse {
            padding:10px 20px 16px;
            font-size:13px; color:#666;
            border-top:1px dashed #eee;
        }
        .commande-adresse span { color:#333; }
    </style>
</head>
<body>

<nav>
    <div class="logo">🛍️ ShopESA</div>
    <div>
        <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
        <a href="panier.php">🛒 Panier</a>
        <a href="index.php">🏠 Accueil</a>
        <a href="deconnexion.php">Déconnexion</a>
    </div>
</nav>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="container">
    <h1>🧾 Mes Commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="vide">
            <div class="icon">📦</div>
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="index.php">← Voir les produits</a>
        </div>
    <?php else: ?>

        <?php foreach ($commandes as $cmd): ?>
        <div class="commande-card">

            <!-- EN-TÊTE -->
            <div class="commande-header">
                <div class="infos">
                    <span class="num">Commande #<?= $cmd['id'] ?></span>
                    <span class="date">📅 <?= date('d/m/Y à H:i', strtotime($cmd['created_at'])) ?></span>
                    <span class="total"><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <?php
                $badges = [
                    'en_attente' => ['badge-attente',  '⏳ En attente'],
                    'expediee'   => ['badge-expediee', '🚚 Expédiée'],
                    'livree'     => ['badge-livree',   '✅ Livrée'],
                    'annulee'    => ['badge-annulee',  '❌ Annulée'],
                ];
                $b = $badges[$cmd['statut']] ?? ['badge-attente', $cmd['statut']];
                ?>
                <span class="badge <?= $b[0] ?>"><?= $b[1] ?></span>
            </div>

            <!-- ARTICLES -->
            <div class="commande-body">
                <?php foreach ($details[$cmd['id']] as $art): ?>
                <div class="article">
                    <?php if (!empty($art['image']) && file_exists('uploads/' . $art['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($art['image']) ?>" class="article-img">
                    <?php else: ?>
                        <div class="article-ph">🛍️</div>
                    <?php endif; ?>
                    <span class="article-nom"><?= htmlspecialchars($art['nom']) ?></span>
                    <span class="article-qte">× <?= $art['quantite'] ?></span>
                    <span class="article-prix"><?= number_format($art['prix_unit'] * $art['quantite'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ADRESSE -->
            <div class="commande-adresse">
                📍 <strong>Livraison :</strong>
                <span><?= htmlspecialchars($cmd['adresse']) ?></span>
            </div>

        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>