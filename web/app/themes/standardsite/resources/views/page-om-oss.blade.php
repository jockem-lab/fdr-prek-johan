@extends('layouts.app')

@section('content')

{{-- TOPP-SEKTION: Bild + Om Oss-text --}}
<section class="em-om-intro">
  <div class="em-om-intro-inner">
    <div class="em-om-intro-bild">
      <img src="/app/uploads/team/johan-franzon.jpg" alt="Om oss">
    </div>
    <div class="em-om-intro-text">
      <p class="em-om-eyebrow">BAKGRUND</p>
      <h1 class="em-om-rubrik">OM OSS</h1>
      <p class="em-om-brodtext">Ett Mäkleri grundades av Johan Franzon, Johan Du Rietz och Farboud Nejad, som tillsammans har lång erfarenhet av bostadsförmedling i Stockholm. Med en gemensam ambition att erbjuda en mer personlig och genomtänkt mäklartjänst arbetar vi med ett selektivt urval av uppdrag varje år.</p>
      <p class="em-om-brodtext">Genom noggrannhet, lokalkännedom och ett starkt personligt engagemang skapar vi de bästa förutsättningarna för varje bostadsaffär. Vårt fokus ligger alltid på kvalitet, strategi och ett nära samarbete med våra kunder genom hela processen.</p>
      <a href="#em-team" class="em-om-cta">VÅRT TEAM</a>
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
          <p class="em-team-eyebrow">{{ strtoupper($m['titel']) }}</p>
          <h3 class="em-team-namn">{{ strtoupper($m['namn']) }}</h3>
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
  <div class="em-vision-inner">
    <div class="em-vision-bild">
      <img src="/app/uploads/hero/placeholder3.jpg" alt="Vår vision">
    </div>
    <div class="em-vision-text">
      <p class="em-om-eyebrow">OM OSS</p>
      <h2 class="em-om-rubrik">VÅR VISION</h2>
      <div class="em-vision-cols">
        <div class="em-vision-col">
          <h3 class="em-vision-col-rubrik">SELEKTION</h3>
          <p>Vi tror på medvetna val och ett selektivt urval. Genom att arbeta med ett begränsat antal uppdrag varje år kan vi ge varje bostad och varje kund den uppmärksamhet och engagemang de förtjänar.</p>
          <p>Varje förmedling är personlig och vi lägger stor vikt vid att förstå både uppdragsgivarens behov och bostadens unika kvaliteter.</p>
        </div>
        <div class="em-vision-col">
          <h3 class="em-vision-col-rubrik">KVALITET</h3>
          <p>För oss handlar kvalitet inte bara om resultatet, utan om hela processen. Från första värderingsmöte till slutförd affär arbetar vi med precision, omsorg och hög integritet.</p>
          <p>Vi tror på långsiktiga relationer och att en bostadsaffär ska vara genomtänkt — för både säljare och köpare.</p>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
