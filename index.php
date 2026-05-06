<?php
session_start();
require_once 'config.php';

// Rediriger si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les produits (avec filtre catégorie si demandé)
$cat_filtre = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

if ($cat_filtre > 0) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nom AS cat_nom 
        FROM products p 
        JOIN categories c ON p.cat_id = c.id 
        WHERE p.cat_id = ?
    ");
    $stmt->execute([$cat_filtre]);
} else {
    $stmt = $pdo->query("
        SELECT p.*, c.nom AS cat_nom 
        FROM products p 
        JOIN categories c ON p.cat_id = c.id
    ");
}
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Accueil</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
        }

        /* NAVBAR */
        nav {
            background: #1a73e8;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        nav .logo {
            font-size: 22px;
            font-weight: bold;
        }
        nav .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
        }
        nav a:hover { background: rgba(255,255,255,0.3); }

        /* CATEGORIES */
        .categories {
            background: white;
            padding: 15px 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .categories a {
            padding: 8px 18px;
            border-radius: 20px;
            text-decoration: none;
            background: #f0f2f5;
            color: #333;
            font-size: 14px;
            transition: all 0.2s;
        }
        .categories a:hover,
        .categories a.active {
            background: #1a73e8;
            color: white;
        }

        /* TITRE */
        .titre {
            padding: 25px 30px 10px;
            font-size: 20px;
            color: red;
            font-weight: bold;
        }

        /* GRILLE PRODUITS */
        .produits {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 10px 30px 30px;
        }

        /* CARTE PRODUIT */
        .carte {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .carte:hover { transform: translateY(-4px); }

        .carte img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #eee;
        }

        .carte .img-placeholder {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #e3f0ff, #1a73e8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }

        .carte .info {
            padding: 15px;
        }
        .carte .cat-badge {
            font-size: 11px;
            background: #e3f0ff;
            color: #1a73e8;
            padding: 3px 8px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 8px;
        }
        .carte h3 {
            font-size: 15px;
            color: #222;
            margin-bottom: 6px;
        }
        .carte .description {
            font-size: 13px;
            color: #777;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .carte .bas {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .carte .prix {
            font-size: 17px;
            font-weight: bold;
            color: #1a73e8;
        }
        .carte .stock {
            font-size: 12px;
            color: #4caf50;
        }
        .carte .stock.rupture {
            color: #e53935;
        }
        .carte button {
            width: 100%;
            margin-top: 12px;
            padding: 10px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .carte button:hover { background: red; }
        .carte button:disabled {
            background: #ccc;
            cursor: not-allowed;
        } 

        /* AUCUN PRODUIT */
        .vide {
            text-align: center;
            padding: 60px;
            color: #999;
            font-size: 18px;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">🛍️ ShopESA</div>
    <div class="user-info">
        <span>👤 <?php echo $_SESSION['user_nom']; ?></span>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="admin/dashboard.php">⚙️ Administrateur</a>
        <?php endif; ?>
        <a href="panier.php">🛒 Panier</a>
        <a href="deconnexion.php">Déconnexion</a>
    </div>
</nav>

<!-- CATEGORIES -->
<div class="categories">
    <a href="index.php" class="<?= $cat_filtre === 0 ? 'active' : '' ?>">
         Tous les produits
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="index.php?cat=<?= $cat['id'] ?>" 
           class="<?= $cat_filtre === $cat['id'] ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['nom']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- TITRE -->
<div class="titre">
    <?php if ($cat_filtre > 0): ?>
        <?php foreach ($categories as $cat): ?>
            <?php if ($cat['id'] === $cat_filtre): ?>
                📦 <?= htmlspecialchars($cat['nom']) ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        📦 Tous les produits
    <?php endif; ?>
    <span style="font-size:14px; color:#999; font-weight:normal;">
        — <?= count($produits) ?> produit(s)
    </span>
</div>

<!-- GRILLE PRODUITS -->
<div class="produits">
    <?php if (empty($produits)): ?>
        <div class="vide" style="grid-column: 1/-1">
            😕 Aucun produit disponible
        </div>
    <?php else: ?>
        <?php foreach ($produits as $p): ?>
            <div class="carte">
                <!-- Image ou placeholder -->
                <?php if (!empty($p['image']) && file_exists('uploads/' . $p['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" 
                         alt="<?= htmlspecialchars($p['nom']) ?>">
                <?php else: ?>
                    <div class="img-placeholder">🛍️</div>
                <?php endif; ?>

                <div class="info">
                    <span class="cat-badge"><?= htmlspecialchars($p['cat_nom']) ?></span>
                    <h3><?= htmlspecialchars($p['nom']) ?></h3>
                    <div class="description">
                        <?= htmlspecialchars($p['description'] ?? '') ?>
                    </div>
                    <div class="bas">
                        <span class="prix">
                            <?= number_format($p['prix'], 0, ',', ' ') ?> FCFA
                        </span>
                        <span class="stock <?= $p['stock'] <= 0 ? 'rupture' : '' ?>">
                            <?= $p['stock'] > 0 ? '✅ En stock' : '❌ Rupture' ?>
                        </span>
                    </div>
                    <form method="POST" action="ajouter_panier.php">
    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
    <button type="submit" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
        🛒 Ajouter au panier
    </button>
</form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<!-- FOOTER -->
<footer style="
    background: blue;
    border-top: 1px solid #23272f;
    padding: 20px 30px;
    text-align: center;
    font-family: Arial, sans-serif;
">
    <p style="color: black; font-size: 13px;">
        &copy; <?= date('Y') ?> 
        <strong style="color: white;">ShopESA</strong> 
        — Développé par 
        <strong style="color: white;">Rahxime</strong>
        · ESA-AGOE Licence 2
    </p>
</footer>

</body>
</html>