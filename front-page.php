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

  <!-- HERO -->
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
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="btn btn-lime btn-lg">Shop Now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        <?php if ( ! empty( $ftgs_home_cats ) ) : ?>
          <a href="<?php echo esc_url( get_term_link( $ftgs_home_cats[0] ) ); ?>" class="btn btn-outline btn-lg">Browse <?php echo esc_html( $ftgs_home_cats[0]->name ); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- VALUE PROPS -->
  <section class="sec ftgs-value-sec">
    <div class="wrap">
      <div class="ftgs-value-grid reveal">
        <div class="ftgs-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <div><strong>Discreet Shipping</strong><span>Plain packaging, every order</span></div>
        </div>
        <div class="ftgs-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <div><strong>Secure Checkout</strong><span>Encrypted &amp; private</span></div>
        </div>
        <div class="ftgs-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 5-3.5 8-9 9-5.5-1-9-4-9-9V5l9-3 9 3v7z"/></svg>
          <div><strong>Verified Quality</strong><span>Body-safe materials</span></div>
        </div>
        <div class="ftgs-value">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <div><strong>Real Support</strong><span>Here when you need us</span></div>
        </div>
      </div>
    </div>
  </section>

  <?php if ( ! empty( $ftgs_home_cats ) ) : ?>
  <!-- SHOP BY CATEGORY -->
  <section class="sec">
    <div class="wrap">
      <div class="sec-head reveal">
        <h2 class="display">Shop by Category</h2>
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>" class="link-arrow">View all <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
      <div class="ftgs-cat-grid">
        <?php foreach ( $ftgs_home_cats as $ftgs_cat ) :
            $ftgs_thumb_id = get_term_meta( $ftgs_cat->term_id, 'thumbnail_id', true );
            $ftgs_img = $ftgs_thumb_id ? wp_get_attachment_image_url( $ftgs_thumb_id, 'ftgs-category-hero' ) : '';
            ?>
          <a href="<?php echo esc_url( get_term_link( $ftgs_cat ) ); ?>" class="ftgs-cat-card reveal"<?php echo $ftgs_img ? ' style="background-image:url(' . esc_url( $ftgs_img ) . ')"' : ''; ?>>
            <div class="ftgs-cat-overlay"></div>
            <div class="ftgs-cat-body">
              <h3><?php echo esc_html( $ftgs_cat->name ); ?></h3>
              <span class="ftgs-cat-count"><?php echo (int) $ftgs_cat->count; ?> products</span>
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
