<?php
/**
 * The header for Feel The G's — a retail-shop header.
 *
 * Solid black bar. Shop-centric nav with one mega menu (Shop Categories) + a
 * few category quick-links. Account + search + cart icons on the right.
 *
 * @package FeelTheGs
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Screen loader -->
<div id="ftgs-loader" class="ftgs-loader">
    <div class="ftgs-loader-logo">
        <?php echo ftgs_logo( 36 ); // phpcs:ignore ?>
    </div>
</div>

<div class="mega-backdrop"></div>

<?php
// Top-level product categories for the mega menu + quick links + mobile nav.
$ftgs_shop_url = get_permalink( wc_get_page_id( 'shop' ) );
$ftgs_top_cats = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0,
    'number'     => 12,
    'orderby'    => 'count',
    'order'      => 'DESC',
) );
if ( is_wp_error( $ftgs_top_cats ) ) {
    $ftgs_top_cats = array();
}
// Quick-link categories (top 3 by count — shown directly in the nav).
$ftgs_quick = array_slice( $ftgs_top_cats, 0, 3 );
// Featured category for the mega menu image block (highest-count top-level cat
// that has a thumbnail or a first-product image).
$ftgs_feat_cat = ! empty( $ftgs_top_cats ) ? $ftgs_top_cats[0] : null;
?>

<header class="site">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" data-cursor>
        <?php echo ftgs_logo( 34 ); // phpcs:ignore ?>
    </a>

    <nav class="nav-row" aria-label="Primary">
        <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( $ftgs_shop_url ); ?>">Shop</a></div>

        <div class="nav-item has-mega" data-menu="categories">
            <a class="nav-link" href="<?php echo esc_url( $ftgs_shop_url ); ?>">Shop Categories <?php echo ftgs_chevron(); // phpcs:ignore ?></a>
        </div>

        <?php foreach ( $ftgs_quick as $ftgs_qc ) : ?>
            <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( get_term_link( $ftgs_qc ) ); ?>"><?php echo esc_html( $ftgs_qc->name ); ?></a></div>
        <?php endforeach; ?>

        <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog' ) ); ?>">Blog</a></div>
    </nav>

    <div class="nav-cta">
        <?php
        // FiboSearch AJAX product search (Solaris skin, purple submit). The plugin
        // is installed + configured on this site; rendering its shortcode here keeps
        // the instant-results UX you already have. Falls back to a search icon on
        // mobile via FiboSearch's own responsive behavior.
        if ( shortcode_exists( 'fibosearch' ) ) {
            echo '<div class="ftgs-header-search">' . do_shortcode( '[fibosearch]' ) . '</div>';
        } else {
            ?>
            <button class="header-icon-btn" aria-label="Search products" id="header-search-trigger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <?php
        }
        ?>

        <a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>" class="header-icon-btn" aria-label="My account">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>

        <?php if ( function_exists( 'ftgs_cart_link' ) ) : ?>
            <?php ftgs_cart_link(); ?>
        <?php endif; ?>

        <button class="menu-trigger" aria-label="Menu" aria-expanded="false"><span></span></button>
    </div>
</header>

<!-- SHOP CATEGORIES MEGA PANEL -->
<div class="mega">
    <div class="mega-inner">
        <div class="mega-panels">
            <div class="mega-panel" data-panel="categories">
                <div class="ftgs-mega-grid">
                    <!-- Left: category link list (no counts) -->
                    <div class="ftgs-mega-list">
                        <div class="ftgs-mega-head">Shop by Category</div>
                        <?php
                        if ( ! empty( $ftgs_top_cats ) ) :
                            foreach ( $ftgs_top_cats as $ftgs_cat ) :
                                printf(
                                    '<a class="ftgs-mega-link" href="%1$s">%2$s</a>',
                                    esc_url( get_term_link( $ftgs_cat ) ),
                                    esc_html( $ftgs_cat->name )
                                );
                            endforeach;
                        endif;
                        ?>
                        <a class="ftgs-mega-link ftgs-mega-all" href="<?php echo esc_url( $ftgs_shop_url ); ?>">All Products</a>
                    </div>

                    <!-- Right: featured category image block -->
                    <?php if ( $ftgs_feat_cat ) : ?>
                        <?php
                        $ftgs_feat_img = function_exists( 'ftgs_category_image' ) ? ftgs_category_image( $ftgs_feat_cat, 'ftgs-category-hero' ) : '';
                        $ftgs_feat_img = $ftgs_feat_img ? $ftgs_feat_img : 'https://images.unsplash.com/photo-1618160452730-2c2c2c2c2c2c?w=600&q=80';
                        ?>
                        <a class="ftgs-mega-feature" href="<?php echo esc_url( get_term_link( $ftgs_feat_cat ) ); ?>"<?php echo $ftgs_feat_img ? ' style="background-image:url(' . esc_url( $ftgs_feat_img ) . ')"' : ''; ?>>
                            <div class="ftgs-mega-feature-overlay"></div>
                            <div class="ftgs-mega-feature-body">
                                <span class="ftgs-mega-feature-eyebrow">Featured</span>
                                <span class="ftgs-mega-feature-title"><?php echo esc_html( $ftgs_feat_cat->name ); ?></span>
                                <span class="ftgs-mega-feature-cta">Shop now &rarr;</span>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MOBILE NAV -->
<div class="mobile-nav">
    <nav class="mn-links">
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>">Shop</a>
        <a href="<?php echo esc_url( $ftgs_shop_url ); ?>">All Products</a>
        <?php foreach ( array_slice( $ftgs_top_cats, 0, 10 ) as $ftgs_mc ) : ?>
            <a href="<?php echo esc_url( get_term_link( $ftgs_mc ) ); ?>"><?php echo esc_html( $ftgs_mc->name ); ?></a>
        <?php endforeach; ?>
        <a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>">My Account</a>
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog' ) ); ?>">Blog</a>
    </nav>
</div>

<?php
/**
 * Custom nav walker for direct (non-mega) nav items.
 */
class FTGS_Direct_Nav_Walker extends Walker_Nav_Menu {
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $output .= '<div class="nav-item"><a class="nav-link" href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></div>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = array() ) {}
}
