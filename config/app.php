<?php
/**
 * App configuration.
 * 
 * I keep config in PHP files (not YAML/JSON) because
 * they're faster, support comments, and can include logic.
 */

return [
    'name'    => env('APP_NAME', 'Pulse'),
    'env'     => env('APP_ENV', 'development'),
    'debug'   => env('APP_DEBUG', true),
    'url'     => env('APP_URL', 'http://localhost:8080'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    // the tagline on the homepage
    'tagline' => 'Full-Stack Developer. Builder of Things.',

    // who am I
    'author'  => [
        'name'  => env('AUTHOR_NAME', 'Your Name'),
        'role'  => env('AUTHOR_ROLE', 'Full-Stack Developer'),
        'bio'   => env('AUTHOR_BIO', 'I build things with PHP, JavaScript, and whatever gets the job done. This is my corner of the internet.'),
        'github'  => env('GITHUB_URL', 'https://github.com'),
        'twitter' => env('TWITTER_URL', ''),
        'linkedin'=> env('LINKEDIN_URL', ''),
    ],
];
