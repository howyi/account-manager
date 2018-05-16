<?php

namespace App\Providers;

use App\Models\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as AuthUserProviderInterface;

/**
 * ユーザプロバイダ
 */
class AuthUserProvider implements AuthUserProviderInterface
{
    /**
     * 識別子からユーザを返す
     *
     * @param mixed $userId
     * @return Authenticatable|null
     */
    public function retrieveById($userId): ?Authenticatable
    {
        return app('em')->getRepository(User::class)->find($userId);
    }

    /**
     * 識別子と `remember me` トークンからユーザを返す
     *
     * @param mixed  $userId
     * @param string $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($id, $token): ?Authenticatable
    {
        var_dump(['name' => 'retrieveByToken', 'args' => compact('id', 'token')]);
    }

    /**
     * `remember me` トークンを更新する
     *
     * @param Authenticatable $user
     * @param string          $token
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        var_dump(['name' => 'updateRememberToken', 'args' => var_dump(compact('user', 'token'))]);
    }

    /**
     * 認証情報からユーザを返す
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        var_dump(['name' => 'retrieveByCredentials', 'args' => compact('credentials')]);
    }

    /**
     * 認証情報が妥当か検証する
     *
     * @param  Authenticatable $user
     * @param  array           $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): ?bool
    {
        var_dump(['name' => 'validateCredentials', 'args' => compact('user', 'credentials')]);
    }
}
