<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Policies;

use Flarum\Group\Group;
use Flarum\User\User;
use Flarum\User\Access\AbstractPolicy;

class GroupPolicy extends AbstractPolicy
{
    /**
     * Determine if the user can assign registration roles
     *
     * @param User $actor
     * @param string $ability
     * @return bool|null
     */
    public function can(User $actor, $ability)
    {
        if ($ability === 'assign-registration-roles') {
            return $actor->hasPermission('user.edit') || $actor->isAdmin();
        }

        return null;
    }

    /**
     * Determine if the user can view registration role settings
     *
     * @param User $actor
     * @param Group $group
     * @return bool|null
     */
    public function viewRegistrationSettings(User $actor, Group $group)
    {
        return $actor->hasPermission('group.edit') || $actor->isAdmin();
    }

    /**
     * Determine if the user can edit registration role settings
     *
     * @param User $actor
     * @param Group $group
     * @return bool|null
     */
    public function editRegistrationSettings(User $actor, Group $group)
    {
        return $actor->hasPermission('group.edit') || $actor->isAdmin();
    }

    /**
     * Determine if the user can manage registration roles
     *
     * @param User $actor
     * @return bool|null
     */
    public function manageRegistrationRoles(User $actor)
    {
        return $actor->hasPermission('group.edit') || $actor->isAdmin();
    }
}