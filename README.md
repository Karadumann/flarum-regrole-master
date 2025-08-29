# Advanced Registration Roles for Flarum

A comprehensive Flarum extension that provides an advanced role selection system during user registration.

## Features

### 🎯 **Advanced Role Selection**
- **Multiple Role Selection**: Allow users to select multiple roles during registration
- **Force Role Selection**: Make role selection mandatory for registration
- **Custom Role Descriptions**: Add detailed descriptions for each role
- **Role Icons & Colors**: Customize visual appearance with FontAwesome icons and colors
- **Role Ordering**: Control the display order of roles in the registration form

### 🎨 **Modern UI/UX**
- **Beautiful Role Cards**: Modern card-based design for role selection
- **Responsive Design**: Works perfectly on desktop and mobile devices
- **Dark Mode Support**: Automatic dark mode compatibility
- **Smooth Animations**: Engaging user interactions with CSS animations
- **Real-time Preview**: Live preview of role customizations in admin panel

### ⚙️ **Admin Features**
- **Comprehensive Settings Panel**: Easy-to-use admin interface
- **Role Management**: Enable/disable roles for registration
- **Bulk Operations**: Sync roles for existing users via console command
- **Permission System**: Granular permissions for role management
- **Settings Validation**: Built-in validation for all configuration options

### 🔧 **Developer Features**
- **Modern Architecture**: Built with latest Flarum standards
- **API Endpoints**: RESTful API for role management
- **Event System**: Comprehensive event listeners for customization
- **Middleware Support**: Request validation and processing
- **Console Commands**: CLI tools for maintenance and synchronization

## 📦 Kurulum

### Gereksinimler
- Flarum 1.0 veya üzeri
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri

### Composer ile Kurulum (Önerilen)
```bash
composer require karadumann/flarum-advanced-registration-roles
```

### Manuel Kurulum
1. Extension dosyalarını `extensions/karadumann-advanced-registration-roles/` klasörüne kopyalayın
2. Composer bağımlılıklarını yükleyin:
   ```bash
   composer install --no-dev
   ```
3. Migration'ları çalıştırın:
   ```bash
   php flarum migrate
   ```
4. Extension'ı etkinleştirin:
   ```bash
   php flarum cache:clear
   ```

### Packagist
Bu extension [Packagist](https://packagist.org/packages/karadumann/flarum-advanced-registration-roles) üzerinden de yüklenebilir.

## Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/karadumann/flarum-advanced-registration-roles.git
   cd flarum-advanced-registration-roles
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Build assets**
   ```bash
   npm run build
   ```

4. **Development mode**
   ```bash
   npm run dev
   ```

## Configuration

### Admin Panel Settings

1. Navigate to **Admin Panel → Extensions → Advanced Registration Roles**
2. Configure general settings:
   - Registration title and description
   - Multiple role selection options
   - Force role selection requirement
   - Display options for descriptions and icons

3. Manage available roles:
   - Enable/disable roles for registration
   - Customize role descriptions, icons, and colors
   - Set display order

### Console Commands

**Sync registration roles for existing users:**
```bash
php flarum karadumann:sync-roles [options]
```

Options:
- `--user=ID` - Sync specific user
- `--dry-run` - Preview changes without applying
- `--force` - Force update existing roles

## API Endpoints

### Get Available Registration Roles
```http
GET /api/registration-roles
```

### Assign Roles During Registration
```http
POST /api/users
Content-Type: application/json

{
  "data": {
    "attributes": {
      "username": "newuser",
      "email": "user@example.com",
      "password": "password",
      "registrationRoles": [1, 2, 3]
    }
  }
}
```

## Permissions

- `user.assignRegistrationRoles` - Can assign registration roles
- `group.manageRegistrationRoles` - Can manage registration role settings
- `admin.viewRegistrationSettings` - Can view registration settings

## Database Schema

The extension adds the following columns to the `users` table:
- `registration_roles` (TEXT, nullable) - JSON array of selected role IDs
- `registration_date` (TIMESTAMP, nullable) - Registration timestamp

## Localization

Supported languages:
- **English** (`en`)
- **Turkish** (`tr`)

To add more languages, create a new file in `locale/[language_code].yml`.

## Technical Architecture

### Backend Structure
```
src/
├── Api/
│   ├── Controllers/     # API controllers
│   └── Serializers/     # Data serializers
├── Console/             # CLI commands
├── Listeners/           # Event listeners
├── Middleware/          # Request middleware
├── Policies/            # Authorization policies
└── Providers/           # Service providers
```

### Frontend Structure
```
js/src/
├── admin/               # Admin panel components
│   ├── components/      # React-like components
│   └── index.js         # Admin entry point
└── forum/               # Forum components
    ├── components/      # Registration components
    └── index.js         # Forum entry point
```

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Run code formatting: `npm run format`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/karadumann/flarum-advanced-registration-roles/issues) page
2. Create a new issue with detailed information
3. Join the discussion in [Flarum Community](https://discuss.flarum.org/)

## Changelog

### Version 1.0.0
- Initial release
- Advanced role selection system
- Modern UI/UX design
- Comprehensive admin panel
- Multi-language support
- Console commands
- API endpoints
