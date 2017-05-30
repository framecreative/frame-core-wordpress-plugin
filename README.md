# F / R / A / M / E Core #

A plugin designed to be included on every site built and managed by Frame Creative. Frame Core is the server-side equivalent to normalize.css - it sets some more sane defaults, smooths over some weird edge cases, and provides some functionality for developer convenience.

The plugin consists of multiple modules which are generally opt-in through the use of a php constant.

All configuration can either be made via PHP constants (usually in wp-config) or variables set int the `.env` file of the project.


**Besides the password protected screen, no markup is ever generated for the front end of the site**


## Available Features ##

### FC_CODE_MANAGED ###
Defines that the codebase and dependencies are being managed externally (e.g. in version control) and disables the addition of plugins as these will disappear on next deployment. A message is also displayed to this effect in the admin. It does not disable plugin updates though, changes should not be deployed with out of date dependencies. This defaults to true.

### FC_SITE_MAINTAINED ###
Should be enabled if we have an agreement to maintain the site. This will not only disable installing new plugins but also plugin and core updates, as well as their notifications. This defaults to false.

### FC_DEV_USER ###
Defines the username of the dev user which will be immune to admin restricts. This user is able to install and update plugins at all times and will be able to edit ACF Field Groups. Defaults to 'frame'. 

### FC_FORCE_DOMAIN ###
Defines the domain that the site should be loaded on. Will redirect to this domain if accessed via another.

### FC_FORCE_SSL ###

### FC_PREFER_SSL ###

### FC_PASSWORD_PROTECT_PASSWORD ###

### FC_PROXY_UPLOADS_URL ###

### FC_PROXY_DISPLAY_ONLY ###

### FC_SMTP_HOST ###

### FC_SMTP_USER ###

### FC_SMTP_PASSWORD ###

### FC_SMTP_PORT ###

### FC_SMTP_FROM ###

### FC_SMTP_FROM_NAME ###

## Contributing and Extending ##

We welcome PRs from within the Frame team, however please keep the following in mind when designing features
* Features must be suitable for ALL frame sites
* PHP must be backwards compatible to PHP 5.5
* Changes to the DB, or generation of client side markup, is not recommended
* All module functionality should be opt-in, with sensible defaults