# A Guide to cloning a Laravel Repository

- __1st__, check if your current branch is the same as main branch itself.
- __2nd__, clone the current branch to your local storage and open it in VS Code.

## WARNING
Please refer to <a href="https://youtu.be/XTDNs4TB_lE?si=sb2QOxhU0OEvEGPX">Installation of Laravel</a> for the installation of the following:

You need these three for laravel to work:
- Composer
- XAMPP
- Laravel

## Checking of requirements
If you already installed those, it's better to it check your sytem.

- __3rd__, open CMD and type the following commands:

To check for composer:
```
composer -v
```

To check for Laravel:
```
laravel
```

As for __XAMPP__, check your applications if you have XAMPP installed.

## Setting up the Cloned Laravel Repo
- __4th__, go now to the cloned repository and do the following commands below.

- __5th__, open the terminal in your VSCODE using `CTRL + SHIFT + `.

We first have to install the following in the cloned project:

- Node modules
- Vendor Files

To install the node modules, run the command below:
```
npm i
```

*or* 

```
npm install
```

To install the vendor files, run the command below:
```
composer install
```

- __6th__, copy the *.env.example* to create a *.env* using:
```
cp .env.example .env
```

- __7th__, open the `env` and locate the following:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cc_website
DB_USERNAME=root
DB_PASSWORD=
```
- You need to copy snippet above to the `.env` file.

- You can change the `DB_DATABASE=cc_website` to your own database name:

- __8th__, run the `php artisan key:generate` command to generate new key:
```
php artisan key:generate
```

- __9th__, after all, that make sure to run your XAMPP application.

- __10th__, run the `php artisan migrate` command to run the migrations in the clone project.
```
php artisan migrate
```

- __11th__, run `php artisan serve` command and go to the link provided:

```
php artisan serve
```

- __12th__, run `npm run dev` command:

```
npm run dev
```

- __13th__, congratulations! You're done setting up your project!