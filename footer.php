<?php
/**
 * The footer for Feel The G's — appears on every page.
 *
 * Sections (top to bottom):
 *   1. Trust badges row (discreet shipping / free shipping / support)
 *   2. "What customers are saying" testimonials (ACF option, seeded with real IG data)
 *   3. Trustpilot review badge
 *   4. Footer columns (brand + social, shop, customer care, about)
 *   5. Newsletter signup
 *   6. Bottom bar (copyright, legal, theme toggle)
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

// Testimonials (ACF option — manage in admin → Feel The G's Theme Settings).
$ftgs_testimonials = function_exists( 'get_field' ) ? get_field( 'ftgs_testimonials', 'option' ) : null;
if ( ! is_array( $ftgs_testimonials ) ) {
    $ftgs_testimonials = array();
}

// Social profile URLs (real handles from the live site).
$ftgs_social = array(
    'instagram' => 'https://www.instagram.com/feelthegs/',
    'facebook'  => 'https://www.facebook.com/FeelTheG/',
    'x'         => 'https://x.com/FeelTheGs',
    'youtube'   => 'https://www.youtube.com/@feelthegs',
    'pinterest' => 'https://www.pinterest.com/FeelTheGs',
);
?>

<footer class="footer site-footer">
    <div class="footer-inner">

        <!-- 1. TRUST BADGES ROW -->
        <section class="ftgs-trust-badges reveal">
            <div class="wrap">
                <div class="ftgs-trust-grid">
                    <a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>" class="ftgs-trust-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <span><strong>Always discreet packaging</strong><small>Plain, unmarked boxes</small></span>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>" class="ftgs-trust-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span><strong>Free shipping over $69</strong><small>USA &amp; Canada</small></span>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="ftgs-trust-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <span><strong>Chat &amp; email support</strong><small>Discreet, friendly help</small></span>
                    </a>
                </div>
            </div>
        </section>

        <?php if ( ! empty( $ftgs_testimonials ) ) : ?>
        <!-- 2. WHAT CUSTOMERS ARE SAYING -->
        <section class="ftgs-testimonials reveal">
            <div class="wrap">
                <div class="ftgs-testimonials-head">
                    <span class="ftgs-testimonials-eyebrow">What customers are saying</span>
                </div>
                <div class="ftgs-testimonials-grid">
                    <?php foreach ( $ftgs_testimonials as $t ) :
                        $quote   = isset( $t['quote'] ) ? $t['quote'] : '';
                        $author  = isset( $t['author'] ) ? $t['author'] : '';
                        $photo   = ! empty( $t['photo'] ) ? $t['photo'] : null;
                        // Photo can be a URL string (our seed) or an ACF image array.
                        $photo_url = '';
                        if ( is_array( $photo ) ) {
                            $photo_url = isset( $photo['sizes']['thumbnail'] ) ? $photo['sizes']['thumbnail'] : ( $photo['url'] ?? '' );
                        } elseif ( is_string( $photo ) ) {
                            $photo_url = $photo;
                        } elseif ( is_numeric( $photo ) ) {
                            $photo_url = wp_get_attachment_image_url( $photo, 'thumbnail' );
                        }
                        $rating  = isset( $t['rating'] ) ? intval( $t['rating'] ) : 5;
                        $social  = isset( $t['social_url'] ) ? $t['social_url'] : '';
                        ?>
                        <figure class="ftgs-testimonial">
                            <?php if ( $photo_url ) :
                                $photo_html = '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $author ) . '" loading="lazy">';
                                echo $social ? '<a href="' . esc_url( $social ) . '" target="_blank" rel="noopener" class="ftgs-testimonial-photo">' . $photo_html . '</a>' : '<span class="ftgs-testimonial-photo">' . $photo_html . '</span>';
                            endif; ?>
                            <div class="ftgs-testimonial-content">
                                <div class="ftgs-testimonial-stars" aria-label="<?php echo esc_attr( sprintf( '%d out of 5 stars', $rating ) ); ?>">
                                    <?php for ( $s = 0; $s < 5; $s++ ) : ?>
                                        <svg viewBox="0 0 24 24" fill="<?php echo $s < $rating ? 'var(--gold)' : 'none'; ?>" stroke="var(--gold)" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <?php endfor; ?>
                                </div>
                                <blockquote class="ftgs-testimonial-quote"><?php echo wp_kses_post( $quote ); ?></blockquote>
                                <figcaption class="ftgs-testimonial-author">
                                    <span class="ftgs-testimonial-name"><?php echo esc_html( $author ); ?></span>
                                    <?php if ( $social ) : ?>
                                        <a href="<?php echo esc_url( $social ); ?>" target="_blank" rel="noopener" class="ftgs-testimonial-social" aria-label="<?php echo esc_attr( $author ); ?> on social media">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </figcaption>
                            </div>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 3. TRUSTPILOT BADGE -->
        <section class="ftgs-trustpilot-row">
            <div class="wrap center">
                <!-- Trustpilot Mini badge — business unit 69e071c12c5ffd1a33010bad -->
                <div class="trustpilot-widget"
                     data-locale="en-US"
                     data-template-id="56278e9abfbbba0bdcd568bc"
                     data-businessunit-id="69e071c12c5ffd1a33010bad"
                     data-style-height="52px"
                     data-style-width="100%"
                     style="position:relative;">
                    <a href="https://www.trustpilot.com/review/feelthegs.com" target="_blank" rel="noopener">Review us on Trustpilot</a>
                </div>
            </div>
        </section>

        <!-- 4. FOOTER COLUMNS -->
        <div class="foot-grid">
            <div class="foot-brand">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand"><?php echo ftgs_logo( 42 ); // phpcs:ignore ?></a>
                <p>Premium sex toys, lingerie, bondage gear, and intimate wellness. Discreet shipping on every order.</p>
                <div class="foot-social">
                    <a href="<?php echo esc_url( $ftgs_social['instagram'] ); ?>" aria-label="Instagram" target="_blank" rel="noopener" class="ftgs-social-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                    </a>
                    <a href="<?php echo esc_url( $ftgs_social['facebook'] ); ?>" aria-label="Facebook" target="_blank" rel="noopener" class="ftgs-social-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>
                    </a>
                    <a href="<?php echo esc_url( $ftgs_social['x'] ); ?>" aria-label="X" target="_blank" rel="noopener" class="ftgs-social-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.844l-5.36-7.01L4.16 22H.9l8.02-9.17L1.5 2h6.99l4.84 6.4L18.244 2z"/></svg>
                    </a>
                    <a href="<?php echo esc_url( $ftgs_social['youtube'] ); ?>" aria-label="YouTube" target="_blank" rel="noopener" class="ftgs-social-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 8.5a3 3 0 0 0-2.1-2.1C18 6 12 6 12 6s-6 0-7.9.4A3 3 0 0 0 2 8.5 31 31 0 0 0 2 12a31 31 0 0 0 .1 3.5 3 3 0 0 0 2 2.1C6 18 12 18 12 18s6 0 7.9-.4a3 3 0 0 0 2.1-2.1A31 31 0 0 0 22 12a31 31 0 0 0-.1-3.5z"/><polygon points="10 9 15 12 10 15" fill="currentColor"/></svg>
                    </a>
                    <a href="<?php echo esc_url( $ftgs_social['pinterest'] ); ?>" aria-label="Pinterest" target="_blank" rel="noopener" class="ftgs-social-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.09 2.46 7.6 5.97 9.13-.08-.78-.16-1.97.03-2.82.18-.78 1.14-4.97 1.14-4.97s-.29-.58-.29-1.44c0-1.35.78-2.36 1.76-2.36.83 0 1.23.62 1.23 1.37 0 .83-.53 2.08-.81 3.24-.23.97.49 1.76 1.44 1.76 1.73 0 3.06-1.82 3.06-4.45 0-2.33-1.67-3.96-4.06-3.96-2.77 0-4.39 2.07-4.39 4.21 0 .83.32 1.73.72 2.21.08.1.09.18.07.28-.07.31-.24.97-.27 1.1-.04.18-.14.22-.33.13-1.22-.57-1.98-2.35-1.98-3.79 0-3.08 2.24-5.92 6.46-5.92 3.39 0 6.03 2.42 6.03 5.65 0 3.37-2.13 6.09-5.08 6.09-.99 0-1.92-.52-2.24-1.13l-.61 2.32c-.22.85-.81 1.91-1.21 2.56.91.28 1.88.43 2.88.43 5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
                    </a>
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
                <a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>">Shipping &amp; Delivery</a>
                <a href="<?php echo esc_url( home_url( '/return-policy/' ) ); ?>">Returns &amp; Exchanges</a>
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a>
                <a href="<?php echo esc_url( home_url( '/sell/' ) ); ?>">Sell Your Products</a>
                <a href="<?php echo esc_url( home_url( '/wholesale/' ) ); ?>">Wholesale</a>
            </div>
            <div class="foot-col">
                <h5>About</h5>
                <a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">Our Story</a>
                <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a>
                <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Service</a>
            </div>
        </div>

        <!-- 5. NEWSLETTER -->
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
                <p class="news-legal">Emails will be sent by or on behalf of Feel The G's, Coast Highway Encinitas, CA 92024. You may withdraw your consent at any time. <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">We Do Not Sell My Personal Information</a>.</p>
            </div>
        </div>

        <!-- 6. BOTTOM BAR -->
        <div class="foot-bottom">
            <div>&copy; <span data-year><?php echo esc_html( date( 'Y' ) ); ?></span> Feel The G's. &nbsp;
                <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" style="color:var(--ink-mute);">Privacy</a> &middot;
                <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>" style="color:var(--ink-mute);">Terms</a> &middot;
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

<?php
// Trustpilot bootstrap script — needed for the badge to render its stars.
// Loaded only once, in the footer, async.
if ( ! wp_script_is( 'trustpilot-bootstrap', 'enqueued' ) ) {
    wp_enqueue_script( 'trustpilot-bootstrap', 'https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js', array(), null, true );
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {
        if ( 'trustpilot-bootstrap' === $handle ) {
            return str_replace( ' src', ' async src', $tag );
        }
        return $tag;
    }, 10, 2 );
}
wp_footer();
?>
</body>
</html>
