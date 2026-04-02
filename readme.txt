=== RecipePress Reloaded ===
Contributors: w33zy
Tags: recipe, recipes, cooking, baking, food, food blog, recipe sharing,
Requires at least: 4.0
Tested up to: 6.1
Stable tag: 2.7.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://paypal.me/wzymedia

The swiss army knife for your food blog. A tool to add nicely formatted recipes that are SEO friendly to your blog and to manage your recipe collection.


== Description ==

RecipePress Reloaded is a powerful and very flexible tool to manage your blog's recipe collection. It was designed with the editor in mind and created for easy and fast usage.
This plugin adds a new post type for recipes to your website. You can publish your recipes as standalone posts, or include in your normal posts and pages.
Organize your recipes in any way you would like. It includes predefined food categories such as cuisines, courses, seasons and difficulties. As creating new categories is
easy it's up to you which and how many categories you will are creating. Use index pages embedded on pages to make your recipes accessible by title or taxonomy terms. Or use one of
the widgets to create tag clouds or top ten lists.
Of course, you can use all the other WordPress goodies you know from posts in your recipes as well: photos, videos and comments. We have also included new additions such as user 5-star reviews and recipe process shots.
Despite its simplicity it's giving editors, administrators, designers and developers all the freedom to adapt the plugin to their needs. Features you don't
need can be deactivated and are hidden from the UI. You can even create your own recipe templates to complete control the look and feel of your recipes. The default ones
are SEO friendly, with support for Google's JSON-LD recipe schema information.


= Features =

* custom post type for recipes
* backend to enter recipes fast
* group ingredients and instructions to sets, e.g., for cake and topping
* add process shots to each step of the recipe instruction if you like
* all the extra metadata such as diet type or recipe source you need for recipes
* user submitted 5-star ratings for recipes, with your complete control
* serving size
* preparation and cooking times
* nutritional information
* all taxonomies you need. Cuisines, courses, seasons and difficulties come predefined, but you can create whatever taxonomy you like.
* use post categories and terms on recipes as well, but only if you want to
* ingredients are a taxonomy as well, so you can easily find all recipes using 'carrots' for example.
* access recipes by alphabetical indices of title or any taxonomy term
* include recipes to your normal posts or pages using shortcodes
* search engine friendly recipe output using [Google's](https://developers.google.com/search/docs/data-types/recipe) recipe JSON-LD format
* choose between different templates to determine how your recipes should look like or create your own template yourself

= Languages =

RecipePress Reloaded currently is available in:

* English
* German
* Russian

For the following languages translations are partly available:

* German (Austria and Switzerland)
* Dutch
* Italian
* Hungarian

Please help [translating](https://translate.wordpress.org/projects/wp-plugins/recipepress-reloaded) the plugin. It's now easier than ever before on
[translate.wordpress.org](https://translate.wordpress.org)

== Installation ==

1. Upload `recipepress-reloaded` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin on the Recipe's menu screen.

== Changelog ==

= 2.7.1 =
* Add the ability to wrap ingredient names with square brackets by highlighting the text and pressing `[` inside the bulk ingredients input field
* Code cleanup

= 2.7.0 =
* Added use of the Markdown link syntax `[red apples](https://example.com/red-apples)` to the recipe ingredients bulk input field
* PHP 8.2 compatibility improvements and fixes
* Fixed a bug with pasting ingredients and instructions into the bulk input fields
* Fixed a bug with the output of JSON-LD schema markup

= 2.6.0 =
* Strip umlauts and other accents from URLs when saving a recipes source URL
* Redesigned ingredients unit list to use type-ahead searching insteaf of a dropdown
* Redesigned servings unit list to use type-ahead searching insteaf of a dropdown
* Add a bulk ingredients input field to the recipe edit screen
* Add a bulk instructions input field to the recipe edit screen

= 2.5.0 =
* Fixed a bug with adding new values to the "do not print" list of HTML classes
* Refactored the "Additional Nutritional fields" feature
* Strip umlauts from URLs when saving a recipes source URL

= 2.4.2 =
* fixed a with the display of empty nutritional field

= 2.4.0 =
* code clenup

= 2.3.0 =
* Code cleanup
* updated `jQuery.print` library
* Added the option to create new fields for the recipe's nutrition

= 2.2.1 =
* Fixed a fatal error due to files missing from last commit

= 2.2.0 =
* Added a new filter for adding content before and after a recipe's ingredients and instructions
* Fixed a bug with the generated JSON-LD schema
* Fixed a bug with WP REST API 404s when accented characters are being used in custom taxonomies

= 2.1.1 =
* Fixed an issue with Yoast SEO integration


= 2.1.0 =
* Added an integration with Yoast SEO's graph metadata to add our recipe JSON-LD data
* Added new "word count" feature to the post publish box which counts ingredients and instruction fields
* Added the number of recipe ratings to that WP admin "At a Glance" widget
* Added our ingredients and instructions data to the WP REST API's new `meta` field
* Display the ingredient image on the ingredient admin screen
* Remove the recipe JSON-LD schema from the `[recipe-collection]` shortcode, per Google's new guidance
* Fixed a bug where too much text was being added to the Pinterest share button
* Fixed a bug where an update task notification is displayed on new installs

= 2.0.0 =
* Added option to display star ratings inside the recipe content areas, also via a `[rpr-ratings]` shortcode
* Added new recipe equipment feature
* Use StimulusJS to handle backend JavaScript to reduce/eliminate the dependency on jQuery
* Updated to CSSTidy 1.7.3
* Add support for Vimeo videos in the JSON-LD schema markup
* Rebuilt the `[rpr-favorites]` shortcode using SvelteJS
* Updated recipe template's layout/design

= 1.11.0 =
* Display the recipe ID in the WP admin recipe table
* The "Recipe filter" widget now has an option to select between checkboxes or dropdowns
* Improved styling of non-hierarchical tags on the recipe edit page
* Fixed a bug where the "RPR Calendar" widget's cache was not being cleared
* Code cleanup

= 1.10.0 =
* Fixed a bug where custom recipe templates are not recognized in child-themes
* Fixed a bug where the link target setting was not being recognized
* Fixed a bug where "update recipe ratings" task is not completed when ratings are not present
* Code cleanup

= 1.9.0 =
* Added a feature to replace YouTube oEmbeds with a lighter (faster) solution. See Settings > Advanced > Speedup YouTube oEmbeds
* Added the option to sort the WP admin recipes table by user ratings count
* Cleaned up the "Author profile" widget

= 1.8.0 =
* Fixed and issue where video thumbnail was not displayed on initial fetch of video
* Add the `defer` tag to our scripts, they are not needed for loading our page
* Use thumbnail for the instruction image in the recipe editor instead of full size image
* Cleaned up the "RPR Favorites" extension

= 1.7.0 =
* Removed usage of font icons to improve GPS scores; now using SVG files for icons
* Added a "Recent Recipes" widget
* Tweak the admin section of the "Author Profile" widget
* Refactored the `Template` class to use more semantic markup for recipe elements
* Fixed an issue where image srcset and sizes are not calculated for in content images in WP 5.5

= 1.6.1 =
* Fixed a bug where the recipe schema was not displayed on certain templates

= 1.6.0 =
* Tweaked the design and layout of recipe templates
* Started work on using the WordPress Customizer for template customization
* Adjust print stylesheet to remove unnecessary page breaks

= 1.5.0 =
* Added a new "Duplicate Recipe" extension
* Fixed a bug when editing taxonomy terms

= 1.4.1 =
* Fixed a bug when saving WP menus with RPR active

= 1.4.0 =
* Created the new "Rpr Delicious" recipe template, with customizer support
* Updated recipe templates
* Fixed an issue with adding an instruction photo when WP does not have a "medium" image size
* Update screenshots
* Minor bug fixes

= 1.3.0 =
* Added a new widget - Recent Recipe Ratings
* Added option to choose a default taxonomy term to use in the recipe JSON-LD schema
* Fixed a bug in the CSSTidy library

= 1.2.0 =
* Added support for Google's new "Guided Recipe" JSON-LD schema markup
* Added recipe listing button to the classic post editor
* Added a background task to correct an old bug with the ordering of recipe instruction group titles

= 1.1.0 =
* Added a translation file for "de-CH"
* Updated translation files
* Fixed an issue with displaying decimal values

= 1.0.0 =
* A complete rewrite of the Recipepress Reloaded plugin

= 0.11.3 =
* Fixed missing translation
* Fixed nutritional information accepting decimals

= 0.11.2 =
* Prepping for the 1.0.0 release

= 0.11.1 =
* Fixed a bug where recipe description from new recipes were not added to JSON-LD data

= 0.11.0 =
* Fixed an infinite loop error with embedding recipes

= 0.10.0 =
* Added a new picture grid based recipe index page via shortcode
* Added library to handle background processes
* Switched recipe description metabox to use the WP editor from increased compatibility with 3rd plugins such as Yoast SEO
* Cleaned up a couple files as per WPCS rules... this is an ongoing process
* Fixed various PHP warning and errors
* Removed the usage of non-breaking spaces, fixed extra spaces before commas in ingredient line

= 0.9.2 =
* Fixing a bug while saving recipes

= 0.9.1 =
* Fixing several display bugs such as double numerated items, double post images or hidden elements at the backend

= 0.9.0 =
* Completely remodeled printing function (thanks to [w33zy](https://wordpress.org/support/users/w33zy/))
* Cite source for recipes
* Compatibility with WP Multisite installations
* Fixing several display bugs

= 0.8.4 =
* Fixing several display bugs

= 0.8.3 =
* Fixing a bug on activation (file name was wrong)

= 0.8.2 =
* Fixing several bugs.

= 0.8.1 =
* Fixing several bugs.

= 0.8.0 =
* Complete recoding of the entire plugin. The code is now modular, easier to maintain and easier to contribute to.
* Extended and improved support of [schema.org](http://schema.org/Recipe)'s recipe microdata. Now supporting Microdata, RFDa and JSON-LD.
* New options backend, now very clearly laid out
* Very easy handling for taxonomies
* Improved recipe editor

== Upgrade Notice ==

= 0.10.0 =
Please make a backup before updating to this new release.

= 0.9.2 =
0.9.2 Bugfix release: Fixing a bug while saving recipes the first time

= 0.9.1 =
0.9.1 Bugfix release: Fixing several display bugs. | 0.9.0 New printing system, enhanced compatibility for multisite.

= 0.8.4 =
Fixing several bugs in displaying recipes.
If you get a 'File not found' error on recipepress-reloaded.php please just reactivate.

= 0.8.3 =
Fixing mor bugs after the complete recode 0.8.0
If you get a 'File not found' error on recipepress-reloaded.php please just reactivate.
This is an error in 0.8.0-0.8.2 only. Updating form 0.7.x is not affected.

= 0.8.2 =
Bugfix release

= 0.8.1 =
Bugfix release

= 0.8.0 =
Complete recoding of the entire plugin. The code is now modular, easier to maintain and easier to contribute to. As the options backend has changed, a database
update is necessary. Please make sure you have a backup of your data!

== Frequently Asked Questions ==

= I hit a problem. What to do? =
Please open a thread in the [support forum](https://wordpress.org/support/plugin/recipepress-reloaded "support forum") or file an issue at
[GitLab](https://gitlab.com/w33zy/recipepress-reloaded).

= I need a special feature. Can I pay you to get it? =
It depends. Depends on the feature/functionality you have in mind and how/if it will affect other users of the plugin. If you need a special feature
please write a post at the [support forum](https://wordpress.org/support/plugin/recipepress-reloaded "support forum") or file an issue at
[GitLab](https://gitlab.com/w33zy/recipepress-reloaded/-/issues).
As RecipePress reloaded is open source software you are very welcome to fork the project on [GitLab](https://gitlab.com/w33zy/recipepress-reloaded),
implement the feature yourself and create a pull request to have it included in the next release.

= I'm not a developer. How can I contribute to the development? =
Open source software is living from user contributions. Fortunately you don't have to be a developer to help. You can
 * Help [translating](https://translate.wordpress.org/projects/wp-plugins/recipepress-reloaded) the plugin. It's now easier than ever before on
[translate.wordpress.org](https://translate.wordpress.org)
 * Give [feedback](https://wordpress.org/support/plugin/recipepress-reloaded). This helps me and others a lot to further improve RecipePress reloaded.
 * Help with creating a [user documentation](https://gitlab.com/w33zy/recipepress-reloaded/-/wikis/home) for RecipePress reloaded.
 * Spread the word! Tell others about your experiences.
 * Fork the project on [GitLab](https://gitlab.com/w33zy/recipepress-reloaded) and implement new features (of course ;)

== Screenshots ==

1. Simple and clean interface to type your recipes easily.
2. Sample recipe output.
3. An example of a Google search result with Rich Snippets applied using [Google's JSON-LD recipe markup](https://developers.google.com/search/docs/data-types/recipe).
4. Details of the backend interface. Easily add number, unit, ingredient name, comment and link. Ingredients will automatically be created as
taxonomy items. You can also group ingredients.
5. A builtin recipe index page.
6. The WordPress recipe admin table
7. Add as many taxonomies as you like through the options page.
8. Another example of one of the builtin recipe template.
