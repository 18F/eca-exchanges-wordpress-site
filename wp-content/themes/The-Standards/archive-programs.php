<?php
/**
 * The template for displaying archive programs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package The_Standards
 */

get_header();

$args = array(
  'post_type'   => 'program',
  'post_status' => 'publish'
);

$programs = new WP_Query( $args );

?>

	<section class="usa-grid usa-section">

		<?php if ( $programs->have_posts() ) : ?>

			<?php
			/* Start the Loop */
			while ( $programs->have_posts() ) :
				$programs->the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile;

			the_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</section><!-- .usa-grid .usa-section -->

<?php
get_sidebar();
get_footer();
