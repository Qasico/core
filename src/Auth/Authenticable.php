<?php

namespace Core\Auth;

interface Authenticable
{
    /**
     * Attemp to do login.
     *
     * @param string $username
     * @param string $password
     * @param bool   $remember
     * @return mixed
     */
    public function loginAttemp(string $username, string $password, bool $remember);

    /**
     * Get user by remember_token.
     *
     * @param string $id
     * @param string $token
     * @return mixed
     */
    public function getByToken(string $id, string $token);

    /**
     * Get user by identifier.
     *
     * @param string $id ,
     * @return mixed
     */
    public function getByIdentifier(string $id);

    /**
     * Update remember_token user.
     *
     * @param string $id
     * @param string $token
     * @return mixed
     */
    public function updateToken(string $id, string $token);
}