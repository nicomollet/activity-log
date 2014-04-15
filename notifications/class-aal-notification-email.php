<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notification_Email extends AAL_Notification_Base {
	
	/**
	 * Store options in a class locally
	 */
	protected $options = array();
	
	public function __construct() {
		parent::__construct();
		
		$this->id = 'email';
		$this->name = __( 'Email', 'aryo-aal' );
		$this->description = __( 'Get notified by Email.', 'aryo-aal' );
	}
	
	public function init() {
		$this->options = array_merge( array(
			'target_email'   => get_option( 'admin_email' ),
//			'message_format' => __( "Hi there!\n\nA notification condition on [sitename] was matched. Here are the details:\n\n[action-details]\n\nSent by ARYO Activity Log", 'aryo-aal' )
		), $this->get_handler_options() );
	}
	
	public function trigger( $args ) {
		$from_email = isset( $this->options['from_email'] ) ? $this->options['from_email'] : '';
		$to_email   = isset( $this->options['to_email'] ) ? $this->options['to_email'] : '';

		// if no from email or to email provided, quit.
		if ( ! ( $from_email || $to_email ) )
			return;

		$format = isset( $this->options['message_format'] ) ? $this->options['message_format'] : '';
		$body = $this->prep_notification_body( $args );
		$site_name = get_bloginfo( 'name' );

		$email_contents = str_replace( array( '[sitename]', '[action-details]' ), array( $site_name, $body ), $format );

		wp_mail(
			$to_email,
			__( 'New notification from Activity Log', 'aryo-aal' ),
			$email_contents,
			array(
				"From: Activity Log @ $site_name <$from_email>"
			)
		);

		error_log( 'AAL: ' . var_export($args, true));

	}
	
	public function settings_fields() {
		$default_email_message = __( "Hi there!\n\nA notification condition on [sitename] was matched. Here are the details:\n\n[action-details]\n\nSent by ARYO Activity Log", 'aryo-aal' );

		$this->add_settings_field_helper( 'from_email', __( 'From Email', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'text_field' ), __( 'The source Email address' ) );
		$this->add_settings_field_helper( 'to_email', __( 'To Email', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'text_field' ), __( 'The Email address notifications will be sent to', 'aryo-aal' ) );
		$this->add_settings_field_helper( 'message_format', __( 'Message', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'textarea_field' ), sprintf( __( 'Customize the message using the following placeholders: %s', 'aryo-aal' ), '[sitename], [action-details]' ), $default_email_message );
	}
	
	public function validate_options( $input ) {
		$output = array();
		$email_fields = array( 'to_email', 'from_email' );

		foreach ( $email_fields as $email_field ) {
			if ( isset( $input[ $email_field ] ) && is_email( $input[ $email_field ] ) )
				$output[ $email_field ] = $input[ $email_field ];
		}

		// email template message
		if ( '' !== $input['message_format'] ) {
			$output['message_format'] = $input['message_format'];
		}

		return $output;
	}
}

// Register this handler, creates an instance of this class when necessary.
aal_register_notification_handler( 'AAL_Notification_Email' );