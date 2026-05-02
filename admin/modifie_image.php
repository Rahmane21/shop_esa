<?php
// admin/modifier_image.php — Modifier l'image d'un produit existant
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$succes  = '';
$erreurs = [];

// Récupérer l'ID du produit
$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header('Location: ajouter_produit.php');
    exit;
}

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    header('Location: ajouter_produit.php');
    exit;
}

// =============================================
// TRAITEMENT : UPLOAD NOUVELLE IMAGE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_FILES['image']['name'])) {
        $erreurs[] = "Veuillez choisir une image.";
    } else {
        $ext_ok = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext    = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $ext_ok)) {
            $erreurs[] = "Format non accepté. Utilisez JPG, PNG, GIF ou WEBP.";
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $erreurs[] = "Image trop grande (max 3 Mo).";
        } else {
            // Supprimer l'ancienne image si elle existe
            if (!empty($produit['image']) && file_exists('../uploads/' . $produit['image'])) {
                unlink('../uploads/' . $produit['image']);
            }

            // Nouveau nom unique
            $nouveau_nom = uniqid('prod_') . '.' . $ext;
            $destination = '../uploads/' . $nouveau_nom;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Mettre à jour en BDD
                $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")
                    ->execute([$nouveau_nom, $product_id]);

                // Recharger le produit pour afficher la nouvelle image
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $produit = $stmt->fetch(PDO::FETCH_ASSOC);

                $succes = "✅ Image mise à jour avec succès !";
            } else {
                $erreurs[] = "Erreur lors de l'upload. Vérifiez les permissions du dossier uploads/.";
            }
        }
    }
}

// Tous les produits pour la liste
$tous = $pdo->query("
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
    <title>Admin — Modifier image</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --bg2:#111318; --bg3:#181b22;
            --border:#23272f; --border2:#2e3340;
            --accent:#6366f1; --accent2:#818cf8; --glow:rgba(99,102,241,0.22);
            --green:#22c55e; --red:#ef4444;
            --text:#e8eaf0; --text2:#9ca3af; --text3:#4b5563;
            --font:'Outfit',sans-serif; --radius:14px; --radius-sm:8px;
            --ease:0.22s cubic-bezier(0.4,0,0.2,1);
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:var(--font); background:var(--bg); color:var(--text); min-height:100vh; -webkit-font-smoothing:antialiased; }

        /* NAVBAR */
        nav { background:rgba(17,19,24,0.95); border-bottom:1px solid var(--border); padding:0 2rem; height:64px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .logo { font-size:1.3rem; font-weight:800; letter-spacing:-0.03em; background:linear-gradient(135deg,#fff 30%,var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .nav-links { display:flex; gap:6px; }
        .nav-link { padding:0.4rem 0.9rem; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:500; color:var(--text2); border:1px solid transparent; transition:all var(--ease); }
        .nav-link:hover { background:var(--bg3); border-color:var(--border); color:var(--text); }

        /* LAYOUT */
        .layout { display:flex; min-height:calc(100vh - 64px); }

        /* SIDEBAR */
        .sidebar { width:220px; background:var(--bg2); border-right:1px solid var(--border); padding:20px 0; flex-shrink:0; }
        .sidebar-title { font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:var(--text3); padding:0 20px; margin-bottom:8px; }
        .sidebar a { display:flex; align-items:center; gap:10px; padding:11px 20px; color:var(--text2); text-decoration:none; font-size:0.875rem; border-left:3px solid transparent; transition:all var(--ease); }
        .sidebar a:hover, .sidebar a.active { background:rgba(99,102,241,0.1); color:var(--accent2); border-left-color:var(--accent); }
        .sidebar hr { border:none; border-top:1px solid var(--border); margin:10px 0; }

        /* CONTENT */
        .content { flex:1; padding:28px; overflow-x:hidden; }

        h1 { font-size:1.3rem; font-weight:700; color:var(--text); margin-bottom:6px; letter-spacing:-0.02em; }
        .subtitle { color:var(--text2); font-size:0.875rem; margin-bottom:24px; }

        /* MESSAGES */
        .msg { padding:12px 18px; border-radius:var(--radius-sm); margin-bottom:20px; font-size:0.875rem; font-weight:500; display:flex; align-items:center; gap:8px; }
        .msg.success { background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:var(--green); }
        .msg.error   { background:rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.3);  color:var(--red); }

        /* GRID */
        .grid { display:grid; grid-template-columns:380px 1fr; gap:24px; align-items:start; }
        @media(max-width:900px){ .grid { grid-template-columns:1fr; } }

        /* CARD */
        .card { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius); padding:24px; }
        .card h2 { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border); }

        /* IMAGE ACTUELLE */
        .img-actuelle {
            width:100%; height:220px; border-radius:var(--radius-sm);
            object-fit:cover; border:1px solid var(--border2);
            margin-bottom:16px;
        }
        .img-placeholder-grand {
            width:100%; height:220px; border-radius:var(--radius-sm);
            background:linear-gradient(135deg,var(--bg3),#1a1d27);
            display:flex; align-items:center; justify-content:center;
            font-size:4rem; border:1px solid var(--border2); margin-bottom:16px;
        }

        /* PRODUIT INFO */
        .produit-info { margin-bottom:20px; padding:14px; background:var(--bg3); border-radius:var(--radius-sm); border:1px solid var(--border); }
        .produit-nom  { font-weight:700; color:var(--text); font-size:1rem; margin-bottom:4px; }
        .produit-meta { font-size:0.8rem; color:var(--text2); }

        /* FORM */
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:0.8rem; font-weight:600; color:var(--text2); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.06em; }

        /* Zone de drop */
        .drop-zone {
            border:2px dashed var(--border2);
            border-radius:var(--radius);
            padding:30px;
            text-align:center;
            cursor:pointer;
            transition:all var(--ease);
            position:relative;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color:var(--accent);
            background:rgba(99,102,241,0.05);
        }
        .drop-zone input[type="file"] {
            position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
        }
        .drop-icon { font-size:2.5rem; margin-bottom:8px; }
        .drop-text { color:var(--text2); font-size:0.875rem; }
        .drop-text span { color:var(--accent2); font-weight:600; }
        .drop-formats { font-size:0.75rem; color:var(--text3); margin-top:6px; }

        /* Aperçu */
        #apercu-nouvelle {
            width:100%; max-height:180px; object-fit:cover;
            border-radius:var(--radius-sm); margin-top:14px;
            border:1px solid var(--border2); display:none;
        }

        /* Bouton submit */
        .btn-submit {
            width:100%; padding:13px; background:var(--accent); color:#fff;
            border:none; border-radius:var(--radius-sm); font-size:0.95rem;
            font-weight:700; cursor:pointer; font-family:var(--font);
            transition:all var(--ease); margin-top:16px;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-submit:hover { background:var(--accent2); box-shadow:0 0 24px var(--glow); transform:translateY(-1px); }

        /* LISTE PRODUITS */
        .produits-list { display:flex; flex-direction:column; gap:10px; max-height:600px; overflow-y:auto; }
        .produit-item {
            display:flex; align-items:center; gap:14px;
            padding:12px 16px; border-radius:var(--radius-sm);
            border:1px solid var(--border); background:var(--bg3);
            text-decoration:none; transition:all var(--ease);
        }
        .produit-item:hover { border-color:var(--border2); background:#1e2130; }
        .produit-item.selected { border-color:var(--accent); background:rgba(99,102,241,0.08); }

        .thumb {
            width:52px; height:52px; border-radius:var(--radius-sm);
            object-fit:cover; flex-shrink:0;
        }
        .thumb-ph {
            width:52px; height:52px; border-radius:var(--radius-sm);
            background:var(--bg2); display:flex; align-items:center;
            justify-content:center; font-size:1.4rem; flex-shrink:0;
            border:1px solid var(--border);
        }
        .produit-item-info { flex:1; min-width:0; }
        .produit-item-nom  { font-weight:600; color:var(--text); font-size:0.875rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .produit-item-cat  { font-size:0.75rem; color:var(--text2); margin-top:2px; }

        .badge-img-ok  { font-size:0.7rem; padding:2px 8px; border-radius:999px; background:rgba(34,197,94,0.1); color:var(--green); white-space:nowrap; }
        .badge-img-non { font-size:0.7rem; padding:2px 8px; border-radius:999px; background:rgba(239,68,68,0.1); color:var(--red); white-space:nowrap; }

        /* Scrollbar */
        ::-webkit-scrollbar { width:5px; }
        ::-webkit-scrollbar-track { background:var(--bg); }
        ::-webkit-scrollbar-thumb { background:var(--border2); border-radius:99px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">⚙️ Admin ShopESA</div>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="../index.php" class="nav-link">🏠 Boutique</a>
        <a href="../deconnexion.php" class="nav-link" style="color:var(--red)">Déconnexion</a>
    </div>
</nav>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="ajouter_produit.php" class="active">📦 Produits</a>
        <a href="commandes.php">🧾 Commandes</a>
        <hr>
        <a href="../index.php">🛍️ Boutique</a>
        <a href="../deconnexion.php" style="color:var(--red)">🚪 Déconnexion</a>
    </aside>

    <main class="content">
        <h1>🖼️ Modifier l'image d'un produit</h1>
        <p class="subtitle">Sélectionne un produit dans la liste et uploade sa nouvelle image</p>

        <?php if ($succes): ?>
            <div class="msg success"><?= $succes ?></div>
        <?php endif; ?>
        <?php if (!empty($erreurs)): ?>
            <div class="msg error">
                <?php foreach ($erreurs as $e): ?>
                    <div>⚠️ <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grid">

            <!-- ===== FORMULAIRE ===== -->
            <div class="card">
                <h2>📦 Produit sélectionné</h2>

                <!-- Image actuelle -->
                <?php if (!empty($produit['image']) && file_exists('../uploads/' . $produit['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($produit['image']) ?>"
                         alt="Image actuelle" class="img-actuelle">
                <?php else: ?>
                    <div class="img-placeholder-grand">🛍️</div>
                <?php endif; ?>

                <!-- Infos produit -->
                <div class="produit-info">
                    <div class="produit-nom"><?= htmlspecialchars($produit['nom']) ?></div>
                    <div class="produit-meta">
                        Prix : <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA
                        · Stock : <?= $produit['stock'] ?>
                        · <?= !empty($produit['image']) ? '📸 Image existante' : '❌ Pas d\'image' ?>
                    </div>
                </div>

                <!-- Formulaire upload -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nouvelle image</label>
                        <div class="drop-zone" id="dropZone">
                            <input type="file" name="image" id="input-img"
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="drop-icon">📁</div>
                            <div class="drop-text">
                                Glisse ton image ici ou <span>clique pour choisir</span>
                            </div>
                            <div class="drop-formats">JPG, PNG, GIF, WEBP · Max 3 Mo</div>
                        </div>
                        <img id="apercu-nouvelle" src="" alt="Aperçu">
                    </div>

                    <button type="submit" class="btn-submit">
                        🖼️ Mettre à jour l'image
                    </button>
                </form>
            </div>

            <!-- ===== LISTE PRODUITS ===== -->
            <div class="card">
                <h2>📋 Choisir un autre produit</h2>
                <div class="produits-list">
                    <?php foreach ($tous as $p): ?>
                    <a href="modifier_image.php?id=<?= $p['id'] ?>"
                       class="produit-item <?= $p['id'] === $product_id ? 'selected' : '' ?>">

                        <!-- Miniature -->
                        <?php if (!empty($p['image']) && file_exists('../uploads/' . $p['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                                 class="thumb" alt="<?= htmlspecialchars($p['nom']) ?>">
                        <?php else: ?>
                            <div class="thumb-ph">🛍️</div>
                        <?php endif; ?>

                        <!-- Infos -->
                        <div class="produit-item-info">
                            <div class="produit-item-nom"><?= htmlspecialchars($p['nom']) ?></div>
                            <div class="produit-item-cat"><?= htmlspecialchars($p['cat_nom'] ?? '-') ?></div>
                        </div>

                        <!-- Badge image -->
                        <?php if (!empty($p['image']) && file_exists('../uploads/' . $p['image'])): ?>
                            <span class="badge-img-ok">✅ Image</span>
                        <?php else: ?>
                            <span class="badge-img-non">❌ Aucune</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    // Aperçu image avant upload
    document.getElementById('input-img').addEventListener('change', function(e) {
        const apercu = document.getElementById('apercu-nouvelle');
        const file   = e.target.files[0];
        if (file) {
            apercu.src = URL.createObjectURL(file);
            apercu.style.display = 'block';
            document.querySelector('.drop-icon').textContent = '✅';
            document.querySelector('.drop-text').innerHTML =
                '<span>' + file.name + '</span>';
        }
    });

    // Effet drag over
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('dragover'); });
</script>

</body>
</html>