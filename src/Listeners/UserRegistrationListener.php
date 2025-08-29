<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Listeners;

use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;

class UserRegistrationListener
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
     * Handle user saving event during registration
     *
     * @param Saving $event
     */
    public function handle(Saving $event)
    {
        $user = $event->user;
        $data = $event->data;
        $actor = $event->actor;

        // Only handle during user registration (new users)
        if ($user->exists) {
            return;
        }

        // Check if registration roles are provided
        $registrationRoles = Arr::get($data, 'attributes.registrationRoles');
        
        if (!$registrationRoles) {
            // Check if role selection is forced
            $forceRoleSelection = (bool) $this->settings->get('karadumann-advanced-registration-roles.force_role_selection', false);
            
            if ($forceRoleSelection) {
                throw new \InvalidArgumentException('Role selection is required during registration');
            }
            
            return;
        }

        // Validate and process registration roles
        $this->processRegistrationRoles($user, $registrationRoles);
    }

    /**
     * Process and validate registration roles
     *
     * @param \Flarum\User\User $user
     * @param array|string $registrationRoles
     */
    protected function processRegistrationRoles($user, $registrationRoles)
    {
        // Ensure roles is an array
        if (is_string($registrationRoles)) {
            $registrationRoles = json_decode($registrationRoles, true) ?: [];
        }
        
        if (!is_array($registrationRoles)) {
            $registrationRoles = [$registrationRoles];
        }

        // Get allowed role IDs from settings
        $allowedRoleIds = json_decode($this->settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true);
        
        if (empty($allowedRoleIds)) {
            return;
        }

        // Filter to only allowed roles
        $validRoleIds = array_intersect($registrationRoles, $allowedRoleIds);
        
        if (empty($validRoleIds)) {
            throw new \InvalidArgumentException('Invalid registration roles selected');
        }

        // Check if multiple roles are allowed
        $allowMultiple = (bool) $this->settings->get('karadumann-advanced-registration-roles.allow_multiple_roles', false);
        
        if (!$allowMultiple && count($validRoleIds) > 1) {
            $validRoleIds = [reset($validRoleIds)];
        }

        // Validate that all selected groups exist and are not hidden
        $groups = Group::whereIn('id', $validRoleIds)
            ->where('is_hidden', false)
            ->get();
            
        if ($groups->count() !== count($validRoleIds)) {
            throw new \InvalidArgumentException('Some selected roles are not available');
        }

        // Store registration roles for later assignment (after user is saved)
        $user->afterSave(function ($user) use ($validRoleIds) {
            $user->groups()->sync($validRoleIds);
            
            // Store the registration role selection in user meta
            $user->registration_roles = json_encode($validRoleIds);
            $user->registration_date = now();
            $user->saveQuietly(); // Use saveQuietly to avoid triggering events again
        });
    }
}