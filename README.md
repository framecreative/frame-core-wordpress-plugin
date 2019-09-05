
# Frame Core #

Frame Core is the server-side equivalent to normalize.css - it sets some better defaults, smooths over some weird edge cases, and provides some functionality for developer convenience.

**Besides the password protected screen, no markup is ever generated for the front end of the site**

## Usage

Preferred method: Add the variables in the project `.env` file:

```
FC_PASSWORD_PROTECT_PASSWORD="1234"
```

Or define within the `wp-config.php`:

```
define('FC_PASSWORD_PROTECT_PASSWORD', '1234');
```

## Automatically Added Features

### Removal of ACF 'Custom Fields' link in admin

So the client can't change our beautiful custom fields.

### Plugin, Theme, Wordpress Core changes are disabled

The following general user restrictions are set:
- Disable installing and deleting plugins
- Disable installing and switching themes
- Add a 'Plugin Installation Disabled' notification in the admin 
- **Users will be able to update plugins, themes and Wordpress core**

Set this to `false` if the hand-over has no agreements in place to manage the code.
```
FC_CODE_MANAGED="false"
```
**Note:  This feature only applies to non-admin accounts. The frame account (default: `frame`) will be immune).**



## Custom Features - General

### Ongoing site maintenance agreement
Set as `true` if we have an agreement to maintain the website (perform updates, maintenance etc).
```
FC_SITE_MAINTAINED="true"
```
If set to `true` this will:
- Disable installing, deleting and updating plugins (including Wordpress)
- Disable installing and switching themes
- Add a 'Plugin Installation Disabled' notification in the admin 

### Set a custom Frame admin username
This defines the username of the dev user which will be immune to admin restricts. This user is able to install and update plugins at all times and will be able to edit ACF Field Groups.
If our admin Wordpress login does _not_ have the username `frame` then you can define the name: 
```
FC_DEV_USER=""
```



### Automatic Google Tag Manager embed code
By entering the ID, Frame Core will add the necessary embed code to the page `<head>` and `<body>`.
Note: As the code placement is slightly different to the suggested placement you cannot verify the site in google site console (webmaster tools) using the Google Tag Manager verification method. 
```
FC_GTM_ID=""
# values: string (google tag manager container id)
```

### Freeze the content (default: `false`)
This activates a content freeze so only the Frame Admin can login to the CMS.
```
FC_CONTENT_FREEZE="true"
# values: true | false
```

## Custom Features - Dev Tools

```
# Define a password that blocks access to the site. Should be used for staging sites.

FC_PASSWORD_PROTECT_PASSWORD=""
# values: string (password)
# default: false / empty string
```
```
# Define the URL for the uploads directory that uploads should be accessed
# from if not available locally. The first time the image is accessed from
# the proxy URL it will be stored locally.
# Note: Proxy uploads don't work with Laravel Valet WordPress Driver

FC_PROXY_UPLOADS_URL=""
# values: string (url of remote site's uploads dir, no trailing slash)
# default: false / empty string
```
```
# This configures the above action to not download images that are
# unavailable locally, but always display them from the proxy source.

FC_PROXY_DISPLAY_ONLY=""
# values  : true | false
# default : false / empty string
```

## Custom Features - Domain Tools

On Frame hosting these settings are mostly performed in Serverpilot / Cloudflare:

```
# Defines the domain that the site should be loaded on.
# Will redirect to this domain if accessed via another.
# Because of the logic of force SSL etc this domain wshould be without protocol or slashes
# eg: "mysite.com"
# Note: Required for FORCE_SSL to work

FC_FORCE_DOMAIN=""
# values: string (fully qualified domain name, without slashes or protocal)
# default: false / empty string
```
```
# Used in conjunction with force domain, this will
# redirect to the desired domain on the HTTPS protocol.

FC_PREFER_SSL=""
# values: true | false
# default: false
```
```
# Similar to above, but will also redirect the desired domain to the HTTPS protocol.

FC_FORCE_SSL=""
# values: true | false
# default: false
```


## Custom Features - Mail

```
# Configures the hostname that the site should send emails
# through via the SMTP protocol. Must be used in conjunction
# with username and password.
FC_SMTP_HOST=""
```
```
# Configures the SMTP username.
FC_SMTP_USER=""
```
```
# Configures the SMTP password.
FC_SMTP_PASSWORD=""
```
```
# Configures the port that emails are sent over.
# Defaults to 25 (standard for SMTP) or 1025 in a
# dev environment (the port required for MailHog).
FC_SMTP_PORT=""
```
```
# Defines the email address that should be shown as the sender of the email.
FC_SMTP_FROM=""
```
```
# Defines the name that should be shown as the sender of the email.
FC_SMTP_FROM_NAME=""
```


## Contributing and Extending

We welcome PRs from within the Frame team, however please keep the following in mind when designing features
* Features must be suitable for ALL frame sites
* PHP must be backwards compatible to PHP 5.5
* Changes to the DB, or generation of client side markup, is not recommended
* All module functionality should be opt-in, with sensible defaults



## Sample .env values
```
#
# ALL VALUES IN .ENV ARE STRINGS
# "true" / "false" will be converted to their boolean equivalents
#


# ==============================================================================
# PRODUCTION TOOLS - CODE MANAGEMENT
# ==============================================================================

# values  : true | false
# default : true
FC_CODE_MANAGED="false"

# values  : true | false
# default : false
FC_SITE_MAINTAINED="true"

# values  : string (username)
# default : 'frame'
FC_DEV_USER="frame"

# ==============================================================================
# DOMAIN TOOLS - HTTPS, FORCE DOMAIN
# ==============================================================================

# On Frame hosting this is mostly done via serverpilot/cloudflare

# values  : string (fully qualified domain name)
# default : false / empty string
FC_FORCE_DOMAIN=""

# values  : true | false
# default : false
FC_FORCE_SSL=""

# values  : true | false
# default : false
FC_PREFER_SSL=""


# ==============================================================================
# DEV TOOLS - PASSWORDS, PROXY
# ==============================================================================

# Note to Valet users: Proxy uploads don't work with the Laravel Valet WordPress Driver

# values  : string (password)
# default : false / empty string
FC_PASSWORD_PROTECT_PASSWORD=""

# values  : string (url of remote site's uploads dir, no trailing slash)
# default : false / empty string
FC_PROXY_UPLOADS_URL=""

# values  : true | false
# default : false / empty string
FC_PROXY_DISPLAY_ONLY=""


# ==============================================================================
# PRODUCTION TOOLS - CONTENT FREEZE, TAG MANAGER
# ==============================================================================

# values  : true | false
# default : false / empty string
FC_CONTENT_FREEZE=""

# values  : string (google tag manager container id)
# default : false / empty string
# For Multisite, add blog ID after env variable eg FC_GTM_ID_2. This will override the default FC_GTM_ID if set
FC_GTM_ID=""


# ==============================================================================
# MAIL
# ==============================================================================

# values  : string (smtp details)
# default : false / empty string
FC_SMTP_HOST=""
FC_SMTP_USER=""
FC_SMTP_PASSWORD=""
FC_SMTP_PORT=""
FC_SMTP_FROM=""
FC_SMTP_FROM_NAME=""

# Sample values for Shared mailtrap account
# FC_SMTP_HOST="smtp.mailtrap.io"
# FC_SMTP_USER="b0691b59004c8e"
# FC_SMTP_PASSWORD="0c2f40265e95e1"
# FC_SMTP_PORT="2525"


```