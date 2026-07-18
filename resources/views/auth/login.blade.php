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
            background-color: #F4F4F8;
            position: relative;
            overflow: hidden;
        }

        /* ── Motif vêtements + ciseaux en fond ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cg fill='none' stroke='%230a0a0a' stroke-opacity='0.14' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cg transform='translate(24 20) rotate(-8)'%3E%3Cpath d='M9,3 L3,3 L0,9 L4,12 L7,10 L7,27 L22,27 L22,10 L25,12 L29,9 L26,3 L20,3 C20,3 19,6 14.5,6 C10,6 9,3 9,3 Z'/%3E%3C/g%3E%3Cg transform='translate(140 130) rotate(18)'%3E%3Cpath d='M6 6 L34 34 M34 6 L6 34'/%3E%3Ccircle cx='6' cy='6' r='6.5'/%3E%3Ccircle cx='34' cy='6' r='6.5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        /* ── Formes colorées style TopTopGo ── */
        .shape {
            position: fixed;
            border-radius: 14px;
            pointer-events: none;
            opacity: 0.72;
        }
        /* Gauche */
        .s1  { left:-5%;  top:6%;   width:220px; height:110px; background:#F5C842; transform:rotate(18deg); }
        .s2  { left:-3%;  top:26%;  width:170px; height: 85px; background:#8FA8C8; transform:rotate(-8deg); }
        .s3  { left:-4%;  top:45%;  width:190px; height: 80px; background:#B8A9D9; transform:rotate(14deg); }
        .s4  { left:-2%;  top:68%;  width:200px; height: 95px; background:#F5C842; transform:rotate(-12deg); }
        .s5  { left:-6%;  top:86%;  width:155px; height: 75px; background:#F0A896; transform:rotate(6deg); }
        /* Droite */
        .s6  { right:-6%; top:4%;   width:230px; height:115px; background:#8FA8C8; transform:rotate(-20deg); }
        .s7  { right:-4%; top:26%;  width:180px; height: 88px; background:#B8A9D9; transform:rotate(10deg); }
        .s8  { right:-5%; top:45%;  width:200px; height: 90px; background:#96C8A8; transform:rotate(-6deg); }
        .s9  { right:-4%; top:67%;  width:215px; height:105px; background:#F5C842; transform:rotate(16deg); }
        .s10 { right:-5%; top:86%;  width:165px; height: 78px; background:#8FA8C8; transform:rotate(-9deg); }
        /* Haut/bas centre */
        .s11 { left:42%;  top:-2%;  width:140px; height: 65px; background:#F0A896; transform:rotate(4deg); }
        .s12 { left:42%;  bottom:-2%; width:150px; height:68px; background:#96C8A8; transform:rotate(-4deg); }

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

    <!-- Formes colorées fond -->
    <div class="shape s1"></div>
    <div class="shape s2"></div>
    <div class="shape s3"></div>
    <div class="shape s4"></div>
    <div class="shape s5"></div>
    <div class="shape s6"></div>
    <div class="shape s7"></div>
    <div class="shape s8"></div>
    <div class="shape s9"></div>
    <div class="shape s10"></div>
    <div class="shape s11"></div>
    <div class="shape s12"></div>

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
