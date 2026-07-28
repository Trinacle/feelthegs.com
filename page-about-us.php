<?php
/**
 * Template Name: About — Feel The G's
 * Template Post Type: page
 *
 * Bespoke dark-luxury About page. Uses the page's featured image as the hero
 * and the WP editor content as the body story. Flagged bespoke so it gets the
 * asset-stripped luxury treatment.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$ftgs_shop_url = get_permalink( wc_get_page_id( 'shop' ) );
$ftgs_hero_img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '';
$ftgs_title    = get_the_title();
$ftgs_subtitle = get_field( 'ftgs_about_subtitle' ); // optional ACF field
?>

<main class="ftgs-about">

  <!-- HERO -->
  <section class="ftgs-about-hero"<?php echo $ftgs_hero_img ? ' style="background-image:url(' . esc_url( $ftgs_hero_img ) . ')"' : ''; ?>>
    <div class="ftgs-about-hero-overlay"></div>
    <div class="ftgs-about-hero-inner wrap center">
      <p class="eyebrow reveal" style="justify-content:center;color:var(--gold);">Our Story</p>
      <h1 class="display reveal reveal-d1"><?php echo esc_html( $ftgs_title ); ?></h1>
      <?php if ( $ftgs_subtitle ) : ?>
        <p class="lede reveal reveal-d2" style="max-width:640px;margin:16px auto 0;color:var(--ink-dim);"><?php echo esc_html( $ftgs_subtitle ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- STATS BAND -->
  <section class="ftgs-about-stats reveal">
    <div class="wrap">
      <div class="ftgs-about-stats-grid">
        <div class="ftgs-about-stat">
          <span class="ftgs-about-stat-num" data-count="7000">7,000+</span>
          <span class="ftgs-about-stat-label">Curated products</span>
        </div>
        <div class="ftgs-about-stat">
          <span class="ftgs-about-stat-num">100%</span>
          <span class="ftgs-about-stat-label">Discreet shipping</span>
        </div>
        <div class="ftgs-about-stat">
          <span class="ftgs-about-stat-num">18+</span>
          <span class="ftgs-about-stat-label">Adults only</span>
        </div>
        <div class="ftgs-about-stat">
          <span class="ftgs-about-stat-num">24/7</span>
          <span class="ftgs-about-stat-label">Secure checkout</span>
        </div>
      </div>
    </div>
  </section>

  <!-- STORY (page body content) -->
  <section class="sec ftgs-about-story">
    <div class="wrap-tight wrap">
      <?php
      while ( have_posts() ) :
          the_post();
          the_content();
      endwhile;
      ?>
    </div>
  </section>

  <!-- VALUES GRID -->
  <section class="sec ftgs-about-values">
    <div class="wrap">
      <div class="ftgs-about-values-grid reveal">
        <div class="ftgs-about-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <h3>Privacy First</h3>
          <p>Every order ships in plain, unmarked packaging. Your secret stays yours.</p>
        </div>
        <div class="ftgs-about-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z"/><polyline points="9 12 11 14 15 10"/></svg>
          <h3>Body-Safe Quality</h3>
          <p>We carry only premium, body-safe materials from trusted brands.</p>
        </div>
        <div class="ftgs-about-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <h3>Real Support</h3>
          <p>Discreet, friendly, judgment-free customer care — whenever you need it.</p>
        </div>
        <div class="ftgs-about-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <h3>Fast, Reliable</h3>
          <p>Quick processing and reliable delivery on every single order.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="sec">
    <div class="wrap">
      <div class="ftgs-cta-band reveal">
        <div>
          <h2 class="display">Ready to explore?</h2>
          <p class="lede">Browse thousands of premium products with discreet shipping on every order.</p>
        </div>
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="btn btn-gold btn-lg">Shop now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
