<?php
/**
 * Blog archive (the "posts page" set in Settings > Reading).
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main>
    <section class="page-hero" style="min-height:auto;padding-bottom:60px;">
      <div class="ph-smoke"><div class="ph-blob b1"></div><div class="ph-blob b2"></div><div class="ph-blob b3"></div></div>
      <div class="ph-inner center">
        <p class="eyebrow reveal" style="justify-content:center;">Blog</p>
        <h1 class="display reveal reveal-d1" style="margin:24px 0;">Guides, news &amp;<br><span class="italic gradient-text">growth tactics.</span></h1>
        <p class="lede reveal reveal-d2" style="max-width:560px;margin:0 auto;">Everything you need to run a adult store dropshipping business.</p>
      </div>
    </section>

    <section class="sec" style="padding-top:0;">
      <div class="wrap">
        <?php if ( have_posts() ) : ?>
          <div class="blog-grid">
            <?php
            $sdn_d = array( '', ' reveal-d1', ' reveal-d2' );
            $sdn_i = 0;
            while ( have_posts() ) :
                the_post();
                $sdn_cat   = get_the_category();
                $cat_name  = ! empty( $sdn_cat ) ? $sdn_cat[0]->name : 'Dropshipping';
                $thumb     = get_the_post_thumbnail_url( get_the_ID(), 'sdn-blog-thumb' ) ?: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&q=80';
                ?>
                <a href="<?php the_permalink(); ?>" class="blog-card reveal<?php echo esc_attr( $sdn_d[ $sdn_i % 3 ] ); ?>">
                  <div class="thumb"><span class="cat"><?php echo esc_html( $cat_name ); ?></span><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></div>
                  <div class="body">
                    <div class="meta"><span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span><span><?php echo esc_html( sdn_reading_time() ); ?></span></div>
                    <h4><?php the_title(); ?></h4>
                    <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                    <span class="more">Read more <?php echo sdn_arrow(); // phpcs:ignore ?></span>
                  </div>
                </a>
                <?php
                $sdn_i++;
            endwhile;
            ?>
          </div>

          <?php $sdn_next_url = get_next_posts_page_link(); ?>
          <?php if ( $sdn_next_url ) : ?>
          <div class="center reveal" style="margin-top:56px;">
            <a href="<?php echo esc_url( $sdn_next_url ); ?>" class="btn btn-outline btn-lg">Load More</a>
          </div>
          <?php endif; ?>
        <?php else : ?>
          <p class="center" style="color:var(--ink-mute);">No posts yet &mdash; check back soon.</p>
        <?php endif; ?>
      </div>
    </section>
</main>

<?php
get_footer();
