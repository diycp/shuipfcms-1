<?php

// +----------------------------------------------------------------------
// | ShuipFCMS 友情链接管理
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.shuipfcms.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 水平凡 <admin@abc3210.com>
// +----------------------------------------------------------------------

namespace Topic\Controller;

use Common\Controller\AdminBase;

class TopicController extends AdminBase {

    public function index() {
        //分类id
        $termsid = I('get.termsid', 0, 'intval');
        $where = array();
        if ($termsid > 0) {
            $where['termsid'] = array("EQ", $termsid);
        }
        $db = M("Topic");
        $count = $db->where($where)->count();
        $page = $this->page($count, 20);
        $data = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "DESC"))->select();
        $Termsdb = M("Terms");
        $Terms = $Termsdb->where(array("module" => "Topic"))->getField("id,name");
        $this->assign("Terms", $Terms);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("data", $data);
        $this->display();
    }

    //添加 
    public function add() {
        if (IS_POST) {
            $db = D("Topic/Topic");
            if (!empty($_POST['terms']['name'])) {
                $_POST['termsid'] = $this->addTerms($_POST['terms']['name']);
            }
            $_POST['updated'] = time();
            if ($db->create()) {
                $id = $db->add();
                if ($id) {
                    //更新附件状态
                    if (!empty($_POST['image'])) {
                        service("Attachment")->api_update('', 'Topic-' . $id, 1);
                    }
                    $this->success("添加成功！", U("Topic/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($db->getError());
            }
        } else {
            $db = M("Terms");
            $Terms = $db->where(array("module" => "Topic"))->select();
            $this->assign("Terms", $Terms);
            $this->display();
        }
    }

    //编辑
    public function edit() {
        if (IS_POST) {
            $db = D("Topic/Topic");
            if (!empty($_POST['terms']['name'])) {
                $_POST['termsid'] = $this->addTerms($_POST['terms']['name']);
            }
            $data = $db->create();
            if ($data) {
                if ($db->save() !== false) {
                    //更新附件状态
                    if (!empty($_POST['image'])) {
                        service("Attachment")->api_update('', 'Topic-' . $data['id'], 1);
                    }
                    $this->success("更新成功！", U("Topic/index"));
                } else {
                    $this->error("更新失败！");
                }
            } else {
                $this->error($db->getError());
            }
        } else {
            $id = I('get.id', 0, 'intval');
            $db = M("Terms");
            $data = M("Topic")->where(array("id" => $id))->find();
            if (!$data) {
                $this->error("该信息不存在！");
            }
            $Terms = $db->where(array("module" => "Topic"))->select();
            $this->assign("Terms", $Terms);
            $this->assign($data);
            $this->display();
        }
    }

    //删除 
    public function delete() {
        if (IS_POST) {
            $ids = I('post.ids', '', '');
            $db = M("Topic");
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $db->where(array("id" => $id))->delete();
                    service("Attachment")->api_delete('Topic-' . $id);
                }
            }
            $this->success("删除成功！");
        } else {
            $id = I('get.id', 0, 'intval');
            $db = M("Topic");
            $status = $db->where(array("id" => $id))->delete();
            if ($status) {
                //删除附件
                service("Attachment")->api_delete('Topic-' . $id);
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    //分类管理
    public function terms() {
        if (IS_POST) {
            
        } else {
            $Terms = M("Terms");
            $data = $Terms->where(array("module" => "Topic"))->select();
            $this->assign("data", $data);
            $this->display();
        }
    }

    //分类编辑
    public function termsedit() {
        $terms = M("Terms");
        if (IS_POST) {
            if ($terms->create()) {
                if ($terms->save() !== false) {
                    $this->success("更新成功！", U("Topic/terms"));
                } else {
                    $this->error("更新失败！");
                }
            } else {
                $this->error($terms->getError());
            }
        } else {
            $id = I('get.id', 0, 'intval');
            $info = $terms->where(array("id" => $id, "module" => "Topic"))->find();
            if (!$info) {
                $this->error("该分类不存在！");
            }
            $this->assign($info);
            $this->assign("big_menu", array(U("Topic/termsadd"), "添加分类"));
            $this->display();
        }
    }

    //分类删除
    public function termsdelete() {
        $id = I('get.id', 0, 'intval');
        $Topic = M("Topic");
        $terms = M("Terms");
        if ($terms->where(array("id" => $id, "module" => "Topic"))->delete()) {
            $Topic->where(array("termsid" => $id))->delete();
            $this->success("分类删除成功！");
        } else {
            $this->error("分类删除失败！");
        }
    }

    /**
     * 添加分类
     * @param type $name 
     */
    protected function addTerms($name) {
        $db = M("Terms");
        $name = trim($name);
        if (empty($name)) {
            $this->error("分类名称不能为空！");
        }
        $count = $db->where(array("name" => $name, "module" => "Topic"))->count();
        if ($count > 0) {
            $this->error("该分类已经存在！");
        }
        $status = $db->add(array("name" => $name, "module" => "Topic"));
        if ($status) {
            return $status;
        } else {
            $this->error("分类添加失败！");
        }
    }

}
