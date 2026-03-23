<?php
/**
 * Template Name: Page Rubber To Metal Seals
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package cabling
 */

get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php get_template_part('template-parts/page', 'header'); ?>
            <div class="container">
                <?php
                while (have_posts()) :
                    the_post();

                    get_template_part('template-parts/content', 'rubber-to-metal');
                    get_template_part('template-parts/section', 'request-a-quote');

                endwhile;//End of the loop.
                ?>
            </div>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
