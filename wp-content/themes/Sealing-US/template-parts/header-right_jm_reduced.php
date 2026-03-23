<?php
$buy_online_link = get_field('buy_online_link', 'option');
?>
<div class="header-right">
    <div class="top-navigation_jm d-flex text-uppercase mb-3">
        <div class="header-cart">
            <?php echo do_shortcode('[country_selector]') ?>
            <?php echo do_shortcode('[language_selector]') ?>
            <a class="buy-online-mb d-none" href="<?= $buy_online_link; ?>" style="display:none !important;">
                <?php echo __('Select & Buy O-Rings', 'cabling');?>
            </a>
            <!-- GE-258 -->
            <i class="fa-solid fa-bars open-menu-mobile" style="font-size: 16px;cursor: pointer;margin-right: 20px;"></i>
            <a href="<?php echo home_url('/my-wishlist') ?>">
                <i class="fa-light fa-heart me-2"></i>
                <?php echo __('MY WISHLIST', 'cabling');?>
            </a>
            <a class="ms-2" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <i class="fa-light fa-shopping-cart me-2"></i>
                <?php echo __('My cart', 'cabling'); ?>
            </a>
        </div>
        <?php if (is_user_logged_in()): ?>
		<!--
			<div class="my-account-item mx-3">
				<a class="nav-link" href="#" role="button">
                    <span class="icon"><img src="<?php echo get_template_directory_uri() . '/images/myquotes.png' ?>"
                                            width="25" height="23" alt=""></span>
                    <?php _e('My Quotes', 'cabling') ?>
                </a>
			</div>
-->
            <div class="my-account-item mx-3">
                <a class="nav-link dropdown-toggle" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="icon"><img src="<?php echo get_template_directory_uri() . '/images/signin.png' ?>"
                                             height="23" alt="" style="height:23px;"></span>
                    <?php _e('My account', 'cabling') ?>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item"
                           href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')) ?>">
                            <?php _e('Menu', 'cabling') ?>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?php echo esc_url(wc_logout_url()) ?>">
                            <?php _e('Logout', 'cabling') ?>
                        </a>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="sign-in-item me-4">
                <span class="icon"><img src="<?php echo get_template_directory_uri() . '/images/signin.png' ?>"
                                        width="19" height="31" alt=""></span>
                <span><a style="color: inherit" href="<?php echo wc_get_account_endpoint_url('') ?>"><?php _e('My account | Sign in', 'cabling') ?><?php //_e('Sign in', 'cabling') ?></a></span>
            </div>
        <?php endif; ?>
        <div class="search-item">
            <span class="icon me-2"><img src="<?php echo get_template_directory_uri() . '/images/search.png' ?>"
                                          alt="" style="height:23px;"></span>
            <span><?php _e('Search', 'cabling') ?></span>
        </div>
    </div>
    <div class="primary-navigation d-flex" style="margin-right:10px;">
        <nav id="site-navigation" class="main-navigation_jm">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'menu-1',
                'menu_id' => 'primary-menu',
            ));
            ?>
        </nav><!-- #site-navigation -->
        <div class="menu-toggle d-flex align-items-center ms-5 top-navigation_jm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <img src="<?php echo get_template_directory_uri() . '/images/menu.png' ?>" width="17"  alt="">
            <span class="ms-2"><?php _e('MENU', 'cabling') ?></span>
        </div>
    </div>
</div>
<!-- GE-258 -->
<div class="drop-menu-mobile">
    <?php
        wp_nav_menu(array(
            'theme_location' => 'menu-1',
            'menu_id' => 'primary-menu',
        ));
    ?>
</div>
<style>
    @media (min-width:768px){
        .open-menu-mobile{
            display:none;
        }
    }
    @media screen and (max-width: 767px) and (min-width: 576px) {
        .header-right .top-navigation_jm .header-cart {
            right: 117px;
            top: 4px;
        }
        .header-right .top-navigation_jm .search-item {
            right: 90px;
            top: 20px;
        }
        .header-right .primary-navigation {
            top: 19px;
        }
    }
    .drop-menu-mobile {
        position: absolute;
        top: 100%;
        z-index: 1;
        background: #ffffff;
        width: 100%;
        left: 0;
        padding: 20px;
        display:none;
    }
    .drop-menu-mobile #primary-menu {
        display: block;
    }
    .drop-menu-mobile #primary-menu a{
        color:#000000;
        text-transform: uppercase;
        font-size: 13px;
    }
    .drop-menu-mobile #primary-menu > li {
        margin: 10px 0;
    }
    .open-menu-mobile.active:before {
        content: "\f00d";
    }
    @media (max-width:575px){
        .header-right {
            margin-top: 10px;
        }
        .header-right .top-navigation_jm .search-item {
            top: 68px;
        }
    }
</style>
<script>
    jQuery(document).ready(function($) {
        $('body').on('click', '.open-menu-mobile', function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            $(".drop-menu-mobile").slideToggle();
        });
    });
</script>
