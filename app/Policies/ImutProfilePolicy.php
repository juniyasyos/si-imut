<?php

namespace App\Policies;

use App\Domains\Imut\Policies\ImutProfilePolicy as DomainImutProfilePolicy;

/**
 * Adapter class that proxies to the domain-level policy so legacy namespaces keep working.
 */
class ImutProfilePolicy extends DomainImutProfilePolicy
{
    //
}

