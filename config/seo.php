<?php

declare(strict_types=1);

/**
 * Laravel SEO Configuration
 *
 * Industrias Husky
 *
 * @see https://github.com/rankbeam/laravel-seo
 */

return [

    'cli_locale' => env('SEO_CLI_LOCALE', 'es'),

    /*
    |--------------------------------------------------------------------------
    | Site-wide Defaults
    |--------------------------------------------------------------------------
    */

    'site_name' => env('APP_NAME', 'Industrias Husky'),

    'title_suffix' => ' | ' . env('APP_NAME', 'Industrias Husky'),

    'title_suffix_skip_when_contains' => [
        'Industrias Husky',
    ],

    'default_og_image' => env(
        'SEO_DEFAULT_OG_IMAGE',
        '/images/og-default.jpg'
    ),

    /*
    |--------------------------------------------------------------------------
    | Generated OG Images
    |--------------------------------------------------------------------------
    */

    'og_image' => [
        'enabled' => env('SEO_OG_IMAGE_ENABLED', false),
        'driver' => env('SEO_OG_IMAGE_DRIVER', 'browsershot'),
        'template' => env('SEO_OG_IMAGE_TEMPLATE', 'seo::og.default'),
        'templates' => [],
        'strip_title_suffix' => env('SEO_OG_IMAGE_STRIP_SUFFIX', true),

        'width' => 1200,
        'height' => 630,

        'disk' => env('SEO_OG_IMAGE_DISK', 'public'),
        'path' => env('SEO_OG_IMAGE_PATH', 'og-images'),

        'models' => [],

        'cache_version' => env('SEO_OG_IMAGE_CACHE_VERSION', 1),

        'gradient_from' => env(
            'SEO_OG_IMAGE_GRADIENT_FROM',
            '#243b30'
        ),

        'gradient_to' => env(
            'SEO_OG_IMAGE_GRADIENT_TO',
            '#34463a'
        ),

        'chrome_path' => env('SEO_OG_IMAGE_CHROME_PATH'),
        'node_binary' => env('SEO_OG_IMAGE_NODE_BINARY'),
        'npm_module_path' => env('SEO_OG_IMAGE_NODE_MODULES'),

        'timeout' => env('SEO_OG_IMAGE_TIMEOUT', 60),
        'no_sandbox' => env('SEO_OG_IMAGE_NO_SANDBOX', false),

        'browsershot_args' => [],

        'font_stack' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | hreflang policies
    |--------------------------------------------------------------------------
    */

    'hreflang' => [
        'normalize' => true,
        'include_self' => false,
        'x_default' => env('SEO_HREFLANG_X_DEFAULT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    */

    'verification' => [
        'google' => env('SEO_VERIFY_GOOGLE'),
        'bing' => env('SEO_VERIFY_BING'),
        'yandex' => env('SEO_VERIFY_YANDEX'),
        'baidu' => env('SEO_VERIFY_BAIDU'),
        'naver' => env('SEO_VERIFY_NAVER'),
        'seznam' => env('SEO_VERIFY_SEZNAM'),
        'pinterest' => env('SEO_VERIFY_PINTEREST'),
        'facebook' => env('SEO_VERIFY_FACEBOOK'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots and Indexing
    |--------------------------------------------------------------------------
    */

    'default_robots' => env('SEO_DEFAULT_ROBOTS', 'index,follow'),

    'robots' => [
        'emit_default' => env('SEO_EMIT_DEFAULT_ROBOTS', false),
    ],

    /*
     * Protects non-production environments from being indexed.
     *
     * Only environments listed in allowed_environments can be indexed.
     * The local environment receives noindex,nofollow.
     */

    'indexing_guard' => [
        'enabled' => env('SEO_INDEXING_GUARD', true),

        'allowed_environments' => array_values(array_filter(array_map(
            'trim',
            explode(
                ',',
                (string) env('SEO_INDEXING_GUARD_ALLOWED', 'production')
            ),
        ))) ?: ['production'],

        'send_header' => env('SEO_INDEXING_GUARD_HEADER', true),
    ],

    'default_twitter_card' => env(
        'SEO_DEFAULT_TWITTER_CARD',
        'summary_large_image'
    ),

    'twitter_site' => env('SEO_TWITTER_SITE'),
    'twitter_creator' => env('SEO_TWITTER_CREATOR'),

    'favicon' => '/favicon.ico',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        'auto_create_meta' => env('SEO_AUTO_CREATE_META', true),
        'sitemap' => env('SEO_SITEMAP_ENABLED', true),
        'schema' => env('SEO_SCHEMA_ENABLED', true),
        'multilingual' => env('SEO_MULTILINGUAL_ENABLED', false),
    ],

    'keywords' => [
        'enabled' => env('SEO_KEYWORDS_ENABLED', false),
    ],

    /*
     * Treat empty strings as unset values so the resolver can use
     * the next available SEO fallback.
     */

    'resolver' => [
        'blank_is_unset' => env('SEO_BLANK_IS_UNSET', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Canonical URLs
    |--------------------------------------------------------------------------
    */

    'canonical' => [
        'query_whitelist' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Free Audit
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'models' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Length policy
    |--------------------------------------------------------------------------
    */

    'length_policy' => [
        'default' => [
            'title_min' => 30,
            'title_max' => 60,
            'description_min' => 70,
            'description_max' => 160,
        ],

        'cjk' => [
            'title_min' => 15,
            'title_max' => 30,
            'description_min' => 35,
            'description_max' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Computed Values
    |--------------------------------------------------------------------------
    */

    'computed' => [
        'description_fields' => [],

        'description_max_length' => 160,

        'image_selection' => [
            'strategy' => env('SEO_IMAGE_SELECTION', 'first'),

            'minimum_width' => 200,
            'minimum_height' => 200,

            'ideal_width' => 1200,
            'ideal_height' => 630,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Generation
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        'disk' => env('SEO_SITEMAP_DISK', 'public'),

        'path' => 'sitemap.xml',

        'max_urls_per_sitemap' => 50000,

        /*
         * No Eloquent models are registered yet.
         * Dynamic content can be added here when the corresponding
         * models implement HasSEO or Sitemapable.
         */

        'models' => [],

        /*
         * Main public pages of Industrias Husky.
         */

        'static_urls' => [
            [
                'url' => '/',
                'priority' => 1.0,
                'changefreq' => 'weekly',
            ],
            [
                'url' => '/nosotros',
                'priority' => 0.8,
                'changefreq' => 'monthly',
            ],
            [
                'url' => '/productos',
                'priority' => 0.9,
                'changefreq' => 'weekly',
            ],
            [
                'url' => '/servicios',
                'priority' => 0.8,
                'changefreq' => 'monthly',
            ],
            [
                'url' => '/proyectos',
                'priority' => 0.8,
                'changefreq' => 'weekly',
            ],
        ],

        'images' => env('SEO_SITEMAP_IMAGES', false),

        'alternates' => env('SEO_SITEMAP_ALTERNATES', false),

        'stylesheet' => [
            'enabled' => env('SEO_SITEMAP_STYLESHEET', true),
            'url' => env('SEO_SITEMAP_STYLESHEET_URL'),
        ],

        'ping_search_engines' => env('SEO_SITEMAP_PING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | llms.txt Generation
    |--------------------------------------------------------------------------
    */

    'llms_txt' => [
        'enabled' => env('SEO_LLMS_TXT_ENABLED', true),

        'route' => env('SEO_LLMS_TXT_ROUTE', true),

        'disk' => env(
            'SEO_LLMS_TXT_DISK',
            env('SEO_SITEMAP_DISK', 'public')
        ),

        'path' => 'llms.txt',

        'title' => env(
            'SEO_LLMS_TXT_TITLE',
            'Industrias Husky'
        ),

        'description' => env(
            'SEO_LLMS_TXT_DESCRIPTION',
            'Soluciones en madera de pino impregnada y maderas tropicales para proyectos de construcción, infraestructura e industria.'
        ),

        'sources' => [],

        'max_entries_per_section' => 100,

        'alternates' => env('SEO_LLMS_TXT_ALTERNATES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Crawlers and robots.txt
    |--------------------------------------------------------------------------
    */

    'ai_crawlers' => [
        'enabled' => env('SEO_AI_CRAWLERS_ENABLED', true),

        /*
         * Keep false while using the static public/robots.txt file.
         */

        'route' => env('SEO_AI_CRAWLERS_ROUTE', false),

        'disk' => env(
            'SEO_AI_CRAWLERS_DISK',
            env('SEO_SITEMAP_DISK', 'public')
        ),

        'path' => 'robots.txt',

        'ai_txt_path' => 'ai.txt',

        /*
         * AI training crawlers are disallowed.
         * AI search, assistants and traditional search engines are allowed.
         */

        'policy' => [
            'ai_training' => 'disallow',
            'ai_search' => 'allow',
            'ai_assistant' => 'allow',
            'search_engine' => 'allow',
        ],

        'overrides' => [],

        'list' => 'blocked',

        'content_signals' => env('SEO_AI_CONTENT_SIGNALS', false),

        'general' => true,

        'include_sitemap' => true,

        'sitemap_url' => env('SEO_AI_CRAWLERS_SITEMAP_URL'),

        'include_llms_txt' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown for Bots
    |--------------------------------------------------------------------------
    */

    'markdown_for_bots' => [
        'enabled' => env('SEO_MARKDOWN_FOR_BOTS', false),

        'auto_register_middleware' => true,

        'serve_to_known_bots' => false,

        'query_param' => 'format',

        'query_value' => 'md',

        'build_from_content' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Structured Data / Schema
    |--------------------------------------------------------------------------
    */

    'schema' => [
        'in_language' => true,

        'organization' => [
            'name' => env('APP_NAME', 'Industrias Husky'),

            'url' => env('APP_URL'),

            'logo' => env('SEO_ORGANIZATION_LOGO'),
        ],

        'publisher' => [
            'name' => env('APP_NAME', 'Industrias Husky'),

            'logo' => env('SEO_PUBLISHER_LOGO'),
        ],

        'website' => [
            'name' => env('APP_NAME', 'Industrias Husky'),

            'url' => env('APP_URL'),
        ],

        'type_map' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Routes
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'enabled' => env('SEO_ROUTES_ENABLED', true),

        'prefix' => '',

        'middleware' => [
            'web',
        ],

        'api_prefix' => 'api/seo',

        'api_middleware' => [
            'api',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'prefix' => 'seo_',

        'store' => env('SEO_CACHE_STORE'),

        'resolver' => [
            'enabled' => env('SEO_RESOLVER_CACHE', false),

            'ttl' => env('SEO_RESOLVER_CACHE_TTL', 3600),
        ],
    ],

];