<?php
/**
 * Enqueue all CSS and JS
 *
 * @package FeelTheGs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'ftgs_enqueue', 20 );
function ftgs_enqueue() {
    // Google Fonts: Inter + Inter Tight (with swap for non-blocking)
    wp_enqueue_style(
        'ftgs-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap',
        array(),
        null
    );

    // Main stylesheet (the full design system)
    wp_enqueue_style(
        'ftgs-styles',
        get_stylesheet_directory_uri() . '/assets/css/styles.css',
        array(),
        FTGS_VERSION
    );

    // Main JS (vanilla — mega menu, reveals, theme toggle, mobile drawer, search)
    wp_enqueue_script(
        'ftgs-main',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array(),
        FTGS_VERSION,
        true // load in footer
    );

    // Expose AJAX URL + nonce for the newsletter form and filter interactions.
    wp_add_inline_script(
        'ftgs-main',
        'window.ftgsData = window.ftgsData || {}; window.ftgsData.ajaxUrl = ' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';',
        'before'
    );
}

/* ---------- Preconnect to third-party origins for faster font loads ---------- */
add_action( 'wp_head', 'ftgs_preconnect', 1 );
function ftgs_preconnect() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}

/* ---------- Add <html> class for the saved theme (prevents dark-mode flash) ---------- */
add_filter( 'language_attributes', 'ftgs_html_attrs' );
function ftgs_html_attrs( $output ) {
    // The theme toggle JS adds/removes .light on <html>. Preload the saved theme
    // to avoid a flash of dark when the user prefers light.
    $saved = isset( $_COOKIE['sd-theme'] ) ? $_COOKIE['sd-theme'] : ''; // keep cookie name for cross-theme continuity
    if ( 'light' === $saved ) {
        $output .= ' class="light"';
    }
    return $output;
}
