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
-   **Vue & Inertia.js**

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

To install **Vue & Inertia.js**, run the following commands:

```bash
# Install Vue
npm i vue@latest

# Install Inertia server-side
composer require inertiajs/inertia-laravel
```

Make app.blade.php in the resources/views/ with the following code:

```bash
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @vite(['resources/js/app.js'])
    @inertiaHead
    @routes
</head>

<body>
    @inertia
</body>

</html>
```

Next is to run _php artisan inertia:middleware_
Add this code in the bootstrap/app.php

```
$middleware->web(append: [
  HandleInertiaRequests::class,
]);
```

```bash
# Install Inertia client-side
npm install @inertiajs/vue3
```

Initialize createApp in resources/js/app.js

```bash
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
resolve: name => {
const pages = import.meta.glob('./Pages/\*_/_.vue', { eager: true })
return pages[`./Pages/${name}.vue`]
},
setup({ el, App, props, plugin }) {
createApp({ render: () => h(App, props) })
.use(plugin)
.mount(el)
},
})
```

```bash
# Install Vue plugin for Vite
npm install @vitejs/plugin-vue
```

```bash
Modify `vite.config.js`
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue' //add import

export default defineConfig({
plugins: [
vue(), /_ added this line _/
laravel({
input: ['resources/css/app.css', 'resources/js/app.js'],
refresh: true,
}),
],
});
```

```bash
# Install npx globally (using Git Bash, change to Git Bash)
npm i -g npx

# Install shadcn-vue components
npx shadcn-vue@latest init
```

Make a file `tsconfig.json` file in the root directory, i.e. Campus Connect/

```bash
{
"compilerOptions": {
"target": "esnext",
"module": "esnext",
"moduleResolution": "bundler",
"strict": true,
"jsx": "preserve",
"importHelpers": true,
"skipLibCheck": true,
"esModuleInterop": true,
"allowSyntheticDefaultImports": true,
"sourceMap": true,
"baseUrl": ".",
"types": [
"vite/client",
"@types/node"
],
"paths": {
"@/_": ["resources/js/_"]
},
"lib": ["esnext", "dom", "dom.iterable", "scripthost"]
},
"include": [
"resources/js/**/*.ts",
"resources/js/**/*.d.ts",
"resources/js/**/*.tsx",
"resources/js/**/*.vue"
],
"exclude": ["node_modules", "public"]
}
```

and execute

```bash
npm install --save-dev typescript @types/node
```

> components.json should be present in the root directory, i.e. Campus Connect/
> If components.json is missing do corrupted, skip this steps.

-   if `components.json` corrupt, delete `components.json` then

```bash
npx clear-npx-cache
npm cache clean --force
```

```bash
# Install additional dependencies

npm install @vueuse/core
npm install -D @iconify/vue @iconify-json/radix-icons

# Install Ziggy for route handling

composer require tightenco/ziggy
```

Modify the app.blade.php to support Ziggy

```bash
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @vite(['resources/js/app.js'])
    @inertiaHead
    @routes /* Add this line */
</head>

<body>
    @inertia
</body>

</html>
```

If `npx shadcn-vue@latest` fails, you'll need to:

```bash
# Install TypeScript dependencies
npm install --save-dev typescript @types/node

# If components.json becomes corrupt:
npx clear-npx-cache
npm cache clean --force
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
