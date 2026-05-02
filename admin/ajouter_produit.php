<?php
session_start();
require_once '../config.php';

// Protection admin uniquement
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$erreurs = [];
$succes  = '';

// SUPPRESSION
if (isset($_GET['supprimer'])) {
    $pid = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists('../uploads/' . $img)) unlink('../uploads/' . $img);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$pid]);
    $succes = "✅ Produit supprimé avec succès.";
}

// =============================================
// MISE À JOUR IMAGE D'UN PRODUIT EXISTANT
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    $pid = (int)$_POST['product_id'];

    if (empty($_FILES['nouvelle_image']['name'])) {
        $erreurs[] = "Veuillez choisir une image.";
    } else {
        $ext_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext    = strtolower(pathinfo($_FILES['nouvelle_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $ext_ok)) {
            $erreurs[] = "Format non accepté (JPG, PNG, GIF, WEBP).";
        } elseif ($_FILES['nouvelle_image']['size'] > 2097152) {
            $erreurs[] = "Image trop grande (max 2Mo).";
        } else {
            // Supprimer l'ancienne image
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$pid]);
            $ancienne = $stmt->fetchColumn();
            if ($ancienne && file_exists('../uploads/' . $ancienne)) {
                unlink('../uploads/' . $ancienne);
            }

            // Upload nouvelle image
            $nom_image = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['nouvelle_image']['tmp_name'], '../uploads/' . $nom_image)) {
                $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$nom_image, $pid]);
                $succes = "✅ Image mise à jour avec succès !";
            } else {
                $erreurs[] = "Erreur upload. Vérifiez les permissions du dossier uploads/.";
            }
        }
    }
}

// AJOUT PRODUIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix        = (float)($_POST['prix'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $cat_id      = (int)($_POST['cat_id'] ?? 0);

    if (empty($nom))  $erreurs[] = "Le nom est obligatoire.";
    if ($prix <= 0)   $erreurs[] = "Le prix doit être supérieur à 0.";
    if ($stock < 0)   $erreurs[] = "Le stock ne peut pas être négatif.";
    if ($cat_id <= 0) $erreurs[] = "Veuillez choisir une catégorie.";

    $nom_image = null;
    if (!empty($_FILES['image']['name'])) {
        $ext_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_ok))               $erreurs[] = "Format image non accepté.";
        elseif ($_FILES['image']['size'] > 2097152)  $erreurs[] = "Image trop grande (max 2Mo).";
        else {
            $nom_image = uniqid('prod_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $nom_image)) {
                $erreurs[] = "Erreur lors de l'upload.";
                $nom_image = null;
            }
        }
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("INSERT INTO products (nom, description, prix, stock, cat_id, image) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$nom, $description, $prix, $stock, $cat_id, $nom_image]);
        $succes = "✅ Produit \"" . htmlspecialchars($nom) . "\" ajouté !";
    }
}

// Récupérer données
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$produits   = $pdo->query("
    SELECT p.*, c.nom AS cat_nom FROM products p
    LEFT JOIN categories c ON p.cat_id = c.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestion Produits</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f0f2f5; }
        nav { background:#1a73e8; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; color:white; }
        nav .logo { font-size:20px; font-weight:bold; }
        nav a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:7px 14px; border-radius:5px; font-size:14px; margin-left:8px; }
        nav a:hover { background:rgba(255,255,255,0.3); }
        .container { max-width:1100px; margin:25px auto; padding:0 20px; }
        .msg { padding:12px 18px; border-radius:8px; margin-bottom:18px; font-size:14px; }
        .msg.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .msg.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .grid { display:grid; grid-template-columns:350px 1fr; gap:25px; align-items:start; }
        @media(max-width:800px){ .grid { grid-template-columns:1fr; } }
        .card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); padding:25px; }
        .card h2 { font-size:17px; color:#333; margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #f0f0f0; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; font-size:13px; font-weight:bold; color:#555; margin-bottom:5px; }
        .form-group input, .form-group textarea, .form-group select {
            width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; font-family:Arial,sans-serif;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline:none; border-color:#1a73e8; }
        .form-group textarea { resize:vertical; min-height:75px; }
        #apercu { width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-top:8px; display:none; border:1px solid #eee; }
        .btn-submit { width:100%; background:#1a73e8; color:white; border:none; padding:12px; border-radius:8px; font-size:15px; font-weight:bold; cursor:pointer; margin-top:5px; }
        .btn-submit:hover { background:#1557b0; }
        table { width:100%; border-collapse:collapse; }
        th { background:#f8f9fa; padding:12px 14px; text-align:left; font-size:12px; color:#555; text-transform:uppercase; border-bottom:2px solid #eee; }
        td { padding:10px 14px; border-bottom:1px solid #f0f0f0; font-size:14px; vertical-align:middle; }
        tr:hover td { background:#fafbff; }

        /* Miniature cliquable */
        .thumb {
            width:52px; height:52px; border-radius:8px;
            object-fit:cover; cursor:pointer;
            border:2px solid transparent;
            transition:border-color 0.2s, transform 0.2s;
        }
        .thumb:hover { border-color:#1a73e8; transform:scale(1.08); }
        .thumb-ph {
            width:52px; height:52px; border-radius:8px;
            background:#e3f0ff; display:flex; align-items:center;
            justify-content:center; font-size:20px; cursor:pointer;
            border:2px dashed #90caf9; transition:border-color 0.2s;
        }
        .thumb-ph:hover { border-color:#1a73e8; }

        .badge { display:inline-block; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:bold; }
        .badge-cat { background:#e3f0ff; color:#1a73e8; }
        .badge-ok  { background:#e8f5e9; color:#388e3c; }
        .badge-rup { background:#ffebee; color:#c62828; }

        .btn-del {
            background:#ffebee; color:#e53935; border:1px solid #ffcdd2;
            padding:5px 10px; border-radius:6px; cursor:pointer;
            font-size:12px; text-decoration:none; display:inline-block;
        }
        .btn-del:hover { background:#ffcdd2; }

        /* =============================================
           ZONE UPLOAD IMAGE INLINE
        ============================================= */
        .upload-zone {
            display:none;                          /* cachée par défaut */
            background:#f0f7ff;
            border:1px solid #90caf9;
            border-radius:8px;
            padding:10px 14px;
            margin-top:8px;
        }
        .upload-zone.open { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

        .upload-zone input[type="file"] {
            font-size:12px; flex:1; min-width:0;
            border:1px solid #ddd; border-radius:6px;
            padding:5px 8px; background:white; cursor:pointer;
        }

        .btn-upload-img {
            background:#1a73e8; color:white; border:none;
            padding:7px 14px; border-radius:6px; cursor:pointer;
            font-size:12px; font-weight:bold; white-space:nowrap;
        }
        .btn-upload-img:hover { background:#1557b0; }

        .btn-annuler-upload {
            background:#f5f5f5; color:#555; border:1px solid #ddd;
            padding:7px 12px; border-radius:6px; cursor:pointer;
            font-size:12px;
        }

        .btn-changer-img {
            background:#e3f0ff; color:#1a73e8; border:1px solid #90caf9;
            padding:5px 10px; border-radius:6px; cursor:pointer;
            font-size:12px; font-weight:bold; white-space:nowrap;
        }
        .btn-changer-img:hover { background:#bbdefb; }

        .apercu-inline {
            width:40px; height:40px; border-radius:6px;
            object-fit:cover; display:none; border:1px solid #ddd;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">⚙️ Admin ShopESA</div>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="../index.php">🏠 Boutique</a>
        <a href="../deconnexion.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <?php if ($succes): ?>
        <div class="msg success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>
    <?php if (!empty($erreurs)): ?>
        <div class="msg error">
            <?php foreach ($erreurs as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <!-- FORMULAIRE AJOUT -->
        <div class="card">
            <h2>➕ Ajouter un produit</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Ex : Smartphone XPro" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Décrivez le produit..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Prix (FCFA) *</label>
                    <input type="number" name="prix" value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" placeholder="Ex : 15000" min="0" step="50" required>
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" placeholder="Ex : 50" min="0" required>
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="cat_id" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_POST['cat_id']) && $_POST['cat_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image (JPG, PNG, max 2Mo)</label>
                    <input type="file" name="image" id="input-img" accept="image/*">
                    <img id="apercu" src="" alt="Aperçu">
                </div>
                <button type="submit" name="ajouter" class="btn-submit">➕ Ajouter le produit</button>
            </form>
        </div>

        <!-- LISTE PRODUITS -->
        <div class="card">
            <h2>📦 Produits existants (<?= count($produits) ?>)</h2>
            <?php if (empty($produits)): ?>
                <p style="color:#888;text-align:center;padding:30px">Aucun produit.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): ?>
                        <tr>
                            <!-- IMAGE cliquable pour changer -->
                            <td>
                                <?php if (!empty($p['image']) && file_exists('../uploads/' . $p['image'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                                         class="thumb"
                                         title="Cliquer pour changer l'image"
                                         onclick="toggleUpload(<?= $p['id'] ?>)">
                                <?php else: ?>
                                    <div class="thumb-ph"
                                         title="Cliquer pour ajouter une image"
                                         onclick="toggleUpload(<?= $p['id'] ?>)">
                                        📷
                                    </div>
                                <?php endif; ?>

                                <!-- ZONE UPLOAD INLINE (cachée par défaut) -->
                                <form method="POST" enctype="multipart/form-data"
                                      id="form-img-<?= $p['id'] ?>">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <div class="upload-zone" id="upload-<?= $p['id'] ?>">
                                        <img class="apercu-inline" id="apercu-<?= $p['id'] ?>" src="">
                                        <input type="file" name="nouvelle_image"
                                               accept="image/*"
                                               onchange="previewInline(this, <?= $p['id'] ?>)">
                                        <button type="submit" name="update_image"
                                                class="btn-upload-img">
                                            ✅ Valider
                                        </button>
                                        <button type="button" class="btn-annuler-upload"
                                                onclick="toggleUpload(<?= $p['id'] ?>)">
                                            ✖
                                        </button>
                                    </div>
                                </form>
                            </td>

                            <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                            <td><span class="badge badge-cat"><?= htmlspecialchars($p['cat_nom'] ?? '-') ?></span></td>
                            <td style="font-weight:bold;color:#1a73e8"><?= number_format($p['prix'],0,',',' ') ?> FCFA</td>
                            <td><span class="badge <?= $p['stock'] > 0 ? 'badge-ok' : 'badge-rup' ?>">
                                <?= $p['stock'] > 0 ? $p['stock'].' en stock' : 'Rupture' ?>
                            </span></td>
                            <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                <!-- Bouton changer image -->
                                <button class="btn-changer-img" onclick="toggleUpload(<?= $p['id'] ?>)">
                                    🖼️ Image
                                </button>
                                <!-- Supprimer -->
                                <a href="ajouter_produit.php?supprimer=<?= $p['id'] ?>"
                                   class="btn-del"
                                   onclick="return confirm('Supprimer ce produit ?')">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    // Aperçu image ajout nouveau produit
    document.getElementById('input-img').addEventListener('change', function(e) {
        const apercu = document.getElementById('apercu');
        if (e.target.files[0]) {
            apercu.src = URL.createObjectURL(e.target.files[0]);
            apercu.style.display = 'block';
        } else {
            apercu.style.display = 'none';
        }
    });

    // Afficher / cacher la zone upload inline
    function toggleUpload(id) {
        const zone = document.getElementById('upload-' + id);
        zone.classList.toggle('open');
    }

    // Aperçu image dans la zone inline
    function previewInline(input, id) {
        const apercu = document.getElementById('apercu-' + id);
        if (input.files && input.files[0]) {
            apercu.src = URL.createObjectURL(input.files[0]);
            apercu.style.display = 'block';
        }
    }
</script>

</body>
</html>