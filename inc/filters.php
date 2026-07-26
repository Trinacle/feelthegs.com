<?php
/**
 * Collection filter for Feel The G's — a faithful WooCommerce recreation of the
 * Fantasies Boutique Shopify filter system.
 *
 * Fantasies Boutique filters via Shopify tag-intersection
 * (/collections/{handle}/{tag1}+{tag2}) gated per-collection by metafields, with
 * a custom dual-thumb price slider. The checkout-cartesian "OR within a group /
 * AND across groups" merge is done client-side via N parallel AJAX calls.
 *
 * WooCommerce gives us a cleaner primitive: product ATTRIBUTES used as facets,
 * queried via ?filter_{slug}=term1,term2 query vars. WC's layered nav already
 * implements "OR within a taxonomy, AND across taxonomies" server-side — so we
 * get the same semantics with crawlable URLs, correct counts, no client-side
 * fan-out, and no duplicate-product risk.
 *
 * This file provides:
 *   - ftgs_filter_config( $term ) : per-category config reader (with defaults)
 *   - Color swatch hex map (Fantasies palette)
 *   - The "FTGS Collection Filter" widget: price slider + attribute groups +
 *     on-sale toggle, all driven by the per-category ACF config.
 *   - Enqueue of the slider JS, only on shop / category / tag pages.
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
   1. Per-category config reader (with sensible defaults)
   ========================================================================== */

/**
 * Get the filter config for the current category (or the global default).
 *
 * @param WP_Term|null $term Product category term. Defaults to the currently
 *                            queried category, or null for the main shop.
 * @return array {
 *     @type bool   $enable        Show the custom filter sidebar at all.
 *     @type bool   $price_display Show the price slider.
 *     @type string $price_heading
 *     @type float  $price_min     0 = auto-detect.
 *     @type float  $price_max     0 = auto-detect.
 *     @type float  $price_step
 *     @type array  $attr_groups   {slug, heading, style, terms} per attribute.
 *     @type bool   $deals_display Show the on-sale filter.
 *     @type array  $related_cats  Related category menu links.
 * }
 */
function ftgs_filter_config( $term = null ) {
    if ( null === $term ) {
        $term = is_product_taxonomy() ? get_queried_object() : null;
    }

    $config = array(
        'enable'        => true,
        'price_display' => true,
        'price_heading' => __( 'Price', 'feelthegs' ),
        'price_min'     => 0,
        'price_max'     => 0,
        'price_step'    => 5,
        'attr_groups'   => array(),
        'deals_display' => false,
        'related_cats'  => array(),
    );

    if ( $term && function_exists( 'get_field' ) ) {
        if ( get_field( 'ftgs_filter_enable', $term ) ) {
            $config['enable']        = (bool) get_field( 'ftgs_filter_enable', $term );
            $config['price_display'] = (bool) get_field( 'ftgs_price_display', $term );
            $config['price_heading'] = (string) ( get_field( 'ftgs_price_heading', $term ) ?: $config['price_heading'] );
            $config['price_min']     = (float) ( get_field( 'ftgs_price_min', $term ) ?: 0 );
            $config['price_max']     = (float) ( get_field( 'ftgs_price_max', $term ) ?: 0 );
            $config['price_step']    = (float) ( get_field( 'ftgs_price_step', $term ) ?: 5 );
            $config['deals_display'] = (bool) get_field( 'ftgs_deals_display', $term );

            $groups = get_field( 'ftgs_attr_groups', $term );
            if ( is_array( $groups ) ) {
                foreach ( $groups as $g ) {
                    $config['attr_groups'][] = array(
                        'slug'    => isset( $g['slug'] ) ? sanitize_title( $g['slug'] ) : '',
                        'heading' => isset( $g['heading'] ) ? trim( $g['heading'] ) : '',
                        'style'   => isset( $g['style'] ) ? $g['style'] : 'checkbox',
                        'terms'   => isset( $g['terms'] ) ? array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $g['terms'] ) ) ) ) : array(),
                    );
                }
            }

            $related = get_field( 'ftgs_related_cats', $term );
            if ( is_array( $related ) ) {
                $config['related_cats'] = $related;
            }
        } else {
            // ftgs_filter_enable explicitly false — disable entirely.
            $config['enable'] = false;
        }
    }

    return $config;
}

/* ==========================================================================
   2. Color swatch map (Fantasies Boutique palette)
   Maps color term names to hex values, mirroring the 18 color_option blocks in
   Fantasies Boutique's collection.json. Term-name lookup is case-insensitive.
   ========================================================================== */

function ftgs_color_swatch_map() {
    return array(
        'black'      => '#1a1a1a',
        'white'      => '#f5f5f5',
        'red'        => '#e23744',
        'blue'       => '#2b6cb0',
        'navy'       => '#1a2b4a',
        'green'      => '#2f855a',
        'purple'     => '#7034fd',
        'pink'       => '#ec4899',
        'hot pink'   => '#ff4e8c',
        'flesh tan'  => '#e8b894',
        'flesh pink' => '#f1c5b8',
        'flesh brown'=> '#9c6b4a',
        'brown'      => '#6b4423',
        'orange'     => '#ed8936',
        'gold'       => '#fedb62',
        'silver'     => '#c0c0c0',
        'grey'       => '#8a8a8a',
        'gray'       => '#8a8a8a',
        'yellow'     => '#ecc94b',
        'rainbow'    => 'linear-gradient(90deg,#e23744,#ed8936,#ecc94b,#2f855a,#2b6cb0,#7034fd)',
        'clear'      => 'transparent',
    );
}

function ftgs_color_hex( $name ) {
    $map = ftgs_color_swatch_map();
    $key = strtolower( trim( $name ) );
    return isset( $map[ $key ] ) ? $map[ $key ] : null;
}

/* ==========================================================================
   3. Register the filter widgets
   ========================================================================== */

add_action( 'widgets_init', 'ftgs_register_widgets' );
function ftgs_register_widgets() {
    register_widget( 'FTGS_Collection_Filter_Widget' );
}

/**
 * One widget that renders the entire filter sidebar (price + attribute groups +
 * on-sale) driven by the per-category config. Output is plain GET-link based so
 * it works without JS (progressive enhancement); the slider JS adds live UX.
 *
 * Filter URL strategy: WC's native query vars.
 *   - Price:      ?min_price=...&max_price=...
 *   - Attribute:  ?filter_{slug}=term-a,term-b   (WC layered nav: OR within)
 *   - On sale:    ?on_sale=1
 * AND across vars is automatic (WP merges tax queries).
 */
class FTGS_Collection_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'ftgs_collection_filter',
            "Feel The G's — Collection Filter",
            array( 'description' => 'Price slider + attribute filter groups, configured per product category.' )
        );
    }

    public function widget( $args, $instance ) {
        if ( ! ( is_shop() || is_product_category() || is_product_tag() ) ) {
            return;
        }
        $config = ftgs_filter_config();
        if ( empty( $config['enable'] ) ) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore

        $heading = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Filter', 'feelthegs' );
        echo '<h3 class="ftgs-filter-title">' . esc_html( $heading ) . '</h3>';

        echo '<div class="ftgs-filter">';

        // ---- Price slider ----
        if ( ! empty( $config['price_display'] ) ) {
            $this->render_price_slider( $config );
        }

        // ---- Attribute groups ----
        if ( ! empty( $config['attr_groups'] ) ) {
            foreach ( $config['attr_groups'] as $group ) {
                $this->render_attribute_group( $group );
            }
        }

        // ---- On sale ----
        if ( ! empty( $config['deals_display'] ) ) {
            $this->render_on_sale();
        }

        echo '<div class="ftgs-filter-actions">';
        echo '<button type="button" class="ftgs-filter-clear" data-ftgs-clear>' . esc_html__( 'Clear all', 'feelthegs' ) . '</button>';
        echo '</div>';

        echo '</div>'; // .ftgs-filter

        echo $args['after_widget']; // phpcs:ignore
    }

    /* ---------- Price slider ---------- */
    private function render_price_slider( $config ) {
        $bounds = ftgs_price_bounds( $config );
        $min    = $bounds['min'];
        $max    = $bounds['max'];
        $step   = max( 0.01, (float) $config['price_step'] );

        $cur_min = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : $min;
        $cur_max = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : $max;
        $cur_min = max( $min, min( $cur_min, $max ) );
        $cur_max = min( $max, max( $cur_max, $min ) );

        $heading = $config['price_heading'] ? $config['price_heading'] : __( 'Price', 'feelthegs' );
        echo '<div class="ftgs-filter-group" data-ftgs-group="price">';
        echo '<button type="button" class="ftgs-filter-head" aria-expanded="true">' . esc_html( $heading ) . '<span class="ftgs-arrow"></span></button>';
        echo '<div class="ftgs-filter-content">';
        echo '<div class="ftgs-price-values"><span data-ftgs-min-label>' . wp_kses_post( wc_price( $cur_min ) ) . '</span> &ndash; <span data-ftgs-max-label>' . wp_kses_post( ( $cur_max >= $max ? wc_price( $cur_max ) . '+' : wc_price( $cur_max ) ) ) . '</span></div>';
        echo '<div class="ftgs-range">';
        echo '<div class="ftgs-range-track"><div class="ftgs-range-progress" data-ftgs-progress></div></div>';
        printf( '<input type="range" min="%s" max="%s" step="%s" value="%s" data-ftgs-range="min" aria-label="%s">', esc_attr( $min ), esc_attr( $max ), esc_attr( $step ), esc_attr( $cur_min ), esc_attr__( 'Minimum price', 'feelthegs' ) );
        printf( '<input type="range" min="%s" max="%s" step="%s" value="%s" data-ftgs-range="max" aria-label="%s">', esc_attr( $min ), esc_attr( $max ), esc_attr( $step ), esc_attr( $cur_max ), esc_attr__( 'Maximum price', 'feelthegs' ) );
        echo '</div>'; // .ftgs-range
        echo '<button type="button" class="ftgs-price-go" data-ftgs-price-go>' . esc_html__( 'Go', 'feelthegs' ) . '</button>';
        echo '</div>'; // .ftgs-filter-content
        echo '</div>'; // .ftgs-filter-group
    }

    /* ---------- Attribute group (checkbox list or color swatch) ---------- */
    private function render_attribute_group( $group ) {
        $slug    = $group['slug'];
        $taxonomy = 'pa_' . $slug;
        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }
        $attr = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $slug ) );
        $name = $group['heading'] ? $group['heading'] : ( $attr ? $attr->name : ucfirst( $slug ) );

        // Get the terms that actually have products in the current context.
        $terms = ftgs_terms_for_current_query( $taxonomy, $group['terms'] );
        if ( empty( $terms ) ) {
            return;
        }

        $current = isset( $_GET[ 'filter_' . $slug ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $_GET[ 'filter_' . $slug ] ) ) ) ) : array();
        $is_color = ( 'swatch' === $group['style'] || in_array( strtolower( $slug ), array( 'color', 'colour' ), true ) );

        echo '<div class="ftgs-filter-group' . ( $is_color ? ' ftgs-color-group' : '' ) . '" data-ftgs-group="' . esc_attr( $slug ) . '">';
        echo '<button type="button" class="ftgs-filter-head" aria-expanded="true">' . esc_html( $name ) . '<span class="ftgs-arrow"></span></button>';
        echo '<div class="ftgs-filter-content">';

        if ( ! $is_color ) {
            echo '<input type="search" class="ftgs-filter-search" placeholder="' . esc_attr__( 'Search', 'feelthegs' ) . '" data-ftgs-search="' . esc_attr( $slug ) . '" aria-label="' . esc_attr( sprintf( __( 'Search %s', 'feelthegs' ), $name ) ) . '">';
        }

        echo '<ul class="ftgs-filter-list' . ( $is_color ? ' ftgs-swatches' : '' ) . '">';
        foreach ( $terms as $t ) {
            $checked = in_array( $t->slug, $current, true );
            $url     = ftgs_filter_toggle_url( 'filter_' . $slug, $t->slug, $current );
            echo '<li>';
            printf( '<a href="%s" class="ftgs-filter-opt%s" data-ftgs-opt="%s">', esc_url( $url ), $checked ? ' is-checked' : '', esc_attr( $t->slug ) );
            if ( $is_color ) {
                $hex = ftgs_color_hex( $t->name );
                $bg  = $hex ? ( '<span class="ftgs-swatch" style="background:' . esc_attr( $hex ) . ';"></span>' ) : '<span class="ftgs-swatch ftgs-swatch-empty"></span>';
                echo $bg; // phpcs:ignore — built from safe map
                echo '<span class="ftgs-swatch-label">' . esc_html( $t->name ) . '</span>';
            } else {
                echo '<span class="ftgs-check" aria-hidden="true"></span>';
                echo '<span class="ftgs-opt-label">' . esc_html( $t->name ) . '</span>';
                if ( ! empty( $t->count ) ) {
                    echo '<span class="ftgs-opt-count">' . esc_html( (int) $t->count ) . '</span>';
                }
            }
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>'; // .ftgs-filter-content
        echo '</div>'; // .ftgs-filter-group
    }

    /* ---------- On sale ---------- */
    private function render_on_sale() {
        $current = isset( $_GET['on_sale'] ) && '1' === $_GET['on_sale'];
        $url     = ftgs_filter_toggle_url( 'on_sale', '1', $current ? array( '1' ) : array() );
        echo '<div class="ftgs-filter-group" data-ftgs-group="sale">';
        echo '<div class="ftgs-filter-content">';
        printf( '<a href="%s" class="ftgs-filter-opt%s" data-ftgs-opt="sale"><span class="ftgs-check" aria-hidden="true"></span><span class="ftgs-opt-label">%s</span></a>', esc_url( $url ), $current ? ' is-checked' : '', esc_html__( 'On sale', 'feelthegs' ) );
        echo '</div>';
        echo '</div>';
    }
}

/* ==========================================================================
   4. URL builders + term lookups
   ========================================================================== */

/**
 * Auto-detect min/max price for the current category if the config leaves them 0.
 */
function ftgs_price_bounds( $config ) {
    global $wpdb;

    $min = (float) $config['price_min'];
    $max = (float) $config['price_max'];

    if ( $min > 0 && $max > 0 ) {
        return array( 'min' => $min, 'max' => $max );
    }

    // Auto-detect from the current query's products.
    $meta_query = array(
        array( 'key' => '_price', 'compare' => 'EXISTS' ),
        array( 'key' => '_stock_status', 'value' => 'outofstock', 'compare' => '!=' ),
    );
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => $meta_query,
        'orderby'        => 'meta_value_num',
        'meta_key'       => '_price',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    );
    if ( is_product_category() ) {
        $args['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => get_queried_object_id() ) );
    }
    $low = get_posts( $args );

    $args['order'] = 'DESC';
    $high = get_posts( $args );

    if ( $low ) { $p = wc_get_product( $low[0] ); if ( $p ) { $detected_min = (float) $p->get_price(); } }
    if ( $high ) { $p = wc_get_product( $high[0] ); if ( $p ) { $detected_max = (float) $p->get_price(); } }

    $detected_min = isset( $detected_min ) ? $detected_min : 0;
    $detected_max = isset( $detected_max ) ? $detected_max : 500;

    return array(
        'min' => $min > 0 ? $min : floor( $detected_min ),
        'max' => $max > 0 ? $max : ceil( $detected_max ),
    );
}

/**
 * Get terms of an attribute taxonomy that have products in the current query
 * context (shop or category). Optionally limited to an allowlist of slugs.
 */
function ftgs_terms_for_current_query( $taxonomy, $allowlist = array() ) {
    $term_args = array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    );
    if ( ! empty( $allowlist ) ) {
        $term_args['slug'] = $allowlist;
    }
    $terms = get_terms( $term_args );
    if ( is_wp_error( $terms ) ) {
        return array();
    }
    return is_array( $terms ) ? $terms : array();
}

/**
 * Build a URL that toggles a filter value on/off, preserving the other active
 * filters. This mirrors how WC layered-nav widgets build their links.
 *
 * @param string $key     Query var key (e.g. filter_color, on_sale, min_price).
 * @param string $value   The term slug to toggle.
 * @param array  $current Currently selected values for this key.
 * @return string
 */
function ftgs_filter_toggle_url( $key, $value, $current = array() ) {
    $current = array_map( 'strval', (array) $current );

    if ( false !== array_search( $value, $current, true ) ) {
        // Currently selected — remove it.
        $new = array_diff( $current, array( $value ) );
    } else {
        // Add (OR within the group).
        $new = array_merge( $current, array( $value ) );
    }

    $qs = $_GET; // phpcs:ignore — re-sanitized below
    unset( $qs['paged'] ); // reset to page 1 on filter change

    if ( in_array( $key, array( 'min_price', 'max_price', 'on_sale' ), true ) ) {
        if ( empty( $new ) ) {
            unset( $qs[ $key ] );
        } else {
            $qs[ $key ] = $value;
        }
    } else {
        if ( empty( $new ) ) {
            unset( $qs[ $key ] );
        } else {
            $qs[ $key ] = implode( ',', $new );
        }
    }

    return add_query_arg( array_map( 'sanitize_text_field', $qs ), ftgs_current_base_url() );
}

/**
 * The current request's path (no query string), used as the base for filter URLs.
 */
function ftgs_current_base_url() {
    $uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $path = explode( '?', $uri, 2 );
    return home_url( $path[0] );
}

/* ==========================================================================
   5. Apply the on_sale + price filters to the main WC query
   ========================================================================== */

add_action( 'woocommerce_product_query', 'ftgs_apply_query_filters', 20, 1 );
function ftgs_apply_query_filters( $q ) {
    // On sale
    if ( is_main_query() && isset( $_GET['on_sale'] ) && '1' === $_GET['on_sale'] ) {
        $q->set( 'post__in', array_merge( array( 0 ), wc_get_product_ids_on_sale() ) );
    }
}

/* ==========================================================================
   6. Enqueue the slider JS (only on shop / category / tag)
   ========================================================================== */

add_action( 'wp_enqueue_scripts', 'ftgs_enqueue_filter_js', 30 );
function ftgs_enqueue_filter_js() {
    if ( ! ( is_shop() || is_product_category() || is_product_tag() ) ) {
        return;
    }
    wp_enqueue_script(
        'ftgs-filter',
        get_stylesheet_directory_uri() . '/assets/js/filter.js',
        array( 'ftgs-main' ),
        FTGS_VERSION,
        true
    );
}
