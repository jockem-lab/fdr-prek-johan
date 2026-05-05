@extends('layouts.app')

@section('content')
@php
function em_unserialize($raw) {
    if (!is_string($raw) || empty($raw)) return null;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}

function em_extract_listing($pid) {
    $loc = em_unserialize(get_post_meta($pid, '_fasad_location', true));
    $address = ($loc && !empty($loc->address)) ? $loc->address : get_the_title($pid);
    $area = ($loc && !empty($loc->area)) ? $loc->area : (($loc && !empty($loc->city)) ? $loc->city : '');

    $eco = em_unserialize(get_post_meta($pid, '_fasad_economy', true));
    $price = '';
    if ($eco && !empty($eco->price->primary->amount))
        $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';

    $size = em_unserialize(get_post_meta($pid, '_fasad_size', true));
    $rooms = ($size && !empty($size->rooms)) ? $size->rooms . ' ' . ($size->roomsInformation ?? 'rum') : '';
    $area_size = '';
    if (!empty($size->area->areas) && is_array($size->area->areas)) {
        foreach ($size->area->areas as $a) {
            if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size)) {
                $area_size = $a->size . ' ' . strtolower($a->unit ?? 'kvm');
                break;
            }
        }
    }

    $type_obj = em_unserialize(get_post_meta($pid, '_fasad_descriptionType', true));
    $type = $type_obj->alias ?? '';

    $imgs = em_unserialize(get_post_meta($pid, '_fasad_images', true));
    $image_list = [];
    if (is_array($imgs)) {
        foreach ($imgs as $img) {
            if (!empty($img->variants)) {
                foreach ($img->variants as $v) {
                    if (($v->type ?? '') === 'large') {
                        $image_list[] = $v->path;
                        break;
                    }
                }
            }
        }
    }

    // Status-logik
    $status_alias = '';
    $is_sold = get_post_meta($pid, '_fasad_sold', true) == '1';

    if ($is_sold) {
        $status_alias = 'SÅLD';
    } else {
        $showings = em_unserialize(get_post_meta($pid, '_fasad_showings', true));
        if (is_array($showings) && !empty($showings)) {
            $now = time();
            $upcoming = null;
            foreach ($showings as $show) {
                if (!empty($show->startDate)) {
                    $ts = strtotime($show->startDate);
                    if ($ts && $ts > $now && ($upcoming === null || $ts < $upcoming)) {
                        $upcoming = $ts;
                    }
                }
            }
            if ($upcoming) {
                $status_alias = 'VISNING ' . date('j/n', $upcoming);
            }
        }

        if (!$status_alias) {
            $preview = em_unserialize(get_post_meta($pid, '_fasad_preview', true));
            if (is_object($preview) && !empty($preview->activated)) {
                $status_alias = 'FÖRHANDSVISNING';
            }
        }

        if (!$status_alias) {
            $bids = em_unserialize(get_post_meta($pid, '_fasad_bids', true));
            if (is_array($bids) && !empty($bids)) {
                $status_alias = 'BUDGIVNING PÅGÅR';
            }
        }

        if (!$status_alias) {
            $status_alias = 'TILL SALU';
        }
    }

    // Kategorisera typ för filtrering
    $type_lc = mb_strtolower($type);
    if (strpos($type_lc, 'lägenhet') !== false || strpos($type_lc, 'bostadsrätt') !== false) {
        $category = 'lagenhet';
    } elseif (strpos($type_lc, 'villa') !== false || strpos($type_lc, 'hus') !== false || strpos($type_lc, 'fritids') !== false) {
        $category = 'hus';
    } else {
        $category = 'annat';
    }

    return (object)[
        'id'       => $pid,
        'slug'     => get_post_field('post_name', $pid),
        'address'  => $address,
        'area'     => $area_size,
        'omrade'   => $area,
        'price'    => $price,
        'type'     => $type,
        'rooms'    => $rooms,
        'image'    => $image_list[0] ?? '',
        'images'   => $image_list,
        'status'   => $status_alias,
        'is_sold'  => $is_sold,
        'category' => $category,
    ];
}

// Hämta alla aktiva
$active_query = new WP_Query([
    'post_type'      => 'fasad_listing',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => '_fasad_firstPublished',
    'order'          => 'DESC',
    'meta_query'     => [
        'relation' => 'OR',
        ['key' => '_fasad_sold', 'compare' => 'NOT EXISTS'],
        ['key' => '_fasad_sold', 'value' => '1', 'compare' => '!='],
    ],
]);

// Hämta alla sålda separat
$sold_query = new WP_Query([
    'post_type'      => 'fasad_listing',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => '_fasad_firstPublished',
    'order'          => 'DESC',
    'meta_query'     => [
        ['key' => '_fasad_sold', 'value' => '1', 'compare' => '='],
    ],
]);

$listings = [];
foreach ($active_query->posts as $post) {
    $listings[] = em_extract_listing($post->ID);
}
foreach ($sold_query->posts as $post) {
    $listings[] = em_extract_listing($post->ID);
}
wp_reset_postdata();

// Samla unika områden för dropdown
$omraden = [];
foreach ($listings as $l) {
    if ($l->omrade && !in_array($l->omrade, $omraden)) {
        $omraden[] = $l->omrade;
    }
}
sort($omraden);

// Samla unika status-typer (utöver sålda)
$statuses = [];
foreach ($listings as $l) {
    if ($l->is_sold) continue;
    // Normalisera VISNING DD/M → VISNING
    $s = preg_match('/^VISNING\s/u', $l->status) ? 'VISNING' : $l->status;
    if (!in_array($s, $statuses)) {
        $statuses[] = $s;
    }
}
@endphp

<section class="em-objekt-page">
  {{-- Filter-rad (Wrede-stil) --}}
  <div class="em-objekt-filter">
    <button class="em-filter-link active" data-filter-typ="alla">Alla</button>
    <button class="em-filter-link" data-filter-typ="lagenhet">Lägenheter</button>
    <button class="em-filter-link" data-filter-typ="hus">Hus</button>
    <button class="em-filter-link" data-filter-typ="sald">Sålda</button>

    <div class="em-filter-dropdown" data-dropdown="omrade">
      <button class="em-filter-link em-filter-dropdown-trigger" type="button">
        <span class="em-filter-label">Område</span>
        <svg class="em-filter-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.2"/></svg>
      </button>
      <div class="em-filter-panel">
        <button class="em-filter-option active" data-value="alla">Alla områden</button>
        @foreach($omraden as $o)
          <button class="em-filter-option" data-value="{{ $o }}">{{ $o }}</button>
        @endforeach
      </div>
    </div>

    <div class="em-filter-dropdown" data-dropdown="status">
      <button class="em-filter-link em-filter-dropdown-trigger" type="button">
        <span class="em-filter-label">Status</span>
        <svg class="em-filter-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.2"/></svg>
      </button>
      <div class="em-filter-panel">
        <button class="em-filter-option active" data-value="alla">Alla statusar</button>
        @foreach($statuses as $st)
          <button class="em-filter-option" data-value="{{ $st }}">{{ $st }}</button>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Objekt-grid (samma component som på startsidan) --}}
  <x-listings-grid id="objekt-grid-page" :listings="$listings" />
</section>

<script>
(function() {
  var grid = document.getElementById('objekt-grid-page');
  if (!grid) return;

  // Lägg till data-attribut på korten för filtrering
  var listings = @json($listings);
  var kort = grid.querySelectorAll('.em-listing-kort');
  kort.forEach(function(k, i) {
    var l = listings[i];
    if (!l) return;
    k.setAttribute('data-typ', l.category || 'annat');
    k.setAttribute('data-omrade', l.omrade || '');
    k.setAttribute('data-status', l.status || '');
    k.setAttribute('data-sold', l.is_sold ? '1' : '0');
  });

  // Tvinga grid att vara öppen direkt på undersidan
  grid.classList.add('em-listings-grid--open');
  grid.setAttribute('aria-hidden', 'false');

  // Filter-state
  var state = {
    typ: 'alla',      // alla | lagenhet | hus | sald
    omrade: 'alla',
    status: 'alla',
  };

  function applyFilter() {
    kort.forEach(function(k) {
      var typ = k.getAttribute('data-typ');
      var omrade = k.getAttribute('data-omrade');
      var status = k.getAttribute('data-status');
      var sold = k.getAttribute('data-sold') === '1';

      var visa = true;

      // Typ-filter (sald hanteras här)
      if (state.typ === 'sald') {
        if (!sold) visa = false;
      } else if (state.typ === 'alla') {
        if (sold) visa = false;
      } else {
        if (sold) visa = false;
        if (typ !== state.typ) visa = false;
      }

      if (visa && state.omrade !== 'alla' && omrade !== state.omrade) visa = false;
      if (visa && state.status !== 'alla') {
        var statusNorm = status.indexOf('VISNING ') === 0 ? 'VISNING' : status;
        if (statusNorm !== state.status) visa = false;
      }

      k.style.display = visa ? '' : 'none';
    });
  }

  // Typ-knappar
  var typBtns = document.querySelectorAll('[data-filter-typ]');
  typBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      typBtns.forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      state.typ = btn.getAttribute('data-filter-typ');
      applyFilter();
    });
  });

  // Dropdown-trigger: öppna/stäng
  document.querySelectorAll('.em-filter-dropdown-trigger').forEach(function(trig) {
    trig.addEventListener('click', function(e) {
      e.stopPropagation();
      var dd = trig.closest('.em-filter-dropdown');
      var open = dd.classList.contains('open');
      // Stäng alla andra
      document.querySelectorAll('.em-filter-dropdown').forEach(function(d) { d.classList.remove('open'); });
      if (!open) dd.classList.add('open');
    });
  });

  // Stäng dropdowns vid klick utanför
  document.addEventListener('click', function() {
    document.querySelectorAll('.em-filter-dropdown').forEach(function(d) { d.classList.remove('open'); });
  });

  // Filter-options inom dropdown
  document.querySelectorAll('.em-filter-dropdown').forEach(function(dd) {
    var key = dd.getAttribute('data-dropdown'); // omrade | status
    var trigLabel = dd.querySelector('.em-filter-label');
    var defaultLabel = trigLabel.textContent;

    dd.querySelectorAll('.em-filter-option').forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        var val = opt.getAttribute('data-value');
        // Markera aktiv
        dd.querySelectorAll('.em-filter-option').forEach(function(o) { o.classList.remove('active'); });
        opt.classList.add('active');
        // Uppdatera label
        if (val === 'alla') {
          trigLabel.textContent = defaultLabel;
          dd.classList.remove('has-value');
        } else {
          trigLabel.textContent = opt.textContent;
          dd.classList.add('has-value');
        }
        // Stäng panelen
        dd.classList.remove('open');
        // Uppdatera state
        state[key] = val;
        applyFilter();
      });
    });
  });

  applyFilter();

  // Pilar för bilder (samma logik som startsidan)
  document.querySelectorAll('.em-listing-pil').forEach(function(pil) {
    pil.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var k = pil.closest('.em-listing-kort');
      if (!k) return;
      var imgs = k.querySelectorAll('.em-listing-img');
      if (imgs.length < 2) return;
      var current = parseInt(k.getAttribute('data-image-index') || '0', 10);
      var direction = pil.classList.contains('em-listing-pil--next') ? 1 : -1;
      var next = (current + direction + imgs.length) % imgs.length;
      imgs[current].classList.remove('em-listing-img--active');
      imgs[next].classList.add('em-listing-img--active');
      k.setAttribute('data-image-index', next);
    });
  });
})();
</script>

@endsection
