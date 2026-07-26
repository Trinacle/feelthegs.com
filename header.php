<?php
/**
 * The header for Feel The G's — a retail-shop header.
 *
 * Shop-centric nav: primary menu + a categories mega menu (built from the live
 * WC product categories), product search, cart icon, and the mobile drawer.
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
// Top-level product categories for the mega menu + mobile nav.
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

// Featured / trending category block (high product count).
$ftgs_trending = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 6,
    'orderby'    => 'count',
    'order'      => 'DESC',
) );
if ( is_wp_error( $ftgs_trending ) ) {
    $ftgs_trending = array();
}
?>

<header class="site">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" data-cursor>
        <?php echo ftgs_logo( 34 ); // phpcs:ignore ?>
    </a>

    <nav class="nav-row" aria-label="Primary">
        <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( $ftgs_shop_url ); ?>">Shop</a></div>

        <div class="nav-item has-mega" data-menu="categories">
            <a class="nav-link" href="<?php echo esc_url( $ftgs_shop_url ); ?>">Categories <?php echo ftgs_chevron(); // phpcs:ignore ?></a>
        </div>

        <?php
        // Trending top-3 quick links (one each) for discoverability.
        $ftgs_quick = array_slice( $ftgs_trending, 0, 3 );
        foreach ( $ftgs_quick as $ftgs_qc ) :
            ?>
            <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( get_term_link( $ftgs_qc ) ); ?>"><?php echo esc_html( $ftgs_qc->name ); ?></a></div>
        <?php endforeach; ?>

        <div class="nav-item"><a class="nav-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog' ) ); ?>">Blog</a></div>
    </nav>

    <div class="nav-cta">
        <button class="header-search-btn" aria-label="Search products" id="header-search-trigger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>

        <?php if ( function_exists( 'ftgs_cart_link' ) ) : ?>
            <?php ftgs_cart_link(); ?>
        <?php endif; ?>

        <button class="menu-trigger" aria-label="Menu" aria-expanded="false"><span></span></button>
    </div>
</header>

<!-- HEADER SEARCH OVERLAY (product search) -->
<div class="header-search-overlay" id="header-search-overlay" hidden>
    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-search-form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" name="s" placeholder="Search products…" autocomplete="off" autofocus>
        <input type="hidden" name="post_type" value="product">
        <button type="button" class="header-search-close" aria-label="Close search">&times;</button>
    </form>
</div>

<!-- CATEGORIES MEGA PANEL -->
<div class="mega">
    <div class="mega-inner">
        <div class="mega-panels">
            <div class="mega-panel" data-panel="categories">
                <div class="mega-grid">
                    <div class="mega-col-head">Shop by Category</div>
                    <?php
                    if ( ! empty( $ftgs_top_cats ) ) :
                        foreach ( $ftgs_top_cats as $ftgs_cat ) :
                            printf(
                                '<a class="mega-link" href="%1$s"><strong>%2$s</strong><span>%3$s products</span></a>',
                                esc_url( get_term_link( $ftgs_cat ) ),
                                esc_html( $ftgs_cat->name ),
                                (int) $ftgs_cat->count
                            );
                        endforeach;
                    endif;
                    ?>
                    <a class="mega-link" href="<?php echo esc_url( $ftgs_shop_url ); ?>"><strong>All Products</strong><span>Browse the full catalog</span></a>

                    <div class="mega-col-head">Trending</div>
                    <?php
                    foreach ( $ftgs_trending as $ftgs_tc ) :
                        printf(
                            '<a class="mega-link" href="%1$s"><strong>%2$s</strong><span>%3$s products</span></a>',
                            esc_url( get_term_link( $ftgs_tc ) ),
                            esc_html( $ftgs_tc->name ),
                            (int) $ftgs_tc->count
                        );
                    endforeach;
                    ?>
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
