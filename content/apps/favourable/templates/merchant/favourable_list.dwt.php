<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!--{extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.merchant.favourable_list.init();
</script>
<!-- {/block} -->

<!-- {block name="home-content"} -->
<div class="row">
	<div class="col-lg-12">
		<h2 class="page-header">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a  class="btn btn-primary data-pjax" href="{$action_link.href}" id="sticky_a" style="float:right;margin-top:-3px;"><i class="fa fa-plus"></i> {$action_link.text}</a>
		<!-- {/if} -->
		</h2>
	</div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-body panel-body-small">
        		<ul class="nav nav-pills pull-left">
        			<li class="{if $smarty.get.type eq ''}active{/if}"><a class="data-pjax" href='{$favourable_list.quickuri.init}'>{t domain="favourable"}全部{/t} <span class="badge badge-info">{if $favourable_list.count.count}{$favourable_list.count.count}{else}0{/if}</span> </a></li>
        			<li class="{if $smarty.get.type eq 'on_going'}active{/if}"><a class="data-pjax" href='{$favourable_list.quickuri.on_going}'>{t domain="favourable"}正在进行中{/t}<span class="badge badge-info">{if $favourable_list.count.on_going}{$favourable_list.count.on_going}{else}0{/if}</span> </a></li>
        		</ul>
            </div>
            <div class="panel-body panel-body-small">
        		<div class="btn-group">
        			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cogs"></i> {t domain="favourable"}批量操作{/t} <span class="caret"></span></button>
        			<ul class="dropdown-menu">
                        <li><a class="batch-trash-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{url path='favourable/merchant/batch'}" data-msg="{t domain="favourable"}您确实要删除选中的优惠活动吗？{/t}" data-noSelectMsg="{t domain="favourable"}请先选中要删除的优惠活动！{/t}" data-name="act_id" href="javascript:;"> <i class="fa fa-trash-o"></i> {t domain="favourable"}删除优惠活动{/t}</a></li>
                   	</ul>
        		</div>
        		<form class="form-inline pull-right" action='{RC_Uri::url("favourable/merchant/init")}{if $smarty.get.type}&type={$smarty.get.type}{/if}' method="post" name="searchForm">
        			<div class="form-group">
        				<input type="text" class="form-control" name="keyword" value="{$smarty.get.keyword}" placeholder="{t domain="favourable"}请输入优惠活动名称{/t}"/>
        			</div>
        			<button type="button" class="btn btn-primary search_articles"><i class="fa fa-search"></i> {t domain="favourable"}搜索{/t} </button>
        		</form>
            </div>
            <div class="panel-body panel-body-small">
	            <section class="panel">
	                <table class="table table-striped table-hover table-hide-edit">
	                    <thead>
	                        <tr data-sorthref="{RC_Uri::url('favourable/merchant/init',"{if $smarty.get.type}type={$smarty.get.type}{/if}{if $smarty.get.keyword}&keyword={$smarty.get.type}{/if}")}">
	                            <th class="table_checkbox w35">
	                                <div class="check-list">
	                                    <div class="check-item">
	                                        <input id="checkall" type="checkbox" name="select_rows" data-toggle="selectall" data-children=".checkbox" />
	                                        <label for="checkall"></label>
	                                    </div>
	                                </div>
	                            </th>
	                            <th>{t domain="favourable"}优惠活动名称{/t}</th>
	                            <th class="w150">{t domain="favourable"}开始时间{/t}</th>
	                            <th class="w150">{t domain="favourable"}结束时间{/t}</th>
	                            <th class="w100">{t domain="favourable"}金额下限{/t}</th>
	                            <th class="w100">{t domain="favourable"}金额上限{/t}</th>
	                            <th class="w70" data-toggle="sortby" data-sortby="sort_order" >{t domain="favourable"}排序{/t}</th>
	                        </tr>
	                    </thead>
	                    <!-- {foreach from=$favourable_list.item item=favourable} -->
	                    <tr>
	                        <td class="check-list">
	                            <div class="check-item">
	                                <input id="checkbox_{$favourable.act_id}" type="checkbox" class="checkbox" value="{$favourable.act_id}" name="checkboxes[]">
	                                <label for="checkbox_{$favourable.act_id}"></label>
	                            </div>
	                        </td>
	                        <td class="hide-edit-area">
	                            <span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('favourable/merchant/edit_act_name')}" data-name="act_name" data-pk="{$favourable.act_id}" data-title='{t domain="favourable"}编辑优惠活动名称{/t}'>{$favourable.act_name}</span>
	                            <div class="edit-list">
	                                <a class="data-pjax" href='{url path="favourable/merchant/edit" args="act_id={$favourable.act_id}"}' title='{t domain="favourable"}编辑{/t}'>{t domain="favourable"}编辑{/t}</a>&nbsp;|&nbsp;
	                                <a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg='{t domain="favourable"}您确定要删除该优惠活动吗？{/t}' href='{url path="favourable/merchant/remove" args="act_id={$favourable.act_id}"}' title='{t domain="favourable"}删除{/t}'>{t domain="favourable"}删除{/t}</a>
	                            </div>
	                        </td>
	                        <td>{$favourable.start_time}</td>
	                        <td>{$favourable.end_time}</td>
	                        <td>{$favourable.min_amount}</td>
	                        <td>{$favourable.max_amount}</td>
	                        <td><span class="edit_sort_order cursor_pointer" data-placement="left" data-trigger="editable" data-url="{RC_Uri::url('favourable/merchant/edit_sort_order')}" data-name="sort_order" data-pk="{$favourable.act_id}" data-title='{t domain="favourable"}编辑优惠活动排序{/t}'>{$favourable.sort_order}</span></td>
	                    </tr>
	                    <!-- {foreachelse} -->
	                    <tr>
	                        <td class="no-records" colspan="7">{t domain="favourable"}没有找到任何记录{/t}</td>
	                    </tr>
	                    <!-- {/foreach} -->
	                    </tbody>
	                </table>
	                <!-- {$favourable_list.page} -->
	            </section>
	          </div>
        </div>
    </div>
</div>
<!-- {/block} -->