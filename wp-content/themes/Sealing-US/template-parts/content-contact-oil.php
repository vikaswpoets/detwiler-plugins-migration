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
			<h1 style="margin-bottom: 0;" class="text-center">Datwyler’s Double E Production Equipment Brings
Together Innovation, Quality and Reliability.</h1>
		</header><!-- .entry-header -->
		<br><br>
			<div class="row" >
				<div class="col-md-6 divleft" style="padding-top: 20px;" >
					Datwyler’s Double E surface production equipment combines over 100 years of U.S. manufacturing expertise with precision engineering in Dallas. <br>
Our product range includes:<br>
					<ul>
						<li><b>Sucker Rod Strippers</b> – Featuring our patented
Spring Assist design, also available for retrofits</li>
						<li><b>Blow Out Preventers</b> – Engineered for safety and durability</li>
						<li><b>Cap Kings</b> – Reliable solutions for wellhead protection</li>
						<li><b>Stuffing Boxes</b> – Designed for maximum sealing performance</li>
						<li><b>Rod Guides</b> – Enhancing rod stability and wear resistance</li>
						<li><b>Oil Savers</b> – Simple and reliable design keeping environment and rig equipment clean</li>
					</ul>
					And that’s just the start. With our extensive range of custom and standard solutions, you get the quality and reliability you expect from Double E.
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
