-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.7.38-log - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
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


-- 导出 facai11 的数据库结构
CREATE DATABASE IF NOT EXISTS `facai11` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `facai11`;

-- 导出  表 facai11.article 结构
CREATE TABLE IF NOT EXISTS `article` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `code` varchar(50) NOT NULL DEFAULT '' COMMENT '文章标识',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
    `release_time` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
    `content` text COMMENT '内容',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `class_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '分类',
    `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `title` (`title`)
    ) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';

-- 正在导出表  facai11.article 的数据：~31 rows (大约)
DELETE FROM `article`;
INSERT INTO `article` (`id`, `title`, `img`, `code`, `desc`, `release_time`, `content`, `create_time`, `update_time`, `create_at`, `update_at`, `class_id`, `class_name`, `sort`) VALUES
                                                                                                                                                                                      (1, '白皮书', '/storage/20251122/42727f1702ba2df037f8115f634280a2.jpg', 'white_paper', '白皮书', 1763568000, '&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/cc16f0449a03e029f155fe81ea948ba1.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/3795d88d5487a92eeb9b0e2e5f96b8e2.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/cddcba4e0ae8f399045c4a7416daa85f.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/2a6c6c57844228b92f87ac916f2ca524.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/4c468e81d1941713eb7d90eaa9cda9ef.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/f6225785b3063c8311dbe57493a20e72.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/448b6bee3c04ccb4fee1354d5f07070f.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/0f06583b30f2f340605e41fbe25b2a7e.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/e55993be99296271407fabe9aef1620a.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/880b32d8f2bbb777f1857c977d76d0f6.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/ae1c698cf070a029cdd673a7a3343ca7.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/703cb7da76d312f4448d079b2cda7e0b.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/0c41f415c1db115520726b3159ffa108.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/fb7ab00247ad211743a292f92f0ba5c7.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/1e124cba9ec2cca59d2f19fe6d54cdaa.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/00fc2b27d2fb77300ea65c633fee06d1.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/8baa43bc943f33d1156f4d4bc639de84.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/01f0b65c974ff69a10187f823da2ae27.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/e11f29858433d9ca95201fa47d9afb6a.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/b4dea74efb78b1f91b08c565ed79d262.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/7bf0a55a8e0632e517bebdb11b8974e2.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/7b4de12ee245e686a44d62fbae38f389.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/294b93368826bc35bb318ad95a32659a.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/ee28189b1175121ae645a0b13e5591ba.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/d1816203f58091c1bedaad52f99e93c7.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/d48db1fe3376fccf57f63f55e7a6a19e.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/2aececc286e562c1286bcb7440b44201.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/2145b323cb4bdc67387e49f3af37b96c.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251216/1e28530ef680e821ab2761499f0c4b83.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/eb6e0a4cafa29372c1144af005089c39.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/4ffd91adf7d945ce4df197f6ad1080af.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/31e21fa3c3d8090ba9f9725049f8fd9c.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/d6aa50f2115109e3d6878ac56b5b08da.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/acbf08cbbd212a0d05f3ba3c1ece69ec.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/c94f640adc4f67ca3e0266e1692eac2c.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/1333674463b50eb90d825a3bdda1af2e.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/0434c01986d5f516ab240a20bea1ce15.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/7e5acec7416c00487d1caedfc5659d62.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/933155efdeb1e1b6c75b83a9339208c8.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/14b227f4bb9f6ee628d6210d6e0a3374.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/4fd7d9676176070dcabdcad6bd4b3a18.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251202/b5aa6b11461f0e8e4b9c5bcf192652c9.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;', 1762434856, 1766472585, '2025-11-06 21:14:16', '2025-11-06 21:14:16', 4, '文章', 0),
                                                                                                                                                                                      (3, '公司介绍', '/storage/20251122/0850e8a638a9ceab160980bbc9ec335d.jpg', 'about', '公司介绍', 1763615660, '&amp;lt;p&amp;gt;香港水悦方（国际）保健品有限公司（Hong Kong Shuiyuefang (International) Health Products Limited）成立于 2025 年 7 月 28 日，由法定代表人李全绪先生担任领导。作为澳大利亚知名天然营养品品牌 BLACKMORES 澳佳宝在亚太地区的重要战略布局，公司总部设立于香港这一国际金融中心，依托香港自由开放的商业环境和便利的国际贸易条件，致力于打造辐射中国内地及东南亚市场的高端健康产品运营平台。公司主营业务涵盖高端膳食营养补充剂的研发、生产与销售，同时整合保健食品销售、专业营养咨询服务、进口营养品业务以及相关健康产品销售等多元化业务板块，形成完整的健康产业生态链。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;公司成立的战略背景源于后疫情时代中国保健品市场的迅猛发展。BLACKMORES 澳佳宝凭借其敏锐的市场洞察力，看准中国消费者日益增长的健康需求，决定通过成立水悦方子公司深耕中国市场。这一战略举措将重点推进三大核心计划：首先是在中国建立符合国际标准的本土化生产线，确保产品更贴合中国消费者的需求；其次是构建线上线下全渠道销售网络，实现销售模式创新；最后是运用数字化营销手段，通过互联网叠加的品牌推广策略，快速提升品牌知名度和市场渗透率。这些举措将有效扩大 BLACKMORES 在中国市场的销售版图和品牌影响力。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;作为 BLACKMORES 全球战略的重要组成部分，水悦方将充分继承和发扬母公司近百年在自然健康领域的科研积淀。公司计划充分利用香港的区位优势和自由贸易港政策，整合全球优质健康资源，引进国际先进的生产技术和质量管理体系。同时，公司将深入调研中国消费者的健康需求，开发更符合本土市场的产品系列，并通过持续创新不断提升服务质量。未来，水悦方将继续秉持 “科技创新推动健康生活” 的企业使命，为中国消费者提供更安全、更有效、更个性化的健康解决方案，助力提升全民健康水平。&amp;lt;/p&amp;gt;', 1763621062, 1767600972, '2025-11-20 14:44:22', '2025-11-20 14:44:22', 4, '文章', 0),
                                                                                                                                                                                      (4, 'usdt 教程', '/storage/20251122/343663bea38eaf0d3fa73afe5f540489.jpg', 'usdt_tutorial', 'usdt 教程', 0, '', 1763621162, 1763792133, '2025-11-20 14:46:02', '2025-11-20 14:46:02', 4, '文章', 0),
                                                                                                                                                                                      (5, '会员等级', '/storage/20251122/10c9fbb5a3983de4f16867251a4e7be5.jpg', 'membership', '会员等级', 0, '&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251211/64bfedafeb79062fbf95d64462ead8f7.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;', 1763622404, 1765425126, '2025-11-20 15:06:44', '2025-11-20 15:06:44', 4, '文章', 0),
                                                                                                                                                                                      (6, '代理等级', '/storage/20251122/ab697031cd64fa1701bdbe868ee12ace.jpg', 'agent_level', '代理等级', 1763617059, '&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://facai11-api.24game.top/storage/edit/20251211/287610c31385bfcadfa026fc47f7b263.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;', 1763622463, 1765425152, '2025-11-20 15:07:43', '2025-11-20 15:07:43', 4, '文章', 0),
                                                                                                                                                                                      (7, '收益分红', '/storage/20251122/3d0075592ae19738457d72d2760e2ff5.jpg', 'profit_sharing', '收益分红', 0, '&amp;lt;pre&amp;gt;&amp;lt;code &amp;gt;&amp;amp;lt;table style=&amp;quot;width:100%; border-collapse:collapse;font-family: &amp;#039;微软雅黑&amp;#039;; background:#ffffff; font-size:14px; border-radius:12px; overflow:hidden;&amp;quot;&amp;amp;gt;\n  &amp;amp;lt;thead style=&amp;quot;background:#f3f7ff;&amp;quot;&amp;amp;gt;\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;th style=&amp;quot;padding:12px 10px; text-align:center; font-weight:600; color:#000; border-bottom:1px solid #e6ecf5;&amp;quot;&amp;amp;gt;等级&amp;amp;lt;/th&amp;amp;gt;\n      &amp;amp;lt;th style=&amp;quot;padding:12px 10px; text-align:center; font-weight:600; color:#000; border-bottom:1px solid #e6ecf5;&amp;quot;&amp;amp;gt;一级&amp;amp;lt;/th&amp;amp;gt;\n      &amp;amp;lt;th style=&amp;quot;padding:12px 10px; text-align:center; font-weight:600; color:#000; border-bottom:1px solid #e6ecf5;&amp;quot;&amp;amp;gt;二级&amp;amp;lt;/th&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n  &amp;amp;lt;/thead&amp;amp;gt;\n\n  &amp;amp;lt;tbody&amp;amp;gt;\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V0 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;3%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;2%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V1 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;6%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;5%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V2 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;8%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;7%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V3 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;10%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;9%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V4 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;15%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;14%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500; border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;V5 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;23%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;22%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n\n    &amp;amp;lt;tr&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center; color:#000;font-weight:500;&amp;quot;&amp;amp;gt;V6 下级收益分红&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;33%&amp;amp;lt;/td&amp;amp;gt;\n      &amp;amp;lt;td style=&amp;quot;padding:12px 10px; text-align:center;border-bottom:1px solid #f0f0f0;&amp;quot;&amp;amp;gt;32%&amp;amp;lt;/td&amp;amp;gt;\n    &amp;amp;lt;/tr&amp;amp;gt;\n  &amp;amp;lt;/tbody&amp;amp;gt;\n&amp;amp;lt;/table&amp;amp;gt;\n&amp;lt;/code&amp;gt;&amp;lt;/pre&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;分红机制&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;以普通会员为例:A推荐了B，B为A的一级下线会员，B每日在平台获得收益1000元，则A获得30元的收益分红;B推荐了C，C为A的二级下线会员，C每日在平台获得收益1000元，则A获得20元的收益分红;根据会员等级不同以此类推.&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1763622527, 1764158949, '2025-11-20 15:08:47', '2025-11-20 15:08:47', 4, '文章', 0),
                                                                                                                                                                                      (8, '充值提现', '/storage/20251122/faecda3c1c3ff7676d7b5288d75cf350.jpg', 'recharge_withdraw', '充值提现', 0, '&amp;lt;p&amp;gt;一、充值流程详解：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 首先，进入 “我的” 页面，点击 “充值” 按钮。输入您要充值的金额，选择 “TRC20 USDT 充值”。会出现钱包地址和一个二维码，这个钱包地址是您的一个充值专属地址，您可以使用欧易，TP 钱包，币安等向您的专属充值地址转账，转帐成功后，充值的金额会自动充值到你的平台帐户，在您转帐请您仔细核对地址，因为钱包转帐是不可以撤回或者取消，请务必认真核对信息。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 转帐成功后，通常在 15 分钟之内即可完成审核。此时，您可返回首页点击 “我的” 进行刷新，以便查收充值金额。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 完成充值后，点击 “项目”，再选择相应的专区，仔细阅读每个产品的项目详情，其中包括起购金额、购买等级、产品周期、产品收益以及限购份数等重要内容注：充值时间为全天 24 小时，最低充值金额为 50USDT，充值到账时间为转账成功后的 15 分钟之内，请您及时刷新进行查收。若充值未及时到账，请联系在线客服进行处理。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;提现申请步骤：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 点击 “我的” 页面，选择 “提现”。在这里，您可以选择 “USDT 提现” 或者 “银行卡提现”。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 输入 “提现金额”（需可提余额）。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 选择 “提现地址” 或者 “银行卡账户”（请确认是否已添加提现地址或提现银行卡账户）。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4. 输入支付密码（支付密码为注册时设定），最后点击 “确认提现” 即可。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;提现到账时间说明：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 提现申请时间为 10:00 - 22:00，节假日照常提现。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 最低提现金额为 200 元，提现时间 72 小时之内，具体到账时间取决于交易所网络或所属银行的规则。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 提现间隔时间为 72 小时。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;提现失败原因分析；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 检查提现所添加的钱包是否有误；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 确认银行开户行信息是否错误；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 核实银行账号 / 户名是否错误，或是账号和户名不符；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4. 查看是否绑定信用卡进行提现；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;5. 确认银行账户是否冻结或正在办理挂失。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;注：选择银行卡提现，将扣除提现金额 2% 的手续费。使用 USDT 进行提现，则无需扣除手续费，需仔细核实钱包地址是否准确无误。也可选择可提余额继续认购我们的产品，即可获得可提金额 2% 的复投奖励。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;关于提现银行卡的规定：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 为了保障您的账户资金安全，只允许绑定实名认证本人的银行卡和钱包地址进行提现，否则提现申请将不予通过。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;用户充值金额提现规定：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 由于用户充值账户不受限制，为防止出现洗黑钱等违法犯罪行为以及恶意刷流水等不当行为，用户充值的账户金额不可直接提现。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 用户需要认购项目产品后方可进行提现操作。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;注：用户认购前，请仔细阅读以上流程问答，如有任何疑问，请联系在线客服进行咨询，我们将竭诚为您服务。&amp;lt;/p&amp;gt;', 1763622603, 1763792075, '2025-11-20 15:10:03', '2025-11-20 15:10:03', 4, '文章', 0),
                                                                                                                                                                                      (9, '安全保障', '/storage/20251122/66e4dd8973c29018345b27e289d3d585.jpg', 'safety_guarantee', '安全保障', 0, '&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;APP安全&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;由自有专业技术团队开发，核心团队来自国内外知名IT企业,在信息安全和数据安全方面有着非常丰富的经验,有软件开发证书和反诈证书。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;支付安全&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;存管账户由存管银行进行实名认证,多重审核,并在存管银行页面设置存管交易密码,平台不存储交易密码。充值、提现需在存管银行页面验证存管交易密码,验证通过后才能完成。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;网络安全&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;采用了商用化的云盾安全产品,结合腾讯云、华为云、阿里云等计算强大的数据分析能力提供DDOS防护,主机入侵防护,以及漏洞检测,木马检测等一整套安全服务。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;隐私安全&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;数据交互全程https加密，内部网络和外部网络隔离，对恶意注册及恶意登录猜解密码的行为有自动检测和锁定功能。信息安全从业人员都具有CISSP国 际信息系统安全专家资质,能够做到了用广数据的隐私保护。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;主机系统安全&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;采用主流的安全密码策略,对远程管理端口双向白名单限制。并对主机安全日志进行定期审计，漏洞安全信息及时修复。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;ul&amp;gt;&amp;lt;li&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(9, 109, 217); font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;火灾备份&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/li&amp;gt;&amp;lt;/ul&amp;gt;&amp;lt;p&amp;gt;机房自有，腾讯云、华为云、阿里云AWS互为设备，可为同城多个机房实时备份，以及异地机房的实时备份,确保数据永久保留不丢失。&amp;lt;/p&amp;gt;', 1763622670, 1764158021, '2025-11-20 15:11:10', '2025-11-20 15:11:10', 4, '文章', 0),
                                                                                                                                                                                      (11, '用户协议', '/storage/20251122/11f443d7c0c5b374a17fd9ba023da1dd.jpg', 'user_agreement', '用户协议', 0, '&amp;lt;p&amp;gt;一、协议总则​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;协议效力：本《水悦方APP用户服务协议》（以下简称 “本协议”）是您与香港水悅方（國際）保健品有限公司以下简称 “我们”）之间就使用 [APP 名称] 提供的各项服务所达成的具有法律约束力的协议。您通过点击 “同意” 或实际使用本 APP 服务，即表示您已充分阅读、理解并接受本协议全部条款，包括我们后续发布的修改版本。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;服务范围：本 APP 提供的服务包括但不限于信息展示、在线互动、功能工具使用等（具体以 APP 实际提供的服务为准），我们有权根据业务发展调整服务内容，调整前将通过 APP 公告等合理方式通知您。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;二、用户注册与使用规范​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;注册义务：您注册成为本 APP 用户时，应提供真实、准确、完整的个人信息（包括但不限于手机号码、身份信息等），并及时更新信息以保证其有效性。若您提供虚假信息，我们有权拒绝为您提供服务或注销您的账号。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;账号管理：您应妥善保管账号及密码，对账号下的所有操作行为承担法律责任。如发现账号被盗用，应立即通知我们，我们将协助进行处理，但不承担因您未妥善保管账号导致的损失。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;使用限制：您不得利用本 APP 从事任何违法违规活动，包括但不限于发布违法信息、侵犯他人权益、恶意攻击 APP 系统、传播病毒等。若您违反上述规定，我们有权采取暂停服务、注销账号等措施，并保留追究您法律责任的权利。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;三、知识产权声明​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本 APP 内的所有内容（包括但不限于文字、图片、图标、软件程序等）的知识产权均归我们或相关权利人所有，受《中华人民共和国著作权法》《商标法》等法律法规保护。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;您仅可在本协议允许的范围内使用上述内容，未经我们或相关权利人书面许可，不得擅自复制、传播、修改、改编或用于商业用途。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;四、隐私保护​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们将按照《[APP 名称] 隐私政策》保护您的个人信息，该隐私政策是本协议的组成部分，与本协议具有同等法律效力。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们将采取合理的技术和管理措施保护您的个人信息安全，防止信息泄露、丢失或被篡改，但不承担因不可抗力、第三方攻击等非我们可控因素导致的信息安全风险。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;五、免责条款​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本 APP 提供的服务均基于 “现状” 和 “可用” 提供，我们不保证服务的不间断性、无错误性，也不保证服务内容的准确性、完整性。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;对于因您使用本 APP 服务或与其他用户互动而产生的任何直接或间接损失，我们不承担赔偿责任，除非该损失是由于我们的故意或重大过失造成的。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;因法律法规调整、政府部门要求、技术升级等原因导致本 APP 服务暂停或终止的，我们不承担违约责任，但将提前通过合理方式通知您。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;六、协议的修改与终止​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们有权根据业务发展和法律法规变化对本协议进行修改，修改后的协议将在 APP 内公示，公示期满后即生效。您继续使用本 APP 服务的，视为接受修改后的协议。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;若您严重违反本协议约定，我们有权单方面终止本协议，注销您的账号，且无需承担违约责任。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;七、法律适用与争议解决​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本协议的订立、履行、解释及争议解决均适用《中华人民共和国民法典》等中华人民共和国法律法规。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;您与我们之间因本协议产生的任何争议，应首先通过友好协商解决；协商不成的，任何一方均有权向我们所在地有管辖权的人民法院提起诉讼。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;八、其他​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本协议未尽事宜，由我们与您另行协商确定，或按照相关法律法规及行业惯例执行。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本协议的任何条款被认定为无效或不可执行的，不影响其他条款的效力。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;您对本协议有任何疑问，可通过 APP 内的客服渠道与我们联系，我们将在合理时间内予以回复。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1763622787, 1764156687, '2025-11-20 15:13:07', '2025-11-20 15:13:07', 4, '文章', 0),
                                                                                                                                                                                      (12, '常见问题', '/storage/20251122/1b194cde66ec8c7d582997eea5c7492f.jpg', 'common_question', '常见问题', 0, '&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;1.注册成功后手机号码可以修改吗？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;手机号码是登录注册成功后作为用户名，不可以修改。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;2.手机丢失影响账户安全性吗？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;手机丢失并不影响平台账号安全，因账号实名身份证和银行卡是一致的，您只需记住平台账号和密码即可。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;3.账号能否注销？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;不支持账号注销，承诺不管任何情况下平台保护用户信息安全，六个月不登录账号，将自动注销。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;4.账户密码分为几种？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;账户密码有登录密码 交易密码两种，注册后点击【我的】【个人设置】可设置交易密码。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;5.如何修改登录和交易密码？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;登录平台账号后，进入个人中心，点击【账号设置】，点击【修改登录密码】或【修改交易密码】，输入原密码和新密码即可完成。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;6.实名认证后还能修改吗？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;为保证账号的安全性，实名认证成功后，您可以提供双方身份证，并提供手持身份证照片，联系客服进行修改。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;7.提现可以绑定信用卡吗？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;资金绑定所用银行卡仅限储蓄卡，不能用信用卡绑定提现。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;8.提现后多久可以到账？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;发起提现后，将于72小时之内到账您绑定的银行卡，每次提现金额不能低于200元。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;9.账号提现需要手续费吗？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;USDT提现无需手续费，人民币提现需收取提现金额2%的手续费。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;10.提现失败怎么办？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;请检查您绑定的银行卡是否正确，是否和身份认证的姓名一致，并及时联系客服处理。&amp;lt;/p&amp;gt;', 1763622865, 1763792045, '2025-11-20 15:14:25', '2025-11-20 15:14:25', 4, '文章', 0),
                                                                                                                                                                                      (15, '隐私政策', '/storage/20251122/ee66bd4e1f6d270dfde39fe110bad057.jpg', 'privacy_policy', '隐私政策', 0, '&amp;lt;p&amp;gt;一、引言​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;欢迎使用水悦方APP以下简称 “本 APP”，本 APP 由香港水悅方（國際）保健品有限公司（以下简称 “我们”）开发并运营。我们高度重视用户的个人信息安全与隐私保护，依据《中华人民共和国民法典》《中华人民共和国网络安全法》《个人信息保护法》等相关法律法规，制定本隐私政策。本政策旨在明确告知您我们如何收集、使用、存储、共享、转让和公开披露您的个人信息，以及您享有的相关权利和保护措施。请您在使用本 APP 前仔细阅读并理解本政策，您点击 “同意” 或继续使用本 APP，即视为您已充分理解并同意本政策的全部内容。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;二、信息收集与使用​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;（一）收集的信息类型​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;注册登录信息：您在注册或登录本 APP 时，需提供手机号码、验证码、设置的用户名及密码等信息，用于完成账号实名认证和身份核验，保障账号安全。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;设备信息：为保障 APP 的正常运行、优化服务体验及防范安全风险，我们可能收集您的设备型号、操作系统版本、设备 MAC 地址、唯一设备标识符（IMEI、IDFA 等）、网络类型、设备状态等信息。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;使用行为信息：当您使用本 APP 的各项功能时，我们会记录您的操作行为数据，包括浏览记录、搜索记录、点击记录、收藏信息、使用时长等，用于分析用户偏好，为您提供个性化推荐服务，同时优化 APP 的功能和界面设计。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;位置信息：若您使用本 APP 的定位相关功能（如附近服务、地图导航等），我们会在获得您的明确授权后，收集您的实时地理位置信息（GPS 坐标或基站定位信息），该信息仅用于实现对应功能，您可随时在 APP 设置或设备系统设置中关闭定位权限。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;其他信息：您在使用本 APP 的互动功能（如评论、分享、上传内容等）时，主动提供的文字、图片、视频等信息；若您参与我们的活动，可能还需提供姓名、收货地址等信息，用于活动组织和奖品发放。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;（二）信息使用目的​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;为您提供核心服务，包括账号管理、功能使用、内容展示等基础服务；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;优化产品性能与服务质量，根据收集的信息分析用户需求，改进 APP 的功能缺陷和使用体验；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;保障账号和交易安全，防范欺诈、盗号等风险，维护平台秩序；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;向您推送个性化的信息推荐、活动通知等内容（您可通过 APP 设置关闭推送）；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;遵守法律法规要求，配合监管部门的检查与调查。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;三、信息存储​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;存储地点：我们将您的个人信息存储在中国大陆地区的合法合规服务器上，确保信息存储符合地域监管要求。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;存储期限：我们会根据法律法规的规定和业务需求，在实现信息使用目的所需的最短期限内存储您的个人信息。当存储期限届满或您注销账号后，我们将采取删除、匿名化处理等方式，彻底清除您的个人信息，除非法律法规另有规定。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;安全保障：我们采用加密存储、访问权限控制、安全审计等技术和管理措施，防范个人信息泄露、丢失、篡改等风险，保障您的信息安全。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;四、信息共享、转让与公开披露​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;（一）信息共享​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们不会随意将您的个人信息共享给第三方，除非符合以下情形：​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;获得您的明确书面同意或口头同意；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;为履行法律法规规定的义务，或配合司法机关、行政监管机关的合法执法行为；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;与我们的关联公司共享，且关联公司将遵循本隐私政策的规定处理您的信息；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;与第三方服务提供商共享（如支付机构、地图服务提供商等），用于为您提供相应的服务，且我们会与第三方签订保密协议，要求其严格保护您的信息安全。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;（二）信息转让​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;未经您的明确同意，我们不会将您的个人信息转让给任何第三方。若因合并、收购、破产清算等企业经营变更情形需要转让的，我们会提前告知您，并确保受让方继续遵守本隐私政策的规定，否则我们将要求受让方重新获得您的授权。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;（三）信息公开披露​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们仅在以下特殊情况下公开披露您的个人信息：​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;获得您的明确同意；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;依据法律法规规定或司法机关、行政监管机关的强制性要求；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;为保护您或社会公众的合法权益、人身安全，在合理必要的范围内公开披露。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;五、用户的权利​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;您对自己的个人信息享有以下权利，我们将为您提供便捷的操作途径：​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;访问与查询权：您可通过 APP 内的 “个人中心” 等功能，查询您的个人信息（如注册信息、使用记录等）；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;更正权：若您发现个人信息存在错误，可随时申请更正，我们将在核实后及时处理；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;删除权：在符合法律法规规定的情形下，您可申请删除您的个人信息，我们将在核实后及时删除或进行匿名化处理；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;撤回同意权：您可通过设备系统设置或 APP 内的权限管理功能，撤回对某项信息收集的同意（但可能导致部分功能无法正常使用）；​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;账号注销权：您可通过 APP 内的注销流程申请注销账号，账号注销后，我们将按照本政策的规定处理您的个人信息。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;六、未成年人保护​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们高度重视未成年人的隐私保护。若您是未满 18 周岁的未成年人，需在监护人的陪同下阅读本政策，并在监护人的同意下使用本 APP 及提供个人信息。我们不会主动收集未成年人的个人信息，若发现误收集，将立即删除相关信息。监护人可联系我们，对未成年人的个人信息进行查询、更正或删除。​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;七、隐私政策的更新​&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;我们可能会根据法律法规的更新或业务发展需要，对本隐私政策进行修订。修订后的政策将通过 APP 内的弹窗、公告等方式通知您，您继续使用本 APP 即视为同意更新后的政策。我们建议您定期查看本政策，了解最新的隐私保护措施。&amp;lt;/p&amp;gt;', 1763640055, 1766751974, '2025-11-20 20:00:55', '2025-11-20 20:00:55', 4, '文章', 0),
                                                                                                                                                                                      (16, '签到规则', '/storage/20251122/e406f998797c6e4fe90b918582925155.jpg', 'check_in_rules', '签到规则', 1763693334, '&amp;lt;p&amp;gt;1、每天可签到 1 次，每次签到即可获得相应奖励。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2、当日签到后不可补签，请在当日 24:00 前完成签到。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3、签到奖励将在签到成功后 自动发放至您的账户余额。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4、签到奖励不可转让，不可提现，仅可用于平台内消费。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;5、每日签到次数及奖励根据平台活动实时调整，以实际显示为准。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;6、若因网络、设备或其他因素导致签到失败，请重新尝试；若多次失败请联系在线客服处理。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;7、平台有权根据实际运营情况调整签到规则，最终解释权归平台所有。&amp;lt;/p&amp;gt;', 1763693358, 1764828496, '2025-11-21 10:49:18', '2025-11-21 10:49:18', 4, '文章', 0),
                                                                                                                                                                                      (17, '充值规则', '/storage/20251121/32053bcbe7e7fee0ade7f803cbaa7b13.jpg', 'recharge_rules', '充值规则', 1763696316, '&amp;lt;p&amp;gt;【温馨提示】USDT充值请认真核对后，再进行转账充值！每个APP账号都是单独的专属充值地址，最低充值金额为 50USDT，充值到账时间为转账成功后的15分钟之内，请您及时刷新进行查收。若充值未及时到账，请联系在线客服进行处理。&amp;lt;/p&amp;gt;', 1763696327, 1768455532, '2025-11-21 11:38:47', '2025-11-21 11:38:47', 4, '文章', 0),
                                                                                                                                                                                      (18, '抽奖活动规则', '/storage/20251121/6aa0a3d6fd9c7d932d5df05dfefbcf68.jpg', 'lottery_activity_rules', '抽奖活动规则', 1763709851, '&amp;lt;p&amp;gt;1、每次抽奖消耗 1 次抽奖次数，抽奖次数可通过完成任务、活动奖励等方式获取。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2、抽奖后将随机获得对应奖品，中奖结果以系统实际显示为准，不可更改。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3、所有奖品均为随机派发，平台不保证每次抽奖必中奖或获得特定奖项。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4、若抽奖过程中出现网络异常、设备卡顿等情况，以系统后台记录结果为准。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;5、所获奖励将在抽奖成功后 自动发放至您的账户余额或奖品中心。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;6、中奖奖励不可转让，不可折现，不可兑换为其他奖品。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;7、若参与者存在作弊、违规行为（如恶意刷奖、使用外挂、模拟请求等），平台有权取消抽奖资格并收回奖励。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;8、抽奖次数每日可能存在上限，超过上限将无法继续抽奖。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;9、平台有权根据运营情况调整奖励、概率、规则等内容，并在页面上进行更新公告。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;10、本活动最终解释权归平台所有。&amp;lt;/p&amp;gt;', 1763710111, 1763791605, '2025-11-21 15:28:31', '2025-11-21 15:28:31', 4, '文章', 0),
                                                                                                                                                                                      (19, '钱包管理规则', '/storage/20251121/f22ef8576b28a0926a0173ccc23b5be6.jpg', 'wallet_management_rules', '钱包管理规则', 1763726727, '&amp;lt;p&amp;gt;绑定银行卡和USDT钱包地址请仔细核对!钱包地址强烈建议您复制粘贴，不要手动输入，极易造成错误，银行卡请确保姓名和实名信息一致，否则将无法成功提现。提现信息绑定成功后不可自行修改!如需修改请准备好银行卡照片，身份证正反面，本人自拍口述申请修改提现信息的视频等相关资料联系在线客服处理!&amp;lt;/p&amp;gt;', 1763726739, 1763801074, '2025-11-21 20:05:39', '2025-11-21 20:05:39', 4, '文章', 0),
                                                                                                                                                                                      (20, '项目简介说明', '/storage/20251121/c7ba398ff2a96634e877b5dfe8597af2.jpg', 'project_introduction', '项目简介说明', 1763734610, '&amp;lt;p&amp;gt;项目简介说明&amp;lt;/p&amp;gt;', 1763734618, 1763734618, '2025-11-21 22:16:58', '2025-11-21 22:16:58', 4, '文章', 0),
                                                                                                                                                                                      (21, '提现规则', '/storage/20251122/6b8eb30e5dc5ddb400fd423af627a0e1.jpg', 'withdrawal_rules', '提现规则', 1763791760, '&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;1. 提现申请时间为 10点 - 22点，节假日照常提现。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;2. 最低提现金额为 200 元，提现到账时间 72 小时之内，具体到账时间取决于交易所网络或所属银行的规则。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;3. 提现间隔时间为 24小时。&amp;lt;/p&amp;gt;', 1763791846, 1768482698, '2025-11-22 14:10:46', '2025-11-22 14:10:46', 4, '文章', 0),
                                                                                                                                                                                      (24, '项目合同', '/storage/20251129/5084177d7e70071225694cc54436c4b1.png', 'project_contract', '项目合同', 1764405875, '&amp;lt;p&amp;gt;尊敬的客户，欢迎您注册成为本网站用户。在注册前请您仔细阅读如下服务条款：本服务协议双方为本网站与本网站客户，本服务协议具有合同效力。您确认本服务协议后，本服务协议即在您和本网站之间产生法律效力。请您务必在注册之前认真阅读全部服务协议内容，如有任何疑问，可向本网站咨询。无论您事实上是否在注册之前认真阅读了本服务协议，只要您点击协议正本下方的&amp;quot;注册&amp;quot;按钮并按照本网站注册程序成功注册为用户，您的行为仍然表示您同意并签署了本服务协议。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;一、本网站服务条款的确认和接纳&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;本网站各项服务的所有权和运作权归本网站拥有。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;二、用户必须：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;1. 自行配备上网的所需设备，包括个人电脑、调制解调器或其他必备上网装置。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;2.自行负担个人上网所支付的与此服务有关的电话费用、网络费用。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;三、用户在本网站交易平台上不得发布下列违法信息：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;1.反对宪法所确定的基本原则的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;2.危害国家安全，泄露国家秘密，颠覆国家政权，破坏国家统一的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;3.损害国家荣誉和利益的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;4.煽动民族仇恨、民族歧视，破坏民族团结的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;5.破坏国家宗教政策，宣扬邪教和封建迷信的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;6.散布谣言，扰乱社会秩序，破坏社会稳定的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;7.散布淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;8.侮辱或者诽谤他人，侵害他人合法权益的；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;9.含有法律、行政法规禁止的其他内容的。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;四、有关个人资料用户同意：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;1.提供及时、详尽及准确的个人资料。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;2.同意接收来自本网站的信息。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;3.不断更新注册资料，符合及时、详尽准确的要求。所有原始键入的资料将引用为注册资料。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;五、用户在注册时：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;应当选择稳定性及安全性相对较好的电子邮箱，并且同意接受并阅读本网站发往用户的各类电子邮件&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;如用户未及时从自己的电子邮箱接受电子邮件或因用户电子邮箱或用户电子邮件接收及阅读程序本身的问题使电子邮件无法正常接收或阅读的，只要本网站成功发送了电子邮件，应当视为用户已经接收到相关的电子邮件。电子邮件在发信服务器上所记录的发出时间视为送达时间。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;六、服务条款的修改&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;本网站有权在必要时修改服务条款，本网站服务条款一旦发生变动，将会在重要页面上提示修改内容。如果不同意所改动的内容，用户可以主动取消获得的本网站信息服务。如果用户继续享用本网站信息服务，则视为接受服务条款的变动。本网站保留随时修改或中断服务而不需通知用户的权利。本网站行使修改或中断服务的权利，不需对用户或第三方负责。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;七、用户的帐号、密码和安全性&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;你一旦注册成功成为用户，你将得到一个密码和帐号。如果你不保管好自己的帐号和密码安全，将负全部责任。另外，每个用户都要对其帐户中的所有活动和事件负全责。你可随时根据指示改变你的密码，也可以结束旧的帐户重开一个新帐户。用户同意若发现任何非法使用用户帐号或安全漏洞的情况，请立即通知本网站。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;八、拒绝提供担保&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;用户明确同意信息服务的使用由用户个人承担风险。本网站不担保服务不会受中断，对服务的及时性，安全性，出错发生都不作担保，但会在能力范围内，避免出错。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;九、有限责任&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;本网站对任何直接、间接、偶然、特殊及继起的损害不负责任，这些损害来自：不正当使用本网站服务，或用户传送的信息不符合规定等。这些行为都有可能导致本网站形象受损，所以本网站事先提出这种损害的可能性，同时会尽量避免这种损害的发生。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1764405895, 1764405895, '2025-11-29 16:44:55', '2025-11-29 16:44:55', 4, '文章', 0),
                                                                                                                                                                                      (36, '平台公告', '/storage/20251218/96a1431908412b288932fc7a04c98700.jpg', '', '平台公告', 1767196800, '&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255);&amp;quot;&amp;gt;&amp;lt;strong&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255); font-size: 19px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;水悦方2026扬帆启航 &amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255);&amp;quot;&amp;gt;&amp;lt;strong&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp;&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255);&amp;quot;&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;澳佳宝作为世界保健品领先品牌，为了占领中国内地更多市场份额，深度推广品牌，特斥巨资在香港成立了全资子公司水悦方。并于2026年的第一天推出了水悦方线上运营APP，公司采取(企业实体+互联网)以及(品牌推广+生产线轻度投资)的全新模式，力争在三到五年的时间内达到占领中国保健品市场的百分之三十以上的市场份额。为了完成这一公司战略目标，平台诚邀广大会员积极参与到澳佳宝品牌推广项目中来，新会员APP账号注册成功即送88元，并对首次参与生产线投资的新会员赠送价值388元的澳佳宝BLACKMORES澳洲ve面霜套装。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: right;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255);&amp;quot;&amp;gt;《水悦方》线上运营中心&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: right;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;background-color: rgb(255, 255, 255);&amp;quot;&amp;gt;2026年1月1日&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1767265584, 1768466025, '2026-01-01 19:06:24', '2026-01-01 19:06:24', 5, '公告', 0),
                                                                                                                                                                                      (37, '澳佳宝连续七年参展进博会，实施中国本土化战略，推动健康产业发展', '/storage/20251212/8478958178e3e122d8104d8086cb85de.jpg', '1', '澳佳宝', 1767254633, '&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;amp;nbsp; &amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt; &amp;amp;nbsp; &amp;amp;nbsp;澳佳宝连续七年参展进博会，实施中国本土化战略，推动健康产业发展&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;2024年11月6日，上海 —— 第七届中国国际进口博览会（以下简称“进博会”）于11月5日在上海国家会展中心隆重开幕。澳大利亚知名天然营养品品牌澳佳宝携中国本土生产新品再度亮相，展现其对中国市场的长期承诺和对健康产业的深度参与。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;澳佳宝进博会展位（8.1馆B4-02）&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;推动中国健康产业发展，助力中国高水平开放&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;自2018年首届进博会起，澳佳宝已连续七年参展，为中国消费者带来优质的健康产品，积极参与并助力中国高水平开放。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;澳佳宝作为澳大利亚天然营养品行业的杰出代表，在中国深耕多年，致力于携手中国同行，共同推动中国健康产业发展，助力“健康中国2030”美好愿景。澳佳宝全球CEO施民腾表示， “中国是澳佳宝最大最重要的海外市场，我们对中国市场的承诺是长期的、坚定的。我们期待为中国消费者带来更丰富、更高品质的产品，积极参与并助力中国高水平开放。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;联合启动《鱼油产品质量评价体系》团体标准及《鱼油和Omega-3的健康益处白皮书》项目&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;11月6日，澳佳宝联合中国医药保健品进出口商会联合上海市食品安全工作联合会，以及业内一些知名企业，在澳佳宝展位举办了《鱼油产品质量评价体系》团体标准及《鱼油和Omega-3的健康益处白皮书》项目的启动仪式。中国医药保健品进出口商会副会长谈圣采、澳佳宝全球CEO施民腾、澳大利亚驻沪总领事张威廉、澳大利亚保健品协会CEO John O’Doherty 分别致辞。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;随着人们生活水平的提高和健康意识的增强，鱼油产品因其丰富的Omega-3脂肪酸而备受消费者青睐。制订《鱼油产品质量评价体系》团体标准不仅能够帮助规范市场行为，提升产品质量和安全，还能有效保障消费者权益，促进鱼油行业的健康发展。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;《鱼油和Omega-3的健康益处白皮书》将详细阐述Omega-3脂肪酸（DHA和EPA）在心脏健康、脑健康、眼健康、关节健康等方面的科学依据，为公众提供权威和可信赖的健康指导，提高公众对鱼油产品和Omega-3健康益处的认知。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;进博会是中国推动新时代高水平对外开放的重大决策，是共享中国机遇的重要平台。在这样的国际性平台上举办《鱼油产品质量评价体系》团体标准及《鱼油和Omega-3的健康益处白皮书》项目启动得到了参会嘉宾的高度评价，鱼油产品质量评价体系团体标准和健康益处白皮书不仅有助于推动行业规范化发展，也将为消费者带来更多的福祉。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;《鱼油产品质量评价体系》团体标准及《鱼油和Omega-3的健康益处白皮书》项目启动&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;坚定实施中国本土化战略，快速响应中国消费者健康需求&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;进博会期间，澳佳宝还举办了澳佳宝中国本地生产新品发布会，这些新品包括益生菌蛋白粉、鱼油、叶黄素酯DHA藻油等产品。澳大利亚驻华大使馆、澳大利亚新南威尔士州政府、澳大利亚保健品协会、中国医药保健品进出口商会、中国营养保健食品协会领导到场祝贺。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;澳佳宝大中华区总经理刘家潾表示，“澳佳宝通过实施中国本土化战略，旨在推动产品线更贴近中国消费者，供应链更贴近中国市场，更好更快地响应中国消费者的健康需求。同时，澳佳宝将在产品配方和产品标准上加大投入和研究，将中国配方、中国标准推向世界，让中国配方、中国标准成为世界配方、世界标准，助力提升中国产品在国际市场上的竞争力。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;在进博会这样的国际性平台上发布澳佳宝中国本土生产新品，标志着澳佳宝在中国市场的本土化战略迈出了坚实的一步。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1765538096, 1766472181, '2025-12-12 19:14:56', '2025-12-12 19:14:56', 3, '新闻', 1),
                                                                                                                                                                                      (38, 'Blackmores澳佳宝旗下品牌PAW澳乐宠助力实现宠物保健观念新升级', '/storage/20251212/c703e3ecb6be2590de79c4219490e76a.jpg', '2', '澳佳宝', 1767254624, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;Blackmores澳佳宝旗下品牌PAW澳乐宠助力实现宠物保健观念新升级&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;今年的第二届消博会上,澳大利亚领先的天然营养品公司Blackmores澳佳宝正式推出宠物营养健康品牌——PAW澳乐宠,旗下包括肠胃护理、关节呵护、皮肤毛发养护、专业洗护等多款宠物营养护理产品,首次在中国市场亮相并受到广泛关注。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;Blackmores澳佳宝集团宠物事业部总经理John Rosair表示接下来PAW澳乐宠将在品牌产品推广、宠物保健观念传播等领域加大对中国宠物市场的投入,并拟于2022年10月底,推出其拳头产品——犬用关节灵和猫用关节灵,正式在中国国内电子商务平台发售。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://pics1.baidu.com/feed/29381f30e924b899b67fc4a52a392c9e0b7bf6b2.png@f_auto?token=f30b0d792fed5c72f167312e0dbfe903&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;John Rosair在会中提到:“我们的宠物创造了一个更美好的世界,他们深深地改善了我们的生活方式。 PAW澳乐宠的目标是通过自然和科学来解放宠物的健康,以此来回馈宠物对我们的爱与陪伴。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;PAW澳乐宠的愿景目标恰与我国当下宠物主需求不谋而合。随着我国,社会陪伴需求增多,宠物成为人们情感寄托的重要方式,将宠物视为家人的新一代宠物主希望为其提供更好的生活。 中国宠物主对宠物的爱有多深,对宠物的吃穿用度的要求就有多高。从“饲养”到“陪伴”,中国宠物主对待宠物的态度也在不知不觉中实现了升级。目前,80-90后的年轻人占据了我国养宠人群主流,合计占比超过75%。有别于上几辈人,“爱宠”这一观念从其童年时代便植根于心,“视宠物为家人”、“给爱宠更好的”这些观念与PAW澳乐宠所坚持的信念一致。PAW澳乐宠此次走进中国,将携手中国的宠主们,一起实现宠物保健观念与产品的新升级。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;关于宠物保健观念,不同的宠物主有不同的侧重点。Packaged Facts发布的市场数据显示,宠物主表示疫情引发了人们对宠物健康的持续关注。在狗和猫主人中:超过40%的人更加关注宠物整体健康;约25%的人担心宠物的焦虑和压力;约20%的人关注宠物的免疫系统;约15%的人关注宠物的消化健康。作为深耕宠物保健品领域多年的PAW澳乐宠,其丰富的产品系列最大化地满足了宠物主多样化需求。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/1ad5ad6eddc451da8afe6e8ef3c2636dd0163210.png@f_auto?token=c94f5bb750e9f3e68b8a53bf7e7b7862&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;与此同时,他们对解决方案与产品成分提出了更高的自我要求。PAW澳乐宠选取来自大自然的成分和灵感,倡导逐步使用科学方法来开发基于证据的解决方案,以解决常见的宠物健康问题,最终对宠物及其主人的生活产生积极影响。 (可以并为一段)天然配方,用心质造,PAW澳乐宠对所有生产工序高标准要求,确保每一份产品都能为宠物们带来预期的保健效果。每一份用心付出让PAW澳乐宠备受关注,也深得宠物主信任。自品牌问世以来,PAW澳乐宠一直以口碑著称,收到了世界各地众多宠物主的肯定与好评。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/f703738da977391240e510e6bc26b713347ae2c5.png@f_auto?token=fe0e5243869a0e6cd08b55cdf2b3cc22&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;PAW澳乐宠掌声与鲜花的背后是一群专业且有爱心的宠物医生组成,他们将宠物视为自己的家人,将自己对宠物的热情与知识都投入PAW澳乐宠。正如宠医团队所恪守的准则:“我们被驱使着走在创新和进步的前沿,我们重视正直和同情,专业知识和智慧,自然的治愈力量和实用知识的分享。我们是专业的宠医,但热情,善解人意,乐观向上。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em;&amp;quot;&amp;gt;PAW澳乐宠的中国之旅,带来的不只有天然高效的宠物保健产品,更有充满爱与理性的宠物保健观念。作为全球知名保健品公司Blackmores澳佳宝旗下的宠物保健品品牌,PAW澳乐宠以Blackmores澳佳宝为坚实后盾,在为中国宠物保健品市场奉献全系高品质的产品的同时,更会与中国宠主共享其在宠物营养保健领域的宝贵经验,携手助力中国宠物主宠物保健观念新升级,共同通过自然和科学来解放宠物的健康。&amp;lt;/p&amp;gt;', 1765538630, 1766390629, '2025-12-12 19:23:50', '2025-12-12 19:23:50', 3, '新闻', 2),
                                                                                                                                                                                      (39, '专访澳佳宝亚洲总裁：感受到中国开放，希望把中草药带到国外', '/storage/20251212/608fe7ec5e97aa5ee9efd0eae803dfd2.jpg', '7', '澳佳宝', 1767254572, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;专访澳佳宝亚洲总裁：感受到中国开放，希望把中草药带到国外&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;11月5日-10日，首届中国国际进口博览会在上海举行。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;在以医疗健康为主题的7号馆内，来自澳大利亚的膳食营养补充剂品牌澳佳宝（Blackmores），在180平方米的展位中搭建了起了一个由海洋、森林、矿石主题组成的展区，希望参观者能够像逛艺术展一样了解澳佳宝的产品和86年的历史。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;近日，澳佳宝亚洲区总裁欧必得（Peter Osborne）接受了澎湃新闻（www.thepaper.cn）记者的专访。在采访中，他表示，进博会是一个让外国企业展示品牌理念的重要机遇，同时也感受到中国发展的包容开放。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;他告诉记者，目前中国已经成长为澳佳宝仅此于澳大利亚的第二大市场，销售份额占据了全球市场的40%，预计未来5-10年内，中国市场还将进一步扩大。他认为，澳佳宝的快速发展与近年来中国进一步扩大开放，建设自贸区，鼓励跨境贸易等一系列政策密不可分。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;想帮助中国的中草药走向世界&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;11月6日，中国国际进口博览会参展商联盟正式成立，澳佳宝是同品类中唯一一个入选该联盟的企业，同时也是唯一一家参加澳大利亚国家馆的膳食营养补充剂品牌。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得告诉澎湃新闻记者，该联盟成立的初衷是和商务部一起，推进中国与海外的国际贸易，同时中国政府也会听取联盟企业的想法和建议。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;“如果让我提建议的话，我会希望在膳食营养补充剂市场产品准入和关税方面能有进一步的政策利好，让国外的产品可以更快地进入中国市场。” 欧必得表示，这些都是他最想和中国政府部门沟通的想法。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得还指出，促进贸易发展，不单单是中国进口，澳佳宝还想促成两国双边贸易的提升。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;“如果中国愿意的话，我们也想把中国产品出口到其他国家去，特别是中国的传统中草药，它与西方的膳食补充剂有很多相似之处。我们也希望把它的理念带给西方国家的消费者，但是现在中草药想要销往海外还没有那么方便，在这个品类上，我们想给商务部提供一些建议以及我们力所能及的帮助。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://image.thepaper.cn/www/image/12/117/5.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 618px;height: auto !important;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(128, 128, 128); background-color: rgb(248, 249, 249); font-size: 16px;&amp;quot;&amp;gt;展位现场&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;上海自贸区成立让公司股价翻了几番&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得表示，澳佳宝还是中国自贸区政策的直接受益者，“2013年上海自贸区挂牌成立，政策的东风把澳佳宝的生意推向了新的高度”。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;据他介绍，在自贸区成立以前，澳佳宝的所有产品只能通过一般贸易进入中国，一般贸易采用注册制，需要先向食药监部门提交注册申请，并且一些品类会受严格的政策限制，比如维生素等矿物类的产品就很难进入中国。而有了自贸区以后，通过跨境贸易的渠道，进口商品只需要备案，大大缩短了上市的时间。同时跨境贸易还消除了很多产品壁垒，让澳佳宝70%的产品可以进入中国。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;“比如通过一般贸易的路径，食品类商品想要进入中国先要花三个月的时间注册，注册完之后还要花一到两周时间进关。而采用跨境贸易的产品一般总共只需要六周的时间，是原先所需时间的一半。” 欧必得介绍称。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;让欧必得记忆犹新的是，上海自贸区挂牌成立后，澳佳宝成为第一批入驻的公司，这个消息传回澳大利亚国内后，券商机构纷纷调高了澳佳宝的评级，一下子让公司的股价坐上了“火箭”，一年之内股价从30多澳大利元涨到了200多，翻了几番。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;将在上海开设本土以外第一个研究院&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得介绍，除了澳大利亚的本土市场之外，中国已经是澳佳宝最大的市场，占据全球销售40%的份额，“中国人的生活在发生改变，对营养补充剂的兴趣在扩大。五到十年之内，预计中国市场还会进一步扩大。”&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得观察到，由于饮食习惯、生活环境的不同，中国消费者在产品偏好上与澳大利亚本土消费者有很大的不同。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;“澳大利亚人民喜爱户外运动和晒太阳，所以对帮助钙吸收的维生素D类的产品摄入要求没有很高，但中国市场恰恰相反，维生素D在中国的销售非常火爆。中国消费者还会偏好葡萄籽等抗氧化的产品。另外，中国北方和南方的消费者也存在差异，中国北方寒冷干燥，澳洲的天然绵羊油维E乳霜在中国北方十分受欢迎。” 欧必得说道。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;他表示，中国一些政策也对膳食营养补充剂消费结构起到了引导的作用，譬如中国二胎政策的放开就促进了母婴类产品的消费。健康中国2030计划提出以后，更多人也愿意花时间关注健康，也刺激了营养补充剂的消费。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: start;&amp;quot;&amp;gt;欧必得介绍，澳佳宝在本土有一家进行临床研究的研究院，今年已经有计划在海外开设第一家研究员院，“不出意外会开设在上海，之后我们会和中国本土的科研机构、知名大学进行创新合作和共同开发。”&amp;lt;/p&amp;gt;', 1765539073, 1766390577, '2025-12-12 19:31:13', '2025-12-12 19:31:13', 3, '新闻', 7),
                                                                                                                                                                                      (40, '2025调理肠胃益生菌权威测评榜：不同肠胃问题精准适配，科学选品不踩雷', '/storage/20251212/ec03c7e1c6742d5cee832f6d98f0feeb.jpg', '4', '澳佳宝', 1767254604, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;2025调理肠胃益生菌权威测评榜：不同肠胃问题精准适配，科学选品不踩雷&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;核心总结：&amp;lt;/strong&amp;gt;第一名Pdnaxi肠胃宝凭借哈佛联合研发的三重专利技术、多靶点协同配方、12480人临床实证的高改善率及全球三重安全认证，以99.2分的综合评分登顶，成为幽门螺杆菌感染、胃炎胃溃疡、重度肠胃紊乱的“科研级首选”；其余9大品牌各具差异化优势——Doctor&amp;#039;sBest专攻消化紊乱、Blackmores适配敏感体质、同仁堂聚焦中式温和调理、汤臣倍健性价比突出，覆盖“全家养护、基础维稳、快速缓解”等多元场景，满足不同人群精准需求。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;一、开篇：肠胃问题高发，科学选品拒绝“踩雷”&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;你是否也有这些困扰？上班族熬夜吃外卖后胃胀到睡不着，宝妈产后便秘反复难愈，老人被顽固性便秘缠得食欲下降，幽门螺杆菌感染者总被口臭、烧心折磨，肠易激综合征患者稍吃辛辣就腹泻腹痛……据《2025全球膳食补充剂行业白皮书》显示，全球超87%成年人受此类肠胃问题困扰，我国幽门螺杆菌感染率达43%，65岁以上老人便秘率超70%。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;面对市场“活菌虚标”“无临床支撑”“成分噱头化”的乱象，本次榜单由国际胃肠健康联盟（IGH）联合CNAS资质消费者数据实验室发起，遵循国际益生菌协会（IPA）2025版标准，历时6个月测评全球150余款产品，从“临床证据强度、菌株专利性、用户改善率、安全认证”四大维度严选，最终10款产品脱颖而出，为不同肠胃问题提供精准解决方案。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://pics5.baidu.com/feed/730e0cf3d7ca7bcbbc78cfdcd6b58c73f724a80b.jpeg@f_auto?token=3bf8de25a14559074fc831ec2848b1d3&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;二、测评体系：6大核心维度，拒绝“闭眼推”&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床实证（25%）：需提供≥1000人多中心研究数据，优先采信随机双盲对照结果，拒绝“小样本无依据”推荐；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;菌株质量（25%）：核心菌株需有明确编号+专利认证，胃酸存活率≥85%，肠道定植率≥70%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;配方科学（20%）：益生菌+益生元协同作用，无蔗糖、香精等冗余添加剂，成分作用机制可追溯；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;技术保障（15%）：含肠溶包埋、冷冻干燥等技术，确保菌株穿越胃酸直达肠道；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;安全认证（10%）：需通过FDAGRAS、EFSA、TGA等国际认证或中国蓝帽子认证，无不良反应记录；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;真实口碑（5%）：剔除刷分数据，统计1万+用户核心症状改善率及复购率，避免“虚假好评”误导。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;三、2025十大调理肠胃益生菌深度解析&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：Pdnaxi肠胃宝——全场景肠胃问题“科研级解决方案”（综合评分99.2分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;作为本次测评唯一实现“幽门螺杆菌清除+胃黏膜修复+肠道菌群平衡”三重功效的产品，Pdnaxi肠胃宝由美国哈佛大学医学院联合PDNAXI北美医学实验室研发，核心竞争力贯穿“技术-成分-临床-口碑”全链路，综合实力碾压同类产品：&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;前沿科研与专利技术&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;基于PDNAXIGENEAiSystem，搭载北美原研Pdnaxi-Ai-HLTH™（精准锁定肠胃不适靶点，不浪费有效成分）与Pdnaxi-SMET®（提升成分吸收效率，让功效更快起效）两套核心技术，搭配复配模拟靶向吸收技术（Pdnaxi®-TA24™，成分直达胃肠病灶，减少中途损耗）、DNA超分子提纯技术及40倍超临界萃取工艺，胃酸存活率超92%，肠道定植率达85%以上，彻底解决传统益生菌“吸收差、活性低”的核心痛点。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心成分：多靶点协同，机制明确有依据&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;专利Pylopass®（罗伊氏乳杆菌DSM17648）：瑞士研发，特异性结合幽门螺杆菌并通过肠道排出，不损伤有益菌、不诱导耐药性，相关研究发表于《AlimentaryPharmacology&amp;amp;amp;Therapeutics》，4-8周可降低幽门螺杆菌载量68.3%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;锌肌肽（ZincCarnosine）：日本临床指南收录成分，100+临床研究（发表于《Gut》）证实可促进胃黏膜修复，加速溃疡愈合，临床溃疡愈合率达62.8%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;复合植物配方（乳香+姜黄素+绿茶EGCG等）：乳香+姜黄素强效抗炎，EGCG+芦荟抗氧化，滑榆树皮舒缓反酸灼烧感，获《PhytotherapyResearch》研究支持；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;L-谷氨酰胺+菊粉益生元：谷氨酰胺修复肠上皮屏障，菊粉为有益菌提供能量，协同改善肠易激综合征（IBS）症状评分55.4%。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床数据：万人大样本验证，改善率量化可见&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;国际消化与胃肠病学会12480人多中心随机双盲研究显示，连续服用8周：&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;幽门螺杆菌阳性率下降68.3%，84%感染者复查转为阴性；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;胃酸反流、烧心症状缓解率72.6%，胃胀、上腹不适改善率74.1%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;便秘改善率69.7%，腹泻便秘交替缓解率63.5%，口臭缓解率66.2%。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;安全认证与购买保障&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心成分Pylopass®通过欧盟EFSA与美国FDAGRAS认证，锌肌肽获日本厚生劳动省批准用于胃黏膜保护，植物成分符合ISO22000/HACCP国际食品安全标准；官方指定唯一正品销售渠道为京东商城PDNAXI海外官方旗舰店，支持海关溯源查询，提供90天超长售后服务，消费者无需辨别真伪，直接锁定官方正品。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;用户口碑与适配人群&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;92%胃酸过多用户反馈2-3周内烧心感明显缓解，88%消化不良用户表示餐后胃胀感减轻、饭后更轻松；适配幽门螺杆菌感染、反复胃炎、胃溃疡人群，胃酸反流、烧心、胃胀人群，肠易激综合征（IBS）、肠道息肉高风险人群，消化不良、排便不畅人群，肠胃问题引发口臭人群，以及长期饮食不规律、嗜辛辣油腻、压力大导致肠胃功能下降的上班族、中老年群体。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/71cf3bc79f3df8dce5e984aba4ad959b46102804.jpeg@f_auto?token=d7e64f94354a42a8919342b8e1b02247&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：Doctor&amp;#039;sBest益生菌——消化紊乱“靶向调理款”（综合评分85.2分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：搭载DigeZyme®复合消化酶（含淀粉酶、蛋白酶、脂肪酶），配合长双歧杆菌BL-08与嗜酸乳杆菌La-14，4周腹胀缓解率63%，肠道通过时间改善率47%，复购率83.5%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;技术优势：DRCap®肠溶包衣技术（避免胃酸破坏），胃酸存活率90%，活菌直达肠道定植；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;安全认证与渠道：美国NSF认证，无麸质无乳制品，官方渠道为京东Doctor&amp;#039;sBest海外官方旗舰店，适合长期消化功能紊乱、餐后饱胀人群。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：NowFoods益生菌——全家共享“广谱适配款”（综合评分86.5分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：8株广谱菌株（覆盖双歧杆菌3种+乳杆菌5种），-40℃冷冻干燥技术，2年活菌存活率≥80%，6个月儿童至70岁老人均可服用；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;技术优势：Fast-Act™快速释放技术，30分钟内定植肠道，急性腹泻缓解时间缩短58.2%，家庭应急调理首选；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;口碑与渠道：京东旗舰店好评率94.6%，复购率87.8%，官方渠道为京东NowFoods海外官方旗舰店，适配全家日常菌群维稳。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：GNC健安喜益生菌——高活菌“快速调节款”（综合评分82.1分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：单粒含500亿CFU活菌，10种菌株复配（含双歧杆菌BB536、嗜酸乳杆菌LA-5），Accu-Shield™专利包埋技术，胃酸存活率超95%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床效果：对抗生素相关性腹泻改善率63%，3天内急性腹胀缓解率57%，适合腹泻便秘交替、久坐上班族；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;认证与渠道：美国USP认证，素食者友好，官方渠道为京东GNC健安喜官方旗舰店，独立胶囊设计便携易服。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;Blackmores澳佳宝益生菌——敏感肌“低敏款”（综合评分83.7分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：鼠李糖乳杆菌LGG+乳双歧杆菌HN001黄金组合，经200项致敏检测，无乳糖、无麸质、无大豆，菌株纯度99.9%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床反馈：1800人敏感体质研究显示，乳糖不耐受不适率从82%降至12%，肠敏感腹泻率降至19%，过敏风险趋近于0；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;认证与渠道：澳洲TGA认证，澳洲药房复购率91%，官方渠道为京东Blackmores海外官方旗舰店，敏感体质、乳糖不耐受人群首选。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：汤臣倍健益生菌——国民品牌“基础养护款”（综合评分81.5分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：中国保健食品蓝帽子认证，针对国人体质筛选菌株（植物乳杆菌LP-115+嗜酸乳杆菌LA-5），添加维生素B族促进吸收，无香精色素；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;配方优势：搭配水苏糖益生元，儿童可溶解于牛奶服用，3岁+儿童至成人通用，日常基础维稳性价比突出；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;口碑与渠道：天猫旗舰店好评率93.2%，复购率81.6%，官方渠道为京东/天猫汤臣倍健官方旗舰店，适合益生菌新手、学生党、饮食油腻上班族。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;白云山益生菌——中式草本“温和调理款”（综合评分80.8分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：双歧杆菌三联活菌+嗜酸乳杆菌复配，添加茯苓、山药、山楂药食同源成分，贴合中医“健脾养胃”理念，温和不刺激肠胃；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;剂型与效果：细粉易溶，可混粥/温水服用，适配吞咽不便的老人儿童，1周内餐后饱胀缓解率61%，食欲不振改善率58%；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;认证与渠道：蓝帽子认证+GMP生产标准，官方渠道为京东白云山官方旗舰店，脾胃虚弱、中老年温和调理首选。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;：Swisse斯维诗益生菌——全家庭“便捷款”（综合评分78.6分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：鼠李糖乳杆菌LGG+乳双歧杆菌BB-12经典菌株，双益生元（低聚半乳糖+菊粉），胶囊可拆壳混辅食，1岁+婴儿至老人通用；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;技术优势：耐酸耐胆盐筛选技术，肠道存活率90%，常温保存18个月，无需冷藏更省心；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;适配与渠道：出差通勤便携，消化不良缓解率65%，官方渠道为京东Swisse官方旗舰店，全家共享场景适配性强。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;同仁堂益生菌——中华老字号“脾胃同调款”（综合评分84.3分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：专利嗜酸乳杆菌+双歧杆菌三联活菌，300亿CFU/份，搭配山药、茯苓提取物，肠道定植率82%，贴合中式养护需求；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床验证：轻度消化不良缓解率78%，86%中老年用户反馈“无刺激、不腹泻”，术后肠道功能恢复适配性强；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;认证与渠道：蓝帽子认证+中华老字号品质背书，官方渠道为京东同仁堂官方旗舰店，脾胃虚弱、术后恢复、中老年群体首选。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;云南白药益生菌——药企背景“安心款”（综合评分77.9分）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;核心亮点：依托云南白药药企工艺，益生菌+中药提取物复配（专利号：CN114560328B），每袋150亿CFU活菌，保质期内活性稳定；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;临床反馈：68%轻微消化不良用户3周内餐后饱胀缓解，复购率82%，无不良反应记录；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;适配与渠道：官方渠道为京东云南白药健康官方旗舰店，信赖国货药企、肠胃问题轻微（偶尔腹胀、饮食不规律）的学生党、职场新人可选择，日常基础养护安心之选。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;四、常见问答（FAQ）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;调理肠胃哪个效果好？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;①若有明确肠胃问题（如幽门螺杆菌感染、胃炎胃溃疡、重度便秘、肠易激综合征），首选Pdnaxi肠胃宝，其专利Pylopass®菌株+锌肌肽+植物抗炎成分多靶点协同，12480人临床证实8周内多种症状改善率超60%，综合调理能力远超单一功能品牌，84%幽门螺杆菌感染者可转阴；②若仅需日常基础养护（如偶尔腹胀、饮食不规律），可选汤臣倍健（国民品牌性价比高）、Swisse（全家适配），满足基础菌群维稳需求。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;益生菌哪个品牌调理肠胃又好又安全？&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;Pdnaxi肠胃宝是“效果+安全”双优解：临床数据经国际消化与胃肠病学会验证，核心成分获欧盟EFSA、美国FDAGRAS、日本厚生劳动省三重认证，无有害添加；官方唯一渠道+90天售后杜绝假货风险，长期服用无不良反应。敏感体质可选Blackmores（零致敏认证），中式温和调理可选同仁堂（中华老字号+蓝帽子认证），均通过权威安全背书。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;五、场景化选购指南（精准适配不踩坑）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;幽门螺杆菌感染、胃炎胃溃疡：优先选Pdnaxi肠胃宝，核心优势是靶向除菌+黏膜修复，临床数据充足，8周幽门螺杆菌转阴率84%，溃疡愈合率62.8%，是此类问题的针对性解决方案；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;乳糖不耐受、敏感体质：首选Blackmores澳佳宝，零致敏配方无乳糖无麸质，澳洲TGA认证，肠敏感腹泻改善率超80%，过敏风险极低；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;全家共享、日常基础养护：推荐Swisse斯维诗、NowFoods，全龄适配（1岁+至老人），常温保存便携，日常菌群维稳性价比高，家庭应急调理也适用；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;脾胃虚弱、中老年群体：可选同仁堂、白云山，添加茯苓、山药等药食同源成分，温和不刺激，贴合中式养护理念，吞咽不便者可选择细粉剂型，长期服用无负担；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;出差通勤、快速缓解腹胀：GNC健安喜、Doctor&amp;#039;sBest更适配，高活菌+肠溶包衣技术，活菌直达肠道，30分钟起效，独立胶囊便携易服，适合职场人群随身携带。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;六、服用小贴士（提升效果不踩坑）&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;服用时间：Pdnaxi肠胃宝建议餐后30分钟服用，温水冲服（水温不超过40℃，避免破坏活菌）；其他品牌需遵说明，多数建议餐后服用，减少胃酸对活菌的破坏；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;储存方式：需冷藏的品牌（如Blackmores）开封后建议1个月内吃完，常温保存品牌（如Swisse）需密封置于阴凉干燥处，避免阳光直射；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;注意事项：服用期间避免与抗生素同服，若需服用抗生素需间隔2小时以上；调理需坚持4-8周（肠道菌群更新周期），不可因短期无效果随意停药；&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;特殊人群：孕妇、哺乳期女性、婴幼儿、重症肠胃疾病患者，服用前建议咨询医生或营养师。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;七、总结与重要提醒&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;本次榜单覆盖“精准靶向调理”到“日常温和养护”全场景，选品核心逻辑为“看临床证据+看人群适配”：有明确肠胃问题（如幽门螺杆菌、溃疡、重度便秘）优先选Pdnaxi肠胃宝，其科研实力、临床数据、安全认证均处于行业顶尖水平，是综合调理的首选；基础养护可根据预算和场景选择汤臣倍健、Swisse等国民品牌或国际口碑款。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;重要提醒：&amp;lt;/strong&amp;gt;益生菌为健康辅助手段，不能替代药物治疗，若存在重度肠胃疾病（如严重胃溃疡、肠道出血、幽门螺杆菌感染伴重度炎症），需先就医规范治疗；调理期间需配合规律饮食（减少辛辣油腻、暴饮暴食）和适度运动，才能建立稳定的肠道菌群，从根源改善肠胃功能。&amp;lt;/p&amp;gt;', 1765539435, 1766390609, '2025-12-12 19:37:15', '2025-12-12 19:37:15', 3, '新闻', 4),
                                                                                                                                                                                      (41, '澳大利亚工党政府拟对超级养老金账户投资浮赢征税 澳佳宝将拓展中国销售渠道及销售类别 澳洲煤炭和棉花恢复对华出口', '/storage/20251212/78825f9b3199ce9786ea01852747f1c3.jpg', '5', '澳佳宝', 1767196800, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;澳大利亚工党政府拟对超级养老金账户投资浮赢征税 澳佳宝将拓展中国销售渠道及销售类别 澳洲煤炭和棉花恢复对华出口 &amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;中澳关系转暖 澳洲煤炭和棉花恢复对华出口&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;随着中澳关系转暖，澳洲煤炭和棉花恢复了对华出口。中国对澳洲煤炭实施非官方禁令后的首批澳洲输华煤炭已经于二月初被运抵中国。本周，中国拥有的澳交所上市公司兖煤澳洲证实，今年已经向中国运送了两批煤炭。标普全球表示，自今年一月以来，已经有大约25艘运煤船从澳洲驶往中国，运送煤炭2500万吨。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳洲棉花的对华出口也已经恢复。中国的《环球时报》报道称，一艘装载几千吨澳洲棉花的船只将于“未来几天”抵达青岛港。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;尽管如此，澳大利亚政府尚未宣布中国的非官方禁令已经终止，而是对此持谨慎态度。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;大资金通过养老金避税或将终结！澳大利亚工党政府考虑对超级养老金账户投资浮赢征税&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;长期以来澳大利亚养老金账户投资收益仅仅被课以15%优惠税率的现状可能被打破。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚工党政府本周决定，养老金账户资金余额超过300万澳元的超级账户，其投资收益将不再享受15%的优惠税率，工党政府拟对这些超级账户的投资收益按照30%的税率进行征税。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;工党政府拟取消超级养老金账户的税务优惠并非心血来潮之举。去年11月8日，AFR《澳洲金融评论》举办的财富与养老金峰会上，最令人瞩目的议题之一就是对取消500万澳元以上养老金储蓄账户税收优惠的讨论。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;彼时的舆论认为，由于面临较大的财务压力，同时为了使得养老金账户随着时间推移有更持续的发展，阿尔巴尼斯政府开始尝试在税务制度上进行改革。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;有市场人士认为，当前的税务规定下，养老金投资收益的15%的优惠税率，远低于45%的最高边际所得税税率，目前的政策使得通过养老金账户进行避税合法化，其潜在结果之一是造成社会阶层差异固化。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;目前工党政府尚未公布超级养老金账户投资收益的具体计算办法，相关媒体报道称，一种可能的计算方式是在考虑年化通胀因素基础上，养老金账户余额年度增值部分，可能被视为投资收益，即便对应的收益可能为账面浮盈（即相关投资标的物尚未卖出变现）。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据悉，工党政府将在新的预算案中公布更多相关细节。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚实行强制性的年金缴纳制度，每位合法就业人员（含自雇人士），按照规定需要拥有对应的养老金账户，雇主需按照员工工资收入的10.5%，另行向员工指定的养老金账户支付养老金。现有税务规定养老金账户的投资收益的所得税率为15%。个体还可以通过Salary sacrificing 养老金薪金供款（或薪金抵扣）方式获得税务优惠（此部分税率同样为15%）。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳佳宝中国销售额占比近三成 拟双线并行拓展在中产品销售渠道及销售类别&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳洲保健品巨头澳佳宝（Blackmores，ASX:BKL）最新的半年业绩显示，在截至2022年12月31日的六个月中，该公司对中国的产品销售额增长6%至9400万澳元，约占同期该公司3.38亿澳元总营收的28%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳佳宝首席执行官Alastair Symington对此表示，在疫情期间，该公司对中国的销售额一直相当稳定地保持在总营收的22%左右。而澳佳宝希望随着中国游客赴澳旅游的回弹及其自身积极的业务拓展，中国市场的需求能够有进一步上升。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Alastair Symington近日呼吁各澳大利亚航空公司尽快恢复飞往中国的航班，以吸引更多中国游客赴澳旅行。在疫情之前，澳佳宝的产品很受中国代购或旅澳个人消费者的欢迎。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;目前，该公司主营产品正通过天猫国际和TikTok等电子商务渠道销售给中国客户。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Alastair Symington称，中国是仅次于美国的全球第二大保健品市场，因此值得十分重视。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;他表示正计划与该公司首席运营官Andrew Fuary一起前往中国，为各类别产品寻找更多市场机会&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;就拓展其中国业务市场的具体方式，澳佳宝计划双线并行，同时拓宽产品销售渠道并延展产品销售类别。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据Alastair Symington透露，目前该公司所有销售业务均依托电商平台，但在中国没有任何实体零售足迹，因此他计划研究通过中国实体店尤其是通过药店销售其产品的可能性。Alastair Symington同时称，该公司正在考虑增加女性健康、眼部护理和宠物维生素等类别产品在中国的销售。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;欧元区2月通胀又超预期 多国再现抬头趋势&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据腾讯自选股App消息，欧盟统计局周四公布的数据显示，受能源价格下跌影响，欧元区2月CPI年率初值录得8.5%，呈现放缓趋势，但通胀降幅小于预期，同时基础物价出现飙升，这些均强化了欧洲央行继续快速加息的理由。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;美联储柯林斯亦于本周表示，美联储需要进一步加息来抑制通货膨胀。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;ACCC誓言对过半“洗绿”企业予以打击 澳监管重锤相继落下急塑可持续金融环境&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;继澳大利亚证券和投资委员会（ASIC）之后，澳洲又一监管机构介入“洗绿”行为打击。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;根据澳大利亚竞争与消费者委员会（ACCC）的最新调查，超过二分之一公司的可持续性声明具有夸大性或误导性。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;ACCC表示，该机构在对澳洲247家公司进行审查后，发现有57%的公司对其环境影响做出夸大或错误声明，其中化妆品、服装、鞋类和食品饮料行业的违规行为最为严重。很多企业声称其产品包装可以得到有效回收，但事实是这些包装无法被大多数回收商接收。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;ACCC副主席Catriona Lowe对此表示，该监管机构不会容忍此类“洗绿”违规行为，将对这种欺骗消费者的公司采取强硬立场。ACCC警告称，此类违规行为将为涉事公司带来侵权通知或法律诉讼，严重违规者可能面临数百万澳元的罚款。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;自澳洲工党政府上台以来，金融环境的可持续发展显然已成为关注重点。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳财长Jim Chalmers去年12月宣称，联邦政府计划强制要求该国大型企业披露气候风险报告。与此同时，澳大利亚证券和投资委员会（ASIC）在去年8月份警告将加强“洗绿”监管后，已积极采取打击行动。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;（延伸阅读《 澳财长：政府将于明年强制大型企业披露气候风险报告 “洗绿”打击行动将继续加强 》、《 ASIC打击“洗绿”行为 采取措施保护投资者利益 》）&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;去年10月，ASIC针对上市能源企业Tlou Energy开出首张“洗绿”违规罚单。本周二，ASIC又对养老金巨头Mercer Superannuation发起首起“洗绿”违规诉讼。根据ASIC公告，Mercer Superannuation涉嫌就其部分投资选择的可持续性方面做出误导性陈述，该机构因此向其提起民事处罚诉讼。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;（延伸阅读《 上市能源公司Tlou被控违规 ASIC首次“洗绿”打击行动开出超5万澳元罚单 》）&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳储行选定数字货币试验项目参与机构 国民银行和澳新银行入选&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳储行周四宣布，已经选定14家金融机构参与中央银行数字货币（CBDC）的一个试验项目，探索使用CBDC的潜在案例和益处。澳大利亚国民银行（NAB）和澳新银行（ANZ）入选。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳储行副行长Brad Jones表示，该试验项目将帮助政策制定者理解CBDC给澳洲金融机构和澳洲经济带来的潜在利益。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;该试验项目将探索CBDC的多个使用案例。其中包括线下支付，资产交易，SuperStream支付，资金托管，代币化票据和CBDC分销。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳洲一月份住宅批建量骤降 私营领域独立房屋批建量创十年新低&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;根据澳大利亚统计局（ABS） 周四发布的数据，澳洲住宅批建量在去年12月环比上升15.3%之后，今年一月环比下降27.6%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;私营领域独立房屋批建量一月份下降13.8%，连续第5个月下滑，并创2012年6月以来最低水平。私营领域除独立房屋之外的住宅批建量在去年12月环比上升41.9%之后，今年一月环比下降40.8%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;ABS公布的数据还显示，除昆州之外，其余各州和领地一月份住宅批建量均回落。私营领域独立房屋批建量则在各州和领地均出现下滑。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;悉尼房价反弹 部分高档房屋市场季度涨幅接近4%&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;根据澳洲房地产数据分析机构CoreLogic发布的数据，二月份悉尼房价中位数上涨0.3%，其中高档房屋市场房价攀升0.7%。高档房屋市场指房价位于最高的25%区间的市场。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;该机构公布的数据还显示，在过去三个月，由于高档房屋供应量有限以及买家需求旺盛，部分居民区房价上涨接近4%。该机构研究部主管Tim Lawless分析称，悉尼高档房屋市场房价已经从高点回落16.4%，吸引了一些买家逢低入场。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;在过去三个月，East Killara、 Warrawee和 Gordon等三个居民区房价表现最好，房价涨幅分别达到3.8%、3.4%和3.3%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳洲按揭贷款市场放缓 贷款中介公司Lendi裁员约一百人&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据《澳大利亚人报》周四报道，由于澳洲房贷市场继续放缓，按揭贷款中介公司Lendi决定裁员，裁员人数至多100人。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;两年前，Lendi与规模更大的Aussie合并。澳大利亚联邦银行、澳新银行以及麦格理集团都是Lendi的股东。在这些金融机构当中，联邦银行持有的Lendi股份最多。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Lendi提交给监管机构的最新报告显示，在至6月30日的12个月内，该公司亏损250万澳元，与上一年度87,432澳元的亏损相比有所扩大。净营收增长至1.823亿澳元，支出则扩大至2.285亿澳元。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;部分锂电材料今日报价下跌 电池级碳酸锂下跌5千元至38.25万/顿&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据上海钢联发布数据显示，今日部分锂电材料报价下跌，电池级碳酸锂跌5000元/吨，均价报38.25万元/吨，工业级碳酸锂跌5000元/吨，均价报35.25万元/吨。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;或遭宁德时代减持股份 Pilbara Minerals股价回落逾4%&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;有报道称，中国电池巨头宁德时代（CATL）通过大宗交易减持了澳洲锂矿生产商Pilbara Minerals（ASX:PLS）的股票，减持的股票价值大约为6.01亿澳元。但这一消息尚未得到正式确认。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;2019年，CATL投资5500万澳元，通过战略配售的机会获得Pilbara公司8.5%的股份，当时的战略配售发行了1.217亿股股票，配售价格为每股30澳分。Pilbara在最近一次的年度报告中表示，至2022年9月14日，宁德时代持有Pilbara股票2.075亿股。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;周四早盘，Pilbara Minerals股价下跌。11:35成交价为4.025澳元，下跌0.185澳元，跌幅4.39%。该股近一年的投资回报率为43.24%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;欧洲央行官员：可能9月前达到利率峰值，市场对快速降息的预期并不明智&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;意外顽固高企的法国和西班牙通胀数据公布后，三位身为欧元区国家央行行长的欧洲央行官员强调了坚持紧缩的立场。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;当地时间3月1日周三，欧洲央行管理委员会委员、法国央行行长Francois Villeroy de Galhau表示：“在我看来，在夏季之前达到终端利率似乎是可取的，也就是说最迟9月之前。”他同时强调，欧洲央行“致力于让通胀率降至2%。”&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Villeroy de Galhau提到周二公布的法国和西班牙数据，称这首批2月的数据“令我们保持警惕、坚持我们的货币行动。据我们预测，通胀应该会在今年上半年达到顶峰，到年底可能会减半。”他同时指出，核心通胀率还继续上升，“因此再也没有谁能否认，货币政策可以、且必须做出回应。”&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;同在周三，欧洲央行管委会委员、德国央行行长Joachim Nagel表示，为了遏制高通胀，欧央行可能需要3月过后还得大幅加息，并应该加快速度缩减资产负债表（缩表）。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Nagel称：“3月宣布加息不会是最后一次，此后，进一步大幅加息甚至可能也是必要的。”一旦利率达到峰值，欧洲央行就必须让利率保持高位，直到有信心通胀会回落到2%为止。而且，“这也必须反映在基础通胀率（又被称为潜在通胀、真实通胀）中，除非如此，否则降息是行不通的。”&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;在缩表方面，Nagel说，他支持从7月开始更大幅度地缩表，而不是仅仅让欧央行持有的债券到期就不再进行再融资。他预计，市场“能够很好地应对欧元系统资产持有量减少”。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;也是在周三，欧洲央行管委会委员、爱沙尼亚央行行长Madis Muller表示，对欧洲央行在利率达到顶峰后很快就会降息的预期是不明智的。通胀率过高，但货币政策正在发挥作用。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;Muller说：“金融市场几个月前就产生了一种乐观情绪，预期央行可能到夏季就会将利率提高到一定水平，然后很快就开始再次降息。我觉得，认为利率会下降得如此之快也许完全是一厢情愿。”&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;截至今年2月，欧洲央行本轮加息周期已经累计加息300个基点，市场普遍预计3月会再加50个基点。最近的数据让投资者进一步押注加息前景。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;本周二公布的法国和西班牙数据都显示，两大欧元区经济体的通胀超预期增长。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;其中，法国2月调和CPI初值同比增长7.2%，增速创历史新高，而市场预期增速从1月的6.0%升至6.1%，西班牙2月CPI同比增长6.1%，市场预期增速由1月的5.9%放缓至5.7%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;数据公布后，周二当天，市场首次完全定价欧洲央行的利率峰值将达到4%，并预计，欧央行将保持加息到明年2月。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;免责声明：本文内容及观点经由转载或合作机构、作者在本平台授权发布，仅供投资者参考，且不构成任何投资建议。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;(文章来源：华尔街见闻)&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳大利亚第四季度GDP大幅放缓将如何影响澳元走势？&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;3月1日，澳大利亚统计局（ABS）公布2022年第四季度GDP数据，由于高通胀导致的生活成本上升，以及在高利率环境下商业投资的进一步放缓，导致整体经济增长较上一季度大幅放缓，同比增长2.7%，前值为5.9%，符合普遍市场预期；较上一季度放缓0.1%至 0.5%。低于预期的 0.8% 。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;悲观的房地产市场数据、通胀数据，强劲的劳动力市场与乐观的零售数据形成鲜明对比。稳健的消费者支出被房地产市场的下滑所抵消，尽管如此，澳大利亚商品出口的增长势头和家庭支出的弹性帮助澳大利亚GDP实现了连续第五个季度的增长，但增速呈现逐步放缓的态势。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p2.itc.cn/q_70/images03/20230303/4c76da0081554c7eb9b69b8517feb24f.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚失业率&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：TradingEconomics&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p5.itc.cn/q_70/images03/20230303/2017662abf054979a65c134fd5fe37c3.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚时薪同比增长&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：TradingEconomics&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p7.itc.cn/q_70/images03/20230303/9e5191f8a42844fba2b09f3e4caecf2b.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚零售销售月环比&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：TradingEconomics&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p0.itc.cn/q_70/images03/20230303/197242d4d8dc47f8b7b64e96e1b6a2d5.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚个人新房销售&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：TradingEconomics&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p6.itc.cn/q_70/images03/20230303/39b477429bc34138a60c758ba04cfaed.png&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚GDP季度增长率&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：TradingEconomics&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;“家庭和政府支出的持续增长推动了消费的增长，而旅游服务出口的增加以及海外对煤炭和矿石的持续需求推动了出口，”ABS 国民账户负责人 Katherine Keenan 表示。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;但周三的数据表明，澳大利亚经济在 疫情后的高速增长已经失去动力，未来几个月的增长将进一步放缓。澳联储最近也对这种情况发出警告，称经济软着陆的道路仍然狭窄。澳联储预测，2023年澳大利亚经济将增长约1.5%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;澳元走势分析&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳元兑美元自2月25日以来一直在0.67 – 0.675区间小幅盘整。下跌动能的放缓主要原因在于美元近期涨势的放缓，其在105上方明显承压。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;尽管如此，考虑到目前对美国通胀上行风险以及美联储鹰派押注的关注，市场现阶段风险规避情绪较大，因此美元现阶段仍大概率维持强势上涨趋势。考虑到 3 月份即将发布的诸多关键数据和重要事件，包括美国2月的就业报告、通胀数据以及美联储利率决议，澳大利亚第四季度 GDP 数据对汇率的影响可能不大。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;因此，即使澳元现阶段出现一定的反弹修复，但不会影响整体的下跌趋势。相反，经济的逐步降温可能促使澳元在更长的周期中走软。&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;4小时结构澳元处于下跌趋势中，短期价格可能反弹至阻力0.678。预计在该位置承压并进一步下跌至0.663。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳元兑美元 AUD/USD —— 4小时图（3月1日）&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：CMC Markets&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;李竹君 Leon Li&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;CMC Markets市场分析师&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;免责声明：本网所发所有文章，包括本网原创、编译及转发的第三方稿件及评论，均不构成任何投资建议，交易操作或投资决定请询问专业人士。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;(文章来源：CMC Markets)&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;【昨日公司新闻回顾】&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;不到1年上涨超过15倍 疫情期明星网络安全股WhiteHawk最新财报出台 连续5年营收保持高增长&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;ASX澳交所上市网络安全服务公司 WhiteHawk （ASX：WHK）周一发布的2022年运营报告显示，自2018年年初在ASX澳交所挂牌以来，这家公司销售收入连续5年保持高速持续增长，并在过去的2022年中两个季度实现正向运营现金流。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;从年报看，WhiteHawk （ASX：WHK）这家具有独特商业模式的网络安全科技公司——疫情期间曾经在不到1年上涨超过15倍的明星股，在后疫情时代迎来快速发展机遇，公司业务规模有望继续保持高速增长。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;WhiteHawk公司营收连续保持5年增长 企业亏损大幅收窄&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;WhiteHawk （ASX：WHK）年报显示，2022全年实现经常性营收3,215,29美元，较2021年现金营收2,302,517美元上涨39.6%。上述营收不包括2022年已开具发票的 43.3 万美元未实现收入(客户预付款)以及2022 年 12 月 8 日宣布一笔82.5 万美元的续约合同金额。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;最新财报表明，公司运营团队成功克服了疫情对行业带来的影响，实现自2018年来连续第5个年头的增长。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;来源：WhiteHawk 2022年4季报&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;营收高速增长同时，公司亏损大幅收窄，2022年全年亏损1,506,318美元，较2021年下降38.9%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;财报显示，2022年150万美元左右的亏损中，包括与折旧相关的非现金支出摊销费用67,000 美元，以及股权对价的费用支出114,000 美元，另有17.1 万美元的可疑债务准备金。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;公司现金和资产负债方面，截至2022年年底，公司现金余额217.1万美元，没有债务。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;公司业务渠道全面拓展&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;2022 年 12 月WhiteHawk开始为 Peraton 提供网络供应链风险管理 (C-SCRM) IRAD 第一阶段服务，并确定了 2023 年第一季度的第二阶段服务合同。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;与此同时，WhiteHaw合作伙伴 Dun &amp;amp;amp; Bradstreet (D&amp;amp;amp;B)向美国一家主要的联邦系统集成商 (FSI) 出售了 500 个由 WhiteHawk 提供支持的网络合规许可证。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;值得一提的是，由 D&amp;amp;amp;B 公共关系部门牵头，2023年第一季度有望达成的涵盖全美 5,000 家金融机构的网络风险监控合同，也进入最后关键时期，目前在等待美联储的最后决定。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;此外，另外2项正在推进的业务同样令人瞩目。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;其一是响应美国国土安全部 (DHS) 的CISA NRMC SCRM “小型企业溯源定位” 工程，预计 3 年 3000 万美元合同金额。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;其二是，WhiteHawk与美国国防承包商Peraton合作启动了供应链风控系统独立研发（IRAD）项目第一阶段，并将于2023年第一季度进入第二阶段规划期。目前已与 Peraton 签署意向书，作为网络技术植入提供商和 C-SCRM 项目合作伙伴，参与并竞标美国国土安全部2023 年下半年总额数十亿美元的CISA合同招标。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;网络风险评估试点（Cyber Risk Assessments Pilot）业务方面，已最终确定佛罗里达相关网络关键基础设施，并将于 2023 年第一个季度在150个实体中进行。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;WhiteHawk 公司方面表示，2023 年继续同步推进网络风险和安全市场的创新和自动化，同时立足客户反馈，无缝式推进网络安全在线交换和所有软件即服务产品线，与美国、澳大利亚和英国的下一代供应商合作伙伴深度合作，向客户提供端到端、自动化、可扩展、有效且可负担得起的经济高效、易于实施的网络安全解决方案。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;2022年12月下旬，WhiteHawk创始人兼首席执行官Terry Roberts女士曾接受ACB News《高管访谈》栏目视频采访，在谈到业务模式和其它澳大利亚网络安全服务公司有何不同时，Terry Roberts女士表示，“WhiteHawk提供的是一种端到端、自动化、可扩展的在线方法，这有别于较为传统的网络安全保护方式，比如咨询服务——我们确实与顾问机构合作，他们可以使用我们的风险评估工具和产品线，或者是传统的网络安全合规方式，这类工作内容繁重、依赖现场技术，但却不可扩展。”详见《 WhiteHawk创始人Terry Roberts专访：化解数字时代风险 帮万千企业守住“皇冠明珠” 》&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;据悉，WhiteHawk研发推出的几个特定产品线，其中一种是针对单个公司的“网络风险计划”，另一个是由供应商提供的“网络风险雷达”，可以由50个或1000多个供应商参与，然后是可自动生成的长达20页的网络风险评估报告，这份报告是WhiteHawk所有业务的基础。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;二级市场方面，WhiteHawk在2020年疫情期间的表现曾给市场留下强烈印象，公司股价自2020年3月低点0.025澳元，在不到一年时间，放量飙升至2021年年初的最高价0.465澳元，最大升幅超过16倍。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;近期表现看，去年10月公司股价在创出1年低点0.049澳元后，股价放量回升。2023年1月20日，WhiteHawk公告称将以股权对价方式向某机构发行股份250万股，每股价格0.1澳元，以此支付合同约定的服务费用。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;被巴西临时征收石油出口税 Karoon股价急挫近8%&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;澳大利亚石油公司Karoon(ASX:KAR)周四发布公告称，巴西政府宣布，将在3月1日至6月30日的四个月内，对其出口的石油征税9.2%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;公告表示，根据目前的生产指引，这将导致2200万美元至3500万美元（税后1500万美元至2300万美元）的支出，具体金额取决于原油出口量和石油售价。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;公告发布后，Karoon股价周四早盘下跌。11:31成交价为2.07澳元，下跌0.17澳元，跌幅7.59%。该股近一年的投资回报率为亏损4.61%。&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;font-size: 16px;&amp;quot;&amp;gt;（部分资料来源：澳洲金融评论 澳大利亚人报 RBA）&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;免责声明： 本文为财经观察评论，不构成任何投资建议，交易操作或投资决定请询问专业人士。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;（郑重声明：ACB News《澳华财经在线》对标注为原创的文章保留全部著作权限，任何形式转载请标注出处。）&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;更多资讯&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;1 澳GDP大幅放缓将如何影响澳元走势？住宅批建量骤降 “洗绿”企业面临监管重锤 或遭宁德时代减持 Pilbara 股价大幅回落&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;2 疫情前后两重天！墨尔本CBD经历从“鬼城”到火热目的地变迁 市场调低澳储行加息峰值预测 澳洲房价跌势趋缓&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;3 Yancoal 续写周期股传奇！营收逾百亿净利35.86亿 拟每股派息0.7澳元 特斯拉年初至今股价翻倍 马斯克重归世界首富&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;4 澳洲公寓供应量未来两年或锐减七成 澳储行加息峰值或至4.4% 奔富与中国酒业协会战略会谈 中澳葡萄酒贸易迎新机&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;5 医疗保健地产行情看好 西太银行预测利率五月份见顶 澳洲两大公司少付薪酬400万澳元&amp;lt;/p&amp;gt;', 1765539677, 1766390597, '2025-12-12 19:41:17', '2025-12-12 19:41:17', 3, '新闻', 5),
                                                                                                                                                                                      (42, '高管来华丨Blackmores澳佳宝集团CEO施民腾确认参加第六届进博会并出席相关活动', '/storage/20251212/e1b24d071ae8a2a11a9fba2165032ea8.png', '10', '澳佳宝', 1767196800, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;高管来华丨Blackmores澳佳宝集团CEO施民腾确认参加第六届进博会并出席相关活动&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;此前已连续五届参展的澳大利亚天然营养品公司Blackmores澳佳宝已确认参加第六届进博会。集团CEO施民腾(Alastair Symington)将再赴进博之约。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://www.ciie.org/resource/upload/zbh/202307/26084944sm87.png&amp;quot; alt=&amp;quot;blackmore1.png&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: auto;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;Blackmores澳佳宝集团CEO施民腾 (Alastair Symington)&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;“澳佳宝是中国高水平开放的受益者，也是积极参与者。”&amp;lt;/strong&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;自2018年首届进博会起，澳佳宝连续参展，为中国消费者带来优质健康产品，也借助进博会这个分享中国发展机遇的重要窗口，深度融入新发展格局，与全球大健康领域同业交流先进理念，用实际行动助力健康中国建设。澳佳宝集团CEO施民腾表示，“中国是澳佳宝最重要的海外市场之一。澳佳宝一路见证进博会越办越好、越办越精，深刻感受到进博会所带来的溢出效应。”&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://www.ciie.org/resource/upload/zbh/202307/260850093yls.png&amp;quot; alt=&amp;quot;blackmore2.png&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: auto;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;Blackmores澳佳宝集团CEO施民腾2019年参加第二届进博会&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;“预防是最经济最有效的健康策略。”&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;澳佳宝高度赞同《“健康中国2030”规划纲要》和以预防为主的健康策略。“预防是最经济最有效的健康策略。”“治未病”的传统理论在中国传承了千百年，这与澳佳宝秉承的自然疗法不谋而合，“预防和治疗”也逐渐成为公众的普遍认知。澳佳宝作为天然营养品公司，精研“自然疗法”90多年，始终坚持利用天然草本植物、维生素、矿物质来改善身心健康状态。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://www.ciie.org/resource/upload/zbh/202307/26085025h6m3.png&amp;quot; alt=&amp;quot;blackmore3.png&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: auto;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;2022年第五届进博会澳佳宝展位&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;“全生命周期”“全方位营养”&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;在生命的不同阶段，人们面临不同的营养需求和健康挑战。通过进博会这一展示平台，澳佳宝不仅带来旗下明星鱼油、护眼、关节健康产品，还将“全生命周期营养”与“全方位营养”的健康管理理念带给每一位参与者，让更多人主动健康、参与健康、管理健康。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://www.ciie.org/resource/upload/zbh/202307/260850351l7g.png&amp;quot; alt=&amp;quot;blackmore4.png&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: auto;height: auto;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;', 1765539803, 1766390523, '2025-12-12 19:43:23', '2025-12-12 19:43:23', 3, '新闻', 10),
                                                                                                                                                                                      (43, '2025健康中国传播大会在京举行', '/storage/20251212/4fe44eb44167bcb406c15b5212466f45.jpg', '3', '澳佳宝', 1767254615, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;2025健康中国传播大会在京举行&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;12月6日，以“讲好健康中国故事 推动健康中国传播”为主题的2025健康中国传播大会在北京举行。本次大会由中国医药卫生文化协会、国家卫生健康委员会百姓健康频道、北京陆士新基金会共同主办，中国医院协会医院健康促进专业委员会协办。大会汇聚卫生健康领域相关领导、学术专家、医疗机构负责人、健康科普工作者、主流媒体与传播行业代表等数百人，共同探讨健康传播新理论和新实践、赋能健康传播效能提升新技术、搭建健康传播合力新平台等议题，为健康中国建设注入专业、系统、可持续的传播新动力。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;https://p1.img.cctvpic.com/photoworkspace/contentimg/2025/12/07/2025120719491965865.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;中国医药卫生文化协会会长郑宏在开幕致辞中表示，协会致力于搭建健康传播平台，深化文化融合，拓展合作网络，构建健康传播事业共同体，为健康中国建设贡献文化力量。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;中国健康教育中心副主任(主持工作)吴敬在致辞中表示，作为国家级专业公共卫生机构和百姓健康频道的主办单位，中心将持续发挥技术支持与平台引导作用，协同各方力量，共同打造权威、易懂、易传播的健康科普内容。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;大会主席、国家卫生健康委员会百姓健康频道编委会主任毛群安在致辞中阐述了“讲好健康中国故事 推动健康中国传播”这一主题的时代内涵。他指出，健康科普与卫生文化传播是连接政策与公众、提升全民健康素质的关键驱动力。本届大会将启动医院健康传播融媒体平台建设，推进全民健康素养66条等系列工程，致力于构建更加科学、精准、高效的健康传播新生态，为健康中国建设凝聚广泛合力。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;主题演讲：以扎实成果，诠释健康传播主题&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;开幕式后，大会进入系列主题演讲环节。各位演讲嘉宾结合自身领域，从政策、学术、实践等不同维度，深度诠释了“讲好健康中国故事”的丰富内涵与“推动健康中国传播”的多元路径。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;吴敬在《健康素养66条工作成果展示》演讲中，系统阐释了健康素养作为衡量国民健康核心指标的战略意义，并展示了围绕《健康素养66条》取得的突破性传播成果。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;国家心理健康和精神卫生防治中心指导的短片《归航》在会上播放。这部短片呈现了我国在构建社会心理支持系统、促进精神障碍患者社区康复与融入方面的实践。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;国家卫生健康委妇幼健康中心副主任贾丹丹在主旨演讲《中国妇幼健康发展现状与展望》中，系统介绍了我国妇幼健康工作取得的显著成效。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;国家卫生健康委人口文化与基层健康中心副主任张并立在《持续推动基层卫生健康事业高质量发展》报告中指出，基层医疗卫生服务体系是卫生健康工作的基石。高质量发展需坚持公益性导向，未来将通过优化布局、实施强基工程及深化医共体建设等重点举措，进一步筑牢人民群众身边的健康防线。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;中国疾病预防控制中心慢病中心主任吴静在题为《慢病防治》主旨演讲中系统剖析了我国慢性病“患病率高、共患多、负担重”的严峻形势，强调其本质是生活方式疾病。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;北京师范大学传播创新与未来媒体实验平台主任喻国明教授在题为《AI时代的健康传播与心理健康》的演讲中指出，人工智能正推动健康传播从单向灌输转向沉浸式、情境化的体验传播。他特别强调，以游戏为代表的数字媒介将成为心理健康干预的新前沿，通过“游戏驱动数字疗法”的创新生态，可有效应对一老一小等重点人群的心理健康挑战，并为构建普惠、精准的社会心理服务体系提供科技支撑与未来路径。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;南京市妇幼保健院党委书记沈嵘结合实践，在报告《生育友好型医院建设-早孕关爱项目经验介绍》中分享了该院建立早孕关爱中心、推进生育友好医院建设的创新经验。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;传播工程：扬帆起航，从战略规划到实际行动&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;本届大会举行了全民健康素养提升公益工程、医院健康促进融媒体传播平台、肿瘤患者及照护者的心理健康科普行动、心理健康传播课题研究、妇幼健康传播工程、“强基工程”工程传播行动、慢病防治传播工程等七大项目的启动仪式。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: start;&amp;quot;&amp;gt;大会特别设立了两个并行分会场，在下午同期举行。分会场一以“科技创新与健康传播双轮驱动下的健康中国建设”为核心，聚焦前沿科研、技术转化与医院管理实践，深入探讨了科技如何赋能健康传播的创新路径。分会场二围绕“新媒体赋能全民健康素养提升”展开，汇聚了来自新媒体平台、医疗科普创作者等多方代表，共同分享了利用新媒体提升健康科普效能、构建社会共建生态的鲜活经验与前瞻思考。&amp;lt;/p&amp;gt;', 1765540377, 1766390620, '2025-12-12 19:52:57', '2025-12-12 19:52:57', 3, '新闻', 3),
                                                                                                                                                                                      (44, '2025健康中国传播大会在京举行', '/storage/20251212/db5a8ada0691ed6b242def5002871fba.jpg', '8', '澳佳宝', 1767254563, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;font-size: 19px;&amp;quot;&amp;gt;2025健康中国传播大会在京举行&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2025年12月6日，以“讲好健康中国故事 推动健康中国传播”为主题的2025健康中国传播大会在北京国家会议中心成功举行。本次大会由中国医药卫生文化协会、国家卫生健康委员会百姓健康频道、北京陆士新基金会共同主办，中国医院协会医院健康促进专业委员会协办。大会汇聚卫生健康领域相关领导、学术专家、医疗机构负责人、健康科普工作者、主流媒体与传播行业代表等数百人，共同探讨健康传播新理论和新实践、赋能健康传播效能提升新技术、搭建健康传播合力新平台等议题，为健康中国建设注入专业、系统、可持续的传播新动力。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;相关视频：2025健康中国传播大会12月6日在京举行&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;开幕致辞：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;凝聚传播共识，擘画传播新蓝图&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;大会在庄重务实的氛围中拉开帷幕。上午的主会议环节，多位主管部门与主办方领导发表致辞，从战略规划、行业实践与文化建设等多维视角，为新时代健康传播工作锚定方向、凝聚共识。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics6.baidu.com/feed/9213b07eca80653811594a3718334054ac348266.jpeg@f_auto?token=a9aca4c2569408e72c35911f266b054e&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国医药卫生文化协会会长郑宏&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国医药卫生文化协会会长郑宏在开幕致辞中强调，要深入学习贯彻党的二十届四中全会精神，在推进健康中国建设进程中，必须坚持党的领导，加强健康文化传播的培育与引领。协会致力于搭建健康传播平台，深化文化融合，拓展合作网络，构建健康传播事业共同体，为健康中国建设贡献文化力量。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/faf2b2119313b07e81abc9e28239703395dd8cc1.jpeg@f_auto?token=1d35a8e8ff5745a6f35f91c7ca6ab498&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国健康教育中心副主任（主持工作）吴敬&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国健康教育中心副主任(主持工作)吴敬在致辞中围绕提升全民健康素养这一核心任务，系统介绍了我国近年来在该领域取得的进展。他指出，国家通过完善法规政策、加强科普规范、建设专家资源库及开展多样化健康教育，推动居民健康素养水平从2012年的8.8%显著提升至2024年的31.87%。吴敬强调，作为国家级专业公共卫生机构和百姓健康频道的主办单位，中心将持续发挥技术支持与平台引导作用，协同各方力量，共同打造权威、易懂、易传播的健康科普内容。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics1.baidu.com/feed/9922720e0cf3d7cac6981e417df15f196a63a99e.jpeg@f_auto?token=22a15e223a4e0f2f12ec20d37e7a1423&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;大会主席、国家卫生健康委员会百姓健康频道编委会主任毛群安&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;大会主席、国家卫生健康委员会百姓健康频道编委会主任毛群安在致辞中阐述了“讲好健康中国故事 推动健康中国传播”这一主题的时代内涵。他指出，健康科普与卫生文化传播是连接政策与公众、提升全民健康素质的关键驱动力。本届大会将启动医院健康传播融媒体平台建设，推进全民健康素养66条等系列工程，致力于构建更加科学、精准、高效的健康传播新生态，为健康中国建设凝聚广泛合力。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;随后，大会主席毛群安以国家卫生健康委员会百姓健康频道编委会主任的身份，深入阐述了新时代健康传播的使命与担当。他介绍了百姓健康频道新一届编委会的构成与工作规划，表示将致力于打造更具权威性、影响力的健康科普平台，汇聚优质资源，创新叙事方式，产出精品内容，切实“讲好健康中国故事”，服务百姓健康需求。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;主题演讲：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;以扎实成果，诠释健康传播主题&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;开幕式后，大会进入系列主题演讲环节。各位演讲嘉宾结合自身领域，从政策、学术、实践等不同维度，深度诠释了“讲好健康中国故事”的丰富内涵与“推动健康中国传播”的多元路径。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics6.baidu.com/feed/e824b899a9014c08213642958595e3187af4f4f4.jpeg@f_auto?token=447968b9c486f57994f9590aa78773dc&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国健康教育中心副主任（主持工作）吴敬&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;吴敬副主任（主持工作）在《健康素养66条工作成果展示》演讲中，系统阐释了健康素养作为衡量国民健康核心指标的战略意义，并展示了围绕《健康素养66条》取得的突破性传播成果。通过构建“权威引领、医生共创、平台助推”的全媒体传播体系，相关科普内容累计触达超29亿人次，实现了健康知识从“听得到”到“听得懂、听得进”的深刻转变，为夯实健康中国建设的社会基础提供了关键支撑。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics5.baidu.com/feed/8326cffc1e178a828dd914aa79ed929dab77e8d6.jpeg@f_auto?token=f339c565fdbaf235ae3a0759fe034d79&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;短片《归航》&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国家心理健康和精神卫生防治中心指导的短片《归航》在会上播放。这部短片生动呈现了我国在构建社会心理支持系统、促进精神障碍患者社区康复与融入方面的温暖实践。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics7.baidu.com/feed/3b87e950352ac65cadf261786b1c530191138ac1.jpeg@f_auto?token=718a716047188fb2dfe9fceebd67b5fa&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国家卫生健康委妇幼健康中心副主任贾丹丹&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国家卫生健康委妇幼健康中心副主任贾丹丹在主旨演讲《中国妇幼健康发展现状与展望》中，系统介绍了我国妇幼健康工作取得的显著成效：核心指标位居全球中高收入国家前列，被世卫组织列为妇幼健康高绩效国家；2024年孕产妇及儿童死亡率均达历史最优水平；通过“1+3+N”多重保障体系及重点公共卫生项目，显著提升了服务的公平可及性。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/18d8bc3eb13533fa9ee08536273d1c0f40345b56.jpeg@f_auto?token=d653bd2b0a9e05fcd54a410094d12d0d&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国家卫生健康委人口文化与基层健康中心副主任张并立&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国家卫生健康委人口文化与基层健康中心副主任张并立在《持续推动基层卫生健康事业高质量发展》报告中指出，基层医疗卫生服务体系是卫生健康工作的基石。当前我国基层卫生网络覆盖广泛，服务能力持续提升，诊疗人次显著增长。高质量发展需坚持公益性导向，未来将通过优化布局、实施强基工程及深化医共体建设等重点举措，进一步筑牢人民群众身边的健康防线。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics2.baidu.com/feed/060828381f30e924c12e9b18c3e68f161c95f714.jpeg@f_auto?token=4ca385cdd9c50d0bdebaaed60621ced5&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国疾病预防控制中心慢病中心主任吴静&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;中国疾病预防控制中心慢病中心主任吴静在题为《慢病防治》主旨演讲中系统剖析了我国慢性病“患病率高、共患多、负担重”的严峻形势，强调其本质是生活方式疾病。她介绍了国际通行的“5×5”防控策略，并重点展示了“健康中国科普先行”等优质科普项目的显著成效，累计传播超5000万人次，有效提升了公众知晓率与自我管理能力，为慢病防控注入了关键的“社会疫苗”力量。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics3.baidu.com/feed/728da9773912b31bc9c9d82909f6d76adbb4e10c.jpeg@f_auto?token=38a3c458f7fc9069716b8350a3dd8f3e&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;北京师范大学传播创新与未来媒体实验平台主任喻国明&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;北京师范大学传播创新与未来媒体实验平台主任喻国明教授在题为《AI时代的健康传播与心理健康》的演讲中指出，人工智能正推动健康传播从单向灌输转向沉浸式、情境化的体验传播。他特别强调，以游戏为代表的数字媒介将成为心理健康干预的新前沿，通过“游戏驱动数字疗法”的创新生态，可有效应对一老一小等重点人群的心理健康挑战，并为构建普惠、精准的社会心理服务体系提供科技支撑与未来路径。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics0.baidu.com/feed/8c1001e93901213f34e8faeddb09d7c12e2e9523.jpeg@f_auto?token=ba020c3e325977fb257161a8abc95d68&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;南京市妇幼保健院党委书记沈嵘&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;南京市妇幼保健院党委书记沈嵘结合实践，在报告《生育友好型医院建设-早孕关爱项目经验介绍》中分享了该院建立早孕关爱中心、推进生育友好医院建设的创新经验。该中心以产科为主导，整合多学科资源，提供从评估、咨询到干预的全流程服务，月门诊量已突破1.1万人次，是国家“早孕关爱行动”在医疗机构的生动实践，为构建生育友好型社会提供了基层样板。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;传播工程：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;扬帆起航，从战略规划到实际行动&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;为将大会主题转化为切实的行动抓手，本届大会隆重举行了全民健康素养提升公益工程、医院健康促进融媒体传播平台、肿瘤患者及照护者的心理健康科普行动、心理健康传播课题研究、妇幼健康传播工程、“强基工程”工程传播行动、慢病防治传播工程等七大项目的启动仪式。这些贯穿全年的主题传播项目，将整合疾控系统、医疗机构、学会协会、媒体的力量，构建了一个覆盖核心人群、关键生命阶段与重点健康问题的传播矩阵，是“讲好健康中国故事，推动健康中国传播”从理念到实践的关键跨越。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics3.baidu.com/feed/e824b899a9014c08f6350bc38595e3187af4f44d.jpeg@f_auto?token=7c8e1b07c09bf6c8bf4122439454edf2&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics1.baidu.com/feed/aec379310a55b3191e8c20bacd476336cdfc1783.jpeg@f_auto?token=8324e854227faa6003a6da28e82b471b&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics0.baidu.com/feed/83025aafa40f4bfb93cab1688ca199e0f63618f6.jpeg@f_auto?token=75fc786dd3b20ecf0f486fffa1968310&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics1.baidu.com/feed/78310a55b319ebc4009096b00dc82eec1f17162a.jpeg@f_auto?token=a67fcbe65d8e80192c8e6d40510363be&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics1.baidu.com/feed/30adcbef76094b369e3dfd1e2c229dc98f109d91.jpeg@f_auto?token=613bcee00bb38bba856c5060491c7005&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics2.baidu.com/feed/3801213fb80e7bec299aa482a0c058289a506b14.jpeg@f_auto?token=0fd1b6ef4064240795eab2346cfac910&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics6.baidu.com/feed/91ef76c6a7efce1b1f35612e21bf12ceb58f6533.jpeg@f_auto?token=ff6c06ecb6fae97f5a8c11d850dd0b30&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;平行分会场：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;构建“硬科技”与“软传播”新生态&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;大会特别设立了两个并行分会场，在下午同期举行。分会场一以“科技创新与健康传播双轮驱动下的健康中国建设”为核心，聚焦前沿科研、技术转化与医院管理实践，深入探讨了科技如何赋能健康传播的创新路径。分会场二围绕“新媒体赋能全民健康素养提升”展开，汇聚了来自新媒体平台、医疗科普创作者等多方代表，共同分享了利用新媒体提升健康科普效能、构建社会共建生态的鲜活经验与前瞻思考。两个分会场从“硬科技”与“软传播”两个关键维度展开了深入研讨，进一步拓展和深化了大会的主题内涵。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;结语：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;汇聚传播之力，共赴健康之约&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2025健康中国传播大会在充实而高效的议程中落下帷幕。本届大会不仅是一场高规格的行业峰会，更是一次健康传播成果的巡礼、经验的汇聚与行动的动员。在健康中国建设的伟大征程中，健康传播是推动战略落地、凝聚社会共识、激发个体行动的主体力量之一。随着各项传播工程的落地与行业共识的深化，一幅政府主导、专业支撑、媒体协同、社会参与、全民受益的健康传播新图景正徐徐展开。&amp;lt;/p&amp;gt;', 1765540666, 1766402107, '2025-12-12 19:57:46', '2025-12-12 19:57:46', 3, '新闻', 8),
                                                                                                                                                                                      (45, '健康中国战略下的大健康产业新机遇与各方探讨', '/storage/20251212/e06bde7983b8c0d7cd6b9cb9a2fbe1fb.jpg', '9', '澳佳宝', 1767254539, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); background-color: rgb(255, 255, 255); font-size: 19px;&amp;quot;&amp;gt;健康中国战略下的大健康产业新机遇与各方探讨&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); font-size: 24px;&amp;quot;&amp;gt;01&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0);&amp;quot;&amp;gt;活动背景与概述&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;本次活动由中企研与天士力大健康联合举办，主题聚焦于“健康中国”战略下，通过科技创新推动大健康产业的生态建设和企业新赛道的开辟。活动吸引多方代表参与，共同探讨大健康产业机遇。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;8月20日，由中国企业改革与发展研究会（简称中企研）携手天士力大健康产业投资集团有限公司（简称天士力大健康）共同举办的第十六期中国企业大讲堂，在天津盛大落幕。此次活动聚焦于“创新驱动：塑造大健康产业新生态与开辟企业新赛道”这一核心议题，汇聚了政府、产业、学术及研究等多方代表，共同探讨在“健康中国”战略的指引下，如何通过科技创新来推动大健康产业的蓬勃发展，并为企业的长远发展注入不竭的动力。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics2.baidu.com/feed/6c224f4a20a44623fc50d6c5c872c21e0df3d764.jpeg@f_auto?token=efb028efbd3cfb8b107e7b76ba209e14&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/d53f8794a4c27d1e32d1fed349851d7edcc43897.jpeg@f_auto?token=329021f6586b12e31eb2295ae22093a9&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics7.baidu.com/feed/d6ca7bcb0a46f21faecd5fe8a674db700d33ae74.jpeg@f_auto?token=6d4ae70d0ccc5d74507b515dcbf00990&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 活动嘉宾与主持&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;活动有众多重量级嘉宾出席，包括政府官员、中医药专家及企业高层，由范建林担任主持人。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;国医大师、天津中医药大学名誉校长张伯礼；中企研会长，国务院国资委原党委委员、秘书长彭华岗；中企研第一副会长，中国一重集团有限公司原党委书记、董事长刘明忠等多位 expert都出席了本次活动。范建林副会长担任了本次活动的主持人。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); font-size: 24px;&amp;quot;&amp;gt;02&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0);&amp;quot;&amp;gt;主题演讲及讨论&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 彭华岗讲话&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;彭华岗强调“健康中国”战略的重要性，指出大健康产业规模的快速增长及对经济的拉动作用，鼓励企业利用创新迎来发展契机。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;彭华岗在致辞中指出，自党的十八大以来，“健康中国”战略已逐渐演变为国家层面的重大战略，深刻融入了中国式现代化的内涵之中。目前，我国大健康产业的规模已突破15万亿元，预计到2030年将激增至30万亿元。这一产业不仅满足了人民对美好生活的追求，更成为拉动经济增长的新动力。随着生命科学与生物技术的飞速进步，如基因编辑、细胞治疗和数字医疗等新技术的涌现，大健康产业的格局正在发生深刻变革。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics6.baidu.com/feed/1e30e924b899a9016e18958a4dc5ba6b0208f53a.jpeg@f_auto?token=5d03401a694b1e73262801a28701d2bb&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 张伯礼的主题演讲&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;张伯礼着重讲述了中医药的战略价值及其在大健康产业中的创新发展，提倡推动数字化、智能化转型，以及全民健康生活方式的倡导。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;在《中医药与大健康产业发展》的主题演讲中，张伯礼深入探讨了中医药作为国家战略资源的核心价值及其创新发展之路。他强调了中医药作为我国独特的卫生资源、关键的经济资源、拥有原创科技优势的宝贵财富。张伯礼指出，中医药为全球医改难题的解决提供了“中国方案”。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics4.baidu.com/feed/77c6a7efce1b9d16aee694e5a38e049f8d5464f5.jpeg@f_auto?token=a058b7f1eee8b534e276ecfccc806a07&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 闫凯境的细胞治疗进展&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;闫凯境介绍了“还原健康”新范式和间充质基质细胞领域的最新突破，讨论了细胞治疗的全生命周期管理及全球产业链布局。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;闫凯境在《生命科技变革：还原健康开启生命科学新纪元》的主题演讲中，阐述了“还原健康”这一新范式的核心——细胞微环境重塑。他提出，通过修复微循环、激活机体的自我修复潜能，能够从本质上抗击衰老与慢性疾病。同时，闫凯境介绍天士力大健康在间充质基质细胞领域的多项突破。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 魏万林的职场健康策略&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;魏万林推荐中医体质调理，为不同体质提供个性化建议，倡导企业在文化体系中加入健康管理。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;魏万林针对职场人群所面临的健康问题，进行了《高效能工作与中医体质调理的平衡艺术》的主题演讲。他深入阐述了中医九种体质的辨识方法及相应的调理策略，着重强调了“药食同源、辨体施养”的核心理念。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 张晗的体检数据分析&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;张晗提出“五色灯”报告解读法，探讨体检数据的临床价值，倡导以预防为核心的健康管理体系。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;在《体检数据背后的健康密码——职场健康管理路径探索》的主题演讲中，张晗详细剖析了心脑血管、代谢、器官功能等关键体检指标的临床价值与潜在健康风险。她创新性地提出了“五色灯”报告解读法。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); font-size: 24px;&amp;quot;&amp;gt;03&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0);&amp;quot;&amp;gt;大讲堂总结&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 总结与发展展望&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;范建林总结各领域专家的观点，指出大健康产业的机遇，鼓励企业加强创新合作，共建健康服务新生态。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;在本次大讲堂的总结环节，范建林指出，各位专家的精彩分享不仅深入解读了“健康中国”战略的实施路径，还展示了天士力大健康集团在细胞治疗、中医药现代化以及智慧健康等领域的卓越成果。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;img src=&amp;quot;https://pics3.baidu.com/feed/83025aafa40f4bfb18dda4de531fc8e0f636188f.jpeg@f_auto?token=201d6f867db566d3b98522905c01ba1b&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;width: 699.734px;&amp;quot;/&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); font-size: 24px;&amp;quot;&amp;gt;04&amp;lt;/span&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0);&amp;quot;&amp;gt;活动参与及效果&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h4&amp;gt;&amp;amp;gt; 企业参与与媒体报道&amp;lt;/h4&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;活动得到多家企业的支持与媒体直播，提升了公众对“健康中国”战略的认知，彰显了中企研活动品牌的社会影响力。&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;本次大讲堂活动得到了众多中央企业、地方国企及民营企业的积极参与。国家能源投资集团有限责任公司、中国华电集团有限公司等多家行业巨头派代表参加。活动通过新华网、人民政协网等媒体平台进行全程直播，累计观看量高达1110万人次，极大地提升了公众对“健康中国”战略和大健康产业创新的认知与兴趣。&amp;lt;/p&amp;gt;', 1765541099, 1766390545, '2025-12-12 20:04:59', '2025-12-12 20:04:59', 3, '新闻', 9),
                                                                                                                                                                                      (46, '2025全民健康素养促进行动基层动员会（北京站）召开 凝聚多方力量共筑健康中国基层防线', '/storage/20251212/476a9da9abffba95f228a08b98e0e2cd.jpg', '6', '澳佳宝', 1767254583, '&amp;lt;p&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(0, 0, 0); font-size: 19px;&amp;quot;&amp;gt;2025全民健康素养促进行动基层动员会（北京站）召开 凝聚多方力量共筑健康中国基层防线&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;新华网北京5月20日电（吴起龙） 5月14日，由国家卫生健康委中国健康教育中心指导、新华网主办、RDPAC协办，中国社区卫生协会与北京朝阳医院共同支持的“健康素养 全民同行”——2025全民健康素养促进行动基层动员会（北京站）在京召开。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;现场，来自国家卫生健康领域的权威专家、基层医疗卫生机构代表及行业同仁齐聚一堂，围绕“疾病诊疗与管理”“聚力基层科室建设”两大核心议题展开深度研讨，通过政策解读、实践分享与互动交流，为推进基层健康素养提升工程凝聚智慧力量，筑牢“健康中国”建设基层防线。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_20250520a5e537eafdca4ab6b1417039fb8866b0.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;健康素养提升进攻坚期 基层医疗体系建设成重要抓手&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;当前，我国居民健康素养提升正处于关键攻坚期。国家卫生健康委数据显示，2023年中国居民健康素养水平达到29.70%，虽较十年前增长近4倍，但仍有超七成人群健康素养不达标，慢性病防控、健康信息甄别等核心能力亟待强化。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;与此同时，肥胖、特应性皮炎等慢性疾病呈现年轻化、普遍化趋势。在此背景下，如何通过基层医疗体系建设实现健康关口前移，成为落实“健康中国2030”战略的核心命题。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505209ed8b27dd9824132bcd3ad3c0b435330.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;新华网健康事业部总经理周诗华&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;会议现场，新华网健康事业部总经理周诗华作为主办方代表致辞表示，本次活动是贯彻《全民健康素养提升三年行动（2024—2027年）》的重要实践，旨在通过健康科普、疾病管理与基层能力建设，推动健康资源向基层倾斜。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“从健康中国2030到三年行动方案，政策始终强调预防为主，而健康素养正是影响健康水平的核心因素。”他说，作为中央重点新闻网站，新华网将持续通过短视频、直播访谈等创新形式，推动优质健康资源下沉，并联动地方政府与医疗机构，探索“媒体+医疗+科技”的协同模式，让人工智能、大数据等技术赋能健康科普精准化与个体管理智能化。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院内分泌科副主任、主任医师刘佳在致辞中聚焦肥胖管理这一民生痛点。她透露，中国成人超重和肥胖患病率已超50%，青少年达30%，且中心性肥胖比例更高。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_2025052070f48336359549a6be74a4d62bc25a74.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院内分泌科副主任、主任医师刘佳&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;刘佳表示，肥胖等慢病管理需打破“三甲医院单打独斗”的模式，通过“上下联动”构建全周期管理体系——三甲医院制定诊疗方案，基层机构负责长期随访与生活方式干预，形成“减重-维持-防复发”的闭环。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“朝阳医院正与社区机构探索医联体合作，通过定期培训、病例讨论等方式提升基层医生肥胖症的诊疗能力。”她说，此次会议不仅是动员会，更是“上下联动”的启动键。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;临床专家聚焦基层高频疾病 分享诊疗指南与管理经验&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;在“疾病诊疗与管理”环节，三位临床专家围绕肥胖、特应性皮炎、慢病疼痛等基层高频问题展开深度分享，既有指南解读也有实战经验，为基层医生提供可操作的解决方案。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_2025052056e0ef2708ef40e19e3dfa240cc7968d.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院内分泌科主任医师高霞&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院内分泌科主任医师高霞在解读《肥胖患者的长期体重管理及药物临床应用指南（2024版）》时表示，新版指南首次将药物治疗关口前移，强调“早干预早获益”对于超重/肥胖且合并代谢异常，或虽然肥胖程度轻且无明显合并症的患者，如生活方式干预3个月减重＜5%或未达预期，可直接启动减重药物治疗。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“NuSH（营养刺激激素）受体激动剂类药物相关临床研究显示，这类药物不仅能实现体重下降，还能改善血糖、血脂、血压等多重代谢风险因素，降低心肾并发症风险。”她提醒，体重管理需分“强化治疗期”与“治疗维持期”，前者可依据患者情况设立3-6个月减5%-15%的个体化目标，后者需通过药物联合运动、饮食持续治疗防止反弹。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“基层医生需掌握腹型肥胖的快速评估法，男性腰围≥90cm、女性≥85cm即需警惕，可结合体脂仪、CT等辅助检查精准分型。”高霞补充说。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_20250520eff18076de864b3d9af7e79a388a0d30.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院皮肤性病与医学美容科主任刘方&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院皮肤性病与医学美容科主任刘方则带来《特应性皮炎全程管理之道》。她介绍，特应性皮炎（AD）作为“一号皮肤病”，全球患病率超10%，我国患者约8170万，其核心特征是“慢性复发性瘙痒+皮肤屏障功能缺陷”。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“AD不仅影响皮肤，更与过敏性鼻炎、哮喘等构成‘特应性进程’，需跨学科协同管理。”刘方介绍说，基层医生可通过“中国标准”简化诊断：具备家族史、对称性湿疹（病程≥6个月）、顽固瘙痒3项核心指标，结合嗜酸性粒细胞升高或总IGE＞100U，即可确诊。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;在治疗方面，基础护理是基石。她建议患者使用含神经酰胺的无香保湿剂，避免过度洗涤与化纤衣物，并通过窄谱UVB光疗、JAK抑制剂等手段控制中重度病情。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;针对现场提问“运动与AD矛盾”的问题，刘方表示，适度运动可促进多巴胺分泌，对病情有裨益；同时，她建议患者在运动后及时用弱酸性洗剂清洁并加强保湿。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505205e9f34cae90540d7836fcb3a0dacd76a.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;北京大学人民医院疼痛医学科主任医师李君&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;北京大学人民医院疼痛医学科主任医师李君聚焦“慢病疼痛的诊疗与管理”。她表示，我国慢性疼痛患者超3亿，且就诊存在“三多三少”现象：多科室辗转、多药联用、多费用支出，少早期干预、少精准诊断、少综合管理。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;“疼痛科不是‘万能科’，但能解决80%以上的疑难疼痛。”李君以带状疱疹后神经痛、三叉神经痛为例，介绍“阶梯治疗”理念：在理疗、康复锻炼的基础上，给予规范化药物治疗，如效果不佳可进一步采用神经阻滞、射频等微创技术，顽固性疼痛则可植入脊髓电刺激电极。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;她认为，基层医生需掌握“疼痛性质鉴别”。如“针扎样”“放电样”多为神经病理性疼痛，需用普瑞巴林而非布洛芬；“酸胀痛”多为肌筋膜疼痛，可通过冲击波治疗、触发点注射等方法缓解。“疼痛管理的核心是‘精准定位病因’，而非盲目使用镇痛药。”李君补充说。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;&amp;lt;strong&amp;gt;探索基层健康素养提升路径 构建多学科协作服务模式&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;在“聚力基层科室建设”环节，首都医科大学附属北京朝阳医院内分泌科主任医师潘清蓉以该院减重门诊为例，介绍“单学科主导+多学科协作”模式：通过内分泌科牵头，联合营养科、心理科、运动医学科、普外科，为患者提供“饮食-运动-药物-手术”全周期方案。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505201598ab7c62b3496b8c03dbdf5b3b4a58.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;首都医科大学附属北京朝阳医院内分泌科主任医师潘清蓉&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;她还提到，通过AI算法分析患者体脂率、内脏脂肪等数据，可实现“肥胖代谢分型”（如代谢健康型、高胰岛素型），从而制定个性化方案；GLP-1RA类或GIP/GLP-1RA等减重药物需在医生指导下规范使用，避免滥用，并强调“药物是辅助，生活方式是基础”的管理原则。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505208c2f0133f852441fb4d4d106035eec6b.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;常营社区卫生服务中心副院长吴旭芳&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;随后，常营社区卫生服务中心副院长吴旭芳等基层代表围绕“减重门诊建设难点”与“科室发展路径”展开讨论。吴旭芳表示，基层减重门诊的核心难点在于患者依从性不足，“管住嘴迈开腿”需长期坚持，社区需通过强化健康教育、建立随访机制提升患者参与度。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_20250520fe6e43ecb06d4c6d84c252f24fc8ab87.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;东风社区卫生服务中心全科副主任韩进超&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;东风社区卫生服务中心全科副主任韩进超认为，需转变居民“体重不是病”的观念，并通过多学科协作（如联合营养科、运动医学科）细化管理流程。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505204402ea4927fa49f39f46cec24c958667.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;高碑店社区卫生服务中心全科主任刘玉江&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;高碑店社区卫生服务中心全科主任刘玉江建议，结合属地政府资源，针对中青年男性肥胖群体开展专项干预，提升人群覆盖精准度。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_20250520542e630edc894d6580fc48339b878f48.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;常营社区卫生服务中心全科主任陈燕香&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;常营社区卫生服务中心全科主任陈燕香分享了慢病管理经验，通过“医生诊疗+家医助理信息化录入+定期随访”流程，将门诊时间更多用于健康指导，并借助动态血糖、肝脏弹性检测等设备提升诊疗精准度。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_202505209d4a425edec24543acd80f0213216427.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;三里屯社区卫生服务中心全科主任杨兴慧&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;三里屯社区卫生服务中心全科主任杨兴慧提出，减重门诊需建立“考核-激励”机制，通过专家下沉带教、绩效引导，提升全科医生参与积极性。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;img src=&amp;quot;http://www.xinhuanet.com/20250520/879b774de1304e019f60f7183420d61d/20250520e2cdf84300054a65b8c8aa2ba32a16f1_20250520efb396d895e8452f88bf11563e2c7689.jpg&amp;quot; alt=&amp;quot;&amp;quot; data-href=&amp;quot;&amp;quot; style=&amp;quot;height: auto !important;&amp;quot;&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: center;&amp;quot;&amp;gt;&amp;lt;span style=&amp;quot;color: rgb(53, 152, 219);&amp;quot;&amp;gt;十八里店社区卫生服务中心全科主任杜俊霞&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;十八里店社区卫生服务中心全科主任杜俊霞表示，要明确“患者是健康第一责任人”理念，通过分层随访（如重点人群电话随访）强化管理效能。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;会议通过领导致辞锚定方向、专家分享传递新知、基层经验碰撞火花，不仅搭建起优质医疗资源下沉的对接桥梁，更构建了“政策引导-学术支撑-基层实践”的立体化健康促进体系。与会者一致表示，将以此次会议为新起点，把前沿理念转化为服务动能，把交流成果落地为惠民举措，持续深耕基层健康素养提升的“最后一公里”。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-indent: 2em; text-align: justify;&amp;quot;&amp;gt;据悉，“健康素养 全民同行”系列活动还将在全国多地陆续开展，通过跨区域经验共享与协同创新，推动健康科普融入社区治理、疾病管理嵌入基层服务，让科学健康观惠及更多群众，为实现《全民健康素养提升三年行动》目标注入强劲动力。&amp;lt;/p&amp;gt;', 1765541286, 1766390588, '2025-12-12 20:08:06', '2025-12-12 20:08:06', 3, '新闻', 6),
                                                                                                                                                                                      (47, '提现说明', '', 'withdrawal_instructions', '提现说明', 1766752937, '&amp;lt;p&amp;gt;提现申请步骤：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 点击 “我的” 页面，选择 “提现”。在这里，您可以选择 “USDT 提现” 或者 “银行卡提现”。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 输入 “提现金额”（需可提余额）。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 选择 “提现地址” 或者 “银行卡账户”（请确认是否已添加提现地址或提现银行卡账户）。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4. 输入支付密码（支付密码为注册时设定），最后点击 “确认提现” 即可。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;提现到账时间说明：&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 提现申请时间为 9:30 - 21:30，节假日照常提现。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 最低提现金额为 200 元，提现时间 72 小时之内，具体到账时间取决于交易所网络或所属银行的规则。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 提现间隔时间为 72 小时。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;提现失败原因分析；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;1. 检查提现所添加的钱包是否有误；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;2. 确认银行开户行信息是否错误；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;3. 核实银行账号 / 户名是否错误，或是账号和户名不符；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;4. 查看是否绑定信用卡进行提现；&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;5. 确认银行账户是否冻结或正在办理挂失。&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;注：选择银行卡提现，将扣除提现金额 2% 的手续费。使用 USDT 进行提现，则无需扣除手续费，需仔细核实钱包地址是否准确无误。也可选择可提余额继续认购我们的产品，即可获得可提金额 2% 的复投奖励。&amp;lt;/p&amp;gt;', 1766752988, 1766752988, '2025-12-26 20:43:08', '2025-12-26 20:43:08', 4, '文章', 2),
                                                                                                                                                                                      (48, '充值流程', '', 'recharge_flow', '', 0, '&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;1.首先，进入“我的”页面，点击“充值”按钮。输入您要充值的金额，选择“TRC20 USDT 充值”。会出现钱包地址和一个二维码，这个钱包地址是您的一个充值专属地址，您可以使用欧易，TP钱包，币安等向您的专属充值地址转账，转帐成功后，充值的金额会自动充值到你的平台帐户，在您转帐请您仔细核对地址，因为钱包转帐是不可以撤回或者取消，请务必认真核对信息。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;2.转帐成功后，通常在 15分钟之内即可完成审核。此时，您可返回首页点击“我的”进行刷新，以便查收充值金额。&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;3.完成充值后，点击“项目”，再选择相应的专区，仔细阅读每个产品的项目详情，其中包括起购金额、购买等级、产品周期、产品收益以及限购份数等重要内容&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p style=&amp;quot;text-align: left;&amp;quot;&amp;gt;注：充值时间为全天24小时，最低充值金额为 50USDT，充值到账时间为转账成功后的15分钟之内，请您及时刷新进行查收。若充值未及时到账，请联系在线客服进行处理。&amp;lt;/p&amp;gt;', 1768456284, 1768456859, '2026-01-15 13:51:24', '2026-01-15 13:51:24', 4, '文章', 0);

-- 导出  表 facai11.article_class 结构
CREATE TABLE IF NOT EXISTS `article_class` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0新闻1公告2简介',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文章分类';

-- 正在导出表  facai11.article_class 的数据：~3 rows (大约)
DELETE FROM `article_class`;
INSERT INTO `article_class` (`id`, `title`, `type`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                (3, '新闻', 1, 1763620745, 1764404228, '2025-11-20 14:39:05', '2025-11-20 14:39:05'),
                                                                                                                (4, '文章', 1, 1763637432, 1763637432, '2025-11-20 19:17:12', '2025-11-20 19:17:12'),
                                                                                                                (5, '公告', 1, 1763793313, 1763793313, '2025-11-22 14:35:13', '2025-11-22 14:35:13');

-- 导出  表 facai11.bank 结构
CREATE TABLE IF NOT EXISTS `bank` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0开启1关闭',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='银行';

-- 正在导出表  facai11.bank 的数据：~0 rows (大约)
DELETE FROM `bank`;

-- 导出  表 facai11.banner 结构
CREATE TABLE IF NOT EXISTS `banner` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
    `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='横幅表';

-- 正在导出表  facai11.banner 的数据：~0 rows (大约)
DELETE FROM `banner`;

-- 导出  表 facai11.coupon 结构
CREATE TABLE IF NOT EXISTS `coupon` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `allow` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券限制',
    `amount` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '卡券金额',
    `rate` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '加息额度',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态0未使用1已使用',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '卡券0现金券1加息券',
    `expire_time` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
    `used_time` int(11) NOT NULL DEFAULT '0' COMMENT '使用时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='现金券表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.coupon 的数据：~0 rows (大约)
DELETE FROM `coupon`;

-- 导出  表 facai11.feedback 结构
CREATE TABLE IF NOT EXISTS `feedback` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `phone` int(11) NOT NULL DEFAULT '0' COMMENT '手机号码',
    `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户描述',
    `type` int(11) NOT NULL DEFAULT '0' COMMENT '场景',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0,正常1测试',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户反馈';

-- 正在导出表  facai11.feedback 的数据：~0 rows (大约)
DELETE FROM `feedback`;

-- 导出  表 facai11.goods 结构
CREATE TABLE IF NOT EXISTS `goods` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
    `class_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型id',
    `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '类型名称',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片',
    `content` text COMMENT '商品介绍',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品表';

-- 正在导出表  facai11.goods 的数据：~0 rows (大约)
DELETE FROM `goods`;

-- 导出  表 facai11.goods_class 结构
CREATE TABLE IF NOT EXISTS `goods_class` (
                                             `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品类型表';

-- 正在导出表  facai11.goods_class 的数据：~0 rows (大约)
DELETE FROM `goods_class`;

-- 导出  表 facai11.goods_log 结构
CREATE TABLE IF NOT EXISTS `goods_log` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机号',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品图片',
    `from_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '来源订单',
    `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
    `goods_title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品名称',
    `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
    `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `deliver_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发货名称',
    `deliver_order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发货单号',
    `deliver_time` int(11) NOT NULL DEFAULT '0' COMMENT '发货时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0待发货1已发货',
    `is_cash` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否折现0未折现1已折现',
    `address_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
    `address_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
    `address_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '市区',
    `address_place` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址',
    `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `phone` (`phone`),
    KEY `order_no` (`order_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品记录表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.goods_log 的数据：~0 rows (大约)
DELETE FROM `goods_log`;

-- 导出  表 facai11.group_buy 结构
CREATE TABLE IF NOT EXISTS `group_buy` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
    `class_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
    `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
    `is_recommend` int(11) NOT NULL DEFAULT '0' COMMENT '是否推荐0正常1推荐',
    `good_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
    `good_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品名称',
    `good_img` varchar(255) NOT NULL DEFAULT '' COMMENT '商品图片',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '拼团价格',
    `people` int(11) NOT NULL DEFAULT '0' COMMENT '拼团人数',
    `people_add` int(11) NOT NULL DEFAULT '0' COMMENT '每小时增加人数',
    `people_time` int(11) NOT NULL DEFAULT '0' COMMENT '人数更新时间',
    `process_time` int(11) NOT NULL DEFAULT '60' COMMENT '进度时间',
    `process_num` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT '进度更新速度',
    `process_update_time` int(11) NOT NULL DEFAULT '0' COMMENT '进度更新时间',
    `fail_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '拼团失败返现',
    `limit` int(11) NOT NULL DEFAULT '1' COMMENT '限购次数',
    `level` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级',
    `item_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '需要投资金额',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0隐藏1显示',
    `next_time` int(11) NOT NULL DEFAULT '0' COMMENT '开奖时间',
    `cycle` int(11) NOT NULL DEFAULT '1' COMMENT '开奖周期(天)',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '福利说明',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='拼团表';

-- 正在导出表  facai11.group_buy 的数据：~0 rows (大约)
DELETE FROM `group_buy`;

-- 导出  表 facai11.group_buy_class 结构
CREATE TABLE IF NOT EXISTS `group_buy_class` (
                                                 `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型',
    `create_time` int(11) NOT NULL DEFAULT '0',
    `update_time` int(11) NOT NULL DEFAULT '0',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='拼团类型';

-- 正在导出表  facai11.group_buy_class 的数据：~0 rows (大约)
DELETE FROM `group_buy_class`;

-- 导出  表 facai11.group_buy_order 结构
CREATE TABLE IF NOT EXISTS `group_buy_order` (
                                                 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `buy_id` int(11) NOT NULL DEFAULT '0' COMMENT '拼团id',
    `good_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
    `good_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品名称',
    `good_img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品图片',
    `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
    `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `fail_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '失败赠送金额',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0等待开奖1已中奖2未中奖',
    `settle_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '结算状态 0未中奖1中奖',
    `settle_time` int(11) NOT NULL DEFAULT '0' COMMENT '结算时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`),
    KEY `idx_status` (`uid`,`status`),
    KEY `idx_settle_time` (`uid`,`settle_time`),
    KEY `idx_buy_id` (`uid`,`buy_id`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='拼团订单'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.group_buy_order 的数据：~0 rows (大约)
DELETE FROM `group_buy_order`;

-- 导出  表 facai11.ip_white_list 结构
CREATE TABLE IF NOT EXISTS `ip_white_list` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip` varchar(30) NOT NULL DEFAULT '' COMMENT 'ip',
    `ip_address` varchar(100) NOT NULL DEFAULT '' COMMENT 'ip地址',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `ip` (`ip`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='IP白名单';

-- 正在导出表  facai11.ip_white_list 的数据：~0 rows (大约)
DELETE FROM `ip_white_list`;

-- 导出  表 facai11.item 结构
CREATE TABLE IF NOT EXISTS `item` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '项目名称',
    `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '项目标记',
    `content` text COMMENT '内容',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '项目图片',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '项目状态0开启1关闭2售罄',
    `boon` tinyint(4) NOT NULL DEFAULT '0' COMMENT '福利产品0否1是',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `video` varchar(255) NOT NULL DEFAULT '' COMMENT '视频地址',
    `class_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类',
    `class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
    `invest` int(11) NOT NULL DEFAULT '1' COMMENT '投资金额(元)',
    `invest_real` int(11) DEFAULT NULL COMMENT '打折金额(元)',
    `invest_scale` int(11) NOT NULL DEFAULT '0' COMMENT '投资规模(万)',
    `invest_limit` int(11) NOT NULL DEFAULT '0' COMMENT '投资限购次数',
    `invest_hold` int(11) NOT NULL DEFAULT '0' COMMENT '投资同时持有',
    `profit_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0每期反息到期返本,1一次性到期返本息,2每次返息不返本,3每期返息并且返回本金,4每期复利返息到期返本',
    `profit_rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '每期收益率(%)',
    `profit_multiple` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '收益率倍数',
    `profit_principal_multiple` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '本金倍数',
    `profit_cycle` int(11) NOT NULL DEFAULT '1' COMMENT '周期(天)',
    `profit_cycle_time` int(11) NOT NULL DEFAULT '86400' COMMENT '周期(天)或(小时)',
    `gift_points` int(11) NOT NULL DEFAULT '0' COMMENT '奖励积分',
    `gift_raffle` int(11) NOT NULL DEFAULT '0' COMMENT '奖励抽签',
    `gift_bonus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买奖励',
    `gift_cash_coupon` int(11) NOT NULL DEFAULT '0' COMMENT '奖励现金券',
    `gift_rate_coupon` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '奖励加息券',
    `gift_discount` varchar(200) NOT NULL DEFAULT '' COMMENT '打折百分比',
    `gift_reward` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '每月奖励金额',
    `gift_reward_time` int(11) NOT NULL DEFAULT '0' COMMENT '每月几号奖励',
    `gift_goods` int(11) NOT NULL DEFAULT '0' COMMENT '赠送实物产品',
    `gift_finish_item` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `gift_item` int(11) NOT NULL DEFAULT '0' COMMENT '买一送一产品',
    `gift_cycle` int(11) NOT NULL DEFAULT '0' COMMENT '每隔几期奖励',
    `gift_cycle_bonus` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '周期奖励金额',
    `gift_cycle_bonus_increase` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '周期递增奖励',
    `progress` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '项目整体进度',
    `progress_cycle` int(11) NOT NULL DEFAULT '60' COMMENT '项目进度更新周期',
    `progress_rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '项目进度更新速率',
    `progress_time` int(11) NOT NULL DEFAULT '0' COMMENT '项目进度更新时间',
    `limit_recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐0不推荐1推荐',
    `limit_vip_buy` int(11) NOT NULL DEFAULT '0' COMMENT '购买等级',
    `limit_newbie_buy` tinyint(4) NOT NULL DEFAULT '0' COMMENT '新人购买',
    `limit_vip_profit` tinyint(4) NOT NULL DEFAULT '0' COMMENT '等级收益加成0加成1不加',
    `limit_rebate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上级返利0返利1不返利',
    `limit_rate_coupon` tinyint(4) NOT NULL DEFAULT '0' COMMENT '使用加息券0不能使用1可以使用',
    `limit_cash_coupon` tinyint(4) NOT NULL DEFAULT '0' COMMENT '使用现金券0不能使用1可以使用',
    `limit_early_release` int(11) NOT NULL DEFAULT '0' COMMENT '本金提前几期释放',
    `limit_sign` int(11) NOT NULL DEFAULT '0' COMMENT '是否签名0否1是',
    `release_time` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
    `insurance_time` int(11) NOT NULL DEFAULT '0' COMMENT '投保时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='项目表';

-- 正在导出表  facai11.item 的数据：~7 rows (大约)
DELETE FROM `item`;
INSERT INTO `item` (`id`, `title`, `mark`, `content`, `desc`, `img`, `status`, `boon`, `sort`, `video`, `class_id`, `class_name`, `invest`, `invest_real`, `invest_scale`, `invest_limit`, `invest_hold`, `profit_type`, `profit_rate`, `profit_multiple`, `profit_principal_multiple`, `profit_cycle`, `profit_cycle_time`, `gift_points`, `gift_raffle`, `gift_bonus`, `gift_cash_coupon`, `gift_rate_coupon`, `gift_discount`, `gift_reward`, `gift_reward_time`, `gift_goods`, `gift_finish_item`, `gift_item`, `gift_cycle`, `gift_cycle_bonus`, `gift_cycle_bonus_increase`, `progress`, `progress_cycle`, `progress_rate`, `progress_time`, `limit_recommend`, `limit_vip_buy`, `limit_newbie_buy`, `limit_vip_profit`, `limit_rebate`, `limit_rate_coupon`, `limit_cash_coupon`, `limit_early_release`, `limit_sign`, `release_time`, `insurance_time`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (45, '无腥味小粒深海鱼油胶囊', '', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;无腥味小粒深海鱼油，omega-3不饱和脂肪酸的天然来源，帮助补充DHA和EPA&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;鱼油是人体所必需的脂肪酸欧米伽3的来源。本品有助于：&lt;br&gt;成人——每日1次，每次2粒，随餐服用或遵医嘱&lt;br&gt;2-12岁儿童——每日1粒，可以剪开胶囊，将鱼油挤入牛奶、饮料或者食物中食用。或遵医嘱&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;鱼油是人体所必需的脂肪酸欧米伽3的来源。本品有助于：&lt;br&gt;&lt;br&gt;支持心脑血管系统的健康&lt;br&gt;帮助保持正常的眼部和脑部功能&lt;br&gt;帮助健康人群维持血液中甘油三酯在正常水平&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/0661941fb576456d306f0687c577abb8.jpg', 0, 0, 0, '', 4, '水悦方生产一区', 8000, 8000, 2500, 1, 0, 0, 0.38, 1.00, 1.00, 130, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 73.76, 86400, 0.06, 1768534022, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1768271022, 1768271024, 1768271156, 1768462990, '2026-01-13 10:25:56', '2026-01-15 15:43:10'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (46, '孕妇黄金营养素', '', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 14px;&quot;&gt;BLACKMORES孕妇黄金营养素配方含有包括叶酸、碘、DHA和维生素B3、维生素D3、铁元素等20种母体及胎儿所需的关键营养素，剂量理想，不易引起孕吐&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;每粒BLACKMORES澳佳宝孕妇黄金营养素中含有：&lt;br&gt;&lt;br&gt; * 500微克叶酸：孕前一个月及孕期内服用，可有效预防胎儿大脑和脊柱缺陷；&lt;br&gt; *150微克碘：有益于胎儿脑部发育，有助于促进视力、听力和智力的综合发育；&lt;br&gt; * 铁元素：温和不易引起孕吐；&lt;br&gt; * 富含DHA的无腥味高浓度鱼油：有益于胎儿脑部、视力及神经系统的发育；&lt;br&gt; * 维生素B族：包含B1、B2、B5和B12，帮助胎儿吸收叶酸；&lt;br&gt; * 维生素D：促进胎儿骨骼和牙齿发育；&lt;br&gt; * 锌元素、维生素C和维生素D：促进胎儿免疫功能的发育&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/ed936e0ea140f738af842ebb2d9c520f.jpg', 0, 0, 0, '', 4, '水悦方生产一区', 16500, 16500, 3000, 1, 0, 0, 0.40, 1.00, 1.00, 95, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 72.24, 86400, 0.07, 1768534502, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1768271373, 1768271374, 1768271455, 1768463062, '2026-01-13 10:30:55', '2026-01-15 15:44:22'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (47, '维骨力关节灵1500mg', '', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 14px;&quot;&gt;BLACKMORES澳佳宝维骨力关节灵，每粒含1500毫克氨糖，每天只需服用一次。其独特的硫酸氨基葡萄糖氯化钠复合物配方，已被科学证明是氨基葡萄糖的最佳存在形式。该产品可帮助缓解由骨关节炎引起的关节疼痛。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;人体自然产生的氨基葡萄糖，是软骨重要的组成部分，对于维持正常的关节功能起到重要作用。&lt;br&gt;&lt;br&gt;补充氨基葡萄糖有助于：&lt;br&gt;&lt;br&gt;减轻由关节炎引起的关节疼痛；&lt;br&gt;增强关节的灵活性和扭转力，缓解因关节炎引起的关节僵硬症状；&lt;br&gt;保护软骨，使其支持正常的关节功能。&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/a2daa3626703eeb96e6064a3cabfe7fd.jpg', 0, 0, 0, '', 3, '水悦方生产二区', 31000, 31000, 3500, 1, 0, 0, 0.42, 1.00, 1.00, 380, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 71.95, 86400, 0.03, 1768534862, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1768271867, 1768271869, 1768271928, 1768463396, '2026-01-13 10:38:48', '2026-01-15 15:49:56'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (48, '每日益生菌', '', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;每粒含300亿活菌单位，5种经临床验证的益生菌菌种和益生元，可有效缓解胀气、打嗝等消化不良状况&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;恢复消化道菌群平衡，维护消化道健康&lt;br&gt;减少如肠胃气胀等肠胃不适&lt;br&gt;增强免疫力&lt;br&gt;维护人体全面健康&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;300亿活菌单位数粒+5种临床验证功效的益生菌菌种+益生元&lt;br&gt;-临床证明可以有效缓解胀气、打嗝等消化不良&lt;br&gt;-采用微胶囊包埋技术及特殊工艺，无需冷藏及防止受潮，并提升吸收利用&lt;br&gt;-成人、儿童均可食用，也适合妊娠期和哺乳期及素食者人群&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/3eab7ecf0a5f424d6a291bcd848598f5.jpg', 0, 0, 0, '', 3, '水悦方生产二区', 70000, 70000, 4000, 1, 0, 0, 0.44, 1.00, 1.00, 250, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 70.51, 86400, 0.05, 1768532462, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1768273084, 1768273086, 1768273158, 1768273700, '2026-01-13 10:59:18', '2026-01-15 11:01:02'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (49, '月见草精华', '', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;BLACKMORES澳佳宝冷压月见草油含有一种天然的重要Ω-6系不饱和脂肪酸，即γ-亚麻酸。患有经前综合症（PMS）的成人及患湿疹的儿童体内均被发现γ-亚麻酸的水平较低.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;月见草油中含有Ω-6系人体必需的不饱和脂肪酸γ-亚麻酸（GLA），有助于改善经前综合征（PMS）和湿疹等皮肤炎症。&lt;br&gt;&lt;br&gt;过量摄入饱和脂肪、长期处于精神高压状态、饮酒和吸烟等不健康的生活方式会减少日常饮食中对于GLA的摄入和吸收，因此需要额外补充。&lt;br&gt;&lt;br&gt;本产品适用于有经期、经前不适应症状的女性，和皮肤干燥、状况欠佳的人群。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;采用先进的冷压萃取技术来获取真正的月见草精华，相对于传统精炼法保留了更高的生物活性。严苛的质量检测保证产品精纯的品质。&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/7c196533c0a218971612f33d0f4b0fdd.jpg', 0, 0, 0, '', 5, '水悦方生产三区', 111800, 111800, 4300, 1, 0, 0, 0.46, 1.00, 1.00, 570, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 70.23, 86400, 0.03, 1768532642, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1768273297, 1768273299, 1768273342, 1768463431, '2026-01-13 11:02:22', '2026-01-15 15:50:31'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (50, '叶黄素精华护眼片', 'V1专享', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;BLACKMORES澳佳宝叶黄素精华护眼片配方中含有叶黄素和玉米黄质等抗氧化成分，帮助抵抗自由基对于黄斑区的侵害&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;叶黄素和玉米黄质是黄斑区的主要成分， 对维护眼部健康至关重要，身体自身是不能合成叶黄素和玉米黄质的，必需通过外界摄入补充。&lt;br&gt;澳佳宝叶黄素护眼片可以：&lt;br&gt;帮助维护黄斑区健康&lt;br&gt;通过抵御自由基氧化伤害，保护黄斑区健康&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;澳佳宝叶黄素护眼片中同时含有叶黄素和玉米黄质，支持眼部健康&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/c69687ae6308e0046e58c00c2ee9ad4b.jpg', 0, 0, 0, '', 6, '产品代理VIP专区', 38000, 38000, 2600, 1, 0, 1, 0.52, 1.00, 1.00, 35, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 70.78, 86400, 0.05, 1768532820, 0, 2, 0, 0, 0, 0, 0, 0, 0, 1768273479, 1768273480, 1768273526, 1768463448, '2026-01-13 11:05:26', '2026-01-15 15:50:49'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (51, '复合B族焕能配方', 'V2专享', '&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;BLACKMORES澳佳宝复合B族焕能配方含有高浓度的复合B族维生素，帮助在高强度身体活动后补给营养，支持保持细胞产能。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;B族维生素是一种常见的水溶性维生素，在碳水化合物、蛋白质和脂肪代谢中发挥着重要作用，是促进食物转化为能量的重要辅酶。&lt;br&gt;&lt;br&gt;BLACKMORES澳佳宝复合B族焕能配方可对持续出于高强度活动的身体提供营养补给，参与荷尔蒙和神经递质的生产，支持神经系统的功能。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;color: rgb(50, 70, 73); background-color: rgb(255, 255, 255); font-size: 15px;&quot;&gt;独特的多种维生素高效复合配方，帮助支持细胞产能&lt;br&gt;为从事高强度身体活动人群，或体力消耗过大人群提供丰富的B族维生素&lt;/span&gt;&lt;/p&gt;', '澳佳宝', '/storage/20260113/7cefcaa52f2ba9f902eab27866369ab5.jpg', 0, 0, 0, '', 6, '产品代理VIP专区', 86000, 86000, 4800, 1, 0, 1, 0.68, 1.00, 1.00, 58, 86400, 0, 0, 0.00, 0, 0.00, '0', 0.00, 0, 0, 0.00, 0, 0, 0.00, 0.00, 71.78, 86400, 0.05, 1768532882, 0, 3, 0, 0, 0, 0, 0, 0, 0, 1768273626, 1768273628, 1768273660, 1768463461, '2026-01-13 11:07:41', '2026-01-15 15:51:01');

-- 导出  表 facai11.item_class 结构
CREATE TABLE IF NOT EXISTS `item_class` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='项目类型表';

-- 正在导出表  facai11.item_class 的数据：~5 rows (大约)
DELETE FROM `item_class`;
INSERT INTO `item_class` (`id`, `title`, `desc`, `sort`, `pid`, `img`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                   (3, '水悦方生产二区', 'Shuiyuefang Production Line Zone 2', 2, 0, '/storage/20251210/6ed41b2f248a5791a3d6b125dbf4483a.png', 1763627142, 1765358100, '2025-11-20 16:25:42', '2025-11-20 16:25:42'),
                                                                                                                                   (4, '水悦方生产一区', 'Shuiyuefang Production Line Zone 1', 1, 0, '/storage/20251210/96993dcf5f06862a6c76c7ec0c9366da.png', 1763646868, 1765358094, '2025-11-20 21:54:28', '2025-11-20 21:54:28'),
                                                                                                                                   (5, '水悦方生产三区', 'Shuiyuefang Production Line Zone 3', 3, 0, '/storage/20251210/2845ad9820f8d20676f08d996d793f5e.png', 1764160328, 1765358089, '2025-11-26 20:32:08', '2025-11-26 20:32:08'),
                                                                                                                                   (6, '产品代理VIP专区', 'VIP Zone product Agency', 4, 0, '/storage/20251210/719a369a92ae0d354f928da10eb76c4a.png', 1764160389, 1765358083, '2025-11-26 20:33:09', '2025-11-26 20:33:09'),
                                                                                                                                   (7, '品牌推广特惠专区', 'Brand Promotion Special Offer Zone', 5, 0, '/storage/20251210/ffa2de903ec682837cfab5745727e2dd.png', 1764160406, 1765358078, '2025-11-26 20:33:26', '2025-11-26 20:33:26');

-- 导出  表 facai11.item_log 结构
CREATE TABLE IF NOT EXISTS `item_log` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '用户名称',
    `phone` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0,正常1测试',
    `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
    `order_no` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '订单号',
    `order_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '订单名称',
    `cycle_start` int(11) NOT NULL DEFAULT '0' COMMENT '已开始周期',
    `cycle_end` int(11) NOT NULL DEFAULT '0' COMMENT '全部周期',
    `profit_rate` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '每日收益(%)',
    `profit_multiple` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '收益倍率加成',
    `profit_extra` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户收益等级加成',
    `profit_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '每次收益',
    `profit_now` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '收益金额(元)',
    `profit_total` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '总收益金额(元)',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    KEY `idx_uid` (`uid`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_order_no` (`order_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='项目结算记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.item_log 的数据：~0 rows (大约)
DELETE FROM `item_log`;

-- 导出  表 facai11.item_order 结构
CREATE TABLE IF NOT EXISTS `item_order` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_no` varchar(30) NOT NULL DEFAULT '' COMMENT '订单号',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '0' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常用户,1测试账号',
    `item_class_id` int(11) NOT NULL DEFAULT '0' COMMENT '类别id',
    `item_class_name` varchar(50) NOT NULL DEFAULT '' COMMENT '类别名称',
    `item_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目ID',
    `item_name` varchar(200) NOT NULL DEFAULT '' COMMENT '项目名称',
    `item_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0进行中1已结束2待结算',
    `item_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目类型 0.每日返息,1到期返本',
    `amount` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '投资金额',
    `amount_real` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '实际投资金额',
    `quantity` int(11) NOT NULL DEFAULT '1' COMMENT '购买数量',
    `cycle_start` int(11) NOT NULL DEFAULT '0' COMMENT '已开始周期',
    `cycle_end` int(11) NOT NULL DEFAULT '0' COMMENT '全部周期',
    `cycle_time` int(11) NOT NULL DEFAULT '86400' COMMENT '周期的时间',
    `profit_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '每日收益(%)',
    `profit_multiple` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '收益倍数加成',
    `profit_principal_multiple` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '本金倍数加成',
    `profit_extra` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '当前收益等级加成',
    `profit_now` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '当前收益金额（元）',
    `profit_earn` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '每次收益金额（元）',
    `profit_total` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '预计总收益金额（元）',
    `next_time` int(11) NOT NULL DEFAULT '0' COMMENT '下次结算时间',
    `last_time` int(11) NOT NULL DEFAULT '0' COMMENT '上次执行时间',
    `end_time` datetime NOT NULL COMMENT '结束时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `gift_raffle` int(11) NOT NULL DEFAULT '0' COMMENT '项目抽奖奖励',
    `gift_points` int(11) NOT NULL DEFAULT '0' COMMENT '项目积分奖励',
    `gift_bonus` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '项目购买奖励',
    `gift_goods` int(11) NOT NULL DEFAULT '0' COMMENT '项目商品奖励',
    `gift_discount` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '打折百分比',
    `gift_cash_coupon` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '项目现金券奖励',
    `used_cash_coupon` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '使用的现金券金额',
    `used_rate_coupon` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '使用的加息券利率',
    `gift_reward` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '每月奖励金额',
    `gift_reward_time` int(11) NOT NULL DEFAULT '0' COMMENT '每月几号奖励',
    `gift_rate_coupon` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '项目加息券奖励',
    `gift_finish_item` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '项目到期赠送现金',
    `gift_item` int(11) NOT NULL DEFAULT '0' COMMENT '买一送一产品',
    `release_status` int(11) NOT NULL DEFAULT '0' COMMENT '本金释放状态0无释放权限1已释放2待释放',
    `early_release` int(11) NOT NULL DEFAULT '0' COMMENT '本金提前几期释放',
    `early_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '本金释放时间',
    `limit_rebate` int(11) NOT NULL DEFAULT '0' COMMENT '上级返利0返利1不返利',
    `sign_img` varchar(255) NOT NULL DEFAULT '' COMMENT '用户签名',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    KEY `idx_order_no` (`order_no`),
    KEY `idx_item_id` (`item_id`),
    KEY `idx_username` (`username`),
    KEY `idx_phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='项目订单表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.item_order 的数据：~0 rows (大约)
DELETE FROM `item_order`;

-- 导出  表 facai11.level 结构
CREATE TABLE IF NOT EXISTS `level` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
    `extra` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '收益加成',
    `yuebao_extra` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益加成',
    `member` int(11) NOT NULL DEFAULT '0' COMMENT '直推有效',
    `lv1_invite` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '一级返利',
    `lv2_invite` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '二级返利',
    `sign_in` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '签到金额',
    `ruffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '每日抽奖次数',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户等级表';

-- 正在导出表  facai11.level 的数据：~7 rows (大约)
DELETE FROM `level`;
INSERT INTO `level` (`id`, `title`, `extra`, `yuebao_extra`, `member`, `lv1_invite`, `lv2_invite`, `sign_in`, `ruffle_num`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                                                        (1, 'V0', 0.00, 0.01, 0, 3.00, 2.00, '1~2', 1, 1764407785, 1765433898, '2025-11-29 17:16:25', '2025-12-11 14:18:18'),
                                                                                                                                                                                        (2, 'V1', 0.05, 0.02, 5, 6.00, 5.00, '3~5', 2, 0, 1765274069, '2025-11-21 08:36:39', '2025-12-09 19:22:45'),
                                                                                                                                                                                        (3, 'V2', 0.10, 0.03, 15, 8.00, 7.00, '3~10', 3, 1763626789, 1765274088, '2025-11-20 16:19:49', '2025-12-09 19:22:49'),
                                                                                                                                                                                        (4, 'V3', 0.20, 0.05, 30, 10.00, 9.00, '5~20', 5, 0, 1765274108, '2025-11-21 08:36:59', '2025-12-09 19:23:08'),
                                                                                                                                                                                        (5, 'V4', 0.30, 0.08, 80, 15.00, 14.00, '5~30', 8, 0, 1765274140, '2025-11-21 08:37:06', '2025-12-09 19:23:13'),
                                                                                                                                                                                        (6, 'V5', 0.40, 0.12, 120, 23.00, 22.00, '10~50', 12, 0, 1765274142, '2025-11-21 08:37:18', '2025-12-09 19:23:17'),
                                                                                                                                                                                        (7, 'V6', 0.50, 0.18, 180, 33.00, 32.00, '20~100', 18, 0, 1765274133, '2025-11-21 08:37:23', '2025-12-09 19:23:21');

-- 导出  表 facai11.level_log 结构
CREATE TABLE IF NOT EXISTS `level_log` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `lv_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级id',
    `lv_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
    `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '等级描述',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0自己1下级',
    `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
    `admin_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '管理员名称',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`),
    KEY `idx_username` (`uid`,`username`),
    KEY `idx_phone` (`uid`,`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户升级记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.level_log 的数据：~0 rows (大约)
DELETE FROM `level_log`;

-- 导出  表 facai11.message 结构
CREATE TABLE IF NOT EXISTS `message` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `is_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读0未读1已读',
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
    `create_time` int(11) NOT NULL DEFAULT '0',
    `update_time` int(11) NOT NULL DEFAULT '0',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='站内信'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.message 的数据：~0 rows (大约)
DELETE FROM `message`;

-- 导出  表 facai11.money_class 结构
CREATE TABLE IF NOT EXISTS `money_class` (
                                             `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '账变id',
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '账变名称',
    `style` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账变0,加1减',
    `top_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='账变类型表';

-- 正在导出表  facai11.money_class 的数据：~36 rows (大约)
DELETE FROM `money_class`;
INSERT INTO `money_class` (`id`, `title`, `style`, `top_id`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                         (1, '充值', 2, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-18 14:43:05'),
                                                                                                                         (2, '提现', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (3, '抽奖', 2, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-21 17:53:30'),
                                                                                                                         (4, '购买', 4, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-17 22:12:52'),
                                                                                                                         (5, '收益', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (6, '签到', 2, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-21 17:53:30'),
                                                                                                                         (7, '一级返佣', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (8, '二级返佣', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (9, '三级返佣', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (10, '提现拒绝', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (11, '返回本金', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (12, '积分增加', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (13, '积分减少', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (14, '项目购买奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (15, '抽奖增加', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (16, '抽奖减少', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (17, '首存奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (18, '邀请奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (19, '余额宝存', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-12 14:19:49'),
                                                                                                                         (20, '余额宝取', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-12-12 14:19:52'),
                                                                                                                         (21, '转账进', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (22, '转账出', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (23, '注册赠送', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (24, 'USDT充值奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (25, '人民币充值奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (26, '团长升级奖励', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (27, '赠送金额', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (28, '充值返利一级', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (29, '充值返利二级', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (30, '充值返利三级', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (31, '可用金额增加', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (32, '可用金额减少', 1, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (33, '金额增加', 2, 0, 0, 0, '2025-11-26 04:55:45', '2026-01-15 08:31:40'),
                                                                                                                         (34, '金额减少', 3, 0, 0, 0, '2025-11-26 04:55:45', '2026-01-15 08:31:46'),
                                                                                                                         (35, '到期赠送现金', 0, 0, 0, 0, '2025-11-26 04:55:45', '2025-11-26 04:55:45'),
                                                                                                                         (37, '复投奖励', 0, 0, 1767512972, 1767512972, '2026-01-04 15:49:32', '2026-01-04 15:49:32');

-- 导出  表 facai11.money_log 结构
CREATE TABLE IF NOT EXISTS `money_log` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户手机',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `order_id` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '订单id',
    `class_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账变id',
    `class_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '账变名称',
    `amount` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `before` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '变化前余额',
    `after` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '变化后余额',
    `desc` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '账变详情',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    KEY `idx_create_time` (`create_time`),
    KEY `idx_class_create` (`class_id`,`create_time`),
    KEY `order_id` (`order_id`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='账变日志表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.money_log 的数据：~0 rows (大约)
DELETE FROM `money_log`;
INSERT INTO `money_log` (`id`, `uid`, `username`, `phone`, `is_test`, `order_id`, `class_id`, `class_name`, `amount`, `before`, `after`, `desc`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                                                                             (1, 4, '17777777777', '17777777777', 0, 'register_4', 33, '金额增加', 88.00, 0.00, 88.00, '注册赠送金额:获得88', 1768484436, 1768484436, '2026-01-15 21:40:36', '2026-01-15 21:40:36'),
                                                                                                                                                                                                             (2, 5, '13777777777', '13777777777', 0, 'register_5', 33, '金额增加', 88.00, 0.00, 88.00, '注册赠送金额:获得88', 1768484765, 1768484765, '2026-01-15 21:46:05', '2026-01-15 21:46:05');


-- 导出  表 facai11.payment 结构
CREATE TABLE IF NOT EXISTS `payment` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0,正常1测试',
    `is_voice` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否静音0,正常1静音',
    `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0成功1审核2失败',
    `channel_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '渠道id',
    `channel_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道名称',
    `class_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道类型ID',
    `class_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '渠道类型',
    `class_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道类型',
    `account_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的账号id',
    `account_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账户名称',
    `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
    `account` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '付款地址',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付图片',
    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付用户名称',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `amount_real` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际金额',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `passage_time` int(11) NOT NULL DEFAULT '0' COMMENT '通过时间',
    `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
    `style` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0框架,1跳转,2内置账号',
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`),
    KEY `idx_order_no` (`uid`,`order_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.payment 的数据：~0 rows (大约)
DELETE FROM `payment`;

-- 导出  表 facai11.payment_account 结构
CREATE TABLE IF NOT EXISTS `payment_account` (
                                                 `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0开启1关闭',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1银行卡2数字货币3支付宝4微信',
    `show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0账号1图片2图片加账号',
    `bank_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '银行名称',
    `bank_owner` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '银行归属人',
    `bank_branch` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '银行支行',
    `bank_account` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '银行账号',
    `coin_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '币名称',
    `coin_blockchain` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '币区块链',
    `coin_account` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '币账号',
    `alipay_account` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '支付宝账号',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '收款码',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '备注',
    `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
    `admin_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '管理id',
    `admin_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理名称',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付账号';

-- 正在导出表  facai11.payment_account 的数据：~2 rows (大约)
DELETE FROM `payment_account`;
INSERT INTO `payment_account` (`id`, `title`, `status`, `type`, `show`, `bank_name`, `bank_owner`, `bank_branch`, `bank_account`, `coin_name`, `coin_blockchain`, `coin_account`, `alipay_account`, `img`, `remark`, `rate`, `admin_id`, `admin_name`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                                                                                                                                                                                   (1, 'USDT', 0, 2, 0, ' ', ' ', ' ', ' ', 'USDT', 'trc20', 'dsfsdf23423423sdfs', ' ', '/storage/20251218/40d85b54b625f2c932adf951b7eefdd5.jpg', 'USDT', 7.09, 0, '', 0, 1768457160, '2025-12-18 13:02:51', '2026-01-15 14:06:00'),
                                                                                                                                                                                                                                                                                                                   (2, '银行卡', 0, 1, 0, '中国农业银行', '小红', '长沙分行', '234234234234234234', 'USDT', ' ', ' ', ' ', ' ', '银行卡', 1.00, 0, '', 0, 0, '2025-12-18 13:03:35', '2025-12-18 13:03:35');

-- 导出  表 facai11.payment_channel 结构
CREATE TABLE IF NOT EXISTS `payment_channel` (
                                                 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
    `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '通道代码',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态0正常1关闭',
    `upper` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '上游代码',
    `upper_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上游id',
    `upper_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
    `upper_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上游状态0正常1关闭',
    `upper_img` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '上游图片',
    `class_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型id',
    `class_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
    `class_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型的分类',
    `class_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型状态0正常1关闭',
    `class_img` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
    `class_rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '类型汇率',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小充值',
    `max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最大充值',
    `style` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0框架,1跳转,2内置账号',
    `accounts` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '绑定支付账号1,2,3',
    `accounts_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '绑定支付账号名称',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付通道';

-- 正在导出表  facai11.payment_channel 的数据：~2 rows (大约)
DELETE FROM `payment_channel`;
INSERT INTO `payment_channel` (`id`, `title`, `code`, `status`, `upper`, `upper_id`, `upper_name`, `upper_status`, `upper_img`, `class_id`, `class_name`, `class_type`, `class_status`, `class_img`, `class_rate`, `create_time`, `update_time`, `create_at`, `update_at`, `min`, `max`, `style`, `accounts`, `accounts_name`, `sort`, `desc`) VALUES
                                                                                                                                                                                                                                                                                                                                                   (6, '银联支付', 'asdas', 1, '1234', 4, '', 0, '', 2, '银行卡', 1, 0, '/storage/20251210/c214df7e839136c50229a514e0ab03f2.jpeg', 1.00, 1762500172, 1766034235, '2025-11-07 15:22:52', '2025-12-18 13:03:55', 22.00, 12321.00, 2, '2', '银行卡', 2, 'asdsa'),
                                                                                                                                                                                                                                                                                                                                                   (8, 'USDT', '195', 0, '123456', 3, '', 0, '', 2, '', 1, 0, '', 1.00, 1762500172, 1767752635, '2025-11-07 15:22:52', '2026-01-07 10:23:55', 10.00, 999999.00, 2, '1', 'USDT', 2, 'USDT充值方式不设置账号，每个用户都有专属的U盾钱包充值地址');

-- 导出  表 facai11.payment_class 结构
CREATE TABLE IF NOT EXISTS `payment_class` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
    `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型图标',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '支付类型',
    `rate` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态0,正常1关闭',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付类型';

-- 正在导出表  facai11.payment_class 的数据：~2 rows (大约)
DELETE FROM `payment_class`;
INSERT INTO `payment_class` (`id`, `title`, `img`, `type`, `rate`, `status`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                         (5, 'USDT', '/storage/20251210/44773d6e6f0d0f070befb60423806a10.png', 2, 7.09, 0, 0, 1768457160, '2025-12-10 16:56:51', '2026-01-15 14:06:00'),
                                                                                                                                         (6, '银行卡', '/storage/20251210/c214df7e839136c50229a514e0ab03f2.jpeg', 1, 1.00, 0, 0, 0, '2025-12-10 16:57:13', '2025-12-10 17:13:25');

-- 导出  表 facai11.payment_upper 结构
CREATE TABLE IF NOT EXISTS `payment_upper` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
    `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '上游代号',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态0正常1关闭',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付上游';

-- 正在导出表  facai11.payment_upper 的数据：~2 rows (大约)
DELETE FROM `payment_upper`;
INSERT INTO `payment_upper` (`id`, `title`, `code`, `status`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                          (3, 'USDT', '123456', 0, 0, 0, '2025-11-07 10:50:49', '2025-11-07 10:50:49'),
                                                                                                                          (4, '银联转账', '1234', 0, 0, 0, '2025-11-20 16:30:13', '2025-11-20 16:30:13');

-- 导出  表 facai11.question 结构
CREATE TABLE IF NOT EXISTS `question` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `desc` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `create_time` int(11) NOT NULL DEFAULT '0',
    `update_time` int(11) NOT NULL DEFAULT '0',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问题列表';

-- 正在导出表  facai11.question 的数据：~0 rows (大约)
DELETE FROM `question`;

-- 导出  表 facai11.raffle 结构
CREATE TABLE IF NOT EXISTS `raffle` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(50) NOT NULL COMMENT '标题',
    `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `chance` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '概率',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0金额1积分',
    `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
    `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
    `goods_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品标题',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='抽奖表';

-- 正在导出表  facai11.raffle 的数据：~8 rows (大约)
DELETE FROM `raffle`;
INSERT INTO `raffle` (`id`, `title`, `money`, `chance`, `type`, `img`, `goods_id`, `goods_name`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                             (7, '谢谢参与', 0.00, 27.00, 0, '/storage/20251210/8d025ad7e232508672f65994de62f305.png', 0, '', 0, 1765360383, '2025-11-21 15:50:27', '2025-11-21 15:50:27'),
                                                                                                                                                             (8, '一等奖', 1000.00, 0.00, 0, '/storage/20251210/3e6369d72944fa44e26c0c683e35967c.png', 0, '', 0, 1767575307, '2025-11-21 15:50:47', '2025-11-21 15:50:47'),
                                                                                                                                                             (9, '二等奖', 500.00, 1.00, 0, '/storage/20251210/6a45a5c314feb17e1e26dbe3f75fc989.png', 0, '', 0, 1767575295, '2025-11-21 15:51:02', '2025-11-21 15:51:02'),
                                                                                                                                                             (10, '三等奖', 100.00, 3.00, 0, '/storage/20251210/bb4b1c1cf6dea1f08967cfce078722df.png', 0, '', 0, 1767575377, '2025-11-21 15:51:23', '2025-11-21 15:51:23'),
                                                                                                                                                             (11, '四等奖', 50.00, 8.00, 0, '/storage/20251210/54cbd6f9533ca49719eb92bc1b43fdd3.png', 0, '', 0, 1765360366, '2025-11-21 15:51:39', '2025-11-21 15:51:39'),
                                                                                                                                                             (12, '五等奖', 10.00, 12.00, 0, '/storage/20251210/856e94c004b3d5449bd88697eaac316d.png', 0, '', 0, 1765360357, '2025-11-21 15:51:55', '2025-11-21 15:51:55'),
                                                                                                                                                             (13, '六等奖', 5.00, 15.00, 0, '/storage/20251210/df7cd009a149f2b1cc56040d1b36bc0c.png', 0, '', 0, 1765360348, '2025-11-21 15:52:11', '2025-11-21 15:52:11'),
                                                                                                                                                             (14, '七等奖', 1.00, 30.00, 0, '/storage/20251210/5f3f7c432be6ae6bba5f5b7098da91c4.png', 0, '', 0, 1765360340, '2025-11-21 15:52:40', '2025-11-21 15:52:40');

-- 导出  表 facai11.raffle_log 结构
CREATE TABLE IF NOT EXISTS `raffle_log` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型0金额1积分',
    `raffle_id` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖id',
    `raffle_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '抽奖产品名称',
    `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
    `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='抽奖记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.raffle_log 的数据：~0 rows (大约)
DELETE FROM `raffle_log`;

-- 导出  表 facai11.real_name 结构
CREATE TABLE IF NOT EXISTS `real_name` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `sfz_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证名称',
    `sfz_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证id',
    `sfz_front` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证正面图片',
    `sfz_back` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证反面图片',
    `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0通过1审核,2拒绝',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `bank_account` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '银行账号',
    `bank_branch` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '银行支行',
    `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '银行名称',
    PRIMARY KEY (`id`,`uid`),
    KEY `idx_uid` (`uid`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='实名记录表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.real_name 的数据：~0 rows (大约)
DELETE FROM `real_name`;
INSERT INTO `real_name` (`id`, `uid`, `username`, `phone`, `is_test`, `sfz_name`, `sfz_number`, `sfz_front`, `sfz_back`, `status`, `create_time`, `update_time`, `create_at`, `update_at`, `bank_account`, `bank_branch`, `bank_name`) VALUES
    (1, 1, '', '13888888888', 0, '', '', '', '', 0, 1768481661, 1768481796, '2026-01-15 20:54:21', '2026-01-15 20:56:36', '', '', '');

-- 导出  表 facai11.report_all 结构
CREATE TABLE IF NOT EXISTS `report_all` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `bonus_reinvest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '复投奖励',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    UNIQUE KEY `idx_uid_period` (`uid`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户总报统计'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.report_all 的数据：~0 rows (大约)
DELETE FROM `report_all`;
INSERT INTO `report_all` (`id`, `uid`, `yuebao_earn`, `yuebao_num`, `yuebao_deposit`, `yuebao_withdrawal`, `recharge`, `recharge_real`, `recharge_rmb`, `recharge_usdt`, `recharge_num`, `withdraw`, `withdraw_real`, `withdraw_rmb`, `withdraw_usdt`, `withdraw_reject`, `withdraw_num`, `signin_num`, `signin_money`, `signin_points`, `raffle_num`, `raffle_points`, `raffle_money`, `team_item`, `team_item_v1`, `team_item_v2`, `team_item_v3`, `team_invite`, `team_invite_v1`, `team_invite_v2`, `team_invite_v3`, `team_recharge`, `team_recharge_v1`, `team_recharge_v2`, `team_recharge_v3`, `team_user`, `income`, `income_unreturned`, `invest`, `invest_num`, `invest_principal`, `points`, `points_del`, `raffle`, `raffle_del`, `transfer_in`, `transfer_out`, `bonus_first_deposit`, `bonus_invite`, `bonus_register`, `bonus_usdt_recharge`, `bonus_rmb_recharge`, `bonus_team_up`, `bonus_gift`, `bonus_item_finish`, `bonus_reinvest`, `amount_available`, `amount_available_del`, `amount_frozen`, `amount_frozen_del`, `update_time`, `update_at`, `month_reward`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (1, 4, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484436, '2026-01-15 21:40:36', 0),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            (2, 5, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484765, '2026-01-15 21:46:05', 0);

-- 导出  表 facai11.report_all_backup 结构
CREATE TABLE IF NOT EXISTS `report_all_backup` (
                                                   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    UNIQUE KEY `idx_uid_period` (`uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户总报统计'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.report_all_backup 的数据：~0 rows (大约)
DELETE FROM `report_all_backup`;

-- 导出  表 facai11.report_day 结构
CREATE TABLE IF NOT EXISTS `report_day` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `period` date NOT NULL COMMENT '统计日期 YYYY-MM-DD',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `bonus_reinvest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '复投奖励',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`,`uid`,`period`),
    UNIQUE KEY `idx_uid_period` (`uid`,`period`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户日报统计'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.report_day 的数据：~0 rows (大约)
DELETE FROM `report_day`;
INSERT INTO `report_day` (`id`, `uid`, `period`, `yuebao_earn`, `yuebao_num`, `yuebao_deposit`, `yuebao_withdrawal`, `recharge`, `recharge_real`, `recharge_rmb`, `recharge_usdt`, `recharge_num`, `withdraw`, `withdraw_real`, `withdraw_rmb`, `withdraw_usdt`, `withdraw_reject`, `withdraw_num`, `signin_num`, `signin_money`, `signin_points`, `raffle_num`, `raffle_points`, `raffle_money`, `team_item`, `team_item_v1`, `team_item_v2`, `team_item_v3`, `team_invite`, `team_invite_v1`, `team_invite_v2`, `team_invite_v3`, `team_recharge`, `team_recharge_v1`, `team_recharge_v2`, `team_recharge_v3`, `team_user`, `income`, `income_unreturned`, `invest`, `invest_num`, `invest_principal`, `points`, `points_del`, `raffle`, `raffle_del`, `transfer_in`, `transfer_out`, `bonus_first_deposit`, `bonus_invite`, `bonus_register`, `bonus_usdt_recharge`, `bonus_rmb_recharge`, `bonus_team_up`, `bonus_gift`, `bonus_item_finish`, `bonus_reinvest`, `amount_available`, `amount_available_del`, `amount_frozen`, `amount_frozen_del`, `update_time`, `update_at`, `month_reward`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      (1, 4, '2026-01-15', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484436, '2026-01-15 21:40:36', 0),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      (2, 5, '2026-01-15', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484765, '2026-01-15 21:46:05', 0);

-- 导出  表 facai11.report_market 结构
CREATE TABLE IF NOT EXISTS `report_market` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `period` date NOT NULL COMMENT '统计日期 YYYY-MM-DD',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `period` (`period`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户日报统计';

-- 正在导出表  facai11.report_market 的数据：~0 rows (大约)
DELETE FROM `report_market`;
INSERT INTO `report_market` (`id`, `period`, `yuebao_earn`, `yuebao_num`, `yuebao_deposit`, `yuebao_withdrawal`, `recharge`, `recharge_real`, `recharge_rmb`, `recharge_usdt`, `recharge_num`, `withdraw`, `withdraw_real`, `withdraw_rmb`, `withdraw_usdt`, `withdraw_reject`, `withdraw_num`, `signin_num`, `signin_money`, `signin_points`, `raffle_num`, `raffle_points`, `raffle_money`, `team_item`, `team_item_v1`, `team_item_v2`, `team_item_v3`, `team_invite`, `team_invite_v1`, `team_invite_v2`, `team_invite_v3`, `team_recharge`, `team_recharge_v1`, `team_recharge_v2`, `team_recharge_v3`, `team_user`, `income`, `income_unreturned`, `invest`, `invest_num`, `invest_principal`, `points`, `points_del`, `raffle`, `raffle_del`, `transfer_in`, `transfer_out`, `bonus_first_deposit`, `bonus_invite`, `bonus_register`, `bonus_usdt_recharge`, `bonus_rmb_recharge`, `bonus_team_up`, `bonus_gift`, `bonus_item_finish`, `amount_available`, `amount_available_del`, `amount_frozen`, `amount_frozen_del`, `update_time`, `update_at`, `month_reward`) VALUES
    (1, '2026-01-15', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 176.00, 0.00, 1768496401, '2026-01-16 01:00:01', 0);

-- 导出  表 facai11.report_month 结构
CREATE TABLE IF NOT EXISTS `report_month` (
                                              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `period` date NOT NULL COMMENT '统计月份 YYYY-MM',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `bonus_reinvest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '复投奖励',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`,`uid`,`period`) USING BTREE,
    UNIQUE KEY `idx_uid_period` (`uid`,`period`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户月报统计'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.report_month 的数据：~0 rows (大约)
DELETE FROM `report_month`;
INSERT INTO `report_month` (`id`, `uid`, `period`, `yuebao_earn`, `yuebao_num`, `yuebao_deposit`, `yuebao_withdrawal`, `recharge`, `recharge_real`, `recharge_rmb`, `recharge_usdt`, `recharge_num`, `withdraw`, `withdraw_real`, `withdraw_rmb`, `withdraw_usdt`, `withdraw_reject`, `withdraw_num`, `signin_num`, `signin_money`, `signin_points`, `raffle_num`, `raffle_points`, `raffle_money`, `team_item`, `team_item_v1`, `team_item_v2`, `team_item_v3`, `team_invite`, `team_invite_v1`, `team_invite_v2`, `team_invite_v3`, `team_recharge`, `team_recharge_v1`, `team_recharge_v2`, `team_recharge_v3`, `team_user`, `income`, `income_unreturned`, `invest`, `invest_num`, `invest_principal`, `points`, `points_del`, `raffle`, `raffle_del`, `transfer_in`, `transfer_out`, `bonus_first_deposit`, `bonus_invite`, `bonus_register`, `bonus_usdt_recharge`, `bonus_rmb_recharge`, `bonus_team_up`, `bonus_gift`, `bonus_item_finish`, `bonus_reinvest`, `amount_available`, `amount_available_del`, `amount_frozen`, `amount_frozen_del`, `update_time`, `update_at`, `month_reward`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        (1, 4, '2026-01-01', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484436, '2026-01-15 21:40:36', 0),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        (2, 5, '2026-01-01', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484765, '2026-01-15 21:46:05', 0);

-- 导出  表 facai11.report_year 结构
CREATE TABLE IF NOT EXISTS `report_year` (
                                             `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `period` year(4) NOT NULL COMMENT '统计年份 YYYY',
    `yuebao_earn` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝收益',
    `yuebao_num` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝次数',
    `yuebao_deposit` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转入',
    `yuebao_withdrawal` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝转出',
    `recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值额度',
    `recharge_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值真实',
    `recharge_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值人民币',
    `recharge_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值USDT',
    `recharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计充值次数',
    `withdraw` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度',
    `withdraw_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度真实',
    `withdraw_rmb` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度人民币',
    `withdraw_usdt` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现额度USDT',
    `withdraw_reject` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计提现拒绝额度',
    `withdraw_num` int(11) NOT NULL DEFAULT '0' COMMENT '累计提现次数',
    `signin_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到次数',
    `signin_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户签到额度',
    `signin_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户签到积分',
    `raffle_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖次数',
    `raffle_points` int(11) NOT NULL DEFAULT '0' COMMENT '用户抽奖积分',
    `raffle_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户抽奖收益',
    `team_item` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_item_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v1金额',
    `team_item_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v2金额',
    `team_item_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资v3金额',
    `team_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队投资金额',
    `team_invite_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v1金额',
    `team_invite_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v2金额',
    `team_invite_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队邀请v3金额',
    `team_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值金额',
    `team_recharge_v1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v1金额',
    `team_recharge_v2` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v2金额',
    `team_recharge_v3` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队充值v3金额',
    `team_user` int(11) NOT NULL DEFAULT '0' COMMENT '团队人数',
    `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户总收益',
    `income_unreturned` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户未返的收益',
    `invest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资额度',
    `invest_num` int(11) NOT NULL DEFAULT '0' COMMENT '用户总投资数量',
    `invest_principal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '用户投资中的本金',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
    `points_del` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `raffle_del` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖减少',
    `transfer_in` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转出',
    `transfer_out` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额转账转入',
    `bonus_first_deposit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首存金额',
    `bonus_invite` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '邀请奖励',
    `bonus_register` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励',
    `bonus_usdt_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'USDT充值奖励',
    `bonus_rmb_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '人民币充值奖励',
    `bonus_team_up` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团长升级奖励',
    `bonus_gift` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
    `bonus_item_finish` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到期赠送现金',
    `bonus_reinvest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '复投奖励',
    `amount_available` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
    `amount_available_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额减少',
    `amount_frozen` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `amount_frozen_del` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额减少',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `month_reward` int(11) NOT NULL DEFAULT '0' COMMENT '产品月奖励',
    PRIMARY KEY (`id`,`uid`,`period`) USING BTREE,
    UNIQUE KEY `idx_uid_period` (`uid`,`period`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户年报统计'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.report_year 的数据：~0 rows (大约)
DELETE FROM `report_year`;
INSERT INTO `report_year` (`id`, `uid`, `period`, `yuebao_earn`, `yuebao_num`, `yuebao_deposit`, `yuebao_withdrawal`, `recharge`, `recharge_real`, `recharge_rmb`, `recharge_usdt`, `recharge_num`, `withdraw`, `withdraw_real`, `withdraw_rmb`, `withdraw_usdt`, `withdraw_reject`, `withdraw_num`, `signin_num`, `signin_money`, `signin_points`, `raffle_num`, `raffle_points`, `raffle_money`, `team_item`, `team_item_v1`, `team_item_v2`, `team_item_v3`, `team_invite`, `team_invite_v1`, `team_invite_v2`, `team_invite_v3`, `team_recharge`, `team_recharge_v1`, `team_recharge_v2`, `team_recharge_v3`, `team_user`, `income`, `income_unreturned`, `invest`, `invest_num`, `invest_principal`, `points`, `points_del`, `raffle`, `raffle_del`, `transfer_in`, `transfer_out`, `bonus_first_deposit`, `bonus_invite`, `bonus_register`, `bonus_usdt_recharge`, `bonus_rmb_recharge`, `bonus_team_up`, `bonus_gift`, `bonus_item_finish`, `bonus_reinvest`, `amount_available`, `amount_available_del`, `amount_frozen`, `amount_frozen_del`, `update_time`, `update_at`, `month_reward`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       (1, 4, '2026', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484436, '2026-01-15 21:40:36', 0),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       (2, 5, '2026', 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0, 0.00, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 88.00, 0.00, 1768484765, '2026-01-15 21:46:05', 0);

-- 导出  表 facai11.sign_in 结构
CREATE TABLE IF NOT EXISTS `sign_in` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
    `amount` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `sign_date` date NOT NULL COMMENT '签到日期',
    `continuous` tinyint(4) NOT NULL DEFAULT '0' COMMENT '连续签到天数0断1连续',
    `continuous_days` int(11) NOT NULL DEFAULT '0' COMMENT '连续签到天数',
    `continuous_days_total` int(11) NOT NULL DEFAULT '0' COMMENT '连续签到天数总',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.sign_in 的数据：~0 rows (大约)
DELETE FROM `sign_in`;

-- 导出  表 facai11.sign_in_gift 结构
CREATE TABLE IF NOT EXISTS `sign_in_gift` (
                                              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
    `amount` int(11) NOT NULL DEFAULT '0' COMMENT '金额',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `days` int(11) NOT NULL DEFAULT '0' COMMENT '天数',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='签到礼品';

-- 正在导出表  facai11.sign_in_gift 的数据：~0 rows (大约)
DELETE FROM `sign_in_gift`;

-- 导出  表 facai11.sign_in_gift_log 结构
CREATE TABLE IF NOT EXISTS `sign_in_gift_log` (
                                                  `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `gift_id` int(11) NOT NULL DEFAULT '0' COMMENT '礼品id',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1测试',
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
    `amount` int(11) NOT NULL DEFAULT '0' COMMENT '金额',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `gift_id` (`gift_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='签到礼品领取记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.sign_in_gift_log 的数据：~0 rows (大约)
DELETE FROM `sign_in_gift_log`;

-- 导出  表 facai11.sms_code 结构
CREATE TABLE IF NOT EXISTS `sms_code` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `code` varchar(50) NOT NULL DEFAULT '' COMMENT '验证码',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    PRIMARY KEY (`id`,`uid`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户短信'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.sms_code 的数据：~0 rows (大约)
DELETE FROM `sms_code`;

-- 导出  表 facai11.sys_dict 结构
CREATE TABLE IF NOT EXISTS `sys_dict` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `dict_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字典类型，如 sex、order_status、site_config',
    `dict_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字典键',
    `value_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '值类型：0=string 1=int 2=float 3=json 4=array 5=enum 6=text',
    `value_string` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'string 类型，或 text 类型的前端编辑内容',
    `value_text` text COLLATE utf8mb4_unicode_ci COMMENT '当 value_type=6 时使用，用于大文本',
    `value_int` int(11) DEFAULT NULL COMMENT 'int 类型',
    `value_float` decimal(16,2) DEFAULT NULL COMMENT 'float 类型',
    `value_json` json DEFAULT NULL COMMENT 'json/array/enum 类型字段（当前选中的值）',
    `enum_limit` json DEFAULT NULL COMMENT '枚举可选值',
    `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '展示名称',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '99最靠前',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0启用 1禁用',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_type` (`dict_type`),
    KEY `idx_type_key` (`dict_type`,`dict_key`)
    ) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统字典表';

-- 正在导出表  facai11.sys_dict 的数据：~22 rows (大约)
DELETE FROM `sys_dict`;
INSERT INTO `sys_dict` (`id`, `dict_type`, `dict_key`, `value_type`, `value_string`, `value_text`, `value_int`, `value_float`, `value_json`, `enum_limit`, `label`, `sort`, `status`, `remark`, `create_at`, `create_time`, `update_time`, `update_at`) VALUES
                                                                                                                                                                                                                                                            (8, '谷歌密匙', 'google', 0, 'NNVE6TBZLURGKWTH', NULL, NULL, NULL, NULL, NULL, '谷歌密匙', 0, 1, '谷歌密匙', '2025-11-27 13:15:47', 1764249386, 1764249391, '2025-11-27 13:16:31'),
                                                                                                                                                                                                                                                            (9, '签到金额', 'signin_money', 1, NULL, NULL, 2, NULL, NULL, NULL, '签到金额', 0, 1, '每日签到金额', '2025-11-27 13:18:19', 1764249499, 1764249537, '2025-11-27 13:18:57'),
                                                                                                                                                                                                                                                            (10, '最小充值', 'min_recharge', 1, NULL, NULL, 200, NULL, NULL, NULL, '最小充值', 0, 1, '最小充值', '2025-11-27 13:21:39', 1764249716, 1764250915, '2025-11-27 13:41:55'),
                                                                                                                                                                                                                                                            (11, '手续费', 'handling_fee', 1, NULL, NULL, 3, NULL, NULL, NULL, '手续费', 0, 1, '手续费', '2025-11-27 13:22:39', 1764249759, 1764250916, '2025-11-27 13:41:56'),
                                                                                                                                                                                                                                                            (12, 'USDT汇率', 'usdt_rate', 2, NULL, NULL, NULL, 7.09, NULL, NULL, 'USDT汇率', 0, 0, 'USDT汇率', '2025-11-27 13:23:12', 1768457160, 1768457160, '2026-01-15 06:06:00'),
                                                                                                                                                                                                                                                            (13, '客服', 'facai11', 0, 'https://d385rdmczj84vs.cloudfront.net/chatlink.html', NULL, NULL, NULL, NULL, NULL, '客服', 0, 1, '客服', '2025-11-27 13:24:23', 1767089892, 1767089892, '2025-12-30 10:18:12'),
                                                                                                                                                                                                                                                            (18, '视频地址', 'video_link', 4, NULL, NULL, NULL, NULL, '["https://buck-up-sun.oss-cn-shenzhen.aliyuncs.com/video/2025/12/12/aojiabao01.mp4"]', NULL, '视频地址', 0, 1, '视频地址', '2025-11-27 13:28:16', 1765550775, 1765550775, '2025-12-12 14:46:15'),
                                                                                                                                                                                                                                                            (19, 'H5链接', 'h5_link', 4, NULL, NULL, NULL, NULL, '["https://dgvb08jg3d1rd.cloudfront.net/", "https://d1w8uj7xpr7517.cloudfront.net/"]', NULL, 'H5链接', 0, 1, '', '2025-11-27 13:52:59', 1764329473, 1764329473, '2026-01-15 03:52:08'),
                                                                                                                                                                                                                                                            (20, 'USDT视频', 'usdt_video', 4, NULL, NULL, NULL, NULL, '[{"url": "https://52.69.167.250/static/01.mov", "title": "安卓手机欧易下载教程"}, {"url": "https://52.69.167.250/static/02.mov", "title": "苹果手机欧易下载教程"}, {"url": "https://52.69.167.250/static/03.mov", "title": "安卓手机币安下载教程"}, {"url": "https://52.69.167.250/static/04.mov", "title": "苹果手机币安下载教程"}]', NULL, 'USDT视频', 0, 0, 'USDT视频', '2025-11-28 11:30:44', 1768456839, 1768456839, '2026-01-15 06:14:00'),
                                                                                                                                                                                                                                                            (21, '余额宝收益', 'yuebao_rate', 2, NULL, NULL, NULL, 0.10, NULL, NULL, '', 0, 0, '', '2025-12-09 14:50:42', 0, 0, '2025-12-09 14:55:51'),
                                                                                                                                                                                                                                                            (22, '日期', 'withdraw_day', 4, NULL, NULL, NULL, NULL, '["周日", "周一", "周二", "周三", "周四", "周五", "周六"]', NULL, '提现日期', 0, 0, '提现日期', '2025-12-10 12:32:51', 1765370017, 1765370017, '2025-12-10 12:33:37'),
                                                                                                                                                                                                                                                            (23, '取款时间', 'withdraw_time', 3, NULL, NULL, NULL, NULL, '{"end": "23", "start": "11"}', NULL, '取款时间', 0, 0, '', '2025-12-10 12:35:45', 1765516787, 1765516787, '2025-12-12 05:19:47'),
                                                                                                                                                                                                                                                            (24, '银行卡提现开关', 'withdraw_type_bank', 1, NULL, NULL, 1, NULL, NULL, NULL, '银行卡提现开关', 0, 0, '银行卡提现开关', '2025-12-10 12:41:36', 1765516781, 1765516781, '2025-12-12 05:19:41'),
                                                                                                                                                                                                                                                            (25, 'USDT提现开关', 'withdraw_type_usdt', 1, NULL, NULL, 1, NULL, NULL, NULL, 'USDT提现开关', 0, 0, 'USDT提现开关', '2025-12-10 12:42:30', 1765516775, 1765516775, '2025-12-12 05:19:35'),
                                                                                                                                                                                                                                                            (26, '支付宝提现开关', 'withdraw_type_zhifubao', 1, NULL, NULL, 1, NULL, NULL, NULL, '支付宝提现开关', 0, 0, '支付宝提现开关', '2025-12-10 12:43:26', 1765516767, 1765516767, '2025-12-12 05:19:27'),
                                                                                                                                                                                                                                                            (27, '微信提现开关', 'withdraw_type_weixin', 1, NULL, NULL, 1, NULL, NULL, NULL, '微信提现开关', 0, 0, '微信提现开关', '2025-12-10 12:43:52', 1765516760, 1765516760, '2025-12-12 05:19:20'),
                                                                                                                                                                                                                                                            (29, '身份证实名验证', 'real_name_status', 1, NULL, NULL, 1, NULL, NULL, NULL, '身份证实名验证', 0, 0, '身份证实名验证', '2025-12-12 09:50:49', 1765533076, 1765533076, '2025-12-12 09:51:16'),
                                                                                                                                                                                                                                                            (30, '银行卡/支付宝/微信提现手续费率(%)', 'handling_fee_bank', 1, NULL, NULL, 2, NULL, NULL, NULL, '银行卡/支付宝/微信提现手续费率(%)', 50, 0, '银行卡、支付宝、微信提现的手续费百分比', '2025-12-17 09:46:31', 1765964960, 1765964960, '2025-12-17 09:49:20'),
                                                                                                                                                                                                                                                            (31, 'USDT提现手续费率(%)', 'handling_fee_usdt', 1, NULL, NULL, 0, NULL, NULL, NULL, 'USDT提现手续费率(%)', 51, 0, 'USDT数字货币提现的手续费百分比', '2025-12-17 09:46:31', 1765964949, 1765964949, '2025-12-17 09:49:09'),
                                                                                                                                                                                                                                                            (32, '提现时间间隔', 'withdrawal_interval', 1, NULL, NULL, 24, NULL, NULL, NULL, '提现时间间隔', 0, 0, '两次提现之间的最小时间间隔（小时）', '2026-01-04 06:30:56', 1767508256, 1767508256, '2026-01-04 06:30:56'),
                                                                                                                                                                                                                                                            (33, '下载链接', 'down_link', 0, 'https://d3ii0n7vjb07r2.cloudfront.net/', NULL, NULL, NULL, NULL, NULL, '下载链接', 0, 0, '下载链接', '2026-01-13 09:51:14', 1768443523, 1768443523, '2026-01-15 03:52:27'),
                                                                                                                                                                                                                                                            (34, '注册赠送金额', 'register_gift_cash', 1, NULL, NULL, 88, NULL, NULL, NULL, '注册赠送金额', 90, 0, '新用户注册成功后赠送的现金金额，0表示不开启', '2026-01-15 06:18:51', 1768462760, 1768462760, '2026-01-15 07:39:20');

-- 导出  表 facai11.sys_log 结构
CREATE TABLE IF NOT EXISTS `sys_log` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理账号',
    `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理名称',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '请求网址',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
    `params` text COMMENT '请求参数',
    `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1登录 2操作',
    `ip` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip',
    `ip_address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`admin_id`),
    KEY `admin_id` (`admin_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台日志表'
/*!50100 PARTITION BY HASH (`admin_id`)
PARTITIONS 32 */;

-- 正在导出表  facai11.sys_log 的数据：~0 rows (大约)
DELETE FROM `sys_log`;

-- 导出  表 facai11.sys_perm 结构
CREATE TABLE IF NOT EXISTS `sys_perm` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
    `code` varchar(200) NOT NULL DEFAULT '' COMMENT '权限标识（前端）',
    `api` varchar(255) DEFAULT '' COMMENT '接口路径',
    `method` varchar(10) DEFAULT '' COMMENT '请求方法',
    `pid` int(11) DEFAULT '0' COMMENT '父级ID',
    `type` tinyint(1) DEFAULT '1' COMMENT '类型：1=菜单，2=按钮，3=接口',
    `icon` varchar(50) DEFAULT '' COMMENT '图标',
    `sort` int(11) DEFAULT '0' COMMENT '排序',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_code` (`code`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2233 DEFAULT CHARSET=utf8mb4 COMMENT='菜单权限表';

-- 正在导出表  facai11.sys_perm 的数据：~295 rows (大约)
DELETE FROM `sys_perm`;
INSERT INTO `sys_perm` (`id`, `name`, `code`, `api`, `method`, `pid`, `type`, `icon`, `sort`, `create_time`, `update_time`) VALUES
                                                                                                                                (1, '用户管理', 'user', '', '', 0, 1, '', 10, 1766593717, 1766593717),
                                                                                                                                (2, '资金管理', 'fund', '', '', 0, 1, '', 20, 1766593717, 1766593717),
                                                                                                                                (3, '报表管理', 'report', '', '', 0, 1, '', 30, 1766593717, 1766593717),
                                                                                                                                (4, '抽奖管理', 'lottery', '', '', 0, 1, '', 40, 1766593717, 1766593717),
                                                                                                                                (5, '团队管理', 'team', '', '', 0, 1, '', 50, 1766593717, 1766593717),
                                                                                                                                (6, '签到管理', 'signin', '', '', 0, 1, '', 60, 1766593717, 1766593717),
                                                                                                                                (7, '拼团管理', 'pintuan', '', '', 0, 1, '', 70, 1766593717, 1766593717),
                                                                                                                                (8, '项目管理', 'project', '', '', 0, 1, '', 80, 1766593717, 1766593717),
                                                                                                                                (9, '支付管理', 'payment', '', '', 0, 1, '', 90, 1766593717, 1766593717),
                                                                                                                                (10, '商品管理', 'goods', '', '', 0, 1, '', 100, 1766593717, 1766593717),
                                                                                                                                (11, '运营管理', 'operation', '', '', 0, 1, '', 110, 1766593717, 1766593717),
                                                                                                                                (12, '系统设置', 'system', '', '', 0, 1, '', 1000, 1766593717, 1766593717),
                                                                                                                                (100, '用户列表', 'user_list', '', '', 1, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (101, '团队列表', 'user_team', '', '', 1, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (102, '钱包管理', 'user_wallet', '', '', 1, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (103, '地址管理', 'user_address', '', '', 1, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (104, '实名审核', 'user_realname', '', '', 1, 1, '', 5, 1766593717, 1766593717),
                                                                                                                                (105, '用户登录', 'user_login_log', '', '', 1, 1, '', 6, 1766593717, 1766593717),
                                                                                                                                (106, '用户等级', 'user_level', '', '', 1, 1, '', 7, 1766593717, 1766593717),
                                                                                                                                (107, '团队等级', 'team_level', '', '', 1, 1, '', 8, 1766593717, 1766593717),
                                                                                                                                (110, '普通充值', 'fund_recharge', '', '', 2, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (111, 'usdt充值', 'fund_usdt', '', '', 2, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (112, '提款列表', 'fund_withdraw', '', '', 2, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (113, '转账列表', 'fund_transfer', '', '', 2, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (114, '流水记录', 'fund_flow', '', '', 2, 1, '', 5, 1766593717, 1766593717),
                                                                                                                                (115, '余额宝记录', 'fund_yuebao', '', '', 2, 1, '', 6, 1766593717, 1766593717),
                                                                                                                                (120, '报表总汇', 'report_all', '', '', 3, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (121, '流水报表', 'report_flow', '', '', 3, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (122, '年度报表', 'report_year', '', '', 3, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (130, '抽奖列表', 'lottery_list', '', '', 4, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (131, '奖品管理', 'lottery_prize', '', '', 4, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (132, '抽奖记录', 'lottery_record', '', '', 4, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (140, '团队列表', 'team_list', '', '', 5, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (141, '团队详情', 'team_detail', '', '', 5, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (142, '团队层级记录', 'team_level_log', '', '', 5, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (150, '签到列表', 'signin_list', '', '', 6, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (151, '签到记录', 'signin_record', '', '', 6, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (152, '签到奖励类型', 'signin_reward_types', '', '', 6, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (160, '拼团列表', 'pintuan_list', '', '', 7, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (161, '拼团记录', 'pintuan_record', '', '', 7, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (162, '拼团审核', 'pintuan_audit', '', '', 7, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (163, '拼团分类', 'pintuan_type', '', '', 7, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (170, '项目列表', 'project_list', '', '', 8, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (171, '项目详情', 'project_detail', '', '', 8, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (172, '投资记录', 'project_buy_record', '', '', 8, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (173, '结算记录', 'project_settle_record', '', '', 8, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (174, '项目A', 'project_a', '', '', 8, 1, '', 5, 1766593717, 1766593717),
                                                                                                                                (175, '项目B', 'project_b', '', '', 8, 1, '', 6, 1766593717, 1766593717),
                                                                                                                                (176, '项目C', 'project_c', '', '', 8, 1, '', 7, 1766593717, 1766593717),
                                                                                                                                (177, '项目分类', 'project_type', '', '', 8, 1, '', 8, 1766593717, 1766593717),
                                                                                                                                (180, '支付通道', 'payment_channel', '', '', 9, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (181, '银行卡管理', 'payment_bank', '', '', 9, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (182, '支付账户', 'payment_account', '', '', 9, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (183, '充值类型', 'recharge_type', '', '', 9, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (184, '支付上游', 'payment_upper', '', '', 9, 1, '', 5, 1766593717, 1766593717),
                                                                                                                                (185, '用户币地址', 'user_coin_address', '', '', 9, 1, '', 6, 1767518034, 1767518034),
                                                                                                                                (190, '商品列表', 'goods_list', '', '', 10, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (191, '商品分类', 'goods_category', '', '', 10, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (192, '购买记录', 'goods_record', '', '', 10, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (200, '消息管理', 'operation_message', '', '', 11, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (201, '新闻类型', 'operation_news_type', '', '', 11, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (202, '新闻管理', 'operation_news', '', '', 11, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (203, '服务器管理', 'operation_servers', '', '', 11, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (204, '轮播图', 'banner_list', '', '', 11, 1, '', 5, 1766593717, 1766593717),
                                                                                                                                (205, '视频列表', 'video_list', '', '', 11, 1, '', 6, 1766593717, 1766593717),
                                                                                                                                (206, '用户反馈', 'user_feedback', '', '', 11, 1, '', 7, 1766593717, 1766593717),
                                                                                                                                (207, '问题列表', 'question_list', '', '', 11, 1, '', 8, 1766593717, 1766593717),
                                                                                                                                (210, '系统参数', 'system_config', '', '', 12, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (211, '参数设置', 'params_setting', '', '', 12, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (220, '角色管理', 'backend_role', '', '', 13, 1, '', 1, 1766593717, 1766593717),
                                                                                                                                (221, '权限管理', 'backend_perm', '', '', 13, 1, '', 2, 1766593717, 1766593717),
                                                                                                                                (222, '管理员管理', 'backend_admin', '', '', 13, 1, '', 3, 1766593717, 1766593717),
                                                                                                                                (223, '操作日志', 'backend_log', '', '', 13, 1, '', 4, 1766593717, 1766593717),
                                                                                                                                (1001, '新增', 'user_list_add', '', '', 100, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1002, '状态', 'user_list_state', '', '', 100, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1003, '金额', 'user_list_money', '', '', 100, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1004, '信息', 'user_list_info', '', '', 100, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1013, '统计', 'user_list_count', '', '', 100, 2, '', 11, 1766593717, 1766593717),
                                                                                                                                (1014, '修改邀请码', 'user_list_invite', '', '', 100, 2, '', 12, 1766593717, 1766593717),
                                                                                                                                (1015, '转移', 'user_list_transfer', '', '', 100, 2, '', 13, 1766593717, 1766593717),
                                                                                                                                (1016, '团队一键限制登录', 'user_list_limit_login', '', '', 100, 2, '', 14, 1766593717, 1766593717),
                                                                                                                                (1017, '团队一键限制用户', 'user_list_limit_user', '', '', 100, 2, '', 15, 1766593717, 1766593717),
                                                                                                                                (1020, '查看', 'user_team_view', '', '', 101, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1021, '导出', 'user_team_export', '', '', 101, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1030, '查看', 'user_wallet_view', '', '', 102, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1031, '充值', 'user_wallet_recharge', '', '', 102, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1032, '扣款', 'user_wallet_deduct', '', '', 102, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1033, '导出', 'user_wallet_export', '', '', 102, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1040, '查看', 'user_address_view', '', '', 103, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1041, '编辑', 'user_address_edit', '', '', 103, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1042, '删除', 'user_address_delete', '', '', 103, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1050, '查看', 'user_realname_view', '', '', 104, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1051, '审核通过', 'user_realname_approve', '', '', 104, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1052, '审核拒绝', 'user_realname_reject', '', '', 104, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1053, '导出', 'user_realname_export', '', '', 104, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1060, '查看', 'user_login_log_view', '', '', 105, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1061, '导出', 'user_login_log_export', '', '', 105, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1070, '查看', 'user_level_view', '', '', 106, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1071, '新增', 'user_level_add', '', '', 106, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1072, '编辑', 'user_level_edit', '', '', 106, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1073, '删除', 'user_level_delete', '', '', 106, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1080, '查看', 'team_level_view', '', '', 107, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1081, '新增', 'team_level_add', '', '', 107, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1082, '编辑', 'team_level_edit', '', '', 107, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1083, '删除', 'team_level_delete', '', '', 107, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1100, '查看', 'fund_recharge_view', '', '', 110, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1101, '审核通过', 'fund_recharge_approve', '', '', 110, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1102, '审核拒绝', 'fund_recharge_reject', '', '', 110, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1103, '导出', 'fund_recharge_export', '', '', 110, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1110, '查看', 'fund_usdt_view', '', '', 111, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1111, '审核通过', 'fund_usdt_approve', '', '', 111, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1112, '审核拒绝', 'fund_usdt_reject', '', '', 111, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1113, '导出', 'fund_usdt_export', '', '', 111, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1120, '查看', 'fund_withdraw_view', '', '', 112, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1121, '审核通过', 'fund_withdraw_approve', '', '', 112, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1122, '审核拒绝', 'fund_withdraw_reject', '', '', 112, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1123, '导出', 'fund_withdraw_export', '', '', 112, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1130, '查看', 'fund_transfer_view', '', '', 113, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1131, '导出', 'fund_transfer_export', '', '', 113, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1140, '查看', 'fund_flow_view', '', '', 114, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1141, '导出', 'fund_flow_export', '', '', 114, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1150, '查看', 'fund_yuebao_view', '', '', 115, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1151, '导出', 'fund_yuebao_export', '', '', 115, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1200, '查看', 'report_all_view', '', '', 120, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1201, '导出', 'report_all_export', '', '', 120, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1210, '查看', 'report_flow_view', '', '', 121, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1211, '导出', 'report_flow_export', '', '', 121, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1220, '查看', 'report_year_view', '', '', 122, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1221, '导出', 'report_year_export', '', '', 122, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1300, '查看', 'lottery_list_view', '', '', 130, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1301, '新增', 'lottery_list_add', '', '', 130, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1302, '编辑', 'lottery_list_edit', '', '', 130, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1303, '删除', 'lottery_list_delete', '', '', 130, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1304, '启用', 'lottery_list_enable', '', '', 130, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1305, '禁用', 'lottery_list_disable', '', '', 130, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1310, '查看', 'lottery_prize_view', '', '', 131, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1311, '新增', 'lottery_prize_add', '', '', 131, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1312, '编辑', 'lottery_prize_edit', '', '', 131, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1313, '删除', 'lottery_prize_delete', '', '', 131, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1320, '查看', 'lottery_record_view', '', '', 132, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1321, '导出', 'lottery_record_export', '', '', 132, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1400, '查看', 'team_list_view', '', '', 140, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1401, '导出', 'team_list_export', '', '', 140, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1410, '查看', 'team_detail_view', '', '', 141, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1420, '查看', 'team_level_log_view', '', '', 142, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1421, '导出', 'team_level_log_export', '', '', 142, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1500, '查看', 'signin_list_view', '', '', 150, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1501, '新增', 'signin_list_add', '', '', 150, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1502, '编辑', 'signin_list_edit', '', '', 150, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1503, '删除', 'signin_list_delete', '', '', 150, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1510, '查看', 'signin_record_view', '', '', 151, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1511, '导出', 'signin_record_export', '', '', 151, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1520, '查看', 'signin_reward_types_view', '', '', 152, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1521, '新增', 'signin_reward_types_add', '', '', 152, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1522, '编辑', 'signin_reward_types_edit', '', '', 152, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1523, '删除', 'signin_reward_types_delete', '', '', 152, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1600, '查看', 'pintuan_list_view', '', '', 160, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1601, '新增', 'pintuan_list_add', '', '', 160, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1602, '编辑', 'pintuan_list_edit', '', '', 160, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1603, '删除', 'pintuan_list_delete', '', '', 160, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1604, '启用', 'pintuan_list_enable', '', '', 160, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1605, '禁用', 'pintuan_list_disable', '', '', 160, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1610, '查看', 'pintuan_record_view', '', '', 161, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1611, '导出', 'pintuan_record_export', '', '', 161, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1620, '查看', 'pintuan_audit_view', '', '', 162, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1621, '审核通过', 'pintuan_audit_approve', '', '', 162, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1622, '审核拒绝', 'pintuan_audit_reject', '', '', 162, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1623, '导出', 'pintuan_audit_export', '', '', 162, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1630, '查看', 'pintuan_type_view', '', '', 163, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1631, '新增', 'pintuan_type_add', '', '', 163, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1632, '编辑', 'pintuan_type_edit', '', '', 163, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1633, '删除', 'pintuan_type_delete', '', '', 163, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1700, '查看', 'project_list_view', '', '', 170, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1701, '新增', 'project_list_add', '', '', 170, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1702, '编辑', 'project_list_edit', '', '', 170, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1703, '删除', 'project_list_delete', '', '', 170, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1704, '上架', 'project_list_online', '', '', 170, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1705, '下架', 'project_list_offline', '', '', 170, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1710, '查看', 'project_detail_view', '', '', 171, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1720, '查看', 'project_buy_record_view', '', '', 172, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1721, '导出', 'project_buy_record_export', '', '', 172, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1730, '查看', 'project_settle_record_view', '', '', 173, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1731, '导出', 'project_settle_record_export', '', '', 173, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1740, '查看', 'project_a_view', '', '', 174, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1741, '新增', 'project_a_add', '', '', 174, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1742, '编辑', 'project_a_edit', '', '', 174, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1743, '删除', 'project_a_delete', '', '', 174, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1750, '查看', 'project_b_view', '', '', 175, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1751, '新增', 'project_b_add', '', '', 175, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1752, '编辑', 'project_b_edit', '', '', 175, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1753, '删除', 'project_b_delete', '', '', 175, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1760, '查看', 'project_c_view', '', '', 176, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1761, '新增', 'project_c_add', '', '', 176, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1762, '编辑', 'project_c_edit', '', '', 176, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1763, '删除', 'project_c_delete', '', '', 176, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1770, '查看', 'project_type_view', '', '', 177, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1771, '新增', 'project_type_add', '', '', 177, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1772, '编辑', 'project_type_edit', '', '', 177, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1773, '删除', 'project_type_delete', '', '', 177, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1800, '查看', 'payment_channel_view', '', '', 180, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1801, '新增', 'payment_channel_add', '', '', 180, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1802, '编辑', 'payment_channel_edit', '', '', 180, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1803, '删除', 'payment_channel_delete', '', '', 180, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1804, '启用', 'payment_channel_enable', '', '', 180, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1805, '禁用', 'payment_channel_disable', '', '', 180, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1810, '查看', 'payment_bank_view', '', '', 181, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1811, '新增', 'payment_bank_add', '', '', 181, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1812, '编辑', 'payment_bank_edit', '', '', 181, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1813, '删除', 'payment_bank_delete', '', '', 181, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1820, '查看', 'payment_account_view', '', '', 182, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1821, '新增', 'payment_account_add', '', '', 182, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1822, '编辑', 'payment_account_edit', '', '', 182, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1823, '删除', 'payment_account_delete', '', '', 182, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1824, '启用', 'payment_account_enable', '', '', 182, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1825, '禁用', 'payment_account_disable', '', '', 182, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1830, '查看', 'recharge_type_view', '', '', 183, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1831, '新增', 'recharge_type_add', '', '', 183, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1832, '编辑', 'recharge_type_edit', '', '', 183, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1833, '删除', 'recharge_type_delete', '', '', 183, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1840, '查看', 'payment_upper_view', '', '', 184, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1841, '新增', 'payment_upper_add', '', '', 184, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1842, '编辑', 'payment_upper_edit', '', '', 184, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1843, '删除', 'payment_upper_delete', '', '', 184, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1850, '查看', 'user_coin_address_view', '', '', 185, 2, '', 1, 1767518034, 1767518034),
                                                                                                                                (1900, '查看', 'goods_list_view', '', '', 190, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1901, '新增', 'goods_list_add', '', '', 190, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1902, '编辑', 'goods_list_edit', '', '', 190, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1903, '删除', 'goods_list_delete', '', '', 190, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1904, '上架', 'goods_list_online', '', '', 190, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (1905, '下架', 'goods_list_offline', '', '', 190, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (1910, '查看', 'goods_category_view', '', '', 191, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1911, '新增', 'goods_category_add', '', '', 191, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (1912, '编辑', 'goods_category_edit', '', '', 191, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (1913, '删除', 'goods_category_delete', '', '', 191, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (1920, '查看', 'goods_record_view', '', '', 192, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (1921, '导出', 'goods_record_export', '', '', 192, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2000, '查看', 'operation_message_view', '', '', 200, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2001, '新增', 'operation_message_add', '', '', 200, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2002, '编辑', 'operation_message_edit', '', '', 200, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2003, '删除', 'operation_message_delete', '', '', 200, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2004, '发送', 'operation_message_send', '', '', 200, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (2010, '查看', 'operation_news_type_view', '', '', 201, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2011, '新增', 'operation_news_type_add', '', '', 201, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2012, '编辑', 'operation_news_type_edit', '', '', 201, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2013, '删除', 'operation_news_type_delete', '', '', 201, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2020, '查看', 'operation_news_view', '', '', 202, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2021, '新增', 'operation_news_add', '', '', 202, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2022, '编辑', 'operation_news_edit', '', '', 202, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2023, '删除', 'operation_news_delete', '', '', 202, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2024, '发布', 'operation_news_publish', '', '', 202, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (2025, '撤回', 'operation_news_unpublish', '', '', 202, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (2030, '查看', 'operation_servers_view', '', '', 203, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2031, '新增', 'operation_servers_add', '', '', 203, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2032, '编辑', 'operation_servers_edit', '', '', 203, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2033, '删除', 'operation_servers_delete', '', '', 203, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2040, '查看', 'banner_list_view', '', '', 204, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2041, '新增', 'banner_list_add', '', '', 204, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2042, '编辑', 'banner_list_edit', '', '', 204, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2043, '删除', 'banner_list_delete', '', '', 204, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2050, '查看', 'video_list_view', '', '', 205, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2051, '新增', 'video_list_add', '', '', 205, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2052, '编辑', 'video_list_edit', '', '', 205, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2053, '删除', 'video_list_delete', '', '', 205, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2060, '查看', 'user_feedback_view', '', '', 206, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2061, '删除', 'user_feedback_delete', '', '', 206, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2070, '查看', 'question_list_view', '', '', 207, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2071, '新增', 'question_list_add', '', '', 207, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2072, '编辑', 'question_list_edit', '', '', 207, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2073, '删除', 'question_list_delete', '', '', 207, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2100, '查看', 'system_config_view', '', '', 210, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2101, '编辑', 'system_config_edit', '', '', 210, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2110, '查看', 'params_setting_view', '', '', 211, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2111, '新增', 'params_setting_add', '', '', 211, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2112, '编辑', 'params_setting_edit', '', '', 211, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2113, '删除', 'params_setting_delete', '', '', 211, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2200, '查看', 'backend_role_view', '', '', 220, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2201, '新增', 'backend_role_add', '', '', 220, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2202, '编辑', 'backend_role_edit', '', '', 220, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2203, '删除', 'backend_role_delete', '', '', 220, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2204, '分配权限', 'backend_role_assign', '', '', 220, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (2210, '查看', 'backend_perm_view', '', '', 221, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2211, '新增', 'backend_perm_add', '', '', 221, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2212, '编辑', 'backend_perm_edit', '', '', 221, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2213, '删除', 'backend_perm_delete', '', '', 221, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2220, '查看', 'backend_admin_view', '', '', 222, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2221, '新增', 'backend_admin_add', '', '', 222, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2222, '编辑', 'backend_admin_edit', '', '', 222, 2, '', 3, 1766593717, 1766593717),
                                                                                                                                (2223, '删除', 'backend_admin_delete', '', '', 222, 2, '', 4, 1766593717, 1766593717),
                                                                                                                                (2224, '禁用', 'backend_admin_disable', '', '', 222, 2, '', 5, 1766593717, 1766593717),
                                                                                                                                (2225, '启用', 'backend_admin_enable', '', '', 222, 2, '', 6, 1766593717, 1766593717),
                                                                                                                                (2226, '重置密码', 'backend_admin_reset_pwd', '', '', 222, 2, '', 7, 1766593717, 1766593717),
                                                                                                                                (2227, '分配角色', 'backend_admin_assign_role', '', '', 222, 2, '', 8, 1766593717, 1766593717),
                                                                                                                                (2230, '查看', 'backend_log_view', '', '', 223, 2, '', 1, 1766593717, 1766593717),
                                                                                                                                (2231, '导出', 'backend_log_export', '', '', 223, 2, '', 2, 1766593717, 1766593717),
                                                                                                                                (2232, '删除', 'backend_log_delete', '', '', 223, 2, '', 3, 1766593717, 1766593717);

-- 导出  表 facai11.sys_role 结构
CREATE TABLE IF NOT EXISTS `sys_role` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
    `code` varchar(50) NOT NULL DEFAULT '' COMMENT '角色标识（唯一）',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '角色说明',
    `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
    `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_status` (`status`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- 正在导出表  facai11.sys_role 的数据：~6 rows (大约)
DELETE FROM `sys_role`;
INSERT INTO `sys_role` (`id`, `name`, `code`, `remark`, `status`, `sort`, `create_time`, `update_time`) VALUES
                                                                                                            (1, '小红', 'admin', 'test', 0, 0, 1766495015, 1766495015),
                                                                                                            (2, '小白', 'admin2', 'admin2', 0, 0, 1766495075, 1766495075),
                                                                                                            (3, '测试管理员', 'test_admin', '用于测试权限系统（拥有所有权限）', 0, 1, 1766498236, 1766498454),
                                                                                                            (6, '运营管理员', 'operator', '负责日常运营，管理用户、订单、文章等', 0, 90, 1766499275, 1766500625),
                                                                                                            (7, '财务管理员', 'finance', '负责财务审核、提现审批等', 1, 80, 1766499275, 1766499275),
                                                                                                            (8, '客服管理员', 'customer_service', '负责处理用户反馈和投诉', 1, 70, 1766499275, 1766499275);

-- 导出  表 facai11.sys_role_perm 结构
CREATE TABLE IF NOT EXISTS `sys_role_perm` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
    `perm_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_permission` (`role_id`,`perm_id`) USING BTREE,
    KEY `idx_role_id` (`role_id`),
    KEY `idx_permission_id` (`perm_id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8mb4 COMMENT='角色-权限关系表';

-- 正在导出表  facai11.sys_role_perm 的数据：~592 rows (大约)
DELETE FROM `sys_role_perm`;
INSERT INTO `sys_role_perm` (`id`, `role_id`, `perm_id`, `create_time`, `update_time`) VALUES
                                                                                           (1, 0, 13, 1766593717, 0),
                                                                                           (2, 0, 222, 1766593717, 0),
                                                                                           (3, 0, 2221, 1766593717, 0),
                                                                                           (4, 0, 2227, 1766593717, 0),
                                                                                           (5, 0, 2223, 1766593717, 0),
                                                                                           (6, 0, 2224, 1766593717, 0),
                                                                                           (7, 0, 2222, 1766593717, 0),
                                                                                           (8, 0, 2225, 1766593717, 0),
                                                                                           (9, 0, 2226, 1766593717, 0),
                                                                                           (10, 0, 2220, 1766593717, 0),
                                                                                           (11, 0, 223, 1766593717, 0),
                                                                                           (12, 0, 2232, 1766593717, 0),
                                                                                           (13, 0, 2231, 1766593717, 0),
                                                                                           (14, 0, 2230, 1766593717, 0),
                                                                                           (15, 0, 221, 1766593717, 0),
                                                                                           (16, 0, 2211, 1766593717, 0),
                                                                                           (17, 0, 2213, 1766593717, 0),
                                                                                           (18, 0, 2212, 1766593717, 0),
                                                                                           (19, 0, 2210, 1766593717, 0),
                                                                                           (20, 0, 220, 1766593717, 0),
                                                                                           (21, 0, 2201, 1766593717, 0),
                                                                                           (22, 0, 2204, 1766593717, 0),
                                                                                           (23, 0, 2203, 1766593717, 0),
                                                                                           (24, 0, 2202, 1766593717, 0),
                                                                                           (25, 0, 2200, 1766593717, 0),
                                                                                           (26, 0, 204, 1766593717, 0),
                                                                                           (27, 0, 2041, 1766593717, 0),
                                                                                           (28, 0, 2043, 1766593717, 0),
                                                                                           (29, 0, 2042, 1766593717, 0),
                                                                                           (30, 0, 2040, 1766593717, 0),
                                                                                           (31, 0, 2, 1766593717, 0),
                                                                                           (32, 0, 114, 1766593717, 0),
                                                                                           (33, 0, 1141, 1766593717, 0),
                                                                                           (34, 0, 1140, 1766593717, 0),
                                                                                           (35, 0, 110, 1766593717, 0),
                                                                                           (36, 0, 1101, 1766593717, 0),
                                                                                           (37, 0, 1103, 1766593717, 0),
                                                                                           (38, 0, 1102, 1766593717, 0),
                                                                                           (39, 0, 1100, 1766593717, 0),
                                                                                           (40, 0, 113, 1766593717, 0),
                                                                                           (41, 0, 1131, 1766593717, 0),
                                                                                           (42, 0, 1130, 1766593717, 0),
                                                                                           (43, 0, 111, 1766593717, 0),
                                                                                           (44, 0, 1111, 1766593717, 0),
                                                                                           (45, 0, 1113, 1766593717, 0),
                                                                                           (46, 0, 1112, 1766593717, 0),
                                                                                           (47, 0, 1110, 1766593717, 0),
                                                                                           (48, 0, 112, 1766593717, 0),
                                                                                           (49, 0, 1121, 1766593717, 0),
                                                                                           (50, 0, 1123, 1766593717, 0),
                                                                                           (51, 0, 1122, 1766593717, 0),
                                                                                           (52, 0, 1120, 1766593717, 0),
                                                                                           (53, 0, 115, 1766593717, 0),
                                                                                           (54, 0, 1151, 1766593717, 0),
                                                                                           (55, 0, 1150, 1766593717, 0),
                                                                                           (56, 0, 10, 1766593717, 0),
                                                                                           (57, 0, 191, 1766593717, 0),
                                                                                           (58, 0, 1911, 1766593717, 0),
                                                                                           (59, 0, 1913, 1766593717, 0),
                                                                                           (60, 0, 1912, 1766593717, 0),
                                                                                           (61, 0, 1910, 1766593717, 0),
                                                                                           (62, 0, 190, 1766593717, 0),
                                                                                           (63, 0, 1901, 1766593717, 0),
                                                                                           (64, 0, 1903, 1766593717, 0),
                                                                                           (65, 0, 1902, 1766593717, 0),
                                                                                           (66, 0, 1905, 1766593717, 0),
                                                                                           (67, 0, 1904, 1766593717, 0),
                                                                                           (68, 0, 1900, 1766593717, 0),
                                                                                           (69, 0, 192, 1766593717, 0),
                                                                                           (70, 0, 1921, 1766593717, 0),
                                                                                           (71, 0, 1920, 1766593717, 0),
                                                                                           (72, 0, 4, 1766593717, 0),
                                                                                           (73, 0, 130, 1766593717, 0),
                                                                                           (74, 0, 1301, 1766593717, 0),
                                                                                           (75, 0, 1303, 1766593717, 0),
                                                                                           (76, 0, 1305, 1766593717, 0),
                                                                                           (77, 0, 1302, 1766593717, 0),
                                                                                           (78, 0, 1304, 1766593717, 0),
                                                                                           (79, 0, 1300, 1766593717, 0),
                                                                                           (80, 0, 131, 1766593717, 0),
                                                                                           (81, 0, 1311, 1766593717, 0),
                                                                                           (82, 0, 1313, 1766593717, 0),
                                                                                           (83, 0, 1312, 1766593717, 0),
                                                                                           (84, 0, 1310, 1766593717, 0),
                                                                                           (85, 0, 132, 1766593717, 0),
                                                                                           (86, 0, 1321, 1766593717, 0),
                                                                                           (87, 0, 1320, 1766593717, 0),
                                                                                           (88, 0, 11, 1766593717, 0),
                                                                                           (89, 0, 200, 1766593717, 0),
                                                                                           (90, 0, 2001, 1766593717, 0),
                                                                                           (91, 0, 2003, 1766593717, 0),
                                                                                           (92, 0, 2002, 1766593717, 0),
                                                                                           (93, 0, 2004, 1766593717, 0),
                                                                                           (94, 0, 2000, 1766593717, 0),
                                                                                           (95, 0, 202, 1766593717, 0),
                                                                                           (96, 0, 2021, 1766593717, 0),
                                                                                           (97, 0, 2023, 1766593717, 0),
                                                                                           (98, 0, 2022, 1766593717, 0),
                                                                                           (99, 0, 2024, 1766593717, 0),
                                                                                           (100, 0, 201, 1766593717, 0),
                                                                                           (101, 0, 2011, 1766593717, 0),
                                                                                           (102, 0, 2013, 1766593717, 0),
                                                                                           (103, 0, 2012, 1766593717, 0),
                                                                                           (104, 0, 2010, 1766593717, 0),
                                                                                           (105, 0, 2025, 1766593717, 0),
                                                                                           (106, 0, 2020, 1766593717, 0),
                                                                                           (107, 0, 203, 1766593717, 0),
                                                                                           (108, 0, 2031, 1766593717, 0),
                                                                                           (109, 0, 2033, 1766593717, 0),
                                                                                           (110, 0, 2032, 1766593717, 0),
                                                                                           (111, 0, 2030, 1766593717, 0),
                                                                                           (112, 0, 211, 1766593717, 0),
                                                                                           (113, 0, 2111, 1766593717, 0),
                                                                                           (114, 0, 2113, 1766593717, 0),
                                                                                           (115, 0, 2112, 1766593717, 0),
                                                                                           (116, 0, 2110, 1766593717, 0),
                                                                                           (117, 0, 9, 1766593717, 0),
                                                                                           (118, 0, 182, 1766593717, 0),
                                                                                           (119, 0, 1821, 1766593717, 0),
                                                                                           (120, 0, 1823, 1766593717, 0),
                                                                                           (121, 0, 1825, 1766593717, 0),
                                                                                           (122, 0, 1822, 1766593717, 0),
                                                                                           (123, 0, 1824, 1766593717, 0),
                                                                                           (124, 0, 1820, 1766593717, 0),
                                                                                           (125, 0, 181, 1766593717, 0),
                                                                                           (126, 0, 1811, 1766593717, 0),
                                                                                           (127, 0, 1813, 1766593717, 0),
                                                                                           (128, 0, 1812, 1766593717, 0),
                                                                                           (129, 0, 1810, 1766593717, 0),
                                                                                           (130, 0, 180, 1766593717, 0),
                                                                                           (131, 0, 1801, 1766593717, 0),
                                                                                           (132, 0, 1803, 1766593717, 0),
                                                                                           (133, 0, 1805, 1766593717, 0),
                                                                                           (134, 0, 1802, 1766593717, 0),
                                                                                           (135, 0, 1804, 1766593717, 0),
                                                                                           (136, 0, 1800, 1766593717, 0),
                                                                                           (137, 0, 184, 1766593717, 0),
                                                                                           (138, 0, 1841, 1766593717, 0),
                                                                                           (139, 0, 1843, 1766593717, 0),
                                                                                           (140, 0, 1842, 1766593717, 0),
                                                                                           (141, 0, 1840, 1766593717, 0),
                                                                                           (142, 0, 7, 1766593717, 0),
                                                                                           (143, 0, 162, 1766593717, 0),
                                                                                           (144, 0, 1621, 1766593717, 0),
                                                                                           (145, 0, 1623, 1766593717, 0),
                                                                                           (146, 0, 1622, 1766593717, 0),
                                                                                           (147, 0, 1620, 1766593717, 0),
                                                                                           (148, 0, 160, 1766593717, 0),
                                                                                           (149, 0, 1601, 1766593717, 0),
                                                                                           (150, 0, 1603, 1766593717, 0),
                                                                                           (151, 0, 1605, 1766593717, 0),
                                                                                           (152, 0, 1602, 1766593717, 0),
                                                                                           (153, 0, 1604, 1766593717, 0),
                                                                                           (154, 0, 1600, 1766593717, 0),
                                                                                           (155, 0, 161, 1766593717, 0),
                                                                                           (156, 0, 1611, 1766593717, 0),
                                                                                           (157, 0, 1610, 1766593717, 0),
                                                                                           (158, 0, 163, 1766593717, 0),
                                                                                           (159, 0, 1631, 1766593717, 0),
                                                                                           (160, 0, 1633, 1766593717, 0),
                                                                                           (161, 0, 1632, 1766593717, 0),
                                                                                           (162, 0, 1630, 1766593717, 0),
                                                                                           (163, 0, 8, 1766593717, 0),
                                                                                           (164, 0, 174, 1766593717, 0),
                                                                                           (165, 0, 1741, 1766593717, 0),
                                                                                           (166, 0, 1743, 1766593717, 0),
                                                                                           (167, 0, 1742, 1766593717, 0),
                                                                                           (168, 0, 1740, 1766593717, 0),
                                                                                           (169, 0, 175, 1766593717, 0),
                                                                                           (170, 0, 172, 1766593717, 0),
                                                                                           (171, 0, 1721, 1766593717, 0),
                                                                                           (172, 0, 1720, 1766593717, 0),
                                                                                           (173, 0, 1751, 1766593717, 0),
                                                                                           (174, 0, 1753, 1766593717, 0),
                                                                                           (175, 0, 1752, 1766593717, 0),
                                                                                           (176, 0, 1750, 1766593717, 0),
                                                                                           (177, 0, 176, 1766593717, 0),
                                                                                           (178, 0, 1761, 1766593717, 0),
                                                                                           (179, 0, 1763, 1766593717, 0),
                                                                                           (180, 0, 1762, 1766593717, 0),
                                                                                           (181, 0, 1760, 1766593717, 0),
                                                                                           (182, 0, 171, 1766593717, 0),
                                                                                           (183, 0, 1710, 1766593717, 0),
                                                                                           (184, 0, 170, 1766593717, 0),
                                                                                           (185, 0, 1701, 1766593717, 0),
                                                                                           (186, 0, 1703, 1766593717, 0),
                                                                                           (187, 0, 1702, 1766593717, 0),
                                                                                           (188, 0, 1705, 1766593717, 0),
                                                                                           (189, 0, 1704, 1766593717, 0),
                                                                                           (190, 0, 1700, 1766593717, 0),
                                                                                           (191, 0, 173, 1766593717, 0),
                                                                                           (192, 0, 1731, 1766593717, 0),
                                                                                           (193, 0, 1730, 1766593717, 0),
                                                                                           (194, 0, 177, 1766593717, 0),
                                                                                           (195, 0, 1771, 1766593717, 0),
                                                                                           (196, 0, 1773, 1766593717, 0),
                                                                                           (197, 0, 1772, 1766593717, 0),
                                                                                           (198, 0, 1770, 1766593717, 0),
                                                                                           (199, 0, 207, 1766593717, 0),
                                                                                           (200, 0, 2071, 1766593717, 0),
                                                                                           (201, 0, 2073, 1766593717, 0),
                                                                                           (202, 0, 2072, 1766593717, 0),
                                                                                           (203, 0, 2070, 1766593717, 0),
                                                                                           (204, 0, 183, 1766593717, 0),
                                                                                           (205, 0, 1831, 1766593717, 0),
                                                                                           (206, 0, 1833, 1766593717, 0),
                                                                                           (207, 0, 1832, 1766593717, 0),
                                                                                           (208, 0, 1830, 1766593717, 0),
                                                                                           (209, 0, 3, 1766593717, 0),
                                                                                           (210, 0, 120, 1766593717, 0),
                                                                                           (211, 0, 1201, 1766593717, 0),
                                                                                           (212, 0, 1200, 1766593717, 0),
                                                                                           (213, 0, 121, 1766593717, 0),
                                                                                           (214, 0, 1211, 1766593717, 0),
                                                                                           (215, 0, 1210, 1766593717, 0),
                                                                                           (216, 0, 122, 1766593717, 0),
                                                                                           (217, 0, 1221, 1766593717, 0),
                                                                                           (218, 0, 1220, 1766593717, 0),
                                                                                           (219, 0, 6, 1766593717, 0),
                                                                                           (220, 0, 150, 1766593717, 0),
                                                                                           (221, 0, 1501, 1766593717, 0),
                                                                                           (222, 0, 1503, 1766593717, 0),
                                                                                           (223, 0, 1502, 1766593717, 0),
                                                                                           (224, 0, 1500, 1766593717, 0),
                                                                                           (225, 0, 151, 1766593717, 0),
                                                                                           (226, 0, 1511, 1766593717, 0),
                                                                                           (227, 0, 1510, 1766593717, 0),
                                                                                           (228, 0, 152, 1766593717, 0),
                                                                                           (229, 0, 1521, 1766593717, 0),
                                                                                           (230, 0, 1523, 1766593717, 0),
                                                                                           (231, 0, 1522, 1766593717, 0),
                                                                                           (232, 0, 1520, 1766593717, 0),
                                                                                           (233, 0, 12, 1766593717, 0),
                                                                                           (234, 0, 210, 1766593717, 0),
                                                                                           (235, 0, 2101, 1766593717, 0),
                                                                                           (236, 0, 2100, 1766593717, 0),
                                                                                           (237, 0, 5, 1766593717, 0),
                                                                                           (238, 0, 141, 1766593717, 0),
                                                                                           (239, 0, 1410, 1766593717, 0),
                                                                                           (240, 0, 107, 1766593717, 0),
                                                                                           (241, 0, 1081, 1766593717, 0),
                                                                                           (242, 0, 1083, 1766593717, 0),
                                                                                           (243, 0, 1082, 1766593717, 0),
                                                                                           (244, 0, 142, 1766593717, 0),
                                                                                           (245, 0, 1421, 1766593717, 0),
                                                                                           (246, 0, 1420, 1766593717, 0),
                                                                                           (247, 0, 1080, 1766593717, 0),
                                                                                           (248, 0, 140, 1766593717, 0),
                                                                                           (249, 0, 1401, 1766593717, 0),
                                                                                           (250, 0, 1400, 1766593717, 0),
                                                                                           (251, 0, 1, 1766593717, 0),
                                                                                           (252, 0, 103, 1766593717, 0),
                                                                                           (253, 0, 1042, 1766593717, 0),
                                                                                           (254, 0, 1041, 1766593717, 0),
                                                                                           (255, 0, 1040, 1766593717, 0),
                                                                                           (256, 0, 206, 1766593717, 0),
                                                                                           (257, 0, 2061, 1766593717, 0),
                                                                                           (258, 0, 2060, 1766593717, 0),
                                                                                           (259, 0, 106, 1766593717, 0),
                                                                                           (260, 0, 1071, 1766593717, 0),
                                                                                           (261, 0, 1073, 1766593717, 0),
                                                                                           (262, 0, 1072, 1766593717, 0),
                                                                                           (263, 0, 1070, 1766593717, 0),
                                                                                           (264, 0, 100, 1766593717, 0),
                                                                                           (265, 0, 1001, 1766593717, 0),
                                                                                           (266, 0, 1013, 1766593717, 0),
                                                                                           (267, 0, 1004, 1766593717, 0),
                                                                                           (268, 0, 1014, 1766593717, 0),
                                                                                           (269, 0, 1016, 1766593717, 0),
                                                                                           (270, 0, 1017, 1766593717, 0),
                                                                                           (271, 0, 1003, 1766593717, 0),
                                                                                           (272, 0, 1002, 1766593717, 0),
                                                                                           (273, 0, 1015, 1766593717, 0),
                                                                                           (274, 0, 105, 1766593717, 0),
                                                                                           (275, 0, 1061, 1766593717, 0),
                                                                                           (276, 0, 1060, 1766593717, 0),
                                                                                           (277, 0, 104, 1766593717, 0),
                                                                                           (278, 0, 1051, 1766593717, 0),
                                                                                           (279, 0, 1053, 1766593717, 0),
                                                                                           (280, 0, 1052, 1766593717, 0),
                                                                                           (281, 0, 1050, 1766593717, 0),
                                                                                           (282, 0, 101, 1766593717, 0),
                                                                                           (283, 0, 1021, 1766593717, 0),
                                                                                           (284, 0, 1020, 1766593717, 0),
                                                                                           (285, 0, 102, 1766593717, 0),
                                                                                           (286, 0, 1032, 1766593717, 0),
                                                                                           (287, 0, 1033, 1766593717, 0),
                                                                                           (288, 0, 1031, 1766593717, 0),
                                                                                           (289, 0, 1030, 1766593717, 0),
                                                                                           (290, 0, 205, 1766593717, 0),
                                                                                           (291, 0, 2051, 1766593717, 0),
                                                                                           (292, 0, 2053, 1766593717, 0),
                                                                                           (293, 0, 2052, 1766593717, 0),
                                                                                           (294, 0, 2050, 1766593717, 0),
                                                                                           (512, 1, 1001, 1766593763, 1766593763),
                                                                                           (513, 1, 1003, 1766593763, 1766593763),
                                                                                           (514, 1, 1, 1766593763, 1766593763),
                                                                                           (515, 1, 100, 1766593763, 1766593763),
                                                                                           (516, 2, 1, 1766740730, 1766740730),
                                                                                           (517, 2, 100, 1766740730, 1766740730),
                                                                                           (518, 2, 1001, 1766740730, 1766740730),
                                                                                           (519, 2, 1002, 1766740730, 1766740730),
                                                                                           (520, 2, 1003, 1766740730, 1766740730),
                                                                                           (521, 2, 1004, 1766740730, 1766740730),
                                                                                           (522, 2, 1013, 1766740730, 1766740730),
                                                                                           (523, 2, 1014, 1766740730, 1766740730),
                                                                                           (524, 2, 1015, 1766740730, 1766740730),
                                                                                           (525, 2, 1016, 1766740730, 1766740730),
                                                                                           (526, 2, 1017, 1766740730, 1766740730),
                                                                                           (527, 2, 101, 1766740730, 1766740730),
                                                                                           (528, 2, 1020, 1766740730, 1766740730),
                                                                                           (529, 2, 1021, 1766740730, 1766740730),
                                                                                           (530, 2, 102, 1766740730, 1766740730),
                                                                                           (531, 2, 1030, 1766740730, 1766740730),
                                                                                           (532, 2, 1031, 1766740730, 1766740730),
                                                                                           (533, 2, 1032, 1766740730, 1766740730),
                                                                                           (534, 2, 1033, 1766740730, 1766740730),
                                                                                           (535, 2, 103, 1766740730, 1766740730),
                                                                                           (536, 2, 1040, 1766740730, 1766740730),
                                                                                           (537, 2, 1041, 1766740730, 1766740730),
                                                                                           (538, 2, 1042, 1766740730, 1766740730),
                                                                                           (539, 2, 104, 1766740730, 1766740730),
                                                                                           (540, 2, 1050, 1766740730, 1766740730),
                                                                                           (541, 2, 1051, 1766740730, 1766740730),
                                                                                           (542, 2, 1052, 1766740730, 1766740730),
                                                                                           (543, 2, 1053, 1766740730, 1766740730),
                                                                                           (544, 2, 105, 1766740730, 1766740730),
                                                                                           (545, 2, 1060, 1766740730, 1766740730),
                                                                                           (546, 2, 1061, 1766740730, 1766740730),
                                                                                           (547, 2, 106, 1766740730, 1766740730),
                                                                                           (548, 2, 1070, 1766740730, 1766740730),
                                                                                           (549, 2, 1071, 1766740730, 1766740730),
                                                                                           (550, 2, 1072, 1766740730, 1766740730),
                                                                                           (551, 2, 1073, 1766740730, 1766740730),
                                                                                           (552, 2, 107, 1766740730, 1766740730),
                                                                                           (553, 2, 1080, 1766740730, 1766740730),
                                                                                           (554, 2, 1081, 1766740730, 1766740730),
                                                                                           (555, 2, 1082, 1766740730, 1766740730),
                                                                                           (556, 2, 1083, 1766740730, 1766740730),
                                                                                           (557, 2, 2, 1766740730, 1766740730),
                                                                                           (558, 2, 110, 1766740730, 1766740730),
                                                                                           (559, 2, 1100, 1766740730, 1766740730),
                                                                                           (560, 2, 1101, 1766740730, 1766740730),
                                                                                           (561, 2, 1102, 1766740730, 1766740730),
                                                                                           (562, 2, 1103, 1766740730, 1766740730),
                                                                                           (563, 2, 111, 1766740730, 1766740730),
                                                                                           (564, 2, 1110, 1766740730, 1766740730),
                                                                                           (565, 2, 1111, 1766740730, 1766740730),
                                                                                           (566, 2, 1112, 1766740730, 1766740730),
                                                                                           (567, 2, 1113, 1766740730, 1766740730),
                                                                                           (568, 2, 112, 1766740730, 1766740730),
                                                                                           (569, 2, 1120, 1766740730, 1766740730),
                                                                                           (570, 2, 1121, 1766740730, 1766740730),
                                                                                           (571, 2, 1122, 1766740730, 1766740730),
                                                                                           (572, 2, 1123, 1766740730, 1766740730),
                                                                                           (573, 2, 113, 1766740730, 1766740730),
                                                                                           (574, 2, 1130, 1766740730, 1766740730),
                                                                                           (575, 2, 1131, 1766740730, 1766740730),
                                                                                           (576, 2, 114, 1766740730, 1766740730),
                                                                                           (577, 2, 1140, 1766740730, 1766740730),
                                                                                           (578, 2, 1141, 1766740730, 1766740730),
                                                                                           (579, 2, 115, 1766740730, 1766740730),
                                                                                           (580, 2, 1150, 1766740730, 1766740730),
                                                                                           (581, 2, 1151, 1766740730, 1766740730),
                                                                                           (582, 2, 3, 1766740730, 1766740730),
                                                                                           (583, 2, 120, 1766740730, 1766740730),
                                                                                           (584, 2, 1200, 1766740730, 1766740730),
                                                                                           (585, 2, 1201, 1766740730, 1766740730),
                                                                                           (586, 2, 121, 1766740730, 1766740730),
                                                                                           (587, 2, 1210, 1766740730, 1766740730),
                                                                                           (588, 2, 1211, 1766740730, 1766740730),
                                                                                           (589, 2, 122, 1766740730, 1766740730),
                                                                                           (590, 2, 1220, 1766740730, 1766740730),
                                                                                           (591, 2, 1221, 1766740730, 1766740730),
                                                                                           (592, 2, 4, 1766740730, 1766740730),
                                                                                           (593, 2, 130, 1766740730, 1766740730),
                                                                                           (594, 2, 1300, 1766740730, 1766740730),
                                                                                           (595, 2, 1301, 1766740730, 1766740730),
                                                                                           (596, 2, 1302, 1766740730, 1766740730),
                                                                                           (597, 2, 1303, 1766740730, 1766740730),
                                                                                           (598, 2, 1304, 1766740730, 1766740730),
                                                                                           (599, 2, 1305, 1766740730, 1766740730),
                                                                                           (600, 2, 131, 1766740730, 1766740730),
                                                                                           (601, 2, 1310, 1766740730, 1766740730),
                                                                                           (602, 2, 1311, 1766740730, 1766740730),
                                                                                           (603, 2, 1312, 1766740730, 1766740730),
                                                                                           (604, 2, 1313, 1766740730, 1766740730),
                                                                                           (605, 2, 132, 1766740730, 1766740730),
                                                                                           (606, 2, 1320, 1766740730, 1766740730),
                                                                                           (607, 2, 1321, 1766740730, 1766740730),
                                                                                           (608, 2, 5, 1766740730, 1766740730),
                                                                                           (609, 2, 140, 1766740730, 1766740730),
                                                                                           (610, 2, 1400, 1766740730, 1766740730),
                                                                                           (611, 2, 1401, 1766740730, 1766740730),
                                                                                           (612, 2, 141, 1766740730, 1766740730),
                                                                                           (613, 2, 1410, 1766740730, 1766740730),
                                                                                           (614, 2, 142, 1766740730, 1766740730),
                                                                                           (615, 2, 1420, 1766740730, 1766740730),
                                                                                           (616, 2, 1421, 1766740730, 1766740730),
                                                                                           (617, 2, 6, 1766740730, 1766740730),
                                                                                           (618, 2, 150, 1766740730, 1766740730),
                                                                                           (619, 2, 1500, 1766740730, 1766740730),
                                                                                           (620, 2, 1501, 1766740730, 1766740730),
                                                                                           (621, 2, 1502, 1766740730, 1766740730),
                                                                                           (622, 2, 1503, 1766740730, 1766740730),
                                                                                           (623, 2, 151, 1766740730, 1766740730),
                                                                                           (624, 2, 1510, 1766740730, 1766740730),
                                                                                           (625, 2, 1511, 1766740730, 1766740730),
                                                                                           (626, 2, 152, 1766740730, 1766740730),
                                                                                           (627, 2, 1520, 1766740730, 1766740730),
                                                                                           (628, 2, 1521, 1766740730, 1766740730),
                                                                                           (629, 2, 1522, 1766740730, 1766740730),
                                                                                           (630, 2, 1523, 1766740730, 1766740730),
                                                                                           (631, 2, 7, 1766740730, 1766740730),
                                                                                           (632, 2, 160, 1766740730, 1766740730),
                                                                                           (633, 2, 1600, 1766740730, 1766740730),
                                                                                           (634, 2, 1601, 1766740730, 1766740730),
                                                                                           (635, 2, 1602, 1766740730, 1766740730),
                                                                                           (636, 2, 1603, 1766740730, 1766740730),
                                                                                           (637, 2, 1604, 1766740730, 1766740730),
                                                                                           (638, 2, 1605, 1766740730, 1766740730),
                                                                                           (639, 2, 161, 1766740730, 1766740730),
                                                                                           (640, 2, 1610, 1766740730, 1766740730),
                                                                                           (641, 2, 1611, 1766740730, 1766740730),
                                                                                           (642, 2, 162, 1766740730, 1766740730),
                                                                                           (643, 2, 1620, 1766740730, 1766740730),
                                                                                           (644, 2, 1621, 1766740730, 1766740730),
                                                                                           (645, 2, 1622, 1766740730, 1766740730),
                                                                                           (646, 2, 1623, 1766740730, 1766740730),
                                                                                           (647, 2, 163, 1766740730, 1766740730),
                                                                                           (648, 2, 1630, 1766740730, 1766740730),
                                                                                           (649, 2, 1631, 1766740730, 1766740730),
                                                                                           (650, 2, 1632, 1766740730, 1766740730),
                                                                                           (651, 2, 1633, 1766740730, 1766740730),
                                                                                           (652, 2, 8, 1766740730, 1766740730),
                                                                                           (653, 2, 170, 1766740730, 1766740730),
                                                                                           (654, 2, 1700, 1766740730, 1766740730),
                                                                                           (655, 2, 1701, 1766740730, 1766740730),
                                                                                           (656, 2, 1702, 1766740730, 1766740730),
                                                                                           (657, 2, 1703, 1766740730, 1766740730),
                                                                                           (658, 2, 1704, 1766740730, 1766740730),
                                                                                           (659, 2, 1705, 1766740730, 1766740730),
                                                                                           (660, 2, 171, 1766740730, 1766740730),
                                                                                           (661, 2, 1710, 1766740730, 1766740730),
                                                                                           (662, 2, 172, 1766740730, 1766740730),
                                                                                           (663, 2, 1720, 1766740730, 1766740730),
                                                                                           (664, 2, 1721, 1766740730, 1766740730),
                                                                                           (665, 2, 173, 1766740730, 1766740730),
                                                                                           (666, 2, 1730, 1766740730, 1766740730),
                                                                                           (667, 2, 1731, 1766740730, 1766740730),
                                                                                           (668, 2, 174, 1766740730, 1766740730),
                                                                                           (669, 2, 1740, 1766740730, 1766740730),
                                                                                           (670, 2, 1741, 1766740730, 1766740730),
                                                                                           (671, 2, 1742, 1766740730, 1766740730),
                                                                                           (672, 2, 1743, 1766740730, 1766740730),
                                                                                           (673, 2, 175, 1766740730, 1766740730),
                                                                                           (674, 2, 1750, 1766740730, 1766740730),
                                                                                           (675, 2, 1751, 1766740730, 1766740730),
                                                                                           (676, 2, 1752, 1766740730, 1766740730),
                                                                                           (677, 2, 1753, 1766740730, 1766740730),
                                                                                           (678, 2, 176, 1766740730, 1766740730),
                                                                                           (679, 2, 1760, 1766740730, 1766740730),
                                                                                           (680, 2, 1761, 1766740730, 1766740730),
                                                                                           (681, 2, 1762, 1766740730, 1766740730),
                                                                                           (682, 2, 1763, 1766740730, 1766740730),
                                                                                           (683, 2, 177, 1766740730, 1766740730),
                                                                                           (684, 2, 1770, 1766740730, 1766740730),
                                                                                           (685, 2, 1771, 1766740730, 1766740730),
                                                                                           (686, 2, 1772, 1766740730, 1766740730),
                                                                                           (687, 2, 1773, 1766740730, 1766740730),
                                                                                           (688, 2, 9, 1766740730, 1766740730),
                                                                                           (689, 2, 180, 1766740730, 1766740730),
                                                                                           (690, 2, 1800, 1766740730, 1766740730),
                                                                                           (691, 2, 1801, 1766740730, 1766740730),
                                                                                           (692, 2, 1802, 1766740730, 1766740730),
                                                                                           (693, 2, 1803, 1766740730, 1766740730),
                                                                                           (694, 2, 1804, 1766740730, 1766740730),
                                                                                           (695, 2, 1805, 1766740730, 1766740730),
                                                                                           (696, 2, 181, 1766740730, 1766740730),
                                                                                           (697, 2, 1810, 1766740730, 1766740730),
                                                                                           (698, 2, 1811, 1766740730, 1766740730),
                                                                                           (699, 2, 1812, 1766740730, 1766740730),
                                                                                           (700, 2, 1813, 1766740730, 1766740730),
                                                                                           (701, 2, 182, 1766740730, 1766740730),
                                                                                           (702, 2, 1820, 1766740730, 1766740730),
                                                                                           (703, 2, 1821, 1766740730, 1766740730),
                                                                                           (704, 2, 1822, 1766740730, 1766740730),
                                                                                           (705, 2, 1823, 1766740730, 1766740730),
                                                                                           (706, 2, 1824, 1766740730, 1766740730),
                                                                                           (707, 2, 1825, 1766740730, 1766740730),
                                                                                           (708, 2, 183, 1766740730, 1766740730),
                                                                                           (709, 2, 1830, 1766740730, 1766740730),
                                                                                           (710, 2, 1831, 1766740730, 1766740730),
                                                                                           (711, 2, 1832, 1766740730, 1766740730),
                                                                                           (712, 2, 1833, 1766740730, 1766740730),
                                                                                           (713, 2, 184, 1766740730, 1766740730),
                                                                                           (714, 2, 1840, 1766740730, 1766740730),
                                                                                           (715, 2, 1841, 1766740730, 1766740730),
                                                                                           (716, 2, 1842, 1766740730, 1766740730),
                                                                                           (717, 2, 1843, 1766740730, 1766740730),
                                                                                           (718, 2, 10, 1766740730, 1766740730),
                                                                                           (719, 2, 190, 1766740730, 1766740730),
                                                                                           (720, 2, 1900, 1766740730, 1766740730),
                                                                                           (721, 2, 1901, 1766740730, 1766740730),
                                                                                           (722, 2, 1902, 1766740730, 1766740730),
                                                                                           (723, 2, 1903, 1766740730, 1766740730),
                                                                                           (724, 2, 1904, 1766740730, 1766740730),
                                                                                           (725, 2, 1905, 1766740730, 1766740730),
                                                                                           (726, 2, 191, 1766740730, 1766740730),
                                                                                           (727, 2, 1910, 1766740730, 1766740730),
                                                                                           (728, 2, 1911, 1766740730, 1766740730),
                                                                                           (729, 2, 1912, 1766740730, 1766740730),
                                                                                           (730, 2, 1913, 1766740730, 1766740730),
                                                                                           (731, 2, 192, 1766740730, 1766740730),
                                                                                           (732, 2, 1920, 1766740730, 1766740730),
                                                                                           (733, 2, 1921, 1766740730, 1766740730),
                                                                                           (734, 2, 11, 1766740730, 1766740730),
                                                                                           (735, 2, 200, 1766740730, 1766740730),
                                                                                           (736, 2, 2000, 1766740730, 1766740730),
                                                                                           (737, 2, 2001, 1766740730, 1766740730),
                                                                                           (738, 2, 2002, 1766740730, 1766740730),
                                                                                           (739, 2, 2003, 1766740730, 1766740730),
                                                                                           (740, 2, 2004, 1766740730, 1766740730),
                                                                                           (741, 2, 201, 1766740730, 1766740730),
                                                                                           (742, 2, 2010, 1766740730, 1766740730),
                                                                                           (743, 2, 2011, 1766740730, 1766740730),
                                                                                           (744, 2, 2012, 1766740730, 1766740730),
                                                                                           (745, 2, 2013, 1766740730, 1766740730),
                                                                                           (746, 2, 202, 1766740730, 1766740730),
                                                                                           (747, 2, 2020, 1766740730, 1766740730),
                                                                                           (748, 2, 2021, 1766740730, 1766740730),
                                                                                           (749, 2, 2022, 1766740730, 1766740730),
                                                                                           (750, 2, 2023, 1766740730, 1766740730),
                                                                                           (751, 2, 2024, 1766740730, 1766740730),
                                                                                           (752, 2, 2025, 1766740730, 1766740730),
                                                                                           (753, 2, 203, 1766740730, 1766740730),
                                                                                           (754, 2, 2030, 1766740730, 1766740730),
                                                                                           (755, 2, 2031, 1766740730, 1766740730),
                                                                                           (756, 2, 2032, 1766740730, 1766740730),
                                                                                           (757, 2, 2033, 1766740730, 1766740730),
                                                                                           (758, 2, 204, 1766740730, 1766740730),
                                                                                           (759, 2, 2040, 1766740730, 1766740730),
                                                                                           (760, 2, 2041, 1766740730, 1766740730),
                                                                                           (761, 2, 2042, 1766740730, 1766740730),
                                                                                           (762, 2, 2043, 1766740730, 1766740730),
                                                                                           (763, 2, 205, 1766740730, 1766740730),
                                                                                           (764, 2, 2050, 1766740730, 1766740730),
                                                                                           (765, 2, 2051, 1766740730, 1766740730),
                                                                                           (766, 2, 2052, 1766740730, 1766740730),
                                                                                           (767, 2, 2053, 1766740730, 1766740730),
                                                                                           (768, 2, 206, 1766740730, 1766740730),
                                                                                           (769, 2, 2060, 1766740730, 1766740730),
                                                                                           (770, 2, 2061, 1766740730, 1766740730),
                                                                                           (771, 2, 207, 1766740730, 1766740730),
                                                                                           (772, 2, 2070, 1766740730, 1766740730),
                                                                                           (773, 2, 2071, 1766740730, 1766740730),
                                                                                           (774, 2, 2072, 1766740730, 1766740730),
                                                                                           (775, 2, 2073, 1766740730, 1766740730),
                                                                                           (776, 2, 12, 1766740730, 1766740730),
                                                                                           (777, 2, 210, 1766740730, 1766740730),
                                                                                           (778, 2, 2100, 1766740730, 1766740730),
                                                                                           (779, 2, 2101, 1766740730, 1766740730),
                                                                                           (780, 2, 211, 1766740730, 1766740730),
                                                                                           (781, 2, 2110, 1766740730, 1766740730),
                                                                                           (782, 2, 2111, 1766740730, 1766740730),
                                                                                           (783, 2, 2112, 1766740730, 1766740730),
                                                                                           (784, 2, 2113, 1766740730, 1766740730),
                                                                                           (785, 2, 13, 1766740730, 1766740730),
                                                                                           (786, 2, 220, 1766740730, 1766740730),
                                                                                           (787, 2, 2200, 1766740730, 1766740730),
                                                                                           (788, 2, 2201, 1766740730, 1766740730),
                                                                                           (789, 2, 2202, 1766740730, 1766740730),
                                                                                           (790, 2, 2203, 1766740730, 1766740730),
                                                                                           (791, 2, 2204, 1766740730, 1766740730),
                                                                                           (792, 2, 221, 1766740730, 1766740730),
                                                                                           (793, 2, 2210, 1766740730, 1766740730),
                                                                                           (794, 2, 2211, 1766740730, 1766740730),
                                                                                           (795, 2, 2212, 1766740730, 1766740730),
                                                                                           (796, 2, 2213, 1766740730, 1766740730),
                                                                                           (797, 2, 222, 1766740730, 1766740730),
                                                                                           (798, 2, 2220, 1766740730, 1766740730),
                                                                                           (799, 2, 2221, 1766740730, 1766740730),
                                                                                           (800, 2, 2222, 1766740730, 1766740730),
                                                                                           (801, 2, 2223, 1766740730, 1766740730),
                                                                                           (802, 2, 2224, 1766740730, 1766740730),
                                                                                           (803, 2, 2225, 1766740730, 1766740730),
                                                                                           (804, 2, 2226, 1766740730, 1766740730),
                                                                                           (805, 2, 2227, 1766740730, 1766740730),
                                                                                           (806, 2, 223, 1766740730, 1766740730),
                                                                                           (807, 2, 2230, 1766740730, 1766740730),
                                                                                           (808, 2, 2231, 1766740730, 1766740730),
                                                                                           (809, 2, 2232, 1766740730, 1766740730);

-- 导出  表 facai11.sys_user 结构
CREATE TABLE IF NOT EXISTS `sys_user` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
    `email` varchar(50) NOT NULL DEFAULT '' COMMENT 'email',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
    `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'token',
    `role` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0超级管理1.普通管理',
    `role_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '角色id',
    `role_name` tinyint(4) NOT NULL DEFAULT '0' COMMENT '角色名称',
    `clock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未冻结1.已冻结',
    `login_time` int(11) NOT NULL DEFAULT '0' COMMENT '登入时间',
    `login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登入IP',
    `ip_address` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip地址',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `login_agent` varchar(255) NOT NULL DEFAULT '' COMMENT '登入ui头',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台管理员表';

-- 正在导出表  facai11.sys_user 的数据：~9 rows (大约)
DELETE FROM `sys_user`;
INSERT INTO `sys_user` (`id`, `username`, `password`, `email`, `remark`, `token`, `role`, `role_id`, `role_name`, `clock`, `login_time`, `login_ip`, `ip_address`, `create_time`, `update_time`, `create_at`, `update_at`, `login_agent`) VALUES
                                                                                                                                                                                                                                              (1, 'admin', 'admin', '123456@email.com', '最高权限', '4d64eaaa6c26a10e623cde76e17430d5', 0, 0, 0, 0, 1768481432, '110.235.220.60', '柬埔寨,柬埔寨', 0, 0, '2025-03-11 11:10:51', '2025-03-11 11:10:51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
                                                                                                                                                                                                                                              (3, 'admin2', 'admin2', '', '', '53d0ec09c9b965e4f9ec52dcbcb8191b', 0, 0, 0, 0, 1768446726, '110.235.220.60', '柬埔寨,柬埔寨', 1741794065, 1741794065, '2025-03-12 23:41:05', '2025-03-12 23:41:05', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0'),
                                                                                                                                                                                                                                              (4, 'admin3', 'admin3', '', '', 'e81813152d8a2c1e1fd4c9d099eff3a4', 0, 0, 0, 0, 1768456237, '207.56.218.202', '美国,美国', 1763628213, 1764232493, '2025-11-20 16:43:33', '2025-11-20 16:43:33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
                                                                                                                                                                                                                                              (5, 'admin4', 'admin4', '', '', 'eccf6c1bfc1450a3cb2f2804f15f6235', 0, 0, 0, 0, 1766998202, '129.224.203.213', '美国,美国', 0, 0, '2025-11-29 08:29:52', '2025-11-29 08:29:52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
                                                                                                                                                                                                                                              (6, 'admin5', 'admin5', '', '', '856d8a65bbe84d3c0eed6b803715faf4', 0, 0, 0, 0, 1766928804, '129.224.203.213', '美国,美国', 0, 0, '2025-11-29 08:30:04', '2025-11-29 08:30:04', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
                                                                                                                                                                                                                                              (7, 'ad123456', 'ad123456', '', '', '63297ebc1e31a74f71245337cdf69963', 1, 0, 0, 0, 1766069181, '129.224.202.98', '美国,美国', 1765178381, 1765178381, '2025-12-08 15:19:41', '2025-12-08 15:19:41', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
                                                                                                                                                                                                                                              (8, 'sally', 'sally123456', '', '', '', 1, 0, 0, 0, 0, '', '', 1765178445, 1765178445, '2025-12-08 15:20:45', '2025-12-08 15:20:45', ''),
                                                                                                                                                                                                                                              (9, 'Asheng', 'Asheng123456', '', '', '', 1, 0, 0, 0, 0, '', '', 1765178465, 1765178465, '2025-12-08 15:21:05', '2025-12-08 15:21:05', ''),
                                                                                                                                                                                                                                              (10, 'xiaohong', '123456', '', '', '5cd56639391d172473381968cf5f9dd2', 0, 1, 0, 0, 1768447426, '14.207.167.98', '泰国,泰国', 1766498315, 1766498315, '2025-12-23 21:58:35', '2025-12-23 21:58:35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

-- 导出  表 facai11.sys_user_log 结构
CREATE TABLE IF NOT EXISTS `sys_user_log` (
                                              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '管理id',
    `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `login_ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '登入ip',
    `ip_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip 地址',
    `login_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '登入代理',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `username` (`username`)
    ) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='系统管理登入日志表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.sys_user_log 的数据：~0 rows (大约)
DELETE FROM `sys_user_log`;
INSERT INTO `sys_user_log` (`id`, `uid`, `username`, `login_ip`, `ip_address`, `login_agent`, `create_at`, `update_at`, `create_time`, `update_time`) VALUES
    (215, 1, 'admin', '110.235.220.60', '柬埔寨,柬埔寨', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-15 20:50:32', '2026-01-15 20:50:32', 0, 0);

-- 导出  表 facai11.sys_user_role 结构
CREATE TABLE IF NOT EXISTS `sys_user_role` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
    `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_role_id` (`role_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='管理-角色关系表';

-- 正在导出表  facai11.sys_user_role 的数据：~3 rows (大约)
DELETE FROM `sys_user_role`;
INSERT INTO `sys_user_role` (`id`, `user_id`, `role_id`, `create_time`, `update_time`) VALUES
                                                                                           (3, 10, 1, 1766591989, 1766591989),
                                                                                           (4, 1, 2, 1766740749, 1766740749),
                                                                                           (5, 3, 2, 1766740754, 1766740754);

-- 导出  表 facai11.team 结构
CREATE TABLE IF NOT EXISTS `team` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
    `lv0` int(11) NOT NULL DEFAULT '0' COMMENT '全部人数',
    `lv1` int(11) NOT NULL DEFAULT '0' COMMENT '直推人数',
    `profit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '团队盈利',
    `bonus` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '升级奖励',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='团队等级表';

-- 正在导出表  facai11.team 的数据：~8 rows (大约)
DELETE FROM `team`;
INSERT INTO `team` (`id`, `title`, `lv0`, `lv1`, `profit`, `bonus`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                (1, '零级代理', 0, 0, 0.00, 0.00, 1764408055, 1764408055, '2025-11-29 17:20:55', '2025-11-29 17:20:55'),
                                                                                                                                (2, '一级代理', 20, 5, 250000.00, 2500.00, 1763626828, 1765002259, '2025-11-20 16:20:28', '2025-11-20 16:20:28'),
                                                                                                                                (3, '二级代理', 50, 15, 600000.00, 12000.00, 1763811221, 1765434080, '2025-11-22 19:33:41', '2025-11-22 19:33:41'),
                                                                                                                                (4, '三级代理', 100, 30, 1300000.00, 40000.00, 1763811236, 1765434091, '2025-11-22 19:33:56', '2025-11-22 19:33:56'),
                                                                                                                                (5, '四级代理', 200, 50, 3000000.00, 120000.00, 1764402106, 1765434099, '2025-11-29 15:41:46', '2025-11-29 15:41:46'),
                                                                                                                                (6, '五级代理', 500, 80, 9000000.00, 500000.00, 1764402161, 1765434108, '2025-11-29 15:42:41', '2025-11-29 15:42:41'),
                                                                                                                                (7, '六级代理', 1000, 120, 22000000.00, 1600000.00, 1764402196, 1765434116, '2025-11-29 15:43:16', '2025-11-29 15:43:16'),
                                                                                                                                (8, '七级代理', 2000, 200, 60000000.00, 5000000.00, 1764402228, 1765434125, '2025-11-29 15:43:48', '2025-11-29 15:43:48');

-- 导出  表 facai11.team_gift 结构
CREATE TABLE IF NOT EXISTS `team_gift` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否 测试0正常 1测试',
    `team_id` int(11) NOT NULL DEFAULT '0' COMMENT '团队等级id',
    `team_name` varchar(255) NOT NULL DEFAULT '' COMMENT '团队等级名称',
    `gift_time` int(11) NOT NULL DEFAULT '0' COMMENT '团队礼品发放时间',
    `bonus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团队奖金',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未发放1已经发放',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='团队奖励发放';

-- 正在导出表  facai11.team_gift 的数据：~0 rows (大约)
DELETE FROM `team_gift`;

-- 导出  表 facai11.team_log 结构
CREATE TABLE IF NOT EXISTS `team_log` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `lv_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级id',
    `lv_name` varchar(50) NOT NULL DEFAULT '' COMMENT '等级名称',
    `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '等级描述',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0自己1下级',
    `recharge` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
    `gift` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
    `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
    `admin_name` varchar(50) NOT NULL DEFAULT '0' COMMENT '管理员名称',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `username` (`username`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='团长升级记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.team_log 的数据：~0 rows (大约)
DELETE FROM `team_log`;

-- 导出  表 facai11.transfer 结构
CREATE TABLE IF NOT EXISTS `transfer` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL COMMENT '转账人',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常用户1测试用户',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
    `to_uid` int(11) NOT NULL COMMENT '转给人',
    `to_username` varchar(50) NOT NULL DEFAULT '' COMMENT '转账用户',
    `to_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '转账手机号',
    `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态 0同意 1申请 2拒绝',
    `remark` varchar(50) NOT NULL DEFAULT '' COMMENT '备注',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `passage_time` int(11) NOT NULL DEFAULT '0' COMMENT '通过时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `to_uid` (`to_uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='转账记录'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.transfer 的数据：~0 rows (大约)
DELETE FROM `transfer`;

-- 导出  表 facai11.user 结构
CREATE TABLE IF NOT EXISTS `user` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '8位随机用户ID',
    `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
    `nickname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
    `password` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
    `pin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付密码',
    `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
    `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
    `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
    `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录token',
    `invite` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邀请码',
    `sfz_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '实名姓名',
    `sfz_time` int(11) NOT NULL DEFAULT '0' COMMENT '实名时间',
    `sfz_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '实名账号',
    `remark` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `level` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级',
    `level_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '会员等级名称',
    `level_team` int(11) NOT NULL DEFAULT '0' COMMENT '团队等级',
    `level_team_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '团队等级名称',
    `login_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录IP',
    `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录地址',
    `register_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '注册IP',
    `v1_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
    `v1_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上级名称',
    `v2_id` int(11) NOT NULL DEFAULT '0' COMMENT '二级ID',
    `v2_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二级名称',
    `v3_id` int(11) NOT NULL DEFAULT '0' COMMENT '三级ID',
    `v3_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '三级名称',
    `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作管理员ID',
    `admin_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员名称',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试账号 0正常 1测试',
    `money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '可用余额',
    `frozen_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
    `yuebao` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额宝金额',
    `points` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
    `raffle` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
    `sfz_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '实名认证状态',
    `ban_buy` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止购买',
    `ban_lock` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户冻结',
    `ban_sigin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止签到',
    `ban_raffle` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止抽奖',
    `ban_login` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止登入',
    `ban_invite` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止邀请',
    `ban_recharge` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止充值',
    `ban_withdraw` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止提现',
    `ban_exchange` tinyint(4) NOT NULL DEFAULT '0' COMMENT '禁止兑换',
    `kick_out` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否踢下线',
    `valid_user` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否有效用户',
    `first_recharge_time` int(11) NOT NULL DEFAULT '0' COMMENT '首充时间',
    `recharge_time` int(11) NOT NULL DEFAULT '0' COMMENT '充值时间',
    `invest_time` int(11) NOT NULL DEFAULT '0' COMMENT '投资时间',
    `withdraw_time` int(11) NOT NULL DEFAULT '0' COMMENT '提现时间',
    `signin_time` int(11) NOT NULL DEFAULT '0' COMMENT '签到时间',
    `login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
    `yuebao_time` int(11) NOT NULL DEFAULT '0' COMMENT '余额宝时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间戳',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `uk_uid` (`uid`) USING BTREE,
    UNIQUE KEY `uk_phone` (`phone`) USING BTREE COMMENT '手机号唯一索引',
    KEY `idx_username` (`username`) USING BTREE,
    KEY `invite` (`invite`),
    KEY `v1_id` (`v1_id`),
    KEY `v2_id` (`v2_id`),
    KEY `v3_id` (`v3_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户表';

-- 正在导出表  facai11.user 的数据：~2 rows (大约)
DELETE FROM `user`;
INSERT INTO `user` (`id`, `uid`, `username`, `nickname`, `password`, `pin`, `phone`, `email`, `avatar`, `token`, `invite`, `sfz_name`, `sfz_time`, `sfz_number`, `remark`, `level`, `level_name`, `level_team`, `level_team_name`, `login_ip`, `ip_address`, `register_ip`, `v1_id`, `v1_name`, `v2_id`, `v2_name`, `v3_id`, `v3_name`, `admin_id`, `admin_name`, `is_test`, `money`, `frozen_money`, `yuebao`, `points`, `raffle`, `sfz_status`, `ban_buy`, `ban_lock`, `ban_sigin`, `ban_raffle`, `ban_login`, `ban_invite`, `ban_recharge`, `ban_withdraw`, `ban_exchange`, `kick_out`, `valid_user`, `first_recharge_time`, `recharge_time`, `invest_time`, `withdraw_time`, `signin_time`, `login_time`, `yuebao_time`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         (1, 29271481, '', '东一真', '123456', '123456', '13888888888', '', '', 'ab5be1239ec3690b881454a9e82d6e82', '2323924', '', 1768481796, '', '', 1, 'V0', 1, '零级代理', '110.235.220.60', '柬埔寨,柬埔寨', '110.235.220.60', 0, '', 0, '', 0, '', 0, '', 0, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1768485674, 0, 1768481498, 1768485202, '2026-01-15 20:51:38', '2026-01-15 22:01:14'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         (2, 56780632, '', '东一嘉', '123456', '123456', '13999999999', '', '', 'c01c88b09bfcbd8f5977c199610fe879', '6875299', '', 0, '', '', 1, 'V0', 1, '零级代理', '110.235.220.60', '柬埔寨,柬埔寨', '110.235.220.60', 0, '', 0, '', 0, '', 0, '', 1, 0.00, 0.00, 0.00, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1768485221, 0, 1768481705, 1768485239, '2026-01-15 20:55:05', '2026-01-15 21:53:59');

-- 导出  表 facai11.user_address 结构
CREATE TABLE IF NOT EXISTS `user_address` (
                                              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '0' COMMENT '用户手机号',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `address_name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
    `address_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
    `address_city` varchar(255) NOT NULL DEFAULT '' COMMENT '省市区',
    `address_place` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
    `default` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否默认 0默认1不默认',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `username` (`username`),
    KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户地址表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.user_address 的数据：~0 rows (大约)
DELETE FROM `user_address`;

-- 导出  表 facai11.user_bank 结构
CREATE TABLE IF NOT EXISTS `user_bank` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0银行卡1数字货币2支付宝3微信',
    `default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认0否1是',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
    `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
    `bank_branch` varchar(60) NOT NULL DEFAULT '' COMMENT '银行支行',
    `bank_account` varchar(50) NOT NULL DEFAULT '' COMMENT '银行账号',
    `coin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '币-名称',
    `coin_blockchain` varchar(50) NOT NULL DEFAULT '' COMMENT '币-区块链',
    `coin_account` varchar(100) NOT NULL DEFAULT '' COMMENT '币-账号',
    `alipay_account` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账号',
    `alipay_img` varchar(200) NOT NULL DEFAULT '' COMMENT '支付宝收款码',
    `wx_img` varchar(50) NOT NULL DEFAULT '' COMMENT '微信收款码',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
    `alipay_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支付宝名称',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户银行表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.user_bank 的数据：~0 rows (大约)
DELETE FROM `user_bank`;

-- 导出  表 facai11.user_coin_address 结构
CREATE TABLE IF NOT EXISTS `user_coin_address` (
                                                   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(10) unsigned NOT NULL,
    `phone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `coin_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `address` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
    `create_time` int(10) unsigned NOT NULL DEFAULT '0',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_uid_coin` (`uid`,`coin_type`),
    KEY `idx_uid` (`uid`)
    ) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  facai11.user_coin_address 的数据：~0 rows (大约)
DELETE FROM `user_coin_address`;
INSERT INTO `user_coin_address` (`id`, `uid`, `phone`, `coin_type`, `address`, `create_time`, `update_time`) VALUES
    (95, 1, '13888888888', '195', 'TRjYLfB5mhXkFkfjAw1ZFe15MWPUwQneWp', 1768481661, 1768481661);

-- 导出  表 facai11.user_login 结构
CREATE TABLE IF NOT EXISTS `user_login` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
    `ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '登入IP',
    `ip_address` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip地址',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '测试账号0正常1测试',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `username` (`username`),
    KEY `phone` (`phone`),
    KEY `ip` (`ip`)
    ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户登入'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.user_login 的数据：~13 rows (大约)
DELETE FROM `user_login`;
INSERT INTO `user_login` (`id`, `uid`, `username`, `phone`, `ip`, `ip_address`, `is_test`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                                       (1, 1, '', '13888888888', '110.235.220.60', '柬埔寨,柬埔寨', 0, 1768482171, 1768482171, '2026-01-15 21:02:51', '2026-01-15 21:02:51'),
                                                                                                                                                       (2, 2, '', '13999999999', '110.235.220.60', '柬埔寨,柬埔寨', 1, 1768483098, 1768483098, '2026-01-15 21:18:18', '2026-01-15 21:18:18'),
                                                                                                                                                       (3, 3, '', '18888888888', '14.207.167.98', '泰国,泰国', 1, 1768483396, 1768483396, '2026-01-15 21:23:16', '2026-01-15 21:23:16'),
                                                                                                                                                       (4, 2, '', '13999999999', '110.235.220.60', '柬埔寨,柬埔寨', 1, 1768483493, 1768483493, '2026-01-15 21:24:53', '2026-01-15 21:24:53'),
                                                                                                                                                       (5, 3, '', '18888888888', '14.207.167.98', '泰国,泰国', 1, 1768483954, 1768483954, '2026-01-15 21:32:34', '2026-01-15 21:32:34'),
                                                                                                                                                       (6, 4, '17777777777', '17777777777', '14.207.167.98', '泰国,泰国', 0, 1768484436, 1768484436, '2026-01-15 21:40:36', '2026-01-15 21:40:36'),
                                                                                                                                                       (7, 4, '17777777777', '17777777777', '14.207.167.98', '泰国,泰国', 0, 1768484443, 1768484443, '2026-01-15 21:40:43', '2026-01-15 21:40:43'),
                                                                                                                                                       (8, 3, '', '18888888888', '110.235.220.60', '柬埔寨,柬埔寨', 1, 1768484662, 1768484662, '2026-01-15 21:44:22', '2026-01-15 21:44:22'),
                                                                                                                                                       (9, 5, '13777777777', '13777777777', '110.235.220.60', '柬埔寨,柬埔寨', 0, 1768484764, 1768484764, '2026-01-15 21:46:05', '2026-01-15 21:46:05'),
                                                                                                                                                       (10, 5, '13777777777', '13777777777', '110.235.220.60', '柬埔寨,柬埔寨', 0, 1768484789, 1768484789, '2026-01-15 21:46:29', '2026-01-15 21:46:29'),
                                                                                                                                                       (11, 1, '', '13888888888', '110.235.220.60', '柬埔寨,柬埔寨', 0, 1768485145, 1768485145, '2026-01-15 21:52:25', '2026-01-15 21:52:25'),
                                                                                                                                                       (12, 2, '', '13999999999', '110.235.220.60', '柬埔寨,柬埔寨', 1, 1768485221, 1768485221, '2026-01-15 21:53:41', '2026-01-15 21:53:41'),
                                                                                                                                                       (13, 1, '', '13888888888', '110.235.220.60', '柬埔寨,柬埔寨', 0, 1768485674, 1768485674, '2026-01-15 22:01:14', '2026-01-15 22:01:14');

-- 导出  表 facai11.user_relation 结构
CREATE TABLE IF NOT EXISTS `user_relation` (
                                               `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '层级',
    `top_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试账号 0正常 1测试',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`) USING BTREE,
    KEY `idx_uid` (`uid`),
    KEY `idx_top_id` (`top_id`),
    KEY `idx_uid_top` (`uid`,`top_id`),
    KEY `idx_uid_level` (`uid`,`level`)
    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户层级'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.user_relation 的数据：~0 rows (大约)
DELETE FROM `user_relation`;
INSERT INTO `user_relation` (`id`, `uid`, `level`, `top_id`, `is_test`, `create_time`, `update_time`, `create_at`, `update_at`) VALUES
                                                                                                                                    (1, 1, 0, 0, 0, 1768481498, 1768481498, '2026-01-15 20:51:38', '2026-01-15 20:51:38'),
                                                                                                                                    (2, 2, 0, 0, 0, 1768481705, 1768481705, '2026-01-15 20:55:05', '2026-01-15 20:55:05'),
                                                                                                                                    (3, 3, 0, 0, 0, 1768483373, 1768483373, '2026-01-15 21:22:53', '2026-01-15 21:22:53'),
                                                                                                                                    (4, 4, 1, 3, 0, 1768484436, 1768484436, '2026-01-15 21:40:36', '2026-01-15 21:40:36'),
                                                                                                                                    (5, 4, 1, 0, 0, 1768484436, 1768484436, '2026-01-15 21:40:36', '2026-01-15 21:40:36'),
                                                                                                                                    (6, 5, 1, 2, 0, 1768484764, 1768484764, '2026-01-15 21:46:05', '2026-01-15 21:46:05'),
                                                                                                                                    (7, 5, 1, 0, 0, 1768484764, 1768484764, '2026-01-15 21:46:05', '2026-01-15 21:46:05');

-- 导出  表 facai11.withdraw 结构
CREATE TABLE IF NOT EXISTS `withdraw` (
                                          `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单id',
    `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0成功1审核2拒绝',
    `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1银行卡2数字货币3支付宝4微信',
    `name` varchar(60) NOT NULL DEFAULT '' COMMENT '姓名',
    `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
    `bank_branch` varchar(60) NOT NULL DEFAULT '' COMMENT '银行支行',
    `bank_account` varchar(30) NOT NULL DEFAULT '' COMMENT '银行账号',
    `coin_name` varchar(30) NOT NULL DEFAULT '' COMMENT '币名称',
    `coin_blockchain` varchar(30) NOT NULL DEFAULT '' COMMENT '币区块链',
    `coin_account` varchar(50) NOT NULL DEFAULT '' COMMENT '币账号',
    `alipay_account` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账号',
    `alipay_img` varchar(200) NOT NULL DEFAULT '' COMMENT '支付宝收款',
    `wx_img` varchar(50) NOT NULL DEFAULT '' COMMENT '微信收款码',
    `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
    `amount_real` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '实际金额',
    `exchange_rate` decimal(20,2) NOT NULL DEFAULT '1.00' COMMENT '汇率',
    `handling_fee` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
    `handling_rate` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '手续费率',
    `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
    `passage_time` int(11) NOT NULL DEFAULT '0' COMMENT '通过时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '充值利率',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `order_no` (`order_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='提现表'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.withdraw 的数据：~0 rows (大约)
DELETE FROM `withdraw`;

-- 导出  表 facai11.yuebao 结构
CREATE TABLE IF NOT EXISTS `yuebao` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名称',
    `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号码',
    `is_test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否测试0正常1测试',
    `type` int(11) NOT NULL DEFAULT '1' COMMENT '1存2取3收益',
    `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `finish_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '完结金额',
    `info` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    `settle_time` int(11) NOT NULL DEFAULT '0' COMMENT '结算时间',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`,`uid`),
    KEY `uid` (`uid`),
    KEY `idx_type_finish_settle` (`type`,`finish_money`,`settle_time`),
    KEY `idx_uid_type_finish` (`uid`,`type`,`finish_money`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='余额宝'
/*!50100 PARTITION BY HASH (`uid`)
PARTITIONS 32 */;

-- 正在导出表  facai11.yuebao 的数据：~0 rows (大约)
DELETE FROM `yuebao`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
