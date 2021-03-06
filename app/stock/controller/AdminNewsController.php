<?php
namespace app\stock\controller;

use app\stock\model\StockNewsModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminNewsController
 * @package app\stock\controller
 * @adminMenuRoot(
 *     'name'   => '新闻管理',
 *     'action' => 'default',
 *     'parent' => 'stock/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   => '',
 *     'remark' => '新闻管理'
 * )
 */
class AdminNewsController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        // $this->mq      = Db::name('stock_news');
        $this->scModel = new StockNewsModel;
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻列表',
     *     'parent' => 'stock/AdminNews/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();

        // $cid=$this->request->param('cid',0,'intval');
        // $cates=Db::name('stock_news_category')->order('list_order asc')->column('id,name');
        // if($cid==0){
        //    $cid=key($cates);
        // }
        $shop=session('shop.id');
        if($shop==1){
            $where=[];
        }else{
            $where=[
                // 'cate_id'=>['eq',$cid],
                'a.shop'=>['eq',$shop],
            ];
        }
        
        $keyword = isset($param['keyword']) ? $param['keyword'] : '';
        if (!empty($keyword)) {
            $where['a.title'] = ['like', '%' . $keyword . '%'];
        }

        $list = $this->scModel->alias('a')
            ->field('a.id,a.title,a.source,a.create_time,a.list_order,a.shop,b.name,s.name as sname')
            ->join('stock_news_category b', 'a.cate_id=b.id','LEFT')
            ->join('shop s', 's.id=a.shop','LEFT')
            ->where($where)
            ->order('a.list_order,a.create_time DESC')
            ->paginate(15);

        $this->assign('keyword', $keyword);
        $this->assign('list', $list->items());
        $this->assign('pager', $list->appends($param)->render());
        return $this->fetch();
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $cateTree = $this->scModel->cateTree();
        $this->assign('categories_tree', $cateTree);
        return $this->fetch();
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻新增提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻新增提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data         = $this->request->param();
        $data['shop'] =session('shop.id');

        // $result = $this->mq->insert($data);
        $result = $this->scModel->isUpdate(false)->allowField(true)->save($data);

        if ($result === false) {
            $this->error('添加失败!');
        }
        $this->success('添加成功!', url('AdminNews/index'));
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        // $post = $this->mq->where('id',$id)->find();
        $post = $this->scModel->get($id);
        if (empty($post)) {
            $this->error('数据丢失！');
        } else {
            $post = $post->toArray();
        }

        $cateTree = $this->scModel->cateTree($post['cate_id']);
        $this->assign('categories_tree', $cateTree);
        $this->assign($post);
        return $this->fetch();
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();
        //只能编辑自己上传的
        $shop=session('shop.id');
        if($shop!=1){
            $info= $this->scModel->get($data['id']);
            if($shop!=$info['shop']){
                $this->success('无权编辑此新闻');
            }
        }
       
        
        // $result = $this->mq->update($data);
        $result = $this->scModel->isUpdate(true)->allowField(true)->save($data);

        if ($result === false) {
            $this->error('保存失败!');
        }
        $this->success('保存成功!');
    }

    /**
     * @adminMenu(
     *     'name'   => '新闻排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '新闻排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('stock_news'));
        $this->success('排序更新成功！', '');
    }

    public function delete()
    {
        // $this->scModel->destroy($data);
    }
}
