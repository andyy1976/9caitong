<?php
class deal_cate
{
	public function index(){
		$root = get_baseroot();
		$deal_cates=$GLOBALS['db']->getAll("select id,name,sort from ".DB_PREFIX."deal_loan_type where is_effect = 1 and is_delete = 0 ");

		foreach($deal_cates as $k=>$v){
            $sort[] = $deal_cates[$k]['sort'];
        }
        array_multisort($sort,SORT_ASC,$deal_cates);
        $root['deal_cate'] = $deal_cates; //项目分类


        $month =array(
			'0' => array('mid' => '0','month' => '全部'),
			'1' => array('mid' => '1','month' => '1个月'),
			'2' => array('mid' => '3','month' => '3个月'),
			'3' => array('mid' => '6','month' => '6个月'),
			'4' => array('mid' => '12','month' => '12个月'),
		);
		$root['month'] = $month;
		$root['response_code'] = 1;	
		$root['program_title'] = "出借分类";
		output($root);		
	}
}
?>
