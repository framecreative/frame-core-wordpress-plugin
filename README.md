# F / R / A / M / E Core #

A plugin designed to be included on every site built and managed by Frame Creative. Frame Core is the server-side equivalent to normalize.css - it sets some more sane defaults, smooths over some weird edge cases, and provides some functionality for developer convenience.

The plugin consists of multiple modules which are generally opt-in through the use of a php constant.

Our preferred method of setting these constants is usually via `.env` files, and in most cases allowances will be made for these values in the `wp-config.php` file of the [Frame WordPress boilerplate](https://bitbucket.org/framecreative/frame-wp-boilerplate).


**Besides the password protected screen, no markup is ever generated for the front end of the site**


## Contributing and Extending ##

We welcome PRs from within the Frame team, however please keep the following in mind when designing features
* Features must be suitable for ALL frame sites
* PHP must be backwards compatible to PHP 5.5
* Changes to the DB, or generation of client side markup, is not recommended
* All module functionality should be opt-in, with sensible defaults

## Current Modules ##