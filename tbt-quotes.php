<?php
/**
 * Plugin Name:       The Blue Tree Quotes
 * Description:       Shows a personal welcome with a rotating quote to signed-in users, and a sign-in prompt to everyone else.
 * Version:           1.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            The Blue Tree
 * Text Domain:       tbt-quotes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TBT_Quotes_Plugin {
	private const VERSION = '1.1.1';
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
	 * Roboto is requested at 400 and 700: the buttons are Roboto 700, and asking
	 * for the weight is what stops the browser synthesising a fake bold.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'tbt-quotes-fonts',
			'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Roboto+Slab:wght@400;700&display=swap',
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
	 * Enqueues the block stylesheet, registering it first if the shortcode runs
	 * before wp_enqueue_scripts.
	 *
	 * Each render branch calls this immediately before it starts printing, so a
	 * branch that returns early never loads a stylesheet for markup it did not
	 * output.
	 *
	 * @return void
	 */
	private function enqueue_style() {
		if ( ! wp_style_is( 'tbt-quotes', 'registered' ) ) {
			$this->register_assets();
		}

		wp_enqueue_style( 'tbt-quotes' );
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
	 * Renders the greeting block in whichever state the visitor is in.
	 *
	 * Signed in: the personal welcome, the rotating quote, and an action row of
	 * lesson notes and dashboard buttons. Signed out: a short prompt in place of
	 * the quote, with log in and offer buttons. The current quote is per-user
	 * meta, so a visitor without a user ID cannot be given one.
	 *
	 * Available attributes:
	 * - welcome: sentence after "Hi [name]."
	 * - intro: sentence before the quote.
	 * - show_author: "yes" or "no".
	 * - notes_url, notes_label: the primary button when signed in.
	 * - dashboard_url, dashboard_label: the secondary button when signed in.
	 * - login_url, login_label: the primary button when signed out. An empty
	 *   login_url resolves to the WordPress login form, returning the visitor to
	 *   the page they came from.
	 * - offer_url, offer_label: the secondary button when signed out.
	 * - stranger_welcome, stranger_intro, stranger_message: the signed-out copy.
	 *
	 * URLs may be written as site-root paths such as "/dashboard/", so a Divi
	 * page slug can be repointed without touching this file.
	 *
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $attributes = array() ) {
		$attributes = shortcode_atts(
			array(
				'welcome'          => __( 'Nice to see you back on The Blue Tree.', 'tbt-quotes' ),
				'intro'            => __( "Here's a thought for you:", 'tbt-quotes' ),
				'show_author'      => 'yes',
				'notes_url'        => 'https://thebluetree.pl/tbt-notes/',
				'notes_label'      => __( 'Lesson notes', 'tbt-quotes' ),
				'dashboard_url'    => '/dashboard/',
				'dashboard_label'  => __( 'My dashboard', 'tbt-quotes' ),
				'login_url'        => '',
				'login_label'      => __( 'Log in', 'tbt-quotes' ),
				'offer_url'        => '/',
				'offer_label'      => __( 'See our offer', 'tbt-quotes' ),
				'stranger_welcome' => __( "You're not logged in yet.", 'tbt-quotes' ),
				'stranger_intro'   => __( 'Getting in:', 'tbt-quotes' ),
				'stranger_message' => __( "Use the button below to log in. If you don't have access yet, check our offer on the homepage.", 'tbt-quotes' ),
			),
			$attributes,
			self::SHORTCODE
		);

		if ( ! is_user_logged_in() ) {
			return $this->render_stranger( $attributes );
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

		$first_name = $this->get_first_name( $user );
		$show_author = ! in_array(
			strtolower( (string) $attributes['show_author'] ),
			array( 'no', 'false', '0' ),
			true
		);

		$this->enqueue_style();

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

			<?php
			echo $this->render_actions(
				array(
					array( 'url' => $attributes['notes_url'],     'label' => $attributes['notes_label'] ),
					array( 'url' => $attributes['dashboard_url'], 'label' => $attributes['dashboard_label'] ),
				)
			);
			?>
		</div>
		<?php

		return trim( (string) ob_get_clean() );
	}

	/**
	 * Renders the signed-out block.
	 *
	 * It reuses __welcome, __hello, __thought and __intro so it inherits the
	 * established type tiers and the blue rule with no new CSS. Only __message is
	 * new: it takes the voice treatment of the quote, minus the quotation marks,
	 * because it speaks in the same voice without pretending to be a quotation.
	 *
	 * @param array<string, mixed> $attributes Resolved shortcode attributes.
	 * @return string
	 */
	private function render_stranger( $attributes ) {
		$login_url = $this->resolve_url( $attributes['login_url'] );

		if ( '' === $login_url ) {
			$login_url = $this->default_login_url();
		}

		$this->enqueue_style();

		ob_start();
		?>
		<div class="tbt-quotes tbt-quotes--stranger">
			<p class="tbt-quotes__welcome">
				<span class="tbt-quotes__hello"><?php esc_html_e( 'Hello Stranger.', 'tbt-quotes' ); ?></span>
				<span class="tbt-quotes__welcome-back"><?php echo esc_html( (string) $attributes['stranger_welcome'] ); ?></span>
			</p>

			<div class="tbt-quotes__thought">
				<p class="tbt-quotes__intro"><?php echo esc_html( (string) $attributes['stranger_intro'] ); ?></p>
				<p class="tbt-quotes__message"><?php echo esc_html( (string) $attributes['stranger_message'] ); ?></p>
			</div>

			<?php
			echo $this->render_actions(
				array(
					array( 'url' => $login_url,                'label' => $attributes['login_label'] ),
					array( 'url' => $attributes['offer_url'],  'label' => $attributes['offer_label'] ),
				)
			);
			?>
		</div>
		<?php

		return trim( (string) ob_get_clean() );
	}

	/**
	 * Renders one action row.
	 *
	 * The first button with a usable URL and label is the primary one and the
	 * rest are secondary, so a view never carries two primary actions (Style
	 * Book §7). A button whose URL or label resolves to an empty string is
	 * dropped, so a mistyped attribute produces no button rather than a link
	 * that goes nowhere.
	 *
	 * @param array<int, array{url:mixed, label:mixed}> $buttons Buttons in display order.
	 * @return string
	 */
	private function render_actions( $buttons ) {
		$usable = array();

		foreach ( $buttons as $button ) {
			$url   = $this->resolve_url( isset( $button['url'] ) ? $button['url'] : '' );
			$label = trim( (string) ( isset( $button['label'] ) ? $button['label'] : '' ) );

			if ( '' !== $url && '' !== $label ) {
				$usable[] = array( 'url' => $url, 'label' => $label );
			}
		}

		if ( empty( $usable ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="tbt-quotes__actions">
			<?php foreach ( $usable as $index => $button ) : ?>
				<a class="tbt-quotes__button tbt-quotes__button--<?php echo 0 === $index ? 'primary' : 'secondary'; ?>" href="<?php echo esc_url( $button['url'] ); ?>"><?php echo esc_html( $button['label'] ); ?></a>
			<?php endforeach; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Resolves an attribute URL.
	 *
	 * A leading slash means a site-root path, so a Divi page slug can be written
	 * as "/dashboard/" and still survive a change of domain or protocol.
	 * Absolute URLs pass through untouched.
	 *
	 * @param mixed $value Raw attribute value.
	 * @return string
	 */
	private function resolve_url( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( 0 === strpos( $value, '/' ) ) {
			return home_url( $value );
		}

		return $value;
	}

	/**
	 * Builds the fallback login URL for an empty login_url attribute.
	 *
	 * The redirect comes from get_permalink() rather than REQUEST_URI, which is
	 * client-supplied and would have to be sanitised before it could be trusted.
	 *
	 * @return string
	 */
	private function default_login_url() {
		$redirect = is_singular() ? get_permalink() : '';

		if ( ! is_string( $redirect ) || '' === $redirect ) {
			$redirect = home_url( '/' );
		}

		return wp_login_url( $redirect );
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
