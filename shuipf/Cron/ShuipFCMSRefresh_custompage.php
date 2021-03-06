<?php

// +----------------------------------------------------------------------
// | ShuipFCMS 计划任务 - 刷新自定义页面
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.shuipfcms.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 水平凡 <admin@abc3210.com>
// +----------------------------------------------------------------------

namespace CronScript;

//指定内容模块生成，没有指定默认使用GROUP_NAME
defined('GROUP_MODULE') or define('GROUP_MODULE', 'Content');

class ShuipFCMSRefresh_custompage {

    //任务主体
    public function run($cronId) {
        $r = M("Cron")->where(array("cron_id" => $cronId))->find();
        if ($r) {
            $catid = explode(",", $r['data']);
            if (is_array($catid)) {
                foreach ($catid as $cid) {
                    $tempid = $cid;
                    $rs = M("Customtemp")->where(array("tempid" => $tempid))->find();
                    if ($rs) {
                        ShuipFCMS()->Html->createHtml($rs);
                    }
                }
            }
        }
    }

}
