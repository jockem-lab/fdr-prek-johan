@props([
    'id' => 'anlita-form',
])

<div class="em-anlita-form" id="{{ $id }}" aria-hidden="true">
  <div class="em-anlita-form-inner">
    <form class="em-anlita-form-grid" novalidate>

      {{-- Vänster kolumn: DINA UPPGIFTER --}}
      <div class="em-anlita-form-col">
        <h3 class="em-anlita-form-rubrik">DINA UPPGIFTER</h3>

        <div class="em-anlita-form-field">
          <input type="text" name="fornamn" id="anlita-fornamn" required>
          <label for="anlita-fornamn">Förnamn*</label>
        </div>

        <div class="em-anlita-form-field">
          <input type="text" name="efternamn" id="anlita-efternamn" required>
          <label for="anlita-efternamn">Efternamn*</label>
        </div>

        <div class="em-anlita-form-field">
          <input type="tel" name="telefon" id="anlita-telefon" required>
          <label for="anlita-telefon">Telefonnummer*</label>
        </div>

        <div class="em-anlita-form-field">
          <input type="email" name="email" id="anlita-email" required>
          <label for="anlita-email">E-post*</label>
        </div>
      </div>

      {{-- Höger kolumn: OM BOSTADEN --}}
      <div class="em-anlita-form-col">
        <h3 class="em-anlita-form-rubrik">OM BOSTADEN</h3>

        <div class="em-anlita-form-field">
          <input type="text" name="adress" id="anlita-adress">
          <label for="anlita-adress">Adress</label>
        </div>

        <div class="em-anlita-form-field em-anlita-form-field--textarea">
          <textarea name="om-bostaden" id="anlita-om-bostaden" rows="6"></textarea>
          <label for="anlita-om-bostaden">Berätta mer om bostaden</label>
        </div>
      </div>

      {{-- Footer-rad: checkbox + submit --}}
      <div class="em-anlita-form-footer">
        <label class="em-anlita-form-checkbox">
          <input type="checkbox" name="integritet" value="1" required>
          <span class="em-anlita-form-checkbox-box"></span>
          <span>Jag har tagit del av <a href="{{ home_url('/integritetspolicy') }}">ETT MÄKLERI's integritetspolicy</a>.</span>
        </label>

        <button type="submit" class="em-anlita-form-submit">SKICKA IN</button>
      </div>

    </form>
  </div>
</div>
