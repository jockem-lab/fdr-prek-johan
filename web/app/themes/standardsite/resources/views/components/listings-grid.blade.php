@props([
    'listings' => [],
    'id' => 'listings-grid',
])

<div class="em-listings-grid" id="{{ $id }}" aria-hidden="true">
  <div class="em-listings-grid-inner">
    @foreach($listings as $listing)
      @php
        $images = $listing->images ?? ($listing->image ? [$listing->image] : []);
        $image_count = count($images);
      @endphp
      <a href="{{ home_url('/objekt/' . $listing->slug) }}" class="em-listing-kort" data-image-count="{{ $image_count }}" data-image-index="0">
        <div class="em-listing-bild">
          @foreach($images as $i => $img)
            <img src="{{ $img }}" alt="{{ $listing->address }}" class="em-listing-img {{ $i === 0 ? 'em-listing-img--active' : '' }}" data-index="{{ $i }}">
          @endforeach

          @if(empty($images))
            <div class="em-listing-bild-placeholder"></div>
          @endif

          @if($listing->status)
            <div class="em-listing-status">{{ strtoupper($listing->status) }}</div>
          @endif

          @if($image_count > 1)
            <div class="em-listing-pilar">
              <button type="button" class="em-listing-pil em-listing-pil--prev" aria-label="Föregående bild">‹</button>
              <button type="button" class="em-listing-pil em-listing-pil--next" aria-label="Nästa bild">›</button>
            </div>
          @endif
        </div>

        <div class="em-listing-info">
          <div class="em-listing-rad">
            <span class="em-listing-adress">{{ mb_strtoupper($listing->address, 'UTF-8') }}</span>
            <span class="em-listing-omrade">{{ mb_strtoupper($listing->type, 'UTF-8') }}</span>
          </div>
          <div class="em-listing-detaljer">
            @if($listing->area){{ $listing->area }}@endif
            @if($listing->price) &nbsp;&nbsp; {{ $listing->price }}@endif
            @if($listing->rooms) &nbsp;&nbsp; {{ $listing->rooms }}@endif
          </div>
        </div>
      </a>
    @endforeach
  </div>
</div>
