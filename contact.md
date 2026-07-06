---
layout: default
title: Contact
---

<section class="section">
  <p class="eyebrow">Contact</p>
  <h1>Get in touch</h1>
  <p>We would be glad to hear from you. Reach out with any questions, or visit us at our clinic in the heart of Limassol.</p>

  <div class="contact-grid">

    <!-- CONTACT DETAILS -->
    <div class="contact-details">
      <h2>Clinic Details</h2>

      <h4>Address</h4>
      <p>
        Archiepiskopou Leontiou A 232<br>
        1st Floor, Apt 13<br>
        Limassol 3021, Cyprus
      </p>

      <!-- ============================================================
           >>> USER TO UPDATE: PHONE <<<
           Edit the "phone" value in _config.yml, or replace below.
           ============================================================ -->
      <h4>Phone</h4>
      <p>{{ site.phone }}</p>

      <!-- ============================================================
           >>> USER TO UPDATE: EMAIL <<<
           Edit the "email" value in _config.yml, or replace below.
           ============================================================ -->
      <h4>Email</h4>
      <p>{{ site.email }}</p>

      <!-- ============================================================
           >>> USER TO UPDATE: OPERATING HOURS <<<
           Edit the "hours_*" values in _config.yml, or replace below.
           ============================================================ -->
      <h4>Operating Hours</h4>
      <p>
        {{ site.hours_weekday }}<br>
        {{ site.hours_saturday }}<br>
        {{ site.hours_sunday }}
      </p>

      <a href="{{ site.baseurl }}/consultation" class="btn btn-solid" style="margin-top:1rem;">Book a Consultation</a>
    </div>

    <!-- ============================================================
         GOOGLE MAPS EMBED
         This embed is pre-set to the clinic address. To fine-tune
         the exact pin, open Google Maps, search the address, click
         "Share" > "Embed a map", copy the <iframe> and paste it here
         to replace the one below.
         ============================================================ -->
    <div class="contact-map">
      <h2>Find Us</h2>
      <!-- The map iframe is inserted by the script below so the address
           can be edited in one place (the "clinicAddress" variable). -->
      <div class="map-embed" id="clinic-map"></div>
      <script>
        (function () {
          // >>> USER: to change the pin, edit this address string. <<<
          var clinicAddress = "Archiepiskopou Leontiou A 232, Limassol 3021, Cyprus";
          var base = "https://www.google.com" + "/maps?q=";
          var iframe = document.createElement("iframe");
          iframe.src = base + encodeURIComponent(clinicAddress) + "&output=embed";
          iframe.width = "100%";
          iframe.height = "400";
          iframe.style.border = "0";
          iframe.loading = "lazy";
          iframe.setAttribute("allowfullscreen", "");
          iframe.setAttribute("referrerpolicy", "no-referrer-when-downgrade");
          iframe.title = "NovaLife Medica location map";
          document.getElementById("clinic-map").appendChild(iframe);
        })();
      </script>
    </div>

  </div>
</section>
