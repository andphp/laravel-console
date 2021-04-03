<?php

namespace AndPHP\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'andphp:docs {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data conversion from postman JSON format to markdown format';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        switch ($this->getNameInput()) {
            case 'postman':
                $files = $this->getDir(app_path() . '/ApiDocs/PostmanJson');
                $post = new \AndPHP\Console\Commands\Docs\PostmanToMarkdown();
                foreach ($files as $fileName) {
                    $post->getMarkdownDocument($fileName, app_path() . '/ApiDocs/Markdown');
                    $this->info($fileName . ' created successfully.');
                }
                break;
            case 'mysql':
                $mysql = new \AndPHP\Console\Commands\Docs\MysqlToMarkdown();
                $mysql->makeMarkdown();
                break;
            case 'error':
                $mysql = new \AndPHP\Console\Commands\Docs\ErrorToMarkdown();
                $mysql->makeMarkdown();
                break;
            case 'html':
                echo "同步加载 markdown 文件 》》" . PHP_EOL;
                $markdown = app_path() . '/ApiDocs/Markdown';
                $source = __DIR__ . '/Docs/hexo_d/source/_posts';
                if (PHP_OS == "WINNT") {
                    shell_exec('xcopy "' . $markdown . '" "' . $source . '" /s /y');
                } elseif (PHP_OS == "Darwin") {
                    shell_exec('cp -R "' . $markdown . '"/ "' . $source . '"');
                } else {
                    echo "同步加载 markdown 文件 》》 失败" . PHP_EOL;
                    return false;
                }
                // hexo g
                echo "构建 html 文件 》》" . PHP_EOL;
                shell_exec("cd " . __DIR__ . "/Docs/hexo_d && hexo clean && hexo g");
                echo "构建 html 文件 》》 成功" . PHP_EOL;
                // cp public to public
                echo "导出 html 文件 》》" . PHP_EOL;

                $docs = public_path() . '/docs';
                $public = __DIR__ . '/Docs/hexo_d/public';
                File::isDirectory($docs) or File::makeDirectory($docs, 0777, true, true);
                if (PHP_OS == "WINNT") {
                    shell_exec('xcopy "' . $public . '" "' . $docs . '" /s /y');
                } elseif (PHP_OS == "Darwin") {
                    shell_exec('cp -R "' . $public . '"/ "' . $docs . '"');
                } else {
                    echo "导出 html 文件 》》 失败" . PHP_EOL;
                    return false;
                }

                echo "======= 执行完成 =======" . PHP_EOL;
                break;
            default:
                echo "指令不存在";
                break;
        }


    }

    /**
     * Write a string as information output.
     *
     * @param  string $string
     * @param  int|string|null $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param  string $string
     * @param  string|null $style
     * @param  int|string|null $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    protected function getDir($dir)
    {
        $files = array();
        $this->searchDir($dir, $files);
        return $files;
    }

    protected function searchDir($path, &$files)
    {

        if (is_dir($path)) {

            $opendir = opendir($path);

            while ($file = readdir($opendir)) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $files);
                }
            }
            closedir($opendir);
        }
        if (!is_dir($path)) {
            $files[] = $path;
        }
    }

    /**
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }
}
