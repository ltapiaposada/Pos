@props(['title', 'subtitle'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tienda') }} - {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,450;9..144,560;9..144,650&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --auth-ink:#15181D; --auth-muted:#585F6B; --auth-paper:#FFF; --auth-soft:#F6F5F1; --auth-line:#E4E1D9; --auth-forest:#1E4A3D; --auth-dark:#123329; }
        * { box-sizing:border-box; } html, body { min-height:100%; margin:0; }
        body { min-height:100vh; padding:24px; background:var(--auth-soft); color:var(--auth-ink); display:grid; place-items:center; font-family:"Inter",system-ui,sans-serif; }
        body[data-auth-theme="dark"] { --auth-ink:#F6F5F1; --auth-muted:#B8C0C8; --auth-paper:#20262D; --auth-soft:#15181D; --auth-line:#3C444D; --auth-forest:#2C6655; --auth-dark:#1E4A3D; }
        button, input { font:inherit; }
        .auth-shell { width:min(920px,100%); min-height:600px; overflow:hidden; border-radius:20px; background:var(--auth-paper); box-shadow:0 12px 32px rgba(21,24,29,.06),0 1px 2px rgba(21,24,29,.04); display:grid; grid-template-columns:.85fr 1.15fr; }
        .auth-theme-toggle { position:fixed; top:20px; right:20px; width:38px; height:38px; border:1px solid var(--auth-line); border-radius:50%; background:var(--auth-paper); color:var(--auth-ink); cursor:pointer; display:grid; place-items:center; }.auth-theme-toggle:hover, .auth-theme-toggle:focus { border-color:var(--auth-forest); background:var(--auth-soft); }.auth-theme-toggle .is-hidden { display:none; }
        .auth-panel { position:relative; overflow:hidden; padding:44px 40px; background:var(--auth-forest); color:#fff; display:flex; flex-direction:column; justify-content:space-between; }
        .auth-panel__mark { position:absolute; right:-60px; bottom:-60px; width:280px; height:280px; opacity:.12; }
        .auth-brand, .auth-panel__body, .auth-panel__footer { position:relative; z-index:1; }
        .auth-brand { display:flex; align-items:center; gap:11px; }.auth-brand__copy { display:flex; flex-direction:column; }.auth-brand__eyebrow { color:#B9D0C4; font-size:10.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }.auth-brand__name { margin-top:1px; font-family:"Fraunces",Georgia,serif; font-size:18px; font-weight:560; }
        .auth-panel__body { margin-top:40px; }.auth-panel h1 { max-width:12ch; margin:0; font-family:"Fraunces",Georgia,serif; font-size:32px; font-weight:560; line-height:1.18; }.auth-panel__body p { max-width:32ch; margin:14px 0 0; color:#D3E0DA; font-size:14.5px; line-height:1.6; }.auth-benefits { margin:34px 0 0; padding:0; list-style:none; display:grid; gap:14px; }.auth-benefits li { color:#D3E0DA; display:flex; align-items:center; gap:10px; font-size:13.5px; }.auth-benefits svg { color:#B9D0C4; flex:0 0 auto; }.auth-panel__footer { color:#9FBDB0; font-size:12.5px; }
        .auth-form-side { padding:52px 56px; display:flex; flex-direction:column; justify-content:center; }.auth-form-head h2 { margin:0; font-family:"Fraunces",Georgia,serif; font-size:26px; font-weight:560; }.auth-form-head p { margin:8px 0 0; color:var(--auth-muted); font-size:14px; }
        .auth-status, .auth-error { border-radius:8px; font-size:13px; line-height:1.45; padding:10px 12px; }.auth-status { margin-top:20px; background:#E5ECE8; color:var(--auth-dark); }.auth-error { margin-top:8px; background:#F1E1DA; color:#74351F; }
        .auth-field { margin-top:24px; }.auth-field label { display:block; margin-bottom:7px; font-size:13px; font-weight:700; }.auth-input-wrap { position:relative; }.auth-field input[type="email"], .auth-field input[type="password"], .auth-field input[type="text"] { width:100%; outline:0; border:1px solid var(--auth-line); border-radius:9px; background:#fff; color:var(--auth-ink); padding:12px 14px; font-size:14.5px; }.auth-field input:focus { border-color:var(--auth-forest); box-shadow:0 0 0 3px rgba(30,74,61,.12); }.auth-field input::placeholder { color:#9A9C9F; }.auth-password { padding-right:82px !important; }.auth-toggle { position:absolute; top:50%; right:12px; transform:translateY(-50%); border:0; background:transparent; color:var(--auth-muted); cursor:pointer; font-size:12.5px; font-weight:700; }
        body[data-auth-theme="dark"] .auth-field input[type="email"], body[data-auth-theme="dark"] .auth-field input[type="password"], body[data-auth-theme="dark"] .auth-field input[type="text"] { background:#282F37; color:var(--auth-ink); } body[data-auth-theme="dark"] .auth-status { background:#1E3F35; color:#D3E0DA; } body[data-auth-theme="dark"] .auth-error { background:#4D2C26; color:#F3D4C7; }
        .auth-options { margin-top:22px; display:flex; align-items:center; justify-content:space-between; gap:16px; }.auth-remember { color:var(--auth-muted); display:inline-flex; align-items:center; gap:9px; font-size:13.5px; }.auth-remember input { width:16px; height:16px; accent-color:var(--auth-forest); }.auth-link { color:var(--auth-dark); font-size:13.5px; font-weight:700; text-decoration:none; }.auth-link:hover, .auth-link:focus { text-decoration:underline; }.auth-submit { width:100%; margin-top:26px; border:0; border-radius:9px; background:var(--auth-forest); color:#fff; cursor:pointer; padding:13px; font-size:15px; font-weight:700; }.auth-submit:hover, .auth-submit:focus { background:var(--auth-dark); }.auth-divider { margin:26px 0; color:var(--auth-muted); display:flex; align-items:center; gap:14px; font-size:12px; }.auth-divider::before, .auth-divider::after { height:1px; background:var(--auth-line); content:""; flex:1; }.auth-footer-link { color:var(--auth-muted); font-size:13.5px; text-align:center; }
        @media (max-width:760px) { body { padding:16px; }.auth-shell { min-height:0; grid-template-columns:1fr; }.auth-panel { display:none; }.auth-form-side { padding:40px 28px; } }
    </style>
</head>
<body>
    @php $business = \App\Models\Setting::getValue('business', []); $businessName = $business['name'] ?? config('app.name', 'Tienda'); @endphp
    <button type="button" class="auth-theme-toggle" id="auth-theme-toggle" aria-label="Activar modo oscuro" title="Cambiar tema">
        <svg id="auth-theme-icon-sun" class="is-hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <svg id="auth-theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <main class="auth-shell">
        <aside class="auth-panel" aria-label="{{ $businessName }}">
            <svg class="auth-panel__mark" viewBox="0 0 32 32" aria-hidden="true"><rect x="4" y="4" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/><rect x="16" y="4" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/><rect x="4" y="16" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/><rect x="16" y="16" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/></svg>
            <div class="auth-brand"><svg width="30" height="30" viewBox="0 0 32 32" aria-hidden="true"><rect x="4" y="4" width="12" height="12" rx="4" fill="#fff" opacity=".95"/><rect x="16" y="4" width="12" height="12" rx="4" fill="#fff" opacity=".7"/><rect x="4" y="16" width="12" height="12" rx="4" fill="#fff" opacity=".5"/><rect x="16" y="16" width="12" height="12" rx="4" fill="#fff" opacity=".3"/></svg><div class="auth-brand__copy"><span class="auth-brand__eyebrow">Tienda oficial</span><span class="auth-brand__name">{{ $businessName }}</span></div></div>
            <div class="auth-panel__body"><h1>Tu tienda, siempre a la mano</h1><p>Accede para consultar tus pedidos y continuar comprando de forma sencilla.</p><ul class="auth-benefits"><li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2"/></svg>Pago rapido y seguro</li><li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2"/></svg>Confirmacion inmediata</li><li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2"/></svg>Acceso protegido en todo momento</li></ul></div>
            <div class="auth-panel__footer">{{ $businessName }} · Compra protegida</div>
        </aside>
        <section class="auth-form-side" aria-labelledby="auth-title"><div class="auth-form-head"><h2 id="auth-title">{{ $title }}</h2><p>{{ $subtitle }}</p></div>@if (session('status'))<div class="auth-status">{{ session('status') }}</div>@endif{{ $slot }}</section>
    </main>
    <script>
        document.querySelectorAll('[data-auth-toggle]').forEach(button => button.addEventListener('click', function () { const input = document.getElementById(this.dataset.authToggle); const hidden = input.type === 'password'; input.type = hidden ? 'text' : 'password'; this.textContent = hidden ? 'Ocultar' : 'Mostrar'; this.setAttribute('aria-label', hidden ? 'Ocultar contrasena' : 'Mostrar contrasena'); }));
        const themeButton = document.getElementById('auth-theme-toggle');
        const sunIcon = document.getElementById('auth-theme-icon-sun');
        const moonIcon = document.getElementById('auth-theme-icon-moon');
        function setAuthTheme(theme) { const dark = theme === 'dark'; document.body.setAttribute('data-auth-theme', dark ? 'dark' : 'light'); sunIcon.classList.toggle('is-hidden', !dark); moonIcon.classList.toggle('is-hidden', dark); themeButton.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro'); }
        setAuthTheme(localStorage.getItem('pos-theme') === 'posdark' ? 'dark' : 'light');
        themeButton.addEventListener('click', function () { const dark = document.body.getAttribute('data-auth-theme') === 'dark'; localStorage.setItem('pos-theme', dark ? 'pos' : 'posdark'); setAuthTheme(dark ? 'light' : 'dark'); });
    </script>
</body>
</html>
