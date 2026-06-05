<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSIT — Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        /* ── Formes géométriques fond ── */
        .shape {
            position: absolute;
            border-radius: 12px;
            opacity: 0.06;
            background: #000;
            transform-origin: center;
        }
        .s1 { width: 300px; height: 300px; top: -80px; right: -60px; transform: rotate(20deg); border-radius: 32px; }
        .s2 { width: 200px; height: 200px; top: 60px;  right: 80px;  transform: rotate(35deg); border-radius: 24px; opacity: 0.04; }
        .s3 { width: 400px; height: 400px; bottom: -120px; left: -80px; transform: rotate(-15deg); border-radius: 40px; }
        .s4 { width: 180px; height: 180px; bottom: 60px; left: 120px; transform: rotate(-30deg); border-radius: 20px; opacity: 0.03; }
        .s5 { width: 120px; height: 120px; top: 40%;  right: 8%;   transform: rotate(45deg); border-radius: 16px; opacity: 0.04; }

        /* ── Logo en haut ── */
        .logo-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .logo-top img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .logo-top .brand {
            font-size: 22px;
            font-weight: 700;
            color: #0a0a0a;
            letter-spacing: 3px;
        }

        /* ── Carte ── */
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 40px 36px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 4px 40px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .card h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0a0a0a;
            margin-bottom: 28px;
            text-align: center;
        }

        /* ── Champs ── */
        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 7px;
        }

        .field input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #111;
            background: #fafafa;
            outline: none;
            transition: all 0.2s;
        }

        .field input::placeholder { color: #c4c4c4; }

        .field input:focus {
            border-color: #111;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.06);
        }

        /* ── Remember ── */
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 18px 0 24px;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #0a0a0a;
            cursor: pointer;
            border-radius: 4px;
            border: 1.5px solid #d1d5db;
        }

        .remember label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
            font-weight: 400;
        }

        /* ── Bouton ── */
        .btn {
            width: 100%;
            padding: 14px;
            background: #0a0a0a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }

        .btn:hover {
            background: #222;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        }

        .btn:active { transform: none; box-shadow: none; }

        /* ── Erreur ── */
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-size: 13px;
            padding: 11px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Footer ── */
        .card-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
        }

        .page-footer {
            margin-top: 24px;
            font-size: 12px;
            color: #aaa;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>

    <!-- Formes géométriques -->
    <div class="shape s1"></div>
    <div class="shape s2"></div>
    <div class="shape s3"></div>
    <div class="shape s4"></div>
    <div class="shape s5"></div>

    <!-- Logo -->
    <div class="logo-top">
        <img src="{{ asset('images/logo-gsit.jpg') }}" alt="GSIT">
        
    </div>

    <!-- Carte -->
    <div class="card">

        <h2>Connectez-vous à votre compte</h2>

        @if($errors->any())
            <div class="error">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="votre@gsit.com"
                       required autofocus>
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       required>
            </div>

            <div class="remember">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Rester connecté pendant une semaine</label>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="card-footer">Accès réservé au personnel GSIT autorisé</div>
    </div>

    <p class="page-footer">© {{ date('Y') }} GSIT · Plateforme de gestion interne</p>

</body>
</html>
