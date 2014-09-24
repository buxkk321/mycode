DELIMITER $$

drop trigger if exists `tp_menu_set_deep_after_insert`$$
CREATE TRIGGER `tp_menu_set_deep_after_insert` AFTER INSERT ON `tp_menu` FOR EACH ROW
begin
if new.pid is not null then  
	update tp_menu t1,tp_menu t2 set t1.deep=t2.deep+1 where t1.id=new.id and t2.id=new.pid; 
end if;
end$$