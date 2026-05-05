@php
$post_id = get_the_ID();

function fasad_unserialize($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}

// ─── Location ───
$loc      = fasad_unserialize(get_post_meta($post_id, '_fasad_location', true));
$address  = ($loc && !empty($loc->address)) ? $loc->address : get_the_title($post_id);
$city     = ($loc && !empty($loc->city) && is_string($loc->city)) ? $loc->city : '';
$zipCode  = ($loc && !empty($loc->zipCode) && is_string($loc->zipCode)) ? $loc->zipCode : '';
$area     = ($loc && !empty($loc->area) && is_string($loc->area)) ? $loc->area : '';
$district = ($loc && !empty($loc->district) && is_string($loc->district)) ? $loc->district : '';
$subarea  = $area ?: $district ?: $city;
$lat      = ($loc && !empty($loc->coordinate->latitude)) ? $loc->coordinate->latitude : null;
$lng      = ($loc && !empty($loc->coordinate->longitude)) ? $loc->coordinate->longitude : null;

// ─── Sales title (för h1 om det finns) ───
$salesTitle = get_post_meta($post_id, '_fasad_salesTitle', true) ?: $address;

// ─── Economy ───
$eco   = fasad_unserialize(get_post_meta($post_id, '_fasad_economy', true));
$price = '';
if ($eco && !empty($eco->price->primary->amount))
    $price = number_format($eco->price->primary->amount, 0, ',', '.') . ' kr/bud';
$fee = '';
if ($eco && !empty($eco->association->fee->amount))
    $fee = number_format($eco->association->fee->amount, 0, ',', '.') . ' kr/mån';

// Driftskostnader
$opcost = '';
if ($eco && !empty($eco->operatingCost->amount))
    $opcost = number_format($eco->operatingCost->amount, 0, ',', '.') . ' kr/mån';

// ─── Size ───
$sz = fasad_unserialize(get_post_meta($post_id, '_fasad_size', true));
$rooms_count = ($sz && !empty($sz->rooms) && is_scalar($sz->rooms)) ? $sz->rooms : '';
$living_area = '';
$biarea = '';
if (!empty($sz->area->areas) && is_array($sz->area->areas)) {
    foreach ($sz->area->areas as $a) {
        if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size))
            $living_area = $a->size . ' kvm';
        if (!empty($a->type) && $a->type === 'Biarea' && !empty($a->size))
            $biarea = $a->size . ' kvm';
    }
}
$area_str = $living_area . ($biarea ? ' + ' . $biarea : '');
$floor = '';
$elevator = '';
if (!empty($sz->floor)) $floor = $sz->floor;
if (!empty($sz->hasElevator)) $elevator = $sz->hasElevator ? 'Ja' : 'Nej';

$build = fasad_unserialize(get_post_meta($post_id, '_fasad_building', true));
$built_year = ($build && !empty($build->constructionYear)) ? $build->constructionYear : '';

// ─── Images via bildproxy ───
$imgs_raw = get_post_meta($post_id, '_fasad_images', true);
$imgs     = fasad_unserialize($imgs_raw);
$images   = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $v) {
                if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                    $images[] = rest_url('prek/v1/bildproxy?url=') . urlencode($v->path);
                    break;
                }
            }
        }
    }
}

// ─── Realtors ───
$realtors_raw  = get_post_meta($post_id, '_fasad_realtors', true);
$realtors_data = fasad_unserialize($realtors_raw);
$first_realtor = null;
if (is_array($realtors_data) && !empty($realtors_data)) {
    $first_realtor = $realtors_data[0];
} elseif (is_object($realtors_data)) {
    $first_realtor = $realtors_data;
}

$maklare_bild = '';
if ($first_realtor && !empty($first_realtor->image)) {
    if (is_string($first_realtor->image)) $maklare_bild = $first_realtor->image;
    elseif (!empty($first_realtor->image->path)) $maklare_bild = $first_realtor->image->path;
}

// Försök matcha lokal team-bild
if ($first_realtor && !empty($first_realtor->email)) {
    $email_prefix = strtolower(strstr($first_realtor->email, '@', true));
    $upload_dir = wp_upload_dir();
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $path = $upload_dir['basedir'] . '/team/' . $email_prefix . '.' . $ext;
        if (file_exists($path)) {
            $maklare_bild = $upload_dir['baseurl'] . '/team/' . $email_prefix . '.' . $ext;
            break;
        }
    }
}

$maklare_namn = $first_realtor ? trim(($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '')) : '';
$maklare_titel = $first_realtor->title ?? 'Fastighetsmäklare';
$maklare_typ = $first_realtor->type ?? 'Ansvarig Mäklare';
$maklare_email = $first_realtor->email ?? '';
$maklare_tel_display = '';
$maklare_tel_href = '';
if ($first_realtor && !empty($first_realtor->cellphoneString)) {
    $maklare_tel_display = $first_realtor->cellphoneString;
    $tel = $first_realtor->cellphone ?? '';
    if ($tel) $maklare_tel_href = '+' . ltrim($tel, '+');
}

// ─── Sales text ───
$sales_text = get_post_meta($post_id, '_fasad_salesText', true) ?: '';

// ─── Showings ───
$showings_raw = get_post_meta($post_id, '_fasad_showings', true);
$showings = fasad_unserialize($showings_raw);
if (!is_array($showings)) $showings = [];
$showings = array_values(array_filter($showings, function($s) {
    return !empty($s->startDate) && strtotime($s->startDate) > time() - 3600;
}));

// ─── Documents ───
$docs_obj  = fasad_unserialize(get_post_meta($post_id, '_fasad_documents', true));
$documents = [];
if ($docs_obj && !empty($docs_obj->listingDocuments)) {
    foreach ($docs_obj->listingDocuments as $doc) {
        $documents[] = (object)['alias' => $doc->alias ?? '', 'href' => $doc->href ?? ''];
    }
}

// ─── Association ───
$assoc = fasad_unserialize(get_post_meta($post_id, '_fasad_association', true));
$assoc_name = ($assoc && !empty($assoc->name)) ? $assoc->name : '';
$assoc_genuine = ($assoc && !empty($assoc->genuine)) ? 'Ja' : 'Nej';
$assoc_form = 'Bostadsrätt - Lägenhet';
if ($assoc && !empty($assoc->formOfOwnership->alias)) $assoc_form = $assoc->formOfOwnership->alias;

// ─── Energy ───
$energy = fasad_unserialize(get_post_meta($post_id, '_fasad_energy', true));
$energy_value = ($energy && !empty($energy->energyDeclaration->value)) ? $energy->energyDeclaration->value : '';
$energy_status = ($energy && !empty($energy->energyDeclaration->status)) ? $energy->energyDeclaration->status : '';
$energy_date = ($energy && !empty($energy->energyDeclaration->date)) ? $energy->energyDeclaration->date : '';

// ─── Descriptions (säljtext + renoveringar) ───
$desc_raw  = get_post_meta($post_id, '_fasad_descriptions', true);
$desc_data = fasad_unserialize($desc_raw);
$desc_text = '';
$renovations = '';
if (is_array($desc_data)) {
    foreach ($desc_data as $d) {
        $alias = strtolower($d->type->alias ?? '');
        $text = $d->text ?? ($d->content ?? '');
        if (!$desc_text && !empty($text)) $desc_text = $text;
        if (stripos($alias, 'renover') !== false || stripos($alias, 'genomfö') !== false) {
            $renovations = $text;
        }
    }
}
if (!$desc_text) $desc_text = $sales_text;

// ─── Liknande objekt ───
$similar = get_posts([
    'post_type'      => 'fasad_listing',
    'post_status'    => 'publish',
    'posts_per_page' => 2,
    'post__not_in'   => [$post_id],
    'orderby'        => 'rand',
    'meta_query'     => [
        ['key' => '_fasad_sold', 'value' => '1', 'compare' => '!='],
    ],
]);
$similar_listings = [];
foreach ($similar as $s) {
    $sloc = fasad_unserialize(get_post_meta($s->ID, '_fasad_location', true));
    $simgs = fasad_unserialize(get_post_meta($s->ID, '_fasad_images', true));
    $sim_first_img = '';
    if (is_array($simgs) && !empty($simgs[0]->variants)) {
        foreach ($simgs[0]->variants as $v) {
            if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                $sim_first_img = rest_url('prek/v1/bildproxy?url=') . urlencode($v->path);
                break;
            }
        }
    }
    $sim_addr = ($sloc && !empty($sloc->address)) ? $sloc->address : $s->post_title;
    $sim_subarea = '';
    if ($sloc) {
        $sim_subarea = $sloc->area ?? $sloc->district ?? $sloc->city ?? '';
    }
    $similar_listings[] = (object)[
        'url'     => get_permalink($s->ID),
        'address' => mb_strtoupper($sim_addr),
        'area'    => $sim_subarea,
        'image'   => $sim_first_img,
    ];
}

// ─── Status-badge logik ───
$status_label = 'TILL SALU';
$is_sold = get_post_meta($post_id, '_fasad_sold', true) === '1';
$preview = fasad_unserialize(get_post_meta($post_id, '_fasad_preview', true));
$is_preview = ($preview && !empty($preview->activated));
$bids = fasad_unserialize(get_post_meta($post_id, '_fasad_bids', true));
$has_bids = is_array($bids) && count($bids) > 0;
if (!empty($showings)) {
    $next = $showings[0];
    $start = strtotime($next->startDate);
    $status_label = 'VISNING ' . date('j/n', $start);
} elseif ($is_preview) {
    $status_label = 'FÖRHANDSVISNING';
} elseif ($has_bids) {
    $status_label = 'BUDGIVNING PÅGÅR';
} elseif ($is_sold) {
    $status_label = 'SÅLD';
}
@endphp

<article class="em-objekt-detalj">

  {{-- ════════ HERO ════════ --}}
  @if(!empty($images))
  <section class="em-detalj-hero">
    <img src="{{ $images[0] }}" alt="{{ $address }}" class="em-detalj-hero-img">
    <button class="em-detalj-alla-bilder-btn" onclick="emOpenGalleri()">ALLA BILDER</button>
  </section>
  @endif

  {{-- ════════ INTRO + MÄKLARE ════════ --}}
  <section class="em-detalj-intro">
    <div class="em-detalj-intro-vanster">
      @if($subarea)
        <p class="em-detalj-omrade">{{ mb_strtoupper($subarea, "UTF-8") }}</p>
      @endif
      <h1 class="em-detalj-rubrik">{{ mb_strtoupper($salesTitle, "UTF-8") }}</h1>
      @if($desc_text)
        <div class="em-detalj-saljtext">{!! $desc_text !!}</div>
      @endif

      <dl class="em-detalj-grundfakta">
        @if($price)
          <div class="em-grundfakta-rad"><dt>Pris</dt><dd>{{ $price }}</dd></div>
        @endif
        @if($fee)
          <div class="em-grundfakta-rad"><dt>Avgift</dt><dd>{{ $fee }}</dd></div>
        @endif
        @if($area_str)
          <div class="em-grundfakta-rad"><dt>Storlek</dt><dd>{{ $area_str }}</dd></div>
        @endif
        @if($rooms_count)
          <div class="em-grundfakta-rad"><dt>Antal Rum</dt><dd>{{ $rooms_count }}</dd></div>
        @endif
        @if($floor)
          <div class="em-grundfakta-rad"><dt>Våning</dt><dd>{{ $floor }}{{ $elevator === 'Ja' ? ', hiss finns' : '' }}</dd></div>
        @endif
        @if($built_year)
          <div class="em-grundfakta-rad"><dt>Byggår</dt><dd>{{ $built_year }}</dd></div>
        @endif
      </dl>
    </div>

    <aside class="em-detalj-intro-hoger">
      @if($maklare_bild)
        <img src="{{ $maklare_bild }}" alt="{{ $maklare_namn }}" class="em-detalj-maklare-bild">
      @endif
      <p class="em-detalj-maklare-typ">{{ strtoupper($maklare_typ) }}</p>
      <h2 class="em-detalj-maklare-namn">{{ strtoupper($maklare_namn) }}</h2>
      <div class="em-detalj-maklare-kontakt">
        <span>{{ strtoupper($maklare_titel) }}</span>
        @if($maklare_tel_display)
          <a href="tel:{{ $maklare_tel_href }}">{{ $maklare_tel_display }}</a>
        @endif
        @if($maklare_email)
          <a href="mailto:{{ $maklare_email }}">{{ strtoupper($maklare_email) }}</a>
        @endif
      </div>

      @if(!empty($showings))
        <div class="em-detalj-visningar">
        @foreach($showings as $i => $sh)
          @php
            $start = strtotime($sh->startDate);
            $end   = !empty($sh->endDate) ? strtotime($sh->endDate) : null;
            $dagar = ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'];
            $datum = $dagar[date('w',$start)] . ' ' . date('j',$start) . '/' . date('n',$start);
            $tid = date('H.i',$start) . ($end ? '-' . date('H.i',$end) : '');
          @endphp
          <label class="em-detalj-visning">
            <input type="checkbox" />
            <span class="em-detalj-visning-text">
              <strong>{{ $datum }} kl {{ $tid }}</strong>
              @if(!empty($sh->note))
                <em>{{ $sh->note }}</em>
              @else
                <em>Välkommen på visning, föranmälan önskas!</em>
              @endif
            </span>
          </label>
        @endforeach
        </div>
      @endif

      <button class="em-detalj-cta">INTRESSEANMÄLAN</button>
    </aside>
  </section>

  {{-- ════════ BILD 2 (planritning) + BILD 3 ════════ --}}
  @if(count($images) >= 3)
  <section class="em-detalj-tva-bilder">
    <div class="em-detalj-bild-vanster">
      <img src="{{ $images[1] }}" alt="Bild 2">
    </div>
    <div class="em-detalj-bild-hoger">
      <img src="{{ $images[2] }}" alt="Bild 3">
      <button class="em-detalj-alla-bilder-btn em-detalj-alla-bilder-btn--inline" onclick="emOpenGalleri()">ALLA BILDER</button>
    </div>
  </section>
  @endif

  {{-- ════════ LÄGENHETSFAKTA + FÖRENINGSFAKTA ════════ --}}
  <section class="em-detalj-fakta-grid">
    <div class="em-detalj-fakta-kolumn">
      <h3 class="em-detalj-fakta-rubrik">LÄGENHETSFAKTA</h3>
      <dl>
        @if($fee)<div><dt>Avgift</dt><dd>{{ $fee }}</dd></div>@endif
        <div><dt>Ingår i avgift</dt><dd>Värme, Vatten, Kabel-TV (basutbud)</dd></div>
        @if($area_str)<div><dt>Storlek</dt><dd>{{ $area_str }}</dd></div>@endif
        @if($rooms_count)<div><dt>Antal Rum</dt><dd>{{ $rooms_count }}</dd></div>@endif
        @if($floor)<div><dt>Våningsplan</dt><dd>{{ $floor }}{{ $elevator === 'Ja' ? ', hiss finns' : '' }}</dd></div>@endif
        @if($opcost)<div><dt>Total driftkostnad</dt><dd>{{ $opcost }}</dd></div>@endif
      </dl>
    </div>

    <div class="em-detalj-fakta-kolumn">
      <h3 class="em-detalj-fakta-rubrik">FÖRENINGSFAKTA</h3>
      <dl>
        @if($assoc_name)<div><dt>Förening</dt><dd>{{ $assoc_name }}</dd></div>@endif
        <div><dt>Äkta förening</dt><dd>{{ $assoc_genuine }}</dd></div>
        <div><dt>Boendetyp</dt><dd>{{ $assoc_form }}</dd></div>
        @if($energy_value)
          <div><dt>Energideklaration</dt><dd>{{ $energy_value }} kWh per kvm/år</dd></div>
        @endif
        @if($energy_status && $energy_date)
          <div><dt>Status</dt><dd>{{ $energy_status }}, {{ $energy_date }}</dd></div>
        @endif
      </dl>
    </div>
  </section>

  {{-- ════════ STOR BILD + RENOVERINGAR + DOKUMENT ════════ --}}
  @if(count($images) >= 4)
  <section class="em-detalj-wide-bild">
    <img src="{{ $images[3] }}" alt="Bild 4">
  </section>
  @endif

  <section class="em-detalj-renovering-dokument">
    @if($renovations)
    <div class="em-detalj-renoveringar">
      <h3 class="em-detalj-fakta-rubrik">GENOMFÖRDA RENOVERINGAR</h3>
      <div class="em-detalj-renoveringar-text">{!! nl2br(e($renovations)) !!}</div>
    </div>
    @endif

    @if(!empty($documents))
    <div class="em-detalj-dokument">
      <h3 class="em-detalj-fakta-rubrik">DOKUMENT PDF</h3>
      <div class="em-detalj-dokument-grid">
        @foreach($documents as $doc)
          <a href="{{ $doc->href }}" target="_blank" class="em-detalj-dokument-knapp">
            {{ strtoupper($doc->alias) }}
          </a>
        @endforeach
      </div>
    </div>
    @endif
  </section>

  {{-- ════════ KARTA + ADRESS ════════ --}}
  @if($lat && $lng)
  <section class="em-detalj-karta-sektion">
    <div id="em-detalj-karta" data-lat="{{ $lat }}" data-lng="{{ $lng }}"></div>
    <div class="em-detalj-adress">
      <p><strong>{{ strtoupper($address) }}</strong></p>
      @if($zipCode || $city)
        <p>{{ trim($zipCode . ' ' . strtoupper($city)) }}</p>
      @endif
      <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($address . ' ' . $city) }}" target="_blank" class="em-detalj-hitta">HITTA HIT</a>
    </div>
  </section>
  @endif

  {{-- ════════ ALLA BILDER (inline expansion) ════════ --}}
  @if(count($images) > 1)
  <section class="em-galleri-expand" id="em-galleri-expand">
    <div class="em-galleri-expand-inner">
      <div class="em-galleri-expand-grid">
        @foreach($images as $i => $img)
          <div class="em-galleri-expand-item">
            <img src="{{ $img }}" alt="Bild {{ $i+1 }}" loading="lazy">
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  {{-- ════════ LIKNANDE OBJEKT ════════ --}}
  @if(!empty($similar_listings))
  <section class="em-detalj-liknande">
    <h3 class="em-detalj-liknande-rubrik">LIKNANDE OBJEKT</h3>
    <div class="em-detalj-liknande-grid">
      @foreach($similar_listings as $sim)
        <a href="{{ $sim->url }}" class="em-detalj-liknande-kort">
          <div class="em-detalj-liknande-bild">
            <img src="{{ $sim->image }}" alt="{{ $sim->address }}">
          </div>
          <div class="em-detalj-liknande-text">
            <p>{{ $sim->area }}</p>
            <h4>{{ $sim->address }}</h4>
          </div>
        </a>
      @endforeach
    </div>
  </section>
  @endif


</article>

<script>
function emOpenGalleri() {
  var el = document.getElementById('em-galleri-expand');
  if (!el) return;
  el.classList.toggle('em-galleri-expand--open');
  if (el.classList.contains('em-galleri-expand--open')) {
    setTimeout(function() {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
  }
}

// Leaflet karta
document.addEventListener('DOMContentLoaded', function() {
  var mapEl = document.getElementById('em-detalj-karta');
  if (!mapEl || typeof L === 'undefined') return;
  var lat = parseFloat(mapEl.dataset.lat);
  var lng = parseFloat(mapEl.dataset.lng);
  if (isNaN(lat) || isNaN(lng)) return;

  var map = L.map(mapEl, { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 15);
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap &copy; CARTO'
  }).addTo(map);
  var icon = L.divIcon({
    className: 'em-detalj-karta-pin',
    html: '<div style="width:18px;height:18px;background:#000;border-radius:50%;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
    iconSize: [18,18],
    iconAnchor: [9,9]
  });
  L.marker([lat, lng], { icon: icon }).addTo(map);
});
</script>
