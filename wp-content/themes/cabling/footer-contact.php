<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package cabling
 */
?>

	</div><!-- #content -->
	<div class="modal fade" id="emailShareModal" tabindex="-1" aria-labelledby="emailShareModalLabel" aria-hidden="true">
		<!--//WTF do I need the editor for....-->
		<?php wp_editor('', 'message', $settings); ?>
	</div>
</div><!-- #page -->
<?php 
// Disable Cookie Yes
//global $wp_customize;
//print_r($wp_customize);
//$wp_customize=false;
//cky_disable_banner();
//remove_action('wp_footer', 'banner_html');


function print_filters_for( $hook = '' ) {
    global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return;

    print '<pre>';
    print_r( json_encode($wp_filter[$hook]) );
    print '</pre>';
}

function remove_action_by_partial_key($hook, $partialkey)
{
	global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return;

    foreach($wp_filter[$hook] as $action)
    {
    	foreach (array_keys($action) as $key) {
    		if(str_contains($key, $partialkey))
    		{
    			remove_action($hook, $key);
    		}
    	}
    	
    }
}



remove_action('wp_footer', 'cabling_add_theme_popup');

    get_template_part('template-parts/loading');
    get_template_part('template-parts/sidebar', 'navigation');
    get_template_part('template-parts/modal/popup', 'customer');
    
    get_template_part('template-parts/modal/popup', 'pdf');
    get_template_part('template-parts/modal/popup', 'success');
    get_template_part('template-parts/modal/popup', 'error');
get_template_part('template-parts/modal/popup', 'errorvalidation');
    get_template_part('template-parts/modal/popup', 'message');

remove_action_by_partial_key("wp_footer", 'banner_html');
wp_footer(); 

?>

<!-- Tag manager conversion -->
<script>
if (typeof window.lintrk === 'function') {
    //window.lintrk('track', { conversion_id: 19146777 });
}
</script>

</body>
</html>
