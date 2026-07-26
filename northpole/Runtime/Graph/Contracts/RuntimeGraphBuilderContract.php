<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph\Contracts;

use Northpole\Runtime\Graph\RuntimeGraph;

interface RuntimeGraphBuilderContract
{
    public function build(): RuntimeGraph;
}
