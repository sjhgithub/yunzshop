{template 'web/_header'}
<div class="w1200 m0a">
    {template 'web/shop/tabs'}

    <!-- 新增加右侧顶部三级菜单 -->
    <div class="main rightlist">
    <div class="right-titpos">
        <ul class="add-snav">
            <li class="active"><a href="#">评价管理</a></li>
        </ul>
    </div>

        <form id="dataform" action="{php echo $this->createWebUrl('goods.comment.reply')}" method="post" class="form-horizontal form" onsubmit='return formcheck()' >
            <input type="hidden" name="id" value="{php echo $comment->id}" />
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    回复评价
                </div>

                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">评价商品</label>

                        <div class="col-sm-9 col-xs-12">
                            <input type="text" name="goods" maxlength="30" value="{if !empty($goods)}[{$goods['id']}]{$goods['title']}{/if}" id="goods" class="form-control" readonly />
                            <span id="goodsthumb" class='help-block' {if empty($goods)}style="display:none"{/if}><img  style="width:100px;height:100px;border:1px solid #ccc;padding:1px" src="{php echo tomedia($goods['thumb'])}"/></span>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">评价者</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="text" name="goods" maxlength="30" value="{php echo $comment->nick_name}" id="goods" class="form-control" readonly />
                            <span id="goodsthumb" class='help-block' ><img  style="width:100px;height:100px;border:1px solid #ccc;padding:1px" src="{php echo tomedia($comment->head_img_url)}"/></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">评分等级</label>
                        <div class="col-sm-9 col-xs-12">
                            <div class="form-control-static" style='color:#ff6600'>
                                {if $comment->level>=1}<i class='fa fa-star'></i>{else}<i class='fa fa-star-o'></i>{/if}
                                {if $comment->level>=2}<i class='fa fa-star'></i>{else}<i class='fa fa-star-o'></i>{/if}
                                {if $comment->level>=3}<i class='fa fa-star'></i>{else}<i class='fa fa-star-o'></i>{/if}
                                {if $comment->level>=4}<i class='fa fa-star'></i>{else}<i class='fa fa-star-o'></i>{/if}
                                {if $comment->level>=5}<i class='fa fa-star'></i>{else}<i class='fa fa-star-o'></i>{/if}
                            </div>
                        </div>
                    </div>
                    <div >
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 评论内容</label>
                            <div class="col-sm-9 col-xs-12">
                                <div class="form-control-static">{php echo $comment->content}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                            <div class="col-sm-9 col-xs-12">
                                <div class="input-group multi-img-details">
                                    {php $images = iunserializer($comment->images)}
                                    {loop $images $img}
                                    <div class="multi-item">
                                        <a href='{php echo tomedia($img)}' target='_blank'>
                                            <img class="img-responsive img-thumbnail" src='{php echo tomedia($img)}' onerror="this.src='./resource/images/nopic.jpg'; this.title='图片未找到.'">
                                        </a>
                                    </div>
                                    {/loop}
                                </div>
                            </div>
                        </div>
                    </div>

                    {loop $replys $reply}
                    <div >
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                            <div class="col-sm-9 col-xs-12">
                                <div class="form-control-static">
                                    {php echo empty($reply['nick_name']) ? '管理员' : $reply['nick_name']; }
                                    回复
                                    {php echo $reply['reply_name']}
                                    <span>时间:{php echo $reply['created_at']}</span>
                                    {if !empty($reply['nick_name'])}
                                    <input type="button" name="reply" data-uid="{$reply['uid']}" value="回复" class="btn btn-default reply" />
                                    {/if}
                                    {ifp 'shop.comment.delete'}
                                        <a class='btn btn-default'  href="{php echo $this->createWebUrl('goods.comment.deleted', ['id' => $reply['id']])}" onclick="return confirm('确认删除此评价吗？');return false;"><i class="fa fa-remove"></i>
                                        </a>
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">
                                回复内容
                            </label>
                            <div class="col-sm-9 col-xs-12">
                                <div class="form-control-static">{php echo $reply['content']}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                            <div class="col-sm-9 col-xs-12">
                                <div class="input-group multi-img-details">
                                    {php $images = iunserializer($reply['images'])}
                                    {loop $images $img}
                                    <div class="multi-item">
                                        <a href='{php echo tomedia($img)}' target='_blank'>
                                            <img class="img-responsive img-thumbnail" src='{php echo tomedia($img)}' onerror="this.src='./resource/images/nopic.jpg'; this.title='图片未找到.'">
                                        </a>
                                    </div>
                                    {/loop}
                                </div>
                            </div>
                        </div>
                    </div>
                    {/loop}

                    <div class="form-group" id="reply_seat">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">回复内容</label>
                        <div class="col-sm-9 col-xs-12">
                            <textarea name='reply[reply_content]' id="reply_content" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            {php echo app\common\helpers\ImageHelper::tplFormFieldMultiImage('reply[reply_images]','')}
                        </div>
                    </div>

                    <input type="hidden" name="reply[reply_id]" id="reply_id" value="{php echo $comment->uid}" />


                    <div class="form-group"></div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="button" name="back" onclick='history.back()' {ifp 'shop.adv.add|shop.adv.edit'}style='margin-left:10px;'{/if} value="返回列表" class="btn btn-default" />
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
    <script language='javascript'>
        function formcheck(){

            if($.trim($('#reply_content').val())==''){
                alert('请填写回复内容!');
                $('#reply_content').focus();
                return false;
            }

            return true;
        }

        $('.reply').click(function () {
            $('#reply_id').val($(this).data('uid'));
            $('#reply_content').focus();
            $('html,body').animate({ scrollTop: $(document).height() }, 100); return false;
        });

    </script>
    {template 'web/_footer'}