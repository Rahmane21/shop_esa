<?php
// commande.php — Validation de la commande
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Récupérer le panier
$stmt = $pdo->prepare("
    SELECT c.quantite, p.id, p.nom, p.prix, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Panier vide → retour
if (empty($items)) {
    header('Location: panier.php');
    exit;
}

// Calcul totaux
$total_ht  = 0;
foreach ($items as $item) $total_ht += $item['prix'] * $item['quantite'];
$tva       = $total_ht * 0.18;
$total_ttc = $total_ht + $tva;

$erreurs = [];

// =============================================
// TRAITEMENT : VALIDATION DE LA COMMANDE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');
    $ville   = trim($_POST['ville']   ?? '');
    $tel     = trim($_POST['tel']     ?? '');

    if (empty($adresse)) $erreurs[] = "L'adresse de livraison est obligatoire.";
    if (empty($ville))   $erreurs[] = "La ville est obligatoire.";
    if (empty($tel))     $erreurs[] = "Le numéro de téléphone est obligatoire.";

    if (empty($erreurs)) {
        // Adresse complète
        $adresse_complete = $adresse . ', ' . $ville . ' — Tél : ' . $tel;

        try {
            $pdo->beginTransaction();

            // 1. Créer la commande
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total, adresse, statut)
                VALUES (?, ?, ?, 'en_attente')
            ");
            $stmt->execute([$user_id, $total_ttc, $adresse_complete]);
            $order_id = $pdo->lastInsertId();

            // 2. Insérer chaque article dans order_items + décrémenter le stock
            foreach ($items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantite, prix_unit)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$order_id, $item['id'], $item['quantite'], $item['prix']]);

                // Décrémenter le stock
                $pdo->prepare("
                    UPDATE products SET stock = stock - ? WHERE id = ?
                ")->execute([$item['quantite'], $item['id']]);
            }

            // 3. Vider le panier
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

            $pdo->commit();

            // Redirection vers confirmation
            $_SESSION['flash_success'] = "✅ Commande #" . $order_id . " passée avec succès ! Merci pour votre achat.";
            header('Location: mes_commandes.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Passer la commande</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:22px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:8px 15px; border-radius:5px; margin-left:8px; }
        nav a:hover { background:rgba(255,255,255,0.3); }

        .container { max-width:900px; margin:25px auto; padding:0 20px; }
        h1 { font-size:22px; color:#333; margin-bottom:20px; }

        .grid { display:grid; grid-template-columns:1fr 380px; gap:25px; align-items:start; }
        @media(max-width:750px){ .grid { grid-template-columns:1fr; } }

        .card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:25px; }
        .card h2 { font-size:17px; color:#333; margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #f0f0f0; }

        /* FORMULAIRE */
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; font-weight:bold; color:#555; margin-bottom:5px; }
        .form-group input, .form-group textarea {
            width:100%; padding:10px 12px;
            border:1px solid #ddd; border-radius:8px;
            font-size:14px; font-family:Arial,sans-serif;
            transition:border-color 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus { outline:none; border-color:#1a73e8; }
        .form-group textarea { min-height:70px; resize:vertical; }

        /* ERREURS */
        .msg-error {
            background:#f8d7da; color:#721c24;
            border:1px solid #f5c6cb;
            padding:12px 16px; border-radius:8px;
            margin-bottom:18px; font-size:14px;
        }

        /* RÉCAP COMMANDE */
        .recap-item {
            display:flex; justify-content:space-between;
            padding:9px 0; border-bottom:1px solid #f5f5f5;
            font-size:13px;
        }
        .recap-item:last-of-type { border-bottom:none; }
        .recap-item .nom { color:#333; }
        .recap-item .qte { color:#888; font-size:12px; }
        .recap-item .prix { font-weight:bold; color:#1a73e8; }

        .total-ligne {
            display:flex; justify-content:space-between;
            padding:8px 0; font-size:14px; color:#555;
            border-bottom:1px solid #f0f0f0;
        }
        .total-ttc {
            display:flex; justify-content:space-between;
            padding:12px 0 0; font-size:18px;
            font-weight:bold; color:#1a73e8;
            border-top:2px solid #1a73e8; margin-top:8px;
        }

        /* BOUTON */
        .btn-commander {
            width:100%; background:#1a73e8; color:white;
            border:none; padding:14px; border-radius:8px;
            font-size:16px; font-weight:bold; cursor:pointer;
            margin-top:18px;
        }
        .btn-commander:hover { background:#1557b0; }
        .btn-retour {
            display:block; text-align:center;
            color:#1a73e8; text-decoration:none;
            font-size:14px; margin-top:12px;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">🛍️ ShopESA</div>
    <div>
        <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
        <a href="panier.php">🛒 Panier</a>
        <a href="index.php">🏠 Accueil</a>
    </div>
</nav>

<div class="container">
    <h1>✅ Finaliser ma commande</h1>

    <?php if (!empty($erreurs)): ?>
        <div class="msg-error">
            <?php foreach ($erreurs as $e): ?>
                <div>⚠️ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <!-- FORMULAIRE LIVRAISON -->
        <div class="card">
            <h2>📦 Informations de livraison</h2>
            <form method="POST">

                <div class="form-group">
                    <label>Adresse complète *</label>
                    <textarea name="adresse" placeholder="Ex : Quartier Bè, Rue des Cocotiers, Maison N°15"
                              required><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ville *</label>
                    <input type="text" name="ville"
                           placeholder="Ex : Lomé"
                           value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Numéro de téléphone *</label>
                    <input type="tel" name="tel"
                           placeholder="Ex : 90 00 00 00"
                           value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>"
                           required>
                </div>

                <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:8px; padding:12px; font-size:13px; color:#795548; margin-top:8px;">
                    💳 <strong>Paiement :</strong> À la livraison (cash)
                </div>

                <button type="submit" class="btn-commander">
                    ✅ Confirmer la commande
                </button>
                <a href="panier.php" class="btn-retour">← Retour au panier</a>

            </form>
        </div>

        <!-- RÉCAPITULATIF -->
        <div class="card">
            <h2>🧾 Récapitulatif (<?= count($items) ?> article<?= count($items) > 1 ? 's' : '' ?>)</h2>

            <?php foreach ($items as $item): ?>
            <div class="recap-item">
                <div>
                    <div class="nom"><?= htmlspecialchars($item['nom']) ?></div>
                    <div class="qte">Qté : <?= $item['quantite'] ?></div>
                </div>
                <div class="prix">
                    <?= number_format($item['prix'] * $item['quantite'], 0, ',', ' ') ?> FCFA
                </div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:15px;">
                <div class="total-ligne">
                    <span>Sous-total HT</span>
                    <span><?= number_format($total_ht, 0, ',', ' ') ?> FCFA</span>
                </div>
                <div class="total-ligne">
                    <span>TVA (18%)</span>
                    <span><?= number_format($tva, 0, ',', ' ') ?> FCFA</span>
                </div>
                <div class="total-ttc">
                    <span>Total TTC</span>
                    <span><?= number_format($total_ttc, 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>