<?php
/**
 * Shared miniOrange notification API logic for contact-type controllers.
 *
 * @package Miniorange_Secure_MCP_Server
 */

namespace MoSMCP\Common\Controllers\Contact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MoSMCP\Common\Controllers\Abstract_Admin_Controller;

/**
 * Class Abstract_Contact_Controller
 *
 * Extends Abstract_Admin_Controller with the miniOrange notification API.
 * Extend this for any controller that dispatches email via the miniOrange API.
 */
abstract class Abstract_Contact_Controller extends Abstract_Admin_Controller {

	protected const NOTIFY_ENDPOINT = 'https://login.xecurify.com/moas/api/notify/send';
	protected const CUSTOMER_KEY    = '16555';
	protected const API_KEY         = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
	protected const TO_EMAIL        = 'aisupport@xecurify.com';
	protected const BCC_EMAIL       = 'info@xecurify.com';

	/**
	 * Sends an email via the miniOrange notification API.
	 *
	 * @param string $from_email Sender / reply-to address.
	 * @param string $subject    Email subject line.
	 * @param string $content    HTML email body.
	 * @param bool   $blocking   Whether to wait for the API response (default true).
	 * @return array|null Decoded response array, or null when $blocking is false.
	 */
	protected static function notify( $from_email, $subject, $content, $blocking = true ) {
		$timestamp = (string) time();
		$hash      = hash( 'sha512', self::CUSTOMER_KEY . $timestamp . self::API_KEY );

		$response = wp_remote_post(
			self::NOTIFY_ENDPOINT,
			array(
				'headers'     => array(
					'Content-Type'  => 'application/json',
					'Customer-Key'  => self::CUSTOMER_KEY,
					'Timestamp'     => $timestamp,
					'Authorization' => $hash,
				),
				'body'        => wp_json_encode(
					array(
						'customerKey' => self::CUSTOMER_KEY,
						'sendEmail'   => true,
						'email'       => array(
							'customerKey' => self::CUSTOMER_KEY,
							'fromEmail'   => $from_email,
							'bccEmail'    => self::BCC_EMAIL,
							'fromName'    => 'miniOrange',
							'toEmail'     => self::TO_EMAIL,
							'toName'      => self::TO_EMAIL,
							'subject'     => $subject,
							'content'     => $content,
						),
					)
				),
				'timeout'     => $blocking ? 20 : 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => $blocking,
				'sslverify'   => true,
			)
		);

		if ( ! $blocking ) {
			return null;
		}

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
