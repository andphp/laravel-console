<?php

/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2021/4/3
 * Time: 11:51 AM
 */

namespace AndPHP\Console\Commands\Docs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MysqlToMarkdown extends MarkdownConfig
{

    public function makeMarkdown()
    {
        $path = 'dictionary/database';
        $output_path = app_path() . '/ApiDocs/Markdown';
        $this->configSetting('数据字典-数据库', $path, [
            '数据字典',
            '数据库'
        ]);

        $databaseName = $this->getDatabaseName();
        // 查询数据库，获取所有数据表
        $selectTableSql = "SELECT `table_name`,`table_comment` FROM information_schema.TABLES WHERE `TABLE_SCHEMA` = '" . $databaseName . "'";
        $tables = json_decode(json_encode(DB::select($selectTableSql)), true);

        foreach ($tables as $table) {
            $table = array_change_key_case($table,CASE_UPPER);
            $sql = "SELECT
COLUMN_NAME,
DATA_TYPE,
    COLUMN_COMMENT,IS_NULLABLE,COLUMN_DEFAULT
FROM
    INFORMATION_SCHEMA. COLUMNS WHERE table_schema = '" . $databaseName . "' AND  table_name = '" . $table['TABLE_NAME'] . "'";
            $CommentsArray = json_decode(json_encode(DB::select($sql)), true);
            $tableDesc = $table['TABLE_COMMENT'] ? '【' . $table['TABLE_COMMENT'] . '】' : '';
            echo $this->htag(3, $table['TABLE_NAME'] . $tableDesc);
            $this->echoTable($CommentsArray);
        }
        # 获取缓冲区内容，写入文件
        $contents = ob_get_clean();
        File::isDirectory($output_path . '/dictionary') or File::makeDirectory($output_path . '/dictionary', 0777, true, true);
        file_put_contents($output_path . '/' . $path . '.md', $contents);
        echo "数据字典 已转换成功" . PHP_EOL;
    }

    protected function getDatabaseName()
    {
        return config('database.connections.mysql.database');
    }

    protected function echoTable($tableAllColumn)
    {
        echo PHP_EOL . '|字段名|类型|描述|允许空|默认值|' . PHP_EOL;
        echo '|--|--|--|--|--|' . PHP_EOL;
        foreach ($tableAllColumn as $colum) {
            echo vsprintf('|%s|%s|%s|%s|%s|' . PHP_EOL, $colum);
        }
    }
}