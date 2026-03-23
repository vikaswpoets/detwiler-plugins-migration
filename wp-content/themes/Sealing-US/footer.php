<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package cabling
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="footer-top">
			<div class="container">
			    <div class="row">
			        <div class="col-lg-6 col-xs-12">
                        <?php if (is_active_sidebar( 'footer-2' ) ): ?>
			            	<?php dynamic_sidebar('footer-2'); ?>
			            <?php endif ?>
                        <div class="footer-brand">
                            <p class="heading"><?php _e('Our brands','cabling') ?></p>
                            <?php if (is_active_sidebar( 'footer-brand' ) ): ?>
                                <?php dynamic_sidebar('footer-brand'); ?>
                            <?php endif ?>
                        </div>
			        </div><!--.col-->

			        <div class="col-lg-6 col-xs-12 footer-right">
                        <div class="footer-social">
                            <div class="datwyler-mobility">
                                <h5><a href="https://datwyler.com/" target="_blank"><?php echo __('Datwyler Group', 'cabling') ?></a></h5>
                                <ul>
                                    <li><a href="https://datwyler.com/mobility" target="_blank"><?php echo __('Mobility', 'cabling') ?></a></li>
                                    <li><a href="https://datwyler.com/healthcare" target="_blank"><?php echo __('Healthcare', 'cabling') ?></a></li>
                                    <li><a href="https://datwyler.com/connectors" target="_blank"><?php echo __('Connectors', 'cabling') ?></a></li>
                                    <li><a href="https://datwyler.com/food-beverage" target="_blank"><?php echo __('Food & Beverage', 'cabling') ?></a></li>
                                </ul>
                            </div>
							<!--
                            <div class="footer-brand">
                                <p class="heading"><?php _e('Follow us', 'cabling') ?></p>
                                <?php if (is_active_sidebar('footer-copyright')): ?>
                                    <?php dynamic_sidebar('footer-copyright'); ?>
                                <?php endif ?>
                            </div>
-->
                        </div>
			        </div><!-- .col -->
			    </div>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->
<?php wp_footer(); ?>
<!-- Tag manager conversion -->
<script>
    if (typeof window.lintrk === 'function') {
        window.lintrk('track', { conversion_id: 19146777 });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const backLink = document.querySelector('a.back-button');
        if (!backLink) return;

        // Insert a synthetic state so the first Back triggers popstate
        history.pushState({ blocker: true }, '', location.href);

        let handled = false;

        function onBackPressed(event) {
            if (handled) return;
            handled = true;

            // Trigger the click on your back link
            backLink.click();

            // Clean up so we don't trap the user on this page
            window.removeEventListener('popstate', onBackPressed);
        }

        window.addEventListener('popstate', onBackPressed);
    });
</script>

</body>
</html>
