# Primary Categories for WordPress

**Note: this plugin currently only works on the classic editor.**

This plugin gives you the ability to set a selected category as the primary category for a post or custom post type.

* Set a primary category on posts and custom post types (if they're using built-in categories)
* Helper function to get all posts with the same *primary* category
* Helper function to get primary category (falls back to first category if none is set) of a post / custom post type.
* Future: We could add a widget but it would probably be better to create a block. Same could be said for a shortcode.
* Future: There might be a use case for someone to alter the category loop query to only show posts with the same primary category but that could lead to a weird user experience.
* Future: Make a REST API endpoint available to get posts by primary category.

## Dependencies

1. [Node & NPM](https://www.npmjs.com/get-npm) - Build packages and 3rd party dependencies are managed through NPM, so you will need that installed globally
2. [Gulp](https://gulpjs.com/) - Gulp is used as the main task runner, it runs PostCSS, processes images, handles SVG sprites (if needed), and executes Webpack
3. [Webpack](https://webpack.js.org/) - Webpack is used to process the JavaScript
4. [Composer](https://getcomposer.org/) - Composer is used to manage PHP.

## NPM Commands

`npm run start` (install dependencies and run initial gulp)

`npm run watch` (watch)

`npm run build` (build all front-end files)

`npm run build-release` (build all front-end files for release)

## Composer Commands

`composer lint` (lint PHP files)

`composer lint-fix` (lint PHP files and automatically correct coding standard violations)

## Usage

Once enabled, the plugin gives the ability to make a selected category the primary category for the post. **Note: The post must be saved before marking a category as saved.**

### Get posts by primary category

To get the posts for a primary category based on it's id you can use the function `\SeagynDavis\WordPressPrimaryCategory\Helpers\get_posts_from_primary_category( $category_id, $posts_per_page, $post_type )`.

* $category_id is the category you're wanting to get the posts of.
* $posts_per_page is the number of posts that must be returned by the query.
* $post_type is the post type(s) you want to search on. Can be a string for a single post type or an array of multiple post types. The post type must be linked to the category taxonomy though.

**Example**
```php
$category_posts = \SeagynDavis\WordPressPrimaryCategory\Helpers\get_posts_from_primary_category( 1 );

while ( $category_posts->have_posts() ) {
    $category_posts->the_post();
    
    the_title( '<h2>', '</h2>' );
    
    the_content();
}
```

### Get primary category for post

To get the primary category for a post you can use `\SeagynDavis\WordPressPrimaryCategory\Helpers\get_primary_category()`. **Note: this must be used within the loop.**

**Example**
```php
$category_posts = \SeagynDavis\WordPressPrimaryCategory\Helpers\get_posts_from_primary_category( 1 );

while ( $category_posts->have_posts() ) {
    $category_posts->the_post();
    
    the_title( '<h2>', '</h2>' );
    
    $category = \SeagynDavis\WordPressPrimaryCategory\Helpers\get_primary_category();
    
    // Passing an opject to get_category_link() saves a DB call
    echo 'Category: <a href="' . get_category_link( $category ) . '" title="'  . $category->name .  '">' . $category->name . '</a>';
    
    the_content();
}
```

## Objectives

* Selecting the primary category should be easy. 
* Limit the amount of queries required to get the primary category.
* Make the ability to query posts / CPTs as performant as possible
