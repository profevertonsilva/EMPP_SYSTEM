<?php

declare(strict_types=1);

namespace Empp\Model;

enum AlertLevel: string
{
    /** Information about how the result was obtained (shown in blue) */
    case NOTE = 'note';
    /** The result needs checking before it is used (shown in orange, counted in the title) */
    case CHECK = 'check';
}
