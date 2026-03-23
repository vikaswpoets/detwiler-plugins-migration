<?php $title = get_field('custom_title');
    if (empty($title)) {
        $title = get_the_title();        
    }
    $secondtitle = get_field('custom_title_next_line');
    
?>
<header class="page-header <?php echo has_post_thumbnail() ? 'has-background' : '' ?>" 
    <?php echo !empty($secondtitle)? "style=\"height:300px;\"":"" ?> >
    <h1 class="entry-title" ><?php echo $title ?></h1>
    <?php if (!empty($secondtitle)){ echo ("<h1 class=\"entry-title\" >". $secondtitle."</h1>");}else{ echo "";} ?>
    <p class="entry-sub-title" ><?php echo get_field('custom_sub_tittle') ?></p>
    <?php if (has_post_thumbnail()) cabling_post_thumbnail(); ?>
</header><!-- .entry-header -->
