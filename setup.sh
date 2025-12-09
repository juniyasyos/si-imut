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

# MinIO Configuration
MINIO_ROOT_USER="admin"
MINIO_ROOT_PASSWORD="password"
MINIO_BUCKET="siimut"
MINIO_ENDPOINT="http://127.0.0.1:9000"
MINIO_CONSOLE_PORT="9001"

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

setup_minio() {
    print_header "MinIO S3 Storage Setup"
    
    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        print_warning "Docker is not installed. MinIO requires Docker."
        read -p "Install Docker? (y/N): " install_docker
        if [[ "$install_docker" =~ ^[Yy]$ ]]; then
            case $OS in
                ubuntu|debian)
                    curl -fsSL https://get.docker.com -o get-docker.sh
                    sudo sh get-docker.sh
                    sudo usermod -aG docker $USER
                    rm get-docker.sh
                    print_success "Docker installed. You may need to log out and back in."
                    ;;
                *)
                    print_error "Please install Docker manually for your OS"
                    return 1
                    ;;
            esac
        else
            print_warning "Skipping MinIO setup"
            return 0
        fi
    fi
    
    # Check if MinIO container already exists
    if docker ps -a --format '{{.Names}}' | grep -q '^minio$'; then
        print_info "MinIO container already exists"
        if ! docker ps --format '{{.Names}}' | grep -q '^minio$'; then
            print_info "Starting existing MinIO container..."
            docker start minio
        fi
        print_success "MinIO is running"
    else
        print_info "Creating MinIO container..."
        
        # Create MinIO data directory
        mkdir -p "$PROJECT_DIR/storage/minio/data"
        
        # Run MinIO container
        docker run -d \
            --name minio \
            -p 9000:9000 \
            -p $MINIO_CONSOLE_PORT:9001 \
            -e MINIO_ROOT_USER=$MINIO_ROOT_USER \
            -e MINIO_ROOT_PASSWORD=$MINIO_ROOT_PASSWORD \
            -v "$PROJECT_DIR/storage/minio/data:/data" \
            --restart unless-stopped \
            quay.io/minio/minio server /data --console-address ":9001"
        
        print_success "MinIO container created and started"
        
        # Wait for MinIO to be ready
        print_info "Waiting for MinIO to be ready..."
        sleep 5
    fi
    
    # Install MinIO client (mc)
    if ! command -v mc &> /dev/null; then
        print_info "Installing MinIO client (mc)..."
        curl -o /tmp/mc https://dl.min.io/client/mc/release/linux-amd64/mc
        chmod +x /tmp/mc
        sudo mv /tmp/mc /usr/local/bin/mc
        print_success "MinIO client installed"
    fi
    
    # Configure MinIO client
    mc alias set siimut-minio $MINIO_ENDPOINT $MINIO_ROOT_USER $MINIO_ROOT_PASSWORD 2>/dev/null || true
    
    # Create bucket if not exists
    if ! mc ls siimut-minio/$MINIO_BUCKET &>/dev/null; then
        print_info "Creating bucket '$MINIO_BUCKET'..."
        mc mb siimut-minio/$MINIO_BUCKET
        mc anonymous set download siimut-minio/$MINIO_BUCKET
        print_success "Bucket '$MINIO_BUCKET' created"
    else
        print_success "Bucket '$MINIO_BUCKET' already exists"
    fi
    
    # Update .env file with MinIO configuration
    if [ -f "$PROJECT_DIR/.env" ]; then
        print_info "Updating .env with MinIO configuration..."
        
        # Update or add MinIO settings
        sed -i "s|FILESYSTEM_DISK=.*|FILESYSTEM_DISK=s3|" "$PROJECT_DIR/.env"
        
        if grep -q "^AWS_ACCESS_KEY_ID=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_ACCESS_KEY_ID=.*|AWS_ACCESS_KEY_ID=$MINIO_ROOT_USER|" "$PROJECT_DIR/.env"
        else
            echo "AWS_ACCESS_KEY_ID=$MINIO_ROOT_USER" >> "$PROJECT_DIR/.env"
        fi
        
        if grep -q "^AWS_SECRET_ACCESS_KEY=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_SECRET_ACCESS_KEY=.*|AWS_SECRET_ACCESS_KEY=$MINIO_ROOT_PASSWORD|" "$PROJECT_DIR/.env"
        else
            echo "AWS_SECRET_ACCESS_KEY=$MINIO_ROOT_PASSWORD" >> "$PROJECT_DIR/.env"
        fi
        
        if grep -q "^AWS_DEFAULT_REGION=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_DEFAULT_REGION=.*|AWS_DEFAULT_REGION=us-east-1|" "$PROJECT_DIR/.env"
        else
            echo "AWS_DEFAULT_REGION=us-east-1" >> "$PROJECT_DIR/.env"
        fi
        
        if grep -q "^AWS_BUCKET=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_BUCKET=.*|AWS_BUCKET=$MINIO_BUCKET|" "$PROJECT_DIR/.env"
        else
            echo "AWS_BUCKET=$MINIO_BUCKET" >> "$PROJECT_DIR/.env"
        fi
        
        if grep -q "^AWS_ENDPOINT=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_ENDPOINT=.*|AWS_ENDPOINT=$MINIO_ENDPOINT|" "$PROJECT_DIR/.env"
        else
            echo "AWS_ENDPOINT=$MINIO_ENDPOINT" >> "$PROJECT_DIR/.env"
        fi
        
        if grep -q "^AWS_USE_PATH_STYLE_ENDPOINT=" "$PROJECT_DIR/.env"; then
            sed -i "s|AWS_USE_PATH_STYLE_ENDPOINT=.*|AWS_USE_PATH_STYLE_ENDPOINT=true|" "$PROJECT_DIR/.env"
        else
            echo "AWS_USE_PATH_STYLE_ENDPOINT=true" >> "$PROJECT_DIR/.env"
        fi
        
        print_success ".env updated with MinIO S3 configuration"
    fi
    
    print_success "MinIO setup complete!"
    print_info "MinIO Console: http://localhost:$MINIO_CONSOLE_PORT"
    print_info "MinIO API: $MINIO_ENDPOINT"
    print_info "Username: $MINIO_ROOT_USER"
    print_info "Password: $MINIO_ROOT_PASSWORD"
    print_info "Bucket: $MINIO_BUCKET"
}

install_dependencies() {
    print_header "Installing Composer Dependencies"
    
    cd "$PROJECT_DIR"
    
    # Clear composer cache
    composer clear-cache
    
    # Install AWS S3 Flysystem adapter if not present
    if ! grep -q "league/flysystem-aws-s3-v3" composer.json; then
        print_info "Installing AWS S3 Flysystem adapter..."
        composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
        print_success "AWS S3 adapter installed"
    fi
    
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
    
    # Setup MinIO S3 storage
    read -p "Setup MinIO S3 storage? (Y/n): " setup_s3
    if [[ ! "$setup_s3" =~ ^[Nn]$ ]]; then
        setup_minio
    fi
    
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