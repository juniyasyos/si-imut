#!/bin/bash

# IAM Session Configuration Diagnostic Script
# Usage: ./diagnose-iam-session.sh

echo -e "\n╔════════════════════════════════════════════════════════════════╗"
echo -e "║  IAM CLIENT SESSION CONFIGURATION DIAGNOSTIC                   ║"
echo -e "╚════════════════════════════════════════════════════════════════╝\n"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_PATH="/home/juni/projects/siimut"
cd "$PROJECT_PATH" || exit 1

echo -e "${YELLOW}1. Checking Environment Variables...${NC}\n"

# Check SESSION_DRIVER
if grep -q "SESSION_DRIVER=database" .env; then
    echo -e "${GREEN}✅${NC} SESSION_DRIVER = database"
else
    echo -e "${RED}❌${NC} SESSION_DRIVER should be 'database'"
fi

# Check IAM_VERIFY_EACH_REQUEST
if grep -q "IAM_VERIFY_EACH_REQUEST=true" .env; then
    echo -e "${GREEN}✅${NC} IAM_VERIFY_EACH_REQUEST = true"
else
    echo -e "${YELLOW}⚠️${NC}  IAM_VERIFY_EACH_REQUEST not found in .env"
fi

# Check IAM_VERIFY_REMOTE_EACH_REQUEST
if grep -q "IAM_VERIFY_REMOTE_EACH_REQUEST=true" .env; then
    echo -e "${GREEN}✅${NC} IAM_VERIFY_REMOTE_EACH_REQUEST = true"
else
    echo -e "${YELLOW}⚠️${NC}  IAM_VERIFY_REMOTE_EACH_REQUEST not found in .env"
fi

# Check IAM_ATTACH_VERIFY_MIDDLEWARE
if grep -q "IAM_ATTACH_VERIFY_MIDDLEWARE=true" .env; then
    echo -e "${GREEN}✅${NC} IAM_ATTACH_VERIFY_MIDDLEWARE = true"
else
    echo -e "${YELLOW}⚠️${NC}  IAM_ATTACH_VERIFY_MIDDLEWARE not found in .env"
fi

echo -e "\n${YELLOW}2. Checking PHP Configuration...${NC}\n"

# Check if sessions table exists
if php -r "
include 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
\$exists = \Illuminate\Support\Facades\Schema::hasTable(config('session.table', 'sessions'));
exit(\$exists ? 0 : 1);
" 2>/dev/null; then
    echo -e "${GREEN}✅${NC} Sessions table exists"
else
    echo -e "${YELLOW}⚠️${NC}  Sessions table might not exist"
fi

echo -e "\n${YELLOW}3. Checking IAM Plugin Installation...${NC}\n"

# Check if IAM client plugin is installed
if [ -d "vendor/juniyasyos/laravel-iam-client" ]; then
    echo -e "${GREEN}✅${NC} IAM Client Plugin installed"
    
    # Check VerifyIamToken middleware exists
    if [ -f "vendor/juniyasyos/laravel-iam-client/src/Http/Middleware/VerifyIamToken.php" ]; then
        echo -e "${GREEN}✅${NC} VerifyIamToken middleware found"
    else
        echo -e "${RED}❌${NC} VerifyIamToken middleware not found"
    fi
else
    echo -e "${RED}❌${NC} IAM Client Plugin not installed"
fi

echo -e "\n${YELLOW}4. Configuration Values (from PHP)...${NC}\n"

php -r "
include 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo 'Session Driver: ' . config('session.driver') . \"\\n\";
echo 'Session Lifetime: ' . config('session.lifetime') . \" mins\\n\";
echo 'Session Table: ' . config('session.table', 'sessions') . \"\\n\";
echo 'Verify Each Request: ' . (config('iam.verify_each_request') ? 'true' : 'false') . \"\\n\";
echo 'Verify Remote Each Request: ' . (config('iam.verify_remote_each_request') ? 'true' : 'false') . \"\\n\";
echo 'Attach Verify Middleware: ' . (config('iam.attach_verify_middleware') ? 'true' : 'false') . \"\\n\";
echo 'IAM Base URL: ' . config('iam.base_url') . \"\\n\";
echo 'IAM Verify Endpoint: ' . config('iam.verify_endpoint') . \"\\n\";
" 2>/dev/null || echo "Could not load PHP configuration"

echo -e "\n${YELLOW}5. Database Connection Check...${NC}\n"

# Test database connection for sessions
php -r "
include 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
try {
    \$connection = \Illuminate\Support\Facades\DB::connection();
    \$result = \$connection->select('SHOW TABLES LIKE \"sessions\"');
    if (!empty(\$result)) {
        echo 'Sessions table connection: OK' . \"\\n\";
    } else {
        echo 'Warning: Sessions table not found' . \"\\n\";
    }
} catch (\Exception \$e) {
    echo 'Database connection error: ' . \$e->getMessage() . \"\\n\";
}
" 2>/dev/null || echo "Could not check database"

echo -e "\n╔════════════════════════════════════════════════════════════════╗"
echo -e "║  RECOMMENDATION                                                 ║"
echo -e "╚════════════════════════════════════════════════════════════════╝\n"

if grep -q "SESSION_DRIVER=database" .env && \
   grep -q "IAM_VERIFY_EACH_REQUEST=true" .env && \
   grep -q "IAM_ATTACH_VERIFY_MIDDLEWARE=true" .env; then
    echo -e "${GREEN}✅ All configurations are correct!${NC}\n"
    echo "Session expiration handling is properly configured."
    echo "Users will be automatically logged out when their token expires."
else
    echo -e "${YELLOW}⚠️ Some configurations might need adjustment.${NC}\n"
    echo "Please verify the .env file settings match the requirements."
fi

echo -e "\n"
