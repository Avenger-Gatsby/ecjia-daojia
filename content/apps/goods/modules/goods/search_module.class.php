<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 商品店铺搜索
 * @author will.chen
 */
class goods_search_module extends api_front implements api_interface {

    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
        $this->authSession();

        $keywords = $this->requestData('keywords');
        $location = $this->requestData('location', array());
        /*经纬度为空判断*/
        if (!is_array($location) || empty($location['longitude']) || empty($location['latitude'])) {
            $data = array();
            $data['type'] = 'goods';
            $data['result'] = array();
            $pager= array(
                    "total" => '0',
                    "count" => '0',
                    "more"    => '0',
            );
            return array('data' => $data, 'pager' => $pager);
        }

        $size = $this->requestData('pagination.count', 15);
        $page = $this->requestData('pagination.page', 1);
        
        if (is_array($location) || !empty($location['longitude']) || !empty($location['latitude'])) {
        	$geohash = RC_Loader::load_app_class('geohash', 'store');
        	$geohash_code = $geohash->encode($location['latitude'] , $location['longitude']);
        	$store_ids = RC_Api::api('store', 'neighbors_store_id', array('geohash' => $geohash_code));
        }

        $options = array(
                'location'        => $location,
                'keywords'        => $keywords,
                'size'            => $size,
                'page'            => $page,
        );

        $result = RC_Api::api('store', 'store_list', $options);
        
        if (is_ecjia_error($result)) {
            return $result;
        }
        if (!empty($result['seller_list'])) {
            $max_goods = 0;
            $mobilebuy_db = RC_Model::model('goods/goods_activity_model');
            $db_favourable	 = RC_DB::table('favourable_activity');
            $seller_list = array();
            $now = RC_Time::gmtime();
            foreach ($result['seller_list'] as $row) {
            	$favourable_result = $db_favourable->where('store_id', $row['id'])->where('start_time', "<=", $now)->where('end_time', ">=", $now)->where('act_type', '!=', 0)->get();
            	$favourable_list = array();
                if (!empty($favourable_result)) {
                    foreach ($favourable_result as $val) {
                        if ($val['act_range'] == '0') {
                            $favourable_list[] = array(
                                    'name' => $val['act_name'],
                                    'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
                                    'type_label' => $val['act_type'] == '1' ? __('满减', 'goods') : __('满折', 'goods'),
                            );
                        } else {
                            $act_range_ext = explode(',', $val['act_range_ext']);
                            switch ($val['act_range']) {
                                case 1 :
                                    if (in_array($val['cat_id'], $act_range_ext)) {
                                        $favourable_list[] = array(
                                                'name' => $val['act_name'],
                                                'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
                                                'type_label' => $val['act_type'] == '1' ? __('满减', 'goods') : __('满折', 'goods'),
                                        );
                                    }
                                    break;
                                case 2 :
                                    if (in_array($val['brand_id'], $act_range_ext)) {
                                        $favourable_list[] = array(
                                                'name' => $val['act_name'],
                                                'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
                                                'type_label' => $val['act_type'] == '1' ? __('满减', 'goods') : __('满折', 'goods'),
                                        );
                                    }
                                    break;
                                case 3 :
                                    if (in_array($val['goods_id'], $act_range_ext)) {
                                        $favourable_list[] = array(
                                                'name' => $val['act_name'],
                                                'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
                                                'type_label' => $val['act_type'] == '1' ? __('满减', 'goods') : __('满折', 'goods'),
                                        );
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }

                    }
                }

//                 $goods_options = array('page' => 1, 'size' => 3, 'store_id' => $row['id']);
//                 if (!empty($goods_category)) {
//                     $goods_options['cat_id'] = $goods_category;
//                 }
//                 $goods_result = RC_Api::api('goods', 'goods_list', $goods_options);
//                 $goods_list = array();
//                 if (!empty($goods_result['list'])) {
//                     foreach ($goods_result['list'] as $val) {
//                         /* 判断是否有促销价格*/
//                         $price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
//                         $activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
//                         /* 计算节约价格*/
//                         $saving_price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_shop_price'] - $val['unformatted_promote_price'] : (($val['unformatted_market_price'] > 0 && $val['unformatted_market_price'] > $val['unformatted_shop_price']) ? $val['unformatted_market_price'] - $val['unformatted_shop_price'] : 0);

//                         $mobilebuy_price = $object_id = 0;

//                         $goods_list[] = array(
//                                 'goods_id'                  => $val['goods_id'],
//                                 'name'                      => $val['name'],
//                                 'market_price'              => $val['market_price'],
//                                 'shop_price'                => $val['shop_price'],
//                                 'promote_price'             => $val['promote_price'],
//                                 'img' => array(
//                                         'thumb'             => $val['goods_img'],
//                                         'url'               => $val['original_img'],
//                                         'small'             => $val['goods_thumb']
//                                 ),
//                                 'activity_type'             => $activity_type,
//                                 'object_id'                 => $object_id,
//                                 'saving_price'              => $saving_price,
//                                 'formatted_saving_price'    => $saving_price > 0 ? sprintf(__('已省%s元', 'goods'), $saving_price) : '',
//                         );
//                     }
//                 }
                //用户端商品展示基础条件
                $filters = [
	                'store_unclosed' 		=> 0,    //店铺未关闭的
	                'is_delete'		 		=> 0,	 //未删除的
	                'is_on_sale'	 		=> 1,    //已上架的
	                'is_alone_sale'	 		=> 1,	 //单独销售的
	                'review_status'  		=> 2,    //审核通过的
	                'no_need_cashier_goods'	=> true, //不需要收银台商品
                ];
                //是否展示货品
                if (ecjia::config('show_product') == 1) {
                	$filters['product'] = true;
                }
                //店铺id
                if (!empty($row['id'])) {
                	$filters['store_id'] = $store_ids;
                }
                //会员等级价格
                $filters['user_rank'] = $_SESSION['user_rank'];
                $filters['user_rank_discount'] = $_SESSION['discount'];
                //分页信息
                $filters['size'] = 3;
                $filters['page'] = 1;
                
                $collection = (new \Ecjia\App\Goods\GoodsSearch\GoodsApiCollection($filters))->getData();
                $goods_list = $collection['goods_list'];
                
                if ($collection['pager']->total_records >= $max_goods) {
                    array_unshift($seller_list, array(
                        'id'                    => $row['id'],
                        'seller_name'           => $row['seller_name'],
                        'seller_category'       => $row['seller_category'],
                        'seller_logo'           => $row['shop_logo'],
                        'seller_goods'          => $goods_list,
                        'manage_mode'		    => $row['manage_mode'],
                        'follower'              => $row['follower'],
                        'is_follower'           => $row['is_follower'],
                        'goods_count'           => $goods_result['page']->total_records,
                        'comment'               => '100%',
                        'favourable_list'       => $favourable_list,
                        'location'              => $row['location'],
                    ));
                } else {
                    $seller_list[] = array(
                        'id'                    => $row['id'],
                        'seller_name'           => $row['seller_name'],
                        'seller_category'       => $row['seller_category'],
                        'seller_logo'           => $row['shop_logo'],
                        'seller_goods'          => $goods_list,
                        'manage_mode'		    => $row['manage_mode'],
                        'follower'              => $row['follower'],
                        'is_follower'           => $row['is_follower'],
                        'goods_count'           => $goods_result['page']->total_records,
                        'comment'               => '100%',
                        'favourable_list'       => $favourable_list,
                        'location'              => $row['location'],
                    );
                }
            }

            $data           = array();
            $data['type']   = 'seller';
            $data['result'] = $seller_list;
            $data['pager']  = array(
                'total'     => $result['page']->total_records,
                'count'     => $result['page']->total_records,
                'more'      => $result['page']->total_pages <= $page ? 0 : 1,
            );

            return array('data' => $data, 'pager' => $data['pager']);
        } else {
//             $options = array(
//                     'keywords'    => $keywords,
//                     'sort'        => array('g.sort_order' => 'ASC', 'goods_id' => 'DESC'),
//                     'page'        => $page,
//                     'size'        => $size,
//                     'location'    => $location,
//             );
//             $result = RC_Api::api('goods', 'goods_list', $options);
//             $goods_list = array();
//             if (!empty($result['list'])) {
//                 $mobilebuy_db = RC_Model::model('goods/goods_activity_model');
//                 /* 手机专享*/
//                 $result_mobilebuy = ecjia_app::validate_application('mobilebuy');
//                 $is_active = ecjia_app::is_active('ecjia.mobilebuy');
//                 foreach ($result['list'] as $val) {
//                     /* 判断是否有促销价格*/
//                     $price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
//                     $activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
//                     /* 计算节约价格*/
//                     $saving_price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_shop_price'] - $val['unformatted_promote_price'] : (($val['unformatted_market_price'] > 0 && $val['unformatted_market_price'] > $val['unformatted_shop_price']) ? $val['unformatted_market_price'] - $val['unformatted_shop_price'] : 0);

//                     $mobilebuy_price = $object_id = 0;
//                     if (!is_ecjia_error($result_mobilebuy) && $is_active) {
//                         $mobilebuy = $mobilebuy_db->find(array(
//                                 'goods_id'      => $val['goods_id'],
//                                 'start_time'    => array('elt' => RC_Time::gmtime()),
//                                 'end_time'      => array('egt' => RC_Time::gmtime()),
//                                 'act_type'      => GAT_MOBILE_BUY,
//                         ));
//                         if (!empty($mobilebuy)) {
//                             $ext_info           = unserialize($mobilebuy['ext_info']);
//                             $mobilebuy_price    = $ext_info['price'];
//                             if ($mobilebuy_price < $price) {
//                                 $val['promote_price']   = price_format($mobilebuy_price);
//                                 $object_id              = $mobilebuy['act_id'];
//                                 $activity_type          = 'MOBILEBUY_GOODS';
//                                 $saving_price           = ($val['unformatted_shop_price'] - $mobilebuy_price) > 0 ? $val['unformatted_shop_price'] - $mobilebuy_price : 0;
//                             }
//                         }
//                     }

//                     $goods_list[] = array(
//                             'id'                        => $val['goods_id'],
//                             'name'                      => $val['name'],
//                             'market_price'              => $val['market_price'],
//                             'shop_price'                => $val['shop_price'],
//                             'promote_price'             => $val['promote_price'],
//                             'img' => array(
//                                     'thumb'             => $val['goods_img'],
//                                     'url'               => $val['original_img'],
//                                     'small'             => $val['goods_thumb']
//                             ),
//                             'activity_type'             => $activity_type,
//                             'object_id'                 => $object_id,
//                             'saving_price'              => $saving_price,
//                             'formatted_saving_price'    => $saving_price > 0 ? sprintf(__('已省%s元', 'goods'), $saving_price) : '',
//                             'seller_id'                  => $val['store_id'],
//                             'seller_name'                => $val['store_name'],
//                     );
//                 }
//             }
        	$goods_list = array(); 
        	//用户端商品展示基础条件
        	$filters = [
	        	'store_unclosed' 		=> 0,    //店铺未关闭的
	        	'is_delete'		 		=> 0,	 //未删除的
	        	'is_on_sale'	 		=> 1,    //已上架的
	        	'is_alone_sale'	 		=> 1,	 //单独销售的
	        	'review_status'  		=> 2,    //审核通过的
	        	'no_need_cashier_goods'	=> true, //不需要收银台商品
        	];
        	//是否展示货品
        	if (ecjia::config('show_product') == 1) {
        		$filters['product'] = true;
        	}
        	//定位附近店铺id
        	if (is_array($store_ids) || empty($store_ids)) {
        		$filters['store_id'] = $store_ids;
        	}
        	//关键字搜索
        	if (!empty($keywords)) {
        		$filters['keywords'] = $keywords;
        	}
        	//排序
        	$order_sort = array('goods.sort_order' => 'ASC', 'goods.goods_id' => 'DESC');
        	if ($order_sort) {
        		$filters['sort_by'] = $order_sort;
        	}
        	//分页信息
        	$filters['size'] = $size;
        	$filters['page'] = $page;
        	
        	$collection = (new \Ecjia\App\Goods\GoodsSearch\GoodsApiCollection($filters))->getData();
        	$goods_list = $collection['goods_list'];
        	$pager = $collection['pager'];
        	
            $data = array();
            $data['type']   = 'goods';
            $data['result'] = $goods_list;
            $data['pager']  =  array(
                    "total"     => $result['page']->total_records,
                    "count"     => $result['page']->total_records,
                    "more"      => $result['page']->total_pages <= $page ? 0 : 1,
            );

            return array('data' => $data, 'pager' => $data['pager']);
        }
    }
}


// end
