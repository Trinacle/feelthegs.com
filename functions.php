<?php
/**
 * Feel The G's — child theme functions
 *
 * Adult-boutique WooCommerce child theme of Astra. Dark editorial design system
 * (purple + gold), mega menu, light/dark toggle, and a Fantasies-Boutique-style
 * collection filter rebuilt natively for WooCommerce layered nav.
 *
 * Architecture inherited from the SmokeDrop Noir / ProductPro lineage:
 *  - WC template overrides in /woocommerce/ pass through template_include and
 *    are flagged "bespoke", which triggers the page-builder asset stripper.
 *  - WC's native loop drives the grid so sort / pagination / filter counts stay
 *    honest.
 *  - Yoast SEO is filtered (never duplicated) for meta title / description / OG.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FTGS_VERSION', '1.1.0' );
define( 'FTGS_DIR', get_stylesheet_directory() );
define( 'FTGS_URI', get_stylesheet_directory_uri() );

/* ---------- Include modular files ---------- */
require_once FTGS_DIR . '/inc/enqueue.php';
require_once FTGS_DIR . '/inc/acf-fields.php';
require_once FTGS_DIR . '/inc/helpers.php';
require_once FTGS_DIR . '/inc/filters.php';       // collection filter config + widgets

/* ---------- Shop filter widget area (archive-product.php renders into it) ---------- */
add_action( 'widgets_init', 'ftgs_register_shop_sidebar' );
function ftgs_register_shop_sidebar() {
    register_sidebar( array(
        'name'          => __( 'Shop Filter Sidebar', 'feelthegs' ),
        'id'            => 'ftgs-shop-filter',
        'description'   => __( 'The collection filter on shop / category pages. Defaults to the FTGS Collection Filter widget if empty.', 'feelthegs' ),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="ftgs-filter-title">',
        'after_title'   => '</h3>',
    ) );
}

/* ---------- On theme activation: seed sensible filter defaults on all categories ----------
 * Runs once (gated by an option) when the theme goes active. Sets per-category
 * filter config via ACF term meta: price slider on, plus the attribute groups
 * that make sense store-wide. Each category is then individually tunable in
 * Products → Categories → "Collection Filter Config".
 */
add_action( 'after_switch_theme', 'ftgs_seed_filter_defaults' );
function ftgs_seed_filter_defaults() {
    if ( get_option( 'ftgs_filter_seeded' ) ) {
        return;
    }
    if ( ! function_exists( 'get_terms' ) || ! function_exists( 'update_field' ) ) {
        return; // no ACF yet — admin can re-run by deleting the option
    }

    // Default attribute groups (slug, heading, style) shown on every category.
    $default_groups = array(
        array( 'slug' => 'color',         'heading' => 'Color',          'style' => 'swatch',   'terms' => '' ),
        array( 'slug' => 'size',          'heading' => 'Size',           'style' => 'checkbox', 'terms' => '' ),
        array( 'slug' => 'material',      'heading' => 'Material',       'style' => 'checkbox', 'terms' => '' ),
        array( 'slug' => 'brand',         'heading' => 'Brand',          'style' => 'checkbox', 'terms' => '' ),
        array( 'slug' => 'features',      'heading' => 'Features',       'style' => 'checkbox', 'terms' => '' ),
        array( 'slug' => 'product-type',  'heading' => 'Type',           'style' => 'checkbox', 'terms' => '' ),
    );

    $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 0 ) );
    if ( is_wp_error( $cats ) ) {
        return;
    }
    foreach ( $cats as $cat ) {
        update_field( 'ftgs_filter_enable', 1, $cat );
        update_field( 'ftgs_price_display', 1, $cat );
        update_field( 'ftgs_price_min', 0, $cat );   // 0 = auto-detect
        update_field( 'ftgs_price_max', 0, $cat );   // 0 = auto-detect
        update_field( 'ftgs_price_step', 5, $cat );
        update_field( 'ftgs_deals_display', 1, $cat );
        // Build the repeater rows in ACF's expected format.
        $rows = array();
        foreach ( $default_groups as $g ) {
            // Only include attribute groups whose taxonomy actually exists.
            if ( taxonomy_exists( 'pa_' . $g['slug'] ) ) {
                $rows[] = $g;
            }
        }
        update_field( 'ftgs_attr_groups', $rows, $cat );
    }
    update_option( 'ftgs_filter_seeded', 1 );
}

/* ---------- Newsletter subscribers CPT (fallback storage) ---------- */
add_action( 'init', 'ftgs_register_sub_cpt' );
function ftgs_register_sub_cpt() {
    register_post_type( 'ftgs_sub', array(
        'labels'       => array( 'name' => 'Newsletter Subs', 'singular_name' => 'Subscriber' ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => array( 'title' ),
    ) );
}

/* ---------- Theme setup ---------- */
add_action( 'after_setup_theme', 'ftgs_theme_setup' );
function ftgs_theme_setup() {
    // WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // Core WordPress features
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    // Image sizes
    add_image_size( 'ftgs-category-hero', 1600, 500, true );
    add_image_size( 'ftgs-blog-thumb', 800, 500, true );

    // Register menu locations
    register_nav_menus( array(
        'primary'      => __( 'Primary Menu', 'feelthegs' ),
        'shop-cats'    => __( 'Shop Categories (mega menu)', 'feelthegs' ),
        'mobile'       => __( 'Mobile Menu', 'feelthegs' ),
        'footer_shop'  => __( 'Footer: Shop', 'feelthegs' ),
        'footer_help'  => __( 'Footer: Help', 'feelthegs' ),
        'footer_about' => __( 'Footer: About', 'feelthegs' ),
    ) );
}

/* ---------- Redirect dead /product-category/* URLs to /collections/* ----------
 * The live site serves real category pages at /collections/{slug}/ (a Shopify-
 * migration rewrite). The WC-default /product-category/{slug}/ URLs fall through
 * to WP attachment matching, where Yoast's attachment_redirect then 301s them to
 * the matching .jpg attachment file (e.g. /product-category/dildos/ -> Dildos.jpg).
 * That's broken. Redirect /product-category/* to the correct /collections/* URL
 * with a 301 so SEO link equity flows to the right place.
 */
add_action( 'template_redirect', 'ftgs_redirect_product_category_to_collections', 1 );
function ftgs_redirect_product_category_to_collections() {
    if ( is_admin() ) return;
    $req = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    if ( preg_match( '#^/product-category/([a-z0-9\-]+)/?#i', $req, $m ) ) {
        $slug = $m[1];
        // Confirm the term exists before redirecting.
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            // Build the /collections/{slug}/ URL preserving query string (filters, paging).
            $qs = '';
            $qpos = strpos( $req, '?' );
            if ( false !== $qpos ) {
                $qs = substr( $req, $qpos );
            }
            $dest = home_url( '/collections/' . $slug . '/' ) . $qs;
            wp_safe_redirect( $dest, 301 );
            exit;
        }
    }
}

/* ---------- Remove Astra header/footer entirely (we render our own) ---------- */
add_action( 'wp_loaded', 'ftgs_kill_astra_hooks' );
function ftgs_kill_astra_hooks() {
    remove_action( 'astra_header', 'astra_header_markup' );
    remove_action( 'astra_header', 'astra_primary_header_markup' );
    remove_action( 'astra_footer', 'astra_footer_markup' );
    remove_action( 'astra_content_top', 'astra_content_top' );
    remove_action( 'astra_content_bottom', 'astra_content_bottom' );
}

/* ---------- Flag our bespoke templates (render 100% custom markup) ----------
 * These templates use ZERO Astra widgets / Elementor widgets. On these views we
 * strip all page-builder CSS/JS so styles.css is the sole authority.
 */
add_filter( 'template_include', 'ftgs_flag_bespoke_template', 999 );
function ftgs_flag_bespoke_template( $template ) {
    $bespoke = array(
        'front-page.php', 'home.php', 'category.php', 'single.php',
        'search.php', '404.php',
        // WooCommerce overrides live in /woocommerce/ but resolve to these
        // basenames and DO pass through template_include, so flag them bespoke.
        'single-product.php', 'archive-product.php',
    );
    // Any bespoke page template (page-{slug}.php) is also flagged.
    $b = basename( $template );
    if ( in_array( $b, $bespoke, true ) || preg_match( '/^page-/', $b ) ) {
        $GLOBALS['ftgs_bespoke'] = true;
    }
    return $template;
}

function ftgs_is_bespoke_view() {
    return is_front_page() || ! empty( $GLOBALS['ftgs_bespoke'] );
}

/* ---------- Strip Astra-addon + Elementor + ElementsKit on our views ---------- */
add_action( 'wp_enqueue_scripts', 'ftgs_strip_builder_assets', 100 );
function ftgs_strip_builder_assets() {
    if ( ! ftgs_is_bespoke_view() ) {
        return; // leave real Elementor-built pages untouched
    }

    $kill = array( 'astra', 'elementor', 'e-animation', 'ekit', 'elementskit', 'uael', 'ultimate-elementor', 'widget-' );

    // Never touch our own assets, the admin bar, core icon fonts, or the
    // WooCommerce runtime (needed for cart / filter AJAX).
    // Modern Cart Starter (moderncart-) drives the slide-out cart drawer —
    // stripping it breaks add-to-cart. CartFlows + FiboSearch also run cart
    // logic and must be preserved.
    $keep = array(
        'ftgs-', 'sdn-', 'admin-bar', 'dashicons',
        'wc-', 'woocommerce', 'jquery', 'js-cookie', 'sourcebuster',
        'selectWoo', 'select-woo',
        'moderncart-', 'cartflows', 'fibosearch', 'dgwt-wcasa',  // cart + search plugins
    );

    foreach ( array( 'styles', 'scripts' ) as $type ) {
        $dep = 'styles' === $type ? wp_styles() : wp_scripts();
        if ( ! ( $dep instanceof WP_Dependencies ) ) {
            continue;
        }
        foreach ( array_keys( $dep->registered ) as $handle ) {
            $h = strtolower( $handle );

            $protected = false;
            foreach ( $keep as $k ) {
                if ( 0 === strpos( $h, $k ) ) { $protected = true; break; }
            }
            if ( $protected ) {
                continue;
            }

            foreach ( $kill as $x ) {
                if ( false !== strpos( $h, $x ) ) {
                    if ( 'styles' === $type ) {
                        wp_dequeue_style( $handle );
                    } else {
                        wp_dequeue_script( $handle );
                    }
                    break;
                }
            }
        }
    }
}

/* ---------- Drop leftover Astra/Elementor body classes on bespoke views ---------- */
add_filter( 'body_class', 'ftgs_clean_body_classes', 20 );
function ftgs_clean_body_classes( $classes ) {
    if ( ! ftgs_is_bespoke_view() ) {
        return $classes;
    }
    return array_filter( $classes, function ( $c ) {
        return false === strpos( $c, 'elementor' )
            && false === strpos( $c, 'ast-theme-transparent-header' )
            && false === strpos( $c, 'ast-hfb-header' );
    } );
}

/* ---------- Performance: only load WooCommerce CSS/JS on shop pages ---------- */
add_action( 'wp_enqueue_scripts', 'ftgs_strip_wc_on_nonshop', 99 );
function ftgs_strip_wc_on_nonshop() {
    if ( ftgs_is_bespoke_view() && ! is_woocommerce() && ! is_shop() && ! is_product() && ! is_product_taxonomy() ) {
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'wc-order-attribution' );
        wp_dequeue_script( 'sourcebuster' );
        wp_dequeue_script( 'js-cookie' );
    }
}

/* ---------- Default product sort to 'latest' on the shop ---------- */
add_filter( 'woocommerce_default_catalog_orderby_options', 'ftgs_default_orderby_latest' );
add_filter( 'woocommerce_default_catalog_orderby', 'ftgs_default_orderby_latest' );
function ftgs_default_orderby_latest() {
    return 'date';
}

/* ---------- Hide out-of-stock products from the shop ----------
 * Keeps pagination / result counts honest. Skipped during AJAX (Modern Cart's
 * recommendation queries run on admin-ajax and shouldn't be filtered).
 */
add_action( 'woocommerce_product_query', 'ftgs_hide_out_of_stock' );
function ftgs_hide_out_of_stock( $q ) {
    if ( wp_doing_ajax() ) {
        return;
    }
    $meta_query = $q->get( 'meta_query' );
    $meta_query[] = array(
        'key'     => '_stock_status',
        'value'   => 'outofstock',
        'compare' => '!=',
    );
    $q->set( 'meta_query', $meta_query );
}

/* ---------- Hide products without a featured image on the shop ----------
 * Critical for the broken-image goal: products whose thumbnail can't be
 * generated (missing source / orphaned media) are excluded at the query level,
 * so a customer never sees a broken-image card in the grid.
 */
add_action( 'woocommerce_product_query', 'ftgs_hide_no_image_products' );
function ftgs_hide_no_image_products( $q ) {
    if ( wp_doing_ajax() ) {
        return;
    }
    $meta_query   = $q->get( 'meta_query' );
    $meta_query[] = array(
        'key'     => '_thumbnail_id',
        'compare' => 'EXISTS',
    );
    $q->set( 'meta_query', $meta_query );
}

/* ---------- Hide SKU on the front-end (kept in admin for inventory) ---------- */
add_filter( 'wc_product_sku_enabled', '__return_false' );

/* ---------- Newsletter signup endpoint ---------- */
add_action( 'wp_ajax_ftgs_newsletter_submit', 'ftgs_newsletter_submit' );
add_action( 'wp_ajax_nopriv_ftgs_newsletter_submit', 'ftgs_newsletter_submit' );
function ftgs_newsletter_submit() {
    $nonce = isset( $_POST['ftgs_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ftgs_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'ftgs_newsletter' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page.' ) );
    }

    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
    }

    $existing = get_page_by_title( $email, OBJECT, 'ftgs_sub' );
    if ( ! $existing ) {
        wp_insert_post( array(
            'post_type'    => 'ftgs_sub',
            'post_title'   => $email,
            'post_status'  => 'publish',
            'meta_input'   => array(
                'ftgs_date'   => current_time( 'mysql' ),
                'ftgs_source' => 'footer',
            ),
        ) );
    }

    wp_send_json_success( array( 'message' => "You're subscribed! Check your inbox to confirm." ) );
}

add_action( 'wp_ajax_ftgs_news_nonce', 'ftgs_news_nonce' );
add_action( 'wp_ajax_nopriv_ftgs_news_nonce', 'ftgs_news_nonce' );
function ftgs_news_nonce() {
    wp_send_json_success( wp_create_nonce( 'ftgs_newsletter' ) );
}

/* ---------- SEO: Yoast integration (filter, never duplicate) ----------
 * Yoast SEO is active. We filter it to enforce correct values where the
 * DB-configured defaults are missing, and add schema Yoast doesn't generate.
 */

// Default social share image fallback (set per-site once a brand OG image exists).
if ( ! defined( 'FTGS_OG_IMAGE' ) ) {
    define( 'FTGS_OG_IMAGE', '' );
}

add_filter( 'wpseo_opengraph_image', 'ftgs_yoast_og_image' );
add_filter( 'wpseo_twitter_image', 'ftgs_yoast_og_image' );
function ftgs_yoast_og_image( $img ) {
    if ( $img || ! FTGS_OG_IMAGE ) {
        return $img;
    }
    return home_url( FTGS_OG_IMAGE );
}

/* Homepage meta title + description — clean, keyword-rich. */
add_filter( 'wpseo_title', 'ftgs_yoast_title', 10, 2 );
function ftgs_yoast_title( $title ) {
    if ( is_front_page() ) {
        return "Feel The G's — Adult Sex Toys, Lingerie & Bondage | Strap Yourself In";
    }
    return $title;
}

add_filter( 'wpseo_metadesc', 'ftgs_yoast_metadesc' );
function ftgs_yoast_metadesc( $desc ) {
    if ( $desc && strlen( $desc ) <= 160 ) {
        return $desc;
    }
    if ( is_front_page() ) {
        return "Shop Feel The G's for premium sex toys, lingerie, bondage gear, lotions & more. Discreet shipping on fashion, jewelry, costumes, restraints and intimate wellness.";
    }
    return $desc;
}

add_filter( 'wpseo_opengraph_title', 'ftgs_yoast_homepage_og_title' );
function ftgs_yoast_homepage_og_title( $title ) {
    if ( is_front_page() ) {
        return "Feel The G's — Adult Sex Toys, Lingerie & Bondage";
    }
    return $title;
}

add_filter( 'wpseo_opengraph_desc', 'ftgs_yoast_homepage_og_desc' );
function ftgs_yoast_homepage_og_desc( $desc ) {
    if ( is_front_page() ) {
        return "Premium sex toys, lingerie, bondage gear & intimate wellness. Discreet shipping. Strap yourself in.";
    }
    return $desc;
}

/* ---------- Enhanced JSON-LD schema (complements Yoast's graph) ----------
 * NOTE: 'Schema & Structured Data for WP' plugin is active on feelthegs and
 * handles Organization / Product / Breadcrumb schema. To avoid duplicate or
 * conflicting schema, the theme yields to the plugin when it's active and only
 * outputs its own minimal Organization + WebSite schema otherwise.
 */
add_action( 'wp_head', 'ftgs_schema', 20 );
function ftgs_schema() {
    if ( is_admin() ) {
        return;
    }
    // Yield to the dedicated schema plugin if active.
    if ( defined( 'SASWP_VERSION' ) || class_exists( 'schema_for_wp' ) ) {
        return;
    }
    $home = home_url( '/' );

    $org = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Store',
        '@id'      => $home . '#organization',
        'name'     => "Feel The G's",
        'url'      => $home,
        'description' => "Adult boutique — sex toys, lingerie, bondage, lotions & intimate wellness.",
    );
    if ( FTGS_OG_IMAGE ) {
        $org['image'] = home_url( FTGS_OG_IMAGE );
    }
    echo '<script type="application/ld+json">' . wp_json_encode( $org ) . '</script>' . "\n";

    if ( is_front_page() ) {
        $site = array(
            '@context'       => 'https://schema.org',
            '@type'          => 'WebSite',
            '@id'            => $home . '#website',
            'url'            => $home,
            'name'           => "Feel The G's",
            'publisher'      => array( '@id' => $home . '#organization' ),
            'potentialAction' => array(
                array(
                    '@type'       => 'SearchAction',
                    'target'      => array(
                        '@type'       => 'EntryPoint',
                        'urlTemplate' => home_url( '/?s={search_term_string}&post_type=product' ),
                    ),
                    'query-input' => 'required name=search_term_string',
                ),
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $site ) . '</script>' . "\n";
    }
}

/* ---------- Custom body classes ---------- */
add_filter( 'body_class', 'ftgs_body_classes' );
function ftgs_body_classes( $classes ) {
    $classes[] = 'feelthegs';
    return $classes;
}

/* ---------- WooCommerce: remove default wrappers, add our own ---------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'ftgs_woo_wrapper_start', 10 );
function ftgs_woo_wrapper_start() {
    echo '<main id="main" class="site-main"><div class="wrap">';
}

add_action( 'woocommerce_after_main_content', 'ftgs_woo_wrapper_end', 10 );
function ftgs_woo_wrapper_end() {
    echo '</div></main>';
}

/* ---------- Remove WooCommerce breadcrumbs (we have our own nav) ---------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/* ---------- Remove stock/availability meta from product pages ---------- */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 10 );

/* ---------- Helper: get the logo ----------
 * Falls back to the site title text if no custom logo is set via the Customizer.
 */
function ftgs_logo( $size = 34 ) {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $html = wp_get_attachment_image( $custom_logo_id, 'full', false, array(
            'alt'   => "Feel The G's",
            'style' => 'height:' . intval( $size ) . 'px;width:auto;',
        ) );
        if ( $html ) {
            return $html;
        }
    }
    $h = intval( $size );
    return '<span class="logo-text" style="font-family:var(--display);font-weight:700;font-size:' . max( 16, round( $h * 0.62 ) ) . 'px;color:var(--ink);letter-spacing:-.02em;">Feel&nbsp;The&nbsp;G&#039;s</span>';
}
