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
							Our apologies…the page you’re looking for might have moved or no longer exists.<br><br>

							But we’re here to help. You can get back to the <a href="<?= home_url(); ?>">Homepage</a> or feel free to <a href="<?= home_url('/contact-form'); ?>">Contact Us</a> if you need any assistance.
						</p>
						<p>
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
