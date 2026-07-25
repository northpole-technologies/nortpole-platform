<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Enums;

enum LifecycleOperation: string
{
    case Install = 'install';

    case Enable = 'enable';

    case Disable = 'disable';

    case Uninstall = 'uninstall';

    case Upgrade = 'upgrade';
}
