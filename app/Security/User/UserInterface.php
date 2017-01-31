<?php

namespace App\Security\User;

/**
 * UserInterface for user security checker
 */
interface UserInterface
{
    /**
     * @return bool
     */
    public function isActive(): bool;
}
