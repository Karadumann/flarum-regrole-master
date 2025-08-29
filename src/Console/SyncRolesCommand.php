<?php

/*
 * This file is part of karadumann/flarum-advanced-registration-roles.
 *
 * Copyright (c) 2024 Karadumann.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Karadumann\AdvancedRegistrationRoles\Console;

use Flarum\Console\AbstractCommand;
use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SyncRolesCommand extends AbstractCommand
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
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('karadumann:sync-registration-roles')
            ->setDescription('Sync registration roles for existing users')
            ->addArgument('user-id', InputArgument::OPTIONAL, 'Specific user ID to sync')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be changed without making changes')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force sync even if user already has registration roles');
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $userId = $this->input->getArgument('user-id');
        $dryRun = $this->input->getOption('dry-run');
        $force = $this->input->getOption('force');

        $this->info('Starting registration roles sync...');

        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $users = User::all();
        }

        $allowedRoleIds = json_decode($this->settings->get('karadumann-advanced-registration-roles.allowed_role_ids', '[]'), true);
        
        if (empty($allowedRoleIds)) {
            $this->error('No registration roles configured.');
            return 1;
        }

        $processed = 0;
        $updated = 0;

        foreach ($users as $user) {
            $processed++;
            
            // Skip if user already has registration roles and not forcing
            if (!$force && $user->registration_roles) {
                continue;
            }

            // Get user's current groups that are in allowed registration roles
            $userRegistrationGroups = $user->groups()->whereIn('id', $allowedRoleIds)->pluck('id')->toArray();
            
            if (!empty($userRegistrationGroups)) {
                if ($dryRun) {
                    $this->line("Would update user {$user->id} ({$user->username}) with registration roles: " . implode(', ', $userRegistrationGroups));
                } else {
                    $user->registration_roles = json_encode($userRegistrationGroups);
                    $user->save();
                    $this->line("Updated user {$user->id} ({$user->username}) with registration roles: " . implode(', ', $userRegistrationGroups));
                }
                $updated++;
            }
        }

        if ($dryRun) {
            $this->info("Dry run completed. Would update {$updated} out of {$processed} users.");
        } else {
            $this->info("Sync completed. Updated {$updated} out of {$processed} users.");
        }

        return 0;
    }
}