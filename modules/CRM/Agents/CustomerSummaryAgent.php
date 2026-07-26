<?php

declare(strict_types=1);

namespace Modules\CRM\Agents;

use InvalidArgumentException;
use Modules\CRM\Models\Customer;
use Northpole\Runtime\Agents\Contracts\ModuleAgentContract;
use Northpole\Runtime\Agents\Contracts\ModuleAgentHandlerContract;

final class CustomerSummaryAgent implements ModuleAgentHandlerContract
{
    /**
     * @return array<string, mixed>
     */
    public function handle(
        ModuleAgentContract $agent,
    ): array {
        $input = $agent->input();

        $customerId = trim(
            (string) ($input['customer_id'] ?? ''),
        );

        if ($customerId === '') {
            throw new InvalidArgumentException(
                'A customer ID is required.'
            );
        }

        $customer = Customer::query()->findOrFail(
            $customerId,
        );

        return [
            'id' => (string) $customer->getKey(),
            'organisation_id' => (string)
                $customer->organisation_id,
            'name' => $customer->name,
            'company_name' => $customer->company_name,
            'type' => $customer->type,
            'status' => $customer->status,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'mobile' => $customer->mobile,
            'city' => $customer->city,
            'country' => $customer->country,
            'summary' => $this->summary($customer),
        ];
    }

    private function summary(
        Customer $customer,
    ): string {
        $summary = sprintf(
            '%s is a %s %s customer.',
            $customer->name,
            $customer->status,
            $customer->type,
        );

        $companyName = trim(
            (string) $customer->company_name,
        );

        if ($companyName !== '') {
            $summary .= sprintf(
                ' They are associated with %s.',
                $companyName,
            );
        }

        $contact = $this->firstAvailableContact(
            $customer,
        );

        if ($contact !== null) {
            $summary .= sprintf(
                ' Their primary contact is %s.',
                $contact,
            );
        }

        return $summary;
    }

    private function firstAvailableContact(
        Customer $customer,
    ): ?string {
        foreach (
            [
                $customer->email,
                $customer->mobile,
                $customer->phone,
            ] as $contact
        ) {
            $contact = trim(
                (string) $contact,
            );

            if ($contact !== '') {
                return $contact;
            }
        }

        return null;
    }
}
