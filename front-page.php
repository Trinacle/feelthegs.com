<?php
/**
 * The front page — Feel The G's homepage.
 *
 * Retail homepage: hero, shop-by-category grid, new arrivals (latest products),
 * value props (discreet shipping / age verification / secure checkout), and a
 * newsletter CTA. Pulls live WC categories + products so it stays current.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$ftgs_shop_url = get_permalink( wc_get_page_id( 'shop' ) );

// Featured categories for the "Shop by category" grid (top 8 by product count).
$ftgs_home_cats = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 8,
    'orderby'    => 'count',
    'order'      => 'DESC',
) );
if ( is_wp_error( $ftgs_home_cats ) ) {
    $ftgs_home_cats = array();
}

// Latest 8 in-stock products with a featured image.
$ftgs_new = new WP_Query( array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => 8,
    'ignore_sticky_posts' => true,
    'meta_query'          => array(
        array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
        array( 'key' => '_stock_status', 'value' => 'outofstock', 'compare' => '!=' ),
    ),
) );

// On-sale products (max 4) for the deals strip.
$ftgs_sale_ids = wc_get_product_ids_on_sale();
$ftgs_sale_ids = array_slice( $ftgs_sale_ids, 0, 4 );
?>

<main>

  <?php
  // HERO SLIDER — pulls slides from the ACF ftgs_hero_slides repeater on the
  // Home page. Falls back to a dark text hero when no slides are configured.
  $ftgs_hero_slides = function_exists( 'get_field' ) ? get_field( 'ftgs_hero_slides' ) : null;
  $ftgs_has_slides  = is_array( $ftgs_hero_slides ) && ! empty( $ftgs_hero_slides );
  ?>

  <?php if ( $ftgs_has_slides ) : ?>
  <!-- HERO SLIDER -->
  <section class="ftgs-hero-slider" data-ftgs-slider>
    <div class="ftgs-hero-track">
      <?php foreach ( $ftgs_hero_slides as $i => $slide ) :
          $img_arr = ! empty( $slide['image'] ) ? $slide['image'] : null;
          $img_url = is_array( $img_arr ) ? ( isset( $img_arr['sizes']['large'] ) ? $img_arr['sizes']['large'] : $img_arr['url'] ) : ( is_string( $img_arr ) ? $img_arr : '' );
          if ( ! $img_url ) { continue; }
          $heading     = isset( $slide['heading'] ) ? $slide['heading'] : '';
          $subheading  = isset( $slide['subheading'] ) ? $slide['subheading'] : '';
          $cta_text    = ! empty( $slide['cta_text'] ) ? $slide['cta_text'] : 'Shop Now';
          $cta_url     = ! empty( $slide['cta_url'] ) ? $slide['cta_url'] : $ftgs_shop_url;
          $text_color  = isset( $slide['text_color'] ) ? $slide['text_color'] : 'light';
          $is_dark_text = ( 'dark' === $text_color );
          ?>
        <div class="ftgs-hero-slide<?php echo 0 === $i ? ' is-active' : ''; ?> ftgs-hero-text-<?php echo esc_attr( $text_color ); ?>" style="background-image:url('<?php echo esc_url( $img_url ); ?>');">
          <div class="ftgs-hero-overlay<?php echo $is_dark_text ? ' ftgs-hero-overlay-light' : ''; ?>"></div>
          <div class="ftgs-hero-inner wrap center">
            <?php if ( $heading ) : ?>
              <h1 class="ftgs-hero-heading reveal reveal-d1"><?php echo wp_kses_post( $heading ); ?></h1>
            <?php endif; ?>
            <?php if ( $subheading ) : ?>
              <p class="ftgs-hero-subheading reveal reveal-d2"><?php echo esc_html( $subheading ); ?></p>
            <?php endif; ?>
            <?php if ( $cta_text ) : ?>
              <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-gold btn-lg reveal reveal-d3"><?php echo esc_html( $cta_text ); ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ( count( $ftgs_hero_slides ) > 1 ) : ?>
      <!-- Navigation -->
      <button class="ftgs-hero-arrow ftgs-hero-prev" aria-label="Previous slide" data-ftgs-slider-prev>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button class="ftgs-hero-arrow ftgs-hero-next" aria-label="Next slide" data-ftgs-slider-next>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <div class="ftgs-hero-dots" data-ftgs-slider-dots>
        <?php foreach ( $ftgs_hero_slides as $i => $slide ) : ?>
          <button class="ftgs-hero-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-ftgs-slider-dot="<?php echo (int) $i; ?>" aria-label="Go to slide <?php echo (int) ( $i + 1 ); ?>"></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <?php else : ?>
  <!-- HERO (text fallback — add slides in Home → Hero Slider to enable the slider) -->
  <section class="page-hero">
    <div class="ph-smoke"><div class="ph-blob b1"></div><div class="ph-blob b2"></div><div class="ph-blob b3"></div></div>
    <div class="ph-inner center">
      <p class="eyebrow reveal" style="justify-content:center;">Discreet &middot; Premium &middot; 18+</p>
      <h1 class="display reveal reveal-d1" style="margin:24px 0;">
        Strap <span class="italic gradient-text">Yourself In.</span>
      </h1>
      <p class="lede reveal reveal-d2" style="max-width:680px;margin:0 auto;">
        Premium sex toys, lingerie, and bondage gear — curated for pleasure. Discreet shipping on every order, every time.
      </p>
      <div class="hero-actions reveal reveal-d3" style="justify-content:center;margin-top:32px;">
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="btn btn-gold btn-lg">Shop Now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- DISCREET SHIPPING BAND (replaces the 4-item SaaS value props) -->
  <section class="ftgs-discreet-band reveal">
    <div class="wrap center">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      <span>Discreet packaging. Every order.</span>
    </div>
  </section>

  <?php if ( ! empty( $ftgs_home_cats ) ) : ?>
  <!-- SHOP BY CATEGORY (auto thumbnails via ftgs_category_image; no counts) -->
  <section class="sec">
    <div class="wrap">
      <div class="sec-head reveal">
        <h2 class="display">Shop by Category</h2>
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="link-arrow">View all <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
      <div class="ftgs-cat-grid">
        <?php foreach ( $ftgs_home_cats as $ftgs_cat ) :
            $ftgs_img = function_exists( 'ftgs_category_image' ) ? ftgs_category_image( $ftgs_cat, 'woocommerce_thumbnail' ) : '';
            ?>
          <a href="<?php echo esc_url( get_term_link( $ftgs_cat ) ); ?>" class="ftgs-cat-card reveal"<?php echo $ftgs_img ? ' style="background-image:url(' . esc_url( $ftgs_img ) . ')"' : ''; ?>>
            <div class="ftgs-cat-overlay"></div>
            <div class="ftgs-cat-body">
              <h3><?php echo esc_html( $ftgs_cat->name ); ?></h3>
              <span class="ftgs-cat-cta">Shop now &rarr;</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $ftgs_new->have_posts() ) : ?>
  <!-- NEW ARRIVALS -->
  <section class="sec">
    <div class="wrap">
      <div class="sec-head reveal">
        <h2 class="display">New Arrivals</h2>
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="link-arrow">Shop all <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
      <ul class="products ftgs-home-products reveal">
        <?php
        while ( $ftgs_new->have_posts() ) :
            $ftgs_new->the_post();
            global $product;
            $product = wc_get_product( get_the_ID() );
            if ( ! $product ) {
                continue;
            }
            ftgs_render_shop_card( $product );
        endwhile;
        wp_reset_postdata();
        ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( ! empty( $ftgs_sale_ids ) ) : ?>
  <!-- DEALS -->
  <section class="sec ftgs-deals-sec">
    <div class="wrap">
      <div class="sec-head reveal">
        <h2 class="display">On Sale Now</h2>
        <a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', $ftgs_shop_url ) ); ?>" class="link-arrow">All deals <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
      <ul class="products ftgs-home-products reveal">
        <?php
        foreach ( $ftgs_sale_ids as $ftgs_pid ) :
            $ftgs_p = wc_get_product( $ftgs_pid );
            if ( ! $ftgs_p ) {
                continue;
            }
            ftgs_render_shop_card( $ftgs_p );
        endforeach;
        ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <!-- DISCREET SHIPPING CTA -->
  <section class="sec">
    <div class="wrap">
      <div class="ftgs-cta-band reveal">
        <div>
          <h2 class="display">Your privacy, our promise.</h2>
          <p class="lede">Every order ships in plain, unmarked packaging. No one knows what's inside but you. Discreet billing, fast shipping, total confidentiality.</p>
        </div>
        <a href="<?php echo esc_url( home_url( '/discreet-shipping' ) ); ?>" class="btn btn-outline btn-lg">Learn more</a>
      </div>
    </div>
  </section>

  <!-- NEWSLETTER CTA -->
  <section class="sec">
    <div class="wrap">
      <div class="ftgs-news-cta reveal">
        <h2 class="display">Get 10% off your first order.</h2>
        <p class="lede">Join the list for exclusive deals, new arrivals, and intimate inspiration. Discreetly delivered.</p>
        <form class="news-form ftgs-news ftgs-news-large" id="ftgs-news-form-hero" novalidate>
          <input type="email" name="email" id="ftgs-news-email-hero" placeholder="Enter your email" required aria-label="Email address">
          <button type="submit" class="btn btn-lime">Get my code</button>
          <?php wp_nonce_field( 'ftgs_newsletter', 'ftgs_nonce_field' ); ?>
        </form>
        <div id="ftgs-news-msg-hero" class="ftgs-news-msg" role="status" aria-live="polite"></div>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
