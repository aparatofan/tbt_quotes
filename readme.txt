=== The Blue Tree Quotes ===
Contributors: aparatofan
Tags: quotes, greeting, students, shortcode, divi
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows a personal welcome with a rotating quote to signed-in users, and a sign-in prompt to everyone else.

== Description ==

The plugin provides the shortcode [tbt_quote_greeting]. It renders one of two
states, depending on who is looking at the page.

A signed-in visitor sees:

1. Hi [first name]. Nice to see you back on The Blue Tree.
2. Here's a thought for you: followed by a quote and its author.
3. Two buttons: Lesson notes and My dashboard.

A signed-out visitor sees:

1. Hello Stranger. You're not logged in yet.
2. A short note explaining how to get in, in place of the quote.
3. Two buttons: Log in and See our offer.

There is no quote in the signed-out state. The current quote is stored against
the user account, so a visitor without an account has none to show.

A new quote is selected on every successful WordPress login. The selection is
stored for that login, so ordinary page refreshes do not change it. Each user has
an independent 100-quote history: a quote cannot repeat while it is still in that
history.

Every signed-in user sees the same two buttons. There is no role or capability
check.

The bundled collection contains 265 quotes imported from ENCHIRIDION_quotes.xlsx.

== Installation ==

1. Upload the tbt-quotes folder to /wp-content/plugins/ or install its ZIP file.
2. Activate The Blue Tree Quotes.
3. In the desired Divi column, add a Text or Code module.
4. Put [tbt_quote_greeting] in the module.
5. Leave the other Divi column and the row background unchanged.

Check that the notes_url and dashboard_url defaults match the page slugs on this
site, and pass the attributes below if they do not.

== Shortcode options ==

The default shortcode is:

[tbt_quote_greeting]

Optional text can be changed in the shortcode:

[tbt_quote_greeting welcome="It's good to have you here."]

Signed-in text:

* welcome - the sentence after "Hi [first name]."
* intro - the lead-in above the quote.
* show_author (yes or no)

Signed-in buttons:

* notes_url - default https://thebluetree.pl/tbt-notes/
* notes_label - default Lesson notes
* dashboard_url - default /dashboard/
* dashboard_label - default My dashboard

Signed-out buttons:

* login_url - empty by default, which sends the visitor to the WordPress login
  form and back to the page they came from. Set it to point somewhere else.
* login_label - default Log in
* offer_url - default /
* offer_label - default See our offer

Signed-out text:

* stranger_welcome - default You're not logged in yet.
* stranger_intro - the lead-in, default Getting in:
* stranger_message - the note in place of the quote.

A URL that starts with / is resolved against the site address, so /dashboard/
keeps working if the domain or the protocol changes. A button whose URL or label
is empty is not rendered at all, so a mistyped attribute leaves a gap rather than
a dead link. The first button in a row is the primary one and the rest are
secondary.

== Styling ==

The plugin styles only the markup it outputs. It does not add a background,
padding, columns or positioning to the surrounding Divi header.

The main CSS hook is .tbt-quotes. The signed-out block carries
.tbt-quotes--stranger as well, so it can be targeted separately. Colours, type
and the spacing between the lines can all be adjusted with:

.tbt-quotes {
  --tbtq-ink: #1f2937;
  --tbtq-muted: #667085;
  --tbtq-rule: #0856c9;
  --tbtq-blue: #0856c9;
  --tbtq-blue-hover: #4b84ce;
  --tbtq-surface: #ffffff;
  --tbtq-border: #d8e3f0;
  --tbtq-selected-bg: #edf4ff;
  --tbtq-focus-ring: 0 0 0 3px rgba(8, 86, 201, .18);
  --tbtq-radius-control: 12px;
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
  --tbtq-gap-actions: 24px;
  --tbtq-gap-button: 12px;
}

--tbtq-voice-leading and --tbtq-label-leading set the space between wrapped
lines within a paragraph. The --tbtq-gap-* properties set the space between the
blocks: below the welcome line, below the lead-in, between the quote and its
author, above the button row, and between the buttons themselves.

--tbtq-rule-width and --tbtq-rule-gap control the blue rule beside the thought;
set --tbtq-rule-width: 0 to remove it.

The --tbtq-blue* and --tbtq-surface, --tbtq-border, --tbtq-selected-bg,
--tbtq-focus-ring and --tbtq-radius-control properties style the buttons.

== Changelog ==

= 1.1.1 =
* Fixed the Lesson notes button so its default destination is the working
  https://thebluetree.pl/tbt-notes/ page instead of the missing
  /lesson-notes/ page.

= 1.1.0 =
* Signed-in users get an action row under the quote: Lesson notes and My
  dashboard. Every signed-in user sees both buttons.
* Logged-out visitors now get output instead of nothing: a Hello Stranger
  greeting, a short note on how to get in, and Log in and See our offer
  buttons.
* Eleven new shortcode attributes carry the button URLs, the button labels and
  the signed-out text, so page slugs can change in Divi without a code change.
* Roboto is now requested at weight 700 as well as 400, so the buttons use the
  real bold rather than a synthesised one.

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
