<?php
// Kleeja Plugin
// kjp_account_charger, the functions of the plugin
// Developer: Kleeja Team

// Prevent illegal run
if (!defined('IN_PLUGINS_SYSTEM')) {
    exit();
}

/**
 * the words of the plugin, in English and Arabic
 *
 * They are written to the language table under the id of kleeja_payment, as 1.x did: the payments of a top-up
 * keep their name in Payments Control after this plugin is removed. The words of KJP_ACT_ are made of the name
 * of the action (charge_account), kleeja_payment reads them so
 *
 * @return array
 */
function kjp_account_charger_translations(): array
{
    return [
        'en' => [
            // the payments of a top-up, in the lists of kleeja_payment
            'KJP_ACT_CHARGE_ACCOUNT' => 'Balance top-up for <strong>%s</strong>',
            'KJP_ACT_ARCH_CHARGE_ACCOUNT' => 'Balance Top-ups',
            // the page of the members
            'KJP_CHRG_ACNT' => 'Top Up Balance',
            'KJP_CHRG_INTRO' => 'The amount is added to your balance as soon as the payment is complete.',
            'KJP_CHRG_AMNT' => 'Amount',
            'KJP_CHRG_MTHD' => 'Payment method',
            'KJP_CHRG' => 'Continue to payment',
            'KJP_MIN_CHARGE_IS' => 'The minimum top-up is <bdi>%s</bdi>.',
            'KJP_ACC_CHARGED' => 'Your balance has been topped up with <bdi>%s</bdi>.',
            'KJP_CHRG_INVALID_AMOUNT' => 'Enter the amount as a whole number, without decimals.',
            'KJP_CHRG_NO_METHOD' => 'No payment method is available for top-ups right now. Please try again later.',
            'KJP_CHRG_NO_BALANCE' => 'A balance can\'t be topped up from itself. Choose another payment method.',
            // the settings page, the <small> is the hint under the name
            'KJP_MIN_CHARGE_AMOUNT' =>
                'Minimum top-up amount<small>The smallest amount a member can add to their balance at a time, as a whole number in the currency of the site.</small>',
            // the plugins page, when Kleeja Payment is missing
            'KJP_CHRG_REQUIRES_KJP' =>
                'Account Top-up is an add-on of Kleeja Payment. Install and enable Kleeja Payment 2.0 or newer first, then install this plugin.',
        ],
        'ar' => [
            'KJP_ACT_CHARGE_ACCOUNT' => 'شحن رصيد <strong>%s</strong>',
            'KJP_ACT_ARCH_CHARGE_ACCOUNT' => 'شحن الرصيد',
            'KJP_CHRG_ACNT' => 'شحن الرصيد',
            'KJP_CHRG_INTRO' => 'يُضاف المبلغ إلى رصيدك فور اكتمال الدفع.',
            'KJP_CHRG_AMNT' => 'المبلغ',
            'KJP_CHRG_MTHD' => 'طريقة الدفع',
            'KJP_CHRG' => 'المتابعة إلى الدفع',
            'KJP_MIN_CHARGE_IS' => 'الحد الأدنى للشحن هو <bdi>%s</bdi>.',
            'KJP_ACC_CHARGED' => 'تم شحن رصيدك بمبلغ <bdi>%s</bdi>.',
            'KJP_CHRG_INVALID_AMOUNT' => 'أدخل المبلغ رقماً صحيحاً، دون كسور عشرية.',
            'KJP_CHRG_NO_METHOD' => 'لا تتوفر حالياً طريقة دفع لشحن الرصيد. يرجى المحاولة لاحقاً.',
            'KJP_CHRG_NO_BALANCE' => 'لا يمكن شحن الرصيد من الرصيد نفسه. اختر طريقة دفع أخرى.',
            'KJP_MIN_CHARGE_AMOUNT' =>
                'الحد الأدنى لمبلغ الشحن<small>أقل مبلغ يستطيع العضو إضافته إلى رصيده في المرة الواحدة، رقماً صحيحاً بعملة الموقع.</small>',
            'KJP_CHRG_REQUIRES_KJP' =>
                'شحن الرصيد ملحق لنظام كليجة للدفع الإلكتروني. ثبّت نظام كليجة للدفع الإلكتروني 2.0 أو أحدث وفعّله أولاً، ثم ثبّت هذه الإضافة.',
        ],
    ];
}

/**
 * the words that stay after the plugin is removed, Payments Control still lists the payments of the top-ups
 *
 * @return array
 */
function kjp_account_charger_kept_words(): array
{
    return ['KJP_ACT_CHARGE_ACCOUNT', 'KJP_ACT_ARCH_CHARGE_ACCOUNT'];
}

/**
 * the words of the plugin in the language of the visitor, then in English,
 * for the words that are missing from the language table
 *
 * @return array
 */
function kjp_account_charger_words(): array
{
    global $config;
    static $words = [];

    $language = (string) ($config['language'] ?? 'en');

    if (!isset($words[$language])) {
        $translations = kjp_account_charger_translations();
        $words[$language] = ($translations[$language] ?? []) + $translations['en'];
    }

    return $words[$language];
}

/**
 * Kleeja Payment 2.0 or newer is installed and enabled, the plugin uses its functions
 *
 * @return bool
 */
function kjp_account_charger_is_ready(): bool
{
    return defined('support_kjPay') && function_exists('kjp_give_balance') && function_exists('kjp_can');
}

/**
 * the current user is a member whose group has the Receive Profits permission, which comes with a balance
 *
 * @return bool
 */
function kjp_account_charger_can_charge(): bool
{
    return kjp_account_charger_is_ready() && kjp_user_id() > 0 && kjp_can('recaive_profits');
}

/**
 * the smallest amount of a top-up, a whole number of 1 or more
 *
 * @return int
 */
function kjp_account_charger_min_amount(): int
{
    return max(1, (int) ceil((float) kjp_setting('kjp_min_charge_amount')));
}

/**
 * an amount that a member typed, a whole number
 *
 * @param  mixed $amount
 * @return int   0 when it is not a whole number, nine digits at most
 */
function kjp_account_charger_amount($amount): int
{
    $amount = trim((string) $amount);

    return preg_match('/^[0-9]{1,9}$/', $amount) ? (int) $amount : 0;
}

/**
 * the payment methods that can top up a balance: the active ones, except the balance itself
 *
 * @return array
 */
function kjp_account_charger_methods(): array
{
    return array_values(array_diff(getPaymentMethods(), ['balance']));
}

/**
 * name of the form key of a top-up, for the current member
 *
 * @return string
 */
function kjp_account_charger_form_key(): string
{
    return 'kjp_charge_account_' . kjp_user_id();
}

/**
 * the page of the members where they top up their balance
 *
 * @return string
 */
function kjp_account_charger_page_url(): string
{
    global $config;

    return $config['siteurl'] . 'ucp.php?go=charge_account';
}

/**
 * folder of the template of the page, a style can have its own copy in its "kj_payment" folder,
 * like the templates of kleeja_payment
 *
 * @return string
 */
function kjp_account_charger_template_path(): string
{
    global $THIS_STYLE_PATH_ABS;

    if (file_exists($THIS_STYLE_PATH_ABS . 'kj_payment/charge_page.html')) {
        return $THIS_STYLE_PATH_ABS . 'kj_payment/';
    }

    // a style that the plugin has no template for gets the bootstrap one, it says that the style is not supported
    return __DIR__ . '/html/' . (kjp_style_folder() ?: 'bootstrap') . '/';
}

/**
 * the input of the minimum on the settings page, a_configs.php adds the class of the inputs itself
 *
 * @return string
 */
function kjp_account_charger_config_field(): string
{
    return '<input type="number" id="kjp_min_charge_amount" name="kjp_min_charge_amount"' .
        ' value="{con.kjp_min_charge_amount}" min="1" step="1" inputmode="numeric" dir="ltr" />';
}

/*
 * The update of a plugin runs while Kleeja is still loading the plugins, before the groups and the current user
 * are known. The config and language functions of Kleeja need them, and they run hooks, which loads the plugins
 * again. So the plugin writes its rows with the queries below, in the update and in the installation.
 */

/**
 * id of a plugin in the plugins table
 *
 * @param  string $name
 * @return int    0 when it is not installed
 */
function kjp_account_charger_plugin_id(string $name): int
{
    global $SQL, $dbprefix;

    $result = $SQL->build([
        'SELECT' => 'plg_id',
        'FROM' => "{$dbprefix}plugins",
        'WHERE' => 'plg_name = :name',
        'BIND' => ['name' => $name],
    ]);

    $row = $SQL->fetch_array($result);
    $SQL->freeresult($result);

    return (int) ($row['plg_id'] ?? 0);
}

/**
 * the setting of the minimum with its current input, it is added when it is missing
 *
 * @param int $plg_id
 */
function kjp_account_charger_save_config(int $plg_id): void
{
    global $SQL, $dbprefix;

    $result = $SQL->build([
        'SELECT' => '`name`',
        'FROM' => "{$dbprefix}config",
        'WHERE' => '`name` = :name',
        'BIND' => ['name' => 'kjp_min_charge_amount'],
    ]);

    $exists = (bool) $SQL->fetch_array($result);
    $SQL->freeresult($result);

    if ($exists) {
        $SQL->build([
            'UPDATE' => "{$dbprefix}config",
            'SET' => '`option` = :option, `display_order` = 10',
            'WHERE' => '`name` = :name',
            'BIND' => ['option' => kjp_account_charger_config_field(), 'name' => 'kjp_min_charge_amount'],
        ]);

        return;
    }

    // after the price limits of Kleeja Payment
    $SQL->build([
        'INSERT' => '`name`, `value`, `option`, `display_order`, `type`, `plg_id`, `dynamic`',
        'INTO' => "{$dbprefix}config",
        'VALUES' => ':name, :value, :option, 10, :type, :plg_id, 0',
        'BIND' => [
            'name' => 'kjp_min_charge_amount',
            'value' => '10',
            'option' => kjp_account_charger_config_field(),
            'type' => 'kleeja_payment',
            'plg_id' => $plg_id,
        ],
    ]);
}

/**
 * write the words of the plugin to the language table, in place of the ones of an older version
 *
 * @param int   $plg_id the plugin they belong to, they are deleted with it
 * @param array $old    words of older versions that are gone
 */
function kjp_account_charger_write_words(int $plg_id, array $old = []): void
{
    global $SQL, $dbprefix;

    $translations = kjp_account_charger_translations();

    // the words of every plugin id, 1.x wrote them twice when it was installed again
    $SQL->build([
        'DELETE' => "{$dbprefix}lang",
        'WHERE' => 'word IN (:words) AND lang_id IN (:languages)',
        'BIND' => [
            'words' => array_merge(array_keys($translations['en']), $old),
            'languages' => array_keys($translations),
        ],
    ]);

    //a plg_id of 0 is the one of the words of Kleeja, they would never be deleted
    if ($plg_id > 0) {
        foreach ($translations as $lang_id => $words) {
            foreach ($words as $word => $trans) {
                $SQL->build([
                    'INSERT' => 'word, trans, lang_id, plg_id',
                    'INTO' => "{$dbprefix}lang",
                    'VALUES' => ':word, :trans, :lang_id, :plg_id',
                    'BIND' => ['word' => $word, 'trans' => $trans, 'lang_id' => $lang_id, 'plg_id' => $plg_id],
                ]);
            }
        }
    }

    kjp_account_charger_clear_cache(['data_lang*']);
}

/**
 * delete cached data and compiled templates, without the hooks of delete_cache()
 *
 * @param array $names names of files in the cache folder, without .php, * is any text
 */
function kjp_account_charger_clear_cache(array $names): void
{
    foreach ($names as $name) {
        foreach (glob(PATH . 'cache/' . $name . '.php') ?: [] as $file) {
            // with OPcache, Kleeja would read the old file until it checks it again
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($file, true);
            }

            @unlink($file);
        }
    }
}

/**
 * the words of the guide in the language of the admin, a word that the translation misses is shown in English
 *
 * @return array
 */
function kjp_account_charger_help_words(): array
{
    global $config;

    $words = (array) require __DIR__ . '/language/help_en.php';
    $language = preg_replace('/[^a-z0-9_-]/i', '', (string) ($config['language'] ?? ''));
    $translation = __DIR__ . "/language/help_{$language}.php";

    if ($language !== '' && $language !== 'en' && file_exists($translation)) {
        // in its own line, Prettier drops the brackets of (require $translation) + $words
        $translated = require $translation;
        $words = (array) $translated + $words;
    }

    return $words;
}

/**
 * a section of the guide from its numbered words, like KJP_CHARGER_HELP_TIP_1, KJP_CHARGER_HELP_TIP_2 ..
 * its title, when it has its own, is KJP_CHARGER_HELP_TIP_TITLE
 *
 * @param  array  $words
 * @param  string $type  how Kleeja shows it: features, steps, tips, warnings or faq
 * @param  string $name  the name of its words, KJP_CHARGER_HELP_{name}_1
 * @return array
 */
function kjp_account_charger_help_section(array $words, string $type, string $name): array
{
    $prefix = 'KJP_CHARGER_HELP_' . $name;
    $section = ['type' => $type, 'title' => $words[$prefix . '_TITLE'] ?? '', 'items' => []];

    for ($n = 1; isset($words[$prefix . ($type === 'faq' ? '_Q_' : '_') . $n]); $n++) {
        $section['items'][] =
            $type === 'faq'
                ? ['q' => $words[$prefix . '_Q_' . $n], 'a' => $words[$prefix . '_A_' . $n] ?? '']
                : $words[$prefix . '_' . $n];
    }

    return $section;
}

/**
 * the guide as the admin_help_guides hook of Kleeja takes it
 *
 * @return array
 */
function kjp_account_charger_help_guide(): array
{
    global $lang;

    $words = kjp_account_charger_help_words();

    return [
        'group' => 'plugins',
        'icon' => 'circle-plus',
        'title' => $words['KJP_CHARGER_HELP_TITLE'],
        'intro' => $words['KJP_CHARGER_HELP_INTRO'],
        // the plugin has no page of its own, its setting is with the ones of Kleeja Payment
        'link' => './?cp=options&amp;smt=kleeja_payment',
        'link_title' => $lang['HELP_PLUGIN_SETTINGS'],
        // tips and warnings are shown beside the others on wide screens
        'sections' => [
            kjp_account_charger_help_section($words, 'features', 'FEATURE'),
            kjp_account_charger_help_section($words, 'steps', 'SETUP'),
            kjp_account_charger_help_section($words, 'faq', 'FAQ'),
            kjp_account_charger_help_section($words, 'tips', 'TIP'),
            kjp_account_charger_help_section($words, 'warnings', 'WARNING'),
        ],
    ];
}
