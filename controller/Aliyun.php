<?php


namespace app\service\controller;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use beacon\core\Config;
use beacon\core\Controller;
use beacon\core\Method;
use beacon\core\Util;
use beacon\core\Logger;

class Aliyun extends Controller
{
    #[Method(act: 'auth', method: Method::GET | Method::POST, contentType: 'json')]
    public function auth()
    {
        $access_id = Config::get('aliyun.access_id', '');
        $access_key = Config::get('aliyun.access_key', '');
        $bucket = Config::get('aliyun.oss_bucket', '');
        $max_size = Config::get('aliyun.oss_max_size', 1000);
        $max_size = $max_size * 1048576;
        $policy = '{"expiration": "2115-01-27T10:56:19Z","conditions":[{"bucket": "' . $bucket . '" },["content-length-range", 0, ' . $max_size . ']]}';
        $policy = base64_encode($policy);
        $signature = base64_encode(hash_hmac('sha1', $policy, $access_key, true));
        return [
            "OSSAccessKeyId" => $access_id,
            "signature" => $signature,
            "policy" => $policy,
            "success_action_status" => 200
        ];
    }

    #[Method(act: 'convert_state', method: Method::GET | Method::POST, contentType: 'json')]
    public function convertState(string $taskId = '')
    {
        $access_id = Config::get('aliyun.access_id', '');
        $access_key = Config::get('aliyun.access_key', '');
        $bucket = Config::get('aliyun.oss_bucket', '');
        $project = Config::get('aliyun.imm_project', '');
        $regionId = Config::get('aliyun.imm_region_id', '');
        $host = 'imm.' . $regionId . '.aliyuncs.com';
        $host = Config::get('aliyun.imm_host', $host);
        $webUrl = Config::get('aliyun.oss_web_url', '');
        AlibabaCloud::accessKeyClient($access_id, $access_key)->regionId($regionId)->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('imm')
                // ->scheme('https') // https | http
                ->version('2017-09-06')
                ->action('GetOfficeConversionTask')
                ->method('POST')
                ->host($host)
                ->options([
                    'query' => [
                        'RegionId' => $regionId,
                        'TaskId' => $taskId,
                        'Project' => $project,
                    ],
                ])
                ->request();
            $data = $result->toArray();

            if ($data['Status'] != 'Finished') {
                $this->success('ok', ['finish' => false]);
            } else {
                // Logger::log('状态数据', $data);
                $tgtUri = $data['TgtUri'];
                $tgtType = $data['TgtType'];
                $srcUri = $data['SrcUri'];
                $ext = pathinfo($srcUri, PATHINFO_EXTENSION);
                $srcUri = str_replace('oss://' . $bucket, '', $srcUri);
                $tgtUri = str_replace('oss://' . $bucket, '', $tgtUri);
                $pageCount = intval($data['PageCount']);
                $list = [];
                for ($i = 1; $i <= $pageCount; $i++) {
                    $list[] = $tgtUri . '/' . $i . '.png';
                }
                $doc = [
                    'host' => $webUrl,
                    'src' => $srcUri,
                    'tgt' => $tgtUri,
                    'list' => $list,
                    'src_ext' => $ext,
                    'tgt_ext' => $tgtType,
                    'count' => $pageCount,
                ];
                $this->success('ok', ['finish' => true, 'document' => $doc]);
            }
        } catch (ClientException $e) {
            Logger::error($e);
            $this->error('转换失败,请检查文件是否损坏.');
        } catch (ServerException $e) {
            Logger::error($e);
            $this->error('转换失败,请检查文件是否损坏.');
        }
    }

    /**
     * 文档转换
     */
    #[Method(act: 'convert_task', method: Method::GET | Method::POST, contentType: 'json')]
    public function convertTask(string $file = '')
    {
        if (empty($file)) {
            $this->error('转换失败');
        }
        $access_id = Config::get('aliyun.access_id', '');
        $access_key = Config::get('aliyun.access_key', '');
        $bucket = Config::get('aliyun.oss_bucket', '');
        $project = Config::get('aliyun.imm_project', '');
        $regionId = Config::get('aliyun.imm_region_id', '');
        $host = 'imm.' . $regionId . '.aliyuncs.com';
        $host = Config::get('aliyun.imm_host', $host);
        //资源源数据地址
        $srcUri = 'oss://' . $bucket . '/' . $file;
        //重新命名，保护原文件
        $tgtUri = 'oss://' . $bucket . '/document/' . Util::randWord(20);
        //Logger::log($tgtUri);
        AlibabaCloud::accessKeyClient($access_id, $access_key)
            ->regionId($regionId)
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('imm')
                // ->scheme('https') // https | http
                ->version('2017-09-06')
                ->action('CreateOfficeConversionTask')
                ->method('POST')
                ->host($host)
                ->options([
                    'query' => [
                        'RegionId' => $regionId,
                        'SrcUri' => $srcUri,
                        'TgtUri' => $tgtUri,
                        'Project' => $project,
                        'TgtType' => "png",
                        'DisplayDpi' => "96",
                    ],
                ])
                ->request();
            $data = $result->toArray();
            if ($data['TaskId']) {
                $this->success('ok', ['taskId' => $data['TaskId']]);
            } else {
                $this->error('转换失败,请检查文件是否损坏.');
            }
            Logger::log($data);
        } catch (ClientException $e) {
            Logger::error($e);
            $this->error('转换失败,请检查文件是否损坏.');
        } catch (ServerException $e) {
            Logger::error($e);
            $this->error('转换失败,请检查文件是否损坏.');
        }
    }
}