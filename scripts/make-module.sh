#!/bin/bash
NAME=$1
if [ -z "$NAME" ]; then
    echo "Usage: scripts/make-module.sh <ModuleName>"
    exit 1
fi

SLUG=$(echo $NAME | sed 's/\([A-Z]\)/-\1/g' | sed 's/^-//' | tr '[:upper:]' '[:lower:]')
BASE="app/Modules/$NAME"

mkdir -p "$BASE"/{Contracts/DTOs,Database/Migrations,Exceptions,Filament/{Resources,Pages,Widgets,Schemas},Http/Controllers,Livewire,Models,Observers,Policies,Repositories,Services,Resources/Views,Events/Listeners}

# Service Provider
cat > "$BASE/${NAME}ServiceProvider.php" << EOF
<?php

namespace App\Modules\\${NAME};

use Illuminate\Support\ServiceProvider;

class ${NAME}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            Contracts\\${NAME}Interface::class,
            Services\\${NAME}Service::class
        );
        \$this->loadViewsFrom(__DIR__.'/Resources/Views', '${SLUG}');
    }

    public function boot(): void
    {
        //
    }
}
EOF

# Interface
cat > "$BASE/Contracts/${NAME}Interface.php" << EOF
<?php

namespace App\Modules\\${NAME}\Contracts;

interface ${NAME}Interface
{
    //
}
EOF

# Service implementation
cat > "$BASE/Services/${NAME}Service.php" << EOF
<?php

namespace App\Modules\\${NAME}\Services;

use App\Modules\\${NAME}\Contracts\\${NAME}Interface;

class ${NAME}Service implements ${NAME}Interface
{
    //
}
EOF

chmod +x "$BASE"

echo "✅ Module $NAME created at $BASE"
