<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - FOODCART360' : 'FOODCART360 - Smart Food Cart Management' }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<!-- Google Fonts: Plus Jakarta Sans for ultra-modern UI -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">

<script>
    (function() {
        const savedTheme = localStorage.getItem('fc_theme') || (document.cookie.match(/fc_theme=([^;]+)/) || [])[1] || 'modern-light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (savedTheme === 'dark-mode' || savedTheme === 'premium-black') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
