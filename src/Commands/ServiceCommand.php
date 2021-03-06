<?php

namespace AndPHP\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServiceCommand extends Command
{
    protected $files;

    protected $type = 'Service';

    /**
     * The name and signature of the console command. 控制台命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'andphp:service {name} {--extend=}';

    /**
     * The console command description. 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Create a new command instance. 创建一个新的命令实例
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }

    /**
     * Execute the console command. 执行控制台命令
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type . ' created successfully.');
    }

    /**
     * Get the stub file for the generator. 获取生成器模板文件
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = $stub ?? '/Stubs/service.plain.stub';

        return __DIR__ . $stub;
    }

    /**
     * Get the default namespace for the class. 获取该类的默认名称空间
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Services';
    }

    /**
     * * Build the class with the given name. 使用给定的名称构建类
     *
     * Remove the base service import if we are already in base namespace. 如果我们已经在基命名空间中，则删除基控制器导入
     *
     * @param $name
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $serviceNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$serviceNamespace}\Service;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), $this->buildClassParent($name)
        );
    }

    /**
     * Build the class with the given name. 使用给定的名称构建类
     *
     * @param $name
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClassParent($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name)
            ->replaceExtendClass($stub, $this->option('extend'));
    }

    /**
     * Get the console command options. 获取控制台命令选项
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'extend',
                'i',
                InputOption::VALUE_NONE,
                'Generate a resource service class.'
            ],
        ];
    }

    /**
     * Parse the class name and format according to the root namespace.  根据根命名空间解析类名和格式
     *
     * @param  string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }

    /**
     * Determine if the class already exists.  确定类是否已经存在
     *
     * @param  string $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the destination class path. 获取目标类路径
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Build the directory for the class if necessary. 如果需要，为类构建目录
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Replace the namespace for the given stub. Replace the namespace for the given stub
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            [
                'DummyNamespace',
                'DummyRootNamespace'
            ],
            [
                $this->getNamespace($name),
                $this->rootNamespace()
            ],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name. 获取给定类的完整名称空间，但不包含类名
     *
     * @param  string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub. 替换给定存根的类名
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceClass(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace('DummyClass', $class, $stub);

        return $this;
    }

    /**
     * 自定义继承 引用
     * @param $stub
     * @param $name
     * @return mixed
     */
    protected function replaceExtendClass($stub, $Extend)
    {
        $class = str_replace($this->getNamespace($Extend) . '\\', '', $Extend);

        $use = count(explode('\\', $Extend)) >= 2 ? "" : 'use ' . $this->qualifyClass($Extend) . "Service;";

        $stub = str_replace([
            'DummyUseNamespace',
            'DummyExtendClass'
        ], [
            $use,
            $class
        ], $stub);
        return $stub;
    }

    /**
     * Get the desired class name from the input. 从输入中获取所需的类名
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the root namespace for the class. 获取类的根名称空间
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }


    /**
     * Get the console command arguments. 获取控制台命令参数
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the class'
            ],
        ];
    }
}
