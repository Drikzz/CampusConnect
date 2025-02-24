# 🚀 Campus Connect - Laravel Setup Guide

## 📋 Pre-Installation Checklist

> Before you begin, ensure you have the following installed:

-   🔧 Composer
-   🌐 XAMPP (version 8.1 or higher)
-   ⚡ Laravel (version 11)

## 🎯 Quick Start Guide

### 1️⃣ Initial Setup

1. Verify your branch matches the main branch
2. Clone the repository and open in VS Code
3. Run these checks in CMD:

    ```bash
    # Check Composer version
    composer -v

    # Check Laravel installation
    laravel
    ```

### 2️⃣ Core Installation Steps

#### Install Required Dependencies

```bash
# Install Node modules
npm install

# Install PHP dependencies
composer install
composer update

# Install Vue & Inertia
npm i vue@latest
composer require inertiajs/inertia-laravel
npm install @inertiajs/vue3
```

#### Setup Frontend Tools

```bash
# Install Vue & development tools
npm install @vitejs/plugin-vue
npm i -g npx
npx shadcn-vue@latest init

# Install UI dependencies
npm install @vueuse/core
npm install -D @iconify/vue @iconify-json/radix-icons

# Install route handling
composer require tightenco/ziggy
```

### 3️⃣ Environment Configuration

```bash
# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Required .env settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cc_website
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

### 4️⃣ Final Setup Steps

Run these commands in order:

```bash
# 1. Start XAMPP

# 2. Run database migrations
php artisan migrate

# 3. Create storage link
php artisan storage:link

# 4. Seed the database
php artisan db:seed

# 5. Start the development server
php artisan serve

# 6. Start Vite development server
npm run dev
```

## 🔧 Troubleshooting

### ShadCN Vue Installation Issues

If `npx shadcn-vue@latest` fails:

```bash
# Install TypeScript dependencies
npm install --save-dev typescript @types/node

# If components.json becomes corrupt:
npx clear-npx-cache
npm cache clean --force
```

## ✨ Success!

Once all steps are completed, your Campus Connect development environment should be up and running!

---

📝 **Note**: Keep this guide handy for future reference. For detailed Laravel installation instructions, check out [this video guide](https://youtu.be/XTDNs4TB_lE?si=sb2QOxhU0OEvEGPX).
