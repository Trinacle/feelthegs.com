<?php
/**
 * The footer for Feel The G's.
 *
 * @package FeelTheGs
 */

$ftgs_shop_url = get_permalink( wc_get_page_id( 'shop' ) );
// Top categories for the footer "Shop" column.
$ftgs_foot_cats = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 7,
    'orderby'    => 'count',
    'order'      => 'DESC',
) );
if ( is_wp_error( $ftgs_foot_cats ) ) {
    $ftgs_foot_cats = array();
}
?>

<footer class="footer site-footer">
    <div class="footer-inner">
        <div class="foot-newsletter">
            <div>
                <h3>Strap yourself in.</h3>
                <p>New arrivals, exclusive deals, and intimate inspiration — discreetly in your inbox. No spam.</p>
            </div>
            <div class="news-form-wrap">
                <form class="news-form ftgs-news" id="ftgs-news-form" novalidate>
                    <input type="email" name="email" id="ftgs-news-email" placeholder="Enter your email" required aria-label="Email address">
                    <button type="submit" id="ftgs-news-btn">Subscribe</button>
                    <?php wp_nonce_field( 'ftgs_newsletter', 'ftgs_nonce_field' ); ?>
                </form>
                <div id="ftgs-news-msg" class="ftgs-news-msg" role="status" aria-live="polite"></div>
            </div>
        </div>

        <div class="foot-grid">
            <div class="foot-brand">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand"><?php echo ftgs_logo( 42 ); // phpcs:ignore ?></a>
                <p>Premium sex toys, lingerie, bondage gear, and intimate wellness. Discreet shipping on every order.</p>
                <div class="foot-social">
                    <a href="https://www.facebook.com/feelthegs" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg></a>
                    <a href="https://x.com/feelthegs" aria-label="X" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.844l-5.36-7.01L4.16 22H.9l8.02-9.17L1.5 2h6.99l4.84 6.4L18.244 2z"/></svg></a>
                    <a href="https://www.instagram.com/feelthegs" aria-label="Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
                </div>
            </div>
            <div class="foot-col">
                <h5>Shop</h5>
                <a href="<?php echo esc_url( $ftgs_shop_url ); ?>">All Products</a>
                <?php foreach ( $ftgs_foot_cats as $ftgs_fc ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $ftgs_fc ) ); ?>"><?php echo esc_html( $ftgs_fc->name ); ?></a>
                <?php endforeach; ?>
            </div>
            <div class="foot-col">
                <h5>Customer Care</h5>
                <a href="<?php echo esc_url( home_url( '/shipping' ) ); ?>">Shipping &amp; Delivery</a>
                <a href="<?php echo esc_url( home_url( '/returns' ) ); ?>">Returns &amp; Exchanges</a>
                <a href="<?php echo esc_url( home_url( '/track-order' ) ); ?>">Track Your Order</a>
                <a href="<?php echo esc_url( home_url( '/faq' ) ); ?>">FAQ</a>
                <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact Us</a>
            </div>
            <div class="foot-col">
                <h5>About</h5>
                <a href="<?php echo esc_url( home_url( '/about-us' ) ); ?>">Our Story</a>
                <a href="<?php echo esc_url( home_url( '/shipping-policy' ) ); ?>">Shipping Policy</a>
                <a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>">Privacy Policy</a>
                <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>">Terms of Service</a>
                <a href="<?php echo esc_url( home_url( '/return-policy' ) ); ?>">Return Policy</a>
            </div>
            <div class="foot-col">
                <h5>Resources</h5>
                <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog' ) ); ?>">Blog</a>
                <a href="<?php echo esc_url( home_url( '/buying-guides' ) ); ?>">Buying Guides</a>
                <a href="<?php echo esc_url( home_url( '/care-guides' ) ); ?>">Care Guides</a>
            </div>
        </div>

        <div class="foot-bottom">
            <div>&copy; <span data-year><?php echo esc_html( date( 'Y' ) ); ?></span> Feel The G's. &nbsp;
                <a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>" style="color:var(--ink-mute);">Privacy</a> &middot;
                <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>" style="color:var(--ink-mute);">Terms</a> &middot;
                <span style="color:var(--ink-mute);">All sales subject to age verification. 18+ only.</span>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <button id="theme-toggle" aria-label="Toggle light/dark mode" style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid var(--line);color:var(--ink-mute);font-size:.82rem;font-weight:500;cursor:pointer;transition:all .25s;">
                    <svg id="theme-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/></svg>
                    <span id="theme-label">Light</span>
                </button>
                <span>feelthegs.com</span>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
