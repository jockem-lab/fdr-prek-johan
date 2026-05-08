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
      <div class="em-listing-kort" data-image-count="{{ $image_count }}" data-href="{{ home_url('/objekt/' . $listing->slug) }}">
        <div class="em-listing-bild">
          @if($image_count > 0)
            <div class="em-listing-track">
              @foreach($images as $i => $img)
                <a href="{{ home_url('/objekt/' . $listing->slug) }}" class="em-listing-slide" data-index="{{ $i }}">
                  <img src="{{ $img }}" alt="{{ $listing->address }}" class="em-listing-img" loading="lazy">
                </a>
              @endforeach
            </div>
          @else
            <a href="{{ home_url('/objekt/' . $listing->slug) }}" class="em-listing-slide">
              <div class="em-listing-bild-placeholder"></div>
            </a>
          @endif

          @if($listing->status)
            <div class="em-listing-status">{{ strtoupper($listing->status) }}</div>
          @endif

          @if($image_count > 1)
            <div class="em-listing-pilar">
              <button type="button" class="em-listing-pil em-listing-pil--prev" aria-label="Föregående bild">
                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 1L1 6L5 11" stroke="currentColor" stroke-width="2" stroke-linecap="square"/>
                </svg>
              </button>
              <button type="button" class="em-listing-pil em-listing-pil--next" aria-label="Nästa bild">
                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 1L5 6L1 11" stroke="currentColor" stroke-width="2" stroke-linecap="square"/>
                </svg>
              </button>
            </div>
          @endif
        </div>

        <a href="{{ home_url('/objekt/' . $listing->slug) }}" class="em-listing-info">
          <div class="em-listing-rad">
            <span class="em-listing-adress">{{ mb_strtoupper($listing->address, 'UTF-8') }}</span>
            <span class="em-listing-omrade">{{ mb_strtoupper($listing->type, 'UTF-8') }}</span>
          </div>
          <div class="em-listing-detaljer">
            @if($listing->area){{ $listing->area }}@endif
            @if($listing->price) &nbsp;&nbsp; {{ $listing->price }}@endif
            @if($listing->rooms) &nbsp;&nbsp; {{ $listing->rooms }}@endif
          </div>
        </a>
      </div>
    @endforeach
  </div>
</div>
