USE seoforbeginners;
TRUNCATE pr_action_group_relation;
TRUNCATE pr_bi_group_relation;
TRUNCATE pr_blockfile;
TRUNCATE pr_blockitem;
TRUNCATE pr_blockitem_regxurl;
TRUNCATE pr_blockitem_settings;
TRUNCATE pr_comp_oicomment;
TRUNCATE pr_comp_oicomment_bi;
TRUNCATE pr_comp_oicomment_prop;
TRUNCATE pr_comp_objItem;
TRUNCATE pr_comp_objItem_prop;
TRUNCATE pr_comp_artlast;
TRUNCATE pr_comp_artlast_prop;
TRUNCATE pr_comp_artlist;
TRUNCATE pr_comp_artlist_prop;
TRUNCATE pr_comp_artpopular;
TRUNCATE pr_comp_artpopular_prop;
TRUNCATE pr_comp_breadcrumbs;
TRUNCATE pr_comp_catalogcont;
TRUNCATE pr_comp_catalogcont_prop;

TRUNCATE pr_comp_menu_tree;
INSERT INTO pr_comp_menu_tree (id, tree_id, `name`) VALUES (1, 1, 'root'),(2,1, '');
UPDATE pr_comp_menu_tree SET id = 0 WHERE id = 2;
UPDATE pr_comp_menu_tree SET tree_id = 0, `name` = 'root' WHERE id = 0;
DELETE FROM pr_comp_menu_tree WHERE id = 1;

TRUNCATE pr_comp_prop;

TRUNCATE pr_compcont_tree;
INSERT INTO pr_compcont_tree (id, tree_id, `name`) VALUES (1, 1, 'root'), (2, 1, '');
UPDATE pr_compcont_tree SET id = 0 WHERE id = 2;
UPDATE pr_compcont_tree SET tree_id = 0, `name` = 'root' WHERE id = 0; 
DELETE FROM pr_compcont_tree WHERE id = 1;

update pr_component_tree set id = 0 where `name` = 'root';

TRUNCATE pr_cont_file;
TRUNCATE pr_event_buffer;
TRUNCATE pr_imgsize_list;
TRUNCATE pr_sitemaps;
TRUNCATE pr_url_tpl_list;

TRUNCATE pr_url_tree;
INSERT INTO pr_url_tree (id, tree_id, `name`) VALUES (1, 1, 'root'), (2, 1, '');
UPDATE pr_url_tree SET id = 0 WHERE id = 2;
UPDATE pr_url_tree SET tree_id = 0, `name` = 'root' WHERE id = 0; 
DELETE FROM pr_url_tree WHERE id = 1;

TRUNCATE pr_urltree_prop_var;

TRUNCATE pr_user_group;
INSERT INTO pr_user_group (id, tree_id, `name`) VALUES (1, 1, 'root'), (2, 1, '');
UPDATE pr_user_group SET id = 0 WHERE id = 2;
UPDATE pr_user_group SET tree_id = 0, `name` = 'root' WHERE id = 0; 
DELETE FROM pr_user_group WHERE id = 1;

TRUNCATE pr_user_group_relation;
TRUNCATE pr_users;
TRUNCATE pr_utils_rss_prop;
TRUNCATE pr_utils_rss;
TRUNCATE pr_utils_seo;
TRUNCATE pr_var_comp;
TRUNCATE pr_var_tree;
TRUNCATE pr_wareframe_tree;

TRUNCATE pr_wareframe_tree;
INSERT INTO pr_wareframe_tree (id, tree_id, `name`) VALUES (1, 1, 'root'), (2, 1, '');
UPDATE pr_wareframe_tree SET id = 0 WHERE id = 2;
UPDATE pr_wareframe_tree SET tree_id = 0, `name` = 'root' WHERE id = 0; 
DELETE FROM pr_wareframe_tree WHERE id = 1;

