<?php
// Kleeja Plugin
// kjp_account_charger
// Version: 2.0
// Developer: Kleeja Team

// Prevent illegal run
if (!defined('IN_PLUGINS_SYSTEM')) {
    exit();
}

require_once __DIR__ . '/functions.php';

// Plugin Basic Information
$kleeja_plugin['kjp_account_charger']['information'] = [
    // The casual name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'Account Top-up for Kleeja Payment',
        'ar' => 'شحن الرصيد لنظام كليجة للدفع الإلكتروني',
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja Team',
    // This plugin version
    'plugin_version' => '2.0',
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' =>
            'Lets members whose group has the Receive Profits permission top up their balance with the payment methods of Kleeja Payment. Requires Kleeja Payment 2.0 or newer.',
        'ar' =>
            'يتيح لأعضاء المجموعات التي تملك صلاحية «تلقي الأرباح» شحن أرصدتهم بطرق الدفع في نظام كليجة للدفع الإلكتروني. يتطلب نظام كليجة للدفع الإلكتروني 2.0 أو أحدث.',
    ],
    // the setting of the plugin is on the settings page of Kleeja Payment
    'settings_page' => 'cp=options&smt=kleeja_payment',
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '4.0.0',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '4.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    // kleeja_payment (10) runs first, it defines support_kjPay and loads its words
    'plugin_priority' => 0,
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['kjp_account_charger']['first_run']['ar'] = "
أضافت هذه الإضافة رابط «شحن الرصيد» إلى قائمة الأعضاء الذين تملك مجموعتهم صلاحية «تلقي الأرباح»،
وإعداد «الحد الأدنى لمبلغ الشحن» إلى إعدادات نظام كليجة للدفع الإلكتروني.
<br>
<a href='./index.php?cp=options&amp;smt=kleeja_payment'>الإعدادات</a> -
<a href='./index.php?cp=s_help#help-kjp_account_charger'>دليل الإضافة</a> -
<a href='https://github.com/kleeja/kjp_account_charger/issues' target='_blank' rel='noopener'>الإبلاغ عن مشكلة</a>
";

$kleeja_plugin['kjp_account_charger']['first_run']['en'] = "
This plugin added Top Up Balance to the user menu of members whose group has the Receive Profits permission,
and the Minimum top-up amount setting to the settings of Kleeja Payment.
<br>
<a href='./index.php?cp=options&amp;smt=kleeja_payment'>Settings</a> -
<a href='./index.php?cp=s_help#help-kjp_account_charger'>Plugin guide</a> -
<a href='https://github.com/kleeja/kjp_account_charger/issues' target='_blank' rel='noopener'>Report a problem</a>
";

// Plugin Installation function
$kleeja_plugin['kjp_account_charger']['install'] = function ($plg_id) {
    global $SQL, $dbprefix, $config;

    // Kleeja has listed the plugin before this runs, without Kleeja Payment it can't work,
    // so it is taken out of the list again, and the admin sees why
    if (!kjp_account_charger_is_ready()) {
        $SQL->build([
            'DELETE' => "{$dbprefix}plugins",
            'WHERE' => 'plg_id = :plg_id',
            'BIND' => ['plg_id' => (int) $plg_id],
        ]);

        $words = kjp_account_charger_translations();
        $message = ($words[$config['language'] ?? 'en'] ?? $words['en'])['KJP_CHRG_REQUIRES_KJP'];

        // the control panel, the store installs from there too. No redirect, the error is there to be read
        if (function_exists('kleeja_admin_err')) {
            kleeja_admin_err($message);
        }

        throw new RuntimeException($message);
    }

    add_config_r([
        'kjp_min_charge_amount' => [
            'value' => '10',
            'html' => kjp_account_charger_config_field(),
            'plg_id' => (int) $plg_id,
            'type' => 'kleeja_payment',
            // after the price limits of Kleeja Payment
            'order' => 10,
        ],
    ]);

    // under the id of kleeja_payment, they are deleted with it
    kjp_account_charger_write_words(kjp_account_charger_plugin_id('kleeja_payment'));
};

// 2.0 works with Kleeja Payment 2.0 on Kleeja 4. The words are written again, so a new version brings its own,
// with queries of its own: the functions of Kleeja run hooks, and the plugins are still loading
$kleeja_plugin['kjp_account_charger']['update'] = function ($old_version, $new_version) {
    $plg_id = kjp_account_charger_plugin_id('kjp_account_charger');

    // the minimum was a text field among the first settings of Kleeja Payment, its value stays
    kjp_account_charger_save_config($plg_id);

    // without Kleeja Payment, they belong to this plugin, the fallback of boot_common shows them anyway
    $owner = kjp_account_charger_plugin_id('kleeja_payment') ?: $plg_id;

    // KJP_ACC_CHARGEED was the word of a successful top-up in 1.x
    kjp_account_charger_write_words($owner, ['KJP_ACC_CHARGEED']);

    // the settings, and the compiled page of the old version: an update doesn't delete the compiled templates
    kjp_account_charger_clear_cache(['data_config', 'tpl_charge_page*']);
};

// Plugin Uninstallation, function to be called at unistalling.
// The balances and the payments stay, they belong to Kleeja Payment
$kleeja_plugin['kjp_account_charger']['uninstall'] = function ($plg_id) {
    global $SQL, $dbprefix;

    delete_config('kjp_min_charge_amount');

    // Payments Control still names the payments of the top-ups, kleeja_payment deletes those words with its own
    $SQL->build([
        'DELETE' => "{$dbprefix}lang",
        'WHERE' => 'word IN (:words)',
        'BIND' => [
            'words' => array_values(
                array_diff(array_keys(kjp_account_charger_translations()['en']), kjp_account_charger_kept_words()),
            ),
        ],
    ]);

    // the ones that the update gave this plugin, when Kleeja Payment was missing.
    // A plg_id of 0 would match the words of Kleeja
    if ((int) $plg_id > 0) {
        $SQL->build([
            'DELETE' => "{$dbprefix}lang",
            'WHERE' => 'plg_id = :plg_id',
            'BIND' => ['plg_id' => (int) $plg_id],
        ]);
    }

    kjp_account_charger_clear_cache(['data_lang*']);
};

// Plugin functions
$kleeja_plugin['kjp_account_charger']['functions'] = [
    // the words of the plugin, for a site where they are missing from the language table.
    // The words of the table are read after this hook, so they win
    'boot_common' => function ($args) {
        global $olang;

        $olang = (array) $olang + kjp_account_charger_words();

        // kleeja_payment returns its words too, the last one returned is the one that Kleeja keeps
        return compact('olang');
    },

    // the link in the user menu
    'Saaheader_links_func' => function ($args) {
        if (!kjp_account_charger_is_ready()) {
            return;
        }

        global $olang;

        $side_menu = $args['side_menu'];
        $side_menu[] = [
            'name' => 'charge_account',
            'title' => $olang['KJP_CHRG_ACNT'],
            'url' => kjp_account_charger_page_url(),
            'icon' => 'circle-plus',
            'show' => $args['user_is'] && kjp_account_charger_can_charge(),
        ];

        // kleeja_payment moved the logout link to the key "logout", it stays the last one
        if (isset($side_menu['logout'])) {
            $logout = $side_menu['logout'];
            unset($side_menu['logout']);
            $side_menu['logout'] = $logout;
        }

        return compact('side_menu');
    },

    // the page of the members, ucp.php?go=charge_account
    'default_usrcp_page' => function ($args) {
        global $olang, $lang;

        if (g('go') !== 'charge_account' || !kjp_account_charger_can_charge()) {
            return;
        }

        $page_url = kjp_account_charger_page_url();
        $methods = kjp_account_charger_methods();
        $min_amount = kjp_account_charger_min_amount();
        $currency = kleeja_html_encode(strtoupper(kjp_setting('kjp_iso_currency_code')));

        if (ip('start_charge')) {
            if (!kleeja_check_form_key(kjp_account_charger_form_key(), 3600)) {
                kleeja_err($lang['INVALID_FORM_KEY'], '', true, $page_url, 2);
            }

            $method = p('charge_method');
            $amount = kjp_account_charger_amount(p('charge_amount'));

            if (!in_array($method, $methods, true)) {
                kleeja_err($olang['KJP_CHRG_NO_METHOD'], '', true, $page_url, 3);
            } elseif ($amount <= 0) {
                kleeja_err($olang['KJP_CHRG_INVALID_AMOUNT'], '', true, $page_url, 3);
            } elseif ($amount < $min_amount) {
                kleeja_err(sprintf($olang['KJP_MIN_CHARGE_IS'], $min_amount . ' ' . $currency), '', true, $page_url, 3);
            }

            // the payment starts on the page of kleeja_payment, the link key is checked there
            redirect(
                KJP::getPayURL('charge_account', $method) .
                    '&amount=' .
                    $amount .
                    '&' .
                    kleeja_add_form_key_get(kjp_account_charger_form_key()),
            );
        }

        $titlee = $olang['KJP_CHRG_ACNT'];
        $no_request = false;
        $stylee = 'charge_page';
        $styleePath = kjp_account_charger_template_path();
        $is_style_supported = is_style_supported();
        $formAction = $page_url;
        $kjFormKeyPost = kleeja_add_form_key(kjp_account_charger_form_key());
        $minAmount = $min_amount;
        $minAmountText = sprintf($olang['KJP_MIN_CHARGE_IS'], $min_amount . ' ' . $currency);
        $payMethods = [];

        foreach ($methods as $method) {
            $payMethods[] = ['name' => kjp_method_title($method), 'value' => $method];
        }

        return compact(
            'titlee',
            'no_request',
            'stylee',
            'styleePath',
            'is_style_supported',
            'formAction',
            'kjFormKeyPost',
            'payMethods',
            'minAmount',
            'currency',
            'minAmountText',
        );
    },

    // go.php?go=kj_payment&method=..&action=charge_account&amount=.. creates the payment
    'KjPay:default_action' => function ($args) {
        global $olang, $lang, $usrcp;

        if ($args['action'] !== 'charge_account' || !kjp_account_charger_can_charge()) {
            return;
        }

        $page_url = kjp_account_charger_page_url();
        $amount = kjp_account_charger_amount(g('amount'));

        // the link comes from the page of the member, not from another site
        if (!kleeja_check_form_key_get(kjp_account_charger_form_key())) {
            kleeja_err($lang['INVALID_FORM_KEY'], '', true, $page_url, 2);
        } elseif ($args['method'] === 'balance') {
            kleeja_err($olang['KJP_CHRG_NO_BALANCE'], '', true, $page_url, 3);
        } elseif ($amount <= 0) {
            kleeja_err($olang['KJP_CHRG_INVALID_AMOUNT'], '', true, $page_url, 3);
        } elseif ($amount < kjp_account_charger_min_amount()) {
            $minimum = kjp_account_charger_min_amount() . ' ' . strtoupper(kjp_setting('kjp_iso_currency_code'));

            kleeja_err(sprintf($olang['KJP_MIN_CHARGE_IS'], kleeja_html_encode($minimum)), '', true, $page_url, 3);
        }

        $PAY = $args['PAY'];

        // PayPal and Stripe go to their page from here, the balance is added when the member comes back
        $PAY->CreatePayment('charge_account', [
            'id' => kjp_user_id(),
            'name' => $usrcp->name(),
            'price' => $amount,
        ]);

        // a method of another plugin may show a page of its own on Kleeja
        foreach ($PAY->varsForCreatePayment() as $varName => $varValue) {
            $GLOBALS[$varName] = $varValue;
        }

        $request = true;

        return compact('request');
    },

    // the balance method asks for the price here, a balance is never topped up from itself
    'KjPay:itemInfoExport_charge_account' => function ($args) {
        global $olang;

        kleeja_err($olang['KJP_CHRG_NO_BALANCE'], '', true, kjp_account_charger_page_url(), 3);
    },

    // the payment is approved, kleeja_payment calls this once for it
    'KjPay:notFoundedAction_charge_account' => function ($args) {
        global $olang;

        $payment = $args['payment'];
        $buyer = (int) $payment['user'];
        // what the payment provider took, never an amount from the visitor
        $amount = round((float) $payment['payment_amount'], 2);

        // a top-up is made by a member for their own balance
        if ($buyer <= 0 || $buyer !== (int) $payment['item_id'] || $amount <= 0) {
            kjp_log('the top-up of the payment ' . (int) $payment['id'] . ' was not added, it has no member or amount');

            return;
        }

        kjp_give_balance($buyer, $amount);

        // the page of a successful payment shows this word
        $olang['KJP_JUIN_SUCCESS'] = sprintf(
            $olang['KJP_ACC_CHARGED'],
            kjp_price($amount, (string) $payment['payment_currency']),
        );

        $toGlobal = (array) ($args['toGlobal'] ?? []);
        $toGlobal['olang'] = $olang;

        return compact('toGlobal');
    },

    // the member left the page of the payment provider without paying
    'KjPay:PayCancel_charge_account' => function ($args) {
        redirect(kjp_account_charger_page_url());
    },

    // Payments Control, the payments of the top-ups of a member
    'KjPay:allTrnc_charge_account' => function ($args) {
        global $olang;

        $user_id = (int) $args['trnc_item'];
        $name = $args['UserById'][$user_id] ?? '';

        $all_trnc_page_title = sprintf(
            $olang['KJP_PAY_OF'],
            sprintf($olang['KJP_ACT_CHARGE_ACCOUNT'], $name !== '' ? kleeja_html_display($name) : '#' . $user_id),
        );

        return compact('all_trnc_page_title');
    },

    // the guide of Kleeja Payment lists its add-ons, this one points to its own guide
    'KjPay:KLJ_HELP' => function ($args) {
        $words = kjp_account_charger_help_words();

        $KJP_HELP = (array) $args['KJP_HELP'];
        $KJP_HELP[] = [
            'ID' => 'KJP_ACC_CHARGE',
            'TITLE' => $words['KJP_CHARGER_HELP_ADDON_Q'],
            'CONTENT' => $words['KJP_CHARGER_HELP_ADDON_A'],
        ];

        return compact('KJP_HELP');
    },

    // the guide of the plugin on the help page of the control panel, its words are in language/help_{code}.php
    'admin_help_guides' => function ($args) {
        $help_guides = $args['help_guides'];
        // the name of the plugin is the key, so Kleeja knows that the plugin has its guide
        $help_guides['kjp_account_charger'] = kjp_account_charger_help_guide();

        return compact('help_guides');
    },
];
