-- xhbc Laravel test data pack (generated from db-update.sql ONLY)
-- Schema source: E:/xhbc/laravel/db-update.sql
-- IMPORTANT: This file does NOT depend on db-init.sql
-- Target DB: xhbc
-- Usage:
--   1) Ensure schema is imported from db-update.sql
--   2) Run this file in MySQL client

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE `xhbc`;

SET @ts = UNIX_TIMESTAMP();
SET @today = DATE_FORMAT(NOW(), '%Y-%m-%d');
SET @yesterday = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d');

-- ------------------------------------------------------------------
-- Cleanup (only business/testing tables in this data pack)
-- ------------------------------------------------------------------
DELETE FROM `sys_role_perm`;
DELETE FROM `sys_user_role`;
DELETE FROM `sys_perm`;
DELETE FROM `sys_role`;
DELETE FROM `sys_user`;
DELETE FROM `sys_user_log`;

DELETE FROM `market_domain`;
DELETE FROM `market_channel`;

DELETE FROM `level_risk`;
DELETE FROM `level`;

DELETE FROM `pay_recharge`;
DELETE FROM `pay_withdraw`;
DELETE FROM `pay_channel`;
DELETE FROM `pay_account`;
DELETE FROM `pay_class`;
DELETE FROM `pay_upper`;
DELETE FROM `pay_bank`;

DELETE FROM `agent_apply`;
DELETE FROM `agent_commission_log`;
DELETE FROM `agent_commission_total`;
DELETE FROM `agent_commission`;

DELETE FROM `user_login`;
DELETE FROM `user_real_name`;
DELETE FROM `user_rebate`;
DELETE FROM `user_money_log`;
DELETE FROM `user_money_transfer`;
DELETE FROM `user_money_class`;
DELETE FROM `user_bets_log`;
DELETE FROM `user_bets_api`;
DELETE FROM `user_level`;
DELETE FROM `user_info`;
DELETE FROM `user_relation`;
DELETE FROM `user_bank`;
DELETE FROM `user`;

DELETE FROM `game_log`;
DELETE FROM `game`;
DELETE FROM `game_upper`;
DELETE FROM `game_class`;

DELETE FROM `ops_notice`;
DELETE FROM `ops_banner`;
DELETE FROM `ops_question`;
DELETE FROM `ops_sms_code`;

DELETE FROM `risk_different_place`;
DELETE FROM `risk_same_device`;
DELETE FROM `risk_same_ip`;
DELETE FROM `risk_user_bets`;
DELETE FROM `risk_user_win`;
DELETE FROM `risk_user_loss`;
DELETE FROM `risk_user_earn`;

DELETE FROM `report_pay_recharge`;
DELETE FROM `report_pay_withdraw`;
DELETE FROM `report_finance`;
DELETE FROM `report_handicap`;
DELETE FROM `report_agent_daily`;
DELETE FROM `report_user_daily`;

-- ------------------------------------------------------------------
-- Admin / RBAC
-- ------------------------------------------------------------------
INSERT INTO `sys_role` (`id`,`name`,`code`,`remark`,`status`,`sort`,`create_time`,`update_time`) VALUES
(1,'SuperAdmin','super_admin','System super administrator',0,1,@ts,@ts),
(2,'OpsAdmin','ops_admin','Operation administrator',0,2,@ts,@ts),
(3,'FinanceAdmin','finance_admin','Finance and payment operator',0,3,@ts,@ts),
(4,'RiskAdmin','risk_admin','Risk control operator',0,4,@ts,@ts),
(5,'ReportAdmin','report_admin','Report and audit operator',0,5,@ts,@ts);

INSERT INTO `sys_perm` (`id`,`name`,`code`,`api`,`method`,`pid`,`type`,`icon`,`sort`,`create_time`,`update_time`) VALUES
(1,'Admin Login','admin.login','/api/admin/v1/login/login','POST',0,1,'',1,@ts,@ts),
(2,'Admin User List','admin.user.list','/api/admin/v1/user/get_user_lists','GET',0,1,'',2,@ts,@ts),
(3,'Recharge List','admin.recharge.list','/api/admin/v1/recharge/get_recharge_lists','GET',0,1,'',3,@ts,@ts),
(4,'Withdraw List','admin.withdraw.list','/api/admin/v1/withdraw/get_withdraw_lists','GET',0,1,'',4,@ts,@ts);

INSERT INTO `sys_role_perm` (`id`,`role_id`,`perm_id`,`create_time`,`update_time`) VALUES
(1,1,1,@ts,@ts),(2,1,2,@ts,@ts),(3,1,3,@ts,@ts),(4,1,4,@ts,@ts),
(5,2,2,@ts,@ts),(6,2,3,@ts,@ts),(7,2,4,@ts,@ts),
(8,3,3,@ts,@ts),(9,3,4,@ts,@ts),
(10,4,4,@ts,@ts),
(11,5,2,@ts,@ts),(12,5,4,@ts,@ts);

INSERT INTO `sys_user`
(`id`,`username`,`password`,`email`,`remark`,`token`,`role`,`role_id`,`role_name`,`clock`,`login_time`,`login_ip`,`ip_address`,`create_time`,`update_time`,`create_at`,`update_at`,`login_agent`)
VALUES
(1,'admin','123456','admin@xhbc.local','default admin','',0,1,1,0,@ts,'127.0.0.1','LOCAL',@ts,@ts,NOW(),NOW(),'seed'),
(2,'ops001','123456','ops001@xhbc.local','ops account','',1,2,2,0,@ts,'127.0.0.1','LOCAL',@ts,@ts,NOW(),NOW(),'seed'),
(3,'fin001','123456','fin001@xhbc.local','finance account','',1,3,3,0,@ts,'127.0.0.1','LOCAL',@ts,@ts,NOW(),NOW(),'seed'),
(4,'risk001','123456','risk001@xhbc.local','risk account','',1,4,4,0,@ts,'127.0.0.1','LOCAL',@ts,@ts,NOW(),NOW(),'seed'),
(5,'report001','123456','report001@xhbc.local','report account','',1,5,5,0,@ts,'127.0.0.1','LOCAL',@ts,@ts,NOW(),NOW(),'seed');

INSERT INTO `sys_user_role` (`id`,`user_id`,`role_id`,`create_time`,`update_time`) VALUES
(1,1,1,@ts,@ts),
(2,2,2,@ts,@ts),
(3,3,3,@ts,@ts),
(4,4,4,@ts,@ts),
(5,5,5,@ts,@ts);

INSERT INTO `sys_user_log` (`id`,`uid`,`username`,`login_ip`,`ip_address`,`login_agent`,`create_at`,`update_at`,`create_time`,`update_time`) VALUES
(1,1,'admin','127.0.0.1','LOCAL','seed',NOW(),NOW(),@ts,@ts),
(2,2,'ops001','127.0.0.1','LOCAL','seed',NOW(),NOW(),@ts,@ts),
(3,3,'fin001','127.0.0.1','LOCAL','seed',NOW(),NOW(),@ts,@ts),
(4,4,'risk001','127.0.0.1','LOCAL','seed',NOW(),NOW(),@ts,@ts),
(5,5,'report001','127.0.0.1','LOCAL','seed',NOW(),NOW(),@ts,@ts);

-- ------------------------------------------------------------------
-- Levels / risk params
-- ------------------------------------------------------------------
INSERT INTO `level`
(`id`,`title`,`rebate`,`rebate_ratio`,`handling_fee`,`kickback`,`kickback_ratio`,`birthday_bonus`,`level_up_bonus`,`level_up_recharge`,`level_keep_recharge`,`level_up_bets`,`level_keep_bets`,`withdraw`,`withdraw_num`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'VIP1',1.00,0.50,1.00,0.00,0.00,18.00,28.00,100.00,50.00,1000.00,500.00,10,5,@ts,@ts,NOW(),NOW()),
(2,'VIP2',1.20,0.60,0.80,0.00,0.00,28.00,58.00,500.00,200.00,5000.00,2500.00,15,8,@ts,@ts,NOW(),NOW());

INSERT INTO `level_risk`
(`id`,`title`,`big_bet`,`big_bet_live`,`big_bet_lottery`,`big_bet_sport`,`big_bet_esport`,`big_bet_slots`,`big_bet_poker`,
`user_earn`,`user_earn_live`,`user_earn_lottery`,`user_earn_sport`,`user_earn_esport`,`user_earn_slots`,`user_earn_poker`,
`user_loss`,`user_loss_live`,`user_loss_lottery`,`user_loss_sport`,`user_loss_esport`,`user_loss_slots`,`user_loss_poker`,
`user_win`,`user_win_live`,`user_win_lottery`,`user_win_sport`,`user_win_esport`,`user_win_slots`,`user_win_poker`,
`update_time`,`create_time`,`create_at`,`update_at`)
VALUES
(1,'DefaultRisk',10000,10000,8000,10000,8000,6000,5000,5000,5000,3000,5000,3000,2000,1000,5000,5000,3000,5000,3000,2000,1000,80,80,80,80,80,80,80,@ts,@ts,NOW(),NOW());

-- ------------------------------------------------------------------
-- Marketing / domains
-- ------------------------------------------------------------------
INSERT INTO `market_channel` (`id`,`title`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'Facebook Ads',@ts,@ts,NOW(),NOW()),
(2,'Telegram',@ts,@ts,NOW(),NOW());

INSERT INTO `market_domain`
(`id`,`desc`,`domain`,`create_time`,`update_time`,`create_at`,`update_at`,`channl_id`,`channl_name`,`agent_id`,`agent_name`,`status`,`visit`)
VALUES
(1,'Agent landing','agent.xhbc.local',@ts,@ts,NOW(),NOW(),1,'Facebook Ads',10001,'agent001',0,120),
(2,'Public landing','www.xhbc.local',@ts,@ts,NOW(),NOW(),2,'Telegram',0,'',0,260);

-- ------------------------------------------------------------------
-- Payment configs
-- ------------------------------------------------------------------
INSERT INTO `pay_upper` (`id`,`title`,`code`,`status`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'ManualGateway','MANUAL',0,@ts,@ts,NOW(),NOW());

INSERT INTO `pay_class` (`id`,`title`,`img`,`rate`,`status`,`type`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'USDT-TRC20','/img/usdt.png',1.00,0,1,@ts,@ts,NOW(),NOW()),
(2,'BankCard','/img/bank.png',1.00,0,0,@ts,@ts,NOW(),NOW());

INSERT INTO `pay_account`
(`id`,`title`,`status`,`type`,`show`,`bank_name`,`bank_branch`,`bank_account`,`coin_name`,`coin_blockchain`,`coin_account`,`alipay_account`,`img`,`remark`,`rate`,`admin_id`,`admin_name`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'Main Crypto Account',0,1,1,'','','','USDT','TRC20','TGx1234567890abc','','/img/usdt-qr.png','test receiving account',1.00,1,'admin',@ts,@ts,NOW(),NOW()),
(2,'Main Bank Account',0,0,1,'ABC Bank','Shenzhen Branch','6222000000000000','','','','','/img/bank-qr.png','bank receiving account',1.00,1,'admin',@ts,@ts,NOW(),NOW());

INSERT INTO `pay_bank` (`id`,`title`,`img`,`status`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'ABC Bank','/img/abc.png',0,@ts,@ts,NOW(),NOW()),
(2,'ICBC','/img/icbc.png',0,@ts,@ts,NOW(),NOW());

INSERT INTO `pay_channel`
(`id`,`title`,`code`,`status`,`upper`,`upper_id`,`upper_name`,`upper_status`,`upper_img`,`class_id`,`class_name`,`class_status`,`class_img`,`class_rate`,
`create_time`,`update_time`,`payment_time`,`create_at`,`update_at`,`handle_fee`,`amt_min`,`amt_max`,`day_amt`,`day_max`,`style`,`accounts`,`levels`)
VALUES
(1,'USDT Quick','USDT_QK',0,'MANUAL',1,'ManualGateway',0,'/img/up.png',1,'USDT-TRC20',0,'/img/usdt.png',1.00,@ts,@ts,0,NOW(),NOW(),0.50,50.00,50000.00,0.00,200000.00,2,'1','0,1,2'),
(2,'Bank Fast','BANK_FAST',0,'MANUAL',1,'ManualGateway',0,'/img/up.png',2,'BankCard',0,'/img/bank.png',1.00,@ts,@ts,0,NOW(),NOW(),0.30,100.00,30000.00,0.00,100000.00,2,'2','0,1,2');

-- ------------------------------------------------------------------
-- Users / relations / balances
-- ------------------------------------------------------------------
INSERT INTO `user`
(`id`,`username`,`nickname`,`password`,`pin`,`birthday`,`birthday_gift_time`,`phone`,`email`,`token`,`invite`,
`sfz_name`,`sfz_number`,`sfz_img`,`avatar`,`level`,`level_change_time`,`level_name`,`role_level`,
`login_time`,`login_ip`,`ip_address`,`register_ip`,`admin_id`,`admin_name`,`top_id`,`top_name`,`plan_commission`,`plan_rebate`,
`tags`,`group_id`,`group_name`,`channel_id`,`channel_name`,`remark`,`money`,`frozen_money`,`yuebao`,`points`,`raffle`,
`first_recharge_money`,`first_recharge_time`,`recharge_time`,`withdraw_time`,`signin_time`,`online_time`,`yuebao_time`,
`create_time`,`update_time`,`create_at`,`update_at`,`ban_buy`,`ban_lock`,`ban_sigin`,`ban_raffle`,`ban_login`,`ban_invite`,
`ban_recharge`,`ban_withdraw`,`ban_exchange`,`ban_bets`,`ban_transfer`,`sfz_status`,`kick_out`,`is_valid_user`,`is_test`,`is_bets`)
VALUES
(10001,'agent001','Agent One','123456','123456','1990-01-01',0,'13900000001','agent001@xhbc.local','', 'AGENT001',
 '','', '', '/img/a1.png',2,@ts,'VIP2',1,
 @ts,'127.0.0.1','LOCAL','127.0.0.1',1,'admin',0,'',1,1,
 'vip,agent',0,'',1,'Facebook Ads','seed agent',10000.00,0.00,0.00,100,5,
 2000.00,@ts,@ts,0,0,@ts,0,@ts,@ts,NOW(),NOW(),0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,0),
(10002,'user001','User One','123456','123456','1993-05-06',0,'13900000002','user001@xhbc.local','', 'USER001',
 '','', '', '/img/u1.png',1,@ts,'VIP1',0,
 @ts,'127.0.0.1','LOCAL','127.0.0.1',1,'admin',10001,'agent001',1,1,
 'normal',0,'',1,'Facebook Ads','seed member',1250.00,0.00,0.00,20,1,
 500.00,@ts,@ts,@ts,0,@ts,0,@ts,@ts,NOW(),NOW(),0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0),
(10003,'test001','Test User','123456','123456','1998-09-09',0,'13900000003','test001@xhbc.local','', 'TEST001',
 '','', '', '/img/u2.png',1,@ts,'VIP1',0,
 @ts,'127.0.0.1','LOCAL','127.0.0.1',1,'admin',10001,'agent001',1,1,
 'test',0,'',2,'Telegram','seed test member',300.00,0.00,0.00,0,0,
 0.00,0,0,0,0,@ts,0,@ts,@ts,NOW(),NOW(),0,0,0,0,0,0,0,0,0,0,0,1,0,1,1,0);

INSERT INTO `user_relation` (`id`,`uid`,`level`,`top_id`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,1,0,@ts,@ts,NOW(),NOW()),
(2,10002,2,10001,@ts,@ts,NOW(),NOW()),
(3,10003,2,10001,@ts,@ts,NOW(),NOW());

INSERT INTO `user_info`
(`uid`,`recharge_money`,`recharge_num`,`withdraw_money`,`withdraw_num`,`signin_money`,`rebate_money`,`compensate_money`,`compensate_num`,`rebate_num`,`commission_money`,`commission_num`,
`bonus_money`,`bonus_num`,`signin_num`,`reffle_money`,`raffle_num`,`team_event`,`team_bonus`,`team_earn`,`team_profit_loss`,`team_bets`,`team_bets_valid`,`team_user`,`team_agent`,`team_rebate`,
`team_rebate_num`,`team_recharge`,`team_withdraw`,`user_points`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(10001,3000.00,5,800.00,2,50.00,120.00,0.00,0,4,500.00,2,80.00,2,10,0.00,0,0.00,0.00,0.00,0.00,5000.00,4200.00,2,1,120.00,4,3000.00,800.00,100,@ts,@ts,NOW(),NOW()),
(10002,1200.00,3,200.00,1,10.00,25.00,0.00,0,2,0.00,0,20.00,1,3,0.00,0,0.00,0.00,0.00,0.00,1300.00,980.00,0,0,0.00,0,1200.00,200.00,20,@ts,@ts,NOW(),NOW()),
(10003,0.00,0,0.00,0,0.00,0.00,0.00,0,0,0.00,0,0.00,0,0,0.00,0,0.00,0.00,0.00,0.00,0.00,0.00,0,0,0.00,0,0.00,0.00,0,@ts,@ts,NOW(),NOW());

INSERT INTO `user_level` (`uid`,`level_bets`,`level_recharge`,`level_change_time`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(10001,5000.00,3000.00,@ts,@ts,@ts,NOW(),NOW()),
(10002,1200.00,1200.00,@ts,@ts,@ts,NOW(),NOW()),
(10003,0.00,0.00,@ts,@ts,@ts,NOW(),NOW());

INSERT INTO `user_bets_api` (`uid`,`ng_reg`,`ng_api`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(10001,1,1,@ts,@ts,NOW(),NOW()),
(10002,1,1,@ts,@ts,NOW(),NOW()),
(10003,0,0,@ts,@ts,NOW(),NOW());

INSERT INTO `user_real_name`
(`id`,`uid`,`username`,`phone`,`is_test`,`sfz_name`,`sfz_number`,`status`,`bank_account`,`bank_branch`,`bank_name`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,'USER REAL','440301199305060011',0,'6222000000000000','Shenzhen Branch','ABC Bank',@ts,@ts,NOW(),NOW());

INSERT INTO `user_bank`
(`id`,`uid`,`username`,`phone`,`is_test`,`name`,`bank_name`,`bank_branch`,`bank_account`,`coin_name`,`coin_blockchain`,`coin_account`,`alipay_account`,`alipay_img`,`wx_img`,`type`,`default`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,'USER REAL','ABC Bank','Shenzhen Branch','6222000000000000','','','','','','',0,0,@ts,@ts,NOW(),NOW());

INSERT INTO `user_login`
(`id`,`uid`,`username`,`phone`,`ip`,`ip_address`,`country`,`province`,`area`,`is_test`,`http_user_agent`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10001,'agent001','13900000001','127.0.0.1','LOCAL','CN','GD','SZ',0,'seed-agent',@ts,@ts,NOW(),NOW()),
(2,10002,'user001','13900000002','127.0.0.1','LOCAL','CN','GD','SZ',0,'seed-user',@ts,@ts,NOW(),NOW()),
(3,10003,'test001','13900000003','127.0.0.1','LOCAL','CN','GD','SZ',1,'seed-test',@ts,@ts,NOW(),NOW());

INSERT INTO `user_rebate`
(`id`,`uid`,`username`,`phone`,`level`,`level_name`,`is_test`,`bets`,`rebate`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',1,'VIP1',0,500.00,5.00,@ts,@ts,NOW(),NOW());

INSERT INTO `user_money_class`
(`id`,`title`,`style`,`type`,`multiple`,`set_up`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'充值',1,0,1.00,1,@ts,@ts,NOW(),NOW()),
(2,'提现',0,0,1.00,1,@ts,@ts,NOW(),NOW()),
(3,'转账',0,0,1.00,1,@ts,@ts,NOW(),NOW()),
(4,'后台上分',1,1,0.00,1,@ts,@ts,NOW(),NOW());

INSERT INTO `user_money_log`
(`id`,`uid`,`username`,`phone`,`is_test`,`order_id`,`class_id`,`class_name`,`amount`,`before`,`is_finish`,`after`,`bets`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,'RCG202603110001',1,'充值',500.00,750.00,0,1250.00,500.00,'用户充值到账',@ts,@ts,NOW(),NOW()),
(2,10002,'user001','13900000002',0,'WDR202603110001',2,'提现',200.00,1250.00,0,1050.00,0.00,'用户提现扣款',@ts,@ts,NOW(),NOW());

INSERT INTO `user_money_transfer`
(`id`,`uid`,`username`,`phone`,`is_test`,`order_id`,`amount`,`style`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,'TRN202603110001',100.00,0,'转出至场馆',@ts,@ts,NOW(),NOW());

-- ------------------------------------------------------------------
-- Agent-related business
-- ------------------------------------------------------------------
INSERT INTO `agent_apply`
(`id`,`uid`,`username`,`phone`,`is_test`,`status`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,0,'I want to be agent',@ts,@ts,NOW(),NOW());

INSERT INTO `agent_commission`
(`id`,`uid`,`username`,`phone`,`total_profit`,`total_commission`,`commission_surplus`,`commission_justify`,`commission_blow`,`commission_remain`,
`commission_rebate_ratio`,`commission_actual_rebate`,`commission_last_month`,`active_member_effective`,`active_member`,`total_bet_count`,`total_bet`,
`date_time`,`update_time`,`create_time`,`is_send`,`send_time`,`remark`,`float_profit`,`create_at`,`update_at`)
VALUES
(1,10001,'agent001','13900000001',500.00,35.00,0.00,0.00,0.00,35.00,
0.50,35.00,0.00,1,2,6,1300.00,
@today,@ts,@ts,0,@ts,'seed monthly commission',145.00,NOW(),NOW());

INSERT INTO `agent_commission_total`
(`id`,`total_agent`,`total_commission`,`total_bet`,`total_profit`,`total_member`,`update_time`,`create_time`,`date`,`total_bet_count`,`total_commission_real`,`create_at`,`update_at`)
VALUES
(1,1,35.00,1300.00,145.00,2,@ts,@ts,@today,6,35.00,NOW(),NOW());

INSERT INTO `agent_commission_log`
(`id`,`uid`,`phone`,`username`,`amount`,`desc`,`admin_id`,`admin_name`,`valid_user`,`active_user`,`user_bets`,`user_bets_count`,`commission_ratio`,`create_time`,`update_time`,`create_at`,`update_at`,`type`)
VALUES
(1,10001,'13900000001','agent001',35.00,'daily settlement',1,'admin',1,2,1300.00,6,0.50,@ts,@ts,NOW(),NOW(),0);

-- ------------------------------------------------------------------
-- Payment business records
-- ------------------------------------------------------------------
INSERT INTO `pay_recharge`
(`id`,`uid`,`username`,`phone`,`is_test`,`status`,`channel_id`,`channel_name`,`class_id`,`class_name`,`account_id`,`order_no`,`account`,`img`,`name`,
`create_time`,`update_time`,`create_at`,`update_at`,`amount`,`amount_real`,`fee`,`fee_rate`,`remark`,`rate`,`style`)
VALUES
(1,10002,'user001','13900000002',0,0,1,'USDT Quick',1,'USDT-TRC20',1,'RCG202603110001','TGx1234567890abc','/img/payproof.png','user001',
@ts,@ts,NOW(),NOW(),500.00,497.50,2.50,0.50,'first recharge success',1.00,2),
(2,10003,'test001','13900000003',1,2,2,'Bank Fast',2,'BankCard',2,'RCG202603110002','6222000000000000','/img/payproof2.png','test001',
@ts,@ts,NOW(),NOW(),300.00,299.10,0.90,0.30,'failed recharge',1.00,2);

INSERT INTO `pay_withdraw`
(`id`,`uid`,`username`,`phone`,`is_test`,`order_no`,`status`,`type`,`name`,`bank_name`,`bank_branch`,`bank_account`,`coin_name`,`coin_blockchain`,`coin_account`,
`alipay_account`,`alipay_img`,`wx_img`,`amount`,`amount_real`,`exchange_rate`,`handling_fee`,`handling_rate`,`remark`,`create_time`,`update_time`,`create_at`,`update_at`,`channel_id`,`channel_name`)
VALUES
(1,10002,'user001','13900000002',0,'WDR202603110001',1,0,'USER REAL','ABC Bank','Shenzhen Branch','6222000000000000','','','','','','',
200.00,198.40,1.00,1.60,0.80,'pending manual review',@ts,@ts,NOW(),NOW(),1,'ManualGateway');

-- ------------------------------------------------------------------
-- Game configs / records
-- ------------------------------------------------------------------
INSERT INTO `game_class` (`id`,`title`,`sort`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'Slots',1,@ts,@ts,NOW(),NOW()),
(2,'Live Casino',2,@ts,@ts,NOW(),NOW());

INSERT INTO `game_upper`
(`id`,`title`,`code`,`status`,`hot`,`fee`,`pic_pc_nav`,`pic_pc_list`,`pic_h5`,`sort`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'NG','NG',0,1,0.50,'/img/ng-nav.png','/img/ng-list.png','/img/ng-h5.png',1,@ts,@ts,NOW(),NOW());

INSERT INTO `game`
(`id`,`title`,`code`,`sort`,`upper_id`,`upper_name`,`pic_h5`,`pic_pc`,`pic_app`,`status`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'Lucky777','SLOT777',1,1,'NG','/img/slot-h5.png','/img/slot-pc.png','/img/slot-app.png',0,@ts,@ts,NOW(),NOW());

INSERT INTO `game_log`
(`id`,`uid`,`in_bets`,`username`,`real_name`,`top_id`,`top_name`,`class_id`,`class_name`,`upper_id`,`upper_name`,`game_id`,`game_name`,
`order_no`,`bet`,`bet_valid`,`bet_time`,`currency`,`settle`,`win_loss`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,1,'user001','USER REAL',10001,10001,'1','Slots','1','NG','1','Lucky777',
'G202603110001',100.00,98.00,@ts,'CNY',1,15.00,'seed game settled order',@ts,@ts,NOW(),NOW());

-- ------------------------------------------------------------------
-- OPS content / SMS
-- ------------------------------------------------------------------
INSERT INTO `ops_notice` (`id`,`title`,`img`,`url`,`content`,`create_time`,`update_time`,`create_at`,`update_at`,`status`,`start_time`,`end_time`,`levels`) VALUES
(1,'System Notice','','','Welcome to xhbc test environment',@ts,@ts,NOW(),NOW(),0,@ts,@ts+86400*30,'0,1,2');

INSERT INTO `ops_banner` (`id`,`title`,`img`,`url`,`content`,`create_time`,`update_time`,`create_at`,`update_at`,`status`,`start_time`,`end_time`,`levels`) VALUES
(1,'Promo Banner','/img/banner1.png','https://xhbc.local/promo','Recharge bonus event',@ts,@ts,NOW(),NOW(),0,@ts,@ts+86400*30,'0,1,2');

INSERT INTO `ops_question` (`id`,`title`,`content`,`type`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'How to recharge?','Go to recharge page and follow channel guide',0,@ts,@ts,NOW(),NOW());

INSERT INTO `ops_sms_code` (`id`,`uid`,`phone`,`username`,`is_test`,`code`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10002,'13900000002','user001',0,'888999',@ts,@ts,NOW(),NOW());

-- ------------------------------------------------------------------
-- Risk records
-- ------------------------------------------------------------------
INSERT INTO `risk_different_place`
(`id`,`uid`,`username`,`phone`,`ip`,`ip_address`,`country`,`province`,`area`,`http_user_agent`,`is_test`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002','10.1.1.2','CN,GD,SZ','CN','GD','SZ','Mozilla/5.0',0,@ts,@ts,NOW(),NOW());

INSERT INTO `risk_same_device`
(`id`,`uid`,`username`,`phone`,`ip`,`ip_address`,`device`,`http_user_agent`,`is_mobile`,`connection`,`is_test`,`action`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002','10.1.1.2','CN,GD,SZ','device_fingerprint_a','Mozilla/5.0',1,'2',0,0,@ts,@ts,NOW(),NOW());

INSERT INTO `risk_same_ip`
(`id`,`uid`,`username`,`phone`,`ip`,`ip_address`,`http_user_agent`,`connection`,`is_test`,`action`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10003,'test001','13900000003','10.1.1.2','CN,GD,SZ','Mozilla/5.0','2',1,0,@ts,@ts,NOW(),NOW());

INSERT INTO `risk_user_bets`
(`id`,`uid`,`username`,`phone`,`is_test`,`level`,`level_name`,`desc`,`is_win`,`bets`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,1,'VIP1','single big bet',1,'[\"G202603110001\"]',@ts,@ts,NOW(),NOW());

INSERT INTO `risk_user_win`
(`id`,`uid`,`username`,`phone`,`is_test`,`level`,`level_name`,`desc`,`amount`,`bets`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10002,'user001','13900000002',0,1,'VIP1','high win ratio',1200.00,3000.00,@ts,@ts,NOW(),NOW());

INSERT INTO `risk_user_loss`
(`id`,`uid`,`username`,`phone`,`is_test`,`level`,`level_name`,`desc`,`amount`,`bets`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10003,'test001','13900000003',1,1,'VIP1','high loss',900.00,1500.00,@ts,@ts,NOW(),NOW());

INSERT INTO `risk_user_earn`
(`id`,`uid`,`username`,`phone`,`is_test`,`level`,`level_name`,`desc`,`amount`,`bets`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10001,'agent001','13900000001',0,2,'VIP2','agent daily earn',500.00,0.00,@ts,@ts,NOW(),NOW());

-- ------------------------------------------------------------------
-- Reports
-- ------------------------------------------------------------------
INSERT INTO `report_pay_recharge` (`id`,`date_time`,`create_time`,`update_time`,`create_at`,`update_at`,`success`,`success_count`,`fail`,`fai_count`) VALUES
(1,@today,@ts,@ts,NOW(),NOW(),500.00,1,300.00,1),
(2,@yesterday,@ts-86400,@ts-86400,NOW(),NOW(),800.00,2,0.00,0);

INSERT INTO `report_pay_withdraw` (`id`,`create_time`,`update_time`,`create_at`,`update_at`,`success`,`success_count`,`fail`,`fai_count`,`date_time`) VALUES
(1,@ts,@ts,NOW(),NOW(),0.00,0,200.00,1,@today);

INSERT INTO `report_finance`
(`id`,`date_time`,`recharge_by_user`,`recharge_by_admin`,`withdraw`,`recharge_gift`,`first_recharge_gift`,`return_water`,`transfe_in`,`transfe_out`,`commission`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,@today,500.00,0.00,200.00,20.00,10.00,5.00,100.00,80.00,35.00,@ts,@ts,NOW(),NOW());

INSERT INTO `report_handicap`
(`id`,`date_time`,`create_time`,`update_time`,`create_at`,`update_at`,`recharge`,`withdraw`,`user_valid`,`user_active`,`user_register`,`bet`,`bet_valid`,`rebate`,`welfare`,`profit`)
VALUES
(1,@today,@ts,@ts,NOW(),NOW(),500.00,200.00,2,2,1,1300.00,980.00,25.00,20.00,145.00);

INSERT INTO `report_agent_daily`
(`id`,`uid`,`date_time`,`username`,`phone`,`total_rebate`,`total_event`,`total_welfare`,`total_venue_fee`,`total_bet`,`total_bet_valid`,`total_bet_count`,`total_profit`,
`total_recharge`,`total_withdrawal`,`total_recharge_fee`,`total_withdrawal_fee`,`total_commission`,`commission_surplus`,`commission_justify`,`commission_blow`,`commission_remain`,
`active_member`,`active_member_effective`,`commission_rebate_ratio`,`active_user`,`bet_user`,`register_user`,`deposit_user`,`first_deposit_user`,
`recharge_amount`,`withdraw_amount`,`is_deducted`,`is_update`,`float_profit`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,10001,@today,'agent001','13900000001',25.00,10.00,20.00,5.00,1300.00,980.00,6,145.00,
500.00,200.00,2.50,1.60,35.00,0.00,0.00,0.00,35.00,
2,1,0.50,2,1,1,1,1,
500.00,200.00,0,0,145.00,@ts,@ts,NOW(),NOW());

INSERT INTO `report_user_daily`
(`id`,`uid`,`username`,`phone`,`is_test`,`date_time`,`create_time`,`update_time`,`create_at`,`update_at`,`recharge`,`recharge_count`,`recharge_fee`,`withdraw`,`withdraw_count`,`withdraw_fee`,
`bet`,`bet_valid`,`bet_count`,`rebate`,`event`,`welfare`,`profit`,`company_profit`,`venue_fee`,`admin_incr`,`admin_decr`)
VALUES
(1,10002,'user001','13900000002',0,@today,@ts,@ts,NOW(),NOW(),500.00,1,2.50,200.00,1,1.60,1300.00,980.00,6,25.00,10.00,20.00,145.00,145.00,5.00,0.00,0.00),
(2,10003,'test001','13900000003',1,@today,@ts,@ts,NOW(),NOW(),0.00,0,0.00,0.00,0,0.00,0.00,0.00,0,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00);

SET FOREIGN_KEY_CHECKS = 1;

-- End of db-data.sql

-- ==================================================================
-- Auto-generated completion seeds for tables missing INSERT rows
-- Generated from db-update.sql to ensure coverage for all tables
-- ==================================================================

DELETE FROM `agent_bets_plan`;
INSERT INTO `agent_bets_plan` (`id`,`title`,`desc`,`bets`,`type`,`give_out`,`rebate`,`rebate_ratio`,`live_ratio`,`lottery_ratio`,`slots_ratio`,`sport_ratio`,`esport_ratio`,`poker_ratio`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'agent_bets_plan_title_1','agent_bets_plan_desc_1',10.00,0,1,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,@ts-60,@ts-60,NOW(),NOW()),
(2,'agent_bets_plan_title_2','agent_bets_plan_desc_2',20.00,1,2,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,@ts-120,@ts-120,NOW(),NOW()),
(3,'agent_bets_plan_title_3','agent_bets_plan_desc_3',30.00,2,3,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,@ts-180,@ts-180,NOW(),NOW()),
(4,'agent_bets_plan_title_4','agent_bets_plan_desc_4',40.00,0,4,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,@ts-240,@ts-240,NOW(),NOW()),
(5,'agent_bets_plan_title_5','agent_bets_plan_desc_5',50.00,1,5,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `agent_commission_plan`;
INSERT INTO `agent_commission_plan` (`id`,`title`,`desc`,`valid`,`active`,`loss`,`ratio`,`type`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'agent_commission_plan_title_1','agent_commission_plan_desc_1',1,1,1,10.00,0,@ts-60,@ts-60,NOW(),NOW()),
(2,'agent_commission_plan_title_2','agent_commission_plan_desc_2',2,2,2,20.00,1,@ts-120,@ts-120,NOW(),NOW()),
(3,'agent_commission_plan_title_3','agent_commission_plan_desc_3',3,3,3,30.00,2,@ts-180,@ts-180,NOW(),NOW()),
(4,'agent_commission_plan_title_4','agent_commission_plan_desc_4',4,4,4,40.00,0,@ts-240,@ts-240,NOW(),NOW()),
(5,'agent_commission_plan_title_5','agent_commission_plan_desc_5',5,5,5,50.00,1,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `event_apply`;
INSERT INTO `event_apply` (`id`,`username`,`phone`,`is_test`,`amount`,`desc`,`event_id`,`event_name`,`status`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'agent001','13900000001',0,10.00,'event_apply_desc_1',1,'event_apply_event_name_1',0,@ts-60,@ts-60,NOW(),NOW()),
(2,'user001','13900000002',0,20.00,'event_apply_desc_2',1,'event_apply_event_name_2',1,@ts-120,@ts-120,NOW(),NOW()),
(3,'test001','13900000003',0,30.00,'event_apply_desc_3',1,'event_apply_event_name_3',2,@ts-180,@ts-180,NOW(),NOW()),
(4,'user001','13900000002',0,40.00,'event_apply_desc_4',1,'event_apply_event_name_4',0,@ts-240,@ts-240,NOW(),NOW()),
(5,'agent001','13900000001',1,50.00,'event_apply_desc_5',1,'event_apply_event_name_5',1,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `event_class`;
INSERT INTO `event_class` (`id`,`title`,`sort`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'event_class_title_1',1,@ts-60,@ts-60,NOW(),NOW()),
(2,'event_class_title_2',2,@ts-120,@ts-120,NOW(),NOW()),
(3,'event_class_title_3',3,@ts-180,@ts-180,NOW(),NOW()),
(4,'event_class_title_4',4,@ts-240,@ts-240,NOW(),NOW()),
(5,'event_class_title_5',5,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `event_list`;
INSERT INTO `event_list` (`id`,`title`,`code`,`class_id`,`class_name`,`create_time`,`start_time`,`end_time`,`amount`,`sort`,`status`,`update_time`,`create_at`,`update_at`) VALUES
(1,'event_list_title_1','EVENT_LIST_0001',1,'event_list_class_name_1',@ts-60,@ts-60,@ts-60,10.00,1,0,@ts-60,NOW(),NOW()),
(2,'event_list_title_2','EVENT_LIST_0002',1,'event_list_class_name_2',@ts-120,@ts-120,@ts-120,20.00,2,1,@ts-120,NOW(),NOW()),
(3,'event_list_title_3','EVENT_LIST_0003',1,'event_list_class_name_3',@ts-180,@ts-180,@ts-180,30.00,3,2,@ts-180,NOW(),NOW()),
(4,'event_list_title_4','EVENT_LIST_0004',1,'event_list_class_name_4',@ts-240,@ts-240,@ts-240,40.00,4,0,@ts-240,NOW(),NOW()),
(5,'event_list_title_5','EVENT_LIST_0005',1,'event_list_class_name_5',@ts-300,@ts-300,@ts-300,50.00,5,1,@ts-300,NOW(),NOW());

DELETE FROM `event_log`;
INSERT INTO `event_log` (`id`,`username`,`phone`,`is_test`,`event_id`,`event_name`,`desc`,`amount`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'agent001','13900000001',0,1,'event_log_event_name_1','event_log_desc_1',10.00,@ts-60,@ts-60,NOW(),NOW()),
(2,'user001','13900000002',0,1,'event_log_event_name_2','event_log_desc_2',20.00,@ts-120,@ts-120,NOW(),NOW()),
(3,'test001','13900000003',0,1,'event_log_event_name_3','event_log_desc_3',30.00,@ts-180,@ts-180,NOW(),NOW()),
(4,'user001','13900000002',0,1,'event_log_event_name_4','event_log_desc_4',40.00,@ts-240,@ts-240,NOW(),NOW()),
(5,'agent001','13900000001',1,1,'event_log_event_name_5','event_log_desc_5',50.00,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `game_quota`;
INSERT INTO `game_quota` (`id`,`create_time`,`update_time`,`create_at`,`update_at`,`all`,`ag`,`ags`,`allbet`,`allbets`,`ap`,`as`,`avia`,`bbin`,`bg`,`boya`,`cmd`,`cq9`,`cq9s`,`cr`,`crown`,`db1`,`db2`,`db3`,`db5`,`db6`,`db7`,`dg`,`esb`,`evo`,`fb`,`fc`,`fg`,`ig`,`im`,`jdb`,`joker`,`ky`,`leg`,`lgd`,`mg`,`mt`,`mw`,`newbb`,`nw`,`og`,`panda`,`pg`,`pgs`,`png`,`pp`,`pt`,`rsg`,`saba`,`sexy`,`sg`,`sgwin`,`ss`,`tcg`,`tf`,`v8`,`vg`,`vr`,`we`,`wl`,`wm`,`ww`,`xgd`,`xj`,`yoo`) VALUES
(1,@ts-60,@ts-60,NOW(),NOW(),10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00),
(2,@ts-120,@ts-120,NOW(),NOW(),20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00),
(3,@ts-180,@ts-180,NOW(),NOW(),30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00),
(4,@ts-240,@ts-240,NOW(),NOW(),40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00),
(5,@ts-300,@ts-300,NOW(),NOW(),50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00);

DELETE FROM `level_log`;
INSERT INTO `level_log` (`id`,`uid`,`username`,`phone`,`is_test`,`recharge`,`bets`,`type`,`lv_before`,`lv_before_name`,`lv_after`,`lv_after_name`,`desc`,`admin_id`,`admin_name`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,10.00,10.00,0,1,'level_log_lv_before_name_1',1,'level_log_lv_after_name_1','level_log_desc_1',1,'admin',@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,20.00,20.00,1,2,'level_log_lv_before_name_2',2,'level_log_lv_after_name_2','level_log_desc_2',1,'admin',@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,30.00,30.00,2,3,'level_log_lv_before_name_3',3,'level_log_lv_after_name_3','level_log_desc_3',1,'admin',@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,40.00,40.00,0,4,'level_log_lv_before_name_4',4,'level_log_lv_after_name_4','level_log_desc_4',1,'admin',@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,50.00,50.00,1,5,'level_log_lv_before_name_5',5,'level_log_lv_after_name_5','level_log_desc_5',1,'admin',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_article`;
INSERT INTO `ops_article` (`id`,`title`,`img`,`code`,`desc`,`content`,`create_time`,`update_time`,`create_at`,`update_at`,`type`) VALUES
(1,'ops_article_title_1','ops_article_img_1','OPS_ARTICLE_0001','ops_article_desc_1','ops_article_content_1',@ts-60,@ts-60,NOW(),NOW(),0),
(2,'ops_article_title_2','ops_article_img_2','OPS_ARTICLE_0002','ops_article_desc_2','ops_article_content_2',@ts-120,@ts-120,NOW(),NOW(),1),
(3,'ops_article_title_3','ops_article_img_3','OPS_ARTICLE_0003','ops_article_desc_3','ops_article_content_3',@ts-180,@ts-180,NOW(),NOW(),2),
(4,'ops_article_title_4','ops_article_img_4','OPS_ARTICLE_0004','ops_article_desc_4','ops_article_content_4',@ts-240,@ts-240,NOW(),NOW(),0),
(5,'ops_article_title_5','ops_article_img_5','OPS_ARTICLE_0005','ops_article_desc_5','ops_article_content_5',@ts-300,@ts-300,NOW(),NOW(),1);

DELETE FROM `ops_bank`;
INSERT INTO `ops_bank` (`id`,`title`,`img`,`status`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'ops_bank_title_1','ops_bank_img_1',0,@ts-60,@ts-60,NOW(),NOW()),
(2,'ops_bank_title_2','ops_bank_img_2',1,@ts-120,@ts-120,NOW(),NOW()),
(3,'ops_bank_title_3','ops_bank_img_3',2,@ts-180,@ts-180,NOW(),NOW()),
(4,'ops_bank_title_4','ops_bank_img_4',0,@ts-240,@ts-240,NOW(),NOW()),
(5,'ops_bank_title_5','ops_bank_img_5',1,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_email_code`;
INSERT INTO `ops_email_code` (`id`,`phone`,`username`,`is_test`,`code`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'13900000001','agent001',0,'OPS_EMAIL_CODE_0001',@ts-60,@ts-60,NOW(),NOW()),
(2,'13900000002','user001',0,'OPS_EMAIL_CODE_0002',@ts-120,@ts-120,NOW(),NOW()),
(3,'13900000003','test001',0,'OPS_EMAIL_CODE_0003',@ts-180,@ts-180,NOW(),NOW()),
(4,'13900000002','user001',0,'OPS_EMAIL_CODE_0004',@ts-240,@ts-240,NOW(),NOW()),
(5,'13900000001','agent001',1,'OPS_EMAIL_CODE_0005',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_feedback`;
INSERT INTO `ops_feedback` (`id`,`uid`,`phone`,`username`,`is_test`,`desc`,`img`,`type`,`create_time`,`update_time`,`create_at`,`update_at`,`information`) VALUES
(1,10001,'13900000001','agent001',0,'ops_feedback_desc_1','ops_feedback_img_1',0,@ts-60,@ts-60,NOW(),NOW(),'ops_feedback_information_1'),
(2,10002,'13900000002','user001',0,'ops_feedback_desc_2','ops_feedback_img_2',1,@ts-120,@ts-120,NOW(),NOW(),'ops_feedback_information_2'),
(3,10003,'13900000003','test001',0,'ops_feedback_desc_3','ops_feedback_img_3',2,@ts-180,@ts-180,NOW(),NOW(),'ops_feedback_information_3'),
(4,10002,'13900000002','user001',0,'ops_feedback_desc_4','ops_feedback_img_4',0,@ts-240,@ts-240,NOW(),NOW(),'ops_feedback_information_4'),
(5,10001,'13900000001','agent001',1,'ops_feedback_desc_5','ops_feedback_img_5',1,@ts-300,@ts-300,NOW(),NOW(),'ops_feedback_information_5');

DELETE FROM `ops_goods`;
INSERT INTO `ops_goods` (`id`,`class_id`,`class_name`,`title`,`sort`,`price`,`img`,`content`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,1,'ops_goods_class_name_1','ops_goods_title_1',1,10.00,'ops_goods_img_1','ops_goods_content_1',@ts-60,@ts-60,NOW(),NOW()),
(2,1,'ops_goods_class_name_2','ops_goods_title_2',2,20.00,'ops_goods_img_2','ops_goods_content_2',@ts-120,@ts-120,NOW(),NOW()),
(3,1,'ops_goods_class_name_3','ops_goods_title_3',3,30.00,'ops_goods_img_3','ops_goods_content_3',@ts-180,@ts-180,NOW(),NOW()),
(4,1,'ops_goods_class_name_4','ops_goods_title_4',4,40.00,'ops_goods_img_4','ops_goods_content_4',@ts-240,@ts-240,NOW(),NOW()),
(5,1,'ops_goods_class_name_5','ops_goods_title_5',5,50.00,'ops_goods_img_5','ops_goods_content_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_goods_class`;
INSERT INTO `ops_goods_class` (`id`,`sort`,`title`,`img`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,1,'ops_goods_class_title_1','ops_goods_class_img_1',@ts-60,@ts-60,NOW(),NOW()),
(2,2,'ops_goods_class_title_2','ops_goods_class_img_2',@ts-120,@ts-120,NOW(),NOW()),
(3,3,'ops_goods_class_title_3','ops_goods_class_img_3',@ts-180,@ts-180,NOW(),NOW()),
(4,4,'ops_goods_class_title_4','ops_goods_class_img_4',@ts-240,@ts-240,NOW(),NOW()),
(5,5,'ops_goods_class_title_5','ops_goods_class_img_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_goods_order`;
INSERT INTO `ops_goods_order` (`id`,`uid`,`username`,`phone`,`is_test`,`img`,`goods_id`,`goods_title`,`order_no`,`money`,`deliver_title`,`deliver_order_no`,`deliver_time`,`status`,`address_name`,`address_phone`,`address_city`,`address_place`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,'ops_goods_order_img_1',1,'ops_goods_order_goods_title_1','OPS_GOODS_ORDER_0001',10.00,'ops_goods_order_deliver_title_1','ops_goods_order_deliver_order_no_1',@ts-60,0,'ops_goods_order_address_name_1','ops_goods_order_address_phone_1','ops_goods_order_address_city_1','ops_goods_order_address_place_1','ops_goods_order_desc_1',@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,'ops_goods_order_img_2',1,'ops_goods_order_goods_title_2','OPS_GOODS_ORDER_0002',20.00,'ops_goods_order_deliver_title_2','ops_goods_order_deliver_order_no_2',@ts-120,1,'ops_goods_order_address_name_2','ops_goods_order_address_phone_2','ops_goods_order_address_city_2','ops_goods_order_address_place_2','ops_goods_order_desc_2',@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,'ops_goods_order_img_3',1,'ops_goods_order_goods_title_3','OPS_GOODS_ORDER_0003',30.00,'ops_goods_order_deliver_title_3','ops_goods_order_deliver_order_no_3',@ts-180,2,'ops_goods_order_address_name_3','ops_goods_order_address_phone_3','ops_goods_order_address_city_3','ops_goods_order_address_place_3','ops_goods_order_desc_3',@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,'ops_goods_order_img_4',1,'ops_goods_order_goods_title_4','OPS_GOODS_ORDER_0004',40.00,'ops_goods_order_deliver_title_4','ops_goods_order_deliver_order_no_4',@ts-240,0,'ops_goods_order_address_name_4','ops_goods_order_address_phone_4','ops_goods_order_address_city_4','ops_goods_order_address_place_4','ops_goods_order_desc_4',@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,'ops_goods_order_img_5',1,'ops_goods_order_goods_title_5','OPS_GOODS_ORDER_0005',50.00,'ops_goods_order_deliver_title_5','ops_goods_order_deliver_order_no_5',@ts-300,1,'ops_goods_order_address_name_5','ops_goods_order_address_phone_5','ops_goods_order_address_city_5','ops_goods_order_address_place_5','ops_goods_order_desc_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_popup`;
INSERT INTO `ops_popup` (`id`,`title`,`img`,`url`,`content`,`create_time`,`update_time`,`create_at`,`update_at`,`status`,`start_time`,`end_time`,`levels`) VALUES
(1,'ops_popup_title_1','ops_popup_img_1','ops_popup_url_1','ops_popup_content_1',@ts-60,@ts-60,NOW(),NOW(),0,@ts-60,@ts-60,'0,1,2'),
(2,'ops_popup_title_2','ops_popup_img_2','ops_popup_url_2','ops_popup_content_2',@ts-120,@ts-120,NOW(),NOW(),1,@ts-120,@ts-120,'0,1,2'),
(3,'ops_popup_title_3','ops_popup_img_3','ops_popup_url_3','ops_popup_content_3',@ts-180,@ts-180,NOW(),NOW(),2,@ts-180,@ts-180,'0,1,2'),
(4,'ops_popup_title_4','ops_popup_img_4','ops_popup_url_4','ops_popup_content_4',@ts-240,@ts-240,NOW(),NOW(),0,@ts-240,@ts-240,'0,1,2'),
(5,'ops_popup_title_5','ops_popup_img_5','ops_popup_url_5','ops_popup_content_5',@ts-300,@ts-300,NOW(),NOW(),1,@ts-300,@ts-300,'0,1,2');

DELETE FROM `ops_raffle`;
INSERT INTO `ops_raffle` (`id`,`title`,`money`,`chance`,`img`,`goods_id`,`goods_name`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'ops_raffle_title_1',10.00,10.00,'ops_raffle_img_1',1,'ops_raffle_goods_name_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'ops_raffle_title_2',20.00,20.00,'ops_raffle_img_2',1,'ops_raffle_goods_name_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'ops_raffle_title_3',30.00,30.00,'ops_raffle_img_3',1,'ops_raffle_goods_name_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'ops_raffle_title_4',40.00,40.00,'ops_raffle_img_4',1,'ops_raffle_goods_name_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'ops_raffle_title_5',50.00,50.00,'ops_raffle_img_5',1,'ops_raffle_goods_name_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_raffle_log`;
INSERT INTO `ops_raffle_log` (`id`,`uid`,`username`,`phone`,`is_test`,`raffle_id`,`raffle_name`,`desc`,`amount`,`img`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,1,'ops_raffle_log_raffle_name_1','ops_raffle_log_desc_1',10.00,'ops_raffle_log_img_1',@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,2,'ops_raffle_log_raffle_name_2','ops_raffle_log_desc_2',20.00,'ops_raffle_log_img_2',@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,3,'ops_raffle_log_raffle_name_3','ops_raffle_log_desc_3',30.00,'ops_raffle_log_img_3',@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,4,'ops_raffle_log_raffle_name_4','ops_raffle_log_desc_4',40.00,'ops_raffle_log_img_4',@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,5,'ops_raffle_log_raffle_name_5','ops_raffle_log_desc_5',50.00,'ops_raffle_log_img_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `ops_sign_in`;
INSERT INTO `ops_sign_in` (`id`,`uid`,`username`,`phone`,`amount`,`is_test`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',10.00,0,@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',20.00,0,@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',30.00,0,@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',40.00,0,@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',50.00,1,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `pay_payout`;
INSERT INTO `pay_payout` (`id`,`title`,`code`,`status`,`upper`,`upper_id`,`upper_name`,`upper_status`,`upper_img`,`class_id`,`class_name`,`class_img`,`class_status`,`class_rate`,`create_time`,`update_time`,`create_at`,`update_at`,`min`,`max`,`quota`) VALUES
(1,'pay_payout_title_1','PAY_PAYOUT_0001',0,'pay_payout_upper_1',1,'pay_payout_upper_name_1',1,'pay_payout_upper_img_1',1,'pay_payout_class_name_1','pay_payout_class_img_1',1,10.00,@ts-60,@ts-60,NOW(),NOW(),10.00,10.00,10.00),
(2,'pay_payout_title_2','PAY_PAYOUT_0002',1,'pay_payout_upper_2',1,'pay_payout_upper_name_2',2,'pay_payout_upper_img_2',1,'pay_payout_class_name_2','pay_payout_class_img_2',2,20.00,@ts-120,@ts-120,NOW(),NOW(),20.00,20.00,20.00),
(3,'pay_payout_title_3','PAY_PAYOUT_0003',2,'pay_payout_upper_3',1,'pay_payout_upper_name_3',3,'pay_payout_upper_img_3',1,'pay_payout_class_name_3','pay_payout_class_img_3',3,30.00,@ts-180,@ts-180,NOW(),NOW(),30.00,30.00,30.00),
(4,'pay_payout_title_4','PAY_PAYOUT_0004',0,'pay_payout_upper_4',1,'pay_payout_upper_name_4',4,'pay_payout_upper_img_4',1,'pay_payout_class_name_4','pay_payout_class_img_4',4,40.00,@ts-240,@ts-240,NOW(),NOW(),40.00,40.00,40.00),
(5,'pay_payout_title_5','PAY_PAYOUT_0005',1,'pay_payout_upper_5',1,'pay_payout_upper_name_5',5,'pay_payout_upper_img_5',1,'pay_payout_class_name_5','pay_payout_class_img_5',5,50.00,@ts-300,@ts-300,NOW(),NOW(),50.00,50.00,50.00);

DELETE FROM `pay_quota`;
INSERT INTO `pay_quota` (`id`,`title`,`img`,`rate`,`status`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'pay_quota_title_1','pay_quota_img_1',10.00,0,@ts-60,@ts-60,NOW(),NOW()),
(2,'pay_quota_title_2','pay_quota_img_2',20.00,1,@ts-120,@ts-120,NOW(),NOW()),
(3,'pay_quota_title_3','pay_quota_img_3',30.00,2,@ts-180,@ts-180,NOW(),NOW()),
(4,'pay_quota_title_4','pay_quota_img_4',40.00,0,@ts-240,@ts-240,NOW(),NOW()),
(5,'pay_quota_title_5','pay_quota_img_5',50.00,1,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `report_pay_channal`;
INSERT INTO `report_pay_channal` (`id`,`pay_name`,`pay_id`,`pay_type`,`success`,`success_count`,`fai_count`,`fail`,`create_time`,`update_time`,`create_at`,`update_at`,`date_time`) VALUES
(1,'report_pay_channal_pay_name_1','REPORT_PAY_CHANNAL_0001',1,10.00,1,1,10.00,@ts-60,@ts-60,NOW(),NOW(),'2026-03-11'),
(2,'report_pay_channal_pay_name_2','REPORT_PAY_CHANNAL_0002',2,20.00,2,2,20.00,@ts-120,@ts-120,NOW(),NOW(),'2026-03-12'),
(3,'report_pay_channal_pay_name_3','REPORT_PAY_CHANNAL_0003',3,30.00,3,3,30.00,@ts-180,@ts-180,NOW(),NOW(),'2026-03-13'),
(4,'report_pay_channal_pay_name_4','REPORT_PAY_CHANNAL_0004',4,40.00,4,4,40.00,@ts-240,@ts-240,NOW(),NOW(),'2026-03-14'),
(5,'report_pay_channal_pay_name_5','REPORT_PAY_CHANNAL_0005',5,50.00,5,5,50.00,@ts-300,@ts-300,NOW(),NOW(),'2026-03-15');

DELETE FROM `report_user_month`;
INSERT INTO `report_user_month` (`id`,`uid`,`username`,`phone`,`is_test`,`date_time`,`create_time`,`update_time`,`create_at`,`update_at`,`recharge`,`recharge_count`,`recharge_fee`,`withdraw`,`withdraw_count`,`withdraw_fee`,`bet`,`bet_valid`,`bet_count`,`rebate`,`event`,`welfare`,`profit`,`company_profit`,`venue_fee`,`admin_incr`,`admin_decr`) VALUES
(1,10001,'agent001','13900000001',0,'2026-03-11',@ts-60,@ts-60,NOW(),NOW(),10.00,1,10.00,10.00,1,10.00,10.00,10.00,1,10.00,10.00,10.00,10.00,10.00,10.00,10.00,10.00),
(2,10002,'user001','13900000002',0,'2026-03-12',@ts-120,@ts-120,NOW(),NOW(),20.00,2,20.00,20.00,2,20.00,20.00,20.00,2,20.00,20.00,20.00,20.00,20.00,20.00,20.00,20.00),
(3,10003,'test001','13900000003',0,'2026-03-13',@ts-180,@ts-180,NOW(),NOW(),30.00,3,30.00,30.00,3,30.00,30.00,30.00,3,30.00,30.00,30.00,30.00,30.00,30.00,30.00,30.00),
(4,10002,'user001','13900000002',0,'2026-03-14',@ts-240,@ts-240,NOW(),NOW(),40.00,4,40.00,40.00,4,40.00,40.00,40.00,4,40.00,40.00,40.00,40.00,40.00,40.00,40.00,40.00),
(5,10001,'agent001','13900000001',1,'2026-03-15',@ts-300,@ts-300,NOW(),NOW(),50.00,5,50.00,50.00,5,50.00,50.00,50.00,5,50.00,50.00,50.00,50.00,50.00,50.00,50.00,50.00);

DELETE FROM `risk_block_area`;
INSERT INTO `risk_block_area` (`id`,`title`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'risk_block_area_title_1','risk_block_area_desc_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'risk_block_area_title_2','risk_block_area_desc_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'risk_block_area_title_3','risk_block_area_desc_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'risk_block_area_title_4','risk_block_area_desc_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'risk_block_area_title_5','risk_block_area_desc_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `risk_block_device`;
INSERT INTO `risk_block_device` (`id`,`title`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'risk_block_device_title_1','risk_block_device_desc_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'risk_block_device_title_2','risk_block_device_desc_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'risk_block_device_title_3','risk_block_device_desc_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'risk_block_device_title_4','risk_block_device_desc_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'risk_block_device_title_5','risk_block_device_desc_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `risk_block_ip`;
INSERT INTO `risk_block_ip` (`id`,`title`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'risk_block_ip_title_1','risk_block_ip_desc_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'risk_block_ip_title_2','risk_block_ip_desc_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'risk_block_ip_title_3','risk_block_ip_desc_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'risk_block_ip_title_4','risk_block_ip_desc_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'risk_block_ip_title_5','risk_block_ip_desc_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `risk_same_pwd`;
INSERT INTO `risk_same_pwd` (`id`,`uid`,`username`,`phone`,`connection`,`is_test`,`create_time`,`update_time`,`create_at`,`update_at`,`action`) VALUES
(1,10001,'agent001','13900000001','10001,10002',0,@ts-60,@ts-60,NOW(),NOW(),0),
(2,10002,'user001','13900000002','10001,10002',0,@ts-120,@ts-120,NOW(),NOW(),1),
(3,10003,'test001','13900000003','10001,10002',0,@ts-180,@ts-180,NOW(),NOW(),2),
(4,10002,'user001','13900000002','10001,10002',0,@ts-240,@ts-240,NOW(),NOW(),3),
(5,10001,'agent001','13900000001','10001,10002',1,@ts-300,@ts-300,NOW(),NOW(),4);

DELETE FROM `sys_dict`;
INSERT INTO `sys_dict` (`id`,`dict_type`,`dict_key`,`value_type`,`value_string`,`value_text`,`value_int`,`value_float`,`value_json`,`enum_limit`,`label`,`sort`,`status`,`remark`,`create_at`,`create_time`,`update_time`,`update_at`) VALUES
(1,'sys_dict_dict_type_1','sys_dict_dict_key_1',0,'sys_dict_value_string_1','sys_dict_value_text_1',1,10.00,'{"selected":"sys_dict_value_json_1"}','["opt_a","opt_b"]','sys_dict_label_1',1,0,'sys_dict_remark_1',NOW(),@ts-60,@ts-60,NOW()),
(2,'sys_dict_dict_type_2','sys_dict_dict_key_2',1,'sys_dict_value_string_2','sys_dict_value_text_2',2,20.00,'{"selected":"sys_dict_value_json_2"}','["opt_a","opt_b"]','sys_dict_label_2',2,1,'sys_dict_remark_2',NOW(),@ts-120,@ts-120,NOW()),
(3,'sys_dict_dict_type_3','sys_dict_dict_key_3',2,'sys_dict_value_string_3','sys_dict_value_text_3',3,30.00,'{"selected":"sys_dict_value_json_3"}','["opt_a","opt_b"]','sys_dict_label_3',3,0,'sys_dict_remark_3',NOW(),@ts-180,@ts-180,NOW()),
(4,'sys_dict_dict_type_4','sys_dict_dict_key_4',3,'sys_dict_value_string_4','sys_dict_value_text_4',4,40.00,'{"selected":"sys_dict_value_json_4"}','["opt_a","opt_b"]','sys_dict_label_4',4,1,'sys_dict_remark_4',NOW(),@ts-240,@ts-240,NOW()),
(5,'sys_dict_dict_type_5','sys_dict_dict_key_5',4,'sys_dict_value_string_5','sys_dict_value_text_5',5,50.00,'{"selected":"sys_dict_value_json_5"}','["opt_a","opt_b"]','sys_dict_label_5',5,0,'sys_dict_remark_5',NOW(),@ts-300,@ts-300,NOW());

DELETE FROM `sys_file`;
INSERT INTO `sys_file` (`id`,`name`,`path`,`url`,`size`,`md5`,`type`,`mime`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'sys_file_name_1','sys_file_path_1','sys_file_url_1',1,'sys_file_md5_1',0,'sys_file_mime_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'sys_file_name_2','sys_file_path_2','sys_file_url_2',2,'sys_file_md5_2',1,'sys_file_mime_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'sys_file_name_3','sys_file_path_3','sys_file_url_3',3,'sys_file_md5_3',2,'sys_file_mime_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'sys_file_name_4','sys_file_path_4','sys_file_url_4',4,'sys_file_md5_4',0,'sys_file_mime_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'sys_file_name_5','sys_file_path_5','sys_file_url_5',5,'sys_file_md5_5',1,'sys_file_mime_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `sys_log`;
INSERT INTO `sys_log` (`id`,`admin_id`,`admin_name`,`url`,`desc`,`params`,`type`,`ip`,`ip_address`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,1,'admin','sys_log_url_1','sys_log_desc_1','sys_log_params_1',0,'10.0.0.1','CN,GD,SZ',@ts-60,@ts-60,NOW(),NOW()),
(2,1,'admin','sys_log_url_2','sys_log_desc_2','sys_log_params_2',1,'10.0.0.2','CN,GD,SZ',@ts-120,@ts-120,NOW(),NOW()),
(3,1,'admin','sys_log_url_3','sys_log_desc_3','sys_log_params_3',2,'10.0.0.3','CN,GD,SZ',@ts-180,@ts-180,NOW(),NOW()),
(4,1,'admin','sys_log_url_4','sys_log_desc_4','sys_log_params_4',0,'10.0.0.4','CN,GD,SZ',@ts-240,@ts-240,NOW(),NOW()),
(5,1,'admin','sys_log_url_5','sys_log_desc_5','sys_log_params_5',1,'10.0.0.5','CN,GD,SZ',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_address`;
INSERT INTO `user_address` (`id`,`uid`,`username`,`phone`,`is_test`,`address_name`,`address_phone`,`address_city`,`address_place`,`default`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,'user_address_address_name_1','user_address_address_phone_1','user_address_address_city_1','user_address_address_place_1',1,@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,'user_address_address_name_2','user_address_address_phone_2','user_address_address_city_2','user_address_address_place_2',2,@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,'user_address_address_name_3','user_address_address_phone_3','user_address_address_city_3','user_address_address_place_3',3,@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,'user_address_address_name_4','user_address_address_phone_4','user_address_address_city_4','user_address_address_place_4',4,@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,'user_address_address_name_5','user_address_address_phone_5','user_address_address_city_5','user_address_address_place_5',5,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_bets`;
INSERT INTO `user_bets` (`uid`,`bets`,`over_loss`,`over_loss_id`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(10001,10.00,10.00,'user_bets_over_loss_id_1',@ts-60,@ts-60,NOW(),NOW()),
(10002,20.00,20.00,'user_bets_over_loss_id_2',@ts-120,@ts-120,NOW(),NOW()),
(10003,30.00,30.00,'user_bets_over_loss_id_3',@ts-180,@ts-180,NOW(),NOW()),
(10004,40.00,40.00,'user_bets_over_loss_id_4',@ts-240,@ts-240,NOW(),NOW()),
(10005,50.00,50.00,'user_bets_over_loss_id_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_bets_log`;
INSERT INTO `user_bets_log` (`id`,`uid`,`username`,`phone`,`is_test`,`order_id`,`amount`,`before`,`after`,`desc`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,'USER_BETS_LOG_0001',10.00,10.00,10.00,'user_bets_log_desc_1',@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,'USER_BETS_LOG_0002',20.00,20.00,20.00,'user_bets_log_desc_2',@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,'USER_BETS_LOG_0003',30.00,30.00,30.00,'user_bets_log_desc_3',@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,'USER_BETS_LOG_0004',40.00,40.00,40.00,'user_bets_log_desc_4',@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,'USER_BETS_LOG_0005',50.00,50.00,50.00,'user_bets_log_desc_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_label`;
INSERT INTO `user_label` (`id`,`name`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'user_label_name_1',@ts-60,@ts-60,NOW(),NOW()),
(2,'user_label_name_2',@ts-120,@ts-120,NOW(),NOW()),
(3,'user_label_name_3',@ts-180,@ts-180,NOW(),NOW()),
(4,'user_label_name_4',@ts-240,@ts-240,NOW(),NOW()),
(5,'user_label_name_5',@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_label_assist`;
INSERT INTO `user_label_assist` (`id`,`uid`,`label_id`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,1,@ts-60,@ts-60,NOW(),NOW()),
(2,10002,2,@ts-120,@ts-120,NOW(),NOW()),
(3,10003,3,@ts-180,@ts-180,NOW(),NOW()),
(4,10002,4,@ts-240,@ts-240,NOW(),NOW()),
(5,10001,5,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_message`;
INSERT INTO `user_message` (`id`,`uid`,`username`,`phone`,`is_test`,`is_system`,`admin_id`,`admin_name`,`title`,`content`,`view_time`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,1,1,'admin','user_message_title_1','user_message_content_1',@ts-60,@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,2,1,'admin','user_message_title_2','user_message_content_2',@ts-120,@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,3,1,'admin','user_message_title_3','user_message_content_3',@ts-180,@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,4,1,'admin','user_message_title_4','user_message_content_4',@ts-240,@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,5,1,'admin','user_message_title_5','user_message_content_5',@ts-300,@ts-300,@ts-300,NOW(),NOW());

DELETE FROM `user_yuebao`;
INSERT INTO `user_yuebao` (`id`,`uid`,`username`,`phone`,`is_test`,`type`,`money`,`finish_money`,`info`,`create_time`,`settle_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,10001,'agent001','13900000001',0,0,10.00,10.00,'user_yuebao_info_1',@ts-60,@ts-60,@ts-60,NOW(),NOW()),
(2,10002,'user001','13900000002',0,1,20.00,20.00,'user_yuebao_info_2',@ts-120,@ts-120,@ts-120,NOW(),NOW()),
(3,10003,'test001','13900000003',0,2,30.00,30.00,'user_yuebao_info_3',@ts-180,@ts-180,@ts-180,NOW(),NOW()),
(4,10002,'user001','13900000002',0,0,40.00,40.00,'user_yuebao_info_4',@ts-240,@ts-240,@ts-240,NOW(),NOW()),
(5,10001,'agent001','13900000001',1,1,50.00,50.00,'user_yuebao_info_5',@ts-300,@ts-300,@ts-300,NOW(),NOW());

-- ------------------------------------------------------------------
-- Day 3 parity tables (data seeds related to existing business data)
-- ------------------------------------------------------------------
DELETE FROM `payment_behalf`;
INSERT INTO `payment_behalf`
(`id`,`title`,`code`,`status`,`upper`,`upper_id`,`upper_name`,`upper_status`,`upper_img`,`class_id`,`class_name`,`class_status`,`class_img`,`class_rate`,`min`,`max`,`quota`,`accounts`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(1,'USDT Behalf Channel','BEHALF_USDT_1',0,'MANUAL',1,'ManualGateway',0,'/img/up.png',1,'USDT-TRC20',0,'/img/usdt.png',1.00,50.00,50000.00,200000.00,'1',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW());

DELETE FROM `report_pay_api`;
INSERT INTO `report_pay_api`
(`id`,`pay_name`,`pay_id`,`pay_type`,`success`,`success_count`,`fail_count`,`fail`,`create_time`,`update_time`,`create_at`,`update_at`,`date_time`)
VALUES
(1,'ManualGateway','MANUAL',0,500.00,1,1,300.00,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW(),CURDATE()),
(2,'ManualGateway','MANUAL',1,0.00,0,1,200.00,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW(),CURDATE());

DELETE FROM `user_state`;
INSERT INTO `user_state`
(`uid`,`username`,`phone`,`user_role`,`is_test`,`sfz_status`,`ban_buy`,`ban_lock`,`ban_sigin`,`ban_raffle`,`ban_login`,`ban_invite`,`ban_recharge`,`ban_withdraw`,`ban_exchange`,`ban_bets`,`ban_transfer`,`kick_out`,`is_valid_user`,`create_time`,`update_time`,`create_at`,`update_at`)
VALUES
(10001,'agent001','13900000001',1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW()),
(10002,'user001','13900000002',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW()),
(10003,'test001','13900000003',0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW());

DELETE FROM `user_tags`;
INSERT INTO `user_tags` (`id`,`title`,`create_time`,`update_time`,`create_at`,`update_at`) VALUES
(1,'vip',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW()),
(2,'agent',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW()),
(3,'test',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),NOW(),NOW());

DELETE FROM `job_fail`;
INSERT INTO `job_fail` (`id`,`connection`,`queue`,`payload`,`exception`,`failed_at`) VALUES
(1,'database','default','{\"job\":\"seed\"}','Seeded failed job record',NOW());


CREATE TABLE `sys_operate_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int NOT NULL DEFAULT '0' COMMENT '管理id',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `title` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作标题(可选)',
  `permission` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '权限标识(可选)',
  `module` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '模块/业务(可选)',
  `method` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求方法',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求URL',
  `route` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '路由/接口标识(可选)',
  `request_data` mediumtext CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '请求参数(JSON/表单)',
  `response_code` int NOT NULL DEFAULT '0' COMMENT '响应码(业务码/HTTP码)',
  `response_msg` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '响应消息(可选)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1成功 0失败',
  `duration_ms` int NOT NULL DEFAULT '0' COMMENT '耗时ms',
  `login_ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作IP',
  `ip_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP归属地',
  `user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'UA',
  `referer` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '来源(可选)',
  `target_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作对象类型(可选)',
  `target_id` bigint NOT NULL DEFAULT '0' COMMENT '操作对象ID(可选)',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间(时间戳)',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间(时间戳)',
  PRIMARY KEY (`id`,`uid`),
  KEY `uid` (`uid`),
  KEY `username` (`username`),
  KEY `route` (`route`),
  KEY `create_time` (`create_time`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci COMMENT='后台操作日志表'

CREATE TABLE `sys_system_log` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id',
  `level` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志级别 debug/info/warning/error...',
  `channel` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志通道/来源(可选)',
  `module` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '模块(可选)',
  `message` text CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '日志内容',
  `context` mediumtext CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '上下文(JSON)',
  `extra` mediumtext CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '额外信息(JSON)',
  `exception_class` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '异常类(可选)',
  `exception_message` text CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '异常信息(可选)',
  `file` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '文件(可选)',
  `line` int NOT NULL DEFAULT '0' COMMENT '行号(可选)',
  `trace` mediumtext CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci COMMENT '堆栈(可选)',
  `request_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求ID(链路追踪)',
  `method` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求方法(可选)',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求URL(可选)',
  `ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP(可选)',
  `user_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'UA(可选)',
  `uid` int NOT NULL DEFAULT '0' COMMENT '关联管理员ID(可选)',
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '关联管理员用户名(可选)',
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `create_time` int NOT NULL DEFAULT '0' COMMENT '创建时间(时间戳)',
  `update_time` int NOT NULL DEFAULT '0' COMMENT '更新时间(时间戳)',
  PRIMARY KEY (`id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_level` (`level`),
  KEY `idx_channel` (`channel`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci COMMENT='系统日志表';