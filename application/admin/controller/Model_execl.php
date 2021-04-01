<?php
namespace app\admin\controller;
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/21 下午9:07
 */
class Model_execl
{
    static function column_str($key)
    {
        $array = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ',
            'BA',
            'BB',
            'BC',
            'BD',
            'BE',
            'BF',
            'BG',
            'BH',
            'BI',
            'BJ',
            'BK',
            'BL',
            'BM',
            'BN',
            'BO',
            'BP',
            'BQ',
            'BR',
            'BS',
            'BT',
            'BU',
            'BV',
            'BW',
            'BX',
            'BY',
            'BZ',
            'CA',
            'CB',
            'CC',
            'CD',
            'CE',
            'CF',
            'CG',
            'CH',
            'CI',
            'CJ',
            'CK',
            'CL',
            'CM',
            'CN',
            'CO',
            'CP',
            'CQ',
            'CR',
            'CS',
            'CT',
            'CU',
            'CV',
            'CW',
            'CX',
            'CY',
            'CZ'
        );
        return $array[$key];
    }
    static function column($key, $columnnum = 1)
    {
        return self::column_str($key) . $columnnum;
    }
    static function export($list, $params = array())
    {
        if (PHP_SAPI == 'cli') {
            die('This example should only be run from a Web Browser');
        }
        require_once ROOT_PATH.'vendor/phpexcel/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator("小区秘书")->setLastModifiedBy("小区秘书")->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document")->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("report file");
        $sheet  = $excel->setActiveSheetIndex(0);
        $rownum = 1;

        foreach ($params['columns'] as $key => $column) {
            $sheet->setCellValue(self::column($key, $rownum), $column['title']);
            if (!empty($column['width'])) {
                $sheet->getColumnDimension(self::column_str($key))->setWidth($column['width']);
            }
        }
        $rownum++;
        foreach ($list as $row) {
            $len = count($row);
            for ($i = 0; $i < $len; $i++) {
                $value = $row[$params['columns'][$i]['field']];
                $sheet->setCellValue(self::column($i, $rownum), $value);
            }
            $rownum++;
        }
        $excel->getActiveSheet()->setTitle($params['title']);
        $filename = urlencode($params['title']);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        //$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

}