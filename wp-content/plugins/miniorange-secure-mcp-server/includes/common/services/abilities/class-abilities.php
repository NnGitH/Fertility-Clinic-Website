<?php
/**
 * First-party abilities shipped by the plugin: create and update posts.
 *
 * @package Miniorange_Secure_MCP_Server
 */

namespace MoSMCP\Common\Services\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WP_Post;

/**
 * Class Abilities
 *
 * Registers the plugin's content abilities (mosmcp/create-post, mosmcp/update-post).
 */
class Abilities {

	/**
	 * Ability category slug for the plugin's content abilities.
	 */
	const CATEGORY = 'mosmcp-content';

	/**
	 * Post statuses a client may set through these abilities.
	 */
	const ALLOWED_STATUSES = array( 'draft', 'publish' );

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public static function register_categories() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Content', 'miniorange-secure-mcp-server' ),
				'description' => __( 'Abilities that create or update site content.', 'miniorange-secure-mcp-server' ),
			)
		);
	}

	/**
	 * Register the create-post and update-post abilities.
	 *
	 * @return void
	 */
	public static function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$post_summary_schema = array(
			'type'                 => 'object',
			'required'             => array( 'id', 'title', 'status', 'link' ),
			'properties'           => array(
				'id'     => array(
					'type'        => 'integer',
					'description' => __( 'The post ID.', 'miniorange-secure-mcp-server' ),
				),
				'title'  => array(
					'type'        => 'string',
					'description' => __( 'The post title.', 'miniorange-secure-mcp-server' ),
				),
				'status' => array(
					'type'        => 'string',
					'description' => __( 'The post status after the operation.', 'miniorange-secure-mcp-server' ),
				),
				'link'   => array(
					'type'        => 'string',
					'description' => __( 'The permalink to the post.', 'miniorange-secure-mcp-server' ),
				),
			),
			'additionalProperties' => false,
		);

		wp_register_ability(
			'mosmcp/create-post',
			array(
				'label'               => __( 'Create Post', 'miniorange-secure-mcp-server' ),
				'description'         => __( 'Creates a new WordPress post. Defaults to draft; set status to publish to publish it.', 'miniorange-secure-mcp-server' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'required'             => array( 'title' ),
					'properties'           => array(
						'title'   => array(
							'type'        => 'string',
							'description' => __( 'The post title (required).', 'miniorange-secure-mcp-server' ),
						),
						'content' => array(
							'type'        => 'string',
							'description' => __( 'The post body. Basic HTML is allowed.', 'miniorange-secure-mcp-server' ),
						),
						'status'  => array(
							'type'        => 'string',
							'enum'        => self::ALLOWED_STATUSES,
							'default'     => 'draft',
							'description' => __( 'Post status: draft or publish. Defaults to draft.', 'miniorange-secure-mcp-server' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'       => $post_summary_schema,
				'execute_callback'    => array( __CLASS__, 'create_post' ),
				'permission_callback' => array( __CLASS__, 'can_create' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
						// Can publish a post (status=publish), changing publicly visible web content.
						'open_world'  => true,
					),
					// The WordPress capability this ability's permission check requires.
					// Surfaced to the admin UI so the role-ability matrix can flag grants
					// to roles that lack the capability.
					'required_cap' => 'edit_posts',
					'show_in_rest' => false,
				),
			)
		);

		wp_register_ability(
			'mosmcp/update-post',
			array(
				'label'               => __( 'Update Post', 'miniorange-secure-mcp-server' ),
				'description'         => __( 'Updates an existing WordPress post. Provide the post ID and at least one of title, content, or status.', 'miniorange-secure-mcp-server' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'required'             => array( 'id' ),
					'properties'           => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'The ID of the post to update (required).', 'miniorange-secure-mcp-server' ),
						),
						'title'   => array(
							'type'        => 'string',
							'description' => __( 'New post title.', 'miniorange-secure-mcp-server' ),
						),
						'content' => array(
							'type'        => 'string',
							'description' => __( 'New post body. Basic HTML is allowed.', 'miniorange-secure-mcp-server' ),
						),
						'status'  => array(
							'type'        => 'string',
							'enum'        => self::ALLOWED_STATUSES,
							'description' => __( 'New post status: draft or publish.', 'miniorange-secure-mcp-server' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'       => $post_summary_schema,
				'execute_callback'    => array( __CLASS__, 'update_post' ),
				'permission_callback' => array( __CLASS__, 'can_edit' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => true,
						// wp_update_post() fires side effects (revisions, modified time, hooks)
						// on every call, so repeated identical calls are not side-effect-free.
						'idempotent'  => false,
						// Can publish/unpublish a post, changing publicly visible web content.
						'open_world'  => true,
					),
					// The WordPress capability this ability's permission check requires.
					// Surfaced to the admin UI so the role-ability matrix can flag grants
					// to roles that lack the capability.
					'required_cap' => 'edit_posts',
					'show_in_rest' => false,
				),
			)
		);
	}

	/**
	 * @return bool
	 */
	public static function can_create() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * @return bool
	 */
	public static function can_edit() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>|WP_Error
	 */
	public static function create_post( $input = array() ) {
		$input = is_array( $input ) ? $input : array();

		$title = isset( $input['title'] ) ? sanitize_text_field( (string) $input['title'] ) : '';
		if ( '' === $title ) {
			return new WP_Error( 'mosmcp_missing_title', __( 'A post title is required.', 'miniorange-secure-mcp-server' ) );
		}

		$status = isset( $input['status'] ) ? (string) $input['status'] : 'draft';
		if ( ! in_array( $status, self::ALLOWED_STATUSES, true ) ) {
			$status = 'draft';
		}
		if ( 'publish' === $status && ! current_user_can( 'publish_posts' ) ) {
			return new WP_Error( 'mosmcp_cannot_publish', __( 'You are not allowed to publish posts.', 'miniorange-secure-mcp-server' ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => isset( $input['content'] ) ? wp_kses_post( (string) $input['content'] ) : '',
				'post_status'  => $status,
				'post_type'    => 'post',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return self::post_summary( (int) $post_id );
	}

	/**
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>|WP_Error
	 */
	public static function update_post( $input = array() ) {
		$input = is_array( $input ) ? $input : array();

		$post_id = isset( $input['id'] ) ? absint( $input['id'] ) : 0;
		if ( $post_id <= 0 ) {
			return new WP_Error( 'mosmcp_missing_id', __( 'A valid post ID is required.', 'miniorange-secure-mcp-server' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return new WP_Error( 'mosmcp_post_not_found', __( 'No post was found with that ID.', 'miniorange-secure-mcp-server' ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'mosmcp_cannot_edit', __( 'You are not allowed to edit this post.', 'miniorange-secure-mcp-server' ) );
		}

		$update = array( 'ID' => $post_id );

		if ( isset( $input['title'] ) ) {
			$update['post_title'] = sanitize_text_field( (string) $input['title'] );
		}
		if ( isset( $input['content'] ) ) {
			$update['post_content'] = wp_kses_post( (string) $input['content'] );
		}
		if ( isset( $input['status'] ) ) {
			$status = (string) $input['status'];
			if ( ! in_array( $status, self::ALLOWED_STATUSES, true ) ) {
				return new WP_Error( 'mosmcp_invalid_status', __( 'Status must be draft or publish.', 'miniorange-secure-mcp-server' ) );
			}
			if ( 'publish' === $status && ! current_user_can( 'publish_posts' ) ) {
				return new WP_Error( 'mosmcp_cannot_publish', __( 'You are not allowed to publish posts.', 'miniorange-secure-mcp-server' ) );
			}
			$update['post_status'] = $status;
		}

		if ( count( $update ) <= 1 ) {
			return new WP_Error( 'mosmcp_nothing_to_update', __( 'Provide at least one of title, content, or status to update.', 'miniorange-secure-mcp-server' ) );
		}

		$result = wp_update_post( $update, true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return self::post_summary( $post_id );
	}

	/**
	 * @param int $post_id
	 * @return array<string, mixed>
	 */
	private static function post_summary( $post_id ) {
		return array(
			'id'     => $post_id,
			'title'  => get_the_title( $post_id ),
			'status' => (string) get_post_status( $post_id ),
			'link'   => (string) get_permalink( $post_id ),
		);
	}
}
