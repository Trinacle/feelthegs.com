<?php
/**
 * Helper functions for the Feel The G's theme.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Product image placeholder ----------
 * Used where a product has no featured image. SVG so it's tiny + scales to any
 * size. (Products missing thumbnails are also hidden at the query level — see
 * ftgs_hide_no_image_products() — but the fallback covers edge cases.)
 */
function ftgs_product_placeholder_url() {
    return get_stylesheet_directory_uri() . '/assets/img/no-product.svg';
}

/* ==========================================================================
   Shop card renderer — used by woocommerce/archive-product.php
   Renders a single product card on the shop / category grid. WC's native loop
   wrapper (woocommerce_product_loop_start) provides the <ul class="products">,
   so we render an <li> here.
   ========================================================================== */

function ftgs_render_shop_card( $product ) {
    if ( ! $product ) {
        return;
    }
    $post_id = $product->get_id();
    ?>
    <li <?php wc_product_class( 'ftgs-product-card', $product ); ?>>
      <a href="<?php the_permalink( $post_id ); ?>" class="ftgs-card-link" style="text-decoration:none;color:inherit;display:block;">

        <div class="ftgs-card-img">
          <?php if ( has_post_thumbnail( $post_id ) ) : ?>
            <?php echo get_the_post_thumbnail( $post_id, 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => get_the_title( $post_id ) ) ); ?>
          <?php else : ?>
            <img src="<?php echo esc_url( ftgs_product_placeholder_url() ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy">
          <?php endif; ?>

          <?php if ( $product->is_on_sale() ) : ?>
            <span class="ftgs-badge ftgs-badge-sale"><?php esc_html_e( 'Sale', 'feelthegs' ); ?></span>
          <?php endif; ?>
          <?php if ( ! $product->is_in_stock() ) : ?>
            <span class="ftgs-badge ftgs-badge-out"><?php esc_html_e( 'Sold out', 'feelthegs' ); ?></span>
          <?php endif; ?>
        </div>

        <div class="ftgs-card-body">
          <?php
          // Brand (product attribute) — quick context above the title.
          $brand_terms = get_the_terms( $post_id, 'pa_brand' );
          if ( $brand_terms && ! is_wp_error( $brand_terms ) ) :
            ?>
            <span class="ftgs-card-brand"><?php echo esc_html( $brand_terms[0]->name ); ?></span>
          <?php endif; ?>

          <h3 class="ftgs-card-title"><?php echo esc_html( $product->get_name() ); ?></h3>

          <?php if ( $product->get_price_html() ) : ?>
            <div class="ftgs-card-price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>
          <?php endif; ?>
        </div>
      </a>
    </li>
    <?php
}

/* ==========================================================================
   Active filter chips — shows the currently applied filters as removable chips
   above the grid, so users always know what's filtered (mirrors WC's default but
   styled to match the theme).
   ========================================================================== */

function ftgs_active_filter_chips() {
    $chips = array();
    $qs    = $_GET; // phpcs:ignore — sanitized below

    if ( isset( $qs['min_price'] ) || isset( $qs['max_price'] ) ) {
        $min = isset( $qs['min_price'] ) ? wc_price( floatval( $qs['min_price'] ) ) : wc_price( 0 );
        $max = isset( $qs['max_price'] ) ? wc_price( floatval( $qs['max_price'] ) ) : '&infin;';
        $chips['price'] = sprintf( __( 'Price: %s &ndash; %s', 'feelthegs' ), $min, $max );
    }

    if ( isset( $qs['on_sale'] ) && '1' === $qs['on_sale'] ) {
        $chips['on_sale'] = __( 'On sale', 'feelthegs' );
    }

    // Attribute filters: filter_{slug}=term-a,term-b
    foreach ( $qs as $key => $val ) {
        if ( 0 === strpos( $key, 'filter_' ) ) {
            $slug = substr( $key, 7 );
            $terms = array_filter( explode( ',', wc_clean( wp_unslash( $val ) ) ) );
            foreach ( $terms as $term_slug ) {
                $term = get_term_by( 'slug', $term_slug, 'pa_' . $slug );
                $label = $term && ! is_wp_error( $term ) ? $term->name : $term_slug;
                $chips[ $key . '|' . $term_slug ] = $label;
            }
        }
    }

    if ( empty( $chips ) ) {
        return;
    }

    echo '<div class="ftgs-active-chips">';
    foreach ( $chips as $ck => $label ) {
        // Build the chip's remove URL: same as toggle off.
        if ( strpos( $ck, '|' ) !== false ) {
            list( $key, $val ) = explode( '|', $ck, 2 );
            $current = isset( $qs[ $key ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $qs[ $key ] ) ) ) ) : array();
            $url = ftgs_filter_toggle_url( $key, $val, $current );
        } elseif ( 'price' === $ck ) {
            $new = $qs;
            unset( $new['min_price'], $new['max_price'] );
            $url = add_query_arg( array_map( 'sanitize_text_field', $new ), ftgs_current_base_url() );
        } else {
            $new = $qs;
            unset( $new[ $ck ] );
            $url = add_query_arg( array_map( 'sanitize_text_field', $new ), ftgs_current_base_url() );
        }
        printf( '<a href="%s" class="ftgs-chip">%s <span aria-hidden="true">&times;</span></a>', esc_url( $url ), wp_kses_post( $label ) );
    }
    // Clear-all
    $url = ftgs_current_base_url();
    printf( '<a href="%s" class="ftgs-chip ftgs-chip-clear">%s</a>', esc_url( $url ), esc_html__( 'Clear all', 'feelthegs' ) );
    echo '</div>';
}

/* ---------- Cart link (header) ---------- */
function ftgs_cart_link() {
    if ( ! function_exists( 'WC' ) ) {
        return;
    }
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $url   = wc_get_cart_url();
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="header-cart-btn" aria-label="<?php esc_attr_e( 'View cart', 'feelthegs' ); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <?php if ( $count > 0 ) : ?>
            <span class="cart-count"><?php echo (int) $count; ?></span>
        <?php endif; ?>
    </a>
    <?php
}

/* ---------- Reading time (blog single) ---------- */
function ftgs_reading_time( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $content = get_post_field( 'post_content', $post_id );
    $count   = str_word_count( wp_strip_all_tags( $content ) );
    return max( 1, round( $count / 200 ) );
}

/* ---------- Pagination (blog / non-WC) ---------- */
function ftgs_pagination() {
    echo '<nav class="ftgs-pagination">';
    echo paginate_links( array(
        'prev_text' => '&larr;',
        'next_text' => '&rarr;',
    ) );
    echo '</nav>';
}

/* ---------- Inline SVG chevrons / arrows ---------- */
function ftgs_chevron() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>';
}
function ftgs_arrow() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
}

/* ==========================================================================
   Backward-compatibility aliases.
   The theme was forked from ProductPro/SmokeDrop Noir which used the sdn_ prefix.
   These aliases let the inherited blog/product templates keep working while we
   migrate them. New code should use the ftgs_ names.
   ========================================================================== */
if ( ! function_exists( 'sdn_logo' ) ) {
    function sdn_logo( $size = 34 ) { return ftgs_logo( $size ); }
}
if ( ! function_exists( 'sdn_chevron' ) ) {
    function sdn_chevron() { return ftgs_chevron(); }
}
if ( ! function_exists( 'sdn_arrow' ) ) {
    function sdn_arrow() { return ftgs_arrow(); }
}
if ( ! function_exists( 'sdn_pagination' ) ) {
    function sdn_pagination() { return ftgs_pagination(); }
}
if ( ! function_exists( 'sdn_reading_time' ) ) {
    function sdn_reading_time( $post_id = null ) { return ftgs_reading_time( $post_id ); }
}
if ( ! function_exists( 'sdn_product_placeholder_url' ) ) {
    function sdn_product_placeholder_url() { return ftgs_product_placeholder_url(); }
}

/* ---------- CTA band (replaces ProductPro's sdn_cta dropshipping CTA) ----------
 * Used at the bottom of single.php and single-product.php. For a retail store
 * this is a "keep shopping" / newsletter band rather than a "create account" CTA.
 */
if ( ! function_exists( 'sdn_cta' ) ) {
    function sdn_cta( $title = '', $desc = '' ) {
        $title = $title ? $title : __( 'Ready to explore more?', 'feelthegs' );
        $desc  = $desc ? $desc : __( 'Discover thousands of premium products with discreet shipping on every order.', 'feelthegs' );
        $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
        ?>
        <section class="sec">
          <div class="wrap">
            <div class="ftgs-cta-band reveal">
              <div>
                <h2 class="display"><?php echo esc_html( $title ); ?></h2>
                <p class="lede"><?php echo esc_html( $desc ); ?></p>
              </div>
              <a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn-lime btn-lg">Shop now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            </div>
          </div>
        </section>
        <?php
    }
}

