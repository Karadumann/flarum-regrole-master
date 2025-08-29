<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Providers;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Container\Container;

class SettingsProvider extends AbstractServiceProvider
{
    /**
     * Register services in the container.
     */
    public function register()
    {
        $this->container->extend('flarum.frontend.forum.content', function ($content, Container $container) {
            $settings = $container->make(SettingsRepositoryInterface::class);
            
            return array_merge($content, [
                'karadumann-advanced-registration-roles' => [
                    'allowedRoleIds' => json_decode($settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true),
                    'allowMultipleRoles' => (bool) $settings->get('karadumann-advanced-registration-roles.allow_multiple_roles', false),
                    'forceRoleSelection' => (bool) $settings->get('karadumann-advanced-registration-roles.force_role_selection', false),
                    'showRoleDescriptions' => (bool) $settings->get('karadumann-advanced-registration-roles.show_role_descriptions', true),
                    'showRoleIcons' => (bool) $settings->get('karadumann-advanced-registration-roles.show_role_icons', true),
                    'registrationTitle' => $settings->get('karadumann-advanced-registration-roles.registration_title', ''),
                    'registrationDescription' => $settings->get('karadumann-advanced-registration-roles.registration_description', ''),
                ]
            ]);
        });

        $this->container->extend('flarum.frontend.admin.content', function ($content, Container $container) {
            $settings = $container->make(SettingsRepositoryInterface::class);
            
            return array_merge($content, [
                'karadumann-advanced-registration-roles' => [
                    'allowedRoleIds' => json_decode($settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true),
                    'allowMultipleRoles' => (bool) $settings->get('karadumann-advanced-registration-roles.allow_multiple_roles', false),
                    'forceRoleSelection' => (bool) $settings->get('karadumann-advanced-registration-roles.force_role_selection', false),
                    'showRoleDescriptions' => (bool) $settings->get('karadumann-advanced-registration-roles.show_role_descriptions', true),
                    'showRoleIcons' => (bool) $settings->get('karadumann-advanced-registration-roles.show_role_icons', true),
                    'registrationTitle' => $settings->get('karadumann-advanced-registration-roles.registration_title', ''),
                    'registrationDescription' => $settings->get('karadumann-advanced-registration-roles.registration_description', ''),
                ]
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Additional bootstrapping if needed
    }
}