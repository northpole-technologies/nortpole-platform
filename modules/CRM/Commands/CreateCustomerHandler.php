<?php

declare(strict_types=1);

namespace Modules\CRM\Commands;

use InvalidArgumentException;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;
use Northpole\Runtime\Events\ModuleEventBus;

final class CreateCustomerHandler implements ModuleCommandHandlerContract
{
    public function __construct(
        private readonly ModuleEventBus $eventBus,
    ) {}

    public function handle(
        ModuleCommandContract $command,
    ): Customer {
        $payload = $command->payload();

        $name = trim(
            (string) ($payload['name'] ?? ''),
        );

        if ($name === '') {
            throw new InvalidArgumentException(
                'A customer name is required.'
            );
        }

        $customer = Customer::query()->create([
            'type' => $this->optionalString(
                $payload,
                'type',
                'individual',
            ),
            'status' => $this->optionalString(
                $payload,
                'status',
                'active',
            ),
            'name' => $name,
            'company_name' => $this->nullableString(
                $payload,
                'company_name',
            ),
            'email' => $this->nullableString(
                $payload,
                'email',
            ),
            'phone' => $this->nullableString(
                $payload,
                'phone',
            ),
            'mobile' => $this->nullableString(
                $payload,
                'mobile',
            ),
            'website' => $this->nullableString(
                $payload,
                'website',
            ),
            'address_line_1' => $this->nullableString(
                $payload,
                'address_line_1',
            ),
            'address_line_2' => $this->nullableString(
                $payload,
                'address_line_2',
            ),
            'city' => $this->nullableString(
                $payload,
                'city',
            ),
            'county' => $this->nullableString(
                $payload,
                'county',
            ),
            'postal_code' => $this->nullableString(
                $payload,
                'postal_code',
            ),
            'country' => $this->nullableString(
                $payload,
                'country',
            ),
            'notes' => $this->nullableString(
                $payload,
                'notes',
            ),
        ]);

        $this->eventBus->publish(
            eventName: 'crm.customer.created',
            sourceModule: 'crm',
            payload: [
                'customerId' => (string) $customer->getKey(),
                'organisationId' => (string) $customer->organisation_id,
                'name' => $customer->name,
                'type' => $customer->type,
                'status' => $customer->status,
            ],
            metadata: [
                'commandName' => $command->name(),
                'commandSourceModule' => $command->sourceModule(),
                'commandIssuedAt' => $command
                    ->issuedAt()
                    ->format(DATE_ATOM),
                'commandMetadata' => $command->metadata(),
            ],
        );

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function optionalString(
        array $payload,
        string $key,
        string $default,
    ): string {
        if (! array_key_exists($key, $payload)) {
            return $default;
        }

        $value = trim(
            (string) $payload[$key],
        );

        return $value === ''
            ? $default
            : $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableString(
        array $payload,
        string $key,
    ): ?string {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = trim(
            (string) $payload[$key],
        );

        return $value === ''
            ? null
            : $value;
    }
}
