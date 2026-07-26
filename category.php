<?php
/**
 * Blog category archive.
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$sdn_cat_desc = category_description();
?>

<main>
    <section class="page-hero" style="min-height:auto;padding-bottom:60px;">
      <div class="ph-smoke"><div class="ph-blob b1"></div><div class="ph-blob b2"></div><div class="ph-blob b3"></div></div>
      <div class="ph-inner center">
        <p class="eyebrow reveal" style="justify-content:center;">Category</p>
        <h1 class="display reveal reveal-d1" style="margin:24px 0;"><?php single_cat_title(); ?></h1>
        <?php if ( $sdn_cat_desc ) : ?>
          <p class="lede reveal reveal-d2" style="max-width:560px;margin:0 auto;"><?php echo wp_kses_post( $sdn_cat_desc ); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="sec" style="padding-top:0;">
      <div class="wrap">

        <div class="reveal" style="max-width:680px;margin:0 auto 48px;">
          <?php get_search_form(); ?>
        </div>

        <?php if ( have_posts() ) : ?>
          <div class="blog-grid">
            <?php
            $sdn_d = array( '', ' reveal-d1', ' reveal-d2' );
            $sdn_i = 0;
            while ( have_posts() ) :
                the_post();
                $sdn_cat  = get_the_category();
                $cat_name = ! empty( $sdn_cat ) ? $sdn_cat[0]->name : 'Dropshipping';
                ?>
                <a href="<?php the_permalink(); ?>" class="blog-card reveal<?php echo esc_attr( $sdn_d[ $sdn_i % 3 ] ); ?>">
                  <div class="body">
                    <span class="cat"><?php echo esc_html( $cat_name ); ?></span>
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

          <?php sdn_pagination(); ?>
        <?php else : ?>
          <p class="center" style="color:var(--ink-mute);">No posts in this category yet. Try the search above.</p>
        <?php endif; ?>
      </div>
    </section>
</main>

<?php
get_footer();
