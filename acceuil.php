<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopESA — Bienvenue</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #1a73e8;
            --blue-dark: #0d47a1;
            --blue-light: #e3f0ff;
            --accent: #ff3b3b;
            --dark: #f0f4ff;
            --mid: #ffffff;
            --light: #f0f4ff;
            --white: #ffffff;
        }

```
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--dark);
        color: #111;
        overflow-x: hidden;
    }

    /* ── NAVBAR ── */
    nav {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 100;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 60px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(0,0,0,0.08);
        animation: slideDown 0.8s ease both;
    }

    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    .logo {
        font-family: 'Syne', sans-serif;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .logo span { color: var(--blue); }

    .nav-links {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .nav-links a {
        color: rgba(0,0,0,0.65);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 9px 20px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .nav-links a:hover { color: #111; background: rgba(0,0,0,0.06); }
    .nav-links .btn-nav {
        background: var(--blue);
        color: white;
        border-radius: 8px;
        font-weight: 600;
    }
    .nav-links .btn-nav:hover { background: #1558c0; }

    /* ── HERO ── */
    .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 100px 30px 60px;
        position: relative;
        overflow: hidden;
    }

    /* Animated mesh background */
    .hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 20% 40%, rgba(26,115,232,0.25) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 80% 70%, rgba(26,115,232,0.15) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 10%, rgba(255,59,59,0.12) 0%, transparent 50%);
        animation: meshMove 8s ease-in-out infinite alternate;
    }

    @keyframes meshMove {
        0%   { opacity: 0.8; transform: scale(1); }
        100% { opacity: 1;   transform: scale(1.05); }
    }

    /* Grid overlay */
    .hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(26,115,232,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(26,115,232,0.07) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 760px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(26,115,232,0.15);
        border: 1px solid rgba(26,115,232,0.4);
        color: var(--blue);
        font-size: 13px;
        font-weight: 500;
        padding: 7px 18px;
        border-radius: 100px;
        margin-bottom: 30px;
        animation: fadeUp 0.8s 0.2s ease both;
    }
    .hero-badge::before {
        content: '';
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--blue);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(1.5); }
    }

    h1 {
        font-family: 'Syne', sans-serif;
        font-size: clamp(46px, 7vw, 80px);
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -2px;
        margin-bottom: 22px;
        animation: fadeUp 0.8s 0.35s ease both;
    }
    h1 .highlight {
        color: var(--blue);
        position: relative;
    }
    h1 .highlight::after {
        content: '';
        position: absolute;
        bottom: 2px; left: 0; right: 0;
        height: 4px;
        background: var(--blue);
        border-radius: 2px;
        opacity: 0.4;
    }

    .hero-sub {
        font-size: 18px;
        font-weight: 300;
        color: rgba(0,0,0,0.55);
        line-height: 1.6;
        max-width: 520px;
        margin: 0 auto 40px;
        animation: fadeUp 0.8s 0.5s ease both;
    }

    .hero-cta {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeUp 0.8s 0.65s ease both;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--blue);
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        padding: 16px 34px;
        border-radius: 10px;
        transition: all 0.25s;
        box-shadow: 0 8px 30px rgba(26,115,232,0.4);
    }
    .btn-primary:hover {
        background: #1558c0;
        transform: translateY(-2px);
        box-shadow: 0 14px 40px rgba(26,115,232,0.5);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.12);
        color: #111;
        text-decoration: none;
        font-weight: 500;
        font-size: 16px;
        padding: 16px 34px;
        border-radius: 10px;
        transition: all 0.25s;
    }
    .btn-secondary:hover {
        background: rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    /* Floating cards decoration */
    .hero-cards {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 16px;
        animation: fadeUp 1s 0.9s ease both;
        z-index: 2;
    }
    .mini-card {
        background: rgba(255,255,255,0.85);
        border: 1px solid rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 13px;
        color: rgba(0,0,0,0.65);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mini-card .dot { width: 8px; height: 8px; border-radius: 50%; }
    .dot-green  { background: #4caf50; }
    .dot-blue   { background: var(--blue); }
    .dot-orange { background: #ff9800; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(28px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── STATS BAND ── */
    .stats-band {
        background: var(--mid);
        border-top: 1px solid rgba(0,0,0,0.07);
        border-bottom: 1px solid rgba(0,0,0,0.07);
        padding: 40px 60px;
        display: flex;
        justify-content: center;
        gap: 0;
    }
    .stat-item {
        flex: 1;
        max-width: 220px;
        text-align: center;
        padding: 0 30px;
        border-right: 1px solid rgba(0,0,0,0.08);
    }
    .stat-item:last-child { border-right: none; }
    .stat-num {
        font-family: 'Syne', sans-serif;
        font-size: 40px;
        font-weight: 800;
        color: var(--blue);
        display: block;
    }
    .stat-label {
        font-size: 13px;
        color: rgba(0,0,0,0.45);
        font-weight: 300;
        margin-top: 4px;
    }

    /* ── FEATURES ── */
    .section {
        padding: 90px 60px;
    }
    .section-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--blue);
        margin-bottom: 14px;
    }
    .section-title {
        font-family: 'Syne', sans-serif;
        font-size: clamp(28px, 4vw, 44px);
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -1px;
        margin-bottom: 16px;
    }
    .section-sub {
        font-size: 16px;
        font-weight: 300;
        color: rgba(0,0,0,0.5);
        max-width: 480px;
        line-height: 1.6;
        margin-bottom: 54px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    .feature-card {
        background: var(--mid);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 30px;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--blue), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .feature-card:hover {
        transform: translateY(-4px);
        border-color: rgba(26,115,232,0.3);
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
    }
    .feature-card:hover::before { opacity: 1; }

    .feature-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        background: rgba(26,115,232,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 20px;
    }
    .feature-card h3 {
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .feature-card p {
        font-size: 14px;
        color: rgba(0,0,0,0.5);
        line-height: 1.65;
        font-weight: 300;
    }

    /* ── HOW IT WORKS ── */
    .how-section {
        background: var(--mid);
        padding: 90px 60px;
    }
    .steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0;
        position: relative;
    }
    .steps::before {
        content: '';
        position: absolute;
        top: 32px; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(26,115,232,0.4), transparent);
    }
    .step {
        padding: 0 30px;
        text-align: center;
        position: relative;
    }
    .step-num {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: var(--dark);
        border: 2px solid var(--blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Syne', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: var(--blue);
        margin: 0 auto 20px;
        position: relative;
        z-index: 1;
    }
    .step h3 {
        font-family: 'Syne', sans-serif;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .step p {
        font-size: 14px;
        color: rgba(0,0,0,0.5);
        line-height: 1.6;
        font-weight: 300;
    }

    /* ── CTA BAND ── */
    .cta-section {
        padding: 100px 60px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 80% at 50% 50%, rgba(26,115,232,0.2) 0%, transparent 70%);
    }
    .cta-section h2 {
        font-family: 'Syne', sans-serif;
        font-size: clamp(30px, 5vw, 56px);
        font-weight: 800;
        letter-spacing: -1.5px;
        margin-bottom: 18px;
        position: relative;
    }
    .cta-section p {
        font-size: 17px;
        font-weight: 300;
        color: rgba(0,0,0,0.5);
        margin-bottom: 38px;
        position: relative;
    }
    .cta-buttons {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
    }

    /* ── FOOTER ── */
    footer {
        background: var(--mid);
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 30px 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    footer .logo { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; }
    footer p {
        font-size: 13px;
        color: rgba(255,255,255,0.4);
    }
    footer strong { color: rgba(255,255,255,0.7); }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
        nav { padding: 15px 24px; }
        .nav-links a:not(.btn-nav) { display: none; }
        .stats-band { padding: 30px 24px; flex-wrap: wrap; gap: 20px; }
        .stat-item { border-right: none; }
        .section, .how-section, .cta-section { padding: 60px 24px; }
        .hero-cards { display: none; }
        footer { padding: 24px; flex-direction: column; text-align: center; }
    }
</style>
```

</head>
<body>

<!-- NAVBAR -->

<nav>
    <div class="logo">🛍️ Shop<span>ESA</span></div>
    <div class="nav-links">
        <a href="#features">Fonctionnalités</a>
        <a href="#comment">Comment ça marche</a>
        <a href="connexion.php"> 🔑Se connecter</a>
        <a href="inscription.php" class="btn-nav">Créer un compte</a>
    </div>
</nav>

<!-- HERO -->

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">✨ La marketplace de l'ESA-AGOE</div>

```
    <h1>
        Achetez <span class="highlight">mieux</span>,<br>
        vivez plus simple.
    </h1>

    <p class="hero-sub">
        ShopESA est la boutique en ligne dédiée à la communauté ESA-AGOE.
        Trouvez vos produits, gérez votre panier et commandez en toute simplicité.
    </p>

    <div class="hero-cta">
       
    </div>
</div>

<div class="hero-cards">
    <div class="mini-card">
        <span class="dot dot-green"></span>
        Livraison disponible
    </div>
    <div class="mini-card">
        <span class="dot dot-blue"></span>
        Paiement sécurisé
    </div>
    <div class="mini-card">
        <span class="dot dot-orange"></span>
        Support 24h/24
    </div>
</div>
```

</section>

<!-- STATS -->

<div class="stats-band">
    <div class="stat-item">
        <span class="stat-num">500+</span>
        <span class="stat-label">Produits disponibles</span>
    </div>
    <div class="stat-item">
        <span class="stat-num">1 200+</span>
        <span class="stat-label">Utilisateurs inscrits</span>
    </div>
    <div class="stat-item">
        <span class="stat-num">50+</span>
        <span class="stat-label">Catégories</span>
    </div>
    <div class="stat-item">
        <span class="stat-num">99%</span>
        <span class="stat-label">Clients satisfaits</span>
    </div>
</div>

<!-- FEATURES -->

<section class="section" id="features">
    <div class="section-label">Pourquoi ShopESA ?</div>
    <h2 class="section-title">Tout ce dont vous avez<br>besoin, au même endroit.</h2>
    <p class="section-sub">
        Une expérience d'achat fluide, pensée pour les étudiants et la communauté ESA-AGOE.
    </p>

```
<div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">🛒</div>
        <h3>Panier intelligent</h3>
        <p>Ajoutez vos produits, gérez vos quantités et validez votre commande en quelques clics.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>Catalogue par catégorie</h3>
        <p>Parcourez nos produits classés par catégorie pour trouver exactement ce que vous cherchez.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Compte sécurisé</h3>
        <p>Inscrivez-vous une seule fois et accédez à votre espace personnel à tout moment.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">⚙️</div>
        <h3>Espace administrateur</h3>
        <p>Gestion complète des produits, catégories, stocks et commandes depuis un tableau de bord dédié.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">📦</div>
        <h3>Suivi des stocks</h3>
        <p>Disponibilité en temps réel. Ne commandez plus un produit en rupture de stock.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">💳</div>
        <h3>Paiement en FCFA</h3>
        <p>Prix affichés en FCFA, adaptés au contexte local pour une transparence totale.</p>
    </div>
</div>
```

</section>

<!-- HOW IT WORKS -->

<section class="how-section" id="comment">
    <div class="section-label">Processus</div>
    <h2 class="section-title">Comment ça marche ?</h2>
    <p class="section-sub">
        En 4 étapes simples, passez de la découverte à la commande confirmée.
    </p>

```
<div class="steps">
    <div class="step">
        <div class="step-num">1</div>
        <h3>Créez un compte</h3>
        <p>Inscrivez-vous gratuitement en quelques secondes avec vos informations.</p>
    </div>
    <div class="step">
        <div class="step-num">2</div>
        <h3>Parcourez le catalogue</h3>
        <p>Explorez tous nos produits par catégorie et trouvez ce qu'il vous faut.</p>
    </div>
    <div class="step">
        <div class="step-num">3</div>
        <h3>Ajoutez au panier</h3>
        <p>Sélectionnez vos articles et constituez votre panier en un clic.</p>
    </div>
    <div class="step">
        <div class="step-num">4</div>
        <h3>Confirmez la commande</h3>
        <p>Validez votre panier et votre commande est prise en charge immédiatement.</p>
    </div>
</div>
```

</section>

<!-- CTA FINAL -->

<section class="cta-section">
    <h2>Prêt à commencer<br>à faire vos achats ?</h2>
    <p>Rejoignez la communauté ShopESA dès aujourd'hui — c'est gratuit.</p>
    <div class="cta-buttons">
        <a href="inscription.php" class="btn-primary">
            🎉 Créer mon compte gratuitement
        </a>
        <a href="connexion.php" class="btn-secondary">
            J'ai déjà un compte →
        </a>
    </div>
</section>

<!-- FOOTER -->

<footer>
    <div class="logo">🛍️ Shop<span style="color: var(--blue)">ESA</span></div>
    <p>&copy; <?php echo date('Y'); ?> <strong>ShopESA</strong> — Développé par <strong>Rahxime</strong> · ESA-AGOE Licence 2</p>
    <p style="color: rgba(255,255,255,0.25); font-size:12px;">Tous droits réservés</p>
</footer>

</body>
</html>