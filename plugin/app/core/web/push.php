<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 16/6/16
 * Time: 下午5:53
 */

global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_push') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY time DESC");
    foreach ($list as $key => $value) {
        $list[$key]['time'] =date('Y-m-d',$value['time']);
    }

} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('shop.push.add');
    } else {
        ca('shop.push.edit|shop.push.view');
    }
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'name' => trim($_GPC['name']),
            'content' => trim($_GPC['content']),
            'description' => trim($_GPC['description']),
            'time'=>time(),
        );
        pdo_insert('sz_yi_push', $data);

        $id = pdo_insertid();
        $url = "http://".$_SERVER['HTTP_HOST']."/app/index.php?i=".$_W['uniacid']."&c=entry&p=pushinfo&do=member&m=sz_yi&id=".$id;
        require IA_ROOT.'/addons/sz_yi/core/inc/plugin/vendor/leancloud/src/autoload.php';
        LeanCloud\LeanClient::initialize("egEtMTe0ky9XbUd57y5rKEAX-gzGzoHsz", "ca0OTkPQUdrXlPTGrospCY2L", "4HFoIDCAwaeOUSedwOISMUrj,master");

        $post_data = '{
          "alert":             "'. $data["name"] . '",
          "badge":             "1",
          "content-available": "0",
          "sound":             "1.wav",
          "action_type":"1",
          "title":            "'. $data["description"] . '",
          "action":            "com.yunzhong_notify.action",
          "ext": {"id":"'.$id.'","url":"'. $url .'"}
        }';

        $data = json_decode($post_data,true);
        $lean_push = new LeanCloud\LeanPush($data);
        $lean_push->setOption("prod", "prod");
        $lean_push->send();
        message('更新推送成功！', $this->createWebUrl('plugin/app', array(
            'method'=> 'push',
            'op' => 'display'
        )), 'success');
    }
    $item = pdo_fetch("select * from " . tablename('sz_yi_push') . " where id=:id and uniacid=:uniacid limit 1", array(
        ":id" => $id,
        ":uniacid" => $_W['uniacid']
    ));
} elseif ($operation == 'delete') {
    ca('shop.push.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,name FROM " . tablename('sz_yi_push') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
    if (empty($item)) {
        message('抱歉，推送不存在或是已经被删除！', $this->createWebUrl('plugin/app', array(
            'method'=> 'push',
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_push', array(
        'id' => $id
    ));
    message('推送删除成功！', $this->createWebUrl('plugin/app', array(
        'method'=> 'push',
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('push');