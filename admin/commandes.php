<?php
// admin/commandes.php — Gestion des commandes (Admin)
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

// Changer le statut d'une commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut'])) {
    $order_id = (int)$_POST['order_id'];
    $statut   = $_POST['statut'];
    $statuts_valides = ['en_attente', 'expediee', 'livree', 'annulee'];

    if (in_array($statut, $statuts_valides)) {
        $pdo->prepare("UPDATE orders SET statut = ? WHERE id = ?")->execute([$statut, $order_id]);
        $_SESSION['flash_success'] = "Statut de la commande #$order_id mis à jour.";
    }
    header('Location: commandes.php');
    exit;
}

// Filtre par statut
$filtre = $_GET['statut'] ?? 'tous';
$statuts_valides = ['en_attente', 'expediee', 'livree', 'annulee'];

if (in_array($filtre, $statuts_valides)) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.nom AS client_nom, u.email AS client_email
        FROM orders o JOIN users u ON o.user_id = u.id
        WHERE o.statut = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$filtre]);
} else {
    $stmt = $pdo->query("
        SELECT o.*, u.nom AS client_nom, u.email AS client_email
        FROM orders o JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
}
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Détails de chaque commande
$details = [];
foreach ($commandes as $cmd) {
    $stmt2 = $pdo->prepare("
        SELECT oi.*, p.nom FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt2->execute([$cmd['id']]);
    $details[$cmd['id']] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

// Compteurs par statut
$compteurs = $pdo->query("
    SELECT statut, COUNT(*) as nb FROM orders GROUP BY statut
")->fetchAll(PDO::FETCH_ASSOC);
$nb_statut = [];
foreach ($compteurs as $c) $nb_statut[$c['statut']] = $c['nb'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Commandes</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:20px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:7px 14px; border-radius:5px; font-size:14px; margin-left:8px; }
        nav a:hover { background:rgba(255,255,255,0.3); }

        .layout { display:flex; min-height:calc(100vh - 56px); }
        .sidebar { width:220px; background:white; box-shadow:2px 0 8px rgba(0,0,0,0.06); padding:20px 0; flex-shrink:0; }
        .sidebar-title { font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#999; padding:0 20px; margin-bottom:8px; }
        .sidebar a { display:flex; align-items:center; gap:10px; padding:11px 20px; color:#444; text-decoration:none; font-size:14px; border-left:3px solid transparent; transition:all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background:#e8f0fe; color:#1a73e8; border-left-color:#1a73e8; }
        .sidebar hr { border:none; border-top:1px solid #f0f0f0; margin:12px 0; }

        .content { flex:1; padding:25px; }
        h1 { font-size:22px; color:#333; margin-bottom:20px; }

        .flash { padding:12px 18px; border-radius:8px; margin-bottom:18px; font-size:14px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; }

        /* FILTRES */
        .filtres { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:22px; }
        .filtre-btn {
            padding:8px 16px; border-radius:20px; text-decoration:none;
            font-size:13px; font-weight:bold; transition:all 0.2s;
            background:white; color:#555; border:1px solid #ddd;
        }
        .filtre-btn:hover { border-color:#1a73e8; color:#1a73e8; }
        .filtre-btn.active { background:#1a73e8; color:white; border-color:#1a73e8; }
        .filtre-count { background:rgba(255,255,255,0.3); border-radius:10px; padding:1px 6px; font-size:11px; margin-left:4px; }
        .filtre-btn:not(.active) .filtre-count { background:#f0f0f0; color:#888; }

        /* COMMANDE CARD */
        .commande-card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.07); margin-bottom:18px; overflow:hidden; }

        .commande-header {
            display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:10px; padding:14px 20px;
            background:#f8f9fa; border-bottom:1px solid #eee;
        }
        .cmd-infos { display:flex; gap:18px; flex-wrap:wrap; align-items:center; }
        .cmd-num { font-weight:bold; font-size:15px; color:#333; }
        .cmd-client { color:#555; font-size:13px; }
        .cmd-date { color:#888; font-size:13px; }
        .cmd-total { font-weight:bold; color:#1a73e8; font-size:15px; }

        /* BADGES */
        .badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; }
        .badge-attente  { background:#fff3e0; color:#e65100; }
        .badge-expediee { background:#e3f2fd; color:#1565c0; }
        .badge-livree   { background:#e8f5e9; color:#2e7d32; }
        .badge-annulee  { background:#ffebee; color:#c62828; }

        /* CORPS COMMANDE */
        .commande-body { display:flex; justify-content:space-between; flex-wrap:wrap; gap:15px; padding:16px 20px; }

        /* ARTICLES */
        .articles { flex:1; min-width:280px; }
        .article { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5; font-size:13px; color:#444; }
        .article:last-child { border-bottom:none; }
        .article-prix { font-weight:bold; color:#1a73e8; }

        /* FORMULAIRE STATUT */
        .statut-form { min-width:200px; }
        .statut-form label { display:block; font-size:12px; font-weight:bold; color:#888; text-transform:uppercase; margin-bottom:6px; }
        .statut-select { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:8px; font-size:13px; margin-bottom:8px; }
        .statut-select:focus { outline:none; border-color:#1a73e8; }
        .btn-update { width:100%; background:#1a73e8; color:white; border:none; padding:9px; border-radius:8px; font-size:13px; cursor:pointer; font-weight:bold; }
        .btn-update:hover { background:#1557b0; }

        /* ADRESSE */
        .cmd-adresse { padding:10px 20px 14px; font-size:13px; color:#666; border-top:1px dashed #eee; }

        /* VIDE */
        .vide { background:white; border-radius:12px; padding:50px; text-align:center; color:#888; box-shadow:0 2px 10px rgba(0,0,0,0.07); }

        @media(max-width:700px){ .layout{flex-direction:column;} .sidebar{width:100%; display:flex; flex-wrap:wrap; padding:10px; gap:5px;} .sidebar a{flex:1; border-left:none; border-radius:6px; justify-content:center; min-width:100px;} .sidebar-title,.sidebar hr{display:none;} }
    </style>
</head>
<body>

<nav>
    <div class="logo">⚙️ Admin ShopESA</div>
    <div>
        <a href="../index.php">🏠 Boutique</a>
        <a href="../deconnexion.php">Déconnexion</a>
    </div>
</nav>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="ajouter_produit.php">📦 Produits</a>
        <a href="commandes.php" class="active">🧾 Commandes</a>
        <hr>
        <a href="../index.php">🛍️ Boutique</a>
        <a href="../deconnexion.php">🚪 Déconnexion</a>
    </aside>

    <main class="content">
        <h1>🧾 Gestion des Commandes</h1>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <!-- FILTRES PAR STATUT -->
        <div class="filtres">
            <a href="commandes.php" class="filtre-btn <?= $filtre === 'tous' ? 'active' : '' ?>">
                Toutes <span class="filtre-count"><?= array_sum($nb_statut) ?></span>
            </a>
            <a href="commandes.php?statut=en_attente" class="filtre-btn <?= $filtre === 'en_attente' ? 'active' : '' ?>">
                ⏳ En attente <span class="filtre-count"><?= $nb_statut['en_attente'] ?? 0 ?></span>
            </a>
            <a href="commandes.php?statut=expediee" class="filtre-btn <?= $filtre === 'expediee' ? 'active' : '' ?>">
                🚚 Expédiées <span class="filtre-count"><?= $nb_statut['expediee'] ?? 0 ?></span>
            </a>
            <a href="commandes.php?statut=livree" class="filtre-btn <?= $filtre === 'livree' ? 'active' : '' ?>">
                ✅ Livrées <span class="filtre-count"><?= $nb_statut['livree'] ?? 0 ?></span>
            </a>
            <a href="commandes.php?statut=annulee" class="filtre-btn <?= $filtre === 'annulee' ? 'active' : '' ?>">
                ❌ Annulées <span class="filtre-count"><?= $nb_statut['annulee'] ?? 0 ?></span>
            </a>
        </div>

        <?php if (empty($commandes)): ?>
            <div class="vide">
                <div style="font-size:40px;margin-bottom:12px">📭</div>
                <p>Aucune commande pour ce filtre.</p>
            </div>
        <?php else: ?>
            <?php foreach ($commandes as $cmd): ?>
            <div class="commande-card">

                <!-- EN-TÊTE -->
                <div class="commande-header">
                    <div class="cmd-infos">
                        <span class="cmd-num">Commande #<?= $cmd['id'] ?></span>
                        <span class="cmd-client">👤 <?= htmlspecialchars($cmd['client_nom']) ?> — <?= htmlspecialchars($cmd['client_email']) ?></span>
                        <span class="cmd-date">📅 <?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></span>
                        <span class="cmd-total"><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</span>
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

                <!-- CORPS -->
                <div class="commande-body">

                    <!-- Articles -->
                    <div class="articles">
                        <?php foreach ($details[$cmd['id']] as $art): ?>
                        <div class="article">
                            <span>🛍️ <?= htmlspecialchars($art['nom']) ?> × <?= $art['quantite'] ?></span>
                            <span class="article-prix"><?= number_format($art['prix_unit'] * $art['quantite'], 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Changer statut -->
                    <div class="statut-form">
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $cmd['id'] ?>">
                            <label>Changer le statut</label>
                            <select name="statut" class="statut-select">
                                <option value="en_attente" <?= $cmd['statut'] === 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                                <option value="expediee"   <?= $cmd['statut'] === 'expediee'   ? 'selected' : '' ?>>🚚 Expédiée</option>
                                <option value="livree"     <?= $cmd['statut'] === 'livree'     ? 'selected' : '' ?>>✅ Livrée</option>
                                <option value="annulee"    <?= $cmd['statut'] === 'annulee'    ? 'selected' : '' ?>>❌ Annulée</option>
                            </select>
                            <button type="submit" name="changer_statut" class="btn-update">
                                💾 Mettre à jour
                            </button>
                        </form>
                    </div>

                </div>

                <!-- ADRESSE -->
                <div class="cmd-adresse">
                    📍 <strong>Livraison :</strong> <?= htmlspecialchars($cmd['adresse']) ?>
                </div>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

</body>
</html>