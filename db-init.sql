-- --------------------------------------------------------
-- 主机:                           103.214.175.86
-- 服务器版本:                        8.0.36 - Source distribution
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  12.12.0.7122
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- 导出 xhbc 的数据库结构
CREATE DATABASE IF NOT EXISTS `xhbc` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `xhbc`;

-- 导出  表 xhbc.agent_apply 结构
CREATE TABLE IF NOT EXISTS `agent_apply` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1测试',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '说明',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态0通过1审核2拒绝',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='代理申请';

-- 数据导出被取消选择。

-- 导出  表 xhbc.agent_bets_plan 结构
CREATE TABLE IF NOT EXISTS `agent_bets_plan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '要求打码量',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0总返水比例1细分返水比例',
  `give_out` tinyint NOT NULL DEFAULT '0' COMMENT '每日0每周1每月2',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水上限',
  `rebate_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '返水比例',
  `live_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '真人返水比例',
  `lottery_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '彩票返水比例',
  `slots_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '电子返水比例',
  `sport_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '体育返水比例',
  `esport_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '电竞返水比例',
  `poker_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '棋牌返水比例',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='代理流水方案';

-- 数据导出被取消选择。

-- 导出  表 xhbc.agent_commission 结构
CREATE TABLE IF NOT EXISTS `agent_commission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '代理id',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '手机号号码',
  `total_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队总盈亏',
  `total_commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队总佣金',
  `commission_surplus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上月结余',
  `commission_justify` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冲正净输赢',
  `commission_blow` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下级总佣金',
  `commission_remain` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '应得收益',
  `commission_rebate_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '返佣比例',
  `commission_actual_rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际返佣',
  `commission_last_month` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上月结余信息',
  `active_member_effective` int NOT NULL DEFAULT '0' COMMENT '有效活跃人数',
  `active_member` int NOT NULL DEFAULT '0' COMMENT '活跃人数',
  `total_bet_count` int NOT NULL DEFAULT '0' COMMENT '总注单量',
  `total_bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总投注',
  `date_time` date NOT NULL COMMENT '时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_send` tinyint NOT NULL DEFAULT '1' COMMENT '0经发送佣金1未发送佣金',
  `send_time` int NOT NULL DEFAULT '0' COMMENT '佣金发送时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '备注',
  `float_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '浮动盈亏',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='代理佣金发放';

-- 数据导出被取消选择。

-- 导出  表 xhbc.agent_commission_log 结构
CREATE TABLE IF NOT EXISTS `agent_commission_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '代理id',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '派发金额',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `admin_id` int NOT NULL DEFAULT '1' COMMENT '管理id',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理名称',
  `valid_user` int NOT NULL DEFAULT '0' COMMENT '有效用户',
  `active_user` int NOT NULL DEFAULT '0' COMMENT '活跃用户',
  `user_bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户投注',
  `user_bets_count` int NOT NULL DEFAULT '0' COMMENT '用户注单数',
  `commission_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '返佣比例',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '系统派发0,手动派发1',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_id` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='代理返佣记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.agent_commission_plan 结构
CREATE TABLE IF NOT EXISTS `agent_commission_plan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `valid` int NOT NULL DEFAULT '0' COMMENT '有效用户',
  `active` int NOT NULL DEFAULT '0' COMMENT '活跃用户',
  `loss` int NOT NULL DEFAULT '0' COMMENT '用户亏损',
  `ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返佣比例',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '系统发放0,手动发放1',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='代理佣金方案';

-- 数据导出被取消选择。

-- 导出  表 xhbc.agent_commission_total 结构
CREATE TABLE IF NOT EXISTS `agent_commission_total` (
  `id` int NOT NULL AUTO_INCREMENT,
  `total_agent` int NOT NULL DEFAULT '0' COMMENT '返佣人数',
  `total_commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返佣金额',
  `total_bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总投注金额',
  `total_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总投注盈亏',
  `total_member` int NOT NULL DEFAULT '0' COMMENT '总投注人数',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `date` date NOT NULL COMMENT '生成月份',
  `total_bet_count` int NOT NULL DEFAULT '0' COMMENT '总注单量',
  `total_commission_real` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总实发返佣',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='盘口佣金数据';

-- 数据导出被取消选择。

-- 导出  表 xhbc.event_apply 结构
CREATE TABLE IF NOT EXISTS `event_apply` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `event_id` int NOT NULL DEFAULT '0' COMMENT '活动id',
  `event_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '活动名称',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0,通过1审核2拒绝',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='活动申请';

-- 数据导出被取消选择。

-- 导出  表 xhbc.event_class 结构
CREATE TABLE IF NOT EXISTS `event_class` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='活动类型';

-- 数据导出被取消选择。

-- 导出  表 xhbc.event_list 结构
CREATE TABLE IF NOT EXISTS `event_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '活动code值',
  `class_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型id',
  `class_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int NOT NULL DEFAULT '0' COMMENT '结束时间',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '钱',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0开启1结束',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='活动列表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.event_log 结构
CREATE TABLE IF NOT EXISTS `event_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `event_id` int NOT NULL DEFAULT '0' COMMENT '活动id',
  `event_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '活动名称',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '活动描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '申请金额',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='活动记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.game 结构
CREATE TABLE IF NOT EXISTS `game` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '编码',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `upper_id` int NOT NULL DEFAULT '0' COMMENT '上游id',
  `upper_name` varchar(50) NOT NULL DEFAULT '' COMMENT '上游名称',
  `pic_h5` varchar(255) NOT NULL DEFAULT '' COMMENT 'h5图片',
  `pic_pc` varchar(255) NOT NULL DEFAULT '' COMMENT 'pc图片',
  `pic_app` varchar(255) NOT NULL DEFAULT '' COMMENT 'app图片',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0开启1关闭',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='游戏列表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.game_class 结构
CREATE TABLE IF NOT EXISTS `game_class` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='游戏分类';

-- 数据导出被取消选择。

-- 导出  表 xhbc.game_log 结构
CREATE TABLE IF NOT EXISTS `game_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `in_bets` tinyint NOT NULL DEFAULT '1' COMMENT '已经统计流水0,1未统计流水',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `real_name` varchar(50) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `top_id` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `top_name` int NOT NULL DEFAULT '0' COMMENT '上级名称',
  `class_id` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏大类id',
  `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏名称',
  `upper_id` varchar(50) NOT NULL DEFAULT '' COMMENT '上级名称id',
  `upper_name` varchar(50) NOT NULL DEFAULT '' COMMENT '上级名称',
  `game_id` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏id',
  `game_name` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏名称',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '投注',
  `bet_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效投注',
  `bet_time` varchar(50) NOT NULL DEFAULT '' COMMENT '投注时间',
  `currency` varchar(50) NOT NULL DEFAULT '' COMMENT '使用货币',
  `settle` tinyint NOT NULL DEFAULT '0' COMMENT '结算状态0:未完成1:已完成2:已取消3:已撤单',
  `win_loss` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '输赢状态',
  `desc` text NOT NULL COMMENT '下注内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_no` (`order_no`),
  KEY `top_id` (`top_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='游戏记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.game_quota 结构
CREATE TABLE IF NOT EXISTS `game_quota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `all` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '通用分',
  `ag` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ag',
  `ags` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ags',
  `allbet` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'allbet',
  `allbets` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'allbets',
  `ap` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ap',
  `as` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'as',
  `avia` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'avia',
  `bbin` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'bbin',
  `bg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'bg',
  `boya` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'boya',
  `cmd` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'cmd',
  `cq9` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'cq9',
  `cq9s` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'cq9s',
  `cr` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'cr',
  `crown` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'crown',
  `db1` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db1',
  `db2` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db2',
  `db3` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db3',
  `db5` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db5',
  `db6` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db6',
  `db7` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'db7',
  `dg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'dg',
  `esb` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'esb',
  `evo` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'evo',
  `fb` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'fb',
  `fc` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'fc',
  `fg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'fg',
  `ig` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ig',
  `im` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'im',
  `jdb` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'jdb',
  `joker` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'joker',
  `ky` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ky',
  `leg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'leg',
  `lgd` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'lgd',
  `mg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'mg',
  `mt` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'mt',
  `mw` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'mw',
  `newbb` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'newbb',
  `nw` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'nw',
  `og` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'og',
  `panda` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'panda',
  `pg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'pg',
  `pgs` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'pgs',
  `png` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'png',
  `pp` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'pp',
  `pt` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'pt',
  `rsg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'rsg',
  `saba` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'saba',
  `sexy` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'sexy',
  `sg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'sg',
  `sgwin` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'sgwin',
  `ss` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ss',
  `tcg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'tcg',
  `tf` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'tf',
  `v8` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'v8',
  `vg` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'vg',
  `vr` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'vr',
  `we` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'we',
  `wl` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'wl',
  `wm` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'wm',
  `ww` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ww',
  `xgd` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'xgd',
  `xj` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'xj',
  `yoo` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'yoo',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='游戏额度';

-- 数据导出被取消选择。

-- 导出  表 xhbc.game_upper 结构
CREATE TABLE IF NOT EXISTS `game_upper` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '编码',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1关闭',
  `hot` tinyint NOT NULL DEFAULT '0' COMMENT '是否热门0正常1热门',
  `fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '场馆费',
  `pic_pc_nav` varchar(255) NOT NULL DEFAULT '' COMMENT 'pc导航图片',
  `pic_pc_list` varchar(255) NOT NULL DEFAULT '' COMMENT 'pc列表图片',
  `pic_h5` varchar(255) NOT NULL DEFAULT '' COMMENT 'h5,app图片',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='游戏上游';

-- 数据导出被取消选择。

-- 导出  表 xhbc.level 结构
CREATE TABLE IF NOT EXISTS `level` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水上限',
  `rebate_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '返水比例（%）',
  `handling_fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `kickback` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '亏损返点',
  `kickback_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '亏损返点（%）',
  `birthday_bonus` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '生日奖金',
  `level_up_bonus` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '升级奖金',
  `level_up_recharge` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '升级充值',
  `level_keep_recharge` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '保级充值',
  `level_up_bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '升级流水',
  `level_keep_bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '保级流水',
  `withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日提款限额',
  `withdraw_num` int NOT NULL DEFAULT '0' COMMENT '单日提款次数',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户等级表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.level_log 结构
CREATE TABLE IF NOT EXISTS `level_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当前充值',
  `bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '前期流水',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '类型0升级1降级',
  `lv_before` tinyint NOT NULL DEFAULT '0' COMMENT '等级变化前ID',
  `lv_before_name` varchar(50) NOT NULL DEFAULT '' COMMENT '等级变化前名称',
  `lv_after` tinyint NOT NULL DEFAULT '0' COMMENT '等级变化后ID',
  `lv_after_name` varchar(50) NOT NULL DEFAULT '' COMMENT '等级变化后名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '等级描述',
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '管理员id',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户升级记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.level_risk 结构
CREATE TABLE IF NOT EXISTS `level_risk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `big_bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '大额注单',
  `big_bet_live` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '真人大额注单',
  `big_bet_lottery` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '彩票大额注单',
  `big_bet_sport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '体育大额注单',
  `big_bet_esport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '电竞大额注单',
  `big_bet_slots` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '电子大额注单',
  `big_bet_poker` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '棋牌大额注单',
  `user_earn` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户盈利',
  `user_earn_live` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户真人盈利',
  `user_earn_lottery` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户彩票盈利',
  `user_earn_sport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户体育盈利',
  `user_earn_esport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电竞盈利',
  `user_earn_slots` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电子盈利',
  `user_earn_poker` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户棋牌盈利',
  `user_loss` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户亏损',
  `user_loss_live` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户真人亏损',
  `user_loss_lottery` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户彩票亏损',
  `user_loss_sport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户体育亏损',
  `user_loss_esport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电竞亏损',
  `user_loss_slots` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电子亏损',
  `user_loss_poker` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户棋牌亏损',
  `user_win` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户高胜率',
  `user_win_live` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户真人高胜率',
  `user_win_lottery` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户彩票高胜率',
  `user_win_sport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户体育高胜率',
  `user_win_esport` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电竞高胜率',
  `user_win_slots` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户电子高胜率',
  `user_win_poker` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户棋牌高胜率',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='等级风控配置';

-- 数据导出被取消选择。

-- 导出  表 xhbc.market_channel 结构
CREATE TABLE IF NOT EXISTS `market_channel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `title` (`title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='市场渠道';

-- 数据导出被取消选择。

-- 导出  表 xhbc.market_domain 结构
CREATE TABLE IF NOT EXISTS `market_domain` (
  `id` int NOT NULL AUTO_INCREMENT,
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '域名',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `channl_id` int NOT NULL DEFAULT '0' COMMENT '渠道id',
  `channl_name` varchar(50) NOT NULL DEFAULT '' COMMENT '渠道名称',
  `agent_id` varchar(50) NOT NULL DEFAULT '' COMMENT '代理id',
  `agent_name` varchar(50) NOT NULL DEFAULT '' COMMENT '代理名称',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0开启1关闭2,官网',
  `visit` int NOT NULL DEFAULT '0' COMMENT '访问次数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `title` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='市场域名';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_article 结构
CREATE TABLE IF NOT EXISTS `ops_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '文章标识',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `content` text COMMENT '内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '其他0,公告1,新闻2,博客3,宣传页4',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='文章表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_bank 结构
CREATE TABLE IF NOT EXISTS `ops_bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0开启1关闭',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='银行';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_banner 结构
CREATE TABLE IF NOT EXISTS `ops_banner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1,关闭',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int NOT NULL DEFAULT '0' COMMENT '结束时间',
  `levels` set('0','1','2','3','4','5','6','7','8','9','10') NOT NULL DEFAULT '0' COMMENT '用户等级0表示全部',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='横幅表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_email_code 结构
CREATE TABLE IF NOT EXISTS `ops_email_code` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '验证码',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='邮箱验证码';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_feedback 结构
CREATE TABLE IF NOT EXISTS `ops_feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `phone` int NOT NULL DEFAULT '0' COMMENT '手机号码',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0,正常1测试',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户描述',
  `img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `type` int NOT NULL DEFAULT '0' COMMENT '0其他,1充值,2提现,3活动,4返水,5返佣',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `information` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '消息回复',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户反馈';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_goods 结构
CREATE TABLE IF NOT EXISTS `ops_goods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL DEFAULT '0' COMMENT '类型id',
  `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '类型名称',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片',
  `content` text COMMENT '商品介绍',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='商品表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_goods_class 结构
CREATE TABLE IF NOT EXISTS `ops_goods_class` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='商品类型表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_goods_order 结构
CREATE TABLE IF NOT EXISTS `ops_goods_order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片',
  `goods_id` int NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_title` varchar(50) NOT NULL DEFAULT '' COMMENT '商品名称',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `deliver_title` varchar(255) NOT NULL DEFAULT '' COMMENT '发货名称',
  `deliver_order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '发货单号',
  `deliver_time` int NOT NULL DEFAULT '0' COMMENT '发货时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0待发货1已发货',
  `address_name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `address_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
  `address_city` varchar(255) NOT NULL DEFAULT '' COMMENT '市区',
  `address_place` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='商品记录表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_notice 结构
CREATE TABLE IF NOT EXISTS `ops_notice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1,关闭',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int NOT NULL DEFAULT '0' COMMENT '结束时间',
  `levels` set('0','1','2','3','4','5','6','7','8','9','10') NOT NULL DEFAULT '0' COMMENT '用户等级0表示全部',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='公告表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_popup 结构
CREATE TABLE IF NOT EXISTS `ops_popup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1,关闭',
  `start_time` int NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int NOT NULL DEFAULT '0' COMMENT '结束时间',
  `levels` set('0','1','2','3','4','5','6','7','8','9','10') NOT NULL DEFAULT '0' COMMENT '用户等级0表示全部',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='弹窗表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_question 结构
CREATE TABLE IF NOT EXISTS `ops_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '问答标题',
  `content` text NOT NULL COMMENT '问答内容',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '问答类型',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='问题列表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_raffle 结构
CREATE TABLE IF NOT EXISTS `ops_raffle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '标题',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `chance` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '概率',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `goods_id` int NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品标题',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='抽奖表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_raffle_log 结构
CREATE TABLE IF NOT EXISTS `ops_raffle_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `raffle_id` int NOT NULL DEFAULT '0' COMMENT '抽奖id',
  `raffle_name` varchar(50) NOT NULL DEFAULT '' COMMENT '抽奖产品名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '图片',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='抽奖记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_sign_in 结构
CREATE TABLE IF NOT EXISTS `ops_sign_in` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `amount` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户签到表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.ops_sms_code 结构
CREATE TABLE IF NOT EXISTS `ops_sms_code` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '验证码',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户短信';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_account 结构
CREATE TABLE IF NOT EXISTS `pay_account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0开启1关闭',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0银行卡1数字货币2支付宝3微信',
  `show` tinyint NOT NULL DEFAULT '0' COMMENT '0账号1图片2图片加账号',
  `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_branch` varchar(60) NOT NULL DEFAULT '' COMMENT '银行支行',
  `bank_account` varchar(30) NOT NULL DEFAULT '' COMMENT '银行账号',
  `coin_name` varchar(30) NOT NULL DEFAULT '' COMMENT '币名称',
  `coin_blockchain` varchar(30) NOT NULL DEFAULT '' COMMENT '币区块链',
  `coin_account` varchar(50) NOT NULL DEFAULT '' COMMENT '币账号',
  `alipay_account` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '收款码',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
  `admin_id` tinyint NOT NULL DEFAULT '0' COMMENT '管理id',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='支付账号';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_bank 结构
CREATE TABLE IF NOT EXISTS `pay_bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `status` tinyint NOT NULL DEFAULT (0) COMMENT '0开启1关闭',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='支付渠道银行列表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_channel 结构
CREATE TABLE IF NOT EXISTS `pay_channel` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
  `code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '通道代码',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0正常1关闭',
  `upper` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游代码',
  `upper_id` tinyint NOT NULL DEFAULT '0' COMMENT '上游id',
  `upper_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
  `upper_status` tinyint NOT NULL DEFAULT '0' COMMENT '上游状态0正常1关闭',
  `upper_img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游图片',
  `class_id` tinyint NOT NULL DEFAULT '0' COMMENT '类型id',
  `class_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `class_status` tinyint NOT NULL DEFAULT '0' COMMENT '类型状态0正常1关闭',
  `class_img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
  `class_rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '类型汇率',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `payment_time` int NOT NULL DEFAULT '0' COMMENT '支付时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `handle_fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费率',
  `amt_min` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最小充值',
  `amt_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最大充值',
  `day_amt` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '今日渠道收款',
  `day_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '今日最大充值',
  `style` tinyint NOT NULL DEFAULT '0' COMMENT '0框架,1跳转,2内置账号',
  `accounts` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '绑定支付账号1,2,3',
  `levels` set('0','1','2','3','4','5','6','7','8','9','10') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '支持的等级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='支付通道';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_class 结构
CREATE TABLE IF NOT EXISTS `pay_class` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
  `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0,正常1关闭',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '支付类型',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='支付类型';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_payout 结构
CREATE TABLE IF NOT EXISTS `pay_payout` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
  `code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '通道代码',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0正常1关闭',
  `upper` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游代码',
  `upper_id` tinyint NOT NULL DEFAULT '0' COMMENT '上游id',
  `upper_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
  `upper_status` tinyint NOT NULL DEFAULT '0' COMMENT '上游状态',
  `upper_img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游图片',
  `class_id` tinyint NOT NULL DEFAULT '0' COMMENT '类型id',
  `class_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `class_img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
  `class_status` tinyint NOT NULL DEFAULT '0' COMMENT '类型状态',
  `class_rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '类型汇率',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小充值',
  `max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最大充值',
  `quota` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '代付限额(每日)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='代付通道';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_quota 结构
CREATE TABLE IF NOT EXISTS `pay_quota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
  `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0,正常1关闭',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='支付三方额度';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_recharge 结构
CREATE TABLE IF NOT EXISTS `pay_recharge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0,正常1测试',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0成功1审核2失败',
  `channel_id` tinyint NOT NULL DEFAULT '0' COMMENT '渠道id',
  `channel_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道名称',
  `class_id` int NOT NULL DEFAULT '0' COMMENT '渠道类型ID',
  `class_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道类型',
  `account_id` tinyint NOT NULL DEFAULT '0' COMMENT '关联的账号id',
  `order_no` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `account` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '付款地址',
  `img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '支付图片',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '支付用户名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `amount_real` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际金额',
  `fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `fee_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费率',
  `remark` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
  `style` tinyint NOT NULL DEFAULT '1' COMMENT '0框架,1跳转,2内置账号',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `order_no` (`order_no`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='充值记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_upper 结构
CREATE TABLE IF NOT EXISTS `pay_upper` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
  `code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '上游代号',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态0正常1关闭',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='支付上游';

-- 数据导出被取消选择。

-- 导出  表 xhbc.pay_withdraw 结构
CREATE TABLE IF NOT EXISTS `pay_withdraw` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单id',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0,成功,1,待处理,2,处理中,3失败,4,已拒绝5,已取消',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '0银行卡1数字货币2支付宝3微信',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '姓名',
  `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_branch` varchar(60) NOT NULL DEFAULT '' COMMENT '银行支行',
  `bank_account` varchar(30) NOT NULL DEFAULT '' COMMENT '银行账号',
  `coin_name` varchar(30) NOT NULL DEFAULT '' COMMENT '币名称',
  `coin_blockchain` varchar(30) NOT NULL DEFAULT '' COMMENT '币区块链',
  `coin_account` varchar(50) NOT NULL DEFAULT '' COMMENT '币账号',
  `alipay_account` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `alipay_img` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝收款',
  `wx_img` varchar(50) NOT NULL DEFAULT '' COMMENT '微信收款码',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `amount_real` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际金额',
  `exchange_rate` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
  `handling_fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `handling_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费率',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `channel_id` int NOT NULL DEFAULT '0' COMMENT '代付渠道id',
  `channel_name` varchar(50) NOT NULL DEFAULT '' COMMENT '代付渠道名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `order_no` (`order_no`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='提现记录';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_agent_daily 结构
CREATE TABLE IF NOT EXISTS `report_agent_daily` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '会员id',
  `date_time` date NOT NULL COMMENT '时间',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '手机号码',
  `total_rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总返水',
  `total_event` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总活动',
  `total_welfare` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总福利',
  `total_venue_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总场馆费',
  `total_bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总投注',
  `total_bet_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总有效投注',
  `total_bet_count` int NOT NULL DEFAULT '0' COMMENT '总注单量',
  `total_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总盈利',
  `total_recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总充值',
  `total_withdrawal` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总提现',
  `total_recharge_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总充值手续费',
  `total_withdrawal_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总提现手续费',
  `total_commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队总佣金',
  `commission_surplus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上月结余',
  `commission_justify` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冲正净输赢',
  `commission_blow` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下级总佣金',
  `commission_remain` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '应得收益',
  `active_member` int NOT NULL DEFAULT '0' COMMENT '活跃人数',
  `active_member_effective` int NOT NULL DEFAULT '0' COMMENT '有效活跃人数',
  `commission_rebate_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '返佣比例',
  `active_user` int NOT NULL DEFAULT '0' COMMENT '当天活跃会员',
  `bet_user` int NOT NULL DEFAULT '0' COMMENT '当天投注用户',
  `register_user` int NOT NULL DEFAULT '0' COMMENT '当天注册用户',
  `deposit_user` int NOT NULL DEFAULT '0' COMMENT '当天充值用户',
  `first_deposit_user` int NOT NULL DEFAULT '0' COMMENT '当天首存用户',
  `recharge_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当天充值金额',
  `withdraw_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当天提款金额',
  `is_deducted` tinyint NOT NULL DEFAULT '1' COMMENT '0已经扣除下级佣金1未扣除',
  `is_update` tinyint NOT NULL DEFAULT '1' COMMENT '0已经更新到月报1未更新',
  `float_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '浮动盈亏',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='代理每日统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_finance 结构
CREATE TABLE IF NOT EXISTS `report_finance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_time` date NOT NULL COMMENT '日期',
  `recharge_by_user` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户充值统计',
  `recharge_by_admin` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台充值统计',
  `withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现统计',
  `recharge_gift` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值奖励',
  `first_recharge_gift` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户首冲',
  `return_water` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户返水',
  `transfe_in` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '转入金额',
  `transfe_out` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '转出金额',
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返佣金额',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='账变统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_handicap 结构
CREATE TABLE IF NOT EXISTS `report_handicap` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_time` date NOT NULL COMMENT '日期',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值',
  `withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现',
  `user_valid` int NOT NULL DEFAULT '0' COMMENT '有效用户',
  `user_active` int NOT NULL DEFAULT '0' COMMENT '活跃用户',
  `user_register` int NOT NULL DEFAULT '0' COMMENT '注册用户',
  `bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '投注量',
  `bet_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效投注',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总返水',
  `welfare` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总福利',
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '公司盈利',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='盘口报表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_pay_channal 结构
CREATE TABLE IF NOT EXISTS `report_pay_channal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pay_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '支付名称',
  `pay_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '支付id',
  `pay_type` tinyint NOT NULL DEFAULT '0' COMMENT '0支付1代付',
  `success` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成功金额',
  `success_count` int NOT NULL DEFAULT '0' COMMENT '成功次数',
  `fai_count` int NOT NULL DEFAULT '0' COMMENT '失败次数',
  `fail` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '失败金额',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `date_time` date NOT NULL COMMENT '日期',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='三方渠道统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_pay_recharge 结构
CREATE TABLE IF NOT EXISTS `report_pay_recharge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_time` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '当前日期',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `success` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成功金额',
  `success_count` int NOT NULL DEFAULT '0' COMMENT '成功次数',
  `fail` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '失败金额',
  `fai_count` int NOT NULL DEFAULT '0' COMMENT '失败次数',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `date_time` (`date_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='每日充值统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_pay_withdraw 结构
CREATE TABLE IF NOT EXISTS `report_pay_withdraw` (
  `id` int NOT NULL AUTO_INCREMENT,
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `success` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成功金额',
  `success_count` int NOT NULL DEFAULT '0' COMMENT '成功次数',
  `fail` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '失败金额',
  `fai_count` int NOT NULL DEFAULT '0' COMMENT '失败次数',
  `date_time` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='每日提现统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_user_daily 结构
CREATE TABLE IF NOT EXISTS `report_user_daily` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `date_time` date NOT NULL COMMENT '日期',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值',
  `recharge_count` int NOT NULL DEFAULT '0' COMMENT '充值次数',
  `recharge_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值手续费',
  `withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现',
  `withdraw_count` int NOT NULL DEFAULT '0' COMMENT '提现次数',
  `withdraw_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '投注量',
  `bet_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效投注',
  `bet_count` int NOT NULL DEFAULT '0' COMMENT '总注单数',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总返水',
  `event` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总活动',
  `welfare` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总福利',
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总盈利',
  `company_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '公司盈利',
  `venue_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总场馆费用',
  `admin_incr` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台上分',
  `admin_decr` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台下分',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户每日报表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.report_user_month 结构
CREATE TABLE IF NOT EXISTS `report_user_month` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `date_time` date NOT NULL COMMENT '日期',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值',
  `recharge_count` int NOT NULL DEFAULT '0' COMMENT '充值次数',
  `recharge_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值手续费',
  `withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现',
  `withdraw_count` int NOT NULL DEFAULT '0' COMMENT '提现次数',
  `withdraw_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `bet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '投注量',
  `bet_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效投注',
  `bet_count` int NOT NULL DEFAULT '0' COMMENT '总注单数',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总返水',
  `event` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总活动',
  `welfare` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总福利',
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总盈利',
  `company_profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '公司盈利',
  `venue_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总场馆费用',
  `admin_incr` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台上分',
  `admin_decr` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台下分',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户每月报表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_block_area 结构
CREATE TABLE IF NOT EXISTS `risk_block_area` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控区域屏蔽';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_block_device 结构
CREATE TABLE IF NOT EXISTS `risk_block_device` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控设备屏蔽';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_block_ip 结构
CREATE TABLE IF NOT EXISTS `risk_block_ip` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控ip屏蔽';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_different_place 结构
CREATE TABLE IF NOT EXISTS `risk_different_place` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip',
  `ip_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP地址',
  `country` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '省份',
  `area` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '地区',
  `http_user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入时候ua头',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `username` (`username`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控异地登入';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_same_device 结构
CREATE TABLE IF NOT EXISTS `risk_same_device` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip',
  `ip_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP地址',
  `device` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '设备',
  `http_user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '操作时候ua头',
  `is_mobile` tinyint NOT NULL DEFAULT '0' COMMENT '是否是手机0电脑1手机',
  `connection` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联用户',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `action` tinyint NOT NULL DEFAULT '0' COMMENT '操作0,登入1彩金2提现3充值4转账',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `connection` (`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控同设备';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_same_ip 结构
CREATE TABLE IF NOT EXISTS `risk_same_ip` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip',
  `ip_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP地址',
  `http_user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入时候ua头',
  `connection` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联用户',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `action` tinyint NOT NULL DEFAULT '0' COMMENT '操作0,登入1彩金2提现3充值4转账',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `connection` (`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控同IP';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_same_pwd 结构
CREATE TABLE IF NOT EXISTS `risk_same_pwd` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `connection` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联用户',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `action` tinyint NOT NULL DEFAULT '0' COMMENT '操作0,后台1,注册2,修改密码',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `connection` (`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控同密码用户';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_user_bets 结构
CREATE TABLE IF NOT EXISTS `risk_user_bets` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '等级',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `is_win` tinyint NOT NULL DEFAULT '0' COMMENT '是否赢1,0输',
  `bets` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联注单',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `bets` (`bets`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控用户注单(大额)';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_user_earn 结构
CREATE TABLE IF NOT EXISTS `risk_user_earn` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '等级',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户盈利',
  `bets` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联注单',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `bets` (`bets`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控用户盈利';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_user_loss 结构
CREATE TABLE IF NOT EXISTS `risk_user_loss` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '等级',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户盈利',
  `bets` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联注单',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `bets` (`bets`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控用户亏损';

-- 数据导出被取消选择。

-- 导出  表 xhbc.risk_user_win 结构
CREATE TABLE IF NOT EXISTS `risk_user_win` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '等级',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `desc` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户高胜率',
  `bets` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '关联注单',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  FULLTEXT KEY `bets` (`bets`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='风控用户高胜率';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_dict 结构
CREATE TABLE IF NOT EXISTS `sys_dict` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dict_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字典类型，如 sex、order_status、site_config',
  `dict_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字典键',
  `value_type` tinyint NOT NULL DEFAULT '0' COMMENT '值类型：0=string 1=int 2=float 3=json 4=array 5=enum 6=text',
  `value_string` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'string 类型，或 text 类型的前端编辑内容',
  `value_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '当 value_type=6 时使用，用于大文本',
  `value_int` int DEFAULT NULL COMMENT 'int 类型',
  `value_float` decimal(16,2) DEFAULT NULL COMMENT 'float 类型',
  `value_json` json DEFAULT NULL COMMENT 'json/array/enum 类型字段（当前选中的值）',
  `enum_limit` json DEFAULT NULL COMMENT '枚举可选值',
  `label` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '展示名称',
  `sort` int NOT NULL DEFAULT '0' COMMENT '99最靠前',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态:0启用 1禁用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`dict_type`),
  KEY `idx_type_key` (`dict_type`,`dict_key`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统字典表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_file 结构
CREATE TABLE IF NOT EXISTS `sys_file` (
  `id` int NOT NULL DEFAULT (0) COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原始文件名',
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问地址',
  `size` bigint NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件MD5',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件类型 image/video/file',
  `mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'MIME类型',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间(时间戳)',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间(时间戳)',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_md5` (`md5`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件存储表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_log 结构
CREATE TABLE IF NOT EXISTS `sys_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '管理账号',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '请求网址',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `params` text COMMENT '请求参数',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1登录 2操作',
  `ip` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip',
  `ip_address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`,`admin_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='后台日志表'
/*!50100 PARTITION BY HASH (`admin_id`)
PARTITIONS 32 */;

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_perm 结构
CREATE TABLE IF NOT EXISTS `sys_perm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `code` varchar(200) NOT NULL DEFAULT '' COMMENT '权限标识（前端）',
  `api` varchar(255) DEFAULT '' COMMENT '接口路径',
  `method` varchar(10) DEFAULT '' COMMENT '请求方法',
  `pid` int DEFAULT '0' COMMENT '父级ID',
  `type` tinyint(1) DEFAULT '1' COMMENT '类型：1=菜单，2=按钮，3=接口',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `sort` int DEFAULT '0' COMMENT '排序',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='菜单权限表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_role 结构
CREATE TABLE IF NOT EXISTS `sys_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '角色标识（唯一）',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '角色说明',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_role_perm 结构
CREATE TABLE IF NOT EXISTS `sys_role_perm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL DEFAULT '0' COMMENT '角色ID',
  `perm_id` int NOT NULL DEFAULT '0' COMMENT '权限ID',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_permission` (`role_id`,`perm_id`) USING BTREE,
  KEY `idx_role_id` (`role_id`),
  KEY `idx_permission_id` (`perm_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色-权限关系表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_user 结构
CREATE TABLE IF NOT EXISTS `sys_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT 'email',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'token',
  `role` tinyint NOT NULL DEFAULT '0' COMMENT '0超级管理1.普通管理',
  `role_id` tinyint NOT NULL DEFAULT '0' COMMENT '角色id',
  `role_name` tinyint NOT NULL DEFAULT '0' COMMENT '角色名称',
  `clock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未冻结1.已冻结',
  `login_time` int NOT NULL DEFAULT '0' COMMENT '登入时间',
  `login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登入IP',
  `ip_address` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `login_agent` varchar(255) NOT NULL DEFAULT '' COMMENT '登入ui头',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='后台管理员表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_user_log 结构
CREATE TABLE IF NOT EXISTS `sys_user_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '管理id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `login_ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入ip',
  `ip_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip 地址',
  `login_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入代理',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`,`uid`),
  KEY `uid` (`uid`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='系统管理登入日志表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 数据导出被取消选择。

-- 导出  表 xhbc.sys_user_role 结构
CREATE TABLE IF NOT EXISTS `sys_user_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `role_id` int NOT NULL DEFAULT '0' COMMENT '角色ID',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='管理-角色关系表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user 结构
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户',
  `nickname` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '密码',
  `pin` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '支付密码',
  `birthday` date NOT NULL COMMENT '用户生日',
  `birthday_gift_time` int NOT NULL DEFAULT '0' COMMENT '生日领取时间',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `token` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '登入token',
  `invite` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '邀请码',
  `sfz_name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '实名姓名',
  `sfz_number` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '实名账号',
  `sfz_img` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '实名图片',
  `avatar` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `level` int NOT NULL DEFAULT '0' COMMENT '会员等级',
  `level_change_time` int NOT NULL DEFAULT '0' COMMENT '会员等级改变时间',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '会员等级名称',
  `role_level` tinyint NOT NULL DEFAULT '0' COMMENT '0会员1代理2团长3总代4股东',
  `login_time` int NOT NULL DEFAULT '0' COMMENT '登入时间',
  `login_ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '登入ip',
  `ip_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '登入地址',
  `register_ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '注册ip',
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `admin_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '管理员名称',
  `top_id` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `top_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '上级名称',
  `plan_commission` tinyint NOT NULL DEFAULT '1' COMMENT '返佣方案',
  `plan_rebate` tinyint NOT NULL DEFAULT '1' COMMENT '返水方案',
  `tags` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户标签',
  `group_id` int NOT NULL DEFAULT (0) COMMENT '分组id',
  `group_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '分组名称',
  `channel_id` int NOT NULL DEFAULT '0' COMMENT '渠道id',
  `channel_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '渠道名称',
  `remark` varchar(50) NOT NULL DEFAULT '' COMMENT '用户备注',
  `money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '可用余额',
  `frozen_money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `yuebao` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝金额',
  `points` int NOT NULL DEFAULT '0' COMMENT '积分',
  `raffle` int NOT NULL DEFAULT '0' COMMENT '抽奖次数',
  `first_recharge_money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充余额',
  `first_recharge_time` int NOT NULL DEFAULT '0' COMMENT '首充时间',
  `recharge_time` int NOT NULL DEFAULT '0' COMMENT '充值时间',
  `withdraw_time` int NOT NULL DEFAULT '0' COMMENT '提现时间',
  `signin_time` int NOT NULL DEFAULT '0' COMMENT '签到时间',
  `online_time` int NOT NULL DEFAULT '0' COMMENT '在线时间',
  `yuebao_time` int NOT NULL DEFAULT '0' COMMENT '余额宝时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT (now()) COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `ban_buy` tinyint NOT NULL DEFAULT '0' COMMENT '禁止购买',
  `ban_lock` tinyint NOT NULL DEFAULT '0' COMMENT '用户冻结',
  `ban_sigin` tinyint NOT NULL DEFAULT '0' COMMENT '禁止签到',
  `ban_raffle` tinyint NOT NULL DEFAULT '0' COMMENT '禁止抽奖',
  `ban_login` tinyint NOT NULL DEFAULT '0' COMMENT '禁止登入',
  `ban_invite` tinyint NOT NULL DEFAULT '0' COMMENT '禁止邀请',
  `ban_recharge` tinyint NOT NULL DEFAULT '0' COMMENT '禁止充值',
  `ban_withdraw` tinyint NOT NULL DEFAULT '0' COMMENT '禁止提现',
  `ban_exchange` tinyint NOT NULL DEFAULT '0' COMMENT '禁止兑换',
  `ban_bets` tinyint NOT NULL DEFAULT '0' COMMENT '禁止游戏0,正常,1禁止',
  `ban_transfer` tinyint NOT NULL DEFAULT '0' COMMENT '禁止转账0,正常,1禁止',
  `sfz_status` tinyint NOT NULL DEFAULT '1' COMMENT '身份证认证,0正常,1未实名,2审核中',
  `kick_out` tinyint NOT NULL DEFAULT '0' COMMENT '踢用户下线0,正常,1禁止',
  `is_valid_user` tinyint NOT NULL DEFAULT '1' COMMENT '是否有效用户',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '0正常1测试',
  `is_bets` tinyint NOT NULL DEFAULT '0' COMMENT '是否自动转账0,不需要,1需要',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_address 结构
CREATE TABLE IF NOT EXISTS `user_address` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `address_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `address_phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `address_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '省市区',
  `address_place` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '地址',
  `default` tinyint NOT NULL DEFAULT '1' COMMENT '是否默认 0默认1不默认',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT (now()) COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`,`uid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户地址表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_bank 结构
CREATE TABLE IF NOT EXISTS `user_bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0银行卡1数字货币2支付宝3微信',
  `default` tinyint NOT NULL DEFAULT '0' COMMENT '是否默认0否1是',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_branch` varchar(60) NOT NULL DEFAULT '' COMMENT '银行支行',
  `bank_account` varchar(50) NOT NULL DEFAULT '' COMMENT '银行账号',
  `coin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '币-名称',
  `coin_blockchain` varchar(50) NOT NULL DEFAULT '' COMMENT '币-区块链',
  `coin_account` varchar(100) NOT NULL DEFAULT '' COMMENT '币-账号',
  `alipay_account` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `alipay_img` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝收款码',
  `wx_img` varchar(50) NOT NULL DEFAULT '' COMMENT '微信收款码',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户银行表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_bets 结构
CREATE TABLE IF NOT EXISTS `user_bets` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户流水',
  `over_loss` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '剩余盈亏',
  `over_loss_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '发生的订单号',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户流水信息';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_bets_api 结构
CREATE TABLE IF NOT EXISTS `user_bets_api` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `ng_reg` tinyint NOT NULL DEFAULT '1' COMMENT 'ng接口注册状态0已经注册1没有注册',
  `ng_api` tinyint NOT NULL DEFAULT '1' COMMENT 'ng接口权限状态0正常1维护',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户三方接口';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_bets_log 结构
CREATE TABLE IF NOT EXISTS `user_bets_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `order_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '订单id',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `before` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变化前余额',
  `after` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变化后余额',
  `desc` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '账变详情',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='流水日志表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_info 结构
CREATE TABLE IF NOT EXISTS `user_info` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `recharge_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值金额',
  `recharge_num` int NOT NULL DEFAULT '0' COMMENT '累计充值次数',
  `withdraw_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现金额',
  `withdraw_num` int NOT NULL DEFAULT '0' COMMENT '累计提现次数',
  `signin_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到金额',
  `rebate_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户返水金额',
  `compensate_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户包赔金额',
  `compensate_num` int NOT NULL DEFAULT '0' COMMENT '用户包赔次数',
  `rebate_num` int NOT NULL DEFAULT '0' COMMENT '用户返水金额',
  `commission_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户佣金金额',
  `commission_num` int NOT NULL DEFAULT '0' COMMENT '用户佣金次数',
  `bonus_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户彩金金额',
  `bonus_num` int NOT NULL DEFAULT '0' COMMENT '用户彩金次数',
  `signin_num` int NOT NULL DEFAULT '0' COMMENT '用户签到次数',
  `reffle_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
  `raffle_num` int NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
  `team_event` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队活动金额',
  `team_bonus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队福利金额',
  `team_earn` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队负盈利',
  `team_profit_loss` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队总输赢',
  `team_bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队投注金额',
  `team_bets_valid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队有效投注金额',
  `team_user` int NOT NULL DEFAULT '0' COMMENT '团队人数',
  `team_agent` int NOT NULL DEFAULT '0' COMMENT '团队代理人数',
  `team_rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队返水',
  `team_rebate_num` int NOT NULL DEFAULT '0' COMMENT '团队返水次数',
  `team_recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
  `team_withdraw` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队提现金额',
  `user_points` int NOT NULL DEFAULT '0' COMMENT '用户积分',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户统计';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_label 结构
CREATE TABLE IF NOT EXISTS `user_label` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间(时间戳)',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间(时间戳)',
  `create_at` timestamp NOT NULL DEFAULT (now()) COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户标签';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_label_assist 结构
CREATE TABLE IF NOT EXISTS `user_label_assist` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户表自增ID',
  `label_id` int NOT NULL DEFAULT '0' COMMENT '标签ID',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间(时间戳)',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间(时间戳)',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`,`uid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_label_id` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户标签辅助表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_level 结构
CREATE TABLE IF NOT EXISTS `user_level` (
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `level_bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级流水',
  `level_recharge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级充值',
  `level_change_time` int NOT NULL DEFAULT '0' COMMENT '等级修改时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户等级信息';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_login 结构
CREATE TABLE IF NOT EXISTS `user_login` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入IP',
  `ip_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip地址',
  `country` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '省份',
  `area` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '地区',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '测试账号0正常1测试',
  `http_user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '登入时候的ui头',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `phone` (`phone`),
  KEY `ip` (`ip`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户登入';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_message 结构
CREATE TABLE IF NOT EXISTS `user_message` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '测试账号0正常1测试',
  `is_system` tinyint NOT NULL DEFAULT '0' COMMENT '是否系统发送0系统1手动',
  `admin_id` int NOT NULL DEFAULT '0' COMMENT '后台管理id',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '后台管理名称',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `view_time` int NOT NULL DEFAULT '0' COMMENT '查看时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户站内信';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_money_class 结构
CREATE TABLE IF NOT EXISTS `user_money_class` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '账变id',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '账变名称',
  `style` tinyint NOT NULL DEFAULT '0' COMMENT '账变0,加1减',
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '类型',
  `multiple` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '流水倍数',
  `set_up` tinyint NOT NULL DEFAULT '0' COMMENT '0不可设置流水,1可设置流水',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='账变类型表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_money_log 结构
CREATE TABLE IF NOT EXISTS `user_money_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `order_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '订单id',
  `class_id` tinyint NOT NULL DEFAULT '0' COMMENT '账变id',
  `class_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '账变名称',
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `before` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '变化前余额',
  `is_finish` tinyint NOT NULL DEFAULT '0' COMMENT '是否结束0完结1未完结2超额',
  `after` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '变化后余额',
  `bets` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '产生流水',
  `desc` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '账变详情',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=900 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='账变日志表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_money_transfer 结构
CREATE TABLE IF NOT EXISTS `user_money_transfer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `order_id` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '订单id',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `style` tinyint NOT NULL DEFAULT '0' COMMENT '0减少1增加',
  `desc` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '账变详情',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户转账';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_real_name 结构
CREATE TABLE IF NOT EXISTS `user_real_name` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `sfz_name` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证名称',
  `sfz_number` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证id',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0通过1审核,2拒绝',
  `bank_account` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '银行账号',
  `bank_branch` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '银行支行',
  `bank_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '银行名称',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='实名记录表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_rebate 结构
CREATE TABLE IF NOT EXISTS `user_rebate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `level` int NOT NULL DEFAULT '0' COMMENT '等级id',
  `level_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `bets` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '投注额度',
  `rebate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水额度',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='用户返水列表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_relation 结构
CREATE TABLE IF NOT EXISTS `user_relation` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `level` tinyint NOT NULL DEFAULT '0' COMMENT '层级',
  `top_id` int NOT NULL DEFAULT '0' COMMENT '上级id',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `top_id` (`top_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户关系表';

-- 数据导出被取消选择。

-- 导出  表 xhbc.user_yuebao 结构
CREATE TABLE IF NOT EXISTS `user_yuebao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
  `is_test` tinyint NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
  `type` int NOT NULL DEFAULT '1' COMMENT '1存2取3收益',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `finish_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '完结金额',
  `info` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间',
  `settle_time` int NOT NULL DEFAULT '0' COMMENT '结算时间',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`,`uid`),
  KEY `uid` (`uid`),
  KEY `idx_type_finish_settle` (`type`,`finish_money`,`settle_time`),
  KEY `idx_uid_type_finish` (`uid`,`type`,`finish_money`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC COMMENT='用户余额宝记录';

-- 数据导出被取消选择。

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
