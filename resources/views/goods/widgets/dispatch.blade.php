﻿
<div class="form-group" id="dispatch_info">
<label class="col-xs-12 col-sm-3 col-md-2 control-label">运费设置</label>
<div class="col-sm-6 col-xs-6">
	<label class="radio-inline" style="float: left;">
        <input type="radio" name="widgets[dispatch][dispatch_type]" value="1" {if $dispatch['dispatch_type'] == 1}checked="true"{/if}  /> 统一邮费
    </label>

	<div class="input-group form-group" style="width: 180px; float: left;">
        <input type="text" name="widgets[dispatch][dispatch_price]" style="margin:0 10px;" id="dispatchprice" class="form-control" value="{if empty($dispatch['dispatch_price'])}0{else}{php echo $dispatch['dispatch_price']}{/if}" />
		<span class="input-group-addon">元</span>
	</div>

	<label class="radio-inline" style="float: left;">
        <input type="radio" name="widgets[dispatch][dispatch_type]" value="0" {if empty($dispatch['dispatch_type'])}checked="true"{/if}   /> 运费模板
    </label>

	<div style="width: auto; float: left; margin-left: 10px;"  id="type_dispatch">
		<select class="form-control tpl-category-parent" id="dispatchid" name="widgets[dispatch][dispatch_id]">
			<option value="0">默认模板</option>
			{loop $dispatch_templates $dispatch_item}

			<option value="{php echo $dispatch_item['id']}" {if $dispatch['dispatch_id'] == $dispatch_item['id']}selected="true"{/if}>{php echo $dispatch_item['dispatch_name']}</option>
			{/loop}
		</select>
	</div>
</div>
</div>

<div class="form-group">
	<label class="col-xs-12 col-sm-3 col-md-2 control-label">是否支持货到付款</label>
	<div class="col-sm-6 col-xs-6">
		<label class="radio-inline"><input type="radio" name="widgets[dispatch][is_cod]" value="1" {if empty($$dispatch['is_cod']) || $$dispatch['is_cod'] == 1}checked="true"{/if}  /> 不支持</label>
		<label class="radio-inline"><input type="radio" name="widgets[dispatch][is_cod]" value="2" {if $dispatch['is_cod'] == 2}checked="true"{/if}   /> 支持</label>
	</div>
</div>



