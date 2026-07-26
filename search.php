<?php
/**
 * Search results template.
 *
 * Renders matched posts in the same blog-grid card style as the category
 * archive and blog index. Shows the search form at the top so users can
 * refine their query.
 *
 * @package ProductPro
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$sdn_query = get_search_query();
?>

<main>
    <section class="page-hero">
      <div class="ph-smoke"><div class="ph-blob b1"></div><div class="ph-blob b2"></div><div class="ph-blob b3"></div></div>
      <div class="ph-inner center">
        <p class="eyebrow reveal" style="justify-content:center;">Search</p>
        <h1 class="display reveal reveal-d1" style="margin:24px 0 16px;">
          <?php if ( $sdn_query ) : ?>
            Results for <span class="italic gradient-text">&ldquo;<?php echo esc_html( $sdn_query ); ?>&rdquo;</span>
          <?php else : ?>
            Search the <span class="italic gradient-text">Knowledge Base</span>
          <?php endif; ?>
        </h1>
      </div>
    </section>

    <section class="sec">
      <div class="wrap">
        <div class="reveal" style="max-width:680px;margin:0 auto 48px;">
          <?php get_search_form(); ?>
        </div>

        <?php if ( have_posts() ) : ?>
          <p class="reveal" style="color:var(--ink-mute);margin-bottom:32px;text-align:center;">
            <?php
            printf(
              esc_html( _n( '%s article found', '%s articles found', $wp_query->found_posts, 'productpro' ) ),
              number_format_i18n( $wp_query->found_posts )
            );
            ?>
          </p>
          <div class="blog-grid">
            <?php
            $sdn_d = array( '', ' reveal-d1', ' reveal-d2' );
            $sdn_i = 0;
            while ( have_posts() ) : the_post();
              $sdn_cats  = get_the_category();
              $sdn_cat   = ! empty( $sdn_cats ) ? $sdn_cats[0]->name : 'Article';
            ?>
              <a href="<?php the_permalink(); ?>" class="blog-card reveal<?php echo esc_attr( $sdn_d[ $sdn_i % 3 ] ); ?>">
                <div class="body">
                  <span class="cat"><?php echo esc_html( $sdn_cat ); ?></span>
                  <div class="meta"><span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span><span><?php echo esc_html( sdn_reading_time() ); ?></span></div>
                  <h4><?php the_title(); ?></h4>
                  <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                  <span class="more">Read more <?php echo sdn_arrow(); // phpcs:ignore ?></span>
                </div>
              </a>
            <?php $sdn_i++; endwhile; ?>
          </div>

          <?php sdn_pagination(); ?>

          <!-- Login to see all products -->
          <div class="login-cta-banner">
            <p>Want to see every product? <a href="https://app.f13commerce.com/register">Log in or create a free account</a> to access the full ProductPro catalog.</p>
          </div>

        <?php else : ?>
          <div class="center" style="padding:60px 20px;">
            <div class="reveal" style="font-size:3rem;margin-bottom:16px;">🔍</div>
            <h2 class="h-sec reveal">No articles found.</h2>
            <p class="reveal" style="color:var(--ink-mute);margin-top:12px;">Try a different keyword, or <a href="<?php echo esc_url( home_url( '/help' ) ); ?>" style="color:var(--green-xl);">browse the Help Center</a>.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
</main>

<?php
get_footer();
