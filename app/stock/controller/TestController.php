<?php
namespace app\stock\controller;

use cmf\controller\HomeBaseController;
use app\stock\model\StockModel;
use app\stock\model\StockIndiceModel;
use app\stock\model\StockNewsModel;
use think\Db;
use sms\Dy;

/**
* 股票代码测试
* \app\stock\controller\TestController - 2.php
*/
class TestController extends HomeBaseController
{
    public function sms()
    {
        $sms = new Dy();
        $result = $sms->dySms('18715511536');
        dump($result);
    }

    public function test()
    {
        // $scModel = new StockModel;
        // $data = $scModel->getStockBase('s_sh000001');
        // $data = $scModel->getIndice('s_sh000001');
        // $data = $scModel->getPrice('s_sh000001');

        lothar_nonTradingDay('2018',1);

        // dump($data);
    }
    public function index()
    {
        $code = $this->request->param('code', 's_sh600000');

        $scModel = new StockModel;
        $result = $scModel->getIndice($code);
        $result = $scModel->getPrice($code);

        dump($result);
    }
    public function index2()
    {
        $code = $this->request->param('code', 'sh000001');

        $scModel = new StockModel;
        $result = $scModel->getStockBase($code);

        $data = [
            'name'  => $result[1],
            'price' => round($result[3], 2),
        ];
        dump($data);
    }

    public function stock()
    {
        // $m = Db::name('stock_indice');
        $scModel = new StockModel;

        $codes = $scModel->limit(700)->column('code0');
        // dump($codes);
        
        $code = '';
        foreach ($codes as $val) {
            $code .= 's_'.$val.',';
        }
        $code = substr($code,0,-1);
        // $code = implode(',',$codes);
        
        // dump($code);

        $data = $scModel->getIndice($code);
        // $data = $scModel->getPrice($code);
        dump($data);

        // $m->insertAll($post);
        // model('StockIndice')->isUpdate(true)->saveAll($post);
        exit('股市指数获取结束');
    }

    public function trade()
    {
        $rest = lothar_nonTradingDay(2018);
        dump($rest);
    }

}