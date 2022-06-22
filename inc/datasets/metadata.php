<?php
/**
 * Registration and management of the post-meta storage for datasets.
 */

namespace Datavis_Block\Datasets\Metadata;

use Datavis_Block\Datasets;

const META_KEY = 'csv_datasets';

/**
 * Connect namespace functions to actions and hooks.
 */
function bootstrap() : void {
	add_action( 'init', __NAMESPACE__ . '\\register_dataset_meta' );
	add_filter( 'ep_prepare_meta_data', '\\do_not_index_dataset_meta' );
}

/**
 * Register our dataset meta field.
 */
function register_dataset_meta() {
	foreach ( Datasets\get_supported_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			META_KEY,
			[
				'single'       => true,
				'default'      => [],
				'show_in_rest' => false, // Use custom REST routes for management.
			]
		);
	}
}

/**
 * Internal function to fetch the dataset metadata array with some error handling.
 *
 * @param int $post_id Post ID.
 * @return array Datasets array stored in meta.
 */
function get_dataset_meta( int $post_id ) : array {
	$datasets = get_post_meta( $post_id, META_KEY, true );

	if ( empty( $datasets ) || ! is_array( $datasets ) ) {
		return [];
	}

	return $datasets;
}

/**
 * Get array of datasets for a post.
 *
 * @param int $post_id Post ID.
 * @return array Datasets array.
 */
function get_datasets( int $post_id ) : array {
	$datasets = get_dataset_meta( $post_id );

	return array_map(
		function( $dataset_key ) use ( $datasets ) : array {
			return [
				'id'      => $dataset_key,
				'content' => $datasets[ $dataset_key ],
			];
		},
		array_keys( $datasets )
	);
}

/**
 * Get a single dataset for a post.
 *
 * @param int   $post_id     Post ID.
 * @param string $dataset_id ID (filename) of the dataset to return.
 * @return ?array [ id: string, content: string ] array, or null if no match.
 */
function get_dataset( int $post_id, string $dataset_id ) : ?array {
	$datasets = get_dataset_meta( $post_id );

	if ( isset( $datasets[ $dataset_id ] ) && is_string( $datasets[ $dataset_id ] ) ) {
		return [
			'id'      => $dataset_id,
			'content' => $datasets[ $dataset_id ],
		];
	}

	return null;
}

/**
 * Exclude dataset metadata from ElasticPress for indexing and search performance.
 *
 * @param array $meta Metadata to index in EP.
 * @return array Filtered metadata fields.
 */
function do_not_index_dataset_meta( $meta ) {
	foreach( $meta as $key => $value ) {
		if ( preg_match( '/datasets/', $key ) ) {
			unset( $meta[$key] );
		}
	}
	return $meta;
}
