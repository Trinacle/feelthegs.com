<?php
/**
 * The template for displaying a single WooCommerce product.
 *
 * Bespoke ProductPro product page — premium two-column layout with a
 * sticky gallery, integrated cart, trust badges, dropship/wholesale CTAs,
 * tabbed description/specs/reviews, and a related-products rail.
 *
 * Still fires the WooCommerce cart form (woocommerce_template_single_add_to_cart)
 * so add-to-cart, variations, and AJAX all work normally.
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) :
    the_post();
    $product = wc_get_product( get_the_ID() );
    if ( ! $product ) break;

    $brand_terms = get_the_terms( get_the_ID(), 'product_brand' );
    $brand_name  = ( $brand_terms && ! is_wp_error( $brand_terms ) ) ? $brand_terms[0]->name : '';
    $brand_link  = ( $brand_terms && ! is_wp_error( $brand_terms ) ) ? get_term_link( $brand_terms[0] ) : '';

    $cat_terms = get_the_terms( get_the_ID(), 'product_cat' );
    $cat_names = ( $cat_terms && ! is_wp_error( $cat_terms ) )
        ? implode( ', ', wp_list_pluck( $cat_terms, 'name' ) ) : '';

    $gallery_ids = $product->get_gallery_image_ids();
    $sku         = $product->get_sku();
    $weight      = $product->get_weight();
    $dimensions  = array_filter( array( $product->get_length(), $product->get_width(), $product->get_height() ) );
    $register    = 'https://app.f13commerce.com/register';
    $shop_url    = get_permalink( wc_get_page_id( 'shop' ) );
    $is_variable = $product->is_type( 'variable' );
    ?>

    <main class="product-main">

      <!-- BREADCRUMB -->
      <div class="wrap product-crumb">
        <a href="<?php echo esc_url( $shop_url ); ?>">Marketplace</a>
        <?php if ( $cat_names ) : ?><span class="sep">/</span><span><?php echo esc_html( $cat_names ); ?></span><?php endif; ?>
        <?php if ( $brand_name && $brand_link && ! is_wp_error( $brand_link ) ) : ?><span class="sep">/</span><a href="<?php echo esc_url( $brand_link ); ?>"><?php echo esc_html( $brand_name ); ?></a><?php endif; ?>
      </div>

      <!-- PRODUCT -->
      <section class="sec product-sec">
        <div class="wrap">
          <div class="product-layout">

            <!-- GALLERY (sticky on desktop) -->
            <div class="product-gallery reveal">
              <div class="pg-main">
                <?php if ( has_post_thumbnail() ) : ?>
                  <?php the_post_thumbnail( 'large', array( 'alt' => the_title_attribute( 'echo=0' ), 'id' => 'pg-main-img' ) ); ?>
                <?php else : ?>
                  <img id="pg-main-img" src="<?php echo esc_url( sdn_product_placeholder_url() ); ?>" alt="<?php the_title_attribute(); ?>">
                <?php endif; ?>
                <span class="pg-flag">&#127482;&#127480; Ships from USA</span>
              </div>
              <?php if ( ! empty( $gallery_ids ) ) : ?>
                <div class="pg-thumbs">
                  <?php if ( has_post_thumbnail() ) : ?>
                    <button class="pg-thumb is-active" data-full="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></button>
                  <?php endif; ?>
                  <?php foreach ( $gallery_ids as $gid ) : ?>
                    <button class="pg-thumb" data-full="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'large' ) ); ?>"><?php echo wp_get_attachment_image( $gid, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?></button>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- INFO -->
            <div class="product-info reveal reveal-d1">
              <?php if ( $brand_name ) : ?>
                <a class="p-brand" <?php echo ( $brand_link && ! is_wp_error( $brand_link ) ) ? 'href="' . esc_url( $brand_link ) . '"' : ''; ?>><?php echo esc_html( $brand_name ); ?> &rarr;</a>
              <?php endif; ?>
              <h1 class="entry-title"><?php the_title(); ?></h1>

              <?php if ( $product->get_price_html() ) : ?>
                <div class="p-price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>
              <?php endif; ?>

              <?php $short = $product->get_short_description() ?: wp_trim_words( wp_strip_all_tags( $product->get_description() ), 30 ); ?>
              <?php if ( $short ) : ?>
                <p class="p-desc"><?php echo wp_kses_post( $short ); ?></p>
              <?php endif; ?>

              <!-- Integrated cart form -->
              <div class="cart-row">
                  <?php woocommerce_template_single_add_to_cart(); ?>
              </div>

              <!-- Dropship + Wholesale CTAs -->
              <div class="action-row">
                <a href="<?php echo esc_url( $register ); ?>" class="btn btn-lime btn-lg btn-dropship">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M5 9v11h14V9"/></svg>
                  Dropship this product
                </a>
                <a href="<?php echo esc_url( $register ); ?>" class="btn btn-outline btn-lg btn-wholesale">Buy wholesale</a>
              </div>

              <!-- Trust badges -->
              <div class="trust-mini">
                <div class="tm-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><span>Auto inventory sync</span></div>
                <div class="tm-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z"/><polyline points="9 12 11 14 15 10"/></svg><span>Blind dropshipping</span></div>
                <div class="tm-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>No transaction fees</span></div>
              </div>

              <!-- Quick specs -->
              <dl class="spec-dl">
                <?php if ( $brand_name ) : ?><div><dt>Brand</dt><dd><?php echo esc_html( $brand_name ); ?></dd></div><?php endif; ?>
                <?php if ( $cat_names ) : ?><div><dt>Category</dt><dd><?php echo esc_html( $cat_names ); ?></dd></div><?php endif; ?>
                <?php if ( $sku ) : ?><div><dt>SKU</dt><dd><?php echo esc_html( $sku ); ?></dd></div><?php endif; ?>
                <?php if ( $weight ) : ?><div><dt>Weight</dt><dd><?php echo esc_html( $weight . ' ' . get_option( 'woocommerce_weight_unit' ) ); ?></dd></div><?php endif; ?>
              </dl>
            </div>

          </div>
        </div>
      </section>

      <!-- TABS: Description / Specs / Reviews -->
      <section class="sec product-tabs-sec">
        <div class="wrap">
          <div class="product-tabs reveal">
            <div class="ptab-head" role="tablist">
              <button class="ptab-btn is-active" data-tab="desc" role="tab">Description</button>
              <button class="ptab-btn" data-tab="specs" role="tab">Specs</button>
              <button class="ptab-btn" data-tab="reviews" role="tab">Reviews</button>
            </div>
            <div class="ptab-body">
              <div class="ptab-pane is-active" data-pane="desc">
                <?php
                $long = $product->get_description();
                if ( $long ) {
                    echo wp_kses_post( wpautop( $long ) );
                } else {
                    echo '<p>' . esc_html( $short ?: 'No description available.' ) . '</p>';
                }
                ?>
              </div>
              <div class="ptab-pane" data-pane="specs">
                <table class="spec-table">
                  <?php if ( $brand_name ) : ?><tr><td>Brand</td><td><?php echo esc_html( $brand_name ); ?></td></tr><?php endif; ?>
                  <?php if ( $cat_names ) : ?><tr><td>Category</td><td><?php echo esc_html( $cat_names ); ?></td></tr><?php endif; ?>
                  <?php if ( $sku ) : ?><tr><td>SKU</td><td><?php echo esc_html( $sku ); ?></td></tr><?php endif; ?>
                  <?php if ( $weight ) : ?><tr><td>Weight</td><td><?php echo esc_html( $weight . ' ' . get_option( 'woocommerce_weight_unit' ) ); ?></td></tr><?php endif; ?>
                  <?php if ( ! empty( $dimensions ) ) : ?><tr><td>Dimensions</td><td><?php echo esc_html( implode( ' × ', $dimensions ) . ' ' . get_option( 'woocommerce_dimension_unit' ) ); ?></td></tr><?php endif; ?>
                </table>
              </div>
              <div class="ptab-pane" data-pane="reviews">
                <?php
                if ( comments_open() || get_comments_number() ) {
                    comments_template();
                } else {
                    echo '<p>No reviews yet. Be the first to review this product.</p>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php
      // Related products — same brand, fallback to same category, fallback to recent.
      $related_args = array( 'status' => 'publish', 'limit' => 4, 'orderby' => 'rand', 'exclude' => array( get_the_ID() ) );
      if ( $brand_terms && ! is_wp_error( $brand_terms ) ) {
          $related_args['product_brand'] = $brand_terms[0]->slug;
      } elseif ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
          $related_args['category'] = array( $cat_terms[0]->slug );
      }
      $related = function_exists( 'wc_get_products' ) ? wc_get_products( $related_args ) : array();
      if ( ! empty( $related ) ) :
          ?>
          <section class="sec product-related-sec" style="background:var(--bg-2);">
            <div class="wrap">
              <h2 class="h-sec reveal" style="font-size:clamp(1.6rem,3vw,2.4rem);margin-bottom:32px;"><?php echo $brand_name ? 'More from ' . esc_html( $brand_name ) : 'Related products'; ?></h2>
              <div class="market">
                <?php
                $d = array( '', ' reveal-d1', ' reveal-d2', ' reveal-d3' );
                foreach ( $related as $i => $rel ) {
                    $rb = get_the_terms( $rel->get_id(), 'product_brand' );
                    $rbname = ( $rb && ! is_wp_error( $rb ) ) ? $rb[0]->name : '';
                    ?>
                    <a href="<?php echo esc_url( $rel->get_permalink() ); ?>" class="market-card reveal<?php echo esc_attr( $d[ $i % 4 ] ); ?>" style="text-decoration:none;color:inherit;">
                      <div class="mimg">
                        <?php $img = wp_get_attachment_image_url( $rel->get_image_id(), 'woocommerce_thumbnail' ) ?: sdn_product_placeholder_url(); ?>
                        <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $rel->get_name() ); ?>" loading="lazy">
                      </div>
                      <div class="mbody">
                        <?php if ( $rbname ) : ?><span class="mbrand"><?php echo esc_html( $rbname ); ?></span><?php endif; ?>
                        <h4><?php echo esc_html( $rel->get_name() ); ?></h4>
                        <?php if ( $rel->get_price_html() ) : ?><span class="mprice-text"><?php echo $rel->get_price_html(); // phpcs:ignore ?></span><?php endif; ?>
                      </div>
                    </a>
                    <?php
                }
                ?>
              </div>
            </div>
          </section>
          <?php
      endif;
      ?>

      <!-- SELL CTA -->
      <?php sdn_cta(); ?>

    </main>

    <script>
    // Product tabs (gallery swap + lightbox live in assets/js/main.js).
    (function(){
      // Tabs.
      var btns = document.querySelectorAll('.ptab-btn');
      var panes = document.querySelectorAll('.ptab-pane');
      btns.forEach(function(b){
        b.addEventListener('click', function(){
          var tab = b.getAttribute('data-tab');
          btns.forEach(function(x){ x.classList.remove('is-active'); });
          panes.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-pane') === tab); });
          b.classList.add('is-active');
        });
      });
    })();
    </script>

    <?php
endwhile;

get_footer();
