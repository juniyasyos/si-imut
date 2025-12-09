#!/bin/bash

#==============================================================================
# SIIMUT Project Setup Script
# Description: Automated setup for Laravel application
# Author: Setup Script Generator
# Date: December 9, 2025
#==============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="siimut"
DB_USER="juni"
DB_PASSWORD="password"
PHP_VERSION="8.4"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

#==============================================================================
# HELPER FUNCTIONS
#==============================================================================

print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

#==============================================================================
# CHECK FUNCTIONS
#==============================================================================

check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_error "This script should not be run as root"
        exit 1
    fi
}

check_os() {
    if [[ ! -f /etc/os-release ]]; then
        print_error "Cannot determine OS. /etc/os-release not found."
        exit 1
    fi
    
    . /etc/os-release
    OS=$ID
    print_info "Detected OS: $OS"
}

check_php() {
    print_header "Checking PHP Installation"
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed"
        return 1
    fi
    
    INSTALLED_PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    print_success "PHP $INSTALLED_PHP_VERSION is installed"
    return 0
}

check_php_extension() {
    local extension=$1
    if php -m | grep -qi "^$extension$"; then
        return 0
    else
        return 1
    fi
}

check_composer() {
    print_header "Checking Composer Installation"
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed"
        return 1
    fi
    
    COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
    print_success "Composer $COMPOSER_VERSION is installed"
    return 0
}

check_mysql() {
    print_header "Checking MySQL Installation"
    
    if command -v mysql &> /dev/null; then
        print_success "MySQL client is installed"
        return 0
    else
        print_error "MySQL is not installed"
        return 1
    fi
}

#==============================================================================
# INSTALLATION FUNCTIONS
#==============================================================================

install_php_extensions() {
    print_header "Installing Required PHP Extensions"
    
    local extensions=(
        "php${PHP_VERSION}-sqlite3"
        "php${PHP_VERSION}-pdo-sqlite"
        "php${PHP_VERSION}-mysql"
        "php${PHP_VERSION}-mbstring"
        "php${PHP_VERSION}-xml"
        "php${PHP_VERSION}-curl"
        "php${PHP_VERSION}-zip"
        "php${PHP_VERSION}-gd"
        "php${PHP_VERSION}-bcmath"
        "php${PHP_VERSION}-intl"
    )
    
    local missing_extensions=()
    
    # Check which extensions are missing
    for ext in "${extensions[@]}"; do
        ext_name=$(echo "$ext" | sed "s/php${PHP_VERSION}-//")
        ext_name=$(echo "$ext_name" | sed 's/-/_/g')
        
        if ! check_php_extension "$ext_name"; then
            missing_extensions+=("$ext")
            print_warning "Missing extension: $ext"
        fi
    done
    
    if [ ${#missing_extensions[@]} -eq 0 ]; then
        print_success "All required PHP extensions are installed"
        return 0
    fi
    
    print_info "Installing missing PHP extensions..."
    
    case $OS in
        ubuntu|debian)
            sudo apt-get update
            sudo apt-get install -y "${missing_extensions[@]}"
            ;;
        fedora|rhel|centos)
            sudo dnf install -y "${missing_extensions[@]}"
            ;;
        arch)
            sudo pacman -S --noconfirm "${missing_extensions[@]}"
            ;;
        *)
            print_error "Unsupported OS for automatic installation"
            print_info "Please install these packages manually: ${missing_extensions[*]}"
            exit 1
            ;;
    esac
    
    print_success "PHP extensions installed successfully"
}

install_composer() {
    print_header "Installing Composer"
    
    cd /tmp
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    
    HASH=$(curl -sS https://composer.github.io/installer.sig)
    
    if php -r "if (hash_file('sha384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; then
        sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        rm composer-setup.php
        print_success "Composer installed successfully"
    else
        print_error "Composer installer verification failed"
        exit 1
    fi
}

install_mysql() {
    print_header "Installing MySQL Server"
    
    case $OS in
        ubuntu|debian)
            sudo apt-get update
            sudo apt-get install -y mysql-server mysql-client
            sudo systemctl start mysql
            sudo systemctl enable mysql
            ;;
        fedora|rhel|centos)
            sudo dnf install -y mysql-server mysql
            sudo systemctl start mysqld
            sudo systemctl enable mysqld
            ;;
        arch)
            sudo pacman -S --noconfirm mysql
            sudo systemctl start mysqld
            sudo systemctl enable mysqld
            ;;
        *)
            print_error "Unsupported OS for automatic MySQL installation"
            exit 1
            ;;
    esac
    
    print_success "MySQL installed successfully"
}

#==============================================================================
# SETUP FUNCTIONS
#==============================================================================

setup_git_branch() {
    print_header "Git Branch Configuration"
    
    if [ ! -d ".git" ]; then
        print_warning "Not a git repository"
        return 0
    fi
    
    # Show current branch
    CURRENT_BRANCH=$(git branch --show-current)
    print_info "Current branch: $CURRENT_BRANCH"
    
    # Show available branches
    print_info "Available branches:"
    git branch -a
    
    echo ""
    read -p "Do you want to switch branch? (y/N): " switch_branch
    
    if [[ "$switch_branch" =~ ^[Yy]$ ]]; then
        read -p "Enter branch name: " branch_name
        
        if git show-ref --verify --quiet "refs/heads/$branch_name"; then
            git checkout "$branch_name"
            print_success "Switched to branch: $branch_name"
        elif git show-ref --verify --quiet "refs/remotes/origin/$branch_name"; then
            git checkout -b "$branch_name" "origin/$branch_name"
            print_success "Checked out remote branch: $branch_name"
        else
            print_error "Branch '$branch_name' not found"
            read -p "Create new branch? (y/N): " create_branch
            if [[ "$create_branch" =~ ^[Yy]$ ]]; then
                git checkout -b "$branch_name"
                print_success "Created and switched to new branch: $branch_name"
            fi
        fi
    fi
}

setup_database() {
    print_header "Database Setup"
    
    # Get database password
    read -sp "Enter password for MySQL user '$DB_USER' (leave empty for no password): " DB_PASSWORD
    echo ""
    
    # Get MySQL root password
    read -sp "Enter MySQL root password (leave empty if no password): " MYSQL_ROOT_PASSWORD
    echo ""
    
    local mysql_cmd="mysql"
    if [ -n "$MYSQL_ROOT_PASSWORD" ]; then
        mysql_cmd="mysql -uroot -p$MYSQL_ROOT_PASSWORD"
    else
        mysql_cmd="sudo mysql"
    fi
    
    print_info "Creating database '$DB_NAME' and user '$DB_USER'..."
    
    # Create database and user
    if $mysql_cmd <<-EOSQL 2>/dev/null
		CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
		CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
		GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
		FLUSH PRIVILEGES;
	EOSQL
    then
        print_success "Database '$DB_NAME' and user '$DB_USER' created successfully"
    else
        print_error "Failed to create database. Trying alternative method..."
        sudo mysql <<-EOSQL2
			CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
			CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
			GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
			FLUSH PRIVILEGES;
		EOSQL2
        print_success "Database '$DB_NAME' and user '$DB_USER' created successfully (using sudo)"
    fi
    
    # Update .env file
    if [ -f "$PROJECT_DIR/.env" ]; then
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" "$PROJECT_DIR/.env"
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" "$PROJECT_DIR/.env"
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" "$PROJECT_DIR/.env"
        print_success ".env file updated with database credentials"
    else
        print_warning ".env file not found. Will be created during composer setup"
    fi
}

setup_environment() {
    print_header "Environment Setup"
    
    cd "$PROJECT_DIR"
    
    # Copy .env.example if .env doesn't exist
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            print_success "Created .env file from .env.example"
        else
            print_warning ".env.example not found"
        fi
    else
        print_info ".env file already exists"
    fi
}

install_dependencies() {
    print_header "Installing Composer Dependencies"
    
    cd "$PROJECT_DIR"
    
    # Clear composer cache
    composer clear-cache
    
    # Install dependencies
    print_info "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    
    print_success "Composer dependencies installed successfully"
}

run_composer_setup() {
    print_header "Running Composer Setup"
    
    cd "$PROJECT_DIR"
    
    if grep -q "\"setup\"" composer.json; then
        print_info "Running composer run setup..."
        composer run setup
        print_success "Composer setup completed successfully"
    else
        print_warning "No 'setup' script found in composer.json"
        
        # Run common Laravel setup commands
        print_info "Running standard Laravel setup commands..."
        
        # Generate application key
        php artisan key:generate --force
        print_success "Application key generated"
        
        # Run migrations
        read -p "Run database migrations? (Y/n): " run_migrations
        if [[ ! "$run_migrations" =~ ^[Nn]$ ]]; then
            php artisan migrate --force
            print_success "Database migrations completed"
        fi
        
        # Run seeders
        read -p "Run database seeders? (y/N): " run_seeders
        if [[ "$run_seeders" =~ ^[Yy]$ ]]; then
            php artisan db:seed --force
            print_success "Database seeding completed"
        fi
        
        # Clear and cache config
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        print_success "Cache cleared"
    fi
}

set_permissions() {
    print_header "Setting Directory Permissions"
    
    cd "$PROJECT_DIR"
    
    # Set permissions for storage and bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    print_success "Directory permissions set"
}

#==============================================================================
# MAIN SETUP WORKFLOW
#==============================================================================

main() {
    clear
    print_header "SIIMUT Project Setup Script"
    
    # Initial checks
    check_root
    check_os
    
    # PHP check and setup
    if ! check_php; then
        print_error "Please install PHP $PHP_VERSION first"
        print_info "Ubuntu/Debian: sudo apt-get install php${PHP_VERSION} php${PHP_VERSION}-cli"
        exit 1
    fi
    
    # Install PHP extensions
    install_php_extensions
    
    # Composer check and install
    if ! check_composer; then
        read -p "Composer is not installed. Install now? (Y/n): " install_comp
        if [[ ! "$install_comp" =~ ^[Nn]$ ]]; then
            install_composer
        else
            print_error "Composer is required to continue"
            exit 1
        fi
    fi
    
    # MySQL check and install
    if ! check_mysql; then
        read -p "MySQL is not installed. Install now? (Y/n): " install_sql
        if [[ ! "$install_sql" =~ ^[Nn]$ ]]; then
            install_mysql
        else
            print_error "MySQL is required to continue"
            exit 1
        fi
    fi
    
    # Setup git branch
    setup_git_branch
    
    # Setup environment
    setup_environment
    
    # Setup database
    setup_database
    
    # Install dependencies
    install_dependencies
    
    # Run composer setup
    run_composer_setup
    
    # Set permissions
    set_permissions
    
    # Final message
    print_header "Setup Complete!"
    print_success "SIIMUT project setup completed successfully"
    print_info "You can now start the development server with:"
    print_info "  php artisan serve"
    echo ""
}

# Run main function
main "$@"