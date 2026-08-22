=== The Blue Tree Quotes ===
Contributors: aparatofan
Tags: quotes, greeting, students, shortcode, divi
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.3
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

The main CSS hook is .tbt-quotes. Colours, type and the spacing between the
lines can all be adjusted with:

.tbt-quotes {
  --tbtq-ink: #1f2937;
  --tbtq-muted: #667085;
  --tbtq-rule: #0856c9;
  --tbtq-welcome-size: 26px;
  --tbtq-quote-size: 20px;
  --tbtq-voice-weight: 400;
  --tbtq-voice-leading: 1.4;
  --tbtq-label-size: 14px;
  --tbtq-label-leading: 1.5;
  --tbtq-rule-width: 3px;
  --tbtq-rule-gap: 24px;
  --tbtq-gap-welcome: 32px;
  --tbtq-gap-intro: 8px;
  --tbtq-gap-quote: 12px;
}

--tbtq-voice-leading and --tbtq-label-leading set the space between wrapped
lines within a paragraph. The three --tbtq-gap-* properties set the space
between the blocks: below the welcome line, below the lead-in, and between the
quote and its author.

--tbtq-rule-width and --tbtq-rule-gap control the blue rule beside the thought;
set --tbtq-rule-width: 0 to remove it.

== Changelog ==

= 1.0.3 =
* The block now sits on the white page rather than a blue panel. Text is ink
  on white; the lead-in and author are muted grey.
* The lead-in and quote gain a blue left rule, marking them as a pull quote.
* The welcome line and the quote are now different sizes (26px and 20px)
  rather than both 28px, so the quote does not compete with the page heading
  below it.
* --tbtq-on-hero and --tbtq-on-hero-muted are renamed to --tbtq-ink and
  --tbtq-muted. --tbtq-voice-size is replaced by --tbtq-welcome-size and
  --tbtq-quote-size.

= 1.0.2 =
* Fixed the vertical spacing in the greeting panel. The margin reset outranked
  the rules that set the gaps, so the welcome line, the lead-in and the quote
  rendered with no space between them.
* Slightly looser line spacing within the welcome line and the quote.
* Spacing is now adjustable through --tbtq-gap-* and --tbtq-*-leading.

= 1.0.1 =
* Typography aligned with the TBT Style Book: two tiers, Roboto Slab for the
  welcome and the quote, Roboto for the lead-in and the author.
* Removed the closing question, which duplicated the prompt below the panel.
* Webfonts are now loaded by the plugin instead of assumed.

= 1.0.0 =
* Initial release with personal greeting, 265 quotes and per-user rotation.
