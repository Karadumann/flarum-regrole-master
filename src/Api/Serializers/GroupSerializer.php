<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Api\Serializers;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;

class GroupSerializer extends AbstractSerializer
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get additional attributes for groups in registration context
     *
     * @param Group $group
     * @return array
     */
    public function getDefaultAttributes($group)
    {
        $attributes = [];
        
        // Add registration-specific attributes
        $attributes['registrationDescription'] = $this->getRegistrationDescription($group);
        $attributes['registrationIcon'] = $this->getRegistrationIcon($group);
        $attributes['registrationColor'] = $this->getRegistrationColor($group);
        $attributes['registrationOrder'] = $this->getRegistrationOrder($group);
        $attributes['isRegistrationRole'] = $this->isRegistrationRole($group);
        
        return $attributes;
    }

    /**
     * Get registration description for the group
     *
     * @param Group $group
     * @return string|null
     */
    protected function getRegistrationDescription(Group $group)
    {
        return $this->settings->get("karadumann-advanced-registration-roles.group_{$group->id}_description");
    }

    /**
     * Get registration icon for the group
     *
     * @param Group $group
     * @return string|null
     */
    protected function getRegistrationIcon(Group $group)
    {
        return $this->settings->get("karadumann-advanced-registration-roles.group_{$group->id}_icon", 'fas fa-users');
    }

    /**
     * Get registration color for the group
     *
     * @param Group $group
     * @return string
     */
    protected function getRegistrationColor(Group $group)
    {
        return $this->settings->get("karadumann-advanced-registration-roles.group_{$group->id}_color", $group->color ?: '#3498db');
    }

    /**
     * Get registration order for the group
     *
     * @param Group $group
     * @return int
     */
    protected function getRegistrationOrder(Group $group)
    {
        return (int) $this->settings->get("karadumann-advanced-registration-roles.group_{$group->id}_order", 0);
    }

    /**
     * Check if group is available for registration
     *
     * @param Group $group
     * @return bool
     */
    protected function isRegistrationRole(Group $group)
    {
        $allowedRoleIds = json_decode($this->settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true);
        
        return in_array($group->id, $allowedRoleIds);
    }
}