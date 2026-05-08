@extends('layouts.app')

@section('content')

{{-- TOPP-SEKTION: Bild + Om Oss-text --}}
<section class="em-om-intro">
  <div class="em-om-intro-inner">
    <div class="em-om-intro-bild">
      <img src="/app/uploads/team/johan-franzon.jpg" alt="Om oss">
    </div>
    <div class="em-om-intro-text em-textblock">
      <p class="em-t-eyebrow--lg">BAKGRUND</p>
      <h1 class="em-t-h1">OM OSS</h1>
      <p class="em-t-body">Ett Mäkleri grundades av Johan Franzon, Johan Du Rietz och Farboud Nejad, som tillsammans har lång erfarenhet av bostadsförmedling i Stockholm. Med en gemensam ambition att erbjuda en mer personlig och genomtänkt mäklartjänst arbetar vi med ett selektivt urval av uppdrag varje år. Genom noggrannhet, lokalkännedom och ett starkt personligt engagemang skapar vi de bästa förutsättningarna för varje bostadsaffär. Vårt fokus ligger alltid på kvalitet, strategi och ett nära samarbete med våra kunder genom hela processen.</p>
      <a href="#em-team" class="em-om-cta em-t-tag em-textblock-cta">VÅRT TEAM</a>
    </div>
  </div>
</section>

{{-- TEAM-GRID --}}
<section class="em-team" id="em-team">
  <div class="em-team-inner">
    @php $team = [
      ['namn' => 'Johan Franzon', 'titel' => 'Fastighetsmäklare', 'telefon' => '+46 704 45 51 80', 'email' => 'franzon@ettmakleri.se', 'bild' => '/app/uploads/team/johan-franzon.jpg'],
      ['namn' => 'Johan Du Rietz', 'titel' => 'Fastighetsmäklare', 'telefon' => '+46 708 80 07 99', 'email' => 'durietz@ettmakleri.se', 'bild' => '/app/uploads/team/johan-durietz.jpg'],
      ['namn' => 'Farboud Nejad', 'titel' => 'Fastighetsmäklare', 'telefon' => '+46 739 09 49 06', 'email' => 'nejad@ettmakleri.se', 'bild' => '/app/uploads/team/farboud-nejad.jpg'],
      ['namn' => 'Emelie Willberg', 'titel' => 'Affärskoordinator', 'telefon' => '+46 765 28 22 68', 'email' => 'willberg@ettmakleri.se', 'bild' => '/app/uploads/team/emelie-willberg.jpg'],
      ['namn' => 'Sandra Zeilon', 'titel' => 'Affärskoordinator & kontorsansvarig', 'telefon' => '+46 730 78 19 60', 'email' => 'zeilon@ettmakleri.se', 'bild' => '/app/uploads/team/sandra-zeilon.jpg'],
      ['namn' => 'Susanne Hagensgård', 'titel' => 'Fastighetsmäklare', 'telefon' => '+46 707 49 04 43', 'email' => 'hagensgard@ettmakleri.se', 'bild' => '/app/uploads/team/susanne-hagensgard.jpg'],
    ]; @endphp

    @foreach($team as $m)
      <div class="em-team-kort">
        <div class="em-team-bild">
          <img src="{{ $m['bild'] }}" alt="{{ $m['namn'] }}">
        </div>
        <div class="em-team-info">
          <p class="em-team-eyebrow em-t-eyebrow">{{ strtoupper($m['titel']) }}</p>
          <h3 class="em-team-namn em-t-h3">{{ strtoupper($m['namn']) }}</h3>
          <div class="em-team-kontakt">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $m['telefon']) }}">{{ $m['telefon'] }}</a>
            <a href="mailto:{{ $m['email'] }}">{{ strtoupper($m['email']) }}</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</section>

{{-- VÅR VISION-SEKTION --}}
<section class="em-vision">
  <div class="em-vision-bild-wrap">
    <img src="/app/uploads/hero/placeholder3.jpg" alt="Vår vision" class="em-vision-bild-img">
  </div>
  <div class="em-vision-content">
    <div class="em-vision-col em-textblock">
      <p class="em-t-eyebrow--lg">OM OSS</p>
      <h2 class="em-t-h2">EXEMPELRUBRIK</h2>
      <p class="em-t-body">Vår främsta målsättning är att upprätthålla högsta kvalitet i varje enskild försäljning. För att säkerställa detta arbetar vi med ett selektivt urval av uppdrag varje år, vilket ger oss möjlighet att erbjuda en genomtänkt, strukturerad och noggrant anpassad försäljningsprocess. Tillgänglighet, flexibilitet och ett personligt engagemang är avgörande faktorer för att skapa bästa möjliga förutsättningar för ett optimalt slutpris.</p>
    </div>
    <div class="em-vision-col em-textblock">
      <p class="em-t-eyebrow--lg">VÅR VISION</p>
      <h2 class="em-t-h2">EXEMPELRUBRIK</h2>
      <p class="em-t-body">Vår främsta målsättning är att upprätthålla högsta kvalitet i varje enskild försäljning. För att säkerställa detta arbetar vi med ett selektivt urval av uppdrag varje år, vilket ger oss möjlighet att erbjuda en genomtänkt, strukturerad och noggrant anpassad försäljningsprocess. Tillgänglighet, flexibilitet och ett personligt engagemang är avgörande faktorer för att skapa bästa möjliga förutsättningar för ett optimalt slutpris.</p>
    </div>
  </div>
</section>

@endsection
