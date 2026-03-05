<h1>Welcome to TourStack</h1>
<p>Find authentic home-stays in Musanze and experience true Rwandan hospitality.</p>

<div class="features">
    <div class="feature">
        <h3>🏠 Authentic Stays</h3>
        <p>Live with local families and experience real Rwandan culture.</p>
    </div>
    <div class="feature">
        <h3>🎨 Cultural Experiences</h3>
        <p>Choose stays with Imigongo art, volcanic stone architecture, and traditional elements.</p>
    </div>
    <div class="feature">
        <h3>💰 Affordable Prices</h3>
        <p>Skip expensive lodges and find budget-friendly home-stays.</p>
    </div>
</div>

<div class="cta">
    <a href="index.php?page=listings" class="btn">Browse Available Stays</a>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="index.php?page=register" class="btn">Become a Host</a>
    <?php endif; ?>
</div>