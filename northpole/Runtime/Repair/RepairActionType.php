<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

enum RepairActionType: string
{
    case Instruction = 'instruction';
    case PowerShell = 'powershell';
}
