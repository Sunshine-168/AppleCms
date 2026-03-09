<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CardSaveRequest;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends BaseController
{
    public function index(Request $request)
    {
        $param = array_merge([
            'sale_status' => '',
            'use_status' => '',
            'time' => '',
            'wd' => '',
            'export' => '',
        ], $request->all());
        $param['page'] = max(1, intval($param['page'] ?? 1));
        $param['limit'] = max(1, intval($param['limit'] ?? $this->pagesize));

        $query = Card::query();
        
        if (isset($param['sale_status']) && in_array($param['sale_status'], ['0', '1'], true)) {
            $query->where('card_sale_status', $param['sale_status']);
        }
        
        if (isset($param['use_status']) && in_array($param['use_status'], ['0', '1'], true)) {
            $query->where('card_use_status', $param['use_status']);
        }
        
        if (!empty($param['wd'])) {
            $wd = htmlspecialchars(urldecode($param['wd']));
            $query->where('card_no', 'like', '%' . $wd . '%');
        }
        
        if ($param['time'] !== '') {
            if ($param['time'] == '1') {
                $t = Card::max('card_add_time');
            } else {
                $t = strtotime(date('Y-m-d', strtotime('-' . $param['time'] . ' day')));
            }
            $query->where('card_add_time', '>=', intval($t));
        }
        
        if ($param['export'] == '1') {
            $param['page'] = 1;
            $param['limit'] = 9999;
        }
        
        if ($param['export'] == '1') {
            $cards = $query->with('user')
                ->orderBy('card_id', 'desc')
                ->limit($param['limit'])
                ->get();

            return $this->exportCards($cards);
        }

        $total = $query->count();
        $list = $query->with('user')
            ->orderBy('card_id', 'desc')
            ->skip(($param['page'] - 1) * $param['limit'])
            ->take($param['limit'])
            ->get();

        $page = $param['page'];
        $limit = $param['limit'];
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';

        return view('admin.card.index', compact('list', 'total', 'page', 'limit', 'param'));
    }

    public function info(CardSaveRequest $request, $id = null)
    {
        if ($request->isMethod('post')) {
            Card::generateBatch(
                (int) $request->input('num', 0),
                (int) $request->input('money', 0),
                (int) $request->input('point', 0),
                (string) $request->input('role_no', ''),
                (string) $request->input('role_pwd', '')
            );

            return $this->success('保存成功');
        }

        $info = $id ? Card::findOrFail($id) : new Card();

        return view('admin.card.info', compact('info'));
    }

    public function del(Request $request)
    {
        $ids = $request->input('ids');
        $all = (int) $request->input('all', 0);
        if (empty($ids) && $all !== 1) {
            return $this->error('请选择要删除的数据');
        }

        $query = Card::query();
        if ($all === 1) {
            $query->where('card_id', '>', 0);
        } else {
            $ids = is_array($ids) ? $ids : explode(',', $ids);
            $query->whereIn('card_id', $ids);
        }

        $query->delete();

        return $this->success('删除成功');
    }

    private function exportCards($cards)
    {
        $filename = 'card_' . date('Y-m-d') . '.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo "卡号,密码,面值,状态,使用时间,添加时间\n";
        
        foreach ($cards as $card) {
            echo $card->card_no . ",";
            echo $card->card_pwd . ",";
            echo $card->card_points . ",";
            echo ($card->card_use_status == 1 ? '已使用' : '未使用') . ",";
            echo ($card->card_use_time > 0 ? date('Y-m-d H:i:s', $card->card_use_time) : '') . ",";
            echo date('Y-m-d H:i:s', $card->card_add_time) . "\n";
        }
        exit;
    }
}
