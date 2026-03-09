<?php

namespace App\TagLib;

/**
 * Maccms 标签库（参考文件）
 * 
 * 原始文件：maccms10/application/common/taglib/Maccms.php
 * 
 * 注意：此文件仅作为参考，实际功能已通过以下方式实现：
 * 1. 数据获取逻辑 → app/Services/TagService.php
 * 2. 标签指令 → app/Providers/AppServiceProvider.php（Blade 指令）
 * 
 * 所有标签已迁移为 Laravel Blade 指令：
 * - maccms:link → @maccmslink
 * - maccms:area → @maccmsarea
 * - maccms:lang → @maccmslang
 * - maccms:year → @maccmsyear
 * - maccms:class → @maccmsclass
 * - maccms:version → @maccmsversion
 * - maccms:state → @maccmsstate
 * - maccms:letter → @maccmsletter
 * - maccms:type → @maccmstype
 * - maccms:comment → @maccmscomment
 * - maccms:gbook → @maccmsgbook
 * - maccms:role → @maccmsrole
 * - maccms:actor → @maccmsactor
 * - maccms:topic → @maccmstopic
 * - maccms:art → @maccmsart
 * - maccms:manga → @maccmsmanga（待实现）
 * - maccms:vod → @maccmsvod
 * - maccms:website → @maccmswebsite
 * 
 * 特殊标签：
 * - maccms:foreach → 使用 Laravel 原生 @foreach
 * - maccms:for → 使用 Laravel 原生 @for
 * 
 * 详细使用说明请参考：TAGLIB_MIGRATION_GUIDE.md
 */
class Maccms
{
    // 此文件仅作为参考，实际功能已迁移到 TagService 和 Blade 指令
}
