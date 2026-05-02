<?php
session_start();
require_once 'config.php';

$erreur  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Vérifications
    if (empty($nom) || empty($email) || empty($password)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $erreur = "Le mot de passe doit avoir au moins 6 caractères.";
    } else {
       // Vérifier si email existe déjà
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    $erreur = "Cet email est déjà utilisé.";
} else {
    // Hacher le mot de passe
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Insérer l'utilisateur ← noms corrigés ici
    $stmt = $pdo->prepare("
        INSERT INTO users (nom, email, password, role, created_at)
        VALUES (?, ?, ?, 'client', NOW())
    ");
    $stmt->execute([$nom, $email, $hash]);

    $success = "Compte créé avec succès !";
}
            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription — ShopESA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        h2 {
            text-align: center;
            color: #1a73e8;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }
        input:focus {
            outline: none;
            border-color: #1a73e8;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 5px;
        }
        button:hover { background: #1557b0; }
        .erreur  { background: #fdecea; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .lien { text-align: center; margin-top: 20px; }
        .lien a { color: #1a73e8; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h2>📝 Inscription</h2>

    <?php if ($erreur)  echo "<div class='erreur'>$erreur</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" name="nom" placeholder="Kofi Mensah" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="kofi@gmail.com" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="Min. 6 caractères" required>
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirm" placeholder="Répéter le mot de passe" required>
        </div>
        <button type="submit">Créer mon compte</button>
    </form>

    <div class="lien">
        Déjà un compte ? <a href="connexion.php">Se connecter</a>
    </div>
</div>
</body>
</html>