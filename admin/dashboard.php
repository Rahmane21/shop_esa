<?php
// admin/dashboard.php — Tableau de bord administrateur
session_start();
require_once '../config.php';

// Protection : admin uniquement
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

// =============================================
// STATISTIQUES GÉNÉRALES
// =============================================

// Nombre de clients
$nb_clients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();

// Nombre de produits
$nb_produits = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Nombre de commandes
$nb_commandes = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Chiffre d'affaires total
$ca_total = $pdo->query("SELECT COALESCE(SUM(total / 1.18), 0) FROM orders WHERE statut = 'livree'")->fetchColumn();

// Commandes récentes (5 dernières)
$commandes_recentes = $pdo->query("
    SELECT o.*, u.nom AS client_nom
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Produits en rupture de stock
$ruptures = $pdo->query("
    SELECT * FROM products WHERE stock = 0
")->fetchAll(PDO::FETCH_ASSOC);

// Produits les plus commandés
$top_produits = $pdo->query("
    SELECT p.nom, SUM(oi.quantite) AS total_vendu
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_vendu DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Dashboard ShopESA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }

        /* NAVBAR */
        nav {
            background: #1a73e8;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        nav .logo { font-size: 20px; font-weight: bold; }
        nav a {
            color: white; text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 7px 14px; border-radius: 5px;
            font-size: 14px; margin-left: 8px;
        }
        nav a:hover { background: rgba(255,255,255,0.35); }

        /* SIDEBAR + CONTENU */
        .layout { display: flex; min-height: calc(100vh - 56px); }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.06);
            padding: 20px 0;
            flex-shrink: 0;
        }
        .sidebar-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #999;
            padding: 0 20px;
            margin-bottom: 8px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 20px;
            color: #444;
            text-decoration: none;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: #e8f0fe;
            color: #1a73e8;
            border-left-color: #1a73e8;
        }
        .sidebar hr { border: none; border-top: 1px solid #f0f0f0; margin: 12px 0; }

        /* CONTENU PRINCIPAL */
        .content { flex: 1; padding: 25px; overflow-x: hidden; }

        h1 { font-size: 22px; color: #333; margin-bottom: 6px; }
        .subtitle { color: #888; font-size: 14px; margin-bottom: 25px; }

        /* CARTES STATISTIQUES */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            font-size: 2rem;
            width: 55px; height: 55px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-icon.bleu   { background: #e8f0fe; }
        .stat-icon.vert   { background: #e8f5e9; }
        .stat-icon.orange { background: #fff3e0; }
        .stat-icon.rouge  { background: #ffebee; }
        .stat-card .chiffre { font-size: 1.7rem; font-weight: bold; color: #222; line-height: 1; }
        .stat-card .label   { font-size: 13px; color: #888; margin-top: 4px; }

        /* GRILLE 2 COLONNES */
        .deux-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 900px) { .deux-col { grid-template-columns: 1fr; } }

        /* SECTION CARD */
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .section-card h2 {
            font-size: 15px;
            color: #333;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* TABLEAU */
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #f8f9fa; padding: 11px 16px;
            text-align: left; font-size: 12px;
            color: #666; text-transform: uppercase;
            border-bottom: 1px solid #eee;
        }
        td { padding: 11px 16px; border-bottom: 1px solid #f5f5f5; font-size: 13px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }

        /* BADGES STATUT */
        .badge {
            display: inline-block; padding: 3px 9px;
            border-radius: 12px; font-size: 11px; font-weight: bold;
        }
        .badge-attente  { background: #fff3e0; color: #e65100; }
        .badge-expediee { background: #e3f2fd; color: #1565c0; }
        .badge-livree   { background: #e8f5e9; color: #2e7d32; }
        .badge-annulee  { background: #ffebee; color: #c62828; }

        /* RUPTURES */
        .rupture-item {
            padding: 11px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
        }
        .rupture-item:last-child { border-bottom: none; }
        .rupture-nom { color: #333; font-weight: 500; }

        /* ACCÈS RAPIDES */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 25px;
        }
        .quick-link {
            background: white;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: all 0.2s;
            font-size: 13px;
            font-weight: bold;
        }
        .quick-link:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.12); color: #1a73e8; }
        .quick-link .ql-icon { font-size: 1.8rem; display: block; margin-bottom: 6px; }

        @media (max-width: 700px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; display: flex; flex-wrap: wrap; padding: 10px; gap: 5px; }
            .sidebar a { border-left: none; border-radius: 6px; flex: 1; justify-content: center; min-width: 100px; }
            .sidebar-title, .sidebar hr { display: none; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">⚙️ Admin — ShopESA</div>
    <div>
        <a href="../index.php">🏠 Voir la boutique</a>
        <a href="../deconnexion.php">Déconnexion</a>
    </div>
</nav>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="ajouter_produit.php">📦 Produits</a>
        <a href="commandes.php">🧾 Commandes</a>
        <hr>
        <a href="../index.php">🛍️ Boutique</a>
        <a href="../deconnexion.php">🚪 Déconnexion</a>
    </aside>

    <!-- CONTENU -->
    <main class="content">
        <h1>Tableau de bord</h1>
        <p class="subtitle">Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?> 👋</p>

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bleu">👥</div>
                <div>
                    <div class="chiffre"><?= $nb_clients ?></div>
                    <div class="label">Clients inscrits</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon vert">📦</div>
                <div>
                    <div class="chiffre"><?= $nb_produits ?></div>
                    <div class="label">Produits en catalogue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">🧾</div>
                <div>
                    <div class="chiffre"><?= $nb_commandes ?></div>
                    <div class="label">Commandes reçues</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rouge">💰</div>
                <div>
                    <div class="chiffre"><?= number_format($ca_total, 0, ',', ' ') ?></div>
                    <div class="label">FCFA de chiffre d'affaires</div>
                </div>
            </div>
        </div>

        <!-- ACCÈS RAPIDES -->
        <div class="quick-links">
            <a href="ajouter_produit.php" class="quick-link">
                <span class="ql-icon">➕</span>Ajouter produit
            </a>
            <a href="commandes.php" class="quick-link">
                <span class="ql-icon">🧾</span>Commandes
            </a>
            <a href="../index.php" class="quick-link">
                <span class="ql-icon">🛍️</span>Voir boutique
            </a>
        </div>

        <!-- TABLEAU 2 COLONNES -->
        <div class="deux-col">

            <!-- Commandes récentes -->
            <div class="section-card">
                <h2>🧾 Dernières commandes</h2>
                <?php if (empty($commandes_recentes)): ?>
                    <p style="padding:20px; color:#888; text-align:center">Aucune commande pour l'instant.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes_recentes as $cmd): ?>
                        <tr>
                            <td>#<?= $cmd['id'] ?></td>
                            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                            <td style="font-weight:bold; color:#1a73e8">
                                <?= number_format($cmd['total'] /1.18, 0, ',', ' ') ?> FCFA
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'en_attente' => ['badge-attente',  'En attente'],
                                    'expediee'   => ['badge-expediee', 'Expédiée'],
                                    'livree'     => ['badge-livree',   'Livrée'],
                                    'annulee'    => ['badge-annulee',  'Annulée'],
                                ];
                                $b = $badges[$cmd['statut']] ?? ['badge-attente', $cmd['statut']];
                                ?>
                                <span class="badge <?= $b[0] ?>"><?= $b[1] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Produits en rupture -->
            <div class="section-card">
                <h2>⚠️ Produits en rupture de stock</h2>
                <?php if (empty($ruptures)): ?>
                    <p style="padding:20px; color:#4caf50; text-align:center">✅ Tous les produits sont en stock !</p>
                <?php else: ?>
                    <?php foreach ($ruptures as $r): ?>
                    <div class="rupture-item">
                        <span class="rupture-nom">🛍️ <?= htmlspecialchars($r['nom']) ?></span>
                        <a href="ajouter_produit.php" style="font-size:12px; color:#1a73e8">Modifier</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

</body>
</html>