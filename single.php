<?php
/**
 * Single blog post.
 *
 * Featured image is shown in full (not cropped) inside a blurred,
 * color-matched backdrop made from the same image, so portrait and
 * landscape images both look intentional. Body copy sits in a white
 * card for readability; sidebar has a Shopify promo, social share, and
 * related posts.
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
the_post();

$sdn_cat      = get_the_category();
$sdn_cat_name = ! empty( $sdn_cat ) ? $sdn_cat[0]->name : 'Dropshipping';
$sdn_thumb    = get_the_post_thumbnail_url( get_the_ID(), 'full' );
$sdn_permalink = get_permalink();
$sdn_title     = get_the_title();
?>

<main>
    <!-- FEATURED IMAGE HERO -->
    <section class="post-hero post-hero-wide">
      <?php if ( $sdn_thumb ) : ?>
        <div class="post-hero-bg" style="background-image:url('<?php echo esc_url( $sdn_thumb ); ?>');"></div>
      <?php endif; ?>
      <div class="post-hero-content reveal">
        <span class="cat-badge"><?php echo esc_html( $sdn_cat_name ); ?></span>
        <h1><?php the_title(); ?></h1>
        <div class="post-meta">
          <span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
          <span><?php echo esc_html( sdn_reading_time() ); ?></span>
        </div>
      </div>
      <?php if ( $sdn_thumb ) : ?>
        <div class="post-featured-wrap reveal reveal-d1">
          <img src="<?php echo esc_url( $sdn_thumb ); ?>" alt="<?php the_title_attribute(); ?>">
        </div>
      <?php endif; ?>
    </section>

    <section class="sec" style="padding-top:0;">
      <div class="wrap">
        <div class="post-layout">
          <article class="post-content reveal">
            <?php the_content(); ?>
          </article>

          <aside class="post-sidebar">
            <a href="https://apps.shopify.com/air-waves-apparel" class="shopify-cta" style="display:flex;">
              <svg viewBox="0 0 24 24" fill="#95bf47" width="24" height="24"><path d="M15.337 4.13a4.36 4.36 0 0 0-2.69 1.43 4.07 4.07 0 0 0-3.34-1.42c-2.41.12-3.96 2.13-3.96 4.4 0 4.04 3.86 7.04 5.95 8.34l.04.02.04-.02c2.09-1.3 5.95-4.3 5.95-8.34 0-2.27-1.55-4.28-3.96-4.4z"/></svg>
              <div><strong>Install on Shopify</strong><small>One-click from the App Store</small></div>
            </a>

            <a href="<?php echo esc_url( 'https://app.f13commerce.com/register' ); ?>" class="shopify-cta" style="display:flex;">
              <svg viewBox="0 0 24 24" fill="#7f54b3" width="24" height="24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 8h4a3 3 0 0 1 0 6h-1l3 4" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <div><strong>Download WooCommerce Plugin</strong><small>Self-hosted WordPress stores</small></div>
            </a>

            <div class="sidebar-block">
              <h5>Share this post</h5>
              <div class="share-row">
                <a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( $sdn_permalink ); ?>&text=<?php echo rawurlencode( $sdn_title ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.844l-5.36-7.01L4.16 22H.9l8.02-9.17L1.5 2h6.99l4.84 6.4L18.244 2z"/></svg></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $sdn_permalink ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.5-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg></a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( $sdn_permalink ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 0 1 6 6v6h-4v-6a2 2 0 0 0-4 0v6h-4v-10h4v1.5"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
              </div>
            </div>

            <?php
            $sdn_related = new WP_Query( array(
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'post__not_in'        => array( get_the_ID() ),
                'category__in'        => wp_list_pluck( $sdn_cat, 'term_id' ),
                'ignore_sticky_posts' => true,
            ) );
            if ( $sdn_related->have_posts() ) :
                ?>
                <div class="sidebar-block">
                  <h5>Related posts</h5>
                  <div class="related-list">
                    <?php
                    while ( $sdn_related->have_posts() ) :
                        $sdn_related->the_post();
                        ?>
                        <a href="<?php the_permalink(); ?>" class="related-item">
                          <span class="ri-thumb"><img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'sdn-blog-thumb' ) ?: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&q=80' ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></span>
                          <span class="ri-title"><?php the_title(); ?></span>
                        </a>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                  </div>
                </div>
                <?php
            endif;
            ?>
          </aside>
        </div>
      </div>
    </section>

    <?php sdn_cta(); ?>
</main>

<?php
get_footer();
