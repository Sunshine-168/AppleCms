CREATE TABLE IF NOT EXISTS `__PREFIX__mpt_task` (
  `mpt_id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mpt_mid`         tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '内容模型 1=vod',
  `mpt_obj_id`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联内容ID',
  `mpt_obj_name`    varchar(255) NOT NULL DEFAULT '' COMMENT '内容名称快照',
  `mpt_subject`     varchar(255) NOT NULL DEFAULT '' COMMENT '视频主题',
  `mpt_script`      text COMMENT '口播脚本',
  `mpt_terms`       varchar(1000) NOT NULL DEFAULT '' COMMENT '素材关键词(逗号分隔)',
  `mpt_params`      text COMMENT '提交给MPT的参数快照(json)',
  `mpt_remote_id`   varchar(64) NOT NULL DEFAULT '' COMMENT 'MPT 侧 task_id',
  `mpt_progress`    tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '进度0-100',
  `mpt_result_url`  varchar(1024) NOT NULL DEFAULT '' COMMENT '本地化后的视频地址',
  `mpt_error`       varchar(500) NOT NULL DEFAULT '' COMMENT '脱敏后的错误信息',
  `mpt_status`      tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待提交 1已就绪 2生成中 3完成 4失败 5推进中(内部租约态,对站长等同2)',
  `mpt_admin_id`    int(10) unsigned NOT NULL DEFAULT '0',
  `mpt_time_add`    int(10) unsigned NOT NULL DEFAULT '0',
  -- 超时判据的锚点：最近一次成功提交给 MPT 的时刻，每次 submit() 都刷新。
  -- 不能拿 mpt_time_add 当锚点 —— 重试一条昨天的任务时它还是昨天，
  -- 提交出去下一轮就被 TASK_TTL 判成「生成超时」，而远端那条任务还在跑。
  -- 也不能拿 mpt_time_update —— 它每次归还租约都会刷新，超时永远不触发。
  -- 0 表示这一行早于本列存在（存量库刚补上），读取处回落到 mpt_time_add。
  `mpt_time_submit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最近一次提交给MPT的时间(超时判据锚点)',
  `mpt_time_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mpt_id`),
  KEY `idx_status` (`mpt_status`),
  KEY `idx_obj` (`mpt_mid`,`mpt_obj_id`),
  -- TaskRunner::pruneOrphanResults() 每次写回成片都要问一句「这个地址还有别的任务在用吗」，
  -- 没有索引就是一次全表扫。前缀取 191 而不是整列：InnoDB 单列索引上限 767 字节，
  -- utf8 下 255 字符已到顶，取 191 连日后改 utf8mb4 也放得下；
  -- 实际值形如 upload/mpt/20260825-1/<md5>.mp4，五十来个字符，选择性绰绰有余。
  KEY `idx_result` (`mpt_result_url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='AI短视频(MPT)生成任务';
