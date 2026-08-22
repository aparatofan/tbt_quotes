<?php
/**
 * Plugin Name:       The Blue Tree Quotes
 * Description:       Shows each logged-in student a personal welcome and a rotating inspiring quote.
 * Version:           1.0.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            The Blue Tree
 * Text Domain:       tbt-quotes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TBT_Quotes_Plugin {
	private const VERSION = '1.0.3';
	private const SHORTCODE = 'tbt_quote_greeting';
	private const HISTORY_LIMIT = 100;
	private const CURRENT_QUOTE_META = '_tbt_quotes_current_quote_id';
	private const RECENT_QUOTES_META = '_tbt_quotes_recent_quote_ids';

	/**
	 * Cached quote collection.
	 *
	 * @var array<int, array{id:string, topic:string, quote:string, author:string}>|null
	 */
	private $quotes = null;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_login', array( $this, 'select_quote_on_login' ), 10, 2 );
		add_shortcode( self::SHORTCODE, array( $this, 'render_shortcode' ) );
	}

	/**
	 * Registers styling without changing the surrounding Divi row or columns.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'tbt-quotes-fonts',
			'https://fonts.googleapis.com/css2?family=Roboto:wght@400&family=Roboto+Slab:wght@400;700&display=swap',
			array(),
			null
		);

		wp_register_style(
			'tbt-quotes',
			plugins_url( 'assets/css/tbt-quotes.css', __FILE__ ),
			array( 'tbt-quotes-fonts' ),
			self::VERSION
		);
	}

	/**
	 * Picks a fresh quote whenever WordPress completes a login.
	 *
	 * @param string   $user_login Authenticated username.
	 * @param WP_User $user       Authenticated user.
	 * @return void
	 */
	public function select_quote_on_login( $user_login, $user ) {
		if ( $user instanceof WP_User ) {
			$this->assign_new_quote( (int) $user->ID );
		}
	}

	/**
	 * Renders the personal greeting. Logged-out visitors receive no output.
	 *
	 * Available attributes:
	 * - welcome: sentence after "Hi [name]."
	 * - intro: sentence before the quote.
	 * - show_author: "yes" or "no".
	 *
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $attributes = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User || ! $user->exists() ) {
			return '';
		}

		$quote = $this->get_current_quote( (int) $user->ID );
		if ( null === $quote ) {
			$quote = $this->assign_new_quote( (int) $user->ID );
		}

		if ( null === $quote ) {
			return '';
		}

		$attributes = shortcode_atts(
			array(
				'welcome'     => __( 'Nice to see you back on The Blue Tree.', 'tbt-quotes' ),
				'intro'       => __( "Here's a thought for you:", 'tbt-quotes' ),
				'show_author' => 'yes',
			),
			$attributes,
			self::SHORTCODE
		);

		$first_name = $this->get_first_name( $user );
		$show_author = ! in_array(
			strtolower( (string) $attributes['show_author'] ),
			array( 'no', 'false', '0' ),
			true
		);

		if ( ! wp_style_is( 'tbt-quotes', 'registered' ) ) {
			$this->register_assets();
		}
		wp_enqueue_style( 'tbt-quotes' );

		ob_start();
		?>
		<div class="tbt-quotes">
			<p class="tbt-quotes__welcome">
				<span class="tbt-quotes__hello"><?php echo esc_html( sprintf( __( 'Hi %s.', 'tbt-quotes' ), $first_name ) ); ?></span>
				<span class="tbt-quotes__welcome-back"><?php echo esc_html( (string) $attributes['welcome'] ); ?></span>
			</p>

			<div class="tbt-quotes__thought">
				<p class="tbt-quotes__intro"><?php echo esc_html( (string) $attributes['intro'] ); ?></p>
				<blockquote class="tbt-quotes__quote">
					<p><?php echo esc_html( $quote['quote'] ); ?></p>
					<?php if ( $show_author && '' !== $quote['author'] ) : ?>
						<cite><?php echo esc_html( '— ' . $quote['author'] ); ?></cite>
					<?php endif; ?>
				</blockquote>
			</div>
		</div>
		<?php

		return trim( (string) ob_get_clean() );
	}

	/**
	 * Returns a user's current quote, if it still exists in the collection.
	 *
	 * @param int $user_id User ID.
	 * @return array{id:string, topic:string, quote:string, author:string}|null
	 */
	private function get_current_quote( $user_id ) {
		$current_id = (string) get_user_meta( $user_id, self::CURRENT_QUOTE_META, true );
		if ( '' === $current_id ) {
			return null;
		}

		foreach ( $this->get_quotes() as $quote ) {
			if ( hash_equals( $quote['id'], $current_id ) ) {
				return $quote;
			}
		}

		return null;
	}

	/**
	 * Selects a random quote outside the user's 100-quote recent history.
	 *
	 * @param int $user_id User ID.
	 * @return array{id:string, topic:string, quote:string, author:string}|null
	 */
	private function assign_new_quote( $user_id ) {
		$quotes = $this->get_quotes();
		if ( empty( $quotes ) ) {
			return null;
		}

		$valid_ids = array_column( $quotes, 'id' );
		$history = get_user_meta( $user_id, self::RECENT_QUOTES_META, true );
		$history = is_array( $history ) ? array_map( 'strval', $history ) : array();
		$history = array_values( array_intersect( $history, $valid_ids ) );
		$history = array_slice( $history, -self::HISTORY_LIMIT );
		$blocked = array_fill_keys( $history, true );

		$available = array_values(
			array_filter(
				$quotes,
				static function ( $quote ) use ( $blocked ) {
					return ! isset( $blocked[ $quote['id'] ] );
				}
			)
		);

		// This fallback matters only if a future quote collection has 100 items or fewer.
		if ( empty( $available ) ) {
			$available = $quotes;
		}

		$selected = $available[ wp_rand( 0, count( $available ) - 1 ) ];
		$history[] = $selected['id'];
		$history = array_slice( $history, -self::HISTORY_LIMIT );

		update_user_meta( $user_id, self::CURRENT_QUOTE_META, $selected['id'] );
		update_user_meta( $user_id, self::RECENT_QUOTES_META, $history );

		return $selected;
	}

	/**
	 * Loads and validates the bundled quote collection.
	 *
	 * @return array<int, array{id:string, topic:string, quote:string, author:string}>
	 */
	private function get_quotes() {
		if ( null !== $this->quotes ) {
			return $this->quotes;
		}

		$path = plugin_dir_path( __FILE__ ) . 'data/quotes.json';
		$contents = is_readable( $path ) ? file_get_contents( $path ) : false;
		$decoded = false !== $contents ? json_decode( $contents, true ) : null;
		$quotes = array();

		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				$topic = isset( $item['topic'] ) ? trim( (string) $item['topic'] ) : '';
				$text = isset( $item['quote'] ) ? trim( (string) $item['quote'] ) : '';
				$author = isset( $item['author'] ) ? trim( (string) $item['author'] ) : '';

				if ( '' === $text ) {
					continue;
				}

				$quotes[] = array(
					'id'     => substr( hash( 'sha256', $text . "\0" . $author ), 0, 20 ),
					'topic'  => $topic,
					'quote'  => $text,
					'author' => $author,
				);
			}
		}

		/**
		 * Filters the quote collection.
		 *
		 * Each item must retain id, topic, quote and author string keys.
		 *
		 * @param array<int, array{id:string, topic:string, quote:string, author:string}> $quotes
		 */
		$this->quotes = apply_filters( 'tbt_quotes_items', $quotes );

		return is_array( $this->quotes ) ? $this->quotes : array();
	}

	/**
	 * Gets a friendly first name with sensible WordPress profile fallbacks.
	 *
	 * @param WP_User $user Current user.
	 * @return string
	 */
	private function get_first_name( $user ) {
		$first_name = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
		if ( '' !== $first_name ) {
			return $first_name;
		}

		$display_name = trim( (string) $user->display_name );
		if ( '' !== $display_name ) {
			$parts = preg_split( '/\s+/u', $display_name );
			if ( is_array( $parts ) && isset( $parts[0] ) && '' !== $parts[0] ) {
				return $parts[0];
			}
		}

		return __( 'there', 'tbt-quotes' );
	}
}

new TBT_Quotes_Plugin();
