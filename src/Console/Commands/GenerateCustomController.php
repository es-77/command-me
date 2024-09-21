<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateCustomController extends Command
{
    protected $signature = 'make:custom-controller {name} {--validation}';
    protected $description = 'Generate a custom controller with dynamic content';

    public function handle()
    {
        $name = $this->argument('name');
        $validation = $this->option('validation') ? true : false;
        $dependency_injection = true;

        $stub_use = "Console/Commands/stubs/controller.api.stub";
        $stub = file_get_contents(app_path($stub_use));

        $stub = str_replace(
            ['{{ class }}', '{{REQUEST_TYPE}}','{{DEPENDENCY_INJECTION}}','{{GET_METHOD}}','{{CREATE_METHOD}}','{{UPDATE_METHOD}}','{{DELETE_METHOD}}','{{RESOURCE_METHOD}}','{{POPULATE_METHODS}}'],
            [
                $name,
                $validation ? 'CustomRequest $request' : 'Request $request',
                $dependency_injection ? 'CustomModel $customModel' : '$id',
                '$estimateTemplates = EstimatorTemplateFee::get();',
                '$estimatorTemplateFee = $this->handleUpsert(new EstimatorTemplateFee(), $request);',
                '$estimatorTemplateFee = $this->handleUpsert($estimatorTemplateFee, $request);',
                '$estimatorTemplateFee->delete();',
                'return new EstimatorTemplateFeeResource($estimatorTemplateFee);',
                $this->generatePopulateMethods()
            ],
            $stub
        );

        $path = app_path("Http/Controllers/{$name}.php");

        if (!file_exists($path)) {
            file_put_contents($path, $stub);
            $this->info("Controller created successfully at {$path}");
        } else {
            $this->error("Controller already exists at {$path}");
        }
    }

    protected function generatePopulateMethods()
    {
        $array = [
            [
                'name' => 'name',
                'type' => 'char',
                'attributes' => [4],
                'nullable' => null,
                'default' => 'test',
                'comment' => 'test comment',
            ],
            [
                'name' => 'age',
                'type' => 'bigInteger',
                'attributes' => [],
                'nullable' => null,
                'default' => 1,
                'comment' => 'no age limit',
            ],
            [
                'name' => 'phone',
                'type' => 'char',
                'attributes' => [18],
                'nullable' => null,
                'default' => null,
                'comment' => null,
            ],
            [
                'name' => 'type_of',
                'type' => 'enum',
                'attributes' => ['user', 'superadmin', 'manager'],
                'nullable' => null,
                'default' => 'user',
                'comment' => null,
            ],
        ];

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
}
