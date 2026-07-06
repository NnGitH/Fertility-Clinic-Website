---
layout: default
title: Book a Consultation
---

<section class="section">
  <p class="eyebrow">Consultation</p>
  <h1>Book your private consultation</h1>
  <p>Every journey to parenthood is different. Your initial consultation is a relaxed, confidential conversation where we listen to your story, review your history, and help you understand your options — with no pressure and no judgement.</p>
  <p>You can either <strong>schedule a time instantly</strong> using our online calendar below, or send us an <strong>enquiry</strong> using the form and we will get back to you promptly.</p>

  <h2>What to expect</h2>
  <ul>
    <li>A thorough review of your medical history and fertility goals.</li>
    <li>Honest, evidence-based answers to your questions.</li>
    <li>A clear, personalised plan for your next steps.</li>
    <li>Complete privacy and confidentiality throughout.</li>
  </ul>
</section>

<!-- ============================================================
     CALENDLY BOOKING WIDGET
     >>> USER TO UPDATE <<<
     Set your real Calendly link in _config.yml (calendly_url).
     Sign up free at https://calendly.com, create an event type
     (e.g. "Fertility Consultation"), copy your event link, and
     paste it as calendly_url in _config.yml.
     ============================================================ -->
<section class="section-alt">
  <div class="section section-center">
    <p class="eyebrow">Schedule Instantly</p>
    <h2>Pick a time that suits you</h2>
    <p>Choose an available slot from our live calendar below.</p>

    <!-- Calendly inline widget -->
    <div class="calendly-inline-widget"
         data-url="{{ site.calendly_url }}"
         style="min-width:320px;height:660px;"></div>
    <script type="text/javascript"
            src="https://assets.calendly.com/assets/external/widget.js"
            async></script>
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">

    <p class="small-note">Not seeing the calendar? Make sure your <code>calendly_url</code> is set correctly in <code>_config.yml</code>.</p>
  </div>
</section>

<!-- ============================================================
     FORMSPREE ENQUIRY FORM
     >>> USER TO UPDATE <<<
     1. Create a free account at https://formspree.io
     2. Create a new form and copy your form's endpoint ID
        (it looks like "xdorzabc" — the part after formspree.io/f/).
     3. Set formspree_id in _config.yml to that ID.
        Alternatively, replace {{ site.formspree_id }} below with it.
     4. The first time a message is submitted, Formspree emails you
        to confirm the form. After confirming, submissions arrive
        at the email registered on your Formspree account.
     ============================================================ -->
<section class="section">
  <p class="eyebrow">Send an Enquiry</p>
  <h2>Prefer to message us first?</h2>
  <p>Fill in the form below and we will respond as soon as possible. All enquiries are treated in the strictest confidence.</p>

  <form class="contact-form"
        action="https://formspree.io/f/{{ site.formspree_id }}"
        method="POST">
    <div class="form-row">
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" required>
    </div>
    <div class="form-row">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-row">
      <label for="phone">Phone (optional)</label>
      <input type="tel" id="phone" name="phone">
    </div>
    <div class="form-row">
      <label for="message">How can we help?</label>
      <textarea id="message" name="message" rows="6" required></textarea>
    </div>
    <!-- Optional: set the email subject line -->
    <input type="hidden" name="_subject" value="New consultation enquiry — NovaLife Medica">
    <button type="submit" class="btn btn-solid">Send Enquiry</button>
  </form>
</section>
