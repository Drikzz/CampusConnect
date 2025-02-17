# A Guide to cloning a Laravel Repository

-   **1st**, check if your current branch is the same as main branch itself.
-   **2nd**, clone the current branch to your local storage and open it in VS Code.

## WARNING

Please refer to <a href="https://youtu.be/XTDNs4TB_lE?si=sb2QOxhU0OEvEGPX">Installation of Laravel</a> for the installation of the following:

You need these three for laravel to work:

-   Composer
-   XAMPP ver. 8.1 ^
-   Laravel ver. 11

## Checking of requirements

If you already installed those, it's better to it check your sytem.

-   **3rd**, open CMD and type the following commands:

To check for composer:

```
composer -v
```

To check for Laravel:

```
laravel
```

As for **XAMPP**, check your applications if you have XAMPP installed.

## Setting up the Cloned Laravel Repo

-   **4th**, go now to the cloned repository and do the following commands below.

-   **5th**, open the terminal in your VSCODE using `` CTRL + SHIFT + `  ``.

We first have to install the following for the cloned project:

-   **Node modules**
-   **Vendor Files**
-   **Filament**
-   **Livewire**

To install the **Node modules**, run the command below:

```
npm i
```

_or_

```
npm install
```

To install the **Vendor Files**, run the command below:

```
composer install
```

To install the **Filament**, run the command below:

```
composer require filament/filament
php artisan filament:install --panels
```

To install the **Livewire**, run the command below:

```
composer require livewire/livewire
```

-   **6th**, copy the _.env.example_ to create a _.env_ using:

```
cp .env.example .env
```

-   **7th**, open the `env` and locate the following:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cc_website
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

-   You need to copy snippet above to the `.env` file.

-   You can change the `DB_DATABASE=cc_website` to your own database name:

-   **8th**, run the `php artisan key:generate` command to generate new key:

```
php artisan key:generate
```

-   **9th**, after all, that make sure to run your XAMPP application.

-   **10th**, open the terminal in the vscode, run the `php artisan migrate` command to run the migrations in the clone project.

```
php artisan migrate
```

-   **11th**, open another terminal in vscode, run `php artisan storage:link` command and go to the link provided:

```
php artisan storage:link
```

-   **12th**, open another terminal in vscode, run `php artisan db:seed` command:

```
php artisan db:seed
```

-   **13th**, open another terminal in vscode, run `php artisan serve` command:

```
php artisan serve
```

-   **14th**, lastly, open another terminal in vscode, then run `npm run dev` command:

```
npm run dev
```

-   **15th**, congratulations! You're done setting up your project!
