#!/bin/bash

# =============================================================================
# SI-IMUT Authentication Mode Switcher
# =============================================================================
# Script untuk memudahkan switching antara SSO dan Custom Login mode
# 
# Usage:
#   ./switch-auth-mode.sh dev     # Switch to development mode (custom login)
#   ./switch-auth-mode.sh prod    # Switch to production mode (SSO)
#   ./switch-auth-mode.sh status  # Check current mode
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Function to check if .env file exists
check_env_file() {
    if [ ! -f .env ]; then
        print_error ".env file not found!"
        print_info "Creating .env from .env.example..."
        cp .env.example .env
        print_success ".env file created"
    fi
}

# Function to update .env value
update_env() {
    local key=$1
    local value=$2
    
    if grep -q "^${key}=" .env; then
        # Key exists, update it
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            sed -i '' "s/^${key}=.*/${key}=${value}/" .env
        else
            # Linux
            sed -i "s/^${key}=.*/${key}=${value}/" .env
        fi
    else
        # Key doesn't exist, append it
        echo "${key}=${value}" >> .env
    fi
}

# Function to get current .env value
get_env_value() {
    local key=$1
    grep "^${key}=" .env 2>/dev/null | cut -d '=' -f2 || echo ""
}

# Function to switch to development mode
switch_to_dev() {
    print_info "Switching to DEVELOPMENT mode (Custom Login)..."
    
    update_env "USE_SSO" "false"
    update_env "IAM_ENABLED" "false"
    update_env "APP_ENV" "local"
    update_env "APP_DEBUG" "true"
    
    print_success "Environment variables updated"
    
    # Clear cache
    print_info "Clearing cache..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    print_success "Cache cleared"
    
    echo ""
    print_success "Successfully switched to DEVELOPMENT mode!"
    echo ""
    print_info "Authentication: Custom Login (NIP + Password)"
    print_info "Login URL: http://localhost:8000/login"
    print_info "Default credentials:"
    echo "  NIP: 0000.00000"
    echo "  Password: adminpassword"
    echo ""
    print_warning "Make sure users have passwords set for custom login!"
}

# Function to switch to production mode
switch_to_prod() {
    print_info "Switching to PRODUCTION mode (SSO)..."
    
    # Check if IAM configuration exists
    local iam_host=$(get_env_value "IAM_HOST")
    local iam_secret=$(get_env_value "IAM_JWT_SECRET")
    
    if [ -z "$iam_host" ] || [ "$iam_host" = "http://127.0.0.1:8000" ]; then
        print_warning "IAM_HOST is not configured for production!"
        print_info "Please update IAM_HOST in .env file"
    fi
    
    if [ -z "$iam_secret" ] || [ "$iam_secret" = "SIIMUT_ZK8FnRKJo5GlfBoN0izTVg0fR63r9UsgY86IaHeN" ]; then
        print_warning "IAM_JWT_SECRET is using default value!"
        print_info "Please update IAM_JWT_SECRET in .env file"
    fi
    
    update_env "USE_SSO" "true"
    update_env "IAM_ENABLED" "true"
    update_env "APP_ENV" "production"
    update_env "APP_DEBUG" "false"
    
    print_success "Environment variables updated"
    
    # Clear and cache config
    print_info "Clearing and caching configuration..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan config:cache
    php artisan route:cache
    print_success "Configuration cached"
    
    echo ""
    print_success "Successfully switched to PRODUCTION mode!"
    echo ""
    print_info "Authentication: SSO (Single Sign-On)"
    print_info "Login URL: http://localhost:8000/sso/login (redirects to IAM)"
    print_info "IAM Server: $iam_host"
    echo ""
    print_warning "Make sure IAM server is accessible and properly configured!"
}

# Function to show current status
show_status() {
    check_env_file
    
    local use_sso=$(get_env_value "USE_SSO")
    local iam_enabled=$(get_env_value "IAM_ENABLED")
    local app_env=$(get_env_value "APP_ENV")
    local app_debug=$(get_env_value "APP_DEBUG")
    local iam_host=$(get_env_value "IAM_HOST")
    
    echo ""
    echo "═══════════════════════════════════════════════════════"
    echo "  SI-IMUT Authentication Status"
    echo "═══════════════════════════════════════════════════════"
    echo ""
    echo "Environment Variables:"
    echo "  USE_SSO       : $use_sso"
    echo "  IAM_ENABLED   : $iam_enabled"
    echo "  APP_ENV       : $app_env"
    echo "  APP_DEBUG     : $app_debug"
    echo "  IAM_HOST      : $iam_host"
    echo ""
    
    if [ "$use_sso" = "true" ] || [ "$iam_enabled" = "true" ]; then
        print_info "Current Mode: ${GREEN}PRODUCTION (SSO)${NC}"
        echo "  • Authentication: Single Sign-On"
        echo "  • Login URL: /sso/login (redirects to IAM)"
        echo "  • User provisioning: Automatic from IAM"
    else
        print_info "Current Mode: ${BLUE}DEVELOPMENT (Custom Login)${NC}"
        echo "  • Authentication: Custom Filament Login"
        echo "  • Login URL: /login"
        echo "  • Login with: NIP + Password"
    fi
    echo ""
    echo "═══════════════════════════════════════════════════════"
    echo ""
}

# Main script logic
main() {
    echo ""
    echo "═══════════════════════════════════════════════════════"
    echo "  SI-IMUT Authentication Mode Switcher"
    echo "═══════════════════════════════════════════════════════"
    echo ""
    
    check_env_file
    
    case "${1:-status}" in
        dev|development)
            switch_to_dev
            ;;
        prod|production)
            switch_to_prod
            ;;
        status|check)
            show_status
            ;;
        help|--help|-h)
            echo "Usage: $0 {dev|prod|status}"
            echo ""
            echo "Commands:"
            echo "  dev, development    Switch to development mode (custom login)"
            echo "  prod, production    Switch to production mode (SSO)"
            echo "  status, check       Show current authentication mode"
            echo "  help                Show this help message"
            echo ""
            ;;
        *)
            print_error "Invalid argument: $1"
            echo ""
            echo "Usage: $0 {dev|prod|status}"
            echo "Run '$0 help' for more information"
            echo ""
            exit 1
            ;;
    esac
}

# Run main function
main "$@"
