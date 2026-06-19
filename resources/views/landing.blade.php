<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SkillSprint — Learn Any Skill in Bite-Sized Steps</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/css/landing.css', 'resources/js/app.js'])
</head>
<body>
    @php $bolt = '<svg viewBox="0 0 24 24" fill="#fff"><path d="M13 2L4.5 13.5H11l-1 8.5L19.5 10H13l0-8z"/></svg>'; @endphp
    @php $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 6"/></svg>'; @endphp

    {{-- ===== Header ===== --}}
    <header class="site-header">
        <div class="container">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-logo">{!! $bolt !!}</span>
                <span class="brand-name">Skill<span>Sprint</span></span>
            </a>
            <div class="header-right">
                <a href="{{ route('login') }}" class="login-link">Log In</a>
                <a href="{{ route('login') }}" class="btn-grad">Get Started Free</a>
            </div>
        </div>
    </header>

    {{-- ===== Hero ===== --}}
    <section class="hero">
        <div class="container">
            <span class="pill-badge"><span class="spark">✦</span> AI-Powered Micro-Learning Platform</span>
            <h1 class="hero-display">Learn Any Skill in<br><span class="grad-text">Bite-Sized Steps</span></h1>
            <p class="hero-lead">SkillSprint uses AI to transform complex topics into digestible micro-lessons. Upload your materials, get instant summaries, flashcards, and quizzes — designed for every type of learner.</p>
            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn-grad lg">{!! $bolt !!} Start Micro-Learning</a>
                <a href="#" class="btn-ghost lg">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    Watch Demo
                </a>
            </div>
            <div class="trust-row">
                <span class="item">{!! $check !!} No credit card required</span>
                <span class="item">{!! $check !!} WCAG 2.2 Compliant</span>
                <span class="item">{!! $check !!} Free forever plan</span>
                <span class="item">{!! $check !!} 50+ Languages</span>
            </div>

            {{-- Dashboard mockup --}}
            <div class="dash-wrap">
                <div class="dash-card text-start">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="dash-panel h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="dash-greet">Good morning, Alex 👋<small>Continue your Python journey</small></div>
                                    <span class="brand-logo">{!! $bolt !!}</span>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span style="font-size:14px;font-weight:600;">Python Fundamentals</span>
                                        <span class="tag">In Progress</span>
                                    </div>
                                    <div class="dash-progress-track"><div class="dash-progress-fill"></div></div>
                                    <small class="text-muted">68% complete</small>
                                </div>
                                <div class="row g-2 mt-3">
                                    <div class="col-4"><div class="dash-stat"><div class="num">12</div><div class="lbl">Day Streak</div></div></div>
                                    <div class="col-4"><div class="dash-stat"><div class="num">847</div><div class="lbl">XP Earned</div></div></div>
                                    <div class="col-4"><div class="dash-stat"><div class="num">4</div><div class="lbl">Skills Active</div></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="dash-panel h-100">
                                <div style="font-size:13px;font-weight:700;letter-spacing:.05em;" class="text-muted mb-3">TODAY'S TASKS</div>
                                <div class="dash-task done">
                                    <span class="tick">{!! $check !!}</span>
                                    <div><div class="t-title">Flashcard Review</div><div class="t-sub">Completed</div></div>
                                </div>
                                <div class="dash-task">
                                    <span class="tick"></span>
                                    <div><div class="t-title">Python Quiz</div><div class="t-sub">10 questions</div></div>
                                </div>
                                <div class="dash-task">
                                    <span class="tick"></span>
                                    <div><div class="t-title">Upload Notes</div><div class="t-sub">New material</div></div>
                                </div>
                                <div class="dash-goal mt-3">
                                    <div><div class="g-title">Daily Goal</div><div class="g-sub">2 / 3 lessons</div></div>
                                    <div class="g-sub">🔥 12 day streak</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- {{-- ===== Stat strip ===== --}}
    <section class="stat-strip">
        <div class="container">
            <div class="row">
                <div class="col-6 col-lg-3"><div class="big-stat"><div class="num grad-text">500K+</div><div class="lbl">Active Learners</div></div></div>
                <div class="col-6 col-lg-3"><div class="big-stat"><div class="num grad-text">2M+</div><div class="lbl">Lessons Completed</div></div></div>
                <div class="col-6 col-lg-3"><div class="big-stat"><div class="num grad-text">98%</div><div class="lbl">Satisfaction Rate</div></div></div>
                <div class="col-6 col-lg-3"><div class="big-stat"><div class="num grad-text">50+</div><div class="lbl">Languages Supported</div></div></div>
            </div>
        </div>
    </section> -->

    {{-- ===== Features ===== --}}
    <section class="section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-tag">★ AI-Driven Features</span>
                <h2 class="section-title">Everything You Need to<br><span class="grad-text">Master Any Skill</span></h2>
                <p class="section-sub">Our AI analyzes your learning materials and creates personalized micro-lessons, quizzes, and flashcards tailored to your pace.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v4M3 5h4M6 17v4M4 19h4"/><path d="M13 3l2.5 6.5L22 12l-6.5 2.5L13 21l-2.5-6.5L4 12l6.5-2.5L13 3z"/></svg></div>
                        <h3>AI Smart Summaries</h3>
                        <p>Upload any document, PDF, or URL. Our AI instantly creates concise summaries with key concepts highlighted for fast comprehension.</p>
                        <div class="tag-row"><span class="tag">PDF Support</span><span class="tag">URL Parsing</span><span class="tag">Auto-outline</span></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-pink"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
                        <h3>Smart Flashcards</h3>
                        <p>Spaced-repetition flashcards generated automatically from your content. Review at the perfect moment for maximum retention.</p>
                        <div class="tag-row"><span class="tag">Spaced Repetition</span><span class="tag">Auto-generated</span></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                        <h3>Adaptive Quizzes</h3>
                        <p>AI-generated quizzes that adapt to your performance. Get harder questions as you improve, easier ones when you need them.</p>
                        <div class="tag-row"><span class="tag">Adaptive AI</span><span class="tag">Instant Feedback</span></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
                        <h3>Learning Timeline</h3>
                        <p>Visual learning paths that break complex skills into achievable milestones. Track your journey from beginner to expert.</p>
                        <div class="tag-row"><span class="tag">Visual Path</span><span class="tag">Milestones</span></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg></div>
                        <h3>Learning Analytics</h3>
                        <p>Deep insights into your learning patterns. Understand your strengths, identify gaps, and optimize your study sessions.</p>
                        <div class="tag-row"><span class="tag">Progress Charts</span><span class="tag">Insights</span></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feat-card">
                        <div class="feat-icon fi-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5"/></svg></div>
                        <h3>Achievements &amp; XP</h3>
                        <p>Gamified learning with streaks, badges, and XP points. Stay motivated with daily challenges and leaderboards.</p>
                        <div class="tag-row"><span class="tag">Gamification</span><span class="tag">Daily Streaks</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== How it works ===== --}}
    <section class="section" id="how" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-tag">⚡ Simple Process</span>
                <h2 class="section-title">From Upload to<br><span class="grad-text">Expert in Minutes</span></h2>
                <p class="section-sub">Three simple steps to transform any material into a complete learning experience.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5-5 5 5"/><path d="M12 5v12"/></svg></div>
                        <h3>Upload Your Material</h3>
                        <p>Drop in PDFs, paste URLs, or type your topic. SkillSprint accepts any format and extracts the key knowledge.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/><path d="M9 21h6M10 17v4M14 17v4"/></svg></div>
                        <h3>AI Creates Your Path</h3>
                        <p>Our AI generates a personalized learning timeline, smart summaries, flashcards, and adaptive quizzes — all in seconds.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg></div>
                        <h3>Learn &amp; Track Progress</h3>
                        <p>Engage with bite-sized lessons, track your streaks, earn XP, and watch your skills grow with detailed analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Accessibility ===== --}}
    <section class="section" id="accessibility">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <span class="section-tag">♿ Inclusive by Design</span>
                    <h2 class="section-title">Learning Without<br><span class="grad-text">Barriers</span></h2>
                    <p class="section-sub mb-4" style="margin-left:0;">SkillSprint is built from the ground up for every learner. Whether you have dyslexia, visual impairments, ADHD, or simply prefer different learning styles — we've got you covered.</p>

                    <div class="a11y-feature">
                        <div class="ic fi-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg></div>
                        <div><h3>Screen Reader Compatible</h3><p>Full ARIA support, semantic HTML, and keyboard navigation throughout the entire platform.</p></div>
                    </div>
                    <div class="a11y-feature">
                        <div class="ic fi-pink"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5h16v2M9 5v14M7 19h4"/></svg></div>
                        <div><h3>Dyslexia-Friendly Fonts</h3><p>Switch to OpenDyslexic font, adjust letter spacing, and customize text size for comfortable reading.</p></div>
                    </div>
                    <div class="a11y-feature">
                        <div class="ic fi-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 0 0 18z" fill="currentColor"/></svg></div>
                        <div><h3>High Contrast &amp; Color Modes</h3><p>Multiple color themes including high contrast, dark, light, and color-blind friendly palettes.</p></div>
                    </div>
                    <div class="a11y-feature">
                        <div class="ic fi-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a9 9 0 0 1 0 14"/></svg></div>
                        <div><h3>Text-to-Speech Built In</h3><p>Listen to any lesson, summary, or flashcard with natural-sounding AI voice narration.</p></div>
                    </div>

                    <div class="tag-row mt-2">
                        <span class="tag">WCAG 2.2 AA</span><span class="tag">Keyboard First</span><span class="tag">Screen Reader</span><span class="tag">Voice Accessible</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="a11y-panel">
                        <div class="ph"><h4>Accessibility Settings</h4><span class="tag">Active</span></div>
                        <div class="a11y-row">
                            <span class="label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V5h16v2M9 5v14M7 19h4"/></svg> Dyslexia Font</span>
                            <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" checked></div>
                        </div>
                        <div class="a11y-row">
                            <span class="label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/></svg> Text-to-Speech</span>
                            <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" checked></div>
                        </div>
                        <div class="a11y-row">
                            <span class="label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/></svg> Reduce Motion</span>
                            <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch"></div>
                        </div>
                        <div class="a11y-row d-block">
                            <span class="label mb-2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg> Text Size</span>
                            <input type="range" class="form-range" min="0" max="100" value="60">
                        </div>
                        <div class="a11y-row d-block">
                            <span class="label mb-2">Color Theme</span>
                            <div class="theme-dots">
                                <span style="background:#0a0a0f;"></span>
                                <span style="background:#f5f5f7;"></span>
                                <span style="background:linear-gradient(135deg,#7c5cfc,#d56bff);"></span>
                                <span style="background:#000;border-color:#fff;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== UI states ===== --}}
    <section class="section" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Thoughtful <span class="grad-text">UI States</span></h2>
                <p class="section-sub">Every interaction is designed with clear feedback — loading, empty, and error states keep users informed.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="state-card">
                        <span class="badge-top">⏳ Loading State</span>
                        <div class="skel" style="width:80%"></div>
                        <div class="skel" style="width:100%"></div>
                        <div class="skel" style="width:65%"></div>
                        <div class="skel" style="width:90%"></div>
                        <div class="skel" style="width:40%"></div>
                        <small class="text-muted">AI is generating your lesson...</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="state-card">
                        <span class="badge-top">📭 Empty State</span>
                        <div class="empty-mid">
                            <div class="circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5-5 5 5M12 5v12"/></svg></div>
                            <h4>No Skills Yet</h4>
                            <p>Start your learning journey by uploading your first material or choosing from our skill library.</p>
                            <a href="{{ route('register') }}" class="btn-grad">+ Add First Skill</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="state-card">
                        <span class="badge-top">⚠️ Error State</span>
                        <div class="error-box mb-3">
                            <span class="ic"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/></svg></span>
                            <div><h5>Upload Failed</h5><p>File format not supported. Please upload a PDF, DOCX, or TXT file.</p></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn-grad">Try Again</a>
                            <a href="#" class="btn-ghost">Get Help</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- {{-- ===== Testimonials ===== --}}
    <section class="section" id="testimonials">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Loved by <span class="grad-text">Diverse Learners</span></h2>
                <p class="section-sub">From students to professionals, SkillSprint adapts to every learning style.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="quote">"As someone with dyslexia, I've struggled with traditional learning apps. SkillSprint's dyslexia font and text-to-speech features are game-changers. I finally feel like learning is accessible to me."</p>
                        <div class="testi-person"><img src="https://i.pravatar.cc/80?img=47" alt=""><div><div class="name">Sarah M.</div><div class="role">Graphic Designer</div></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="quote">"I uploaded my entire Python course materials and SkillSprint created perfect flashcards and quizzes in under 2 minutes. My retention has improved dramatically. Absolutely incredible AI."</p>
                        <div class="testi-person"><img src="https://i.pravatar.cc/80?img=12" alt=""><div><div class="name">Marcus T.</div><div class="role">Software Engineer</div></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="quote">"The bite-sized lessons are perfect for my busy schedule. I can learn in 5-minute bursts and still make real progress. The streak system keeps me coming back every single day!"</p>
                        <div class="testi-person"><img src="https://i.pravatar.cc/80?img=32" alt=""><div><div class="name">Priya K.</div><div class="role">Medical Student</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- {{-- ===== Pricing ===== --}}
    <section class="section" id="pricing" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Simple, <span class="grad-text">Transparent Pricing</span></h2>
                <p class="section-sub">Start for free. Upgrade when you're ready.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="price-card">
                        <div class="plan">Free</div>
                        <div class="price">$0<small>/month</small></div>
                        <ul>
                            <li>{!! $check !!} 5 AI summaries/month</li>
                            <li>{!! $check !!} 50 flashcards</li>
                            <li>{!! $check !!} Basic analytics</li>
                            <li>{!! $check !!} All accessibility features</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost w-100 justify-content-center">Get Started Free</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="price-card popular">
                        <span class="popular-flag">Most Popular</span>
                        <div class="plan">Pro</div>
                        <div class="price">$12<small>/month</small></div>
                        <ul>
                            <li>{!! $check !!} Unlimited AI summaries</li>
                            <li>{!! $check !!} Unlimited flashcards</li>
                            <li>{!! $check !!} Advanced analytics</li>
                            <li>{!! $check !!} Priority AI processing</li>
                            <li>{!! $check !!} Offline mode</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-grad w-100 justify-content-center">Start Pro Plan</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="price-card">
                        <div class="plan">Team</div>
                        <div class="price">$39<small>/month</small></div>
                        <ul>
                            <li>{!! $check !!} Everything in Pro</li>
                            <li>{!! $check !!} Up to 10 members</li>
                            <li>{!! $check !!} Shared skill libraries</li>
                            <li>{!! $check !!} Admin dashboard</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost w-100 justify-content-center">Get Team Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- {{-- ===== CTA ===== --}}
    <section class="section">
        <div class="container">
            <div class="cta-box">
                <span class="pill-badge"><span class="spark">✦</span> Start Learning Today</span>
                <h2>Your Skills Are<br><span class="grad-text">Waiting to Grow</span></h2>
                <p>Join 500,000+ learners who are transforming their skills with AI-powered micro-learning. Free to start, no credit card required.</p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn-grad lg">{!! $bolt !!} Start Micro-Learning Free</a>
                    <a href="#" class="btn-ghost lg">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        Watch Demo
                    </a>
                </div>
            </div>
        </div>
    </section> -->

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
                    <ul><li><a href="#features">Features</a></li><li><a href="#pricing">Pricing</a></li><li><a href="#">Changelog</a></li><li><a href="#">Roadmap</a></li></ul>
                </div>
                <div class="col-6 col-lg-2 footer-col">
                    <h4>Learn</h4>
                    <ul><li><a href="#">Documentation</a></li><li><a href="#">Tutorials</a></li><li><a href="#">Blog</a></li><li><a href="#">Community</a></li></ul>
                </div>
                <div class="col-6 col-lg-3 footer-col">
                    <h4>Company</h4>
                    <ul><li><a href="#">About</a></li><li><a href="#accessibility">Accessibility</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul>
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
</body>
</html>
