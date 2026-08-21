=== The Blue Tree Quotes ===
Contributors: aparatofan
Tags: quotes, greeting, students, shortcode, divi
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows each logged-in student a personal welcome and a rotating inspiring quote.

== Description ==

The plugin provides the shortcode [tbt_quote_greeting].

It displays:

1. Hi [first name]. Nice to see you back on The Blue Tree.
2. Here's a thought for you: followed by a quote and its author.
3. What shall we learn about today?

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

[tbt_quote_greeting encouragement="Let's work on your English today, shall we?"]

All supported attributes:

* welcome
* intro
* encouragement
* show_author (yes or no)

== Styling ==

The plugin styles only the markup it outputs. It does not add a background,
padding, columns or positioning to the surrounding Divi header.

The main CSS hook is .tbt-quotes. Colours can be adjusted with:

.tbt-quotes {
  --tbt-quotes-color: #ffffff;
  --tbt-quotes-muted-color: rgba(255, 255, 255, 0.82);
  --tbt-quotes-accent-color: #ffffff;
}

== Changelog ==

= 1.0.0 =
* Initial release with personal greeting, 265 quotes and per-user rotation.
