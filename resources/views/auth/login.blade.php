<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Presensi Biometrik & RFID Desa Balunglor</title>
    <!-- Favicon & PWA App Manifest -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#be123c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Presensi Balunglor">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
            background: #0f172a;
        }

        /* ── Sliding Background ── */
        .bg-slideshow {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        .bg-slideshow .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.8s ease-in-out;
        }
        .bg-slideshow .slide.active {
            opacity: 1;
        }
        /* Dark overlay on top of images */
        .bg-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: linear-gradient(
                135deg,
                rgba(15, 23, 42, 0.82) 0%,
                rgba(88, 10, 34, 0.72) 50%,
                rgba(15, 23, 42, 0.80) 100%
            );
            backdrop-filter: blur(2px);
        }

        /* ── Login Card ── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.97);
            border: none;
            border-radius: 1.5rem;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            animation: cardSlideUp 0.7s ease-out;
        }
        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Header ── */
        .card-header {
            background: linear-gradient(135deg, #580a22 0%, #881337 50%, #9f1239 100%);
            color: #fff;
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
            position: relative;
        }

        .logo-wrapper {
            width: 72px;
            height: 72px;
            background: #fff;
            padding: 10px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(245, 158, 11, 0.5);
            transition: transform 0.3s ease;
        }
        .logo-wrapper:hover { transform: scale(1.06); }
        .logo-wrapper img { width: 100%; height: 100%; object-fit: contain; }

        .header-title {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.3;
            margin-bottom: 0.35rem;
        }
        .header-subtitle {
            font-size: 0.72rem;
            color: rgba(255, 200, 200, 0.85);
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* ── Form Body ── */
        .card-body {
            padding: 1.75rem 1.75rem 1.25rem;
        }

        .welcome-note {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .welcome-note .greeting {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .welcome-note .instruction {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Error Box */
        .error-box {
            margin-bottom: 1.25rem;
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 0.75rem;
        }
        .error-box ul { list-style: disc; padding-left: 1.25rem; }
        .error-box li { font-weight: 500; margin-bottom: 0.15rem; }

        /* Form Fields */
        .form-group { margin-bottom: 1rem; }
        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            margin-bottom: 0.35rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            font-size: 0.875rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-input:focus {
            background: #fff;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }
        .form-input::placeholder { color: #94a3b8; }

        .password-wrapper { position: relative; }
        .password-wrapper .form-input { padding-right: 3rem; }
        .toggle-pw-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.25rem;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }
        .toggle-pw-btn:hover { color: #475569; }
        .toggle-pw-btn svg { width: 20px; height: 20px; }

        /* Remember Me */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 500;
            color: #475569;
            user-select: none;
        }
        .remember-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 0.3rem;
            background: #fff;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .remember-checkbox:checked {
            background: #dc2626;
            border-color: #dc2626;
        }
        .remember-checkbox:checked::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 5px;
            width: 5px;
            height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .remember-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.3);
            transition: all 0.2s ease;
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, #991b1b, #b91c1c);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
        }
        .submit-btn:active {
            transform: translateY(0);
        }

        /* ── Footer ── */
        .card-footer {
            background: #f8fafc;
            padding: 0.85rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.68rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>

    <!-- Slideshow Background -->
    <div class="bg-slideshow" id="bgSlideshow">
        <div class="slide active" style="background-image: url('{{ asset('background/IMG_4663.jpg') }}')"></div>
        <div class="slide" style="background-image: url('{{ asset('background/IMG_4665.jpg') }}')"></div>
        <div class="slide" style="background-image: url('{{ asset('background/IMG_4673.jpg') }}')"></div>
    </div>
    <div class="bg-overlay"></div>

    <!-- Login Card -->
    <div class="login-card">
        <!-- Header -->
        <div class="card-header">
            <div class="logo-wrapper">
                <img src="{{ asset('logo.png') }}" alt="Logo Desa Balunglor">
            </div>
            <h1 class="header-title">Sistem Informasi Presensi<br>Biometrik & RFID Desa Balunglor</h1>
            <p class="header-subtitle">Pemerintah Desa Balunglor, Kecamatan Balung, Jember</p>
        </div>

        <!-- Form Body -->
        <div class="card-body">
            <!-- Welcome Note -->
            <div class="welcome-note">
                <p class="greeting">Selamat Datang</p>
                <p class="instruction">Silahkan masukkan Username dan Password Anda</p>
            </div>

            @if($errors->any())
            <div class="error-box">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                        class="form-input"
                        placeholder="Masukkan username anda">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required
                            class="form-input"
                            placeholder="Masukkan password anda">
                        <button type="button" onclick="togglePasswordVisibility()" aria-label="Lihat password" class="toggle-pw-btn">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.007 10.007 0 013.122-.563c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.09 3.568M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember" class="remember-checkbox"
                            {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="submit-btn">MASUK</button>
            </form>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            (PROSENDI 2026 | FASILKOM X TEKNIK ELEKTRO UNIVERSITAS JEMBER)
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        // Background slideshow
        (function() {
            const slides = document.querySelectorAll('#bgSlideshow .slide');
            if (slides.length <= 1) return;
            let current = 0;
            setInterval(function() {
                slides[current].classList.remove('active');
                current = (current + 1) % slides.length;
                slides[current].classList.add('active');
            }, 5000);
        })();
    </script>
</body>
</html>
