#!/bin/bash

# Laravel Deployer - Installation Script
# This script helps you quickly set up Laravel Deployer

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Laravel Deployer - Installation     ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo

# Check PHP version
echo -e "${YELLOW}Checking PHP version...${NC}"
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓ PHP ${PHP_VERSION} found${NC}"

# Check if composer is installed
echo -e "${YELLOW}Checking for Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer not found. Installing...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo -e "${GREEN}✓ Composer installed${NC}"
else
    echo -e "${GREEN}✓ Composer found${NC}"
fi

# Install dependencies
echo -e "${YELLOW}Installing dependencies...${NC}"
composer install
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Create .env file if not exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env file...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
    echo -e "${YELLOW}Please edit .env with your configuration${NC}"
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi

# Run tests
echo -e "${YELLOW}Running tests...${NC}"
php laravel-deployer test
echo -e "${GREEN}✓ Tests passed${NC}"

# Build the application
echo
read -p "Do you want to build the standalone PHAR file? [y/N]: " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Building application...${NC}"
    php laravel-deployer app:build
    echo -e "${GREEN}✓ Application built at builds/laravel-deployer${NC}"
    
    # Install globally
    read -p "Do you want to install it globally? [y/N]: " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        sudo mv builds/laravel-deployer /usr/local/bin/laravel-deployer
        sudo chmod +x /usr/local/bin/laravel-deployer
        echo -e "${GREEN}✓ Installed to /usr/local/bin/laravel-deployer${NC}"
    fi
fi

echo
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Installation Complete!                ║${NC}"
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo
echo -e "Usage:"
echo -e "  ${BLUE}php laravel-deployer deploy${NC}              # Run deploy command"
echo -e "  ${BLUE}php laravel-deployer deploy --help${NC}       # Show help"
echo -e "  ${BLUE}php laravel-deployer test${NC}                # Run tests"
echo
echo -e "Documentation:"
echo -e "  ${BLUE}README.md${NC}      - Full documentation"
echo -e "  ${BLUE}QUICKSTART.md${NC}  - Quick start guide"
echo -e "  ${BLUE}EXAMPLES.md${NC}    - Usage examples"
echo -e "  ${BLUE}MIGRATION.md${NC}   - Migration guide from bash script"
echo
echo -e "${GREEN}Happy deploying! 🚀${NC}"
