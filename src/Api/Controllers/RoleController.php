<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Api\Controllers;

use Flarum\Api\Controller\AbstractListController;
use Flarum\Api\Controller\AbstractShowController;
use Flarum\Group\Group;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Flarum\Api\Serializer\GroupSerializer;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;

class RoleController extends AbstractListController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = GroupSerializer::class;

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
     * Get available registration roles
     *
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return Collection
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Get allowed role IDs from settings
        $allowedRoleIds = json_decode($this->settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true);
        
        if (empty($allowedRoleIds)) {
            return new Collection();
        }

        // Get groups that are allowed for registration
        $groups = Group::whereIn('id', $allowedRoleIds)
            ->where('is_hidden', false)
            ->orderBy('name_singular')
            ->get();

        return $groups;
    }

    /**
     * Assign roles to user during registration
     *
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return mixed
     */
    public function assign(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $data = Arr::get($request->getParsedBody(), 'data', []);
        
        $userId = Arr::get($data, 'attributes.user_id');
        $roleIds = Arr::get($data, 'attributes.role_ids', []);
        
        // Validate user exists and is the same as actor (during registration)
        $user = User::findOrFail($userId);
        
        if ($actor->id !== $user->id && !$actor->can('assign-registration-roles')) {
            throw new PermissionDeniedException();
        }

        // Get allowed role IDs from settings
        $allowedRoleIds = json_decode($this->settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true);
        
        // Filter role IDs to only allowed ones
        $validRoleIds = array_intersect($roleIds, $allowedRoleIds);
        
        if (empty($validRoleIds)) {
            throw new \InvalidArgumentException('No valid roles selected');
        }

        // Check if multiple roles are allowed
        $allowMultiple = (bool) $this->settings->get('karadumann-advanced-registration-roles.allow_multiple_roles', false);
        
        if (!$allowMultiple && count($validRoleIds) > 1) {
            $validRoleIds = [reset($validRoleIds)];
        }

        // Assign roles to user
        $groups = Group::whereIn('id', $validRoleIds)->get();
        $user->groups()->sync($groups->pluck('id')->toArray());
        
        // Save user registration role selection
        $user->registration_roles = json_encode($validRoleIds);
        $user->save();

        return $user;
    }
}