<?php
/*
 * Author: Bob Hutzel
 * Description: Spaced repetition of a user's own posts, for an exercise routine or learning.
 * Domain Path: /languages
 * License: MIT
 * License URI: https://mit-license.org/
 * Plugin Name: Personal Rotation
 * Text Domain: personal-rotation
 * Version: 1.0.0
 */

if (!class_exists("PersonalRotation")) {

	class PersonalRotation {

		public function __construct() {
			add_filter('comments_open', '__return_false', 20, 2);
			add_action('init', array($this, 'init'));
			add_filter('pings_open', '__return_false', 20, 2);
			add_action('pre_get_posts', array($this, 'filter_frontend_posts_by_user'), 0);
			add_action('template_redirect', array($this, 'template_redirect'), 0);
			add_filter('the_content', array($this, 'add_rotation_buttons'));
			add_action('wp_body_open', array($this, 'render_login_reminder'));
		}

		public function init() {
			// Handle the metadata update request
			if (isset($_GET['action']) && $_GET['action'] === 'remove_from_rotation_for_days') {
				$post_id = intval($_GET['post_id']);
				$days = intval($_GET['days']);
				$this->handle_remove_from_rotation_for_days($post_id, $days);
			}

			load_plugin_textdomain('personal-rotation', false, dirname(plugin_basename(__FILE__)) . '/languages');
		}

		public function render_login_reminder() {
			if (!is_user_logged_in() && !is_login()) {
				?>
				<div class="below-title-text">
					<?php echo "<p><a href=\"".wp_login_url()."\">".
					           __('Login to view your posts.', 'personal-rotation').
					           "</a></p>"; ?>
				</div>
				<?php
			}
		}

		/**
		 * Restrict posts to their authors.
		 */
		public function template_redirect()
		{
			if(is_home()) {
				return;
			}
			global $post;
			if (!is_user_logged_in() || intval($post->post_author) != wp_get_current_user()->ID) {
				wp_redirect(home_url());
				exit;
			}
		}

		/**
		 * Filter frontend post queries to show only current user's posts
		 */
		function filter_frontend_posts_by_user($query) {
			if (is_admin()) {
				return;
			}
			$query->set('author', is_user_logged_in() ? get_current_user_id() : 999999);
		}

		public function add_rotation_buttons($content) {
			if (is_single() && in_the_loop() && is_main_query()) {
				$nonce = wp_create_nonce('remove_from_rotation_for_days');

				$rotation_url = esc_url(add_query_arg(array(
					"action" => "remove_from_rotation_for_days",
					"post_id" => get_the_ID(),
					"nonce" => $nonce
				), home_url()));

				$skip_i18n = __('Skip', 'personal-rotation');
				/* translators: %s: Number of days to remove from rotation. */
				$one_day_i18n = _n('%s Day', '%s Days', 1, 'personal-rotation');
				$one_day_i18n = str_replace('%s', number_format_i18n(1), $one_day_i18n);
				$three_days_i18n = _n('%s Day', '%s Days', 3, 'personal-rotation');
				$three_days_i18n = str_replace('%s', number_format_i18n(3), $three_days_i18n);
				$ten_days_i18n = _n('%s Day', '%s Days', 10, 'personal-rotation');
				$ten_days_i18n = str_replace('%s', number_format_i18n(10), $ten_days_i18n);

				$content = <<<EOS
                    <div class="custom-button-wrapper">
                        Days out of rotation:<br />
                        <button class="wp-element-button has-small-font-size" onclick="location.href='$rotation_url&days=0'">$skip_i18n</button>
                        <button class="wp-element-button has-small-font-size" onclick="location.href='$rotation_url&days=1'">$one_day_i18n</button>
                        <button class="wp-element-button has-small-font-size" onclick="location.href='$rotation_url&days=3'">$three_days_i18n</button>
                        <button class="wp-element-button has-small-font-size" onclick="location.href='$rotation_url&days=10'">$ten_days_i18n</button>
                    </div>
                    EOS . $content;
			}
			return $content;
		}

		public function handle_remove_from_rotation_for_days($post_id, $days) {
			$user = wp_get_current_user();
			if (!isset($user->ID)) {
				wp_die("Unauthenticated");
			}

			$post = get_post($post_id);
			if (!$post) {
				wp_die("Invalid post ID");
			}

			if ($days > 0) {
				if ($post->post_author != $user->ID) {
					wp_die("Post access denied");
				}

				$next_date = date('Y-m-d', strtotime("+$days days"));
				update_post_meta($post->ID, 'personal_rotation_next_date', $next_date);
			}

			$next_post = $this->shuffle_next_post_for_user($post_id);
			wp_redirect(get_post_permalink(isset($next_post) ? $next_post : $post));
			exit();
		}

		function shuffle_next_post_for_user($current_post_id) {
			error_log("current_post_id = $current_post_id");
			$candidates = get_posts(array(
				'author' => wp_get_current_user(),
				'exclude' => [$current_post_id],
				'numberposts' => -1,
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'compare' => '<=',
						'key' => 'personal_rotation_next_date',
						'type' => 'DATE',
						'value' => date('Y-m-d')
					),
					array(
						'compare' => 'NOT EXISTS',
						'key' => 'personal_rotation_next_date'
					)
				),
				'post_type' => 'post',
			));

			if (sizeof($candidates) == 0) {
				return get_post(get_the_ID());
			}
			return get_post($candidates[rand(0, sizeof($candidates)-1)]);
		}

	}

}

$personal_rotation = new PersonalRotation();

?>
