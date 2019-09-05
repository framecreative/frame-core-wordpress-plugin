# Changelog

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
