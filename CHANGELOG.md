# v1.0.2
## 28/09/2025
1. [](#improved)
    * Updated CHANGELOG format to match Grav repository standards

# v1.0.1
## 28/09/2025
1. [](#improved)
    * Updated README and CHANGELOG documentation

# v1.0.0
## 28/09/2025
1. [](#new)
    * Backend-only payment processing functionality
    * Admin panel configuration for all settings including secure credentials
    * Sandbox/production mode support
    * RESTful API endpoints for payment processing (`/przelewy24/wspieraj`, `/przelewy24/status`)
    * Transaction verification and status handling
    * Admin panel configuration for non-sensitive settings
    * Comprehensive documentation and examples
    * Support for multiple currencies (PLN, EUR, USD)
    * Configurable default payment descriptions
2. [](#improved)
    * Compatible with Grav >= 1.7.0
    * PHP >= 7.4 support
    * Sensitive credentials stored securely in admin configuration
    * Payment verification via Przelewy24 API
    * Input validation and sanitization
3. [](#bugfix)
    * Removed frontend templates (moved to backend-only architecture)
    * Removed hardcoded configuration (replaced with admin panel configuration)
    * Removed demo/test files from development