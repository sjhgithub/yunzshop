<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$apply_id = $_GPC['apply_id'];
$condition = ' and o.uniacid=:uniacid and o.status>=3';
if (!empty($apply_id)) {
    $apply_info = pdo_fetch("select * from " . tablename('sz_yi_supplier_apply') . " where uniacid={$_W['uniacid']} and id={$apply_id}");
    if (empty($apply_info['apply_ordergoods_ids'])) {
        $ap_id = $apply_info['id'] - 1;
        $ap_time = pdo_fetchcolumn("select apply_time from " . tablename('sz_yi_supplier_apply') . " where uniacid={$_W['uniacid']} and id={$ap_id}");
        if (empty($ap_time)) {
            $ap_time = 0;
        }
        $ordergoods_ids = pdo_fetchall("select og.id from " . tablename('sz_yi_order_goods') . " og left join " . tablename('sz_yi_order') . " o on o.id=og.orderid where og.uniacid={$_W['uniacid']} and o.status=3 and og.supplier_uid={$apply_info['uid']} and o.finishtime<{$apply_info['apply_time']} and o.finishtime>{$ap_time}");
        $ap_og_ids = array();
        foreach ($ordergoods_ids as $key => $value) {
            $ap_og_ids[] = $value['id'];
        }
        $ap_og_ids = implode(',', $ap_og_ids);
        pdo_update('sz_yi_supplier_apply', array('apply_ordergoods_ids' => $ap_og_ids), array('uniacid' => $_W['uniacid'], 'id' => $apply_id));
        $apply_info = pdo_fetch("select * from " . tablename('sz_yi_supplier_apply') . " where uniacid={$_W['uniacid']} and id={$apply_id}");
    }
    $condition .= " and og.id in ({$apply_info['apply_ordergoods_ids']}) ";
}
$suppliers = pdo_fetchall("select * from " . tablename('sz_yi_perm_user') . " where uniacid={$_W['uniacid']} and roleid = (select id from " .tablename('sz_yi_perm_role') . " where status1=1 LIMIT 1)");
$pindex    = max(1, intval($_GPC['page']));
$psize     = 20;
$params    = array(
    ':uniacid' => $_W['uniacid']
);
if (empty($starttime) || empty($endtime)) {
    $starttime = strtotime('-1 month');
    $endtime   = time();
}
if (!empty($_GPC['datetime'])) {
    $starttime = strtotime($_GPC['datetime']['start']);
    $endtime   = strtotime($_GPC['datetime']['end']);
    if (!empty($_GPC['searchtime'])) {
        $condition .= " AND o.createtime >= :starttime AND o.createtime <= :endtime ";
        $params[':starttime'] = $starttime;
        $params[':endtime']   = $endtime;
    }
}
if (!empty($_GPC['ordersn'])) {
    $condition .= " and o.ordersn like :ordersn";
    $params[':ordersn'] = "%{$_GPC['ordersn']}%";
}
if (!empty($_GPC['supplier_uid'])) {
    $condition .= " and og.supplier_uid = :supplier_uid";
    $params[':supplier_uid'] = "{$_GPC['supplier_uid']}";
} else {
    $condition .= " and og.supplier_uid > 0";
}
$sql = "select o.id,o.ordersn,o.price,o.goodsprice, o.dispatchprice,o.createtime, o.paytype, a.realname as addressname,m.realname from " . tablename('sz_yi_order') . " o  left join " . tablename('sz_yi_order_goods') . " og on og.orderid=o.id left join " . tablename('sz_yi_member') . " m on o.openid = m.openid left join " . tablename('sz_yi_member_address') . " a on a.id = o.addressid  where 1 {$condition} ";
$sql .= " ORDER BY o.id DESC ";
if (empty($_GPC['export'])) {
    $sql .= "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
}
$list = pdo_fetchall($sql, $params);
foreach ($list as &$row) {
    $row['ordersn'] = $row['ordersn'] . " ";
    $row['goods']   = pdo_fetchall("SELECT g.thumb,og.price,og.total,og.realprice,g.title,og.optionname from " . tablename('sz_yi_order_goods') . " og" . " left join " . tablename('sz_yi_goods') . " g on g.id=og.goodsid  " . " where og.uniacid = :uniacid and og.orderid=:orderid order by og.createtime  desc ", array(
        ':uniacid' => $_W['uniacid'],
        ':orderid' => $row['id']
    ));
    $totalmoney += $row['price'];
}
if (empty($totalmoney)) {
    $totalmoney = 0;
}
unset($row);
$totalcount = $total = pdo_fetchcolumn("select count(*) from " . tablename('sz_yi_order') . ' o ' . " left join " . tablename('sz_yi_order_goods') . " og on og.orderid=o.id left join " . tablename('sz_yi_member') . " m on o.openid = m.openid " . " left join " . tablename('sz_yi_member_address') . " a on a.id = o.addressid " . " where 1 {$condition}", $params);
$pager      = pagination($total, $pindex, $psize);
if ($_GPC['export'] == 1) {
    ca('statistics.export.order');
    plog('statistics.export.order', '导出订单统计');
    $list[] = array(
        'data' => '订单总计',
        'count' => $totalcount
    );
    $list[] = array(
        'data' => '金额总计',
        'count' => $totalmoney
    );
    foreach ($list as &$row) {
        if ($row['paytype'] == 1) {
            $row['paytype'] = '余额支付';
        } else if ($row['paytype'] == 11) {
            $row['paytype'] = '后台付款';
        } else if ($row['paytype'] == 21) {
            $row['paytype'] = '微信支付';
        } else if ($row['paytype'] == 22) {
            $row['paytype'] = '支付宝支付';
        } else if ($row['paytype'] == 23) {
            $row['paytype'] = '银联支付';
        } else if ($row['paytype'] == 3) {
            $row['paytype'] = '货到付款';
        }
        $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
    }
    unset($row);
    m('excel')->export($list, array(
        "title" => "订单报告-" . date('Y-m-d-H-i', time()),
        "columns" => array(
            array(
                'title' => '订单号',
                'field' => 'ordersn',
                'width' => 24
            ),
            array(
                'title' => '总金额',
                'field' => 'price',
                'width' => 12
            ),
            array(
                'title' => '商品金额',
                'field' => 'goodsprice',
                'width' => 12
            ),
            array(
                'title' => '运费',
                'field' => 'dispatchprice',
                'width' => 12
            ),
            array(
                'title' => '付款方式',
                'field' => 'paytype',
                'width' => 12
            ),
            array(
                'title' => '会员名',
                'field' => 'realname',
                'width' => 12
            ),
            array(
                'title' => '收货人',
                'field' => 'addressname',
                'width' => 12
            ),
            array(
                'title' => '下单时间',
                'field' => 'createtime',
                'width' => 24
            )
        )
    ));
}
load()->func('tpl');
include $this->template('supplier_list');
exit;
