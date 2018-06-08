<?php
include_once(FRAMEROOT."/lib/mpdf/mpdf.php");

/**
 *
 * 发票
 *
 * @author adadsa
 *
 * @time 2016-09-23
 *

 */
class invoice {
	
    private $error;
    private $db;
    private $id;
	private $html;
	private $tempPath;

    public function __construct($db = NULL) {       
       $this->db = is_null($db) ? new MySql() : $db;
       $this->tempPath = 'app/public/template/cn/print/';
    }

 	/* 添加
	*return ;
	*/
	public function add($data, $replace){
		$this->createID();		
		$data['inv_id'] = $this->id;
		$data['inv_createTime'] = F::mytime();
		
		
		$replace['No'] = $this->id;
		$replace['date'] = F::mytime('Y 年  m  月  d 日');
		$this->createHtml($replace);
		
		$data['inv_html']		= $this->html;
		$result = $this->db->insert('t_invoice', $data);
/* 		echo $result;
		print_r($data);die; */
		return ( $result == 1);
	}
	
	/* 打印 */
	public function pdfPrint($id){
		$inv = $this->db->getRow("SELECT * FROM `t_invoice` WHERE inv_id='".$id."'");
		if(!$inv){
			return false;
		}else{
			if(in_array($inv['inv_type'], [1, 2])){
				$pageSize = [205, 140];
			}else{
				$pageSize = [205, 140];
			}
			
			$titleArray = [
				'1' => '会员充值凭证',
				'2' => '会员服务费'
			];
			$title = $titleArray[$inv['inv_type']];
			
			
			$html = $inv['inv_html'];
			$mpdf = new mPDF('-aCJK', $pageSize, '', '', 0, 0, 0, 0);
			$mpdf->SetDisplayMode('fullpage');
			$stylesheet = file_get_contents(APPROOT . '/template/' . LANGUAGE . '/print/style/table1.css');
			//echo $stylesheet;die;
			$mpdf->WriteHTML($stylesheet,1);
			
			// LOAD a stylesheet
			//$stylesheet = file_get_contents('TianX-pc-xy/css/css.css');
			
			
			
			//$mpdf->WriteHTML($stylesheet, 1);	// The parameter 1 tells that this is css/style only and no body/html/text
			$mpdf->SetTitle($title);
			$mpdf->SetAuthor('大唐天下');// - Set document Author in metadata
			$mpdf->SetCreator('大唐天下');// - Set document Creator in metadata
			$mpdf->SetSubject($title);// - Set document Subject in metadata
			$mpdf->SetKeywords($title);// - Set document Keywords in metadata
			
			//$mpdf->SetWatermarkImage('TianX-pc-xy/images/shuiy-bg.png', 1, '', array(30, 0));
			//$mpdf->showWatermarkImage = true;
			//$mpdf->SetFilename('kkk');// - Set document Keywords in metadata
			$mpdf->autoLangToFont = true;
			
			$mpdf->WriteHTML($html);
			//$mpdf->Output();
			$mpdf->Output($id.'.pdf','D');			
		}
		
	}
	
	
	private function createID(){
		$maxID = $this->db->getField("SELECT * FROM `t_invoice` ORDER BY inv_createTime DESC LIMIT 1");
		if(empty($maxID)){
			$maxID = 0;
		}else{
			$maxID = $maxID - 0;
		}
		
		$maxID = substr((100000001 + $maxID).'', 1);
		$this->id = $maxID;		
	}
	
	private function convertHtml(){	
		$this->html = trim(preg_replace('/\\\u([0-9a-f]{4})/i', '&#x${1};', json_encode($this->html)), '"');
		//$str = preg_replace('/<\\\/i', '<', $str);
		$this->html = preg_replace('/\\\"/i', '"', $this->html);
		$this->html = preg_replace('/\\\r\\\n/i', '', $this->html);
		$this->html = preg_replace('/\\\n/i', '', $this->html);	
		$this->html = preg_replace('/\\\t/i', '', $this->html);	
		$this->html = preg_replace('/\\\\\//i', '/', $this->html);
	}
	
	private function createHtml($data){
		$temp = new myHTML();
		$this->html = file_get_contents($this->tempPath.'invoice-'.$data['type'].'.html');
		$replace = [
			'_replace' => $data
		];
		
		$this->html = $temp->getHTML($this->html, $replace);
		$this->convertHtml();
		
	}
	
    public function getError(){
        return $this->error;
    }
	
    public function getID(){
        return $this->id;
    }
}
