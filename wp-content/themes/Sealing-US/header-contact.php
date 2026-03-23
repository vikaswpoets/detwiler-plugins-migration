<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/assets/css/jm.css" type="text/css" media="screen" />
	<link rel="icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon" />

    <?php wp_head(); ?>
</head>
<?php
    $custom_logo_id = get_theme_mod( 'custom_logo' );
?>

<body <?php body_class(); ?>>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'cabling'); ?></a>

    <header id="masthead" class="site-header" >
        <div class="site-branding_jm_reduced">
            <div class="container">
                <div class="row" style="top:39px;">
                    <div class="col-sm-3">
                        <?php echo '<a href="'.$args['headerurl'].'" class="custom-logo-link" rel="home" itemprop="url">'.wp_get_attachment_image( $custom_logo_id, 'full', false, array('class'=> 'custom-logo',) ).'</a>'; ?>
                    </div>
                    <div class="col-sm-9">
                        <?php //echo get_template_part('/template-parts/header', 'right_jm_reduced') ?>
                    </div>
                </div>
            </div>
        </div><!-- .site-branding -->
    </header><!-- #masthead -->

    <div id="content" class="site-content">
