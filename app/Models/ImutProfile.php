<?php

namespace App\Models;

use App\Domains\Imut\Models\ImutProfile as DomainImutProfile;

/**
 * Alias model to maintain backward compatibility with legacy namespaces.
 * Extends the domain-layer ImutProfile so existing references keep working.
 */
class ImutProfile extends DomainImutProfile
{
    //
}
