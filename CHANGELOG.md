# Changelog

## Frame Core 3.1
- Login branding (logo detection + `custom-login` theme support) now also applies to the password protect screen, via the `password_protected_login_head` action (works with the built-in gate and the standalone Password Protected plugin)
- New `custom-login` options: `logo_height`, `logo_width`, `accent_color_hover`, `button_text_color_hover`
- Content freeze login notice now uses the env-warning card styling so it reads correctly on branded backgrounds, and returns through the `login_message` filter instead of echoing
- Removed dead `login_body_classes` hook and tidied login filter registrations
- Fixed typo in the login environment warning

## Frame Core 3.0
**Requires PHP 8.1 or higher**
- Updated to latest Rollbar SDK (`2.x` => `4.x`)
- Updated to latest `composer/installers` package

## Frame Core 2.0.0
- `MAJOR` Add support for Rollbar Error Logging
- `MAJOR` Include the Rollbar PHP SDK as a dependency via composer.
- `MINOR` Site Health Widget and Section is no longer hidden to Frame User

## Frame Core 1.9.0
- Disabled site health dashboard widget by default.
- Configuration options to re-enable dashboard widget or disable site health page completely.

## Frame Core 1.8.0
- Use the `send_headers` action to send our `no-follow` headers on staging
- Add URL based staging detection for the noindex function, to prevent mistakes

####Url based Staging Detection
If you are using a custom staging domain (not frmdv.com or frame.hosting) then you can add the custom domain via the `frame/core/staging_domains` filter.

## Frame Core 1.7.0
- Add login screen helper, to improve the client experience.
- Include theme support check so that we can easily register custom login styles

## Frame Core 1.6.0
- Added multisite functionality for Google Tag Manager

## Frame Core 1.5.0
- Added author enum protection

## Frame Core 1.4.0
- Simplified plugin deactivate list to accept and folder names and removed activation functionality

## Frame Core 1.3.1
- Added multisite support to plugin deactivate list

## Frame Core 1.3.0
- Added common mail /SMPT plugins to staging deactivate list

## Frame Core 1.2.0
- Conditional plugin loading
- Changes to how we detect environments (they are normalised now)


## Frame Core 1.1.0

- Implement content freeze feature
- Increased GTM tag priority

## Frame Core 1.0.14

- Remove meta generators tags from wp_head
- Hide yoast version when using premium

## Frame Core 1.0.13

- Implement Google Tag Manager configuration

## Frame Core 1.0.12

- Prevent search engines if not a live environment

## Frame Core 1.0.11

- Disable WC CBA Admin nag

## Frame Core 1.0.10

- added FC_CODE_MANAGED - constant defaults true, Disables plugin installation and displays a notice*

- added FC_SITE_MAINTAINED constant - defaults false, as above and also prevents plugin updates because we manage them.

- added FC_DEV_USER constant - no default Allows us to set a username that is exempt from all admin restrictions.

- set auto minor updates for core so that security patches and translations are installed automatically.
