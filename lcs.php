<?php
class LCS{
	private $result = '';
	private $tableC = array();
	public function getResult(){
		return $this->result;
	}
	public function getMap(){
		return $this->tableC;
	}
	public function LCSLENGTH($a,$b){
		$alen = strlen($a);
		$blen = strlen($b);
		$this->tableC[0][0]['length'] = 0;
		for($i=1;$i<=$alen;$i++){
			$this->tableC[$i][0]['length'] = 0;
			$this->tableC[$i][0]['dir'] = -1;
		}
		for($j=1;$j<=$blen;$j++){
			$this->tableC[0][$j]['length'] = 0;
			$this->tableC[0][$j]['dir'] = -1;
		}
		//init the log table
		//start to count the LCS length
		for($i=1;$i<=$alen;$i++){
			for($j=1;$j<=$blen;$j++){
				if($this->getChar($a,$i-1) == $this->getChar($b,$j-1)){
					$this->tableC[$i][$j]['length'] = $this->tableC[$i-1][$j-1]['length']+1;
					$this->tableC[$i][$j]['dir'] = 2; //left:3;up:1;up-left:2
				}
				else if($this->tableC[$i-1][$j]['length'] >= $this->tableC[$i][$j-1]['length']){
					$this->tableC[$i][$j]['length'] = $this->tableC[$i-1][$j]['length'];
					$this->tableC[$i][$j]['dir'] = 1;
				}
				else{
					$this->tableC[$i][$j]['length'] = $this->tableC[$i][$j-1]['length'];
					$this->tableC[$i][$j]['dir'] = 3;
				}
			}
		}
	}
	private function getChar($str,$pos){
		return substr($str,$pos,1);
	}
	public function getLCS($str,$alen,$blen){
		if($alen == 0 || $blen == 0){
		//echo $result
		}
		if( !empty($this->tableC[$alen][$blen]['dir']) && $this->tableC[$alen][$blen]['dir'] == 2 ){
			$this->getLCS($str,$alen-1,$blen-1);
			$this->result .= $this->getChar($str,$alen-1);
			echo $this->getChar($str,$alen-1);
		}
		else if(!empty($this->tableC[$alen][$blen]['dir']) && $this->tableC[$alen][$blen]['dir'] == 1){
			$this->getLCS($str,$alen-1,$blen);
		}
		else if(!empty($this->tableC[$alen][$blen]['dir']) && $this->tableC[$alen][$blen]['dir'] == 3){
			$this->getLCS($str,$alen,$blen-1);
		}
		else{
			return;
		}
	}
}
//the area of using the class of LCS
$strA = '10010101';
$strB = '010110110';
//print_r($map);
$a = strlen($strA);
$b = strlen($strB);
$lcs = new LCS();
$lcs->LCSLENGTH($strA,$strB);
//print_r($lcs->getMap());die;
$lcs->getLCS($strA,$a,$b);
$result = $lcs->getResult();
echo "<br>the result is ".$result;
?>