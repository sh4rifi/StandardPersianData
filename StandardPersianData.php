<?php

namespace App\Http\Controllers;

use \DB;

class StandardPersianData extends Controller
{
    public $tables;
    public $except;
    public $database;


    public function __construct($tables = null, $except = ['id', 'created_at', 'updated_at'])
    {
        $this->database = env("DB_DATABASE");
        $this->setTables($tables);
        $this->setExcept($except);
    }


    public function getTables()
    {
        return $this->tables;
    }


    public function setTables($tables = null): void
    {
        if ($tables) {
            $this->tables = $tables;
        } else {
            $tables_in_db = DB::select('SHOW TABLES');
            $str = "Tables_in_" . $this->database;
            foreach ($tables_in_db as $table) {
                $this->tables[] = $table->$str;
            }
        }

    }


    public function getExcept()
    {
        return $this->except;
    }


    public function setExcept($except): void
    {
        $this->except = $except;
    }

    public function index()
    {
        if ($this->makeTablesStandard())
            return "The table was successfully standardized for Iranian.";
    }


    public function makeTablesStandard()
    {
        foreach ($this->getTables() as $table)
            $this->makeTableStandard($table);
        return true;
    }


    public function makeTableStandard($table)
    {
        $except = $this->getExcept();
        $tableFields = DB::getSchemaBuilder()->getColumnListing($table);
        $targetFields = array_diff($tableFields, $except);
        $rows = DB::table($table)->get();
        foreach ($rows as $row) {
            foreach ($targetFields as $field)
                $newRow[$field] = $this->convertChars($row->$field);

            DB::table($table)->where('id', $row->id)->update($newRow);
            $newRow = null;

        }
        return true;
    }


    public function convertChars($string)
    {
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩‎', 'ك', 'ي');
        $standard = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'ک', 'ی');
        return str_replace($persian, $standard, str_replace($arabic, $standard, $string));
    }


    public function strip()
    {
        $strip = function ($str) {
            $str = strip_tags($str);
            $str = trim($str);
            $str = strtolower($str);
            $str = preg_replace('/_/', '', $str);
            return $str;
        };
        $items = array_map($strip, $this->items);
        return $items;
    }


}
