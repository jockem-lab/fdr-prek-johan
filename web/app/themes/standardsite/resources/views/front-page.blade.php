@extends('layouts.app')

@section('content')

{{-- SPLASH SCREEN --}}
<div id="em-splash">
  <div id="em-splash-logo">
    <img id="em-splash-text" src="/app/uploads/ett-logo.svg" alt="ETT">
    <img id="em-splash-tagline" src="/app/uploads/makleri-logo.svg" alt="MÄKLERI">
  </div>
</div>

{{-- HERO med bildkarusell --}}
<section class="em-hero" id="em-hero">
  <div class="em-hero-slide active" style="background-image:url('/app/uploads/hero/start-hero.jpg')"></div>
  <div class="em-hero-slide" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="em-hero-overlay"></div>
</section>

{{-- SEKTIONER --}}
<div class="em-sektioner" id="em-sektioner">

  {{-- Lägenheter --}}
  <section class="em-sektion" data-index="0">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder2.jpg" alt="Lägenheter">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">TILL SALU</p>
      <h2 class="em-sektion-rubrik">LÄGENHETER</h2>
      <p class="em-sektion-beskrivning">Ett kurerat urval av lägenheter i Östermalm, Södermalm, Vasastan och på Kungsholmen. Tidlösa hem med tydlig karaktär. Utöver publicerade objekt förmedlar vi även bostäder underhand, med samma omsorg och diskretion.</p>
      <a href="#" class="em-sektion-btn em-listings-toggle" data-target="lagenheter-grid" aria-expanded="false">UTFORSKA VÅRA LÄGENHETER</a>
    </div>
  </section>

  {{-- Lägenheter expanderbar grid --}}
  <x-listings-grid id="lagenheter-grid" :listings="$listings ?? []" />

  {{-- Karusell 1 --}}
  <div class="em-karusell">
    <div class="em-karusell-track">
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
    </div>
  </div>

  {{-- Hus --}}
  <section class="em-sektion em-sektion--reverse" data-index="1">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder3.jpg" alt="Hus">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">TILL SALU</p>
      <h2 class="em-sektion-rubrik">HUS</h2>
      <p class="em-sektion-beskrivning">Ett kurerat urval av hus i Stockholm och skärgården. Permanenta boenden och landställen. Villor, radhus och fritidshus. Arkitektur, läge och helhet i fokus.</p>
      <a href="{{ home_url('/objekt') }}" class="em-sektion-btn">UTFORSKA VÅRA HUS</a>
    </div>
  </section>

  {{-- Karusell 2 --}}
  <div class="em-karusell">
    <div class="em-karusell-track">
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
    </div>
  </div>

  {{-- Underhand --}}
  <section class="em-sektion" data-index="2">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder2.jpg" alt="Underhand" style="filter:brightness(0.95);">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">INTRESSEANMÄLAN</p>
      <h2 class="em-sektion-rubrik">UNDERHAND</h2>
      <p class="em-sektion-beskrivning">En del av de bostäder vi förmedlar når aldrig den öppna marknaden, detta i enlighet med våra uppdragsgivares önskemål. ETT MÄKLERI disponerar över ett omfattande kontaktnät och en köpstark databas som ständigt hålls uppdaterad.</p>
      <a href="{{ home_url('/underhand') }}" class="em-sektion-btn">MAILA OSS</a>
    </div>
  </section>

  {{-- Karusell 3 --}}
  <div class="em-karusell">
    <div class="em-karusell-track">
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder1.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder2.jpg" alt=""></div>
    </div>
  </div>

  {{-- Anlita oss --}}
  <section class="em-sektion em-sektion--reverse" data-index="3">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder3.jpg" alt="Anlita oss">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">VÄRDERING AV BOSTAD</p>
      <h2 class="em-sektion-rubrik">ANLITA OSS</h2>
      <p class="em-sektion-beskrivning">Överväger ni att sälja och önskar en värdering av ert hem? Vi ser fram emot att träffa er för ett helt förutsättningslöst möte. Vänligen fyll i formuläret nedan, så återkommer vi snarast möjligt för att diskutera era specifika behov.</p>
      <a href="#" class="em-sektion-btn em-anlita-toggle" data-target="anlita-form" aria-expanded="false">FYLL I FORMULÄRET</a>
    </div>
  </section>

  {{-- Anlita oss formulär (expanderbart) --}}
  <x-forms.anlita-form id="anlita-form" />

  {{-- Karusell 4 --}}
  <div class="em-karusell">
    <div class="em-karusell-track">
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/start-hero.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/hero2.jpg" alt=""></div>
      <div class="em-karusell-bild"><img src="/app/uploads/hero/placeholder3.jpg" alt=""></div>
    </div>
  </div>

</div>

{{-- KARTA --}}
<section class="em-karta-sektion" id="em-karta-anchor">
  <div class="em-karta-wrap">
    <div id="em-karta" class="em-karta"></div>
    <div class="em-karta-info">
      <p class="em-karta-adress">
        GREV TUREGATAN 50<br>
        114 38 STOCKHOLM
      </p>
      <a href="https://www.google.com/maps/dir/?api=1&destination=Grev+Turegatan+50,+11438+Stockholm" target="_blank" class="em-karta-link">HITTA HIT</a>
    </div>
  </div>
</section>

<script>
(function() {
  var splash = document.getElementById('em-splash');
  var hero = document.getElementById('em-hero');
  var header = document.querySelector('.fdr-header');
  var splashLogo = document.getElementById('em-splash-logo');
  var splashTagline = document.getElementById('em-splash-tagline');

  if (header) header.style.opacity = '0';
  var splashText = document.getElementById('em-splash-text');

  // Steg 1 (0.3s): ETT MÄKLERI tonar in långsamt centrerat (1.4s fade)
  setTimeout(function() {
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        splashText.classList.add('em-splash-text--visible');
      });
    });
  }, 300);

  // Steg 2 (1.8s): FASTIGHETSMÄKLERI expanderar in och knuffar ETT MÄKLERI åt vänster
  setTimeout(function() {
    splashTagline.classList.add('em-splash-tagline--visible');
  }, 1800);

  // Steg 3 (4.6s): Båda texter fadas ut tillsammans
  setTimeout(function() {
    splashLogo.classList.add('em-splash-logo--fade-out');
  }, 4600);

  // Steg 4 (4.8s): Svart bakgrund tonas ut, hero/video visas, header fade in
  setTimeout(function() {
    splash.classList.add('em-splash--exit');
    hero.classList.add('em-hero--visible');
    if (header) {
      header.style.transition = 'opacity 0.6s ease';
      header.style.opacity = '1';
    }
  }, 4800);

  // Cleanup (6.0s): Ta bort splash helt
  setTimeout(function() {
    splash.style.display = 'none';
  }, 6000);

  setTimeout(function() {
    var slides = document.querySelectorAll('.em-hero-slide');
    if (slides.length < 2) return;
    var current = 0;
    setInterval(function() {
      slides[current].classList.remove('active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('active');
    }, 5000);
  }, 3000);

  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('em-sektion--visible');
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.em-sektion').forEach(function(s) {
    observer.observe(s);
  });

  // Pilar inuti objekt-kort: byt bild utan att trigga länken
  document.querySelectorAll('.em-listing-pil').forEach(function(pil) {
    pil.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var kort = pil.closest('.em-listing-kort');
      if (!kort) return;
      var imgs = kort.querySelectorAll('.em-listing-img');
      if (imgs.length < 2) return;
      var current = parseInt(kort.getAttribute('data-image-index') || '0', 10);
      var direction = pil.classList.contains('em-listing-pil--next') ? 1 : -1;
      var next = (current + direction + imgs.length) % imgs.length;
      imgs[current].classList.remove('em-listing-img--active');
      imgs[next].classList.add('em-listing-img--active');
      kort.setAttribute('data-image-index', next);
    });
  });

  // Listings-grid toggle: expandera/dölj objekt-grid
  document.querySelectorAll('.em-listings-toggle').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var targetId = btn.getAttribute('data-target');
      var grid = document.getElementById(targetId);
      if (!grid) return;
      var isOpen = grid.classList.contains('em-listings-grid--open');
      if (isOpen) {
        grid.classList.remove('em-listings-grid--open');
        grid.setAttribute('aria-hidden', 'true');
        btn.setAttribute('aria-expanded', 'false');
      } else {
        grid.classList.add('em-listings-grid--open');
        grid.setAttribute('aria-hidden', 'false');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // Anlita oss toggle: expandera/dölj formuläret
  document.querySelectorAll('.em-anlita-toggle').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var targetId = btn.getAttribute('data-target');
      var form = document.getElementById(targetId);
      if (!form) return;
      var isOpen = form.classList.contains('em-anlita-form--open');
      if (isOpen) {
        form.classList.remove('em-anlita-form--open');
        form.setAttribute('aria-hidden', 'true');
        btn.setAttribute('aria-expanded', 'false');
      } else {
        form.classList.add('em-anlita-form--open');
        form.setAttribute('aria-hidden', 'false');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

})();
</script>

<script>
window.addEventListener('load', function() {
  var el = document.getElementById('em-karta');
  if (!el || typeof L === 'undefined') return;
  if (el._leaflet_id) return;
  var map = L.map('em-karta', {
    center: [59.3413, 18.0820],
    zoom: 15,
    zoomControl: true,
    scrollWheelZoom: false
  });
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CartoDB',
    subdomains: 'abcd',
    maxZoom: 19
  }).addTo(map);
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd',
    maxZoom: 19
  }).addTo(map);
  var icon = L.divIcon({
    className: 'em-karta-pin',
    html: '<svg width="24" height="32" viewBox="0 0 24 32" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 20 12 20s12-11 12-20c0-6.6-5.4-12-12-12z" fill="#000"/></svg>',
    iconSize: [24, 32],
    iconAnchor: [12, 32]
  });
  L.marker([59.3413, 18.0820], { icon: icon }).addTo(map);
  setTimeout(function() { map.invalidateSize(); }, 200);
});
</script>

@endsection
