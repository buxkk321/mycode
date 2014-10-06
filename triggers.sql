DELIMITER $$

drop trigger if exists `menu_set_deep_before_insert`$$
CREATE TRIGGER `menu_set_deep_before_insert` BEFORE INSERT ON `tp_menu` FOR EACH ROW
begin
if new.pid is not null then  
	SET new.deep=(SELECT deep FROM tp_menu WHERE id=NEW.pid)+1; 
end if;
end$$

drop trigger if exists `menu_set_deep_before_update`$$
CREATE TRIGGER `menu_set_deep_before_update` BEFORE UPDATE ON `tp_menu` FOR EACH ROW
begin
if new.pid!=old.pid and new.pid is not null then  
	SET new.deep=(SELECT deep FROM tp_menu WHERE id=NEW.pid)+1; 
end if;
end$$

drop trigger if exists article_delete$$
create trigger article_delete BEFORE delete on tp_article for each row   
begin
DELETE FROM `tp_article_text` WHERE doc_id=Old.id;
end$$