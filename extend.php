<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Flarum\Extend;
use Karadumann\AdvancedRegistrationRoles\Api\Controllers\RoleController;
use Karadumann\AdvancedRegistrationRoles\Api\Serializers\GroupSerializer;
use Karadumann\AdvancedRegistrationRoles\Listeners\UserRegistrationListener;
use Karadumann\AdvancedRegistrationRoles\Providers\SettingsProvider;

return [
    // Frontend Assets
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    // Locales
    new Extend\Locales(__DIR__.'/locale'),

    // API Routes
    (new Extend\Routes('api'))
        ->get('/registration-roles', 'registration-roles.index', RoleController::class)
        ->post('/registration-roles/assign', 'registration-roles.assign', RoleController::class),

    // Event Listeners
    (new Extend\Event())
        ->listen(\Flarum\User\Event\Saving::class, UserRegistrationListener::class),

    // API Serializers
    (new Extend\ApiSerializer(\Flarum\Api\Serializer\GroupSerializer::class))
        ->attributes(GroupSerializer::class),

    // Settings
    (new Extend\ServiceProvider())
        ->register(SettingsProvider::class),

    // Permissions
    (new Extend\Policy())
        ->modelPolicy(\Flarum\Group\Group::class, \Karadumann\AdvancedRegistrationRoles\Policies\GroupPolicy::class),

    // Console Commands (for future use)
    (new Extend\Console())
        ->command(\Karadumann\AdvancedRegistrationRoles\Console\SyncRolesCommand::class),

    // Middleware (for additional security)
    (new Extend\Middleware('api'))
        ->add(\Karadumann\AdvancedRegistrationRoles\Middleware\RoleValidationMiddleware::class),
];