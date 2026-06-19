<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SkillSprint — Log In</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/css/landing.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>
<body>
    @php $bolt = '<svg viewBox="0 0 24 24" fill="#fff"><path d="M13 2L4.5 13.5H11l-1 8.5L19.5 10H13l0-8z"/></svg>'; @endphp

    {{-- ===== Header ===== --}}
    <header class="site-header">
        <div class="container">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-logo">{!! $bolt !!}</span>
                <span class="brand-name">Skill<span>Sprint</span></span>
            </a>

            <div class="header-right">
                <a href="#" class="login-link" onclick="showTab('login'); return false;">Log In</a>
                <a href="#" class="btn-grad" onclick="showTab('register'); return false;">Get Started Free</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="login-hero">
            {{-- LEFT --}}
            <section>
                <h1 class="hero-title">
                    Your Learning<br>
                    Journey Starts<br>
                    <span class="grad-text">Right Here</span>
                </h1>

                <p class="hero-sub">
                    Upload materials, get AI-powered summaries, flashcards, and quizzes — all designed for your unique learning style.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon fi-purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v4M3 5h4M6 17v4M4 19h4"/><path d="M13 3l2.5 6.5L22 12l-6.5 2.5L13 21l-2.5-6.5L4 12l6.5-2.5L13 3z"/></svg>
                        </div>
                        <div class="feature-text">
                            <h3>AI Smart Summaries</h3>
                            <p>Instant insights from any material</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon fi-pink">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </div>
                        <div class="feature-text">
                            <h3>Smart Flashcards</h3>
                            <p>Spaced repetition for max retention</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon fi-green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>
                        </div>
                        <div class="feature-text">
                            <h3>Inclusive by Design</h3>
                            <p>WCAG 2.2 AA — built for every learner</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- RIGHT --}}
            <section>
                @php $tab = ($errors->has('name') || old('form') === 'register') ? 'register' : 'login'; @endphp
                <div class="auth-card">
                    <div class="tab-switch">
                        <button type="button" id="tabLogin" class="{{ $tab === 'login' ? 'active' : '' }}" onclick="showTab('login')">Log In</button>
                        <button type="button" id="tabRegister" class="{{ $tab === 'register' ? 'active' : '' }}" onclick="showTab('register')">Sign Up</button>
                    </div>

                    <p class="continue-label">CONTINUE WITH</p>
                    <div class="social-row">
                        <button type="button" class="social-btn">
                            <svg viewBox="0 0 24 24"><path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.4-1.7 4.1-5.5 4.1-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.5l2.6-2.5C16.7 3.1 14.6 2 12 2 6.9 2 2.8 6.1 2.8 12S6.9 22 12 22c5.5 0 9.1-3.9 9.1-9.3 0-.6-.1-1.1-.2-1.5H12z"/></svg>
                            Google
                        </button>
                        <button type="button" class="social-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.4 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.3 9.3 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.4 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5a10 10 0 0 0 6.8-9.7C22 6.6 17.5 2 12 2z"/></svg>
                            GitHub
                        </button>
                        <button type="button" class="social-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.4 12.7c0-2.3 1.9-3.4 2-3.5-1.1-1.6-2.8-1.8-3.4-1.8-1.4-.1-2.8.9-3.5.9-.7 0-1.8-.8-3-.8-1.5 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.2 9.3.8 1.1 1.7 2.4 2.9 2.3 1.2 0 1.6-.7 3-.7 1.4 0 1.8.7 3 .7 1.2 0 2-1.1 2.8-2.2.9-1.3 1.2-2.5 1.3-2.6-.1 0-2.5-.9-2.5-3.6zM14.2 5.9c.6-.8 1-1.9.9-3-.9 0-2 .6-2.7 1.4-.6.7-1.1 1.8-.9 2.9 1 .1 2-.5 2.7-1.3z"/></svg>
                            Apple
                        </button>
                    </div>

                    <div class="divider-text">or continue with email</div>

                    {{-- LOG IN FORM --}}
                    <form id="loginForm" class="auth-form {{ $tab === 'login' ? '' : 'hidden' }}" method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="form" value="login">
                        <div class="field">
                            <div class="field-head"><label for="email">Email Address</label></div>
                            <div class="input-wrap">
                                <span class="lead-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('form') === 'register' ? '' : old('email') }}" placeholder="you@example.com" required autocomplete="email">
                            </div>
                            @if ($tab === 'login') @error('email') <span class="field-error">{{ $message }}</span> @enderror @endif
                        </div>

                        <div class="field">
                            <div class="field-head">
                                <label for="password">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">Forgot password?</a>
                                @endif
                            </div>
                            <div class="input-wrap">
                                <span class="lead-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                </span>
                                <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                <button type="button" class="eye" onclick="togglePw('password')" aria-label="Show password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            @if ($tab === 'login') @error('password') <span class="field-error">{{ $message }}</span> @enderror @endif
                        </div>

                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me for 30 days</label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <svg viewBox="0 0 24 24" fill="#fff"><path d="M13 2L4.5 13.5H11l-1 8.5L19.5 10H13l0-8z"/></svg>
                            Log In to SkillSprint
                        </button>
                    </form>

                    {{-- REGISTER FORM --}}
                    <form id="registerForm" class="auth-form {{ $tab === 'register' ? '' : 'hidden' }}" method="POST" action="{{ route('register') }}" onsubmit="prepRegister()">
                        @csrf
                        <input type="hidden" name="form" value="register">
                        <input type="hidden" name="name" id="reg_name" value="{{ old('name') }}">
                        <input type="hidden" name="password_confirmation" id="reg_password_confirmation">

                        <div class="name-row">
                            <div class="field">
                                <div class="field-head"><label for="reg_first">First Name</label></div>
                                <div class="input-wrap">
                                    <span class="lead-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                    </span>
                                    <input id="reg_first" type="text" placeholder="Alex" required autocomplete="given-name">
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-head"><label for="reg_last">Last Name</label></div>
                                <div class="input-wrap">
                                    <span class="lead-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                    </span>
                                    <input id="reg_last" type="text" placeholder="Johnson" required autocomplete="family-name">
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <div class="field-head"><label for="reg_email">Email Address</label></div>
                            <div class="input-wrap">
                                <span class="lead-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                </span>
                                <input id="reg_email" type="email" name="email" value="{{ old('form') === 'register' ? old('email') : '' }}" placeholder="you@example.com" required autocomplete="email">
                            </div>
                            @if ($tab === 'register') @error('email') <span class="field-error">{{ $message }}</span> @enderror @endif
                        </div>

                        <div class="field">
                            <div class="field-head"><label for="reg_password">Password</label></div>
                            <div class="input-wrap">
                                <span class="lead-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                </span>
                                <input id="reg_password" type="password" name="password" placeholder="Create a strong password" required autocomplete="new-password" oninput="pwStrength(this.value)">
                                <button type="button" class="eye" onclick="togglePw('reg_password')" aria-label="Show password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <div class="strength">
                                <span class="seg"></span><span class="seg"></span><span class="seg"></span><span class="seg"></span>
                            </div>
                            <span class="strength-label" id="strengthLabel">Enter a password to check strength</span>
                            @if ($tab === 'register') @error('password') <span class="field-error">{{ $message }}</span> @enderror @endif
                        </div>

                        <label class="terms">
                            <input type="checkbox" required>
                            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                        </label>

                        <button type="submit" class="btn-submit">
                            <svg viewBox="0 0 24 24" fill="#fff"><path d="M13 2L4.5 13.5H11l-1 8.5L19.5 10H13l0-8z"/></svg>
                            Create Free Account
                        </button>
                    </form>

                    <div class="card-foot">
                        <span>
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                            Secured with 256-bit encryption
                        </span>
                        <span class="sep">•</span>
                        <span class="wcag">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
                            WCAG 2.2
                        </span>
                    </div>
                </div>

                <p class="explore">Want to explore first? <a href="{{ url('/') }}">Back to home</a></p>
            </section>
        </div>
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5 footer-about">
                    <a href="{{ url('/') }}" class="brand">
                        <span class="brand-logo">{!! $bolt !!}</span>
                        <span class="brand-name">Skill<span>Sprint</span></span>
                    </a>
                    <p>AI-powered bite-sized learning for everyone. Transform any material into an inclusive, personalized learning experience.</p>
                    <div class="footer-socials">
                        <a href="#" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h3l-7 8 8 12h-6l-5-7-5 7H1l8-9L1 2h6l4 6 5-6z"/></svg></a>
                        <a href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21H21v-5.3c0-1.3 0-3-1.8-3s-2 1.4-2 2.9V21h-4z"/></svg></a>
                        <a href="#" aria-label="GitHub"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.4 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.3 9.3 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.4 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5a10 10 0 0 0 6.8-9.7C22 6.6 17.5 2 12 2z"/></svg></a>
                        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01" stroke-linecap="round"/></svg></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2 footer-col">
                    <h4>Product</h4>
                    <ul><li><a href="{{ url('/#features') }}">Features</a></li><li><a href="{{ url('/#pricing') }}">Pricing</a></li><li><a href="#">Changelog</a></li><li><a href="#">Roadmap</a></li></ul>
                </div>
                <div class="col-6 col-lg-2 footer-col">
                    <h4>Learn</h4>
                    <ul><li><a href="#">Documentation</a></li><li><a href="#">Tutorials</a></li><li><a href="#">Blog</a></li><li><a href="#">Community</a></li></ul>
                </div>
                <div class="col-6 col-lg-3 footer-col">
                    <h4>Company</h4>
                    <ul><li><a href="#">About</a></li><li><a href="{{ url('/#accessibility') }}">Accessibility</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 SkillSprint. All rights reserved. Built for inclusive learning.</p>
                <div class="badges">
                    <span class="wcag"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg> WCAG 2.2 AA</span>
                    <span class="a11y"><span class="heart">♥</span> Made with accessibility in mind</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function togglePw(id) {
            var pw = document.getElementById(id);
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }

        function showTab(tab) {
            document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
            document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
            document.getElementById('loginForm').classList.toggle('hidden', tab !== 'login');
            document.getElementById('registerForm').classList.toggle('hidden', tab !== 'register');
        }

        function pwStrength(val) {
            var segs = document.querySelectorAll('#registerForm .strength .seg');
            var label = document.getElementById('strengthLabel');
            var score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            if (val.length === 0) score = 0;

            var colors = ['#ff5a6e', '#fca13d', '#f5d04d', '#38d98a'];
            var labels = ['Too weak', 'Weak', 'Good', 'Strong'];
            segs.forEach(function (seg, i) {
                seg.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,0.10)';
            });
            label.textContent = val.length === 0 ? 'Enter a password to check strength' : labels[Math.max(0, score - 1)];
        }

        function prepRegister() {
            var first = document.getElementById('reg_first').value.trim();
            var last = document.getElementById('reg_last').value.trim();
            document.getElementById('reg_name').value = (first + ' ' + last).trim();
            document.getElementById('reg_password_confirmation').value = document.getElementById('reg_password').value;
            return true;
        }

        // Prefill first/last name from a previous failed registration
        (function () {
            var fullName = document.getElementById('reg_name').value.trim();
            if (fullName) {
                var parts = fullName.split(' ');
                document.getElementById('reg_first').value = parts.shift();
                document.getElementById('reg_last').value = parts.join(' ');
            }
        })();
    </script>
</body>
</html>
