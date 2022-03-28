<?php
/**
 * The template for displaying all single programs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package The_Standards
 */

get_header();

$args = array(
	'post_type'   => 'programs',
	'post_status' => 'publish'
  );

$programs = new WP_Query( $args );

?>

	<section class="usa-grid usa-section">

		<?php
		while ( $programs->have_posts() ) :
			$programs->the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation();

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

	</section><!-- .usa-grid .usa-section -->

<?php
get_sidebar();
get_footer();
