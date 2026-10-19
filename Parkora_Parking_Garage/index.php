<?php 
require 'config.php';
$page_title = 'Smart Parking';
require 'includes/header.php';
?>

<section class="hero">
    <div>
        <span class="eyebrow">SMART PARKING GARAGE</span>
        <h1>Park easy.<br><span>Leave happy.</span></h1>
        <p>Parkora handles spaces, drivers, parking sessions, exits and charges in one polished garage system.</p>
        <a class="btn primary" href="parking.php">Find a parking slot →</a> 
        <a class="btn light" href="user_login.php">Driver login</a>
    </div>
    <div class="hero-card">
        <span class="eyebrow" style="color:#efbd7d">PARKORA GARAGE</span>
        
        <!-- Added Marquee in place of the big emoji -->
        <marquee behavior="scroll" direction="left" scrollamount="6" style="font-size: 2.5rem; font-weight: bold; margin: 15px 0; color: #fff;">
            ⚡ 24/7 LIVE ACCESS • EV CHARGING READY • INSTANT DIGITAL PARKING PASSES • DRIVE IN & RELAX 🌟
        </marquee>
        
        <h2>YOUR SPACE IS WAITING.</h2>
        <div class="chips">
            <span class="chip">24/7 Access</span>
            <span class="chip">EV Ready</span>
            <span class="chip">Digital Records</span>
        </div>
    </div>
</section>

<section class="wrap">
    <div class="head">
        <div>
            <span class="eyebrow">HOW IT WORKS</span>
            <h2>Three simple steps.</h2>
        </div>
    </div>
    <div class="cards">
        <div class="card">
            <div class="code">01 · Find</div>
            <p class="muted">Browse live available spaces and choose the right one for your vehicle.</p>
        </div>
        <div class="card">
            <div class="code">02 · Park</div>
            <p class="muted">Enter your vehicle number. Your session immediately appears in My Garage.</p>
        </div>
        <div class="card">
            <div class="code">03 · Exit</div>
            <p class="muted">End the session when you leave. The system calculates the parking charge automatically.</p>
        </div>
    </div>
</section>

<?php 
require 'includes/footer.php'; 
?>