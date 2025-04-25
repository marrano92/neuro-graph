#!/bin/bash
# [ai-generated-code]
# Script to set up Brevo email integration

# ANSI color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================${NC}"
echo -e "${BLUE}    Brevo Email Setup Script        ${NC}"
echo -e "${BLUE}====================================${NC}"
echo

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    exit 1
fi

# Ask for setup method
echo -e "${YELLOW}How would you like to set up Brevo?${NC}"
echo "1. SMTP Integration (simpler, recommended for most users)"
echo "2. API Integration (more features, requires additional setup)"
read -p "Choose an option (1 or 2): " setup_option

case $setup_option in
    1)
        # SMTP Integration
        echo -e "\n${GREEN}Setting up Brevo SMTP integration...${NC}"
        
        # Ask for SMTP credentials
        read -p "Enter your Brevo email (username): " brevo_email
        read -p "Enter your Brevo SMTP key: " brevo_key
        read -p "Enter your FROM email address (default: no-reply@neuro-graph.com): " from_email
        from_email=${from_email:-no-reply@neuro-graph.com}
        
        # Backup .env file
        cp .env .env.backup
        echo -e "${GREEN}Created backup of .env at .env.backup${NC}"
        
        # Update .env file
        sed -i '' 's/MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env
        sed -i '' 's/MAIL_HOST=.*/MAIL_HOST=smtp-relay.brevo.com/' .env
        sed -i '' 's/MAIL_PORT=.*/MAIL_PORT=587/' .env
        sed -i '' 's/MAIL_USERNAME=.*/MAIL_USERNAME='"$brevo_email"'/' .env
        sed -i '' 's/MAIL_PASSWORD=.*/MAIL_PASSWORD='"$brevo_key"'/' .env
        sed -i '' 's/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/' .env
        sed -i '' 's/MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS="'"$from_email"'"/' .env
        
        echo -e "${GREEN}Updated .env file with Brevo SMTP settings${NC}"
        ;;
        
    2)
        # API Integration
        echo -e "\n${GREEN}Setting up Brevo API integration...${NC}"
        
        # Ask for API credentials
        read -p "Enter your Brevo API key: " brevo_api_key
        read -p "Enter your FROM email address (default: no-reply@neuro-graph.com): " from_email
        from_email=${from_email:-no-reply@neuro-graph.com}
        
        # Backup .env file
        cp .env .env.backup
        echo -e "${GREEN}Created backup of .env at .env.backup${NC}"
        
        # Update .env file
        sed -i '' 's/MAIL_MAILER=.*/MAIL_MAILER=brevo/' .env
        sed -i '' 's/MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS="'"$from_email"'"/' .env
        
        # Add BREVO_API_KEY if it doesn't exist
        if grep -q "BREVO_API_KEY" .env; then
            sed -i '' 's/BREVO_API_KEY=.*/BREVO_API_KEY='"$brevo_api_key"'/' .env
        else
            echo "" >> .env
            echo "BREVO_API_KEY=$brevo_api_key" >> .env
        fi
        
        echo -e "${GREEN}Updated .env file with Brevo API settings${NC}"
        
        # Install required package
        echo -e "\n${GREEN}Installing required package...${NC}"
        ./vendor/bin/sail composer require symfony/brevo-mailer
        ;;
        
    *)
        echo -e "${RED}Invalid option selected. Exiting.${NC}"
        exit 1
        ;;
esac

# Restart containers and clear cache
echo -e "\n${GREEN}Restarting containers and clearing cache...${NC}"
./vendor/bin/sail down
./vendor/bin/sail up -d
./vendor/bin/sail artisan config:clear

# Test email sending
echo -e "\n${YELLOW}Would you like to test the email configuration now?${NC} (y/n)"
read -p "" test_option

if [[ $test_option == "y" || $test_option == "Y" ]]; then
    echo -e "\n${GREEN}Sending test email...${NC}"
    ./vendor/bin/sail artisan email:test
fi

echo -e "\n${GREEN}Brevo setup completed!${NC}"
echo -e "${BLUE}For more information, check the documentation at:${NC}"
echo -e "${BLUE}docs/email/brevo-integration.md${NC}" 