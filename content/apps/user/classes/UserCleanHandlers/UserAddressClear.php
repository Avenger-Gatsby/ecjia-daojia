<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\User\UserCleanHandlers;

use Ecjia\App\User\UserCleanAbstract;
use RC_Uri;
use RC_DB;
use RC_Api;
use ecjia_admin;

class UserAddressClear extends UserCleanAbstract
{

    /**
     * 代号标识
     * @var string
     */
    protected $code = 'user_address_clear';

    /**
     * 排序
     * @var int
     */
    protected $sort = 1;

    public function __construct($user_id)
    {
        $this->name = __('账户收货地址', 'user');

        parent::__construct($user_id);
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count = $this->handleCount();

        $url = RC_Uri::url('user/admin/address_list', array('id' => $this->user_id));

        $text = sprintf(__('总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>个收货地址', 'user'), $count);

        $view_all = __('查看全部>>>', 'user');

        return <<<HTML

<span class="controls-info w300">{$text}</span>

<span class="controls-info"><a href="{$url}" target="_blank">{$view_all}</a></span>

HTML;

    }

    /**
     * 获取数据统计条数
     *
     * @return mixed
     */
    public function handleCount()
    {
        $user_address_count = RC_DB::table('user_address')->where('user_id', $this->user_id)->count();

        return $user_address_count;
    }


    /**
     * 执行清除操作
     *
     * @return mixed
     */
    public function handleClean()
    {
        $count = $this->handleCount();
        if (empty($count)) {
            return true;
        }

        $result = RC_DB::table('user_address')->where('user_id', $this->user_id)->delete();

        if ($result) {
            $this->handleAdminLog();
        }

        return $result;
    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {
        \Ecjia\App\User\Helper::assign_adminlog_content();

        $user_info = RC_Api::api('user', 'user_info', array('user_id' => $this->user_id));

        $user_name = !empty($user_info) ? sprintf(__('用户名是%s', 'user'), $user_info['user_name']) : sprintf(__('用户ID是%s', 'user'), $this->user_id);

        ecjia_admin::admin_log($user_name, 'clean', 'user_address');
    }

    /**
     * 是否允许删除
     *
     * @return mixed
     */
    public function handleCanRemove()
    {
        return !empty($this->handleCount()) ? true : false;
    }


}