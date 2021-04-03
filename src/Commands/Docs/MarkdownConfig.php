<?php

/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2021/4/3
 * Time: 11:56 AM
 */

namespace AndPHP\Console\Commands\Docs;


class MarkdownConfig
{

    public function __construct()
    {
        // 开启缓冲区
        ob_start();

    }

    protected function configSetting($title,$path,array $categories)
    {
        # 输出title
        $this->echoTitle($title);
        $this->echoUrlName($path);
        # 输出分组
        $this->echoCategories($categories);
    }

    public function echoTitle($title)
    {
        echo '---' . PHP_EOL;
        echo 'title: ' . $title . PHP_EOL;
    }

    public function echoUrlName($path)
    {
        echo 'url_name: ' . $path . PHP_EOL;
        echo 'sticky: 1' . PHP_EOL;
        echo 'cover: false' . PHP_EOL;
        echo 'date: ' . now()->toDateTimeString() . PHP_EOL;
    }

    public function echoCategories($categories)
    {
        echo 'categories: ' . PHP_EOL;
        foreach ($categories as $item) {
            echo '- ' . $item . PHP_EOL;
        }
        echo '---' . PHP_EOL . PHP_EOL;
    }

    public function htag(int $num, $title)
    {
        return PHP_EOL . str_repeat('#', $num) . ' ' . $title . PHP_EOL;
    }
}