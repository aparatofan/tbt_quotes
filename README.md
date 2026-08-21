# The Blue Tree Quotes

A WordPress plugin for a personal post-login welcome on The Blue Tree home page.

## Shortcode

Place this in a Divi Text or Code module in the text column of the existing
header:

    [tbt_quote_greeting]

The plugin does not change the surrounding Divi row, columns, background or
logo. Logged-out visitors receive no output.

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
      encouragement="Let's work on your English today, shall we?"
      show_author="yes"]

## Installation

1. Download a packaged tbt-quotes.zip build (or package the repository as below).
2. Upload it in WordPress under Plugins > Add New Plugin > Upload Plugin.
3. Activate The Blue Tree Quotes.
4. Add the shortcode to the appropriate Divi module.

For a directly installable ZIP, place the runtime files in a folder named
tbt-quotes and package that folder:

    mkdir -p build/tbt-quotes
    cp -R tbt-quotes.php assets data readme.txt uninstall.php build/tbt-quotes/
    cd build
    zip -r tbt-quotes.zip tbt-quotes
