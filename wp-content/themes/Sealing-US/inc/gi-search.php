<?php
// Force enable SearchWP's alternate indexer.
add_filter( 'searchwp\indexer\alternate', '__return_true' );
function search_ajax()
{
    $search_query = isset($_REQUEST['key_search']) ? sanitize_text_field($_REQUEST['key_search']) : null;
    $paged = (int)$_REQUEST['paged'] ?? 1;

    $posts_per_page = 5;
    $data = [];
    $post_type = [];
    $tax_query = [];
    if (!empty($_REQUEST['data'])) {
        parse_str($_REQUEST['data'], $data);

        if (empty($data['search-all'])) {
            if (!empty($data['search-blog'])) {
                $post_type[] = 'post';
            }
            if (!empty($data['search-news'])) {
                $post_type[] = 'company_news';
            }
            if (!empty($data['search-product'])) {
                $post_type[] = 'product';

                if (!empty($data['product_cat'])) {
                    $tax_query = array(
                        array(
                            'taxonomy' => 'product_group',
                            'field' => 'term_id',
                            'terms' => $data['product_group'],
                        )
                    );
                }
            }
        }
    }

    $args = [
        's' => $search_query,
        'posts_per_page' => $posts_per_page,
        'post_type' => $post_type,
        'tax_query' => $tax_query,
        'paged' => $paged,
    ];

    // If a search query is present use SWP_Query
    // else fall back to WP_Query
    if (!empty($search_query)) {
        $swp_query = new SWP_Query($args);
    } else {
        $swp_query = new WP_Query($args);
    }
    $pagination = '';
    ob_start();
    if ($swp_query->have_posts()) {
        while ($swp_query->have_posts()) :
            $swp_query->the_post();

            $post_type = get_post_type();
            switch ($post_type) {
                case 'post':
                    $post_type_name = __('Blog', 'cabling');
                    break;
                case 'company_news':
                    $post_type_name = __('News', 'cabling');
                    break;
                case 'production-equipment':
                    $post_type_name = __('Production Equipment', 'cabling');
                    break;
                case 'gi_learn':
                    $post_type_name = __('Learns', 'cabling');
                    break;
                case 'page':
                    $page_id = wp_get_post_parent_id();
                    $post_type_name = get_the_title($page_id);
                    break;
                default:
                    $post_type_name = $post_type;
                    break;
            }
            $title = get_the_title();
            $content = wp_trim_words(get_the_content(), 40);
            ?>
            <div class="search-result post-item">
                <div class="entry-content row">
                    <div class="featured-image col-12 col-lg-4">
                        <a href="<?php echo get_permalink(); ?>">
                            <?php if (has_post_thumbnail()): the_post_thumbnail(); ?>
                            <?php else: echo wp_get_attachment_image(1032601); endif; ?>
                        </a>
                    </div>
                    <div class="info col-12 col-lg-8">
                        <div class="post-type">
                            <h5><?php echo $post_type_name ?></h5>
                        </div>
                        <h4><a href="<?php echo get_permalink(); ?>"><?php echo $title; ?></a></h4>
                        <div class="meta"><?php echo the_date('M d, Y'); ?></div>
                        <div class="desc"><?php echo $content; ?></div>
                        <?php //echo do_shortcode('[Sassy_Social_Share url="' . get_permalink() . '"]')
                        ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <?php
        // Output pagination links
        $total_posts = $swp_query->found_posts;
        $current_page = max(1, $paged);
        $start_post = min(($current_page - 1) * $posts_per_page + 1, $total_posts);
        $end_post = min($current_page * $posts_per_page, $total_posts);

        $pagination .= '<div class="pagination">';
        $pagination .= '<div class="pagination-info">';
        $pagination .= sprintf('%d-%d of %d', $start_post, $end_post, $total_posts);
        $pagination .= '</div>';
        // Previous link
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            $pagination .= '<a href="#" data-action="' . $prev_page . '" class="filter-pagination prev"><i class="fa-light fa-chevron-left"></i></a>';
        }

        // Next link
        if ($current_page < $swp_query->max_num_pages) {
            $next_page = $current_page + 1;
            $pagination .= '<a href="#" data-action="' . $next_page . '" class="filter-pagination next"><i class="fa-light fa-chevron-right"></i></a>';
        }

        $pagination .= '</div>';
    } else { ?>
        <p class="mb-0"><?php echo esc_attr_x('Sorry – we can’t find directly what you’re looking for, but we’ve provided further information related to your search.', 'submit button') ?></p>
        <?php
    }
    $data = ob_get_clean();
    $response = array(
        'data' => $data,
        'search_query' => $search_query,
        'pagination' => $pagination,
    );
    wp_send_json($response);
}

add_action('wp_ajax_search_ajax', 'search_ajax');
add_action('wp_ajax_nopriv_search_ajax', 'search_ajax');
