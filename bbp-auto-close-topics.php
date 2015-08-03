<?php

/**
 * Plugin Name: BBP Auto-Close Topics
 * Plugin URI:  https://github.com/thebrandonallen/bbp-auto-close-topics
 * Description: bbPress plugin to auto-close topics after a specified time period.
 * Author:      Brandon Allen
 * Author URI:  https://github.com/thebrandonallen/
 * Version:     0.1.1
 * Text Domain: tba-bbp-auto-close-topics
 * Domain Path: /languages/
 * License:     GPLv2 or later (license.txt)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hook into 'the_posts' and check the topic status on single topic pages. If
 * the topic is older than the specified time period, set the topic status to
 * closed if it's not already closed.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @param array $posts Array of post objects.
 * @param object $query WP_Query object.
 * @uses WP_Query::is_singular() To determine if we're on a single topic page.
 * @uses bbp_get_topic_post_type() To get the topic post type.
 * @uses bbp_get_closed_status_id() To get the topic status id.
 * @uses tba_bbp_auto_close_topics() To get auto-close option.
 * @uses tba_bbp_auto_close_age() To get the auto-close age.
 * @uses bbp_get_topic_id() To get the topic id.
 * @uses bbp_get_topic_last_active_time() To get the topic's last active time.
 *
 * @return array $posts
 */
function tba_bbp_auto_close_topics_the_posts( $posts, $query ) {
	if ( empty( $posts ) || ! $query->is_singular() ) {
		return $posts;
	}

	// Are we checking a topic?
	if ( $posts[0]->post_type !== bbp_get_topic_post_type() ) {
		return $posts;
	}

	// Are we already closed?
	if ( $posts[0]->post_status === bbp_get_closed_status_id() ) {
		return $posts;
	}

	// Should we auto-close?
	if ( ! tba_bbp_auto_close_topics() ) {
		return $posts;
	}

	// Do we have a valid topic age?
	$days_old = tba_bbp_auto_close_age();
	if ( empty( $days_old ) ) {
		return $posts;
	}

	// Validate the topic id, and get the topic last active time
	$topic_id    = bbp_get_topic_id( $posts[0]->ID );
	$last_active = get_post_field( 'post_date', bbp_get_topic_last_active_id( $topic_id ) );

	// Check the topic age, and close if needed
	if ( time() - strtotime( $last_active ) > ( $days_old * DAY_IN_SECONDS ) ) {
		$posts[0]->post_status = bbp_get_closed_status_id();
	}

	return $posts;
}

/**
 * Hook into 'the_posts' and check the topic status on single topic pages. If
 * the topic is older than the specified time period, set the topic status to
 * closed if it's not already closed.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @param string $status Status of supplied topic id.
 * @param int $topic_id The topic id.
 * @uses bbp_get_closed_status_id() To get the topic status id.
 * @uses bbp_get_topic_id() To get the topic id.
 * @uses bbp_is_topic() To verify we're checking a topic.
 * @uses tba_bbp_auto_close_topics() To get auto-close option.
 * @uses tba_bbp_auto_close_age() To get the auto-close age.
 * @uses bbp_get_topic_last_active_time() To get the topic's last active time.
 *
 * @return string $status
 */
function tba_bbp_auto_close_topics_topic_status( $status, $topic_id ) {
	// Bail if topic is already closed
	if ( $status === bbp_get_closed_status_id() ) {
		return $status;
	}

	// Validate topic id
	$topic_id = bbp_get_topic_id( $topic_id );

	// Are we checking a topic?
	if ( ! bbp_is_topic( $topic_id ) ) {
		return $status;
	}

	// Should we auto-close?
	if ( ! tba_bbp_auto_close_topics() ) {
		return $status;
	}

	// Do we have a valid topic age?
	$days_old = tba_bbp_auto_close_age();
	if ( empty( $days_old ) ) {
		return $status;
	}

	// Get the topic's last active time
	$last_active = get_post_field( 'post_date', bbp_get_topic_last_active_id( $topic_id ) );

	// Check the topic age, and close if needed
	if ( time() - strtotime( $last_active ) > ( $days_old * DAY_IN_SECONDS ) ) {
		$status = bbp_get_closed_status_id();
	}

	return $status;
}

/**
 * Add our settings fields to the bbPress Forum options page.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @param array $settings (default: array())
 *
 * @return array $settings
 */
function tba_bbp_auto_close_topics_settings_fields( $settings = array() ) {
	// Add the auto-close topics option and callback to the bbPress settings array
	$settings['bbp_settings_features']['_tba_bbp_auto_close_topics'] = array(
		'title'             => __( 'Auto-Close Topics', 'tba-bbp-auto-close-topics' ),
		'callback'          => 'tba_bbp_admin_setting_callback_auto_close_topics',
		'sanitize_callback' => 'intval',
		'args'              => array()
	);

	// Add the auto-close age option and callback to the bbPress settings array
	$settings['bbp_settings_features']['_tba_bbp_auto_close_age'] = array(
		'sanitize_callback' => 'intval',
		'args'              => array()
	);

	return $settings;
}

/**
 * Output HTML for auto-close topics admin setting.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @uses apply_filters() To call the 'tba_bbp_auto_close_age_options' hook.
 * @uses tba_bbp_auto_close_age() To get the topic age option.
 * @uses tba_bbp_auto_close_topics()
 * @uses checked()
 * @uses bbp_maybe_admin_setting_disabled()
 * @uses esc_html_e() To escape and echo translated text.
 * @uses esc_attr()
 * @uses selected()
 * @uses esc_html()
 */
function tba_bbp_admin_setting_callback_auto_close_topics() {

	// Set topic age options
	$options = (array) apply_filters( 'tba_bbp_auto_close_age_options', array(
		'30'  => __( '30 days',  'tba-bbp-auto-close-topics' ),
		'45'  => __( '45 days',  'tba-bbp-auto-close-topics' ),
		'60'  => __( '60 days',  'tba-bbp-auto-close-topics' ),
		'90'  => __( '3 months', 'tba-bbp-auto-close-topics' ),
		'180' => __( '6 months', 'tba-bbp-auto-close-topics' ),
		'365' => __( '1 year',   'tba-bbp-auto-close-topics' ),
	) );

	// Get the current topic age
	$current_age = tba_bbp_auto_close_age();
	?>

	<label for="_tba_bbp_auto_close_topics">
		<input name="_tba_bbp_auto_close_topics" id="_tba_bbp_auto_close_topics" type="checkbox" value="1" <?php checked( tba_bbp_auto_close_topics() ); bbp_maybe_admin_setting_disabled( '_tba_bbp_auto_close_topics' ); ?> />
		<?php esc_html_e( 'Auto-close topics after', 'tba-bbp-auto-close-topics' ); ?>
	</label>
	<label for="_tba_bbp_auto_close_age">
		<select name="_tba_bbp_auto_close_age" id="_tba_bbp_auto_close_age" <?php bbp_maybe_admin_setting_disabled( '_tba_bbp_auto_close_age' ); ?>>
		<?php foreach ( $options as $key => $value ) : ?>

			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $current_age ); ?>><?php echo esc_html( $value ); ?></option>

		<?php endforeach; ?>
		</select>
	</label>

<?php
}

/**
 * Get filtered _tba_bbp_auto_close_topics option.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @param bool $default (default: false)
 * @uses get_option() To get the _tba_bbp_auto_close_topics option.
 * @uses apply_filters() To call the 'tba_bbp_auto_close_topics' hook.
 *
 * @return bool
 */
function tba_bbp_auto_close_topics( $default = false ) {
	return (bool) apply_filters( 'tba_bbp_auto_close_topics', (bool) get_option( '_tba_bbp_auto_close_topics', $default ) );
}

/**
 * Get filtered _tba_bbp_auto_close_age option. Defaults to 365.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @param int $default (default: 365)
 * @uses get_option() To get the '_tba_bbp_auto_close_age' option.
 * @uses apply_filters() To call the 'tba_bbp_auto_close_age' hook.
 *
 * @return int
 */
function tba_bbp_auto_close_age( $default = 365 ) {
	return (int) apply_filters( 'tba_bbp_auto_close_age', (int) get_option( '_tba_bbp_auto_close_age', $default ) );
}

/**
 * Only load our filters after bbPress has loaded.
 *
 * @since 0.1.0
 *
 * @access public
 *
 * @uses add_filter() To load our filters.
 */
function tba_bbp_auto_close_topics_loader() {
	add_filter( 'the_posts', 'tba_bbp_auto_close_topics_the_posts', 10, 2 );
	add_filter( 'bbp_get_topic_status', 'tba_bbp_auto_close_topics_topic_status', 10, 2 );
	add_filter( 'bbp_admin_get_settings_fields', 'tba_bbp_auto_close_topics_settings_fields' );
}
add_action( 'bbp_includes', 'tba_bbp_auto_close_topics_loader' );
