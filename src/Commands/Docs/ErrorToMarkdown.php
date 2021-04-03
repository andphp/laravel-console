<?php

/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2021/4/3
 * Time: 1:57 PM
 */

namespace AndPHP\Console\Commands\Docs;


use App\Constant\Error;
use Illuminate\Support\Facades\File;

class ErrorToMarkdown extends MarkdownConfig
{
    public function makeMarkdown()
    {
        $path = 'dictionary/error';
        $output_path = app_path() . '/ApiDocs/Markdown';
        $this->configSetting('数据字典',$path,['数据字典','错误码']);

        $this->htag(3,'错误码字典');
        $error = new Error();
        $this->echoTable($error->getKeyValue());

        # 获取缓冲区内容，写入文件
        $contents = ob_get_clean();
        File::isDirectory($output_path.'/dictionary') or File::makeDirectory($output_path.'/dictionary', 0777, true, true);
        file_put_contents($output_path . '/' . $path . '.md', $contents);

        echo "错误码 转换完成".PHP_EOL;
    }

    protected function echoTable($tableAllColumn)
    {
        echo PHP_EOL . '|错误码|错误值|错误信息|' . PHP_EOL;
        echo '|--|--|--|' . PHP_EOL;
        foreach ($tableAllColumn as $colum){
            echo vsprintf('|%s|%s|%s|' . PHP_EOL, $colum);
        }
    }
}