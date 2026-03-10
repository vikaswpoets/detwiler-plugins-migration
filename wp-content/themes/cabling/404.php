<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package cabling
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="container">
				<section class="error-404 not-found">
					<header class="page-header" style="height: auto;padding-top: 35px;">
						<h1 class="page-title">Page Not Found</h1>
					</header><!-- .page-header -->

					<div class="page-content text-center">
						<p>
						The page you're looking for might have been moved or no longer exists. <br>
						But don't worry, we're here to help!
						</p>
						<p>
						You can:
						<br><br>
						Return to our <a href="<?= home_url(); ?>">Homepage</a><br>
						Explore <a href="<?= home_url('/products-and-services'); ?>">our Product</a><br>
						Or <a href="<?= home_url('/contact-form'); ?>">Contact Us</a> for further assistance.
						<br><br>
						Thank you for visiting Datwyler Sealing!
						</p>
					</p>
					</div><!-- .page-content -->
				</section><!-- .error-404 -->
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
