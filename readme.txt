=== The Blue Tree Quotes ===
Contributors: aparatofan
Tags: quotes, greeting, students, shortcode, divi
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows each logged-in student a personal welcome and a rotating inspiring quote.

== Description ==

The plugin provides the shortcode [tbt_quote_greeting].

It displays:

1. Hi [first name]. Nice to see you back on The Blue Tree.
2. Here's a thought for you: followed by a quote and its author.

A new quote is selected on every successful WordPress login. The selection is
stored for that login, so ordinary page refreshes do not change it. Each user has
an independent 100-quote history: a quote cannot repeat while it is still in that
history.

The bundled collection contains 265 quotes imported from ENCHIRIDION_quotes.xlsx.

== Installation ==

1. Upload the tbt-quotes folder to /wp-content/plugins/ or install its ZIP file.
2. Activate The Blue Tree Quotes.
3. In the desired Divi column, add a Text or Code module.
4. Put [tbt_quote_greeting] in the module.
5. Leave the other Divi column and the row background unchanged.

Logged-out visitors receive no shortcode output.

== Shortcode options ==

The default shortcode is:

[tbt_quote_greeting]

Optional text can be changed in the shortcode:

[tbt_quote_greeting welcome="It's good to have you here."]

All supported attributes:

* welcome
* intro
* show_author (yes or no)

== Styling ==

The plugin styles only the markup it outputs. It does not add a background,
padding, columns or positioning to the surrounding Divi header.

The main CSS hook is .tbt-quotes. Colours can be adjusted with:

.tbt-quotes {
  --tbtq-on-hero: #ffffff;
  --tbtq-on-hero-muted: rgba(255, 255, 255, 0.78);
  --tbtq-voice-size: 28px;
  --tbtq-voice-weight: 400;
  --tbtq-label-size: 14px;
}

== Changelog ==

= 1.0.1 =
* Typography aligned with the TBT Style Book: two tiers, Roboto Slab for the
  welcome and the quote, Roboto for the lead-in and the author.
* Removed the closing question, which duplicated the prompt below the panel.
* Webfonts are now loaded by the plugin instead of assumed.

= 1.0.0 =
* Initial release with personal greeting, 265 quotes and per-user rotation.
