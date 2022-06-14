<?php

namespace TypiCMS\Modules\Details\Commands;

use Illuminate\Console\Command;
use League\Flysystem\Visibility;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class CreateDetails extends Command
{
    /**
     * Source files path
     *
     * @var string
     *
     */
    protected $from_path;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name of the module, pluralized and ucfirsted.
     *
     * @var string
     */
    protected $module;

    /**
     * The name of the module, pluralized and ucfirsted.
     *
     * @var string
     */
    protected $details;

    /**
     * Helper collection with words in singular and plural.
     *
     * @var object
     */
    protected $names;

    /**
     * The object search array.
     *
     * @var array
     */
    protected $search = [
        'objects',
        'object',
        'Objects',
        'Object',

        'details',
        'detail',
        'Details',
        'Detail',
    ];

    /**
     * The replace array.
     *
     * @var array
     */
    protected $replace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'typicms:create:details
                {module : The module where you want to add details}
                {details : The details that you want to create}
                {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a details for existing module.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->from_path = config('details.stub_path');

        $style = new OutputFormatterStyle('red', null, ['bold']);
        $this->output->getFormatter()->setStyle('warning', $style);
        $style = new OutputFormatterStyle('cyan');
        $this->output->getFormatter()->setStyle('good', $style);

        if (!preg_match('/^[a-z]+$/i', $this->argument('module'))) {
            return $this->error('Only alphabetic characters are allowed module.');
        }

        $this->module = Str::plural(mb_ucfirst(mb_strtolower($this->argument('module'))));

        if (!preg_match('/^[a-z]+$/i', $this->argument('details'))) {
            return $this->error('Only alphabetic characters are allowed for details.');
        }

        $this->details = Str::plural(mb_ucfirst(mb_strtolower($this->argument('details'))));

        $this->prepareNames();

        $this->replace = [
            $this->names->modules,
            $this->names->module,
            $this->names->Modules,
            $this->names->Module,

            $this->names->details,
            $this->names->detail,
            $this->names->Details,
            $this->names->Detail,
        ];

        if (!$this->moduleExists()) {
            return $this->error('A module named [' . $this->module . '] does not exists or not published.');
        }
        if ($this->detailsExists()) {
            return $this->error('Details named [' . $this->details . '] already exists in module ' . $this->module . '.');
        }
        $this->publishModule();
        $this->publishViews();
        $this->publishScssFiles();
        $this->moveMigrationFile();
        $this->publishAdditionalTranslations();
        $this->addTranslations();
        $this->deleteResourcesDirectory();

        $this->updateModuleServiceProvider();
        $this->updateRouteServiceProvider();
        $this->updateModel();


        $this->line('------------------');
        $this->line('<info>Details</info> <comment>' . $this->details . '</comment> <info>was added into</info> <comment>/Modules/' . $this->module . '</comment><info>, customize it!</info>');
        $this->line('<info>Run the database migration with the command</info> <comment>php artisan migrate</comment><info>.</info>');
        $this->line('<info>Run</info> <comment>npm run dev</comment> <info>to finish.</info>');
        $this->line('<info>Use</info> <comment>@include(\'' . $this->names->modules . ':admin.' . $this->names->details . '._index\')</comment> '
            . '<info>to add <info>' . $this->names->details . '</info>> list to <info>' . $this->names->module . '</info> form.</info>');
        $this->line('------------------');

    }

    private function prepareNames()
    {
        $this->names = json_decode(json_encode([
            'modules' => mb_strtolower($this->module),
            'module' => Str::singular(mb_strtolower($this->module)),
            'Modules' => $this->module,
            'Module' => Str::singular($this->module),

            'details'  => mb_strtolower($this->details),
            'detail'  => Str::singular(mb_strtolower($this->details)),
            'Details'  => $this->details,
            'Detail'  => Str::singular($this->details),

            'ModuleDetails' => Str::singular($this->module) . $this->details,
            'ModuleDetail' => Str::singular($this->module) . Str::singular($this->details),
        ]));

    }

    /**
     * Generate the module details in Modules directory.
     */
    private function publishModule()
    {
        $from = base_path($this->from_path);
        $to = base_path('Modules/' . $this->module);

        if ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } else {
            $this->error("Can’t locate path: <{$from}>");
        }
    }

    /**
     * Publish views.
     */
    public function publishViews()
    {
        $from = base_path('Modules/' . $this->module . '/resources/views');
        $to = resource_path('views/vendor/' . $this->names->modules);
        $this->publishDirectory($from, $to);
    }

    /**
     * Publish scss files.
     */
    public function publishScssFiles()
    {
        $from = base_path('Modules/' . $this->module . '/resources/scss/public');
        $to = resource_path('scss/public');
        $this->publishDirectory($from, $to);
    }

    /**
     * Rename and move migration file.
     */
    public function moveMigrationFile()
    {
        $from = base_path('Modules/' . $this->module . '/database/migrations/create_' . $this->names->module . '_' . $this->names->details . '_table.php.stub');
        $to = getMigrationFileName('create_' . $this->names->module . '_' . $this->names->details . '_table');
        //dd($from, $to);

        $this->files->move($from, $to);
    }

    /**
     * Prepare additional translation files for details.
     */
    protected function publishAdditionalTranslations()
    {
        if(config('typicms.translations.details.stub')){
            $from = base_path(config('typicms.translations.details.stub'));
            $to = base_path('Modules/' . $this->module . '/resources/lang');

            if ($this->files->isDirectory($from)) {
                $this->publishDirectory($from, $to);
            } else {
                $this->error("Can’t locate path: <{$from}>");
            }
        }
    }

    /**
     * Add translations.
     */
    public function addTranslations()
    {
        $this->call('translations:add', ['path' => 'Modules/' . $this->module . '/resources/lang']);
    }

    /**
     * Delete resources directory.
     */
    public function deleteResourcesDirectory()
    {
        $this->files->deleteDirectory(base_path('Modules/' . $this->module . '/resources'));
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     */
    protected function publishDirectory($from, $to)
    {
        $visibility = PortableVisibilityConverter::fromArray([], Visibility::PUBLIC);

        $manager = new MountManager([
            'from' => new Flysystem(new LocalFilesystemAdapter($from)),
            'to' => new Flysystem(new LocalFilesystemAdapter($to, $visibility)),
        ]);

        foreach ($manager->listContents('from://', true) as $file) {
            $path = Str::after($file['path'], 'from://');
            if ($file['type'] === 'file' && (!$manager->fileExists('to://' . $path) || $this->option('force'))) {
                $content = str_replace($this->search, $this->replace, $manager->read($file['path']));
                $location = str_replace($this->search, $this->replace, $path);
                $manager->write('to://'. $location, $content);
            }
        }
    }

    /**
     * Update ModuleServiceProvider
     */
    protected function updateModuleServiceProvider()
    {
        $path = 'Modules/' . $this->module . '/Providers/ModuleServiceProvider.php';
        $filePath = base_path($path);
        $content = file_get_contents($filePath);

        $total_count = 0;

        $new_rows = 'use TypiCMS\Modules\\' . $this->module . '\Models\\' . $this->names->ModuleDetail . ';';
        $count = $this->addToContent($content,
            'use TypiCMS\Modules\\' . $this->module . '\Models\\' . $this->names->Module . ';', $new_rows
        );
        if (!$count) {
            $this->line('<warning>Use Model is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = 'use TypiCMS\Modules\\' . $this->module . '\Facades\\' . $this->names->ModuleDetails . ';';
        $count = $this->addToContent($content,
            'use TypiCMS\Modules\\' . $this->module . '\Facades\\' . $this->names->Modules . ';', $new_rows
        );
        if (!$count) {
            $this->line('<warning>Use Facade is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = "        \$this->mergeConfigFrom(__DIR__.'/../config/config-{$this->names->details}.php', 'typicms.{$this->names->module}_{$this->names->details}');";
        $count = $this->addToContent($content,
            "\$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'typicms.{$this->names->modules}');", $new_rows
        );
        if (!$count) {
            $this->line('<warning>Config is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = '        AliasLoader::getInstance()->alias(\'' . $this->names->ModuleDetails . '\', ' . $this->names->ModuleDetails . '::class);';
        $count = $this->addToContent($content,
            'AliasLoader::getInstance()->alias(\'' . $this->module . '\', ' . $this->module . '::class);', $new_rows
        );
        if (!$count) {
            $this->line('<warning>AliasLoader is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = '        ' . $this->names->ModuleDetail . '::observe(new SlugObserver());';
        $count = $this->addToContent($content,
            $this->names->Module . '::observe(new SlugObserver());', $new_rows
        );
        if (!$count) {
            $this->line('<warning>SlugObserver for facade is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = '        $this->app->bind(\'' . $this->names->ModuleDetails . '\', ' . $this->names->ModuleDetail . '::class);';
        $count = $this->addToContent($content,
            '$this->app->bind(\'' . $this->module . '\', ' . $this->names->Module . '::class);', $new_rows
        );
        if (!$count) {
            $this->line('<warning>Bind for facade is not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;


        if ($total_count > 0) {
            file_put_contents($filePath, $content);

            $this->line('<info>updated</info> <good>' . $path . '</good>');
        }
    }

    /**
     * Update RouteServiceProvider
     */
    protected function updateRouteServiceProvider()
    {
        $path = 'Modules/' . $this->module . '/Providers/RouteServiceProvider.php';
        $filePath = base_path($path);
        $content = file_get_contents($filePath);

        $total_count = 0;

        $new_rows = "use TypiCMS\Modules\\{$this->module}\Http\Controllers\\{$this->details}AdminController;" . PHP_EOL .
            "use TypiCMS\Modules\\{$this->module}\Http\Controllers\\{$this->details}ApiController;" . PHP_EOL .
            "use TypiCMS\Modules\\{$this->module}\Http\Controllers\\{$this->details}PublicController;";
        $count = $this->addToContent($content,
            "use TypiCMS\Modules\\{$this->module}\Http\Controllers\PublicController;",
            $new_rows
        );
        if (!$count) {
            $this->line('<warning>Use controllers are not added</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = <<<EOT
                        \$router->get('{slug}/{{$this->names->detail}Slug}', [{$this->details}PublicController::class, 'show'])->name('{$this->names->module}-{$this->names->detail}');
EOT;
        $count = $this->addToContent($content,
            "\$router->get('{slug}', [PublicController::class, 'show'])->name('{$this->names->module}');", $new_rows
        );
        if (!$count) {
            $this->line('<warning>Public routes are not updated</warning><info>, add manually to Public routes section in </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        $new_rows = <<<EOT

            \$router->get('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}/create', [{$this->details}AdminController::class, 'create'])->name('create-{$this->names->module}_{$this->names->detail}')->middleware('can:update {$this->names->modules}');
            \$router->get('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}/{{$this->names->detail}}/edit', [{$this->details}AdminController::class, 'edit'])->name('edit-{$this->names->module}_{$this->names->detail}')->middleware('can:update {$this->names->modules}');
            \$router->post('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}', [{$this->details}AdminController::class, 'store'])->name('store-{$this->names->module}_{$this->names->detail}')->middleware('can:update {$this->names->modules}');
            \$router->put('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}/{{$this->names->detail}}', [{$this->details}AdminController::class, 'update'])->name('update-{$this->names->module}_{$this->names->detail}')->middleware('can:update {$this->names->modules}');
EOT;
        $count = $this->addToContent($content,
            "\$router->put('{$this->names->modules}/{{$this->names->module}}', [AdminController::class, 'update'])->name('update-{$this->names->module}')->middleware('can:update {$this->names->modules}');", $new_rows
        );
        if (!$count) {
            $this->line('<warning>Admin routes are not updated</warning><info>, add manually to Admin routes section in </info><comment>' . $path . '</comment>');
            $this->line($new_rows);

        }
        $total_count += $count;

        $new_rows = <<<EOT

            \$router->get('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}', [{$this->details}ApiController::class, 'index'])->middleware('can:update {$this->names->modules}');
            \$router->patch('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}/{{$this->names->detail}}', [{$this->details}ApiController::class, 'updatePartial'])->middleware('can:update {$this->names->modules}');
            \$router->delete('{$this->names->modules}/{{$this->names->module}}/{$this->names->details}/{{$this->names->detail}}', [{$this->details}ApiController::class, 'destroy'])->middleware('can:update {$this->names->modules}');
EOT;

        $count = $this->addToContent($content,
            "\$router->delete('{$this->names->modules}/{{$this->names->module}}', [ApiController::class, 'destroy'])->middleware('can:delete {$this->names->modules}');", $new_rows
        );
        if (!$count) {
            $this->line('<warning>Api routes are not updated</warning><info>, add manually to Api routes section in </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }
        $total_count += $count;

        if ($total_count > 0) {
            file_put_contents($filePath, $content);

            $this->line('<info>updated</info> <good>' . $path . '</good>');
        }

    }

    /**
     * Update Object Model
     */

    protected function updateModel()
    {
        $path = "Modules/{$this->module}/Models/{$this->names->Module}.php";
        $filePath = base_path($path);
        $content = file_get_contents($filePath);

        $total_count = 0;

        $content = trim($content);

        $count = 0;
        $new_rows = <<<EOT

    public function {$this->names->details}()
    {
        return \$this->hasMany({$this->names->Module}{$this->names->Detail}::class, '{$this->names->module}_id');
    }
}
EOT;
        if (substr($content, -1) == '}') {
            $content = substr($content, 0, strlen($content) - 1);

            $content .= $new_rows;
            $count = 1;
            $total_count++;
        }
        if (!$count) {
            $this->line('<warning>Model is not updated</warning><info>, add manually to </info><comment>' . $path . '</comment>');
            $this->line($new_rows);
        }

        if ($total_count > 0) {
            file_put_contents($filePath, $content);

            $this->line('<info>updated</info> <good>' . $path . '</good>');
        }
    }

    /**
     * Helper function to add new text lines
     *
     * @param $content
     * @param $after
     * @param $text
     * @return int
     */
    private function addToContent(&$content, $after, $text)
    {
        $count = 0;
        $content = str_replace($after, $after . PHP_EOL . $text, $content, $count);
        return $count;
    }

    /**
     * Check if the module exists.
     *
     * @return bool
     */
    public function moduleExists()
    {
        $location1 = $this->files->isDirectory(base_path('Modules/' . $this->module));

        return $location1;
    }

    /**
     * Check if the details exists.
     *
     * @return bool
     */
    public function detailsExists()
    {
        return file_exists(base_path('Modules/' . $this->module . '/Models/' . Str::singular($this->module) . Str::singular($this->details) . '.php'));
    }
}