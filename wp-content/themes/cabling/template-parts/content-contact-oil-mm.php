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
	/*.divleft{font-size: 20px;}*/
</style>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div style="position: relative; margin-top:-100px;margin-bottom:20px; background-color: white; box-shadow: 10px 10px 10px #cbc7c7;margin-left: 5%;margin-right: 5%;">
		<div class="entry-content" style="padding-top: 20px;padding-left: 5%;padding-right: 5%;">
		<header class="entry-header">
			<h1 style="margin-bottom: 0;" class="text-center">Custom Parts Created with Efficiency,</h1>
			<h1 style="margin-bottom: 0;" class="text-center">Precision - and Speed</h1>
		</header><!-- .entry-header -->
		<br>
			<div class="row" >
				<div class="col-md-6 divleft" style="padding-top: 20px;" >
					At Datwyler, we deliver high-quality, custom-machined metal solutions designed to meet the demanding needs of the Oil & Gas industry. With our <b>Double E, TST, and Olympian brands,</b> we offer fast turnaround times, precision engineering, and a wide range of metals to ensure the right solution for your application.<br>
					<b>Materials we machine include:</b><br>
					<ul>
						<li><b>Alloy Steel</b> (4130-4145, 4340, 8620, 9310)</li>
						<li><b>Nickel</b> (400, K500, 625, 718, 945, 925)</li>
						<li><b>Aluminum</b> (1100, 2024, 3003, 5052, 6061, 6063, 7075)</li>
						<li><b>Stainless Steel</b> (303, 304, 316, 410, 420, 17-4PH, 13-8PH, 15-5PH, NIT50, NIT60)</li>
						<li><b>Beryllium Copper</b> (C17200, A25, AT (TFOO), HT (TH04), C173)</li>
						<li><b>CF Carbon</b> (1018-1026, 12L14, 1215, A105, A350-LF2)</li>
						<li><b>Aluminum Bronze, Hyper Chrome, Inconel</b></li>
						<li><b>Machined Thermoplastics, Phenolics</b></li>
					</ul>
					With cutting-edge lathes and mills in our state-of-theart facilities, we handle <b>both small and large custom orders with efficiency and precision.</b>
				</div>
				<div class="col-md-6" style="background-color: #f5f5f5;padding-left: 20px;padding-right: 20px;padding-top: 20px;">
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
