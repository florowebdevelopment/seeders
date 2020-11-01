# Seeders

<a href="https://packagist.org/packages/florowebdevelopment/seeders"><img src="https://poser.pugx.org/florowebdevelopment/seeders/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/florowebdevelopment/seeders"><img src="https://poser.pugx.org/florowebdevelopment/seeders/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/florowebdevelopment/seeders"><img src="https://poser.pugx.org/florowebdevelopment/seeders/license.svg" alt="License"></a>

This library adds seeders with ***versioning*** support for [Laravel](https://laravel.com/), suitable for a ***production environment***.
The seeders are stored in the *database/seeders* directory using JSON format.
Progress is tracked in the seeders table. so that the seeder is only run once.

## Install

```
composer require florowebdevelopment/seeders
```

```php
php artisan migrate
```

## Usage

### Create a new seeder and fill it manually.

With the command below you create an empty seeder, good if you are already familiar with the JSON structure, and you want to add the data manually.

```php
php artisan seeders:make my_table
```

Created Seeder: */database/seeders/YYYY_MM_DD_XXXXXX_my_table.json*:

```json
{
    "RECORDS": [
        {
            "name": "Example"
        }
    ]
}
```

In [Navicat for MySQL](https://www.navicat.com/en/products/navicat-for-mysql), the same structure is used when exporting to JSON file (.json).

### Create a new seeder and fill it automaticly.

With the command below you create a fully completed seeder based on existing records in a table.

```php
php artisan seeders:generate my_table
```

Which columns are requested depends on the specified table. Columns that are not answered are not included in the seeder. At range you specify which record IDs should be included in de seeder.

```bash
Columns
Add column "id" ? (yes/no) [no]: y
Add column "name" ? (yes/no) [no]: y
Add column "dateofbirth" ? (yes/no) [no]:
Range
my_table.id from [0]: 12
my_table.id to [14]: 14
```

Created Seeder: */database/seeders/YYYY_MM_DD_XXXXXX_my_table.json*:

```json
{
    "RECORDS": [
        {
            "id": 12,
            "name": "Bill Gates"
        },
        {
            "id": 13,
            "name": "Steve Jobs"
        },
        {
            "id": 14,
            "name": "John Doe"
        }
    ]
}
```

## Running Seeders

The pending seeders are executed with the command below:

```php
php artisan seeders:run
```

During execution it is checked whether the table and / or columns actually exist, otherwise the seeder is skipped.

```bash
Check seeder: YYYY_MM_DD_XXXXXX_my_table
[OK] Table "my_table" exists.
[OK] Columns "id, name" exists.
```

***Update Or Insert***

If the id column exists in the seeder, the [updateOrInsert](https://laravel.com/docs/8.x/queries#update-or-insert) method is used, otherwise the [insert](https://laravel.com/docs/8.x/queries#inserts) method.
