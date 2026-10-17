# Account Top-up for Kleeja Payment

An add-on of [Kleeja Payment](https://github.com/kleeja/kleeja_payment) for [Kleeja](https://github.com/kleeja/kleeja), the file upload script. Members top up their balance with the payment methods of Kleeja Payment, then spend it on files, paid groups and subscriptions.

## Requirements

- Kleeja 4.0.0 or newer.
- Kleeja Payment 2.0 or newer, installed and enabled, with PayPal, Stripe or both set up.

## Install

Install Kleeja Payment first, then install this plugin from **Admin → Plugins**. Without Kleeja Payment, the installation stops and says why.

Then give the members the permission: open **Users & Groups**, select **Edit Permissions** on their group, and turn on **Receive Profits**. A **Top Up Balance** link appears in their user menu.

The guide of the plugin is on the **Help** page of the control panel, in English and Arabic. Its words are in `language/help_en.php` and `language/help_ar.php`.

## Settings

| Setting               | Where                                  | What it does                                                                                                      |
| --------------------- | -------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| Minimum top-up amount | **Settings → Kleeja Payment Settings** | The smallest amount a member can add at a time, as a whole number in the currency of the site. The default is 10. |

## How a top-up works

1. The member opens **Top Up Balance** (`ucp.php?go=charge_account`), enters an amount and chooses a payment method. The balance itself is not offered.
2. Kleeja Payment creates the payment and sends the member to the page of PayPal or Stripe.
3. When the member comes back and the payment is approved, the amount of that payment is added to their balance. Payments Control lists it as "Balance top-up for" and the name of the member.

The balances and the past top-ups belong to Kleeja Payment, so they stay when this plugin is uninstalled.

## Styles

The plugin has templates for the `bootstrap` and `og_default` styles, in `html/bootstrap` and `html/og_default`. A style that depends on one of them gets its template. Another style brings its own copy as `styles/<name>/kj_payment/charge_page.html`, like the templates of Kleeja Payment.

The template gets these variables:

| Variable             | Content                                                         |
| -------------------- | --------------------------------------------------------------- |
| `formAction`         | The address the form posts to                                   |
| `kjFormKeyPost`      | The hidden form key, print it inside the form                   |
| `payMethods`         | A loop of the payment methods, with `{{name}}` and `{{value}}`  |
| `minAmount`          | The minimum amount, as a number                                 |
| `currency`           | The currency code of the site                                   |
| `minAmountText`      | "The minimum top-up is 10 USD.", in the language of the visitor |
| `is_style_supported` | Whether Kleeja Payment supports the style of the site           |

The form posts `charge_amount`, `charge_method` and `start_charge`.

## Updating from 1.x

- Version 2.0 needs Kleeja 4 and Kleeja Payment 2.0.
- The amount added to a balance is the amount of the approved payment. Update as soon as you can.
- The balance can't top up itself, and the link that starts a payment is checked with a form key.
- When a member cancels on the page of PayPal or Stripe, they come back to **Top Up Balance**.
- The template moved to `html/bootstrap/charge_page.html` and uses the Bootstrap 5.3 of the `bootstrap` style. A style that has its own `kj_payment/charge_page.html` has to update it.
- The words were rewritten in English and Arabic. `KJP_ACC_CHARGEED` is now `KJP_ACC_CHARGED`.

## Developer

Kleeja Team
