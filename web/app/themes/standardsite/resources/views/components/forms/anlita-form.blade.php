@props([
    'id' => 'anlita-form',
])

<div class="em-anlita-form" id="{{ $id }}" aria-hidden="true">
  <div class="em-anlita-form-inner">
    <form class="em-anlita-form-grid" novalidate>

      {{-- Vänster spalt: DINA UPPGIFTER --}}
      <div class="em-anlita-form-col">
        <h3 class="em-anlita-form-rubrik">DINA UPPGIFTER</h3>
        <div class="em-anlita-form-fields">
          <div class="em-anlita-form-field">
            <input type="text" name="fornamn" placeholder="Förnamn*" required>
          </div>
          <div class="em-anlita-form-field">
            <input type="text" name="efternamn" placeholder="Efternamn*" required>
          </div>
          <div class="em-anlita-form-field">
            <input type="tel" name="telefon" placeholder="Telefonnummer*" required>
          </div>
          <div class="em-anlita-form-field">
            <input type="email" name="email" placeholder="E-post*" required>
          </div>
        </div>
        <div class="em-anlita-form-bottom">
          <label class="em-anlita-form-checkbox">
            <input type="checkbox" name="integritet" value="1" required>
            <span class="em-anlita-form-checkbox-box"></span>
            <span class="em-anlita-form-checkbox-text">Jag har tagit del av ETT MÄKLERI's integritetspolicy.</span>
          </label>
          <button type="submit" class="em-anlita-form-submit">SKICKA IN</button>
        </div>
      </div>

      {{-- Höger spalt: OM BOSTADEN --}}
      <div class="em-anlita-form-col">
        <h3 class="em-anlita-form-rubrik">OM BOSTADEN</h3>
        <div class="em-anlita-form-fields">
          <div class="em-anlita-form-field">
            <input type="text" name="adress" placeholder="Adress">
          </div>
          <div class="em-anlita-form-field em-anlita-form-field--textarea">
            <textarea name="om-bostaden" placeholder="Berätta mer om bostaden"></textarea>
          </div>
        </div>
        </div>

    </form>
  </div>
</div>
