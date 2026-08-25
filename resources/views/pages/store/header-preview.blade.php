<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Header preview</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ cartOpen: false, mobileMenuOpen: false, cart: [] }" class="bg-white">
        <x-store.layout.header :categories="$categories" />
    </body>
</html>
