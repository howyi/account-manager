<?php
namespace App\Enums;

use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;

class AuthenticateStateType extends AbstractEnum
{
    public const NEW   = 'NEW';
    public const ADD   = 'ADD';
    public const LOGIN = 'LOGIN';

    public const STATES = [
        self::NEW,
        self::ADD,
        self::LOGIN,
    ];

    public function getName()
    {
        return 'authenticateStateType';
    }

    public function getValues(): array
    {
        return self::STATES;
    }
}
