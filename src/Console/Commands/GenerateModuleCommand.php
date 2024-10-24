<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateFilter extends Command
{
    protected $signature = 'make:filter {moduleSingular} {modulePlural} {displayName} {fields*}';
    protected $description = 'Generate a new module with a model and filter based on provided fields';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $moduleSingular = ucfirst($this->argument('moduleSingular'));
        $modulePlural = strtolower($this->argument('modulePlural'));
        $fields = $this->argument('fields');

        // Create directory for filters
        $filterPath = app_path("Filters/{$moduleSingular}");
        if (!is_dir($filterPath)) {
            mkdir($filterPath, 0755, true);
        }

        // Create filter file
        $this->createFilter($moduleSingular, $fields, $filterPath);

        // Create directory for models
        $modelPath = app_path("Models/{$moduleSingular}");
        if (!is_dir($modelPath)) {
            mkdir($modelPath, 0755, true);
        }
        // Create model file
        $this->createModel($moduleSingular, $modulePlural, $fields, $modelPath);

        // Create directory for models
        $requestPath = app_path("Http/Requests/{$moduleSingular}");
        if (!is_dir($requestPath)) {
            mkdir($requestPath, 0755, true);
        }
        $this->createRequests($moduleSingular, $modulePlural, $fields, $requestPath);
        $this->createRepository($moduleSingular, $modulePlural, $fields, app_path('Repositories'));

        $controllerPath = app_path("Http/Controllers/{$moduleSingular}");
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0755, true);
        }
        $this->createController($moduleSingular, $modulePlural, $fields, $controllerPath);

        // Create directory for models
        $resourcePath = app_path("Http/Resources/{$moduleSingular}");
        if (!is_dir($resourcePath)) {
            mkdir($resourcePath, 0755, true);
        }
        $this->createResources($moduleSingular, $modulePlural, $fields, $resourcePath);

        $this->info("Module and Model created successfully!");
    }

    protected function createFilter($moduleSingular, $fields, $path): void
    {
        $fileName = "{$moduleSingular}Filter.php";
        $filePath = "{$path}/{$fileName}";
        $fieldsArray = json_decode($fields[0], true);
        $stub = $this->generateFilterClass($moduleSingular, $fieldsArray);
        file_put_contents($filePath, $stub);
    }

    protected function generateFilterClass($moduleSingular, $fieldsArray): string
    {
        $classContent = "<?php\n\nnamespace App\Filters\\{$moduleSingular};\n\n";
        $classContent .= "use Ahmadifar\Filters\Filters;\n";
        $classContent .= "use App\Traits\Filters\FilterIdsTrait;\n";
        $classContent .= "use Illuminate\Database\Eloquent\Builder;\n\n";
        $classContent .= "class {$moduleSingular}Filter extends Filters\n{\n";
        $classContent .= "    use FilterIdsTrait;\n\n";

        // Generate protected $filters array
        $classContent .= "    protected array \$filters = [\n";
        $classContent .= "        'ids',\n";
        foreach ($fieldsArray as $field) {
            $classContent .= "        '{$field['name_field']}',\n";
        }
        $classContent .= "        'search_all',\n";
        $classContent .= "    ];\n\n";

        // Generate protected $attributes array
        $classContent .= "    public array \$attributes = [\n";
        $classContent .= "        'ids' => 'array',\n";

        foreach ($fieldsArray as $field) {
            $classContent .= "        '{$field['name_field']}' => '{$field['type']}',\n";
        }
        $classContent .= "        'search_all' => 'string',\n";
        $classContent .= "    ];\n\n";

        // Generate protected $orderByColumns array
        $classContent .= "    public array \$orderByColumns = [\n";
        $classContent .= "        'id',\n";
        foreach ($fieldsArray as $field) {
            $classContent .= "        '{$field['name_field']}',\n";
        }
        $classContent .= "    ];\n\n";

        // Generate methods for each field
        foreach ($fieldsArray as $field) {
            $classContent .= "    protected function {$field['name_field']}(string \${$field['name_field']}): Builder\n";
            $classContent .= "    {\n";
            $classContent .= "        return \$this->builder->where('{$field['name_field']}', 'like', \"%\${$field['name_field']}%\");\n";
            $classContent .= "    }\n\n";
        }

        $classContent .= "    protected function search_all(string \$search_all): Builder\n";
        $classContent .= "    {\n";
        $classContent .= "        return \$this->builder->where(function (\$query) use (\$search_all) {\n            \$query";
        foreach ($fieldsArray as $field) {
            $classContent .= "\n               ->orWhere('{$field['name_field']}', 'like', \"%\$search_all%\")";
        }
        $classContent .= ";\n        });\n    }\n\n";

        $classContent .= "}";

        return $classContent;
    }

    protected function createModel($moduleSingular, $modulePlural, $fields, $path): void
    {
        $fileName = "{$moduleSingular}.php";
        $filePath = "{$path}/{$fileName}";
        $fields = json_decode($fields[0], true);
        $stub = $this->generateModelClass($moduleSingular, $modulePlural, $fields);
        file_put_contents($filePath, $stub);
    }


    protected function generateModelClass($moduleSingular, $modulePlural, $fields): string
    {
        $classContent = "<?php\n\nnamespace App\Models\\{$moduleSingular};\n\n";
        $classContent .= "use App\Models\BaseModel;\n";
        $classContent .= "use Illuminate\Database\Eloquent\Factories\HasFactory;\n\n";
        $classContent .= "class {$moduleSingular} extends BaseModel\n{\n";
        $classContent .= "    use HasFactory;\n\n";

        // Fillable fields
        $classContent .= "    protected \$fillable = [\n";
        foreach ($fields as $field) {
            $classContent .= "        '{$field['name_field']}',\n";
        }
        $classContent .= "    ];\n\n";

        // ROUTE constant
        $classContent .= "    const ROUTE = 'panel.{$modulePlural}';\n\n";

        // VIEW_MANAGE definition
        $classContent .= "    const VIEW_MANAGE = [\n";
        $classContent .= "        \"link_tbody\" => self::ROUTE . '.tbody',\n";
        $classContent .= "        \"excel_csv\" => self::ROUTE . '.excelCsv',\n";
        $classContent .= "        \"link_edit\" => self::ROUTE . '.edit',\n";
        $classContent .= "        \"link_create\" => self::ROUTE . '.create',\n";
        $classContent .= "        \"link_list\" => self::ROUTE . '.list',\n";
        $classContent .= "        \"link_update\" => self::ROUTE . '.update',\n";
        $classContent .= "        \"link_store\" => self::ROUTE . '.store',\n";
        $classContent .= "        \"link_delete\" => self::ROUTE . '.delete',\n";
        $classContent .= "        \"title_page\" => '{$moduleSingular}',\n";
        $classContent .= "        \"trs\" => [\n";
        $classContent .= "            [\n";
        $classContent .= "                \"view\" => 'inputNull',\n";
        $classContent .= "                \"data\" => ['name' => 'id', 'fName' => 'id', 'oName' => 'id']\n";
        $classContent .= "            ],\n";

        // TRS Fields
        foreach ($fields as $field) {
            $classContent .= "            [\n";
            $classContent .= "                \"view\" => 'inputString',\n";
            $classContent .= "                \"data\" => ['name' => '{$field['name']}', 'fName' => '{$field['name_field']}', 'oName' => '{$field['name_field']}']\n";
            $classContent .= "            ],\n";
        }
        $classContent .= "        ],\n";

        // TDS Fields
        $classContent .= "        \"tds\" => [\n";
        $classContent .= "            [\n";
        $classContent .= "                \"type\" => 'string',\n";
        $classContent .= "                \"icon\" => '',\n";
        $classContent .= "                \"class\" => \"\",\n";
        $classContent .= "                \"fName\" => 'id',\n";
        $classContent .= "            ],\n";
        foreach ($fields as $field) {
            $classContent .= "            [\n";
            $classContent .= "                \"type\" => 'string',\n";
            $classContent .= "                \"icon\" => '',\n";
            $classContent .= "                \"class\" => \"\",\n";
            $classContent .= "                \"fName\" => '{$field['name_field']}',\n";
            $classContent .= "            ],\n";
        }
        $classContent .= "        ],\n";

        // Bts
        $classContent .= "        \"bts\" => [\n            [\n                \"name\" => \"Edit\",";
        $classContent .= "                \"href\" => self::ROUTE . '.edit',\n                \"icon\" => 'ti-pencil',";
        $classContent .= "            ],\n            [\n                \"name\" => \"Delete\",\n                \"href\" => self::ROUTE . '.delete',";
        $classContent .= "                \"icon\" => 'ti-pencil',\n            ]\n        ],\n";
        // Left Form Fields
        $classContent .= "        \"forms\" => [\n";
        $classContent .= "            \"left\" => [\n";
        foreach ($fields as $field) {
            $classContent .= "                [\n";
            $classContent .= "                    'name' => '{$field['name']}',\n";
            $classContent .= "                    'type' => 'string',\n";
            $classContent .= "                    'fname' => '{$field['name_field']}',\n";
            $classContent .= "                    'required' => 'required',\n";
            $classContent .= "                    'class' => 'col-6',\n";
            $classContent .= "                ],\n";
        }
        $classContent .= "            ],\n";
        $classContent .= "            \"right\" => [],\n";
        $classContent .= "        ]\n";
        $classContent .= "    ];\n";
        $classContent .= "}\n";

        return $classContent;
    }


    protected function createRequests($moduleSingular, $modulePlural, $fields, $path): void
    {
        $fileName = "UpdateRequest.php";
        $filePath = "{$path}/{$fileName}";
        $fields = json_decode($fields[0], true);
        $stub = $this->generateRequestClass($moduleSingular, $modulePlural, $fields, 'Update');
        file_put_contents($filePath, $stub);

        $fileName = "StoreRequest.php";
        $filePath = "{$path}/{$fileName}";
        $stub = $this->generateRequestClass($moduleSingular, $modulePlural, $fields, 'Store');
        file_put_contents($filePath, $stub);

        $fileName = "CreateRequest.php";
        $filePath = "{$path}/{$fileName}";
        $stub = $this->generateRequestEmptyClass($moduleSingular, $modulePlural, $fields, 'Create');
        file_put_contents($filePath, $stub);
        $fileName = "EditRequest.php";
        $filePath = "{$path}/{$fileName}";
        $stub = $this->generateRequestEmptyClass($moduleSingular, $modulePlural, $fields, 'Edit');
        file_put_contents($filePath, $stub);
        $fileName = "ListRequest.php";
        $filePath = "{$path}/{$fileName}";
        $stub = $this->generateRequestEmptyClass($moduleSingular, $modulePlural, $fields, 'List');
        file_put_contents($filePath, $stub);
        $fileName = "DeleteRequest.php";
        $filePath = "{$path}/{$fileName}";
        $stub = $this->generateRequestEmptyClass($moduleSingular, $modulePlural, $fields, 'Delete');
        file_put_contents($filePath, $stub);

    }

    public function generateRequestClass($moduleSingular, $modulePlural, $fields, $titleClass): string
    {

        $classContent = "<?php\n\nnamespace App\Http\Requests\\$moduleSingular;\n\n";
        $classContent .= "use App\Http\Requests\BaseRequest;\n";
        $classContent .= "class {$titleClass}Request extends BaseRequest\n{\n";
        $classContent .= "    public function authorize(): bool\n";
        $classContent .= "    {\n";
        $classContent .= "        return true;\n";
        $classContent .= "    }\n";
        $classContent .= "    public function rules(): array\n";
        $classContent .= "    {\n";
        $classContent .= "        return [\n";
        foreach ($fields as $field) {
            $classContent .= "            \"{$field['name_field']}\" => \$this->rString(),\n";
        }
        $classContent .= "        ];\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        return $classContent;
    }
    public function generateRequestEmptyClass($moduleSingular, $modulePlural, $fields, $titleClass): string
    {
        $classContent = "<?php\n\nnamespace App\Http\Requests\\$moduleSingular;\n\n";
        $classContent .= "use App\Http\Requests\BaseRequest;\n";
        $classContent .= "class {$titleClass}Request extends BaseRequest\n{\n";
        $classContent .= "    public function authorize(): bool\n";
        $classContent .= "    {\n";
        $classContent .= "        return true;\n";
        $classContent .= "    }\n";
        $classContent .= "    public function rules(): array\n";
        $classContent .= "    {\n";
        $classContent .= "        return [\n";
        $classContent .= "        ];\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        return $classContent;
    }

    protected function createRepository($moduleSingular, $modulePlural, $fields, $path): void
    {
        $fileName = "{$moduleSingular}Repository.php";
        $filePath = "{$path}/{$fileName}";
        $fields = json_decode($fields[0], true);
        $stub = $this->generateRepositoryClass($moduleSingular, $modulePlural, $fields);
        file_put_contents($filePath, $stub);
    }

    public function generateRepositoryClass($moduleSingular, $modulePlural, $fields): string
    {
        $classContent = "<?php\n\nnamespace App\Repositories;\n\n";
        $classContent .= "use App\Models\\{$moduleSingular}\\{$moduleSingular};\n";
        $classContent .= "class {$moduleSingular}Repository extends BaseRepository\n{\n";
        $classContent .= "    public function getModel(): string\n";
        $classContent .= "    {\n";
        $classContent .= "        return {$moduleSingular}::class;\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        return $classContent;
    }


    protected function createController($moduleSingular, $modulePlural, $fields, $path): void
    {
        $fileName = "{$moduleSingular}Controller.php";
        $filePath = "{$path}/{$fileName}";
        $fields = json_decode($fields[0], true);
        $stub = $this->generateControllerClass($moduleSingular, $modulePlural, $fields);
        file_put_contents($filePath, $stub);
    }

    public function generateControllerClass($moduleSingular, $modulePlural, $fields): string
    {
        $classContent = "<?php\n\nnamespace App\Http\Controllers\\{$moduleSingular};\n\n";
        $classContent .= "use App\Filters\\{$moduleSingular}\\{$moduleSingular}Filter as Filter;\n";
        $classContent .= "use App\Http\Controllers\BaseController;\n";
        $classContent .= "use App\Http\Requests\\{$moduleSingular}\\StoreRequest as Store;\n";
        $classContent .= "use App\Http\Requests\\{$moduleSingular}\\UpdateRequest as Update;\n";
        $classContent .= "use App\Http\Requests\\{$moduleSingular}\\EditRequest as Edit;\n";
        $classContent .= "use App\Http\Resources\\{$moduleSingular}\\{$moduleSingular}Resource as Resource;\n";
        $classContent .= "use App\Models\\{$moduleSingular}\\{$moduleSingular};\n";
        $classContent .= "use App\Repositories\\{$moduleSingular}Repository;\n";
        $classContent .= "use Illuminate\Contracts\Container\BindingResolutionException;\n";
        $classContent .= "use Illuminate\Http\JsonResponse;\n";
        $classContent .= "use Illuminate\Http\Request;\n";
        $classContent .= "use Illuminate\View\View;\n\n";

        $classContent .= "class {$moduleSingular}Controller extends BaseController\n{\n";
        $classContent .= "    protected {$moduleSingular}Repository \$repository;\n\n";

        $classContent .= "    /**\n";
        $classContent .= "     * @param {$moduleSingular}Repository \$repository\n";
        $classContent .= "     */\n";
        $classContent .= "    public function __construct({$moduleSingular}Repository \$repository)\n";
        $classContent .= "    {\n";
        $classContent .= "        \$this->repository = \$repository;\n";
        $classContent .= "    }\n\n";

        // List function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Filter \$filters\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @return View\n";
        $classContent .= "     * @throws BindingResolutionException\n";
        $classContent .= "     */\n";
        $classContent .= "    public function list(Filter \$filters, Request \$request): View\n";
        $classContent .= "    {\n";
        $classContent .= "        \$viewManage = {$moduleSingular}::VIEW_MANAGE;\n";
        $classContent .= "        \$responses = \$this->dataList(\$filters, \$request);\n";
        $classContent .= "        \$pageNumbers = self::PAGE_NUMBERS;\n\n";
        $classContent .= "        return view('panel.template.list',\n";
        $classContent .= "            compact('responses', 'pageNumbers', 'viewManage')\n";
        $classContent .= "        );\n";
        $classContent .= "    }\n\n";

        // listTBody function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Filter \$filters\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @return array\n";
        $classContent .= "     * @throws BindingResolutionException\n";
        $classContent .= "     */\n";
        $classContent .= "    public function listTBody(Filter \$filters, Request \$request): array\n";
        $classContent .= "    {\n";
        $classContent .= "        \$viewManage = {$moduleSingular}::VIEW_MANAGE;\n";
        $classContent .= "        \$responses = \$this->dataList(\$filters, \$request);\n\n";
        $classContent .= "        \$tbodyHtml = view('panel.template.tbody', compact('responses', 'viewManage'))->render();\n";
        $classContent .= "        \$paginationHtml = view('panel.layouts.paginate', compact('responses'))->render();\n\n";
        $classContent .= "        return [\n";
        $classContent .= "            \"tbody\" => \$tbodyHtml,\n";
        $classContent .= "            \"pagination\" => \$paginationHtml,\n";
        $classContent .= "        ];\n";
        $classContent .= "    }\n\n";

        // dataList function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Filter \$filters\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @return mixed\n";
        $classContent .= "     * @throws BindingResolutionException\n";
        $classContent .= "     */\n";
        $classContent .= "    function dataList(Filter \$filters, Request \$request): mixed\n";
        $classContent .= "    {\n";
        $classContent .= "        \$responses = listPaginate(Resource::collection(\n";
        $classContent .= "            \$this->repository->list()\n";
        $classContent .= "                ->filter(\$filters)\n";
        $classContent .= "                ->paginate(\$this->getPageSize(\$request))\n";
        $classContent .= "        )->additional(\$this->getAdditionals(\$filters, new {$moduleSingular}())));\n\n";
        $classContent .= "        \$content = \$responses->getContent();\n";
        $classContent .= "        return json_decode(\$content, true);\n";
        $classContent .= "    }\n\n";

        // Excel CSV function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Filter \$filters\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @return JsonResponse\n";
        $classContent .= "     * @throws BindingResolutionException\n";
        $classContent .= "     * @throws \OpenSpout\Common\Exception\IOException\n";
        $classContent .= "     * @throws \OpenSpout\Common\Exception\InvalidArgumentException\n";
        $classContent .= "     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException\n";
        $classContent .= "     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException\n";
        $classContent .= "     */\n";
        $classContent .= "    public function excelCsv(Filter \$filters, Request \$request): JsonResponse\n";
        $classContent .= "    {\n";
        $classContent .= "        \$query = \$this->dataList(\$filters, \$request);\n";
        $classContent .= "        \$list = collect([]);\n";
        $classContent .= "        \$fileType = \$request->input('type') == 'xlsx' ? 'xlsx' : 'csv';\n";
        $classContent .= "        \$TDS = {$moduleSingular}::VIEW_MANAGE['tds'];\n";
        $classContent .= "        foreach (\$query['data'] as \${$moduleSingular}) {\n";
        $classContent .= "            \$exportData = [];\n";
        $classContent .= "            foreach (\$TDS as \$field) {\n";
        $classContent .= "                \$fName = \$field['fName'];\n";
        $classContent .= "                if (isset(\${$moduleSingular}[\$fName])) {\n";
        $classContent .= "                    \$exportData[\$fName] = \${$moduleSingular}[\$fName];\n";
        $classContent .= "                }\n";
        $classContent .= "            }\n";
        $classContent .= "            \$list->push(\$exportData);\n";
        $classContent .= "        }\n";
        $classContent .= "        \$link = ExcelCsv(\$list, \$request, '.' . \$fileType);\n\n";
        $classContent .= "        return success(asset(\$link), []);\n";
        $classContent .= "    }\n\n";

        // Create function
        $classContent .= "    /**\n";
        $classContent .= "     * @return View\n";
        $classContent .= "     */\n";
        $classContent .= "    public function create(): View\n";
        $classContent .= "    {\n";
        $classContent .= "        \$viewManage = {$moduleSingular}::VIEW_MANAGE;\n";
        $classContent .= "        return view('panel.template.form', compact('viewManage'));\n";
        $classContent .= "    }\n\n";

        // Store function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Store \$request\n";
        $classContent .= "     * @return JsonResponse\n";
        $classContent .= "     */\n";
        $classContent .= "    public function store(Store \$request): JsonResponse\n";
        $classContent .= "    {\n";
        $classContent .= "        \$array = \$request->validated();\n";
        $classContent .= "        {$moduleSingular}::create(\$array);\n\n";
        $classContent .= "        return success('Der Datensatz wurde erfolgreich registriert!', []);\n";
        $classContent .= "    }\n\n";

        // Edit function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Edit \$request\n";
        $classContent .= "     * @param int \$id\n";
        $classContent .= "     * @return View\n";
        $classContent .= "     */\n";
        $classContent .= "    public function edit(Edit \$request, int \$id): View\n";
        $classContent .= "    {\n";
        $classContent .= "        \$viewManage = {$moduleSingular}::VIEW_MANAGE;\n";
        $classContent .= "        \$resource = \$this->repository->show(\$id);\n";
        $classContent .= "        return view('panel.template.form', compact('viewManage', 'resource', 'id'));\n";
        $classContent .= "    }\n\n";

        // Update function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Update \$request\n";
        $classContent .= "     * @param int \$id\n";
        $classContent .= "     * @return JsonResponse\n";
        $classContent .= "     */\n";
        $classContent .= "    public function update(Update \$request, int \$id): JsonResponse\n";
        $classContent .= "    {\n";
        $classContent .= "        \$this->repository->update(\$this->repository->show(\$id), \$request->validated());\n";
        $classContent .= "        return success('Der Datensatz wurde erfolgreich registriert!', []);\n";
        $classContent .= "    }\n\n";

        // Destroy function
        $classContent .= "    /**\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @param int \$id\n";
        $classContent .= "     * @return JsonResponse\n";
        $classContent .= "     */\n";
        $classContent .= "    public function destroy(Request \$request, int \$id): JsonResponse\n";
        $classContent .= "    {\n";
        $classContent .= "        \$this->repository->delete(\$id);\n";
        $classContent .= "        return success('Der Löschvorgang war erfolgreich', []);\n";
        $classContent .= "    }\n";

        $classContent .= "}\n";

        return $classContent;
    }

    protected function createResources($moduleSingular, $modulePlural, $fields, $path): void
    {
        $fileName = "{$moduleSingular}Resource.php";
        $filePath = "{$path}/{$fileName}";
        $fields = json_decode($fields[0], true);
        $stub = $this->generateResourceClass($moduleSingular, $modulePlural, $fields);
        file_put_contents($filePath, $stub);
    }
    protected function generateResourceClass($moduleSingular, $modulePlural, $fields): string
    {
        $classContent = "<?php\n\nnamespace App\Http\Resources\\$moduleSingular;\n\n";
        $classContent .= "use Illuminate\Http\Request;\n";
        $classContent .= "use Illuminate\Http\Resources\Json\JsonResource;\n\n";
        $classContent .= "class {$moduleSingular}Resource extends JsonResource\n{\n";
        $classContent .= "    /**\n";
        $classContent .= "     * Transform the resource into an array.\n";
        $classContent .= "     *\n";
        $classContent .= "     * @param Request \$request\n";
        $classContent .= "     * @return array\n";
        $classContent .= "     */\n";
        $classContent .= "    public function toArray(\$request)\n";
        $classContent .= "    {\n";
        $classContent .= "        return [\n";
        $classContent .= "            'id' => \$this->id,\n";

        // Adding fields dynamically
        foreach ($fields as $field) {
            $classContent .= "            '{$field['name_field']}' => \$this->{$field['name_field']},\n";
        }

        $classContent .= "        ];\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        return $classContent;
    }

}

