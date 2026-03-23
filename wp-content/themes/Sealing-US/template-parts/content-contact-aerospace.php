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
			<h1 style="margin-bottom: 0;" class="text-center">In aerospace, every component matters.</h1>
		</header><!-- .entry-header -->
		<br>
			<div class="row" >
				<div class="col-md-6 divleft" style="padding-top: 20px;" >
					<div class="divhead">Get the Aerospace Sealing Solutions<br>You Can Rely On<br><br></div>
					Datwyler’s high-performance Parco O-rings
are crafted with QPL-listed military and
aerospace compounds to meet the strictest
industry standards. Whether for commercial
aviation, defense, or space applications, we
provide custom and standard solutions that
fuse cutting-edge innovation with reliability
you can trust.
					<ul>
						<li>Nadcap and AS9100-certified manufacturing</li>
						<li>Trusted by leading aerospace manufacturers</li>
						<li>Certifications: ISO 9001, AS9100, AC7115,ISO 17025</li>						
					</ul>
				</div>
				<div class="col-md-6" style="background-color: #f5f5f5;padding-left: 20px;padding-right: 20px;padding-top: 20px;">
					<div class="divhead text-center">Contact us today for the right solution</div>
					<div class="col-12"><?php the_content(); ?></div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">&nbsp;</div>
			</div>
		</div><!-- .entry-content -->

	</div>
</article><!-- #post-<?php the_ID(); ?> -->
