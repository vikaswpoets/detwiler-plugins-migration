<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package cabling
 */
$contact_types = get_terms(array('taxonomy' => 'contact_type','parent' => 0));
// Precision, Performance, and Reliability in Every Seal
?>
<style type="text/css"> 
	input,select, textarea {border-radius: 10px !important;}
	.divhead{font-size:24px;font-weight: bold;}
	.divleft{font-size: 20px;}
</style>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div style="position: relative; margin-top:-100px;margin-bottom:20px; background-color: white; box-shadow: 10px 10px 10px #cbc7c7;margin-left: 5%;margin-right: 5%;">
		<div class="entry-content" style="padding-top: 20px;padding-left: 5%;padding-right: 5%;">
		<header class="entry-header text-center">
			<div  class="contactthanks">
				<h1 style="margin-bottom: 0;">Thank you for your request</h1>
			</div>
		</header><!-- .entry-header -->
		<br>
			<div class="row" >
				<div class="col-md-12 text-center" style="padding-top: 20px;" >
					One of our specialists will review it and get back to you shortly
					<br><br>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="wp-block-button text-center">
					<a href="/industries/energy-completion-drilling/" class="button_link_thank_you wp-element-button">Explore more energy completion and drilling sealing solutions from Datwyler</a>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">&nbsp;</div>

			</div>
		</div><!-- .entry-content -->

	</div>
</article><!-- #post-<?php the_ID(); ?> -->
