# Przelewy24 Plugin for Grav

Backend-only plugin for integrating Przelewy24 payment processing with Grav CMS.

## Features

- Backend-only payment processing (no frontend templates)
- Secure credential handling via admin panel configuration
- Sandbox/production mode support
- Configurable payment settings
- RESTful API endpoints for payment processing
- Transaction verification and status handling

## Installation

### Via GPM (Grav Package Manager)

**Note: This plugin is not yet available via GPM.** Once published to the official Grav repository, you will be able to install it with:

```bash
bin/gpm install przelewy24
```

### Manual Installation

1. Download the plugin
2. Extract to `user/plugins/przelewy24/`
3. Enable the plugin in Admin Panel or set `enabled: true` in plugin configuration

## Configuration

The plugin supports two methods for configuring credentials with automatic fallback:

### Method 1: Environment Variables (Recommended for Production)

Create a `.env` file in your Grav root directory:

```env
# Przelewy24 API Credentials
P24_MERCHANT_ID=your_merchant_id_here
P24_CRC_KEY=your_crc_key_here
P24_API_KEY=your_api_key_here
```

### Method 2: Admin Panel (Development Fallback)

Configure via Admin Panel or edit `user/config/plugins/przelewy24.yaml`:

```yaml
enabled: true
merchant_id: 'your_merchant_id'           # Fallback if env var not set
crc_key: 'your_crc_key'                   # Fallback if env var not set
api_key: 'your_api_key'                   # Fallback if env var not set
sandbox: true                              # Set to false for production
currency: 'PLN'                           # Default currency (PLN, EUR, USD, GBP, CHF, etc.)
country: 'PL'                             # Default country (PL, DE, GB, FR, IT, ES, etc.)
language: 'pl'                            # Payment interface language (pl, en, de, fr, it, es, etc.)
payment_description: 'Payment via Przelewy24'  # Default payment description
```

### Credential Priority System

The plugin uses the following priority order for credentials:

1. **Environment Variables** (Highest Priority)
   - `P24_MERCHANT_ID`
   - `P24_CRC_KEY`
   - `P24_API_KEY`

2. **Admin Panel Configuration** (Fallback)
   - Used only if environment variables are not set
   - Stored in `user/config/plugins/przelewy24.yaml`

This allows you to:
- Use admin panel for quick development setup
- Override with environment variables for production security
- Mix both approaches as needed

### Supported Options

**Currencies**: PLN, EUR, USD, GBP, CHF, CZK, NOK, SEK, DKK, CAD, AUD, JPY, HUF, RON, BGN, HRK, UAH

**Countries**: Poland, Germany, United Kingdom, France, Italy, Spain, Netherlands, Belgium, Austria, Switzerland, Czech Republic, Slovakia, Hungary, Romania, Bulgaria, Croatia, Slovenia, Lithuania, Latvia, Estonia, Finland, Sweden, Denmark, Norway, Ireland, Portugal, Greece, Luxembourg, Malta, Cyprus, United States, Canada, Australia, New Zealand, Japan, Ukraine

**Languages**: Polish, English, German, French, Italian, Spanish, Dutch, Czech, Slovak, Hungarian, Romanian, Bulgarian, Croatian, Slovenian, Lithuanian, Latvian, Estonian, Finnish, Swedish, Danish, Norwegian, Portuguese, Greek, Ukrainian, Russian

## Usage

### API Endpoints

The plugin provides two main endpoints:

#### 1. Payment Initiation
- **URL**: `/przelewy24/wspieraj`
- **Method**: POST
- **Required Parameters**:
  - `amount` (required) - Payment amount in currency units (e.g., 50.00 for 50 PLN)
  - `email` (required) - Customer email address
- **Optional Parameters**:
  - `description` (optional) - Payment description (defaults to plugin setting)
  - `currency` (optional) - Currency code (defaults to plugin setting)
  - `country` (optional) - Country code (defaults to plugin setting)
  - `language` (optional) - Interface language (defaults to plugin setting)

#### 2. Payment Status/Notification
- **URL**: `/przelewy24/status`
- **Method**: POST
- **Purpose**: Handles Przelewy24 notifications and verifies transactions (internal use)
- **Note**: This endpoint is called automatically by Przelewy24 servers after payment completion. No user action required.

### Frontend Integration

Since this is a backend-only plugin, you need to create your own payment forms. Example HTML form:

```html
<form method="POST" action="przelewy24/wspieraj">
    <input type="hidden" name="currency" value="PLN">
    <input type="hidden" name="description" value="Donation">

    <div>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
    </div>

    <div>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" min="1" step="0.01" required>
    </div>

    <button type="submit">Pay</button>
</form>
```

#### Overriding Default Settings Per Form

You can override the default plugin settings for specific forms using hidden input fields:

**Available Override Fields:**
- `currency` - Override default currency for this payment
- `description` - Override payment description for this payment
- `country` - Override default country for this payment
- `language` - Override payment interface language for this payment

**Examples:**

```html
<!-- Form with custom currency and language -->
<form method="POST" action="przelewy24/wspieraj">
    <input type="hidden" name="currency" value="EUR">
    <input type="hidden" name="description" value="Premium Membership">
    <input type="hidden" name="country" value="DE">
    <input type="hidden" name="language" value="en">
    <!-- form fields... -->
</form>

<!-- Form using default settings (no hidden fields needed) -->
<form method="POST" action="przelewy24/wspieraj">
    <!-- Will use all defaults from admin panel -->
    <!-- form fields... -->
</form>

<!-- Form with only custom description and language -->
<form method="POST" action="przelewy24/wspieraj">
    <input type="hidden" name="description" value="Product Purchase">
    <input type="hidden" name="language" value="fr">
    <!-- Will use default currency/country, custom description/language -->
    <!-- form fields... -->
</form>

<!-- Multilingual site example -->
<form method="POST" action="przelewy24/wspieraj">
    <input type="hidden" name="language" value="de">
    <input type="hidden" name="country" value="AT">
    <input type="hidden" name="currency" value="EUR">
    <input type="hidden" name="description" value="Österreich Zahlung">
    <!-- Austrian customer with German interface -->
    <!-- form fields... -->
</form>
```

### Important Note About AJAX

**This plugin is designed for traditional form submissions only.** The plugin uses `header('Location: ...')` redirects to Przelewy24, which cannot be handled by AJAX/fetch() requests.

**Use traditional HTML forms** as shown in the examples above. AJAX integration would require significant plugin modifications to return JSON responses instead of redirects.

## Security Considerations

**IMPORTANT SECURITY NOTICE**

This plugin stores API credentials in Grav's configuration files, which are stored as plain text YAML files. While convenient for development, this approach has security implications for production environments.

### Development vs Production

**For Development:**
- Admin panel configuration is convenient and fast
- Acceptable for local development and testing
- Easy to set up and modify

**For Production:**
- **Never commit real credentials** to version control
- Consider using environment variables instead
- Use secure configuration management systems
- Restrict file system access to configuration files
- Regular security audits of configuration files

### Security Best Practices

1. **Configuration Security**:
   - Credentials are stored in `user/config/plugins/przelewy24.yaml`
   - Ensure this file has restricted permissions (600 or 644)
   - Never commit this file with real credentials to git
   - Use `.gitignore` to exclude configuration files with credentials

2. **HTTPS**: Always use HTTPS in production environments

3. **Validation**: The plugin validates all incoming data

4. **Verification**: All payments are verified via Przelewy24 API

5. **Access Control**: Restrict admin panel access to authorized users only

### Built-in Environment Variable Support

The plugin automatically checks for environment variables first, then falls back to admin configuration:

```php
// Plugin automatically uses this priority:
// 1. $_ENV['P24_MERCHANT_ID'] ?? $config['merchant_id']
// 2. $_ENV['P24_CRC_KEY'] ?? $config['crc_key']
// 3. $_ENV['P24_API_KEY'] ?? $config['api_key']
```

## Development

### File Structure

```
przelewy24/
├── blueprints.yaml           # Admin panel configuration
├── przelewy24.yaml          # Default plugin configuration
├── przelewy24.php           # Main plugin file
└── classes/
    └── Przelewy24/
        ├── TransactionRegister.php  # Payment registration
        └── TransactionVerify.php    # Payment verification
```

### Testing

1. Enable sandbox mode in plugin configuration
2. Use Przelewy24 test credentials
3. Test payment flow with test amounts

## Requirements

- Grav >= 1.7.0
- PHP >= 7.4
- Przelewy24 merchant account

## License

MIT License - see LICENSE file for details

## Support

- Report issues: [GitHub Issues](https://github.com/jklejczyk/grav-plugin-przelewy24/issues)

## Acknowledgments

Special thanks to **Adam Szczepiński**, IT Application Support Specialist at Przelewy24, for his detailed and patient responses to integration questions and invaluable assistance during the development of this plugin.

## Changelog

### v1.0.0
- Initial backend-only release
- Environment variable configuration for credentials
- Sandbox/production mode support
- RESTful API endpoints
- Transaction verification