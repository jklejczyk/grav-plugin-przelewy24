# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-09-28

### Changed
- Updated README and CHANGELOG documentation

## [1.0.0] - 2025-09-28

### Added
- Backend-only payment processing functionality
- Admin panel configuration for all settings including secure credentials
- Sandbox/production mode support
- RESTful API endpoints for payment processing (`/przelewy24/wspieraj`, `/przelewy24/status`)
- Transaction verification and status handling
- Admin panel configuration for non-sensitive settings
- Comprehensive documentation and examples
- Support for multiple currencies (PLN, EUR, USD)
- Configurable default payment descriptions

### Removed
- Frontend templates (moved to backend-only architecture)
- Hardcoded configuration (replaced with admin panel configuration)
- Demo/test files from development

### Security
- Sensitive credentials stored securely in admin configuration
- Payment verification via Przelewy24 API
- Input validation and sanitization

### Technical
- Compatible with Grav >= 1.7.0
- PHP >= 7.4 support