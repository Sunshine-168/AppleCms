-- LaraVideo 默认分类 / 播放器。空库可导入；已有数据请跳过。
INSERT INTO video_types (id, parent_id, name, slug, sort, status, created_at, updated_at) VALUES
(1, 0, '电影', 'movie', 100, 1, 0, 0),
(2, 0, '电视剧', 'tv', 90, 1, 0, 0),
(3, 0, '综艺', 'show', 80, 1, 0, 0),
(4, 0, '动漫', 'anime', 70, 1, 0, 0);

INSERT INTO video_players (code, name, parse, sort, status) VALUES
('dplayer', '直链播放', '', 10, 1),
('parse', '解析接口', '', 0, 1);
