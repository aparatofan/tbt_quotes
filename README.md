# The Blue Tree Quotes

A WordPress plugin for a personal post-login welcome on The Blue Tree home page.

## Shortcode

Place this in a Divi Text or Code module in the text column of the existing
header:

    [tbt_quote_greeting]

The plugin does not change the surrounding Divi row, columns, background or
logo.

The block renders in one of two states:

- **Signed in** — the personal greeting, the rotating quote, and an action row
  of two buttons: Lesson notes and My dashboard. Every signed-in user sees both;
  there is no role or capability check.
- **Signed out** — a Hello Stranger greeting with a short note on how to get in
  where the quote normally sits, and two buttons: Log in and See our offer.
  There is no quote here, because the current quote is stored against a user
  account.

The first button in a row is the primary one and the rest are secondary. A
button whose URL or label is empty is not rendered, so a mistyped attribute
leaves a gap rather than a dead link.

## Rotation behaviour

- WordPress selects a new quote after every successful login.
- Refreshing the page keeps the same quote.
- Recent history is stored separately for each user.
- A quote cannot repeat until 100 other quotes have been shown.
- The initial collection contains 265 quotes from the supplied workbook.

## Optional shortcode attributes

    [tbt_quote_greeting
      welcome="Nice to see you back on The Blue Tree."
      intro="Here's a thought for you:"
      show_author="yes"
      notes_url="/lesson-notes/"
      notes_label="Lesson notes"
      dashboard_url="/dashboard/"
      dashboard_label="My dashboard"
      login_url=""
      login_label="Log in"
      offer_url="/"
      offer_label="See our offer"
      stranger_welcome="You're not logged in yet."
      stranger_intro="Getting in:"
      stranger_message="Use the button below to log in. If you don't have access yet, check our offer on the homepage."]

A URL beginning with `/` is resolved against the site address, so a Divi page
slug can be repointed without touching the code. An empty `login_url` sends the
visitor to the WordPress login form and back to the page they came from.

Confirm that the `notes_url` and `dashboard_url` defaults match the actual page
slugs in Divi before relying on them.

## Installation

1. Download a packaged tbt-quotes.zip build (or package the repository as below).
2. Upload it in WordPress under Plugins > Add New Plugin > Upload Plugin.
3. Activate The Blue Tree Quotes.
4. Add the shortcode to the appropriate Divi module.

For a directly installable ZIP, run the build script:

    bin/build-plugin.sh

It writes build/tbt-quotes.zip, ready for Upload Plugin, alongside
build/tbt-quotes/ containing the same files unpacked. The script checks that the
plugin header, the VERSION constant and the readme.txt stable tag all agree
before packaging anything.

## Deployment

Merging a branch into main deploys the plugin to the live site automatically,
over FTPS, using the workflow in .github/workflows/deploy-wordpress.yml. The
code is linted and tested before anything is uploaded, and only the runtime
files are sent.

See docs/DEPLOYMENT.md for the required secrets, how to do a dry run first, and
what to check if a deploy fails.
