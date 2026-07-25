<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestNotificationsTest extends TestCase
{
    public function test_it_returns_notifications(): void
    {
        $manifest = $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'class' => 'Modules\\CRM\\Notifications\\CustomerCreatedNotification',
                        'channels' => [
                            'mail',
                            'database',
                        ],
                        'queue' => 'notifications',
                    ],
                    [
                        'name' => 'customer-updated',
                        'class' => 'Modules\\CRM\\Notifications\\CustomerUpdatedNotification',
                        'channels' => [
                            'database',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                [
                    'name' => 'customer-created',
                    'class' => 'Modules\\CRM\\Notifications\\CustomerCreatedNotification',
                    'channels' => [
                        'database',
                        'mail',
                    ],
                    'queue' => 'notifications',
                ],
                [
                    'name' => 'customer-updated',
                    'class' => 'Modules\\CRM\\Notifications\\CustomerUpdatedNotification',
                    'channels' => [
                        'database',
                    ],
                ],
            ],
            $manifest->notifications()
        );
    }

    public function test_it_trims_and_normalises_notifications(): void
    {
        $manifest = $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => ' customer-created ',
                        'class' => ' Modules\\CRM\\Notifications\\CustomerCreatedNotification ',
                        'channels' => [
                            ' mail ',
                            ' database ',
                            'mail',
                        ],
                        'queue' => ' notifications ',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                [
                    'name' => 'customer-created',
                    'class' => 'Modules\\CRM\\Notifications\\CustomerCreatedNotification',
                    'channels' => [
                        'database',
                        'mail',
                    ],
                    'queue' => 'notifications',
                ],
            ],
            $manifest->notifications()
        );
    }

    public function test_it_returns_empty_notifications_when_not_defined(): void
    {
        self::assertSame(
            [],
            $this->manifest()->notifications()
        );
    }

    public function test_it_rejects_a_non_object_notifications_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [notifications] must be an object'
        );

        $this->manifest([
            'notifications' => 'mail',
        ]);
    }

    public function test_it_rejects_a_non_list_sends_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [notifications.sends] must be a list'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    'customer-created' => [
                        'name' => 'customer-created',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_object_notification(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] must be an object'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    'customer-created',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_missing_notification_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] must define a non-empty name'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'class' => 'NotificationClass',
                        'channels' => ['mail'],
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_missing_notification_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] must define a non-empty class'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'channels' => ['mail'],
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_list_channels_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] field [channels] must be a list'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'class' => 'NotificationClass',
                        'channels' => 'mail',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_empty_notification_channels(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] must define at least one channel'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'class' => 'NotificationClass',
                        'channels' => [],
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_channel_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] field [channels] must contain non-empty strings'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'class' => 'NotificationClass',
                        'channels' => ['mail', '   '],
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_queue_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest notification [0] field [queue] must be a non-empty string'
        );

        $this->manifest([
            'notifications' => [
                'sends' => [
                    [
                        'name' => 'customer-created',
                        'class' => 'NotificationClass',
                        'channels' => ['mail'],
                        'queue' => '   ',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function manifest(
        array $overrides = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'CRM',
                    'slug' => 'crm',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides
            ),
            path: '/modules/crm',
            manifestPath: '/modules/crm/module.json',
        );
    }
}