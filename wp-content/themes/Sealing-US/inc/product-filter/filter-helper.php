<?php
add_action('wp', 'custom_taxonomy_redirect');
function custom_taxonomy_redirect() {
	global $wp_query;
	if ( empty( $wp_query->query['product_custom_type'] ) ) {
		return;
	}
	if ( $wp_query->is_404 ) {
		// Search for taxonomy terms that contain the queried string
		global $wpdb;

		$search_term = sanitize_text_field( $wp_query->query['product_custom_type'] );
		$search_term = '%' . $wpdb->esc_like($search_term) . '%';
        $query = $wpdb->prepare(
            "SELECT t.*, tt.* 
            FROM {$wpdb->terms} AS t 
            INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = %s 
            AND t.slug LIKE %s 
            AND tt.count > 0 
            LIMIT 1",
            'product_custom_type',
            $search_term
        );

		$term = $wpdb->get_row( $query );

		if ( $term ) {
			$term_link = get_term_link( (int) $term->term_id, 'product_custom_type' );
			if ( ! is_wp_error( $term_link ) ) {
				wp_redirect( $term_link );
				exit;
			}
		}
	}
}
function get_product_category($taxonomy = 'product_cat', $isParent = 0, $includes = [])
{
    $args = array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'parent' => $isParent,
        'exclude' => [7889],
        'orderby' => 'term_order'

    );
    if (!empty($includes)) {
        $args['exclude'] = [];
        $args['include'] = $includes;
    }

    return get_terms($args);
}

function get_product_category_list()
{
    $taxs = ProductsFilterHelper::getProductLineCategory('product_group', 'family_category', ['8626']);;
    $cat = '';
    if ($taxs) {
        $cat .= '<select name="product_group" id="product_group" class="form-select">';
        $cat .= '<option value="">' . __('Select Category', 'cabling') . '</option>';
        foreach ($taxs as $tax) {
            $cat .= '<option value="' . $tax->term_id . '">' . $tax->name . '</option>';
        }
        $cat .= '</select>';
    }
    return $cat;
}

// Define a function to get product IDs by category ID
function get_product_ids_by_category($taxonomy = '', $term_id = array(), $attributes = array())
{
    $args = array(
        'fields' => 'ids',
        'post_type' => 'product',
        'post_status' => 'publish',
        'numberposts' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        ),
    );

    if (!empty($attributes)) {
        $meta_query = get_meta_query_from_attributes($attributes);
        $args['meta_query'] = $meta_query;
    }

    $posts = get_posts($args);

    return $posts;
}

if (!function_exists('custom_compare')) {
    function custom_compare($a, $b)
    {
        if (!is_string($a) || !is_string($b)) {
            return 0;
        }

        $pattern = '/-?\d+/';
        preg_match_all($pattern, $a, $matches_a);
        preg_match_all($pattern, $b, $matches_b);

        if (empty($matches_a[0]) || empty($matches_b[0])) {
            return 0;
        }

        $max_a = max($matches_a[0]);
        $max_b = max($matches_b[0]);

        return $max_a <=> $max_b;
    }
}


function get_filter_lists($field_group_key = 'group_655f1001b4d9e', $product_ids = []): array
{
    // Generate a cache key based on the function parameters
    $cache_key = 'filter_lists_' . $field_group_key;

    // Add current taxonomy/term to cache key if on taxonomy page
    if (is_tax('product_custom_type')) {
        $term = get_queried_object();
        $cache_key .= '_tax_' . $term->term_id;

        // Include attributes in cache key if present
        $attributes = $_POST['attributes'] ?? [];
        if (!empty($attributes)) {
            $cache_key .= '_attrs_' . md5(serialize($attributes));
        }
    } elseif (is_product()) {
        $cache_key .= '_product_' . get_the_ID();
    }

    // Try to get cached
    $cached_result = get_transient($cache_key);
    if ($cached_result !== false) {
        return $cached_result;
    }

    $fields = acf_get_fields($field_group_key);
    $fieldList = array();
    if ($fields) {
        // Loop through the fields and add them to the $fieldList array
        foreach ($fields as $key => $field) {
            $choices = array();
            $value = '';
            $valueType = '';
            $label = $field['label'];
            if (is_product()) {
                $postId = get_the_ID();
                $value = get_post_meta($postId, $field['name'], true);
            }
            $run_asort = true;
            if (is_tax('product_custom_type')) {
				if (empty($term)){
					$term = get_queried_object();
	            }
                switch ($field['name']) {
                    case 'product_contact_media':
                        $contact_media = get_field('type_contact_media', $term);

                        if ($contact_media) {
                            $choices = array($contact_media => get_the_title($contact_media));
                        }
                        $valueType = 'key';
                        break;
                    case 'product_material':
                        $product_material = get_field('type_material', $term);

                        if ($product_material) {
                            $choices = array($product_material => get_the_title($product_material));
                            $valueType = 'key';
                        }
                        break;
                    case 'product_compound':
                        $choices = get_acf_taxonomy_options('compound_certification');
                        $valueType = 'key';
                        break;
                    case 'product_dash_number':
                    case 'product_dash_number_backup_rings':
                        $choices = get_all_meta_values_cached($field['name'], $product_ids);
                        break;
                    case 'product_colour':
                    case 'product_complance':
                    case 'product_min':
                    case 'product_max':
                        $choices = get_all_meta_values_cached($field['name'], $product_ids);
                        break;
                    case 'product_type':
                        $choices = ['Standard Size'];
                        break;
                    default:
                        if ($field['type'] === 'post_object') {
                            $choices = get_acf_post_options($field['post_type'], $product_ids);
                            $valueType = 'key';
                        } elseif (!empty($field['choices'])) {
                            $choices = get_all_meta_values_cached($field['name'], $product_ids);
                            $valueType = 'key';
                        } else {
                            $choices = get_all_meta_values_cached($field['name'], $product_ids);
                        }
                        asort($choices);
                        break;
                }
            }
			else {
                if ($field['name'] === 'product_compound') {
                    $choices = get_acf_taxonomy_options('compound_certification');
                    $valueType = 'key';
                } elseif ($field['name'] === 'product_type') {
                    $choices = $field['choices'];
                    $valueType = 'key';
                } elseif ($field['type'] === 'post_object') {
                    $choices = get_acf_post_options($field['post_type']);
                    $valueType = 'key';
                } elseif ($field['name'] === 'product_min') {
                    $choices = get_all_meta_values_cached($field['name']);
                    usort($choices, 'custom_compare');
                    $run_asort = false;
                } elseif ($field['name'] === 'product_max') {
                    $choices = get_all_meta_values_cached($field['name']);
                    usort($choices, 'custom_compare');
                    $run_asort = false;
                } elseif (!empty($field['choices'])) {
                    $choices = get_all_meta_values_cached($field['name']);
                } else {
                    $choices = get_all_meta_values_cached($field['name']);
                }
                if ($run_asort) {
                    asort($choices);
                }
            }

            $name = empty($field['name']) ? $key : $field['name'];

			$fieldList[$name] = array(
                'label' => $label,
                'multiple' => $field['multiple'] ?? 0,
                'type' => empty($field['multiple']) ? 'radio' : 'checkbox',
                'field_type' => $field['type'],
                'choices' => $choices,
                'valueType' => $valueType,
                'value' => $value
            );
        }
    }

    // Cache the result for 7 days
    set_transient($cache_key, $fieldList, 7 * 24 * HOUR_IN_SECONDS);

    return $fieldList;
}

/**
 * Helper function to clear filter lists cache and related ACF field group cache
 */
function clear_filter_lists_cache(): void
{
	global $wpdb;

	// Clear filter lists transients
	$wpdb->query(
		"DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_filter_lists_%'"
	);
}

// Add hooks to clear cache when relevant content is updated
add_action('acf/save_post', 'clear_filter_lists_cache');
add_action('acf/update_field_group', 'clear_filter_lists_cache');
add_action('acf/update_field', 'clear_filter_lists_cache');

function get_all_meta_values_cached($meta_key, array $post_ids = []) {
	$cache_key = 'meta_values_' . md5($meta_key . serialize($post_ids));

	$cached_data = get_transient($cache_key);
	if (false !== $cached_data) {
		return $cached_data;
	}

	$acf_field = acf_get_field($meta_key);
	global $wpdb;

	$sql = "SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s";

	if (!empty($post_ids)) {
		$placeholders = implode(', ', array_fill(0, count($post_ids), '%s'));
		$sql .= " AND post_id IN ($placeholders) ";
	}

	$sql .= " ORDER BY meta_value";

	$values = $wpdb->get_col(
		$wpdb->prepare(
			$sql,
			$meta_key,
			...$post_ids
		)
	);

	if (!empty($values) && $acf_field['type'] === 'checkbox') {
		$new_values = array();
		foreach ($values as $value) {
			if (empty($value)) {
				continue;
			}
			$unserializedData = unserialize($value);

			if ($unserializedData === false) {
				continue;
			} else {
				foreach ($unserializedData as $val) {
					if (in_array($val, $new_values)) {
						continue;
					}
					$new_values[] = $val;
				}
			}
		}
		$values = $new_values;
	}
	sort($values);

	// Cache the results
	set_transient($cache_key, $values, 24 * HOUR_IN_SECONDS);

	return $values;
}

/**
 * Helper function to clear the meta values cache
 */
function clear_meta_values_cache() {
	global $wpdb;

	// Delete all transients that match our prefix
	$wpdb->query(
		"DELETE FROM $wpdb->options 
        WHERE option_name LIKE '_transient_meta_values_%' 
        OR option_name LIKE '_transient_timeout_meta_values_%'"
	);
}

// Add hooks to clear cache when post meta is modified
add_action('updated_post_meta', 'clear_meta_values_cache');
add_action('deleted_post_meta', 'clear_meta_values_cache');
add_action('added_post_meta', 'clear_meta_values_cache');

function get_post_options_wpdb($post_types = [], $product_ids = []) {
    global $wpdb;

    $post_types = array_map('esc_sql', $post_types);
    $post_types_in = "'" . implode("','", $post_types) . "'";

    $query = "SELECT ID, post_title 
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ({$post_types_in})";

    if (!empty($product_ids)) {
        $product_ids = array_map('absint', $product_ids);
        $ids_in = implode(',', $product_ids);
        $query .= " AND ID IN ({$ids_in})";
    }

    $query .= " ORDER BY post_title ASC LIMIT 1000";

    $results = $wpdb->get_results($query);
    $list = [];

    foreach ($results as $result) {
        $list[$result->ID] = $result->post_title;
    }

    return $list;
}

function get_acf_post_options($post_types = [], $product_ids = [])
{
	$cache_key = 'acf_post_options_' . md5(json_encode($post_types) . serialize($product_ids));

	$cached_data = get_transient($cache_key);

	if (false !== $cached_data) {
		return $cached_data;
	}

	$results = get_post_options_wpdb($post_types, $product_ids);

	// Store the data in cache for 12 hours.
	set_transient($cache_key, $results, 24 * HOUR_IN_SECONDS);

	return $results;
}
/**
 * Helper function to clear the cache when posts are updated
 */
function clear_acf_post_options_cache() {
    global $wpdb;

    // Delete all transients that match our prefix
    $wpdb->query(
        "DELETE FROM $wpdb->options 
        WHERE option_name LIKE '_transient_acf_post_options_%' 
        OR option_name LIKE '_transient_timeout_acf_post_options_%'"
    );
}

// Add hooks to clear cache when posts are modified
add_action('save_post', 'clear_acf_post_options_cache');
add_action('deleted_post', 'clear_acf_post_options_cache');


/**
 * Retrieves taxonomy options based on Advanced Custom Fields (ACF) criteria.
 *
 * Fetches and caches taxonomy terms based on the provided taxonomy type and optional
 * filter criteria from the request. Utilizes WordPress transients for caching.
 *
 * @param string $taxonomy The taxonomy to retrieve terms for. Defaults to an empty string.
 *
 * @return array            An associative array of term IDs and term names.
 */
function get_acf_taxonomy_options($taxonomy = ''): array
{
	$cache_key = 'acf_tax_' . $taxonomy;
	if (!empty($_REQUEST['certification-compound'])) {
		$cache_key .= '_' . sanitize_key($_REQUEST['certification-compound']);
	}

	$list = get_transient($cache_key);

	if ($list === false) {
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'parent' => 0,
			'orderby' => 'term_order'
		);

		if (!empty($_REQUEST['certification-compound'])) {
			$args['slug'] = $_REQUEST['certification-compound'];
		}

		$taxonomies = get_terms($args);
		$list = [];

		if ($taxonomies) {
			foreach ($taxonomies as $post) {
				$list[$post->term_id] = $post->name;
			}
		}

		// Cache the result for 24 hours
		set_transient($cache_key, $list, 24 * HOUR_IN_SECONDS);
	}

	return $list;
}

// Add these action hooks to clear the cache when terms are modified
add_action('edited_term', 'clear_acf_taxonomy_cache', 10, 3);
add_action('created_term', 'clear_acf_taxonomy_cache', 10, 3);

function clear_acf_taxonomy_cache($term_id, $tt_id, $taxonomy) {
	$cache_key = 'acf_tax_' . $taxonomy;
	delete_transient($cache_key);
}

function redirect_on_product_type()
{
    if (is_tax('product_custom_type')) {
        global $wp_query;

        if (empty($_REQUEST['_wpnonce']) && $wp_query->found_posts === 1) {
            $products = $wp_query->get_posts();
            $productLink = get_the_permalink($products[0]);
			if (isset($_REQUEST['data-filter'])) {
				$productLink = add_query_arg( 'data-history', $_REQUEST['data-filter'], $productLink );
			}
            wp_redirect($productLink);
            exit();
        }
    }
}

add_action('template_redirect', 'redirect_on_product_type');

function cabling_change_product_query($query)
{
    if ((is_tax('product_cat') || is_tax('compound_cat') || is_tax('product_custom_type'))) {
        $paged = $query->get('paged');
        if (isset($_REQUEST['data-filter'])) {
            $data = json_decode(base64_decode($_REQUEST['data-filter']), true);
            $attributes = $data['attributes'] ?? [];
        } elseif (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'product-category-filter') && !empty($_POST['attributes'])) {
            $attributes = $_POST['attributes'];
        } else {
            return $query;
        }
		if (!empty($data['categoryType'])){
			$attributes['categoryType'] = $data['categoryType'];
			$query->set('skuType', $data['categoryType']);
		}

        $paged = $_POST['paged'] ?? $paged;

        $custom_filter = $attributes;
        $old_meta_query = $query->get('meta_query');
        if (!empty($attributes['product_compound'])) {
            $attributes['product_compound'] = get_compound_product($attributes['product_compound']);
        }
        if (!empty($attributes['product_compound_single'])) {
            if (empty($attributes['product_compound'])) {
                $attributes['product_compound'] = $attributes['product_compound_single'];
            } else {
                $attributes['product_compound'] = array_merge($attributes['product_compound'], $attributes['product_compound_single']);
            }

            unset($attributes['product_compound_single']);
        }
        unset($attributes['group-type']);
        $meta_query = get_meta_query_from_attributes($attributes);

        $query->set('meta_query', array_merge($old_meta_query, $meta_query));
        $query->set('orderby', 'meta_value');
        $query->set('meta_key', 'product_dash_number');
        $query->set('order', 'ASC');
        $query->set('paged', $paged);
        $query->set('custom_filter', $custom_filter);
    }
    return $query;
}

add_action('woocommerce_product_query', 'cabling_change_product_query');

/**
 * @param $attributes
 * @return array
 */
function get_meta_query_from_attributes($attributes): array
{
    $meta_query['relation'] = 'AND';

    foreach ($attributes as $meta_key => $meta_values) {
        if (empty($meta_values)) {
            continue;
        }
        if ($meta_key === 'product_compound' || $meta_key === 'product_dash_number' || $meta_key === 'product_dash_number_backup_rings') {
            $meta_query[] = array(
                'key' => $meta_key,
                'value' => $meta_values,
                'compare' => 'IN'
            );
            continue;
        }
        if ($meta_key === 'compound_certification') {
            continue;
        }
        if ($meta_key === 'categoryType') {
			$meta_query[] = array(
                'key' => 'sku_type',
                'value' => $meta_values,
                'compare' => '='
            );
            continue;
        }
        if (is_array($meta_values) && sizeof($meta_values)) {
            $meta_array = array();
            foreach ($meta_values as $value) {
                if (empty($value)) {
                    continue;
                }
                $query = array(
                    'key' => $meta_key,
                    'value' => $value,
                    'compare' => '='
                );
                if ($meta_key === 'product_contact_media' || $meta_key === 'product_complance' || $meta_key === 'product_colour') {
                    $query['value'] = serialize(strval($value));
                    $query['compare'] = 'LIKE';
                }
                $meta_array[] = $query;
            }

            if (count($meta_array) === 1) {
                $meta_query[] = $meta_array[0];
                continue;
            }

            if (count($meta_array) > 1) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    $meta_array
                );
            }
        } else {
            $meta_query[] = array(
                'key' => $meta_key,
                'value' => $meta_values,
                'compare' => '='
            );
        }
    }
    return $meta_query;
}

function selected_filter($name, $value): bool
{
    $metas = $_REQUEST['attributes'];
    if (isset($metas[$name])) {
        if (is_array($metas[$name]) && in_array($value, $metas[$name])) {
            return true;
        } else if ($metas[$name] == $value) {
            return true;
        }
    }
    return false;
}

function show_product_filter_input_name($slug, $attribute): string
{
    if ($slug === 'product_type') {
        $name = 'name="product_type"';
    } else {
        $name = 'name="attributes[' . $slug . '][]"';
    }
    return $name;
}

/**
 * Clears the cache for available attributes.
 * Call this function when products or their attributes are updated.
 *
 * @param int|array $product_ids Single product ID or array of product IDs
 * @return void
 */
function clear_available_attributes_cache($product_ids): void
{
	if (!is_array($product_ids)) {
		$product_ids = [$product_ids];
	}

	$product_ids = array_filter(array_map('intval', array_unique($product_ids)));

	if (empty($product_ids)) {
		return;
	}

	$cache_key = 'available_attributes_' . md5(serialize($product_ids));
	delete_transient($cache_key);
}
// Clear cache when product meta is updated
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if (get_post_type($post_id) === 'product') {
        clear_available_attributes_cache($post_id);
    }
}, 10, 4);

// Clear cache when product is saved
add_action('save_post_product', function($post_id) {
    clear_available_attributes_cache($post_id);
});

// Clear cache when product is deleted
add_action('delete_post', function($post_id) {
    if (get_post_type($post_id) === 'product') {
        clear_available_attributes_cache($post_id);
    }
});

function show_product_filter_input_value($attribute, $value)
{
    if ($attribute === 'product_dash_number') {
        return str_replace('AS', '', $value);
    }
    return $value;
}



function get_product_filter_link($isBack = false): string
{
    if (isset($_POST['attributes'])) {
        $attributes = array(
            'attributes' => $_POST['attributes'],
            'paged' => $_POST['paged'] ?? 1,
        );
        $data = base64_encode(json_encode($attributes));
    } elseif (isset($_GET['data-filter'])) {
        $data = $_GET['data-filter'];
    } else {
        $data = '';
    }
    if ($isBack) {
        $link = home_url('/products-and-services');
        $history = $data;
    } else {
        $previous_link = add_query_arg('data-filter', $data, get_term_link(get_queried_object()));
        $link = get_the_permalink();
        $history = base64_encode($previous_link);
    }
    return add_query_arg('data-history', $history, $link);
}

function checkFilterHasSize($attributes): bool
{
    $size = array(
        'nominal_size_id',
        'nominal_size_od',
        'nominal_size_width',
        'inches_id',
        'inches_od',
        'inches_id_tol',
        'inches_width',
        'inches_width_tol',
        'milimeters_id',
        'milimeters_od',
        'milimeters_width',
        'milimeters_width_tol',
        'inches_id_backup-ring',
	    'inches_t_backup-ring',
	    'inches_width_backup-ring',
    );
    foreach ($attributes as $key => $attribute) {
        if (in_array($key, $size) && !empty($attribute)) {
            return true;
        }
    }

    return false;
}

/**
 * Retrieves and organizes filter size values
 */
function get_filter_sizes_values(array $meta_keys) {
	$cache_key = 'meta_values_bulk_' . md5(serialize($meta_keys));

	$cached_data = get_transient($cache_key);
	if (false !== $cached_data) {
		return $cached_data;
	}

	global $wpdb;

	$meta_keys_placeholders = implode(', ', array_fill(0, count($meta_keys), '%s'));

	$sql = "SELECT DISTINCT meta_key, meta_value 
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ($meta_keys_placeholders)
            ";

	$query_params = $meta_keys;

	$results = $wpdb->get_results(
		$wpdb->prepare($sql, ...$query_params),
		ARRAY_A
	);

	$values = array_fill_keys($meta_keys, []);
	$values = array_reduce($results, function($carry, $row) {
		if (!in_array($row['meta_value'], $carry[$row['meta_key']]) && !empty($row['meta_value'])) {
			$carry[$row['meta_key']][] = $row['meta_value'];
		}
		return $carry;
	}, $values);

	foreach ($values as &$value_array) {
        sort($value_array);
    }
	unset($value_array);

	set_transient($cache_key, $values, 7 * 24 * HOUR_IN_SECONDS);

	return $values;
}
