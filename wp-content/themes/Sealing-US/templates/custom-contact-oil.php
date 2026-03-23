<?php
/**
  * Template Name: Custom Contact Page for Oil and Gas
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
$link="https://datwylersealing.com/production-equipments/";
get_header('contact',['headerurl'=>$link]);
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php get_template_part( 'template-parts/page', 'header-contact' ); ?>
            <div class="container">
                <?php get_template_part( 'template-parts/page', 'breadcrumb' ); ?>
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <?php
                            get_template_part( 'template-parts/content', 'contact-oil' );
                        ?>
                    </div><!-- .col -->
                </div><!-- .row -->

            </div>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php
//get_footer();
get_footer('contact');
