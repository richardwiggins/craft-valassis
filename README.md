# Valassis plugin for Craft CMS 3.x

Attach Valassis coupons to Contact form requests

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require superbig/valassis

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Valassis.

### From a private repository

1. Add these entries to Craft's composer.json file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/sjelfull/craft-valassis"
        }
    ],
    "require": {
        ...
        "superbig/craft-valassis": "dev-master"
    }
}
```

2. `composer update`

### Freeform modifications

The current version of Freeform (2.0.1) is missing the necessary hook to insert the coupon, so I have modified a few core files.

These are contain in the folder `freeform-hacked-files`, and you need to copy it into the relevant directories.

1. Copy `RenderEmailEvent.php` to `/vendor/solspace/craft3-freeform/src/Events/Mailer`
2. Copy `MailerService.php` to `vendor/solspace/craft3-freeform/src/Services` (overwrite the existing file)

Solspace has confirmed that they will add the hook in the next release.

*Note: If you update Freeform, these files may be overwrritten*

## Configuring Valassis

Copy the sample config.php into Craft's config directory, usually config/, and update all the fields

```php
<?php
return [
    // Username from Valassis
    'username'           => 'username',
    // Password
    'password'           => 'password',

    // The API endpoint received from Valassis
    'printUrl'           => 'https://coupons.valassis.eu/capi/directPrint/xxxxxxxxxx',

    // Where customers should return after printing
    'returnUrl'          => '',

    // The Freeform notification handles you want to trigger this for
    'couponEmailHandles' => [
        'couponEmail',
    ],
];
```

## Using Valassis

When a Freeform submission is sent, the Valassis plugin will listen for notifications constrained by the config`s `couponEmailHandles` setting.

The Valassis plugin expects a submission to contain these 3 fields:

- email
- firstName
- lastName

### Checking if a user should receieve a coupon via email

The plugin will check these:

1. If a user with the same email has received a coupon before, it will not generate a new coupon or send off an email.
2. If there is no available coupons for the current site, it will stop processing
3. It will make a call to Valassis to generate a coupon, and if the response isn't valid, it will stop processing

If all the steps above passes, it will save the email and name as a customer record, tie to the coupon, and send off a email

### Email notification

Setup a email notification with your desired content under Freeform Lite -> Email Notifications. Make sure to copy the emails handle into the `couponEmailHandles` setting.

The email notification will receive the Coupon model as the variable `coupon`.

To access the coupon's print URL, you can use the following code:

```twig
...

{% if coupon is defined %}
    You may download your coupon from {{ coupon.getUrl() }}
{% endif %}
```

Brought to you by [Superbig](https://superbig.co)
