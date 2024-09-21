<?php

namespace EmmanuelSaleem\CommandMe\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class GenerateClasses extends Command
{
    protected $signature = 'run:command-me';
    protected $description = 'Generate Controller, Model, Request, Resource, Migration, Seeder, and Factory based on user input';
    protected $columnsName = [];
    protected $entityName = '';

    public function handle()
    {

        $entityName = $this->ask('What is the entity name?');
        $this->entityName = $entityName;
        $options = [
            'Controller' => 0,
            'Model' => 1,
            'Request' => 2,
            'Resource' => 3,
            'Migration' => 4,
            'Seeder' => 5,
            'Factory' => 6,
        ];
        $choices = $this->choice(
            'What would you like to generate? [Controller, Model, Request, Resource, Migration, Seeder, Factory]', 
            array_keys($options), 
            0, 
            null, 
            true
        );
        foreach ($choices as $choice) {
            $this->generateClass($choice, $entityName);
        }
    }

    protected function generateClass($type, $entityName)
    {
        switch ($type) {
            case 'Controller':
                $this->generateController($entityName);
                break;
            case 'Model':
                $this->generateModel($entityName);
                break;
            case 'Request':
                $this->generateRequest($entityName);
                break;
            case 'Resource':
                $this->generateResource($entityName);
                break;
            case 'Migration':
                $this->generateMigration($entityName);
                break;
            case 'Seeder':
                $this->generateSeeder($entityName);
                break;
            case 'Factory':
                $this->generateFactory($entityName);
                break;
        }
    }

    protected function generateMigration($entityName)
    {
        $name = $this->askEntityOrCustomName('Migration', $entityName);
        $migrationName = 'create_' . Str::snake($name) . '_table';
        Artisan::call('make:migration', ['name' => $migrationName]);
        $this->info("Migration $migrationName created successfully.");

        if ($this->confirm('Would you like to add columns to the migration?', true)) {
            $this->addColumnsToMigration($migrationName,$entityName);
        }
    }

    protected function askEntityOrCustomName($type, $entityName)
    {
        $useEntityName = $this->confirm("Would you like to use the entity name ($entityName) for the $type or provide a custom name?", true);
        return $useEntityName ? $entityName : $this->ask("Enter a custom name for the $type");
    }

    protected function addColumnsToMigration($migrationName,$entityName)
    {
        $columns = [];
        do {
            $columnName = $this->ask('Enter the column name (or type "done" to finish)');
            
            if (empty($columnName)) {
                $this->error('Column name cannot be empty. Please enter a valid name.');
                continue;
            }
            if (strtolower($columnName) === 'done') {
                break;
            }
    
            $columnType = $this->choice('Enter the column type', [
                'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime',
                'decimal', 'double', 'enum', 'float', 'increments', 'integer', 'longText',
                'mediumInteger', 'mediumText', 'morphs', 'nullableTimestamps', 'smallInteger',
                'tinyInteger', 'softDeletes', 'string', 'text', 'time', 'timestamp', 'timestamps',
                'rememberToken'
            ]);
    
            $attributes = [];
            if ($columnType === 'char' || $columnType === 'string') {
                $attributes[] = $this->ask('Enter the length of the column');
            }
    
            if ($columnType === 'decimal' || $columnType === 'double') {
                $attributes[] = $this->ask('Enter the precision of the column');
                $attributes[] = $this->ask('Enter the scale of the column');
            }
    
            if ($columnType === 'enum') {
                $enumOptions = $this->ask('Enter the enum options (comma-separated)');
                $attributes[] = $enumOptions;
            }
    
            if ($columnType === 'morphs') {
                $attributes[] = $this->ask('Enter the morphs name');
            }
    
            $isNullable = $this->confirm('Should this column be nullable?', false);
            $default = $this->ask('Enter a default value for this column (optional)', '');
            $comment = $this->ask('Enter a comment for this column (optional)', '');
    
            $columns[] = [
                'name' => $columnName,
                'type' => $columnType,
                'attributes' => $attributes,
                'nullable' => $isNullable,
                'default' => $default,
                'comment' => $comment,
            ];
        } while (true);
    
        $migrationPath = database_path('migrations');
        $files = scandir($migrationPath);
    
        $migrationFile = array_filter($files, function ($file) use ($migrationName) {
            return strpos($file, $migrationName) !== false;
        });
    
        if ($migrationFile) {
            $migrationFile = reset($migrationFile);
            $filePath = $migrationPath . '/' . $migrationFile;
            $fileContents = file_get_contents($filePath);
    
            $columnDefinitions = '';
            foreach ($columns as $column) {
                $columnDefinition = '$table->' . $column['type'] . '(\'' . $column['name'] . '\'';
    
                if (in_array($column['type'], ['char', 'string'])) {
                    $columnDefinition .= ', ' . $column['attributes'][0];
                }
    
                if (in_array($column['type'], ['decimal', 'double'])) {
                    $columnDefinition .= ', ' . $column['attributes'][0] . ', ' . $column['attributes'][1];
                }
    
                if ($column['type'] === 'enum') {
                    $enumOptions = array_map(fn($opt) => "'$opt'", explode(',', $column['attributes'][0]));
                    $columnDefinition .= ', [' . implode(', ', $enumOptions) . ']';
                }
    
                if ($column['type'] === 'morphs') {
                    $columnDefinition .= ', ' . $column['attributes'][0];
                }
    
                $columnDefinition .= ')';
    
                if ($column['nullable']) {
                    $columnDefinition .= '->nullable()';
                }
    
                if ($column['default']) {
                    $columnDefinition .= '->default(\'' . $column['default'] . '\')';
                }
    
                if ($column['comment']) {
                    $columnDefinition .= '->comment(\'' . $column['comment'] . '\')';
                }
    
                $columnDefinition .= ';' . PHP_EOL;
                $columnDefinitions .= $columnDefinition;
            }
    
            $pattern = '/Schema::create\(\'.+?\', function \(Blueprint \$table\) {[^}]*\}/s';
            $replacement = "Schema::create('test', function (Blueprint \$table) {\n" . $columnDefinitions . "}";
            $fileContents = preg_replace($pattern, $replacement, $fileContents);
    
            file_put_contents($filePath, $fileContents);
            $this->info("Migration columns added successfully.");
             $this->columnsName = $columns;
            print_r($columns);
            if ($this->confirm('Would you like to add these columns as fillable in the model?', true)) {
                $this->generateModel($this->convertToPascalCase($entityName), $columns);
            }
            if ($this->confirm('Would you like to add these columns in the seeder?', true)) {
                $this->generateSeeder($this->convertToPascalCase($entityName));
            }
            if ($this->confirm('Would you like to add these columns in the factory?', true)) {
                $this->generateFactory($this->convertToPascalCase($entityName));
            }
            if ($this->confirm('Would you like to add these columns in the Resource?', true)) {
                $this->generateResource($this->convertToPascalCase($entityName));
            }
            if ($this->confirm('Would you like to add these columns in the Validation Request?', true)) {
                $this->generateRequest($this->convertToPascalCase($entityName));
            }
            if ($this->confirm('Would you like to add these columns in the Controller?', true)) {
                $this->generateController($this->convertToPascalCase($entityName));
            }
            $this->generateRoute($this->convertToPascalCase($entityName));
        } else {
            $this->error("Migration file not found.");
        }
    }

    protected function generateModel($entityName, $columns = [])
    {
        $modelName = $this->askEntityOrCustomName('Model', $entityName);
        $modelPath = app_path("Models/{$modelName}.php");

        if (!file_exists($modelPath)) {
            if ($this->confirm("Model $modelName does not exist. Would you like to create it?", true)) {
                Artisan::call('make:model', ['name' => $modelName]);
                $this->info("Model $modelName created successfully.");
            } else {
                $this->error("Model $modelName does not exist and was not created.");
                return;
            }
        }

        if (!empty($columns) && $this->confirm('Would you like to add fillable properties to the model?', true)) {
            $fillableFields = array_map(function ($column) {
                return $column['name'];
            }, $columns);

            $this->addFillableToModel($modelPath, $fillableFields);
        }
    }

    protected function addFillableToModel($modelPath, $fillableFields)
    {
        $fileContents = file_get_contents($modelPath);

        $fillableArray = "protected \$fillable = [\n";
        foreach ($fillableFields as $field) {
            $fillableArray .= "    '{$field}',\n";
        }
        $fillableArray .= "];\n";

        if (strpos($fileContents, '$fillable') !== false) {
            $this->error("Model already contains fillable fields.");
        } else {
            $fileContents = preg_replace(
                '/class\s+\w+\s+extends\s+Model\s*\{/',
                "$0\n    $fillableArray",
                $fileContents
            );

            file_put_contents($modelPath, $fileContents);
            $this->info("Fillable fields added to the model.");
        }
    }

    protected function generateResource($entityName)
    {
        $name = $this->askEntityOrCustomName('Resource', $entityName);
        $resourcePath = app_path('Http/Resources/' . $name . 'Resource.php');
        if (!file_exists($resourcePath)) {
            Artisan::call('make:resource', ['name' => $name . 'Resource']);
            $this->info("Resource $name created successfully.");
        } else {
            $this->info("Resource $name already exists.");
        }
        if ($this->confirm('Would you like to populate the resource with data?', true)) {
            $this->populateResource($resourcePath, $entityName);
        }
    }

    protected function populateResource($resourcePath, $entityName)
    {
        // Fetch the list of columns (assuming you have a method to fetch column names and types)
        $columns = $this->columnsName;

        // Open the resource file and read its content
        $resourceContent = file_get_contents($resourcePath);

        // Find the return statement inside the toArray method
        $toArrayMethodPattern = '/public function toArray\(Request \$request\): array\s*{\s*return parent::toArray\(\$request\);\s*}/';

        // Build the array structure for columns
        $columnsArray = "return [\n";
        foreach ($columns as $column) {
            $columnsArray .= "            '" . $column['name'] . "' => \$this->" . $column['name'] . ",\n";
        }
        $columnsArray .= "        ];";

        // Replace the existing return statement in the toArray method
        $updatedContent = preg_replace(
            $toArrayMethodPattern,
            "public function toArray(Request \$request): array\n    {\n        $columnsArray\n    }",
            $resourceContent
        );

        // Save the updated content back to the resource file
        file_put_contents($resourcePath, $updatedContent);

        $this->info("Resource $entityName populated with columns successfully.");
    }


    protected function generateSeeder($entityName)
    {
        $name = $this->askEntityOrCustomName('Seeder', $entityName);
        $seederPath = database_path('seeders/' . $name . 'Seeder.php');

        if (!file_exists($seederPath)) {
            Artisan::call('make:seeder', ['name' => $name . 'Seeder']);
            $this->info("Seeder $name created successfully.");
        } else {
            $this->info("Seeder $name already exists.");
        }

        if ($this->confirm('Would you like to populate the seeder with data?', true)) {
            $this->populateSeeder($seederPath, $entityName);
        }
    }


    protected function populateSeeder($seederPath, $entityName)
    {
        $columns = $this->columnsName;

        if (empty($columns)) {
            $this->error('No migration columns found for ' . $entityName);
            return;
        }

        $data = [];
        foreach ($columns as $column) {
            $columnName = $column['name'];
            $columnType = $column['type'];
            $nullable = $column['nullable'];

            switch ($columnType) {
                case 'string':
                    $maxLength = isset($column['attributes'][0]) ? $column['attributes'][0] : 255;
                    $data[$columnName] = "Sample Text";
                    break;

                case 'bigInteger':
                case 'integer':
                case 'smallInteger':
                case 'tinyInteger':
                    $data[$columnName] = $nullable ? rand(1, 100) : rand(1, 1000);
                    break;

                case 'boolean':
                    $data[$columnName] = $nullable ? 'null' : true;
                    break;

                case 'date':
                case 'dateTime':
                case 'timestamp':
                    $data[$columnName] = $nullable ? 'null' : 'now()';
                    break;

                default:
                    $data[$columnName] = "'Default Value'";
                    break;
            }

            if ($nullable) {
                $data[$columnName] = 'null';
            }
        }

        $dataString = var_export($data, true);
        $seederContent = <<<EOD
        DB::table('$entityName')->insert($dataString);
        EOD;

        $fileContents = file_get_contents($seederPath);
        $fileContents = str_replace('//', $seederContent, $fileContents);
        file_put_contents($seederPath, $fileContents);

        $this->info("Seeder for $entityName populated successfully.");
    }


    protected function generateFactory($entityName)
    {
        $name = $this->askEntityOrCustomName('Factory', $entityName);
        $factoryPath = database_path('factories/' . $name . 'Factory.php');

        if (!file_exists($factoryPath)) {
            Artisan::call('make:factory', ['name' => $name . 'Factory']);
            $this->info("Factory $name created successfully.");
        } else {
            $this->info("Factory $name already exists.");
        }

        if ($this->confirm('Would you like to populate the factory with columns?', true)) {
            $this->populateFactory($factoryPath, $entityName);
        }
    }

    protected function populateFactory($factoryPath, $entityName)
    {
        $columns = $this->columnsName;
        if ($this->confirm('Do you want to use default columns or enter your own?', true)) {
            $columns = $columns;
        }

        $stubContent = file_get_contents($factoryPath);

        $factoryContent = $this->generateFactoryColumns($entityName, $columns);

        file_put_contents($factoryPath, $factoryContent);
        $this->info("Factory $entityName populated with columns.");
    }

    protected function generateFactoryColumns($entityName, $columns)
    {
        $factoryContent = "<?php\n\nuse Faker\Generator as Faker;\nuse App\\Models\\$entityName;\n\n/** @var \Illuminate\Database\Eloquent\Factory \$factory */\n\n\$factory->define($entityName::class, function (Faker \$faker) {\n    return [\n";
        
        foreach ($columns as $column) {
            $factoryContent .= "        '{$column['name']}' => ";

            switch ($column['type']) {
                case 'string':
                    $factoryContent .= "\$faker->word,\n";
                    break;
                case 'integer':
                case 'bigInteger':
                    $factoryContent .= "\$faker->numberBetween(1, 100),\n";
                    break;
                case 'date':
                    $factoryContent .= "\$faker->date(),\n";
                    break;
                default:
                    $factoryContent .= "\$faker->word,\n";
                    break;
            }
        }

        $factoryContent .= "    ];\n});\n";

        return $factoryContent;
    }


    protected function generateValidationRuleFromMigration($column)
    {
        $rules = [];

        if (empty($column['nullable'])) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        switch ($column['type']) {
            case 'string':
            case 'text':
                $rules[] = 'string';
                if (isset($column['attributes'][0])) {
                    $rules[] = 'max:' . $column['attributes'][0];
                }
                break;
            case 'integer':
            case 'bigInteger':
            case 'smallInteger':
            case 'tinyInteger':
                $rules[] = 'integer';
                break;
            case 'enum':
                $rules[] = 'in:' . implode(',', $column['attributes'][0]);
                break;
            case 'boolean':
            case 'tinyInteger':
                $rules[] = 'boolean';
                break;
            case 'date':
            case 'datetime':
                $rules[] = 'date';
                break;
            case 'time':
                $rules[] = 'date_format:H:i:s';
                break;
            // Add more types as needed
        }

        if (!empty($column['default'])) {
            $rules[] = 'default:' . $column['default'];
        }

        return implode('|', $rules);
    }

    protected function generateRequest($entityName)
    {
        $name = $this->askEntityOrCustomName('Request', $entityName);
        $requestClassName = $name . 'Request';
        $requestPath = app_path('Http/Requests/' . $requestClassName . '.php');

        if (!file_exists($requestPath)) {
            Artisan::call('make:request', ['name' => $requestClassName]);
            $this->info("Request $requestClassName created successfully.");
        } else {
            $this->info("Request $requestClassName already exists.");
        }

        if (!empty($this->columnsName) && $this->confirm('Would you like to populate validation rules for the request?', true)) {
            $this->populateRequestValidation($requestPath, $this->columnsName);
        }
    }

    protected function populateRequestValidation($requestPath, $columns)
    {
        $fileContents = file_get_contents($requestPath);

        $validationRules = "return [\n";
        foreach ($columns as $column) {
            $rules = [];

            if (in_array($column['type'], ['string', 'text', 'char'])) {
                $rules[] = "'string'";
            } elseif (in_array($column['type'], ['integer', 'bigInteger', 'smallInteger', 'tinyInteger'])) {
                $rules[] = "'integer'";
            } elseif (in_array($column['type'], ['decimal', 'float', 'double'])) {
                $rules[] = "'numeric'";
            } elseif (in_array($column['type'], ['boolean'])) {
                $rules[] = "'boolean'";
            } elseif (in_array($column['type'], ['date', 'dateTime', 'timestamp'])) {
                $rules[] = "'date'";
            } elseif ($column['type'] === 'enum') {
                $enumOptions = implode(',', array_map(function($option) {
                    return "'$option'";
                }, explode(',', $column['attributes'][0])));
                $rules[] = "'in:{$enumOptions}'";
            }

            if ($column['nullable']) {
                $rules[] = "'nullable'";
            } else {
                $rules[] = "'required'";
            }

            $validationRules .= "    '{$column['name']}' => [" . implode(', ', $rules) . "],\n";
        }
        $validationRules .= "];";

        $updatedContents = preg_replace('/return\s+\[.*\];/s', $validationRules, $fileContents);

        file_put_contents($requestPath, $updatedContents);

        $this->info("Validation rules populated in {$requestPath}");
    }


    protected function generateController($name)
    {
        $entityName = $this->convertToPascalCase($this->entityName);
        $entityVaribleName = $this->toCamelCase($this->entityName);
        $validation =  true;
        $dependency_injection = true;

        $stub_use = __DIR__ . '/stubs/controller.api.stub';
        $stub = file_get_contents($stub_use);
        
        $stub = str_replace(
            ['{{ class }}', '{{REQUEST_TYPE}}','{{DEPENDENCY_INJECTION}}','{{GET_METHOD}}','{{CREATE_METHOD}}','{{UPDATE_METHOD}}','{{DELETE_METHOD}}','{{RESOURCE_METHOD}}','{{POPULATE_METHODS}}'],
            [
                $name,
                $validation ? ''.$entityName.'Request $request' : 'Request $request',
                $dependency_injection ? ''.$entityName.' $'.$this->toCamelCase($entityName).'' : '$id',
                '$'.$entityVaribleName.'s = '.$entityName.'::get();',
                '$'.$entityVaribleName.' = $this->handleUpsert(new '.$entityName.'(), $request);',
                '$'.$entityVaribleName.' = $this->handleUpsert($'.$entityName.', $request);',
                '$'.$entityVaribleName.'->delete();',
                'return new '.$entityName.'Resource($'.$entityVaribleName.');',
                $this->generatePopulateMethods()
            ],
            $stub
        );

        $path = app_path("Http/Controllers/{$name}Controller.php");

        if (!file_exists($path)) {
            file_put_contents($path, $stub);
            $this->info("Controller created successfully at {$path}");
        } else {
            $this->error("Controller already exists at {$path}");
        }
        return 0;
    }

    protected function generatePopulateMethods()
    {

        $array = $this->columnsName;

        $columns = collect($array)->pluck('name')->toArray();

        $methods = [];

        $methods[] = "protected function setData(\$validateData)";
        $methods[] = "{";
        $methods[] = "    return [";
        foreach ($columns as $column) {
            $methods[] = "        '$column' => \$validateData->$column,";
        }
        $methods[] = "    ];";
        $methods[] = "}";

        $methods[] = "";

        $methods[] = "protected function handleUpsert(\$model, \$validateData)";
        $methods[] = "{";
        $methods[] = "    \$model->fill(\$this->setData(\$validateData));";
        $methods[] = "    \$model->save();";
        $methods[] = "    return \$model;";
        $methods[] = "}";

        return implode("\n", $methods);
    }

    protected function generateRoute($entityName)
    {
        if ($this->confirm('Would you like to add a route?', true)) {
            $routeType = $this->choice('Which type of route would you like to add?', ['web', 'api']);
            $routeFile = $routeType === 'web' ? 'web.php' : 'api.php';
            $routePath = base_path("routes/{$routeFile}");
            
            if (!file_exists($routePath)) {
                $this->error("Route file not found: {$routePath}");
                return;
            }
    
            // Prompt user for the type of route
            $routeKind = $this->choice('What kind of route would you like to create?', ['Resource', 'Simple']);
    
            $controllerName = ucfirst($entityName) . 'Controller'; // Assuming controller is named after entity
            $routeContent = '';
    
            if ($routeKind === 'Resource') {
                if ($routeType === 'web') {
                    $routeContent = "\nRoute::resource('/" . strtolower($entityName) . "', $controllerName::class);";
                } else {
                    $routeContent = "\nRoute::apiResource('/" . strtolower($entityName) . "', $controllerName::class);";
                }
            } else { // Simple
                $routeContent = "\nRoute::get('/" . strtolower($entityName) . "', '{$controllerName}@index');";
                $routeContent .= "\nRoute::get('/" . strtolower($entityName) . "/{id}', '{$controllerName}@show');";
                $routeContent .= "\nRoute::post('/" . strtolower($entityName) . "', '{$controllerName}@store');";
                $routeContent .= "\nRoute::put('/" . strtolower($entityName) . "/{id}', '{$controllerName}@update');";
                $routeContent .= "\nRoute::delete('/" . strtolower($entityName) . "/{id}', '{$controllerName}@destroy');";
            }
    
            // Add the route to the route file
            file_put_contents($routePath, $routeContent, FILE_APPEND);
    
            $this->info("Routes added to routes/{$routeFile}.");
        }
    }
    


    protected function convertToPascalCase($input) {
        $input = str_replace(['-', '_'], ' ', $input);
        $input = ucwords($input);
        return str_replace(' ', '', $input);
    }

    protected function toCamelCase($string) {
        $string = preg_replace('/[^a-zA-Z0-9]+/', '_', $string);
        $string = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $string = lcfirst($string);
    
        return $string;
    }

    // protected function handleSetDataInsertMethod(){

    //     $array = [
    //         [
    //             'name' => 'name',
    //             'type' => 'char',
    //             'attributes' => [4],
    //             'nullable' => null,
    //             'default' => 'test',
    //             'comment' => 'test comment',
    //         ],
    //         [
    //             'name' => 'age',
    //             'type' => 'bigInteger',
    //             'attributes' => [],
    //             'nullable' => null,
    //             'default' => 1,
    //             'comment' => 'no age limit',
    //         ],
    //         [
    //             'name' => 'phone',
    //             'type' => 'char',
    //             'attributes' => [18],
    //             'nullable' => null,
    //             'default' => null,
    //             'comment' => null,
    //         ],
    //         [
    //             'name' => 'type_of',
    //             'type' => 'enum',
    //             'attributes' => ['user', 'superadmin', 'manager'],
    //             'nullable' => null,
    //             'default' => 'user',
    //             'comment' => null,
    //         ],
    //     ];
    //     $columns = collect($array)->pluck('name')->toArray();
    
    //     protected function setData($validateData)
    //     {
    //         return [
    //             foreach ($columns as $key => $column) {

    //                 ''.$column.'' => '$validateData->'.$column,
    //             }
    //         ];
    //     }
    //     protected function handleUpsert($model, $validateData)
    //     {
    //         $model->fill($this->setData($validateData));
    //         $model->save();
    //         return $model;
    //     }
    // }

    // protected function generateController($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Controller', $entityName);
    //     Artisan::call('make:controller', ['name' => $name . 'Controller']);
    //     $this->info("Controller $name created successfully.");
    // }



       // protected function generateSeeder($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Seeder', $entityName);
    //     Artisan::call('make:seeder', ['name' => $name . 'Seeder']);
    //     $this->info("Seeder $name created successfully.");
    // }

    // protected function generateFactory($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Factory', $entityName);
    //     Artisan::call('make:factory', ['name' => $name . 'Factory']);
    //     $this->info("Factory $name created successfully.");
    // }
        // protected function generateModel($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Model', $entityName);
    //     Artisan::call('make:model', ['name' => $name]);
    //     $this->info("Model $name created successfully.");
    // }

    // protected function generateRequest($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Request', $entityName);
    //     Artisan::call('make:request', ['name' => $name . 'Request']);
    //     $this->info("Request $name created successfully.");
    // }

    // protected function generateResource($entityName)
    // {
    //     $name = $this->askEntityOrCustomName('Resource', $entityName);
    //     Artisan::call('make:resource', ['name' => $name . 'Resource']);
    //     $this->info("Resource $name created successfully.");
    // }
 
    
}
