<?php
namespace app\admin\controller;

use app\admin\service\v1\SysFileService;
use app\common\utils\Ajax;
use app\common\utils\ServiceFactory;
use think\facade\Request;
use think\facade\Validate;
use think\response\Json;

/**
 * 系统文件
 */
class SysFile
{
    protected SysFileService $SysFileService;

    public function __construct()
    {
        $this->SysFileService = ServiceFactory::make(SysFileService::class);
    }

    /**
     * 上传
     */
    public function upload(): Json
    {
        $file = Request::file('file');

        if (!$file) {
            return Ajax::fail('请选择文件');
        }

        $validate = Validate::rule([
            'file' => 'file|fileExt:jpg,png,gif,heic,heif,jpeg,mp3,wav,ogg,m4a,aac,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z|fileSize:104857600'
        ]);

        $result = $validate->check(['file' => $file]);

        if (!$result) {
            return Ajax::fail($validate->getError());
        }

        $data = $this->SysFileService->upload($file);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取文件列表
     */
    public function getLists(): Json
    {
        $params = Request::only([
            'keyword'
        ]);

        $keyword = $params['keyword'] ?? '';

        $data = $this->SysFileService->getLists( $keyword);

        return Ajax::success($data);
    }

    /**
     * 删除文件（支持批量）
     */
    public function delete(): Json
    {
        $ids = Request::post('ids');

        if (empty($ids)) {
            return Ajax::fail('请选择要删除的数据');
        }

        // 支持数组或逗号分隔
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $validate = Validate::rule([
            'ids' => 'require|array'
        ]);

        if (!$validate->check(['ids' => $ids])) {
            return Ajax::fail('参数错误');
        }

        $res = $this->SysFileService->delete($ids);

        if (!$res) {
            return Ajax::fail('删除失败');
        }

        return Ajax::success([], '删除成功');
    }
}
