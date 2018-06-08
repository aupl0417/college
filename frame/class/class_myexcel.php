<?php

/**
 * Description of class_myexcel.php
 * @author 钟俊均 (303198069@qq.com)
 * @datetime 2014-6-21  11:15:31
 */
class myexcel
{

    protected $template; //模板文件
    protected $_loader; //打开文件的实例
    protected $_writer; //写入实例
    protected $worksheet; //当前工作簿

    function __construct($template, $version = 0)
    {
        require_once 'include/lib/PHPExcel/PHPExcel.php';
        require_once 'include/lib/PHPExcel/PHPExcel/Reader/Excel5.php';
        include_once 'include/lib/PHPExcel/PHPExcel/IOFactory.php';
        switch ($version) {
            case 0:
                //97-2003版本的excel
                $PHPExcel = new PHPExcel_Reader_Excel5();
                $this->_loader = $PHPExcel->load($template);
                $this->_writer = new PHPExcel_Writer_Excel5($this->_loader);
                break;
            case 1:
                //2007版本的excel
                $PHPExcel = new PHPExcel_Reader_Excel2007();
                $this->_loader = $PHPExcel->load($template);
                $this->_writer = new PHPExcel_Writer_Excel2007($this->_loader);
                break;
        }
    }

    //读取工作簿
    function getWorksheet($pIndex = 0)
    {
        return $this->_loader->getSheet($pIndex);
    }

    //获取所有列数
    function getAllColCount()
    {
        return $this->getWorksheet()->getHighestColumn();
    }

    //获取所有行数
    function getAllRowCount()
    {
        return $this->getWorksheet()->getHighestRow();
    }

    //修改、写入单元格
    function setCell($col, $row, $value)
    {
        $address = $col . $row;
//        ob_start();
        $this->getWorksheet()->setCellValue($address, $value);
        return $this;
    }

    //读取行列数据
    function getCellByCR($col, $row)
    {
        $address = $col . $row;
        return $this->getWorksheet()->getCell($address)->getValue();
    }

    //遍历输出excel
    function read()
    {
        //TODO
    }

    //另存为
    function saveAs($file)
    {
        $this->_writer->save($file);
    }

}
