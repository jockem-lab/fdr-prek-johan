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
    // Område: district (Vasastan/Östermalm) först, fallback till city eller area
    $district = ($loc && !empty($loc->district)) ? $loc->district : '';
    $city = ($loc && !empty($loc->city)) ? $loc->city : '';
    $area = $district ?: (($loc && !empty($loc->area)) ? $loc->area : $city);

    $eco = em_unserialize(get_post_meta($pid, '_fasad_economy', true));
    $price = '';
    if ($eco && !empty($eco->price->primary->amount))
        $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';
    $fee = '';
    if ($eco && !empty($eco->fee->amount))
        $fee = number_format($eco->fee->amount, 0, ',', ' ') . ' kr/mån';

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
        'fee'      => $fee,
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
  {{-- Filter-rad (Figma-spec) --}}
  <div class="em-objekt-filter">
    {{-- Vänster: typ-filter --}}
    <div class="em-filter-grupp em-filter-grupp--typ">
      <button class="em-filter-knapp active" data-filter-typ="alla">ALLA</button>
      <button class="em-filter-knapp" data-filter-typ="lagenhet">LÄGENHETER</button>
      <button class="em-filter-knapp" data-filter-typ="hus">HUS</button>
    </div>
    {{-- Höger: sortering + sålda --}}
    <div class="em-filter-grupp em-filter-grupp--sort">
      <button class="em-filter-knapp active" data-sort="senaste">SENASTE</button>
      <button class="em-filter-knapp" data-sort="yta">YTA</button>
      <button class="em-filter-knapp" data-sort="pris">PRIS</button>
      <button class="em-filter-knapp" data-filter-sald="1">SÅLDA</button>
    </div>
  </div>

  {{-- Objekt-grid (samma component som på startsidan) --}}
  <x-listings-grid id="objekt-grid-page" :listings="$listings" />
</section>

<script>
(function() {
  var grid = document.getElementById('objekt-grid-page');
  if (!grid) return;

  var listings = @json($listings);
  var kort = grid.querySelectorAll('.em-listing-kort');

  // Lägg till data-attribut för filtrering + sortering
  kort.forEach(function(k, i) {
    var l = listings[i];
    if (!l) return;
    k.setAttribute('data-typ', l.category || 'annat');
    k.setAttribute('data-sold', l.is_sold ? '1' : '0');
    k.setAttribute('data-pris', l.price_raw || 0);
    k.setAttribute('data-yta', l.area_raw || 0);
    k.setAttribute('data-datum', l.published_raw || 0);
  });

  var state = {
    typ: 'alla',
    sort: 'senaste',
    sortDir: 'asc',  // stigande default
    visaSalda: false
  };

  function applyFilter() {
    kort.forEach(function(k) {
      var typ = k.getAttribute('data-typ');
      var sold = k.getAttribute('data-sold') === '1';
      var visa = true;

      // Sålda-filter har högsta prio
      if (state.visaSalda) {
        if (!sold) visa = false;
      } else {
        if (sold) visa = false;
        if (state.typ !== 'alla' && typ !== state.typ) visa = false;
      }

      k.style.display = visa ? '' : 'none';
    });
  }

  function applySort() {
    var parent = kort[0] && kort[0].parentNode;
    if (!parent) return;

    var sortKey = state.sort === 'senaste' ? 'data-datum' :
                  state.sort === 'pris' ? 'data-pris' :
                  state.sort === 'yta' ? 'data-yta' : null;
    if (!sortKey) return;

    var arr = Array.from(kort);
    arr.sort(function(a, b) {
      var av = parseFloat(a.getAttribute(sortKey)) || 0;
      var bv = parseFloat(b.getAttribute(sortKey)) || 0;
      // Senaste: nyast först (descending)
      if (state.sort === 'senaste') return bv - av;
      // Yta/Pris: respektera sortDir
      return state.sortDir === 'asc' ? av - bv : bv - av;
    });
    arr.forEach(function(k) { parent.appendChild(k); });
  }

  // Typ-knappar (vänster grupp)
  var typBtns = document.querySelectorAll('[data-filter-typ]');
  typBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      typBtns.forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      state.typ = btn.getAttribute('data-filter-typ');
      // Avaktivera Sålda om typ-filter används
      if (state.visaSalda) {
        state.visaSalda = false;
        var saldBtn = document.querySelector('[data-filter-sald]');
        if (saldBtn) saldBtn.classList.remove('active');
      }
      applyFilter();
    });
  });

  // Sortering-knappar (höger grupp)
  var sortBtns = document.querySelectorAll('[data-sort]');
  sortBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var sortVal = btn.getAttribute('data-sort');
      // Toggle riktning om samma knapp klickas igen
      if (state.sort === sortVal) {
        state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        state.sort = sortVal;
        state.sortDir = 'asc';
      }
      sortBtns.forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      // Avaktivera Sålda
      if (state.visaSalda) {
        state.visaSalda = false;
        var saldBtn = document.querySelector('[data-filter-sald]');
        if (saldBtn) saldBtn.classList.remove('active');
      }
      applySort();
      applyFilter();
    });
  });

  // Sålda-knapp
  var saldBtn = document.querySelector('[data-filter-sald]');
  if (saldBtn) {
    saldBtn.addEventListener('click', function() {
      state.visaSalda = !state.visaSalda;
      if (state.visaSalda) {
        saldBtn.classList.add('active');
        // Avaktivera typ + sortering visuellt
        typBtns.forEach(function(b) { b.classList.remove('active'); });
        sortBtns.forEach(function(b) { b.classList.remove('active'); });
      } else {
        saldBtn.classList.remove('active');
        // Återställ default-aktiva
        var allaBtn = document.querySelector('[data-filter-typ="alla"]');
        var senasteBtn = document.querySelector('[data-sort="senaste"]');
        if (allaBtn) allaBtn.classList.add('active');
        if (senasteBtn) senasteBtn.classList.add('active');
        state.typ = 'alla';
        state.sort = 'senaste';
      }
      applyFilter();
      applySort();
    });
  }

  // Initial sortering
  applySort();
  applyFilter();
})();
</script>

@endsection
