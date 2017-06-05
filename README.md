# F / R / A / M / E Core #

A plugin designed to be included on every site built and managed by Frame Creative. Frame Core is the server-side equivalent to normalize.css - it sets some more sane defaults, smooths over some weird edge cases, and provides some functionality for developer convenience.

**Besides the password protected screen, no markup is ever generated for the front end of the site**

## Usage ##

All configuration can either be made via PHP constants (usually in wp-config).

```
define( 'FC_PASSWORD_PROTECT_PASSWORD', '1234' );
```

Or variables set int the `.env` file of the project. This is preferred.

```
FC_PASSWORD_PROTECT_PASSWORD=1234
```

## Available Features ##

### FC_CODE_MANAGED ###
Defines that the codebase and dependencies are being managed externally (e.g. in version control) and disables the addition of plugins as these will disappear on next deployment. A message is also displayed to this effect in the admin. It does not disable plugin updates though, changes should not be deployed with out of date dependencies. This defaults to true.

### FC_SITE_MAINTAINED ###
Should be enabled if we have an agreement to maintain the site. This will not only disable installing new plugins but also plugin and core updates, as well as their notifications. This defaults to false.

### FC_DEV_USER ###
Defines the username of the dev user which will be immune to admin restricts. This user is able to install and update plugins at all times and will be able to edit ACF Field Groups. Defaults to 'frame'. 

### FC_FORCE_DOMAIN ###
Defines the domain that the site should be loaded on. Will redirect to this domain if accessed via another.

### FC_PREFER_SSL ###
Used in conjunction with force domain, this will redirect to the desired domain on the HTTPS protocol.

### FC_FORCE_SSL ###
Similar to above, but will also redirect the desired domain to the HTTPS protocol.

### FC_PASSWORD_PROTECT_PASSWORD ###
Defines a password that blocks access to the site. Should be used for staging sites.

### FC_PROXY_UPLOADS_URL ###
Defines the URL for the uploads directory that uploads should be accessed from if not available locally. The first time the image is accessed from the proxy URL it will be stored locally.

### FC_PROXY_DISPLAY_ONLY ###
This configures the above action to not download images that are unavailable locally, but always display them from the proxy source.

### FC_SMTP_HOST ###
Configures the hostname that the site should send emails through via the SMTP protocol. Must be used in conjunction with username and password.

### FC_SMTP_USER ###
Configures the SMTP username.

### FC_SMTP_PASSWORD ###
Configures the SMTP password.

### FC_SMTP_PORT ###
Configures the port that emails are sent over. Defaults to 25 (standard for SMTP) or 1025 in a dev environment (the port required for MailHog).

### FC_SMTP_FROM ###
Defines the email address that should be shown as the sender of the email.

### FC_SMTP_FROM_NAME ###
Defines the name that should be shown as the sender of the email.

## Contributing and Extending ##

We welcome PRs from within the Frame team, however please keep the following in mind when designing features
* Features must be suitable for ALL frame sites
* PHP must be backwards compatible to PHP 5.5
* Changes to the DB, or generation of client side markup, is not recommended
* All module functionality should be opt-in, with sensible defaults