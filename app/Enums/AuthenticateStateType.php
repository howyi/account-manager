<?php
namespace App\Enums;

use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;

class AuthenticateServiceType extends AbstractEnum
{
    public const PROVIDERS = [
        'GOOGLE'   => GoogleProvider::class,
        'GITHUB'   => GithubProvider::class,
        'SLACK'    => \Mpociot\Socialite\Slack\Provider::class,
        // 'CHATWORK' => ChatWorkProvider::class,
    ];

    public const SCOPES = [
        'SLACK' => [
            'identity.basic',
            'identity.email',
            'identity.team',
            'identity.avatar'
        ]
    ];

    public function getName()
    {
        return 'authenticateServiceType';
    }

    public function getValues(): array
    {
        return array_keys(self::PROVIDERS);
    }
}
