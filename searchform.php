<?php
/**
 * Custom search form — used by get_search_form() in the Knowledge Base,
 * Help Center, and search results page. Styled to match the noir design
 * system (dark input, green submit, magnifier icon).
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<form role="search" method="get" class="sdn-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="screen-reader-text" for="sdn-s"><?php esc_html_e( 'Search the Knowledge Base', 'productpro' ); ?></label>
    <span class="sdn-search-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    </span>
    <input type="search" id="sdn-s" class="sdn-search-input" placeholder="<?php echo esc_attr( 'Search articles&hellip;' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
    <input type="hidden" name="post_type" value="post" />
    <button type="submit" class="sdn-search-btn"><?php esc_html_e( 'Search', 'productpro' ); ?></button>
</form>
