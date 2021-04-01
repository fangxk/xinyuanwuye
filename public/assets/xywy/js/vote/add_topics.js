
$(function(){
    function fixedLeft(){
        $(document).scroll(function(){
            var scrollTop = $(this).scrollTop();
            if(scrollTop > 156){
                $('.left-col').addClass('tofixed');
            }else{
                $('.left-col').removeClass('tofixed');
            }
        });
    }

    function pageOps(){
        $('.page-opts').on('click','.up',function(){
            $('html,body').animate({
                scrollTop:0
            },1000);
        });

        $('.page-opts').on('click','.down',function(){
            var domHeight = $('#content').height();
            $('html,body').animate({
                scrollTop:domHeight
            },1000);
        });
    };
    pageOps();
    fixedLeft();
    function addTopics(type){
        var topicsItem = '';
        var dataType = '';
        var choiceDom ='';
        var topicsName = '';
        var qsLen = $('.topics-box .question-item').size();

        switch(type)
        {
            case 'topics-radio':
                dataType = 'topics-radio';
                topicsName = '单选题';
                topicsType = '1';
                choiceDom = '<ul class="question-choice">'+
                    '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项1</label><span></span></div></div></div>'+
                    '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li>'+
                    '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项2</label><span></span></div></div></div>'+
                    '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></ul>';
                break;

            case 'topics-checkbox':
                dataType = 'topics-checkbox';
                topicsName = '多选题';
                topicsType = '2';
                choiceDom = '<ul class="question-choice">'+
                    '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项1</label><span></span></div></div></div>'+
                    '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></li>'+
                    '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项2</label><span></span></div></div></div>'+
                    '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></ul>';
                break;

            case 'topics-blank':
                dataType = 'topics-blank';
                topicsName = '填空题';
                topicsType = '3';
                //choiceDom = '<ul class="question-choice"><li><div class="choice-item"><input name="" readonly type="text" class="form-control-k"></div></li></ul>';
                break;
        }
        topicsItem +='<div class="question-item" data-ppp="'+ppp+'" data-type="'+dataType+'"><div  class="question-title"><span>Q<i class="qs-index">'+parseInt(qsLen+1)+'</i></span>';
        topicsItem +='<div class="qs-title">'+topicsName+''+
            '</div><div class="topics-desc"></div><input name="timu['+ppp+'][title]" type="hidden"  value="'+topicsName+'" class="se_timu" />'+
            '<input name="timu['+ppp+'][type]" value="'+topicsType+'" type="hidden"  class="se_type" />'+
            '<input name="timu['+ppp+'][i_desc]" value="" type="hidden"  class="se_desc" /><input name="timu['+ppp+'][i_max]" value="" type="hidden"  class="se_max" /></div>'+choiceDom+'<div class="question-operate">';
        // topicsItem +='<ul><li title="移动" class="qs-move"><span>移动<span></li><li title="操作" class="qs-handle"><span>操作<span></li>';
        topicsItem +='<ul><li title="操作" class="qs-handle"><span>操作<span></li>';
        topicsItem +='<li title="删除" class="qs-delete"><span>删除<span></li></ul></div></div>';

        $('.topics-box').append(topicsItem);
        $('.topics-init').addClass('none');
        $('html,body').animate({
            scrollTop:$('.topics-box').height()
        },500);

        $('.qs-handle').trigger("click");
    };

    function qsIndex(){
        $('.topics-box .question-item').each(function(i){
            $(this).find('.qs-index').text(i+1);
        })
    }

    function qsMove(){
        $( ".topics-box" ).sortable({
            placeholder: "ui-state-highlight",
            handle: ".qs-move",
            start:function(e){
                $('.ui-state-highlight').css('height',$(e.toElement).parents('.question-item').css('height'));
            },
            update:function(){
                qsIndex();
            }
        });
        $( ".topics-box" ).disableSelection();
    }

    function closeUpImg(){
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        $('#upload').removeClass('in').attr('aria-hidden',true).css('display','none');

        $('.yulan').hide();
        $('.that_i').val('');
        $('.yulan').find('img').attr('src','');
    }

    $('.topics-type li').click(function(){
        var _class =  $(this).attr('class');
        addTopics(_class);
        ppp++;
    });

    $(document).on('click','.qs-delete',function(){

        $(this).parents('.question-item').remove();
        qsIndex();
    });

    $(document).on('click','.qs-copy',function(){

        $(this).parents('.question-item').removeClass('setting');
        $(this).parents('.question-operate').next('.question-setting').remove();
        $(this).parents('.question-item').after($(this).parents('.question-item').clone());

        qsIndex();
    });

    qsMove();

    $(document).on('click','.qs-handle',function(){
        var _type = $(this).parents('.question-item').attr('data-type');
        var choice_item = $(this).parents('.question-item').find('.question-choice').children('li');
        var qs_title = $(this).parents('.question-item').find('.qs-title').text();
        var qs_desc = $(this).parents('.question-item').find('.topics-desc').text();
        var qs_max = $(this).parents('.question-item').find('.qs-max').text();
        var setBox = '';
        setBox += '<div class="question-setting"><div class="set-qs-title"><label for="">问题标题：</label><div>';
        setBox += '<input type="text" class="form-control-k qs_title_input" value="'+qs_title+'"></div></div><div class="set-qs-title m-t-15">';
        setBox += '<label for="">问题描述：</label><div><input type="text" placeholder="问题描述" class="form-control-k i_desc" value="'+qs_desc+'"></div></div><div class="set-qs-title m-t-15">';
       /* if(_type == 'topics-checkbox'){
            setBox += '<label for="">最大选择：</label><div><input type="text" placeholder="最大选择数" class="form-control-k i_max" value="'+qs_max+'"></div></div>';
        }*/
        if(_type == 'topics-radio' || _type == 'topics-checkbox'){
            setBox +='<div class="set-qs-choice"><table><thead><tr><th width="20%">选项文字</th>';
            setBox +='<th width="30%">操作</th></tr></thead><tbody>';
            for(var i=0;i<choice_item.size();i++){
                setBox +='<tr><td><input type="text" class="form-control-k choice_text_input" value="'+choice_item.eq(i).find('.choice-text label').text()+'"></td>';
                setBox +='<td><a href="javascript:" class="move-up" title="向上"><img src="../addons/xfeng_community/static/img/vote/top.png" alt="" width="15px"></a>';
                setBox +='<a href="javascript:" class="m-l-20 move-down" title="向下"><img src="../addons/xfeng_community/static/img/vote/bot.png" alt="" width="15px"></a>';
                setBox +='<a href="javascript:" class="m-l-20 remove-choice" title="删除"><img src="../addons/xfeng_community/static/img/vote/del.png" alt="" width="15px"></a></td></tr>';
            }
            setBox +='<tr><td colspan="3"><a href="javascript:" class="addChoice"><b class="fs-20"></b>添加选项</a></td></tr>';
            setBox +='</tbody></table><button type="button" class="btn btn-sm btn-primary btn-w-m save-set">保存</button></div></div>';
        }else{
            setBox +='<button type="button" class="btn btn-sm m-t-15 btn-primary btn-w-m save-set">保存</button></div>';
        }

        if($(this).parents('.question-item').hasClass('setting')){
            $(this).parents('.question-item').removeClass('setting');
            $(this).parents('.question-item').children('.question-setting').remove();
        }else{
            $('.question-item').removeClass('setting');
            $('.topics-box .question-setting').remove();

            $(this).parents('.question-item').addClass('setting');
            $(this).parents('.question-item').append(setBox);
        }
    });

    $(document).on('click','.addChoice',function(){
        var _tr = '';
        var _li = '';
        _tr +='<tr><td><input type="text" class="form-control-k choice_text_input" placeholder="选项文字"></td>';
        _tr +='<a href="javascript:" class="m-l-20 remove-choice" title="删除"><img src="../addons/xfeng_community/static/img/vote/del.png" alt="" width="15px"></a></td></tr>';
        _li +='<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项文字</label><span><span></div></div></div>'+
            '<input name="list['+$(this).parents('.question-item').attr('data-ppp')+'][]" type="hidden" class="se_an" value=""></li>';
        $(this).parents('tr').before(_tr);
        $(this).parents('.question-setting').siblings('.question-choice').append(_li);
    });

    $(document).on('keyup','.qs_title_input',function(){
        $(this).parents('.question-item').find('.qs-title').text($(this).val());
        $(this).parents('.question-item').find('.se_timu').val($(this).val());
    });

    $(document).on('keyup','.i_desc',function(){
        $(this).parents('.question-item').find('.topics-desc').text($(this).val());
        $(this).parents('.question-item').find('.se_desc').val($(this).val());
    });

    $(document).on('keyup','.i_max',function(){
        $(this).parents('.question-item').find('.qs-max').text($(this).val());
        $(this).parents('.question-item').find('.se_max').val($(this).val());
    });

    $(document).on('keyup','.choice_text_input',function(){
        var _i = $(this).parents('tr').index();
        if($(this).val() != ''){
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text label').text($(this).val());
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.se_an').val($(this).val());
        }else{
            $(this).val('');
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text label').text($(this).val());
        }
    });

    $(document).on('keyup','td.p-l-10>input',function(){
        var _i = $(this).parents('tr').index();
        if($(this).val() != ''){
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text span').text($(this).val());
        }else{
            $(this).val('');
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text span').text($(this).val());
        }
    });

    $(document).on('click','.remove-choice',function(){
        var _tr = $(this).parents('tr');

        $(this).parents('.question-item').children('.question-choice').children('li').eq(_tr.index()).remove();
        _tr.remove();
    });

    $(document).on('click','.move-up',function(){
        var thisTr = $(this).parents('tr');
        var thisChoice = $(this).parents('.question-item').children('.question-choice').children('li').eq(thisTr.index());
        if(thisTr.index() == 0){
            alert('已经是最顶部了');
        }else{
            thisTr.insertBefore(thisTr.prev('tr'));
            thisChoice.insertBefore(thisChoice.prev('li'));
        }
    });

    $(document).on('click','.move-down',function(){
        var thisTr = $(this).parents('tr');
        var thisChoice = $(this).parents('.question-item').children('.question-choice').children('li').eq(thisTr.index());

        if(thisTr.index() == parseInt($(this).parents('tbody').children('tr').size()-2)){
            alert('已经是最底部了');
        }else{
            thisTr.insertAfter(thisTr.next('tr'));
            thisChoice.insertAfter(thisChoice.next('li'));
        }

    });

    $(document).on('click','.choice-img',function(){
        var that_i = $(this).parents('tr').index();

        $('.that_i').val(that_i);
        $('body').addClass('modal-open').append('<div class="modal-backdrop fade in"></div>');
        $('#upload').addClass('in').attr('aria-hidden',false).css('display','block');
    });

    $(document).on('click','.close-upimg',function(){
        closeUpImg();
    });


    $(document).on('click','.save-img',function(){

        var _i = parseInt($('.that_i').val());
        var _img = $('.yulan img').attr('src');

        if($('.setting').find('.question-setting tr').eq(_i+1).find('.choice-img').hasClass('uped')){
            $('.setting').find('.question-choice li').eq(_i).find('img').attr('src',_img);
        }else{
            $('.setting').find('.question-choice li').eq(_i).addClass('has-img').children('.choice-info').before('<img src="'+_img+'"/>');
        }

        $('.setting').find('.question-setting tr').eq(_i+1).find('.choice-img').addClass('uped');

        closeUpImg();
    });

    $(document).on('click','.save-set',function(){
        $(this).parents('.setting').find('.qs-handle').trigger('click');
    })

});
