<header id="site-header" class="fdr-header" role="banner">
  <div class="fdr-header-inner">
    <a href="{{ home_url('/') }}" class="fdr-logo" id="fdr-logo">
      <img src="/app/uploads/ett-makleri-logo.svg" alt="ETT MÄKLERI" class="fdr-logo-svg">
    </a>
    <button class="fdr-meny-btn" id="fdr-meny-open" aria-label="Öppna meny">MENY</button>
  </div>
</header>

<div class="fdr-menu-overlay" id="fdr-menu-overlay">
  <div class="fdr-menu-bg" id="fdr-menu-bg"></div>
  <div class="fdr-menu-panel">
    <button class="fdr-menu-close" id="fdr-meny-close" aria-label="Stäng meny">&#x2715;</button>
    <nav class="fdr-menu-nav">
      <a href="{{ home_url('/objekt') }}">Lägenheter</a>
      <a href="{{ home_url('/objekt') }}">Hus</a>
      <a href="{{ home_url('/underhand') }}">Underhand</a>
      <a href="{{ home_url('/om-oss') }}">Om oss</a>
    </nav>
    <div class="fdr-menu-footer-links">
      <a href="{{ home_url('/') }}#em-karta-anchor">Besök oss</a>
      <a href="{{ home_url('/om-oss') }}">Medarbetare</a>
      <a href="https://instagram.com" target="_blank">Instagram</a>
      <a href="https://facebook.com" target="_blank">Facebook</a>
    </div>
  </div>
</div>

<script>
(function() {
  var overlay  = document.getElementById('fdr-menu-overlay');
  var bg       = document.getElementById('fdr-menu-bg');
  var openBtn  = document.getElementById('fdr-meny-open');
  var closeBtn = document.getElementById('fdr-meny-close');
  var header   = document.getElementById('site-header');
  var logo     = document.getElementById('fdr-logo');
  var menyBtn  = document.getElementById('fdr-meny-open');

  /* ── Meny open/close ── */
  function openMenu() {
    bg.style.backgroundImage = 'url(' + window.location.href + ')';
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    logo.style.opacity  = '0';
    menyBtn.style.opacity = '0';
    menyBtn.style.pointerEvents = 'none';
  }
  function closeMenu() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    logo.style.opacity  = '1';
    menyBtn.style.opacity = '1';
    menyBtn.style.pointerEvents = '';
  }
  if (openBtn)  openBtn.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay || e.target === bg) closeMenu();
  });

  /* ── Scroll: byt färg baserat på bakgrund ── */

})();

  /* ── Hero-läge: ta bort mix-blend för vit text ── */
  if (document.body.classList.contains('home')) {
    var hdr = document.getElementById('site-header');
    function updateHeroMode() {
      var hero = document.getElementById('em-hero');
      if (!hero) return;
      var heroBottom = hero.getBoundingClientRect().bottom;
      if (heroBottom > 0) {
        hdr.classList.add('fdr-header--over-hero');
      } else {
        hdr.classList.remove('fdr-header--over-hero');
      }
    }
    window.addEventListener('scroll', updateHeroMode);
    setTimeout(updateHeroMode, 100);
  }
</script>
