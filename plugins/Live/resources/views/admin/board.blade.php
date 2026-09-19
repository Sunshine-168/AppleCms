@extends('admin.layouts.inner')
@section('title','直播')
@php($desk=in_array(request('desk','channels'),['channels','pending','categories'],true)?request('desk','channels'):'channels')
@section('plain')
<div class="card card-panel" id="live-board">
    <div class="card-header"><span>直播工作台</span><button class="btn btn-sm" id="add">新增</button></div>
    <div class="card-body">
        <div class="queue-chips"><a class="chip" href="/admin/video/lives">频道</a><a class="chip" href="?desk=pending">待审</a><a class="chip" href="?desk=categories">分类</a></div>
        @if($desk==='channels')
        <form class="filter-bar" id="quick"><input type="hidden" name="desk" value="channels"><input name="title" placeholder="频道名"><button type="button" class="btn btn-sm" id="quick-add">快速添加</button></form>
        @endif
        <div class="filter-bar"><button type="button" class="btn btn-sm batch" data-action="status" data-value="1">上架</button><button type="button" class="btn btn-sm batch" data-action="status" data-value="0">下架</button><button type="button" class="btn btn-sm batch" data-action="delete">删除</button></div>
        <div id="live-table"></div>
    </div>
</div>
<template id="channel-form"><form><input type="hidden" name="desk" value="channels"><label>频道名</label><input name="title"><label>副标题</label><input name="sub"><label>分类</label><select name="cate_id"><option value="0">未分类</option>@foreach($categories as $category)<option value="{{$category->id}}">{{$category->name}}</option>@endforeach</select><label>封面</label><input name="cover"><label>播放地址</label><textarea name="urls" rows="5" placeholder="高清$https://example/live.m3u8#备用$https://example/live.flv"></textarea><label>播放来源</label><input name="play_from" value="hls"><label>备注</label><input name="remarks"><label>简介</label><textarea name="content"></textarea><label>排序</label><input type="number" name="sort" value="0"><label>状态</label><select name="status"><option value="1">上架</option><option value="0">待审/下架</option></select></form></template>
<template id="category-form"><form><input type="hidden" name="desk" value="categories"><label>分类名</label><input name="name"><label>标识</label><input name="slug"><label>图片</label><input name="pic"><label>排序</label><input type="number" name="sort" value="0"><label>状态</label><select name="status"><option value="1">启用</option><option value="0">停用</option></select></form></template>
@endsection
@push('scripts')
<script>(function(){var U=AdminUi,desk=@json($desk),url='/admin/video/lives';
var pick={title:'选择',html:d=>'<input type="checkbox" class="row-pick" value="'+Number(d.id)+'">'};
var cols=desk==='categories'?[pick,{title:'分类',html:d=>'<a href="#" class="edit">'+U.escape(d.name)+'</a>'},{title:'状态',html:d=>String(d.status)==='1'?'启用':'停用'},{title:'操作',html:()=>'<a href="#" class="edit">编辑</a> · <a href="#" class="del">删除</a>'}]:[pick,{title:'频道',html:d=>'<a href="#" class="edit">'+U.escape(d.title)+'</a>'},{title:'分类',html:d=>U.escape(d.cate_name||'未分类')},{title:'线路',html:d=>U.escape(d.play_from||'hls')},{title:'状态',html:d=>String(d.status)==='1'?'上架':'待审'},{title:'操作',html:()=>'<a href="#" class="edit">编辑</a> · <a href="#" class="del">删除</a>'}];
var table=U.table({el:'#live-table',url:url+'/list',where:{desk:desk,limit:20},cols:cols});
function open(row){row=row||{};U.dialog({title:row.id?'编辑':'新增',content:document.getElementById(desk==='categories'?'category-form':'channel-form').innerHTML,onOpen:b=>U.fillForm(b.querySelector('form'),row),onSave:b=>{var d=U.formData(b.querySelector('form'));if(row.id)d.id=row.id;return U.post(url+'/save',d).then(r=>{if(!r||r.code!==0){U.toast((r&&r.msg)||'失败','err');return false}table.refresh()})}})}
U.on('#add','click',()=>open());U.on('#quick-add','click',()=>{var f=document.getElementById('quick');U.post(url+'/save',U.formData(f)).then(r=>{if(r&&r.code===0){f.reset();table.refresh()}else U.toast((r&&r.msg)||'失败','err')})});
U.on('#live-table','click',e=>{var a=e.target.closest('a');if(!a)return;e.preventDefault();var tr=e.target.closest('tr'),row=table.rows()[tr.getAttribute('data-idx')];if(a.classList.contains('edit'))open(row);if(a.classList.contains('del')&&U.confirm('确认删除？'))U.post(url+'/delete',{id:row.id,desk:desk}).then(()=>table.refresh())});
document.querySelectorAll('.batch').forEach(function(btn){btn.addEventListener('click',function(){var ids=Array.from(document.querySelectorAll('.row-pick:checked')).map(x=>x.value);if(!ids.length){U.toast('请选择数据','err');return}if(btn.dataset.action==='delete'&&!U.confirm('确认删除？'))return;U.post(url+'/batch',{ids:ids,desk:desk,action:btn.dataset.action,value:btn.dataset.value||''}).then(r=>{U.toast((r&&r.msg)||'已处理');table.refresh()})})});
})();</script>
@endpush
