# Command Me

🚀 **Introducing My Custom Laravel Artisan Command!** 🚀

This powerful Laravel Artisan Command streamlines the process of generating a fully functional CRUD application in minutes!

## Features

- **Generate Migrations**: Define your database columns directly in the command.
- **Create Models with Fillable Attributes**: Automatically set up your models without manual updates.
- **Generate Seeders & Factories**: Effortlessly populate your database.
- **Build Controllers**: Complete CRUD operations and validation rules are generated automatically.
- **Create API Resources**: Easily generate resources to format responses.
- **Add Routes**: Choose between resource routes or simple routes to fit your needs.

## How It Helps Developers

This command saves time, ensures consistency, and reduces the potential for errors. Just enter your column details once, and let the command handle the rest!

## Key Benefits

- Save hours of repetitive coding 🕒
- Ensure best practices in your application ✅
- Accelerate your development process 🚀

If you're a Laravel developer looking to simplify your workflow and save time, this tool is a game-changer! 💻


Follow the prompts to input the entity name and specify what you would like to generate (e.g., Controller, Model, Request, Resource, Migration, Seeder, Factory).

Enter the details for the migration, including column names and types.

Decide if you want to add these columns as fillable properties in the model.

Proceed to create the Seeder, Factory, Resource, and Request, populating them with data as needed.

Finally, choose to add a route, selecting between web and API routes.

Sample Interaction

## What is the entity name?:
> user admin

## What would you like to generate? [Controller, Model, Request, Resource, Migration, Seeder, Factory]:
> 4

## Would you like to use the entity name (user admin) for the Migration or provide a custom name? (yes/no) [yes]:
> yes

Migration create_user_admin_table created successfully.

## Would you like to add columns to the migration? (yes/no) [yes]:
> yes

## Enter the column name (or type "done" to finish):
> name

## Enter the column type:
  [0] bigIncrements
  [1] bigInteger
  [2] binary
  [3] boolean
  [4] char
  [5] date
  [6] dateTime
  [7] decimal
  [8] double
  [9] enum
  [10] float
  [11] increments
  [12] integer
  [13] longText
  [14] mediumInteger
  [15] mediumText
  [16] morphs
  [17] nullableTimestamps
  [18] smallInteger
  [19] tinyInteger
  [20] softDeletes
  [21] string
  [22] text
  [23] time
  [24] timestamp
  [25] timestamps
  [26] rememberToken
> 4
 
## Enter the length of the column:
> 12

## Should this column be nullable? (yes/no) [no]:
> no

## Enter a default value for this column (optional):
> Emmanuel

## Enter a comment for this column (optional):
> This is comment

## Enter the column name (or type "done" to finish):
> done

Migration columns added successfully.

## Would you like to add these columns as fillable in the model? (yes/no) [yes]:
> yes

## Would you like to use the entity name (UserAdmin) for the Model or provide a custom name? (yes/no) [yes]:
> yes

## Model UserAdmin does not exist. Would you like to create it? (yes/no):
> yes

Model UserAdmin created successfully.

## Would you like to add fillable properties to the model? (yes/no):
> yes

Fillable fields added to the model.

## Would you like to add these columns in the seeder? (yes/no):
> yes

## Would you like to use the entity name (UserAdmin) for the Seeder or provide a custom name? (yes/no):
> yes

Seeder UserAdmin created successfully.

## Would you like to populate the seeder with data? (yes/no):
> yes

Seeder for UserAdmin populated successfully.

## Would you like to add these columns in the factory? (yes/no):
> yes

## Would you like to use the entity name (UserAdmin) for the Factory or provide a custom name? (yes/no):
> yes

Factory UserAdmin created successfully.

## Would you like to populate the factory with columns? (yes/no):
> yes

## Do you want to use default columns or enter your own? (yes/no):
> yes

Factory UserAdmin populated with columns.

## Would you like to add these columns in the Resource? (yes/no):
> yes

## Would you like to use the entity name (UserAdmin) for the Resource or provide a custom name? (yes/no):
> yes

Resource UserAdmin created successfully.

## Would you like to populate the resource with data? (yes/no):
> yes

Resource UserAdmin populated with columns successfully.

## Would you like to add these columns in the Validation Request? (yes/no):
> yes

## Would you like to use the entity name (UserAdmin) for the Request or provide a custom name? (yes/no):
> yes

Request UserAdminRequest created successfully.

## Would you like to populate validation rules for the request? (yes/no):
> yes

Validation rules populated in `C:\laragon\www\package-create\app\Http\Requests\UserAdminRequest.php`.

 Wo##uld you like to add these columns in the Controller? (yes/no):
> yes

Controller created successfully at `C:\laragon\www\package-create\app\Http\Controllers\UserAdminController.php`.

## Would you like to add a route? (yes/no):
> yes

## Which type of route would you like to add?:
  [0] web
  [1] api
> 0

## What kind of route would you like to create?:
  [0] Resource
  [1] Simple
> 1

Routes added to `routes/web.php`.



## Installation

You can install the package via Composer:

```bash
composer require emmanuel_saleem/command_me:dev-maste

php artisan run:command-me
