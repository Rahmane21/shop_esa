
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Supprimer un article
if (isset($_GET['supprimer'])) {
    $pid = (int)$_GET['supprimer'];
    $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, $pid]);
    header('Location: panier.php');
    exit;
}

// Vider le panier
if (isset($_GET['vider'])) {
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    header('Location: panier.php');
    exit;
}

// Modifier la quantité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qte'])) {
    $pid = (int)$_POST['product_id'];
    $qte = (int)$_POST['quantite'];
    if ($qte <= 0) {
        $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, $pid]);
    } else {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$pid]);
        $stock = $stmt->fetchColumn();
        if ($qte > $stock) $qte = $stock;
        $pdo->prepare("UPDATE cart SET quantite = ? WHERE user_id = ? AND product_id = ?")->execute([$qte, $user_id, $pid]);
    }
    header('Location: panier.php');
    exit;
}

// Récupérer le contenu du panier
$stmt = $pdo->prepare("
    SELECT c.quantite, p.id, p.nom, p.prix, p.stock, p.image, cat.nom AS cat_nom
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN categories cat ON p.cat_id = cat.id
    WHERE c.user_id = ?
    ORDER BY c.id ASC
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul totaux
$total_ht = 0;
foreach ($items as $item) $total_ht += $item['prix'] * $item['quantite'];
$tva       = $total_ht * 0.18;
$total_ttc = $total_ht + $tva;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Mon Panier</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:22px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:8px 15px; border-radius:5px; }
        nav a:hover { background:rgba(255,255,255,0.3); }
        .flash { margin:15px 30px; padding:12px 18px; border-radius:8px; font-size:14px; }
        .flash.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .flash.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .container { max-width:900px; margin:25px auto; padding:0 20px; }
        h1 { font-size:22px; color:#333; margin-bottom:20px; }
        .panier-vide { text-align:center; background:white; border-radius:12px; padding:60px; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
        .panier-vide .icon { font-size:60px; margin-bottom:15px; }
        .panier-vide p { color:#888; margin-bottom:20px; }
        .panier-vide a { background:#1a73e8; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; }
        table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
        th { background:#f8f9fa; padding:14px 16px; text-align:left; font-size:12px; color:#555; text-transform:uppercase; border-bottom:1px solid #eee; }
        td { padding:14px 16px; border-bottom:1px solid #f0f0f0; vertical-align:middle; font-size:14px; }
        .produit-info { display:flex; align-items:center; gap:12px; }
        .thumb { width:55px; height:55px; border-radius:8px; object-fit:cover; }
        .thumb-placeholder { width:55px; height:55px; border-radius:8px; background:#e3f0ff; display:flex; align-items:center; justify-content:center; font-size:22px; }
        .prix { font-weight:bold; color:#1a73e8; }
        .qte-form { display:flex; align-items:center; gap:6px; }
        .qte-input { width:55px; padding:6px 8px; border:1px solid #ddd; border-radius:6px; text-align:center; font-size:14px; }
        .btn-ok { background:#f0f2f5; border:1px solid #ddd; padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px; }
        .btn-suppr { background:#fff0f0; color:#e53935; border:1px solid #ffcdd2; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:13px; text-decoration:none; display:inline-block; }
        .recap { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:25px; margin-top:20px; }
        .recap h2 { font-size:17px; margin-bottom:18px; }
        .recap-ligne { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:14px; color:#555; }
        .recap-total { display:flex; justify-content:space-between; padding:14px 0 0; font-size:18px; font-weight:bold; color:#1a73e8; border-top:2px solid #1a73e8; margin-top:8px; }
        .actions-bas { display:flex; justify-content:space-between; align-items:center; margin-top:18px; flex-wrap:wrap; gap:10px; }
        .btn-vider { background:white; color:#e53935; border:1px solid #e53935; padding:10px 20px; border-radius:8px; text-decoration:none; font-size:14px; }
        .btn-commander { background:#1a73e8; color:white; border:none; padding:12px 28px; border-radius:8px; font-size:15px; font-weight:bold; text-decoration:none; display:inline-block; cursor:pointer; }
        .btn-continuer { background:white; color:#1a73e8; border:1px solid #1a73e8; padding:10px 20px; border-radius:8px; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
<nav>
    <div class="logo">🛍️ ShopESA</div>
    <div style="display:flex;gap:12px;align-items:center;">
        <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="admin/dashboard.php">⚙️ Admin</a>
        <?php endif; ?>
        <a href="index.php">🏠 Accueil</a>
        <a href="panier.php" style="background:rgba(255,255,255,0.35)">🛒 Panier</a>
        <a href="deconnexion.php">Déconnexion</a>
    </div>
</nav>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="container">
    <h1>🛒 Mon Panier</h1>

    <?php if (empty($items)): ?>
        <div class="panier-vide">
            <div class="icon">🛒</div>
            <p>Votre panier est vide.</p>
            <a href="index.php">← Retour aux produits</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div class="produit-info">
                            <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" class="thumb">
                            <?php else: ?>
                                <div class="thumb-placeholder">🛍️</div>
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($item['nom']) ?></strong><br>
                                <small style="color:#888"><?= htmlspecialchars($item['cat_nom'] ?? '') ?></small>
                            </div>
                        </div>
                    </td>
                    <td class="prix"><?= number_format($item['prix'], 0, ',', ' ') ?> FCFA</td>
                    <td>
                        <form method="POST" class="qte-form">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantite" class="qte-input"
                                   value="<?= $item['quantite'] ?>" min="0" max="<?= $item['stock'] ?>">
                            <button type="submit" name="update_qte" class="btn-ok">✔</button>
                        </form>
                    </td>
                    <td class="prix"><?= number_format($item['prix'] * $item['quantite'], 0, ',', ' ') ?> FCFA</td>
                    <td>
                        <a href="panier.php?supprimer=<?= $item['id'] ?>" class="btn-suppr"
                           onclick="return confirm('Retirer ce produit ?')">🗑️ Retirer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="recap">
            <h2>📋 Récapitulatif</h2>
            <div class="recap-ligne"><span>Sous-total HT</span><span><?= number_format($total_ht, 0, ',', ' ') ?> FCFA</span></div>
            <div class="recap-ligne"><span>TVA (18%)</span><span><?= number_format($tva, 0, ',', ' ') ?> FCFA</span></div>
            <div class="recap-total"><span>Total TTC</span><span><?= number_format($total_ttc, 0, ',', ' ') ?> FCFA</span></div>
            <div class="actions-bas">
                <a href="panier.php?vider=1" class="btn-vider"
                   onclick="return confirm('Vider tout le panier ?')">🗑️ Vider le panier</a>
                <div style="display:flex;gap:10px;">
                    <a href="index.php" class="btn-continuer">← Continuer mes achats</a>
                    <a href="commande.php" class="btn-commander">✅ Passer la commande →</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
