<?php

class ProductsFilterHelper {
	// SKU type constants
	public const SKU_PRODUCT = 'PRODUCT';
	public const SKU_REPLACEMENTS = [
       'COMPONENT' => 'COMPONENT',
       'REPLACEMENT' => 'REPLACEMENT',
       'RUBBER' => 'RUBBER',
       'RAMS' => 'RAMS',
   ];

	/**
	 * Get translatable labels for SKU types
	 */
	public static function getSkuTypeLabel($type) {
       $labels = [
           self::SKU_PRODUCT => __('Assembly', 'cabling'),
           self::SKU_REPLACEMENTS['COMPONENT'] => __('Component', 'cabling'),
           self::SKU_REPLACEMENTS['REPLACEMENT'] => __('Replacement', 'cabling'),
           self::SKU_REPLACEMENTS['RUBBER'] => __('Rubber', 'cabling'),
           self::SKU_REPLACEMENTS['RAMS'] => __('RAMS', 'cabling'),
       ];
       return $labels[$type] ?? $type;
   }

	/**
	 * Check if the filter contains size-related attributes
	 *
	 * @param array $attributes
	 *
	 * @return bool
	 */
	public static function hasFilterSize( array $attributes ): bool {
		$sizeAttributes = [ 'nominal_size_id', 'nominal_size_od', 'nominal_size_width' ];
		foreach ( $sizeAttributes as $size ) {
			if ( ! empty( $attributes[ $size ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Clean up nominal sizes from attributes array
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public static function cleanupNominalSizes( array $attributes ): array {
		$nominalSizes = [ 'nominal_size_id', 'nominal_size_od', 'nominal_size_width' ];
		foreach ( $nominalSizes as $size ) {
			if ( empty( $attributes[ $size ] ) ) {
				unset( $attributes[ $size ] );
			}
		}

		return $attributes;
	}

	/**
	 * Get terms IDs by product and taxonomy
	 */
	public static function getTermsIdsByProduct( array $productIds, string $taxonomy = 'product_group' ): array {

		sort( $productIds );
		$cache_key      = 'filter_terms_' . md5( $taxonomy . '_' . implode( '_', $productIds ) );
		$cached_results = get_transient( $cache_key );

		if ( false !== $cached_results ) {
			return $cached_results;
		}

		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $productIds ), '%d' ) );

		$sql = $wpdb->prepare( "
	        SELECT DISTINCT tt.term_id
	        FROM {$wpdb->term_taxonomy} AS tt
	        LEFT JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
	        WHERE tt.taxonomy = %s AND tr.object_id IN ({$placeholders})
	    ", $taxonomy, ...$productIds );

		$results = $wpdb->get_results( $sql );

		if ( $wpdb->last_error ) {
			error_log( 'Database error: ' . $wpdb->last_error );

			return [];
		}

		$term_ids = $results ? wp_list_pluck( $results, 'term_id' ) : [];

		// Cache the results for 1 day
		set_transient( $cache_key, $term_ids, DAY_IN_SECONDS );

		return $term_ids;
	}

	/**
	 * Retrieves available attributes for given product IDs with caching.
	 */
	public static function getAvailableAttributes( array $product_ids , bool $isSurfaceEquipment = false): array {
		if ( empty( $product_ids ) ) {
			return [];
		}

		$product_ids = array_filter( array_map( 'intval', array_unique( $product_ids ) ) );

		if ( empty( $product_ids ) ) {
			return [];
		}

		// Create a cache key based on the product IDs
		$cache_key      = 'available_attributes_' . md5( serialize( $product_ids ) );
		$cache_duration = 24 * HOUR_IN_SECONDS;

		// Try to get cached data first
		$cached_result = get_transient( $cache_key );
		if ( false !== $cached_result ) {
			return $cached_result;
		}

		global $wpdb;

		if ($isSurfaceEquipment){
			$meta_keys = [
				'surface_rubber_type',
				//'surface_assembly_kit',
				'surface_thread_up',
				'surface_thread_down',
				'surface_pressure_rating',
				'surface_line_rod_size',
				'surface_minimum_vertical_id',
			];
		} else {
			$meta_keys = [
				'inches_id',
				'inches_width',
				'inches_od',
				'milimeters_id',
				'milimeters_od',
				'milimeters_width',
				'product_dash_number',
				'product_contact_media',
				'product_min',
				'product_max',
				'product_colour',
				'product_compound',
				'product_complance',
				'product_material',
				'product_hardness',
				'inches_id_backup-ring',
				'inches_t_backup-ring',
				'inches_width_backup-ring',
			];
		}

		$post_ids_placeholder  = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );
		$meta_key_placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );

		try {
			$query = $wpdb->prepare(
				"SELECT meta_key, meta_value
            FROM $wpdb->postmeta
            WHERE post_id IN ($post_ids_placeholder)
            AND meta_key IN ($meta_key_placeholders)
            AND meta_value IS NOT NULL
            AND meta_value != ''
            AND meta_value != 'null'",
				[ ...$product_ids, ...$meta_keys ]
			);

			$meta_values = $wpdb->get_results( $query, ARRAY_A );

			if ( $wpdb->last_error ) {
				return [];
			}

			$result_metas = [];

			foreach ( $meta_values as $meta ) {
				$meta_key   = $meta['meta_key'];
				$meta_value = $meta['meta_value'];

				$result_metas[ $meta_key ] ??= [];

				$values = @unserialize( $meta_value );
				$values = ( $values !== false ) ? (array) $values : [ $meta_value ];

				$result_metas[ $meta_key ] = array_unique(
					array_merge( $result_metas[ $meta_key ], $values )
				);
			}

			// Cache the results
			set_transient( $cache_key, $result_metas, $cache_duration );

			return $result_metas;
		} catch ( Exception $e ) {
			error_log( "Error in get_available_attributes: " . $e->getMessage() );

			return [];
		}
	}

	public static function searchProductByMeta( array $metas, $group, array $surfaceIds, array $filterTaxonomy = [] ): array {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
			)
		);

		$args['tax_query'] = array();

		if ( ! empty( $group ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_group',
				'field'    => 'term_id',
				'terms'    => $group,
			);
		}

		if ( ! empty( $surfaceIds ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_line',
				'field'    => 'term_id',
				'terms'    => $surfaceIds,
			);
		}

		if ( ! empty( $filterTaxonomy['type'] ) && ! empty( $filterTaxonomy['id'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $filterTaxonomy['type'],
				'field'    => 'term_id',
				'terms'    => [$filterTaxonomy['id']],
			);
		}

		foreach ( $metas as $meta_key => $meta_values ) {
			if ( $meta_key === 'compound_certification' ) {
				continue;
			}
			if ( empty( $meta_values ) ) {
				continue;
			}
			if ( is_array( $meta_values ) && sizeof( $meta_values ) ) {
				$meta_array = array(
					'relation' => 'OR',
				);
				if ( $meta_key === 'product_compound' || $meta_key === 'product_dash_number' || $meta_key === 'product_dash_number_backup_rings' ) {
					$meta_array[] = array(
						'key'     => $meta_key,
						'value'   => $meta_values,
						'compare' => 'IN',
					);
				} else {
					foreach ( $meta_values as $value ) {
						$meta_array[] = array(
							'key'     => $meta_key,
							'value'   => $value,
							'compare' => 'LIKE'
						);
					}
				}

				$args['meta_query'][] = $meta_array;
			} else {
				$args['meta_query'][] = array(
					'key'     => $meta_key,
					'value'   => $meta_values,
					'compare' => '=',
				);
			}
		}

		$posts = get_posts( $args );

		return $posts;
	}

	public static function getProductCustomType( array $meta_query, $returnId = false, $includes = [] ) {
		$args = array(
			'taxonomy'   => 'product_custom_type',
			'hide_empty' => false,
			'include'    => $includes,
			'meta_query' => array(),
		);

		if ( ! empty( $meta_query ) ) {
			foreach ( $meta_query as $query ) {
				if ( empty( $query ) ) {
					continue;
				}
				foreach ( $query as $meta_key => $meta_values ) {
					$args['meta_query'][] = array(
						'key'     => $meta_key,
						'value'   => $meta_values['value'],
						'compare' => $meta_values['compare'],
					);
				}
			}
		}

		$terms = get_terms( $args );

		return $returnId ? wp_list_pluck( $terms, 'term_id' ) : $terms;
	}

	public static function getCompoundProduct( $term_ids ): array {
		global $wpdb;

		$term_ids = is_array( $term_ids ) ? $term_ids : [ $term_ids ];

		$cache_key = 'compound_products_' . md5( serialize( $term_ids ) );

		$results = get_transient( $cache_key );

		if ( $results === false ) {
			$sql = $wpdb->prepare(
				"SELECT DISTINCT p.ID 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE p.post_type = 'compound'
            AND p.post_status = 'publish'
            AND tt.taxonomy = 'compound_certification'
            AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $term_ids ), '%d' ) ) . ")",
				...$term_ids
			);

			$results = $wpdb->get_col( $sql );

			// Cache the results for 1 day
			set_transient( $cache_key, $results, DAY_IN_SECONDS );
		}

		return $results ?: [];
	}

	public static function getProductLineCategory( $taxonomy = '', $meta_key = '', $meta_values = array(), $returnId = false, $includes = array() )
	{
		global $wpdb;

		$cache_duration = 24 * HOUR_IN_SECONDS;

	    $transient_key = 'get_product_line_category_' . md5( serialize( func_get_args() ) );

	    $cached_results = get_transient( $transient_key );

	    if ( false !== $cached_results ) {
	        // Return cached results if available
	        return $returnId ? wp_list_pluck( $cached_results, 'term_id' ) : $cached_results;
	    }

		$taxonomy = sanitize_text_field( $taxonomy );
		$meta_key = sanitize_text_field( $meta_key );

		$query = "
	        SELECT t.term_id, t.name, t.slug
	        FROM {$wpdb->terms} AS t
	        INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
	    ";

		if ( ! empty( $meta_key ) && ! empty( $meta_values ) ) {
			$query .= "
	            INNER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id
	        ";
		}

		$where = [];
		$params = [];

		// Filter by taxonomy
		if ( ! empty( $taxonomy ) ) {
			$where[] = 'tt.taxonomy = %s';
			$params[] = $taxonomy;
		}

		if ( ! empty( $includes ) ) {
			$includes = array_map( 'intval', $includes );
			$placeholders = implode( ', ', array_fill( 0, count( $includes ), '%d' ) );
			$where[] = "t.term_id IN ($placeholders)";
			$params = array_merge( $params, $includes );
		}

		if ( ! empty( $meta_key ) && ! empty( $meta_values ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $meta_values ), '%s' ) );
			$where[] = "tm.meta_key = %s AND tm.meta_value IN ($placeholders)";
			$params[] = $meta_key;
			$params = array_merge( $params, $meta_values );
		}

		if ( ! empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		$query .= ' GROUP BY t.term_id';
	    $query .= ' ORDER BY t.name ASC';

		$prepared_query = $wpdb->prepare( $query, $params );
		$results = $wpdb->get_results( $prepared_query );

		// Set the transient cached
	    set_transient( $transient_key, $results, $cache_duration );


		if ( $returnId ) {
			return wp_list_pluck( $results, 'term_id' );
		}

		return $results;
	}

    /**
     * Clear all transient caches used in product filtering
     */
    public static function clearTransientCaches(): int {
        global $wpdb;

        // List of cache key prefixes to clear
        $key_prefixes = array(
            'available_attributes_',
            'filter_terms_',
            'compound_products_',
            'get_product_line_category_',
            'filter_lists_',
            'meta_values_',
            'acf_post_options_',
            'acf_tax_',
            'meta_values_bulk_',
        );

        $deleted = 0;

        // Loop through each prefix and delete matching transients
        foreach ($key_prefixes as $prefix) {
            // SQL LIKE query to find transients with this prefix
            $transients = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM $wpdb->options 
                WHERE option_name LIKE %s OR option_name LIKE %s",
                    '_transient_' . $prefix . '%',
                    '_transient_timeout_' . $prefix . '%'
                )
            );

            // Delete each found transient
            foreach ($transients as $transient) {
                if (strpos($transient, '_transient_timeout_') === 0) {
                    $transient_name = substr($transient, strlen('_transient_timeout_'));
                } else {
                    $transient_name = substr($transient, strlen('_transient_'));
                }

                if (delete_transient($transient_name)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

	public static function taxonomyProductIdList( $productTypeId = 0, $sku_type = [] ) {
		global $wpdb;

		$term_id = intval( $productTypeId );
		$default_types = ProductsFilterHelper::getSkuTypes();
		$sku_types = !empty( $sku_type ) ? (array) $sku_type : $default_types;
		$sku_type_placeholders = implode( ',', array_fill( 0, count( $sku_types ), '%s' ) );
		$sql = "
	    SELECT DISTINCT p.ID
	    FROM {$wpdb->posts} p
	    JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
	    JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
	    JOIN {$wpdb->postmeta} sku ON p.ID = sku.post_id AND sku.meta_key = 'sku_type'
	    WHERE p.post_type = 'product'
	      AND p.post_status = 'publish'
	      AND tt.taxonomy = 'product_custom_type'
	      AND tt.term_id = %d
	      AND sku.meta_value IN ($sku_type_placeholders)
	";
		$params = array_merge( [ $term_id ], $sku_types );
		return $wpdb->get_col( $wpdb->prepare( $sql, ...$params ) );
	}
	public static function getProductSkuType(): string {
		return self::SKU_PRODUCT;
	}
	public static function getReplacementSkuType(): array {
		return array_keys(self::SKU_REPLACEMENTS);
	}
	public static function getSkuTypes(): array {
		return array_merge(
			[self::SKU_PRODUCT],
			array_keys(self::SKU_REPLACEMENTS)
		);
	}
}
