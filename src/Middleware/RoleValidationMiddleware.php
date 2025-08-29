<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Middleware;

use Flarum\Group\Group;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RoleValidationMiddleware implements MiddlewareInterface
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
     * Process the request and validate registration roles
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Only validate on user registration endpoints
        if ($method === 'POST' && (strpos($path, '/api/users') !== false || strpos($path, '/api/registration-roles') !== false)) {
            $validation = $this->validateRegistrationRoles($request);
            
            if ($validation !== true) {
                return new JsonResponse([
                    'errors' => [
                        [
                            'status' => '422',
                            'code' => 'validation_error',
                            'detail' => $validation
                        ]
                    ]
                ], 422);
            }
        }

        return $handler->handle($request);
    }

    /**
     * Validate registration roles in the request
     *
     * @param ServerRequestInterface $request
     * @return bool|string
     */
    protected function validateRegistrationRoles(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        $registrationRoles = Arr::get($data, 'data.attributes.registrationRoles');
        
        // If no registration roles provided, check if they're required
        if (!$registrationRoles) {
            $forceRoleSelection = (bool) $this->settings->get('karadumann-advanced-registration-roles.force_role_selection', false);
            
            if ($forceRoleSelection) {
                return 'Registration role selection is required';
            }
            
            return true;
        }

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
            return 'No registration roles are currently available';
        }

        // Validate that all selected roles are allowed
        $invalidRoles = array_diff($registrationRoles, $allowedRoleIds);
        if (!empty($invalidRoles)) {
            return 'Some selected roles are not available for registration';
        }

        // Check if multiple roles are allowed
        $allowMultiple = (bool) $this->settings->get('karadumann-advanced-registration-roles.allow_multiple_roles', false);
        
        if (!$allowMultiple && count($registrationRoles) > 1) {
            return 'Multiple role selection is not allowed';
        }

        // Validate that all selected groups exist and are not hidden
        $groups = Group::whereIn('id', $registrationRoles)
            ->where('is_hidden', false)
            ->get();
            
        if ($groups->count() !== count($registrationRoles)) {
            return 'Some selected roles are not available';
        }

        return true;
    }
}