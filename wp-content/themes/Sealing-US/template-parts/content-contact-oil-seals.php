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
		<header class="entry-header">
			<h1 style="margin-bottom: 0;" class="text-center">Seals That Perform in the Toughest Conditions</h1>
		</header><!-- .entry-header -->
		<br><br>
			<div class="row" >
				<div class="col-md-6 divleft" style="padding-top: 20px;" >
					Datwyler’s Parco O-rings and custom molded rubber seals are built to withstand extreme pressures, temperatures, and corrosive environments - ensuring durability and reliability across upstream, midstream, and downstream applications.<br>
					<ul>
						<li><b>High-performance</b> materials for extreme conditions</li>
						<li><b>Custom & standard O-rings</b> for drilling, refining & transport</li>
						<li><b>Custom Molded Seals</b> for your most demanding applications</li>
						<li><b>Proven reliability</b> in the field</li>
					</ul>
					Trust Datwyler for sealing solutions that keep your operations running smoothly.
				</div>
				<div class="col-md-6" style="background-color: #f5f5f5;padding: 20px;">
					<div class="divhead text-center">Get a quote for your project</div>
					<div class="col-12"><?php the_content(); ?></div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">&nbsp;</div>

			</div>
		</div><!-- .entry-content -->

	</div>
</article><!-- #post-<?php the_ID(); ?> -->
