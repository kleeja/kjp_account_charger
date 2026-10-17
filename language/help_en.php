<?php
//
// kjp_account_charger, its guide on the help page of the control panel
// English
//
// The guide is built by kjp_account_charger_help_guide() in functions.php, the texts here can have HTML.
//  - a list is numbered: KJP_CHARGER_HELP_TIP_1, KJP_CHARGER_HELP_TIP_2 ... it ends at the first missing number,
//    so an item can be added or removed without touching the code
//  - KJP_CHARGER_HELP_{LIST}_TITLE is the title of the list, a list without it takes the title that Kleeja gives its type
//  - questions and answers are KJP_CHARGER_HELP_FAQ_Q_1 and KJP_CHARGER_HELP_FAQ_A_1, no HTML in a question
//  - names in <strong> are written as the control panel shows them: the words of Kleeja are in lang/en/,
//    the words of Kleeja Payment in its language/en.php, the words of this plugin in kjp_account_charger_translations()
//

return [
    'KJP_CHARGER_HELP_TITLE' => 'Account Top-up',
    'KJP_CHARGER_HELP_INTRO' =>
        'An add-on of Kleeja Payment. Members top up their balance with PayPal or a card, then spend it on files, paid groups and subscriptions. It works only while Kleeja Payment is installed and enabled.',

    //
    // what the plugin does
    //
    'KJP_CHARGER_HELP_FEATURE_1' =>
        'Adds <strong>Top Up Balance</strong> to the user menu of members whose group has the <strong>Receive Profits</strong> permission.',
    'KJP_CHARGER_HELP_FEATURE_2' =>
        'The member enters an amount, chooses a payment method and pays on the page of PayPal or Stripe. The amount is added to their balance as soon as the payment is complete.',
    'KJP_CHARGER_HELP_FEATURE_3' =>
        'The balance pays for files, paid groups and subscriptions through the <strong>Balance</strong> method of Kleeja Payment.',
    'KJP_CHARGER_HELP_FEATURE_4' => 'You set the smallest amount that a member can add at a time.',

    //
    // the first setup
    //
    'KJP_CHARGER_HELP_SETUP_TITLE' => 'Set up top-ups',
    'KJP_CHARGER_HELP_SETUP_1' =>
        'Install and enable <strong>Kleeja Payment</strong> 2.0 or newer, and set up PayPal, Stripe or both, as <a href="#help-kleeja_payment">its guide</a> explains.',
    'KJP_CHARGER_HELP_SETUP_2' =>
        'Open <strong>Users &amp; Groups</strong>, select <strong>Edit Permissions</strong> on the group of your members, and turn on <strong>Receive Profits</strong>. The same permission lets them price their files and ask for withdrawals.',
    'KJP_CHARGER_HELP_SETUP_3' =>
        'Open <strong>Settings → Kleeja Payment Settings</strong> and set <strong>Minimum top-up amount</strong>.',
    'KJP_CHARGER_HELP_SETUP_4' =>
        'Sign in as a member of that group, open <strong>Top Up Balance</strong> in the user menu, and make a test top-up with the PayPal sandbox or a Stripe test key.',

    //
    // questions
    //
    'KJP_CHARGER_HELP_FAQ_Q_1' => 'Who can top up their balance?',
    'KJP_CHARGER_HELP_FAQ_A_1' =>
        'Signed-in members of a group that has the <strong>Receive Profits</strong> permission, while Kleeja Payment is enabled. Guests can\'t, because a balance belongs to an account.',
    'KJP_CHARGER_HELP_FAQ_Q_2' => 'Why is “Balance” missing from the payment methods?',
    'KJP_CHARGER_HELP_FAQ_A_2' =>
        'A balance can\'t be topped up from itself, so the page offers every active method of Kleeja Payment except <strong>Balance</strong>. When none is left, it says that no payment method is available: turn PayPal or Stripe on in <strong>KJPayments Active Methods</strong>, and set its keys in <strong>Kleeja Payment Settings</strong>.',
    'KJP_CHARGER_HELP_FAQ_Q_3' => 'A member paid, but their balance didn\'t change.',
    'KJP_CHARGER_HELP_FAQ_A_3' =>
        'The amount is added when the member comes back from PayPal or Stripe to your site. If they closed the page before that, the payment stays in <strong>Pending Payments</strong> and nothing is added. With PayPal, no money was taken. With Stripe, the card may have been charged: find the payment in your Stripe dashboard and refund it there.',
    'KJP_CHARGER_HELP_FAQ_Q_4' => 'Where do I see the top-ups?',
    'KJP_CHARGER_HELP_FAQ_A_4' =>
        'In <strong>Payments Control</strong>, with the other payments. Each one is named “Balance top-up for” and the name of the member, and the archive of a day or a month counts them under <strong>Balance Top-ups</strong>. Members see their own on the <strong>My Payments</strong> page.',
    'KJP_CHARGER_HELP_FAQ_Q_5' => 'What happens when I uninstall this plugin?',
    'KJP_CHARGER_HELP_FAQ_A_5' =>
        'The balances of the members stay, and so do the past top-ups in Payments Control. The menu link, the page and the <strong>Minimum top-up amount</strong> setting are removed.',

    //
    // advice
    //
    'KJP_CHARGER_HELP_TIP_1' =>
        'PayPal and Stripe take a fixed fee from every payment, and the member gets the whole amount in their balance. Set the minimum high enough that the fee stays small next to it.',
    'KJP_CHARGER_HELP_TIP_2' =>
        'Keep <strong>Active Balance</strong> on in <strong>KJPayments Active Methods</strong>. Without it, members can top up a balance they can\'t spend.',

    'KJP_CHARGER_HELP_WARNING_1' =>
        'Top-ups and earnings share one balance, and members can ask to withdraw any part of it. Before you select <strong>Send Payout</strong>, check the payments of the member in Payments Control, so you don\'t send out money that was only topped up.',
    'KJP_CHARGER_HELP_WARNING_2' =>
        'Refunds made in PayPal or Stripe don\'t change the balance in Kleeja. If you refund a top-up, the member keeps the amount in their balance.',
    'KJP_CHARGER_HELP_WARNING_3' =>
        'Uninstalling Kleeja Payment deletes the balance of every member, including the amounts they topped up.',

    //
    // the add-ons list of the guide of Kleeja Payment, with the KjPay:KLJ_HELP hook
    //
    'KJP_CHARGER_HELP_ADDON_Q' => 'How do members top up their balance?',
    'KJP_CHARGER_HELP_ADDON_A' =>
        'With the <strong>Account Top-up</strong> add-on, which adds <strong>Top Up Balance</strong> to their user menu. <a href="#help-kjp_account_charger">Its guide</a> explains how to set it up.',
];
