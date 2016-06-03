<?php
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
load()->func('tpl');
$article_sys = pdo_fetch("select * from" . tablename('sz_yi_article_sys') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
$article_sys['article_image'] = tomedia($article_sys['article_image']);

if ($article_sys['article_temp'] == 0) {
	$limit = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
	$articles = pdo_fetchall("SELECT id,article_title,resp_img,article_category,article_rule_credit,article_rule_money,article_date FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid order by article_date_v desc limit " . $limit, array(':uniacid' => $_W['uniacid']));

$member_levels = m('member')->getLevels();
$distributor_levels = p("commission")->getLevels();
	foreach ($articles as $key => &$row) {
		$category = pdo_fetch("SELECT * FROM " . tablename('sz_yi_article_category') . " WHERE uniacid=:uniacid and id = '" .$row['article_category']. "'", array(':uniacid' => $_W['uniacid']));
		$row['isread'] = false;
		if($category['m_level'] == 0)
		{
			$row['isread'] = true;
		}elseif($category['m_level'] == $member['level']){
			$row['isread'] = true;
		}
		if($member['isagent'] == 1)
		{
			if($category['d_level'] == 0)
			{
				$row['isread'] = true;
			}elseif($category['d_level'] == $member['agentlevel']){
				$row['isread'] = true;
			}
			
		}

		foreach ($member_levels as $key => $value) {
			if($category['m_level'] == $value['id'])
			{
				$row['m_message'] = "成为“".$value['levelname']."”等级的会员";
			}
		}

		if($category['d_level'] == 0)
		{
			$row['d_message'] = "成为分销商！";
		}else{
			foreach ($distributor_levels as $key => $value) {
				if($category['d_level'] == $value['id'])
				{
					$row['d_message'] = "成为“".$value['levelname']."”等级的分销商";
				}
			}
			
		}

	}
	unset($row);
	//echo "<pre>";print_r($articles);exit;
} elseif ($article_sys['article_temp'] == 1) {
	$limit = empty($article_sys['article_shownum']) ? '7' : $article_sys['article_shownum'];
	$articles = pdo_fetchall("SELECT distinct article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid order by article_date_v desc limit " . $limit, array(':uniacid' => $_W['uniacid']), 'article_date_v');
	foreach ($articles as &$a) {
		$a['articles'] = pdo_fetchall("SELECT id,article_title,article_date_v,resp_img,resp_desc,article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid and article_date_v=:article_date_v order by article_date desc ", array(':uniacid' => $_W['uniacid'], ':article_date_v' => $a['article_date_v']));
	}
	unset($a);
} elseif ($article_sys['article_temp'] == 2) {
	$categorys = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_article_category') . " WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
}


include $this->template('list');
