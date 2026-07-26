<?php
/**
 * The template for displaying the WooCommerce shop / product category / tag archive.
 *
 * Retail shop layout: a filter sidebar (FTGS Collection Filter widget) on the
 * left, the native WC product grid on the right. No login wall — Feel The G's
 * is a direct-to-consumer store.
 *
 * The grid uses WC's native loop (woocommerce_product_loop()) so the native sort
 * dropdown, filter counts, and pagination all stay correct. The filter sidebar
 * is rendered via the FTGS_Collection_Filter_Widget (works with zero Widgets
 * setup; Appearance → Widgets can override the title).
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header( 'shop' );

/**
 * woocommerce_before_main_content hook.
 */
do_action( 'woocommerce_before_main_content' );
?>

<section class="sec ftgs-shop-sec">
  <div class="wrap">

    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
      <header class="ftgs-shop-header reveal">
        <h1 class="display"><?php woocommerce_page_title(); ?></h1>
        <?php
        // Category description (set in Products → Categories).
        if ( is_product_category() ) {
            $term = get_queried_object();
            if ( $term && $term->description ) {
                echo '<div class="lede ftgs-shop-desc">' . wp_kses_post( wpautop( $term->description ) ) . '</div>';
            }
        }
        ?>
      </header>
    <?php endif; ?>

    <div class="ftgs-shop-layout">

      <?php
      // ---- Filter sidebar (desktop) ----
      // The widget itself decides whether to render based on per-category config.
      ?>
      <aside class="ftgs-shop-sidebar reveal" id="ftgs-shop-sidebar">
        <?php
        if ( is_active_sidebar( 'ftgs-shop-filter' ) ) {
            dynamic_sidebar( 'ftgs-shop-filter' );
        } else {
            // Render the filter widget directly so it works with zero setup.
            the_widget( 'FTGS_Collection_Filter_Widget', array( 'title' => __( 'Filter', 'feelthegs' ) ) );
        }
        ?>
      </aside>

      <div class="ftgs-shop-main">
        <div class="ftgs-shop-toolbar reveal">
          <button type="button" class="ftgs-mobile-filter-toggle" data-ftgs-open-filter aria-label="<?php esc_attr_e( 'Open filters', 'feelthegs' ); ?>">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
            <span><?php esc_html_e( 'Filter & Sort', 'feelthegs' ); ?></span>
          </button>

          <?php woocommerce_catalog_ordering(); ?>

          <?php
          // Active filter chips (clears one at a time).
          if ( function_exists( 'ftgs_active_filter_chips' ) ) {
              ftgs_active_filter_chips();
          }
          ?>

          <div class="ftgs-result-count">
            <?php woocommerce_result_count(); ?>
          </div>
        </div>

        <?php
        if ( woocommerce_product_loop() ) {
            woocommerce_product_loop_start();

            while ( have_posts() ) {
                the_post();
                global $product;
                $product = wc_get_product( get_the_ID() );
                if ( ! $product ) {
                    continue;
                }
                ftgs_render_shop_card( $product );
            }

            woocommerce_product_loop_end();
        } else {
            $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
            echo '<div class="ftgs-shop-empty">';
            echo '<h3>' . esc_html__( 'No products found', 'feelthegs' ) . '</h3>';
            echo '<p>' . esc_html__( 'Try adjusting your filters or browsing another category.', 'feelthegs' ) . '</p>';
            echo '<a href="' . esc_url( $shop_url ) . '" class="btn btn-lime">' . esc_html__( 'Clear filters', 'feelthegs' ) . '</a>';
            echo '</div>';
        }
        ?>

        <div class="ftgs-shop-pagination">
          <?php woocommerce_pagination(); ?>
        </div>
      </div>

    </div>
  </div>
</section>

<?php
/**
 * woocommerce_after_main_content hook.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
