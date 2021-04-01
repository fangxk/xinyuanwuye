<?php

namespace addons\xshop;

use \think\Db;
use \think\Exception;

class Sql
{
    protected $logFile = 'install.log';
    public static function instance()
    {
        return new self;
    }

    /**
     * 执行sql
     * @param String $addon 插件目录
     * @param String $sql_dir 执行此目录下所有sql文件
     * @param Integer $model 1:表示只执行一次，0：表示可以重复执行
     */
    public function exec($addon = 'xshop', $sql_dir = 'sql', $mode = 1)
    {
        $dirPath = ADDON_PATH . $addon . DS . $sql_dir;
        $this->addon = $addon;
        $this->sql_dir = $sql_dir;
        $files = $this->getInstallFiles();
        $success_files = [];
        $error_files = [];
        cache('xshop_install_sql_err', null);
        foreach ($files as $v) {
            try {
                $arr = parse_ini_file($dirPath . DS . $v, true);
            } catch (\Exception $e) {
                $arr = [];
            }
            if (!empty($arr)) {
                foreach ($arr as $item) {
                    if (!empty($item['IF'])) {
                        try {
                            $_arr = explode(';', $item['IF']);
                            if (empty($_arr)) {
                                throw new Exception("格式错误");
                            }
                            $sql = $_arr[0];
                            $data = Db::getPdo()->query($this->parseSql($sql))->fetch();
                            $data = ['data' => $data];
                            $rule = isset($_arr[1]) ? $_arr[1] : '';
                            $rule = ['data' => $rule];
                            if (empty($item['THEN'])) {
                                throw new Exception("格式错误");
                            }
                            $validate = new \think\Validate($rule);
                            if ($validate->check($data)) {
                                Db::getPdo()->exec($this->parseSql($item['THEN']));
                                if (!in_array($v, $success_files)) {
                                    $success_files[] = $v;
                                }
                            }
                        } catch (\PdoException $e) {
                            throw new Exception($e->getMessage());
                        } catch (Exception $e) {
                            throw $e;
                        } catch (\Exception $e) {
                            throw new Exception($e->getMessage());
                        }
                    }
                }
            } else {
                $filepath = $dirPath . DS . $v;
                $lines = file($filepath);
                $templine = '';
                
                $errSqls = [];
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                        continue;

                    $templine .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                        $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                        try {
                            Db::getPdo()->exec($templine);
                            if (!in_array($v, $success_files)) {
                                $success_files[] = $v;
                            }
                        } catch (\PDOException $e) {
                            $errSqls[] = ['file' => $filepath, 'sql' => $templine, 'err_msg' => $e->getMessage()];
                        }
                        $templine = '';
                    }
                }
                if (!empty($errSqls)) $error_files[] = [
                    'addon' => $this->addon,
                    'name' => $v,
                    'dir' => $sql_dir,
                    'err_sqls' => $errSqls
                ];
            }
        }
        if (!empty($error_files)) {
            cache('xshop_install_sql_err', $error_files);
            \think\Log::error(['tag' => 'xshop_install_sql_err_tag', 'data' => $error_files]);
        }
        if ($mode) {
            $data = [];
            $now = time();
            foreach ($success_files as $file) {
                $data[] = [
                    'addon' => $this->addon,
                    'name' => $file,
                    'dir' => $sql_dir,
                    'create_time' => $now
                ];
            }
            Db::table("__XSHOP_UPGRADE_SQL__")->insertAll($data);
        }
        
        return true;
    }

    public function parseSql($sql)
    {
        $parseArr = [
            '__PREFIX__' => config('database.prefix'),
            '__DATABASE__' => config('database.database')
        ];
        foreach ($parseArr as $k => $v) {
            $sql = str_replace($k, $v, $sql);
        }
        return $sql;
    }


    public function getInstallFiles()
    {
        $sqlFiles = $this->getSqlFiles();
        $installedFiles = Db::table('__XSHOP_UPGRADE_SQL__')->where('addon', $this->addon)->where('dir', $this->sql_dir)->column('name');
        return array_diff($sqlFiles, $installedFiles);
    }

    public function getSqlFiles()
    {
        $dirPath = ADDON_PATH . $this->addon . DS . $this->sql_dir;
        $files = scandir($dirPath);
        $res = [];
        foreach ($files as $v) {
            if (strtoupper(substr($v, -4, 4)) == '.SQL') {
                $res[] = $v;
            }
        }
        $this->sqlFiles = $res;
        return $res;
    }
}
