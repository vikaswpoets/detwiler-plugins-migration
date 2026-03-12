DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateAttributeProperties`(p_attr varchar(100),p_termid bigint,p_option varchar(200) )
BEGIN

declare v_fname,v_fvalue text;
declare v_new_meta_id,v_count bigint;
DECLARE done INT DEFAULT FALSE;
DECLARE attributes_cur CURSOR FOR select field_name,field_value from attributefields where attribute=p_attr;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;



DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR proc CreateAttributeProperties : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 

OPEN attributes_cur;
cur:
loop 
FETCH attributes_cur INTO v_fname,v_fvalue;

	set @query=replace(v_fvalue,'#val',p_option);
    


    PREPARE stmt1 FROM @query  ; 
	EXECUTE stmt1; 
	DEALLOCATE PREPARE stmt1;
        
    set v_fvalue=@v_fvalue;
    


select count(1) into v_count from cportal.ws_termmeta  where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci;
if(v_count=0) then
begin
	select max(meta_id) + 1 into v_new_meta_id  from cportal.ws_termmeta;

	insert into cportal.ws_termmeta (meta_id,term_id,meta_key,meta_value) values(v_new_meta_id,p_termid,v_fname,v_fvalue); 
end;
else
begin
	update cportal.ws_termmeta set meta_value=v_fvalue where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci; 
end;
end if;
if done then 
	leave cur; 
end if;
end loop;

CLOSE attributes_cur;   

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateAttributeProperties_v6`(p_attr varchar(100),p_termid bigint,p_option varchar(200),v_log bit )
BEGIN

declare v_fname,v_fvalue text;
declare v_new_meta_id,v_count bigint;
DECLARE done INT DEFAULT FALSE;
DECLARE attributes_cur CURSOR FOR select field_name,field_value from attributefields where attribute=p_attr;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;



DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR proc CreateAttributeProperties_v6 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
if( select count(1) from attributefields where attribute=p_attr)>0 then
begin

OPEN attributes_cur;
cur:
loop 
FETCH attributes_cur INTO v_fname,v_fvalue;

	if v_log=1 then  insert into temp_debug(value) values(concat('CreateAttributeProperties_v6 -> f_name:',v_fname,' f_value:', v_fvalue));
	end if; 
    
	set @query=replace(v_fvalue,'#val',p_option);
    
	if v_log=1 then  insert into temp_debug(value) values(concat('CreateAttributeProperties_v6 -> f_name:',v_fname,' f_value:', v_fvalue));
	end if; 
    
    PREPARE stmt1 FROM @query  ; 
	EXECUTE stmt1; 
	DEALLOCATE PREPARE stmt1;
        
    set v_fvalue=@v_fvalue;
    
    insert into temp_debug(value) values(concat('after prepare::: f_name:',v_fname,' f_value:', v_fvalue));

select count(1) into v_count from cportal.ws_termmeta  where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci;
if(v_count=0) then
begin
	select max(meta_id) + 1 into v_new_meta_id  from cportal.ws_termmeta;

	if v_log=1 then  insert into temp_debug(value) values(concat('CreateAttributeProperties_v6 -> creating meta key meta_id:', v_new_meta_id,' with value:',v_fvalue));
	end if; 

	insert into cportal.ws_termmeta (meta_id,term_id,meta_key,meta_value) values(v_new_meta_id,p_termid,v_fname,v_fvalue); 
end;
else
begin
	if v_log=1 then  insert into temp_debug(value) values(concat('CreateAttributeProperties_v6 -> update meta key:', v_fname,' with value:',v_fvalue));
	end if; 

	update cportal.ws_termmeta set meta_value=v_fvalue where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci; 
end;
end if;
if done then 
	leave cur; 
end if;
end loop;

CLOSE attributes_cur;   

end;

end if;
	if v_log=1 then  insert into temp_debug(value) values(concat('CreateAttributeProperties_v6 -> leaving'));
	end if; 

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateOrReplaceAttribute`(p_attr varchar(200),p_type varchar(200),p_value  varchar(200),p_lang varchar(5),out result bigint)
BEGIN


declare v_term_taxonomy_id,v_term_id,v_next_trid,v_count_trans,v_aux,v_trid,v_new_meta_id,v_metaid,v_res,v_total_options,v_optionid int;
declare v_attr,v_slug,v_fname,v_fvalue,v_options text;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR PROCEDURE CreateOrReplaceAttribute : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 
 


select option_id into v_optionid from cportal.ws_options where option_value like concat('%','s:15:"attribute_label";s:',length(p_attr),':"',p_attr,'";%') collate utf8mb4_unicode_ci and   option_name='_transient_wc_attribute_taxonomies'; 

if v_optionid is null then
begin
	
    select option_value into v_options from cportal.ws_options where option_name='_transient_wc_attribute_taxonomies'; 
    select substring(v_options,3,POSITION(':' in substring(v_options,2,length(v_options)-2))) into v_total_options; 
    set v_total_options=v_total_options+1;
    set v_options=concat('a:',v_total_options,
    substring(v_options,3+POSITION(':' in substring(v_options,2,length(v_options)-2)),length(v_options)-4+length(v_total_options))); 

	
    set v_options=concat(v_options,'{i:',v_total_options-1,';O:8:"stdClass":6:{s:12:"attribute_id";s:',length(v_total_options-1),':"',v_total_options-1,'";s:14:"attribute_name";s:',
    length(replace(p_attr,' ','-')),':"',replace(p_attr,' ','-'),'";s:15:"attribute_label";s:',
    length(p_attr),':"',p_attr,'";s:14:"attribute_type";s:6:"select";s:17:"attribute_orderby";s:10:"menu_order";s:16:"attribute_public";s:1:"0";}','}');
    
	update cportal.ws_options 
    set option_value=v_options
    where option_name='_transient_wc_attribute_taxonomies'; 
end;
end if;



	select tx.term_id,term_taxonomy_id,ter.slug into v_term_id,v_term_taxonomy_id,v_slug
	from cportal.ws_term_taxonomy tx 
	inner join cportal.ws_terms ter on ter.term_id=tx.term_id
	inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
	where tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
    and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_',p_lang))  collate utf8mb4_unicode_ci 
    and icl.language_code=p_lang collate utf8mb4_unicode_ci  
    and case when p_lang='en' then icl.source_language_code  is null else icl.source_language_code ='en' collate utf8mb4_unicode_ci end
    and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci 
    and ter.name=p_value collate utf8mb4_unicode_ci 
    ; 


if(v_term_id is null) then
begin

    
	select max(term_id)+1 into v_term_id from cportal.ws_terms;
	insert into cportal.ws_terms(`term_id`,`name`,`slug`) values(v_term_id,p_value,concat(replace(p_value,' ','-'),'_',p_lang));
	
    select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy; 
	insert INTO cportal.ws_term_taxonomy(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`) values( v_term_taxonomy_id,v_term_id,p_type,'',0,0) ;          
    
    
    if(p_lang<>'en') then
    begin
    

    
		select icl.trid into v_trid
		from cportal.ws_term_taxonomy tx 
		inner join cportal.ws_terms ter on ter.term_id=tx.term_id
		inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
		where 
        tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
        and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_en'))  collate utf8mb4_unicode_ci 
        and icl.language_code='en'  collate utf8mb4_unicode_ci  
        and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
        limit 1; 
        
         
        if (v_trid>0) then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        else
        begin
			
             insert into temp_debug(value) values(concat('CreateOrReplaceAttribute -> creating en version:'));
            call CreateOrReplaceAttribute(p_attr,p_type,p_value,'en',@v_attr);

            set v_res=@v_attr;
            
           
			
            
			select icl.trid into v_trid
			from cportal.ws_term_taxonomy tx 
			inner join cportal.ws_terms ter on ter.term_id=tx.term_id
			inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
			where 
			tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
			and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_en'))  collate utf8mb4_unicode_ci 
			and icl.language_code='en'  collate utf8mb4_unicode_ci  
			and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
			limit 1; 
            
            insert into temp_debug(value) values(concat('CreateOrReplaceAttribute -> creating translation version v_trid:',v_trid,' lang:',p_lang));
            
            insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        end if;    
    end;
    else
    begin

        select max(trid)+1 into v_trid from cportal.ws_icl_translations;
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, 'en',null);        
    end;
    end if;


	
	
	call CreateAttributeProperties(p_attr,v_term_id,p_value);

end;
end if;

 insert into temp_debug(value) values('CreateOrReplaceAttribute -> leaving');

set result=v_term_id;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateOrReplaceAttribute_v6`(p_attr varchar(200),p_type varchar(200),p_value  varchar(200),p_lang varchar(5),v_log bit,out result bigint)
BEGIN


declare v_term_taxonomy_id,v_term_id,v_next_trid,v_count_trans,v_aux,v_trid,v_new_meta_id,v_metaid,v_res,v_total_options,v_optionid int;
declare v_attr,v_slug,v_fname,v_fvalue,v_options text;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR PROCEDURE CreateOrReplaceAttribute_v6 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 set p_type=lower(replace(p_type,' ','-'));
 
 if v_log=1 then insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6## p_attr:',ifnull(p_attr,'N/A'),' p_type:',ifnull(p_type,'N/A'),' p_value:',ifnull(p_value,'N/A'),'p_lang:',ifnull(p_lang,'N/A')));
 end if;
 


select option_id into v_optionid from cportal.ws_options where option_value like concat('%','s:15:"attribute_label";s:',length(p_attr),':"',p_attr,'";%') collate utf8mb4_unicode_ci and   option_name='_transient_wc_attribute_taxonomies'; 

if v_optionid is null then
begin
	 if v_log=1 then 
	 insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> option not exists, creating option:',ifnull(p_attr,'N/A')));
     end if;  
     
     INSERT INTO cportal.`wp_woocommerce_attribute_taxonomies` (`attribute_label`, `attribute_name`, `attribute_type`, `attribute_orderby`, `attribute_public`) VALUES (p_attr,replace( p_attr,' ','-'), 'select', 'menu_order', 0);
     
	
    select option_value into v_options from cportal.ws_options where option_name='_transient_wc_attribute_taxonomies'; 
    select substring(v_options,3,POSITION(':' in substring(v_options,2,length(v_options)-2))) into v_total_options; 
    set v_total_options=v_total_options+1;
    set v_options=concat('a:',v_total_options,
    substring(v_options,3+POSITION(':' in substring(v_options,2,length(v_options)-2)),length(v_options)-4+length(v_total_options))); 

	
    set v_options=concat(v_options,'{i:',v_total_options-1,';O:8:"stdClass":6:{s:12:"attribute_id";s:',length(v_total_options-1),':"',v_total_options-1,'";s:14:"attribute_name";s:',
    length(replace(p_attr,' ','-')),':"',replace(p_attr,' ','-'),'";s:15:"attribute_label";s:',
    length(p_attr),':"',p_attr,'";s:14:"attribute_type";s:6:"select";s:17:"attribute_orderby";s:10:"menu_order";s:16:"attribute_public";s:1:"0";}','}');
    
	update cportal.ws_options 
    set option_value=v_options
    where option_name='_transient_wc_attribute_taxonomies'; 
end;
end if;


	select tx.term_id,term_taxonomy_id,ter.slug into v_term_id,v_term_taxonomy_id,v_slug
	from cportal.ws_term_taxonomy tx 
	inner join cportal.ws_terms ter on ter.term_id=tx.term_id
	inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
	where tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
    and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_',p_lang))  collate utf8mb4_unicode_ci 
    and icl.language_code=p_lang collate utf8mb4_unicode_ci  
    and case when p_lang='en' then icl.source_language_code  is null else icl.source_language_code ='en' collate utf8mb4_unicode_ci end
    and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci 
    and ter.name=p_value collate utf8mb4_unicode_ci; 
    
	 if v_log=1 then 
	 insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> term_id:',ifnull(v_term_id,'N/A')));
     end if;    


if(v_term_id is null) then
begin

	if v_log=1 then  insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> attribute value not found'));
	end if;

    
	select max(term_id)+1 into v_term_id from cportal.ws_terms;
	insert into cportal.ws_terms(`term_id`,`name`,`slug`) values(v_term_id,p_value,lower(concat(replace(p_value,' ','-'),'_',p_lang))); 
	
    select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy; 
	insert INTO cportal.ws_term_taxonomy(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`) values( v_term_taxonomy_id,v_term_id,p_type,'',0,0) ;          
    
    
    if(p_lang<>'en') then
    begin
    
		 if v_log=1 then 
		 insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> lang:',p_lang,' taxonomy:',concat('pa_',replace(p_attr,' ','-')),' slug:',lower(concat(replace(p_value,' ','-'),'_en')),' ele type:',concat('tax_','pa_',replace(p_attr,' ','-')) ));
		 end if; 
    
		select icl.trid into v_trid
		from cportal.ws_term_taxonomy tx 
		inner join cportal.ws_terms ter on ter.term_id=tx.term_id
		inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
		where 
        tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
        and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_en'))  collate utf8mb4_unicode_ci 
        and icl.language_code='en'  collate utf8mb4_unicode_ci  
        and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
        limit 1; 
        
         
        if (ifnull(v_trid,0)>0) then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        else
        begin
			
			if v_log=1 then  insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> creating en version'));
			end if;            

            call CreateOrReplaceAttribute_v6(p_attr,p_type,p_value,'en',1,@v_attr);

            set v_res=@v_attr;
            if v_log=1 then  
			insert into temp_debug(value) values(concat('inside CreateOrReplaceAttribute_v6 end of create en version trid:',ifnull(@v_attr,'N/A'),' p_attr:',ifnull(p_attr,'N/A'),' p_value:',ifnull(p_value,'N/A')));
			end if;
            
			select icl.trid into v_trid
			from cportal.ws_term_taxonomy tx 
			inner join cportal.ws_terms ter on ter.term_id=tx.term_id
			inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
			where 
			tx.taxonomy =concat('pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
			and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_en'))  collate utf8mb4_unicode_ci 
			and icl.language_code='en'  collate utf8mb4_unicode_ci  
			and icl.element_type=concat('tax_','pa_',replace(p_attr,' ','-')) collate utf8mb4_unicode_ci  
			limit 1; 
            

			if v_log=1 then  insert into temp_debug(value) values(concat('CreateOrReplaceAttribute_v6 -> creating translation version v_trid:',ifnull(v_trid,0),' lang:',p_lang));
			end if;                  
            
            insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        end if;    
    end;
    else
    begin

        select max(trid)+1 into v_trid from cportal.ws_icl_translations;
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, 'en',null);        
    end;
    end if;

	if v_log=1 then  insert into temp_debug(value) values('CreateOrReplaceAttribute_v6 - > Create attribute properties');
	end if;  
	call CreateAttributeProperties_v6(p_attr,v_term_id,p_value,v_log);

end;
end if;

 if v_log=1 then insert into temp_debug(value) values('CreateOrReplaceAttribute_v6 -> leaving');
 end if;
 

set result=v_term_id;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `createMainPost_v6`(p_post_json longtext,p_parent_id bigint,p_lang varchar(2),v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_post_title text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_variations,v_current_var,v_price text;
declare v_attribute varchar(200);

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr,cur_finished,v_counter,v_count_var int;
declare v_next_trid,v_count_trans,v_trid_main_post int;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function createMainPost_v6 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET cur_finished = 1;

 
 if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> parameters # p_parent_id:: ',ifnull(p_parent_id,0),' lang:',p_lang,' p_post_json:',ifnull(p_post_json,'')));
end if;


begin

select JSON_UNQUOTE(json_extract(p_post_json,'$.name')) into v_post_title; 

select JSON_UNQUOTE(json_extract(p_post_json,'$.type')) into v_product_type; 

select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pim_id; 

select JSON_UNQUOTE(json_extract(p_post_json,'$.status')) into v_status; 

select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 

select getproperty_v5(p_post_json,'Main Text',p_lang,v_log) into v_post_excerpt;



 select getsubsubcategory_v5(p_post_json,p_lang,v_log) into v_subsubcategory;
 select getsubcategory_v5(p_post_json,p_lang,v_log) into v_subcategory;
 select getcategory_v5(p_post_json,p_lang,v_log) into v_category;

end;


select i.postid into v_postid from imported_ids i where pimid=v_pim_id and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;

if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> postid: ',ifnull(v_postid,0),' lang:',p_lang,' pim_id:',ifnull(v_pim_id,0)));
end if;

if v_postid>0 then 
begin


update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content=ifnull(v_post_content,'') ,
	post_title=ifnull(v_post_title,'') ,
	post_excerpt=ifnull(v_post_excerpt,'') ,
	post_status= case when v_status='Enabled' then 'publish' else 'draft' end,
	post_name=replace(ifnull(v_post_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('/product/',LOWER(replace(ifnull(v_post_title,''),' ','-')),'/')
    where id=     v_postid ;

if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> updated post: ',v_postid,' lang:',p_lang));
end if;


set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
    insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
 end if;

update import_pim
set json=p_post_json,
title=ifnull(v_post_title,''),
subtitle=ifnull(v_post_excerpt,''),
product_type=v_product_type,
maintext=ifnull(v_post_content,''),
category=v_category,
subcategory=v_subcategory,
wp_id=v_postid
where id=v_pim_id;
    
end;
else
begin


	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	ifnull(v_post_content,'') as post_content,
	ifnull(v_post_title,'') post_title,
	ifnull(v_post_excerpt,'') as post_excerpt,
	case when v_status='Enabled' then 'publish' else 'draft' end as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_post_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	0 as post_parent,
	concat('/product/',LOWER(replace(ifnull(v_post_title,''),' ','-')),'/') as guid,
	0 as menu_order,
	'product' as post_type,
	'' as post_mime_type,
	0 as comment_count;

if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> inserted new post: ',v_postid));
end if;


insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;


if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> Define translation'));
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid; 

	if v_log=1 then    insert into temp_debug(value) values(concat('createMainPost_v6 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang));
	end if;    

	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations; 
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
        
	if v_count_trans >0 then  
    begin
		
		if(select count(*)  from cportal.ws_icl_translations where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
			begin
				insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
				values('post_product',v_postid,  v_trid_main_post, p_lang,'en');
			end;
			end if;
	end;
    end if;
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> Translation defined'));
end if;



   select createcategorytree_v5(v_category,v_subcategory,v_subsubcategory  ,v_postid,p_lang,v_log)  into v_term_taxonomy_id;






if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> set product type:',v_product_type));
end if;

 call createtaxonomy_v5(case v_product_type when 'Product' then 'variable' else 'grouped' end ,'product_type',v_postid,v_log);


if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> iterating attributes'));
end if;
set cur_finished=0;


select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.variations')) into v_variations ;

	set v_count_var=json_length(v_variations);
	set v_counter=0;

	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));
		call sp_CreatePostVariation_v7(v_current_var ,v_postid , p_lang,v_price , v_log );
        set v_counter=v_counter+1;
	end while;


if v_log=1 then insert into temp_debug(value) values(concat('createMainPost_v6 -> #################### set product as imported for further updates'));
end if;


insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);


set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `createVariationOnAttribute`(p_attribute varchar(100),p_parent_id bigint,p_lang varchar(100),out postid bigint)
BEGIN

declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_title text; 
declare v_subcategory,v_category,v_status,v_subsubcategory text;

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr int;
declare v_next_trid,v_count_trans,v_trid_main_post int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure createVariation : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;
 
set v_postid=0;
select postid into v_postid from imported_ids where v_pim_id=pimid and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;
select id into v_postid from cportal.ws_posts p where post_type='product_variation' and post_excerpt=ifnull(p_attribute,'') collate utf8mb4_unicode_ci  and post_parent=p_parent_id limit 1;
select post_title into v_title from cportal.ws_posts where id=p_parent_id;

if v_postid>0 then 
begin


update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content='' ,
	post_title=ifnull(v_title,'') ,
	post_excerpt=ifnull(p_attribute,'') ,
	post_status= 'publish' ,
	post_name=replace(ifnull(v_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('?post_type=product_variation&p=',p_parent_id),
    post_parent=p_parent_id
    where id=     v_postid ;

insert into temp_debug(value) values(concat('createVariation -> updated post: ',v_postid,' lang:',p_lang));


set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
    insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
 end if;

update import_pim
set json=p_post_json,
title=ifnull(v_post_title,''),
subtitle=ifnull(v_post_excerpt,''),
product_type=v_product_type,
maintext=ifnull(v_post_content,''),
category=v_category,
subcategory=v_subcategory,
wp_id=v_postid
where id=v_pim_id;
    
end;
else
begin


	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	'' as post_content,
	ifnull(v_title,'') post_title,
	ifnull(p_attribute,'') as post_excerpt,
	'publish' as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	p_parent_id as post_parent,
	concat('?post_type=product_variation&p=',p_parent_id) as guid,
	0 as menu_order,
	'product_variation' as post_type,
	'' as post_mime_type,
	0 as comment_count;

insert into temp_debug(value) values(concat('createVariation -> inserted new post: ',v_postid));


insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid; 
        
    insert into temp_debug(value) values(concat('createVariation -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang));
    
	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations where language_code = p_lang  collate utf8mb4_unicode_ci;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product_variation',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
    

    
	if v_count_trans >0 then  
    begin
    
    if
		(select count(*)  from cportal.ws_icl_translations 
		where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values('post_product_variation',v_postid,  v_trid_main_post, p_lang,'en');
        end;
        end if;
	end;
    end if;
end;
end if;




insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);

insert into temp_debug(value) values(concat('createVariationOnAttribute-> Insert wp_postmeta for variation post:',v_postid));
insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,null from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_variation=1 and wp_pm.post_id is null;

set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `createVariationOnAttribute_v5`(p_attribute varchar(100),p_term_taxonomy_id bigint,p_parent_id bigint,p_lang varchar(100),v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_title text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_term_name,v_term_slug text;

declare v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr int;
declare v_next_trid,v_count_trans,v_trid_main_post int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure createVariationOnAttribute_v5 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;
 
set v_postid=0;


select ter.slug,ter.name into v_term_slug,v_term_name from cportal.ws_term_taxonomy tx inner join cportal.ws_terms ter on ter.term_id=tx.term_id where tx.term_taxonomy_id=p_term_taxonomy_id;

if v_log=1 then 
	insert into temp_debug(value) values(concat('createVariationOnAttribute_v5 -> v_term_name: ',ifnull(v_term_name,''),' v_term_slug:',ifnull(v_term_slug,''),' p_term_taxonomy_id:',p_term_taxonomy_id));
end if;

select id into v_postid from cportal.ws_posts p where post_type='product_variation' and post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) collate utf8mb4_unicode_ci  and post_parent=p_parent_id limit 1;
select post_title into v_title from cportal.ws_posts where id=p_parent_id;

if v_postid>0 then 
begin


update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content='' ,
	post_title=ifnull(v_title,'') ,
	post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) ,
	post_status= 'publish' ,
	post_name=replace(ifnull(v_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('?post_type=product_variation&p=',p_parent_id),
    post_parent=p_parent_id
    where id=     v_postid ;

if v_log=1 then 
	insert into temp_debug(value) values(concat('createVariationOnAttribute_v5 -> updated post: ',v_postid,' lang:',p_lang));
end if;

set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
    insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
 end if;

update import_pim
set json='',
title=ifnull(v_title,''),
subtitle=ifnull(v_post_excerpt,''),
product_type=v_product_type,
maintext=ifnull(v_post_content,''),
category=v_category,
subcategory=v_subcategory,
wp_id=v_postid
where id=v_pim_id;
    
end;
else
begin


	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	'' as post_content,
	ifnull(v_title,'') post_title,
	concat(ifnull(p_attribute,''),': ',v_term_name)  as post_excerpt,
	'publish' as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	p_parent_id as post_parent,
	concat('?post_type=product_variation&p=',p_parent_id) as guid,
	0 as menu_order,
	'product_variation' as post_type,
	'' as post_mime_type,
	0 as comment_count;


if v_log=1 then 
	insert into temp_debug(value) values(concat('createVariationOnAttribute_v5 -> inserted new post: ',v_postid));
end if;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,concat('attribute_pa_',lower(p_attribute)),v_term_slug  from cportal.ws_postmeta  ;    

end;
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid; 
        
    insert into temp_debug(value) values(concat('createVariationOnAttribute_v5 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang));
    
	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations where language_code = p_lang  collate utf8mb4_unicode_ci;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product_variation',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
    

    
	if v_count_trans >0 then  
    begin
    
    if
		(select count(*)  from cportal.ws_icl_translations 
		where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values('post_product_variation',v_postid,  v_trid_main_post, p_lang,'en');
        end;
        end if;
	end;
    end if;
end;
end if;





insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);

if v_log=1 then 
	insert into temp_debug(value) values(concat('createVariationOnAttribute_v5 -> Insert wp_postmeta for variation post:',v_postid));
end if;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_variation=1 and wp_pm.post_id is null;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
values(v_postid,'_price',10),(v_postid,'_regular_price',10);

set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `createtaxonomy_v4`(p_term_name varchar(200),p_taxonomy_type varchar(200),p_object_id int)
BEGIN

declare v_term_id,v_term_taxonomy_id,v_aux int;
DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function createtaxonomy_v4 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;
 
select tx.term_id,term_taxonomy_id into v_term_id,v_term_taxonomy_id 
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id  collate utf8mb4_unicode_ci
where tx.taxonomy =p_taxonomy_type  collate utf8mb4_unicode_ci and name=p_term_name collate utf8mb4_unicode_ci and parent=0  collate utf8mb4_unicode_ci; 

if(v_term_id is null) then 
begin

select max(term_id)+1 into v_term_id from wp_terms;
INSERT INTO cportal.ws_terms
(`term_id`,`name`,`slug`,`term_group`)
values(v_term_id,p_term_name,p_term_name,0); 

select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy;

INSERT INTO cportal.ws_term_taxonomy
(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`)
select v_term_taxonomy_id,v_term_id,p_taxonomy_type,'',0,1; 

end;
end if;

select rel.term_taxonomy_id into v_aux 
from cportal.ws_term_relationships rel inner join 
cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=rel.term_taxonomy_id collate utf8mb4_unicode_ci
where rel.object_id=p_object_id  collate utf8mb4_unicode_ci and rel.term_taxonomy_id=v_term_taxonomy_id  collate utf8mb4_unicode_ci limit 1;

if(v_aux is null) then
begin
	INSERT INTO cportal.ws_term_relationships
	(`object_id`,`term_taxonomy_id`,`term_order`)
	VALUES
	(p_object_id,v_term_taxonomy_id,0);
end;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `createtaxonomy_v5`(p_term_name varchar(200),p_taxonomy_type varchar(200),p_object_id int,v_log bit)
BEGIN

declare v_term_id,v_term_taxonomy_id,v_aux int;
DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function createtaxonomy_v5 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;

if v_log=1 then insert into temp_debug(value) values(concat('createtaxonomy_v5 -> Defining term/taxonomy for term:',ifnull(p_term_name,''),' in post:',p_object_id));
end if;
 
select tx.term_id,term_taxonomy_id into v_term_id,v_term_taxonomy_id 
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id  collate utf8mb4_unicode_ci
where tx.taxonomy =p_taxonomy_type  collate utf8mb4_unicode_ci and name=p_term_name collate utf8mb4_unicode_ci and parent=0  collate utf8mb4_unicode_ci; 

if(v_term_id is null) then 
begin

select max(term_id)+1 into v_term_id from wp_terms;
INSERT INTO cportal.ws_terms
(`term_id`,`name`,`slug`,`term_group`)
values(v_term_id,p_term_name,p_term_name,0); 

select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy;

INSERT INTO cportal.ws_term_taxonomy
(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`)
select v_term_taxonomy_id,v_term_id,p_taxonomy_type,'',0,0; 

end;
end if;

select rel.term_taxonomy_id into v_aux 
from cportal.ws_term_relationships rel inner join 
cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=rel.term_taxonomy_id collate utf8mb4_unicode_ci
where rel.object_id=p_object_id  collate utf8mb4_unicode_ci and rel.term_taxonomy_id=v_term_taxonomy_id  collate utf8mb4_unicode_ci limit 1;

if(v_aux is null) then
begin
	INSERT INTO cportal.ws_term_relationships
	(`object_id`,`term_taxonomy_id`,`term_order`)
	VALUES
	(p_object_id,v_term_taxonomy_id,0);
end;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddComplianceToProduct`(p_postid bigint,p_prop_trans text,v_log bit)
BEGIN

declare v_meta_id,v_count_compliance,v_current_index bigint;
declare v_serialized_compliance,v_current_compliance text;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_AddComplianceToProduct : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
if v_log=1 then insert into temp_debug(value) values(concat('start sp_AddComplianceToProduct p_postid:',p_postid,' p_prop_trans:',p_prop_trans)); end if;
			select fn_AddPostMetaValue_v10(p_postid,'_product_complance','field_655f10b9564ee',v_log) into v_meta_id;
            select ROUND ((LENGTH(p_prop_trans)- LENGTH( REPLACE ( p_prop_trans, ";", "") ) ) / LENGTH(";"))+1 into v_count_compliance;
            set v_current_index=1;
            set v_current_compliance='';
            set v_serialized_compliance='';
            while v_current_index<(v_count_compliance+1) do
				select SUBSTRING_INDEX(p_prop_trans, ";", 1) into v_current_compliance;				
                
                set v_serialized_compliance=concat(v_serialized_compliance,'i:',v_current_index,';s:',length(v_current_compliance),':"',v_current_compliance,'";'); 
				
                set p_prop_trans= trim(replace(p_prop_trans,concat(v_current_compliance,';'),''));
                set v_current_index=v_current_index+1;
            end while;
            if nullif(v_serialized_compliance,'') is not null then
				set v_serialized_compliance=concat('a:',v_count_compliance,':{',v_serialized_compliance,'}');
				select fn_AddPostMetaValue_v10(p_postid,'product_complance',v_serialized_compliance ,v_log) into v_meta_id;
            end if;
            set v_meta_id=0;
            select id into v_meta_id from cportal.ws_posts where post_status='publish' and post_excerpt='product_complance' and post_title='Compliance' and post_content like '%v_current_compliance%';
            if (v_meta_id>0) then
				update cportal.ws_posts set post_content=
				concat(
				substring_index(post_content,'"choices";',1), 
				'"choices";','a:', 
				replace(substring_index(substring_index(post_content,'"choices";',-1),':{',1),'a:','')+1, 
				concat(':{','i:',v_current_index,';s:',length(v_current_compliance),':"',v_current_compliance,'";','i:',v_current_index,';s:',length(v_current_compliance),':"',v_current_compliance,'";'),
				substring(substring_index(post_content,'"choices";',-1),
				length(substring_index(substring_index(post_content,'"choices";',-1),':{',1))+1+2, 
				length(substring_index(post_content,'"choices";',-1)))
				)
				where post_status='publish' and post_excerpt='product_complance' and post_title='Compliance';
            end if;
	if v_log=1 then insert into temp_debug(value) values(concat('end sp_AddComplianceToProduct p_postid:',p_postid,' p_prop_trans:',p_prop_trans)); end if;            
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateAttributeProperties_v10`(p_attr varchar(100),p_termid bigint,p_option varchar(200),v_log bit )
BEGIN

declare v_fname,v_fvalue text;
declare v_new_meta_id,v_count bigint;
DECLARE done INT DEFAULT FALSE;
DECLARE attributes_cur CURSOR FOR select field_name,field_value from attributefields where attribute=p_attr;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;



DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR proc sp_CreateAttributeProperties_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
if( select count(1) from attributefields where attribute=p_attr)>0 then
begin

OPEN attributes_cur;
cur:
loop 
FETCH attributes_cur INTO v_fname,v_fvalue;

	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> f_name:',v_fname,' f_value:', v_fvalue));
	end if; 
    
	set @query=replace(v_fvalue,'#val',p_option);
    
	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> f_name:',v_fname,' f_value:', v_fvalue));
	end if; 
    
    PREPARE stmt1 FROM @query  ; 
	EXECUTE stmt1; 
	DEALLOCATE PREPARE stmt1;
        
    set v_fvalue=@v_fvalue;
    
    insert into temp_debug(value) values(concat('after prepare::: f_name:',v_fname,' f_value:', v_fvalue));

select count(1) into v_count from cportal.ws_termmeta  where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci;
if(v_count=0) then
begin
	select max(meta_id) + 1 into v_new_meta_id  from cportal.ws_termmeta;

	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> creating meta key meta_id:', v_new_meta_id,' with value:',v_fvalue));
	end if; 

	insert into cportal.ws_termmeta (meta_id,term_id,meta_key,meta_value) values(v_new_meta_id,p_termid,v_fname,v_fvalue); 
end;
else
begin
	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> update meta key:', v_fname,' with value:',v_fvalue));
	end if; 

	update cportal.ws_termmeta set meta_value=v_fvalue where term_id=p_termid and meta_key=v_fname collate utf8mb4_unicode_ci; 
end;
end if;
if done then 
	leave cur; 
end if;
end loop;

CLOSE attributes_cur;   

end;
else
begin
if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> no additional properties to add')); end if;
end;
end if;
	
if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateAttributeProperties_v10 -> leaving')); end if; 

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateAttribute_v10`(p_json text,p_postid bigint , p_lang varchar(2), v_log bit)
BEGIN

declare v_properties,v_prop,v_translated_data,v_prop_trans,v_current_trans,v_current_prop text;
declare v_counter,v_count_props,v_translation_counter,v_workcounter,v_prop_id,v_break,v_lang_id,v_term_taxonomy_id,v_res,v_pimid int;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_CreateAttribute_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;

select id into v_lang_id from languages where code=p_lang collate utf8mb4_unicode_ci;

select JSON_UNQUOTE(json_extract(p_json,'$.properties')) into v_properties;

select JSON_UNQUOTE(json_extract(p_json,'$.id')) into v_pimid;

set v_count_props=json_length(v_properties);
set v_counter=0;


while v_counter<v_count_props do

	
	set v_current_prop=json_extract(v_properties,concat('$[',v_counter,']'));

	select JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.property')) into v_prop;

    set v_prop_id=JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.id')); 

	set v_workcounter=0;    
	set v_translation_counter=0;
    set v_translated_data='';
        
    set v_prop_trans=JSON_UNQUOTE(json_extract(p_json,'$.property_translations'));
    set v_break=0;
    while v_translation_counter<json_length(v_prop_trans) and v_break=0 do
		set v_translated_data='';
		set v_current_trans=json_extract(v_prop_trans,concat('$[',v_translation_counter,']'));            
        if(json_extract(v_current_trans,'$.language_id')=v_lang_id and json_extract(v_current_trans,'$.property_id')=v_prop_id) then
			begin					
				set v_translated_data=JSON_UNQUOTE(json_extract(v_current_trans,'$.translation_value'));

				if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateAttribute_v10 -> got property: ',v_prop,' translated value:',v_translated_data)); end if;
                    
                set v_break=1;
			end;
        end if;
		set v_translation_counter=v_translation_counter+1;
	end while;
    set @v_attr=null;

if(v_prop<>'Product Code') then 
begin

	call sp_CreateOrReplaceAttribute_v10(v_prop,concat('pa_',v_prop),v_translated_data,p_lang,v_log,@v_attr);


	
    if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateAttribute_v10 -> Relating to post:',p_postid,' v_prop:',ifnull(v_prop,'N/A'),' p_lang:',p_lang,' id:',ifnull(@v_attr,'N/A'))); end if;
    select fn_relateAttributeToPost_v10(p_postid,@v_attr,v_prop,concat('pa_',v_prop),p_lang,v_log ) into v_res;
 
	insert into v_tbl_attributes(parent_postid,postid,attr_name ,attr_type ,attr_id,attr_translation,lang ,dt )
    values(p_postid,v_pimid, v_prop,concat('pa_',lower(v_prop)),@v_attr,concat(ifnull(v_translated_data,''),' -- ',ifnull(v_current_trans,'')),p_lang,now());
end;
else
begin
if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateAttribute_v10 -> adding product code to v_tbl_attributes:',p_postid,' p_lang:',p_lang,' id:',ifnull(@v_attr,'N/A'))); end if;
	insert into v_tbl_attributes(parent_postid,postid,attr_name ,attr_type ,attr_id,attr_translation,lang ,dt )
    values(p_postid,v_pimid, '_sku','_sku',@v_attr,concat(ifnull(v_translated_data,'')),p_lang,now());
end;
end if;

	set v_counter=v_counter+1;
end while;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateAttribute_v10 -> Attributes created: ',' p_lang:',p_lang,' id:',ifnull(@v_attr,'N/A'),' attr:',v_prop)); end if;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateMainPost_v10`(p_post_json longtext,p_parent_id bigint,p_lang varchar(2),v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_post_title,v_props text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_current_var,v_price,v_count_var,v_sku text;
declare v_attribute varchar(200);
declare v_variations,v_techdetails longtext;

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr,cur_finished,v_meta_id int;
declare v_next_trid,v_count_trans,v_trid_main_post,v_counter int;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_CreateMainPost_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET cur_finished = 1;
 
 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 ->  p_parent_id:: ',ifnull(p_parent_id,0),' lang:',p_lang,' p_post_json:',ifnull(p_post_json,'')));
end if;


begin
select JSON_UNQUOTE(json_extract(p_post_json,'$.name')) into v_post_title; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.type')) into v_product_type; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pim_id; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.status')) into v_status; 
select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 

select fn_GetProperty_v10(p_post_json,'Short Description',p_lang,v_log) into v_post_excerpt;
select fn_GetProperty_v10(p_post_json,'Description',p_lang,v_log) into v_post_content;

 select fn_GetSubsubCategory_v10(p_post_json,p_lang,v_log) into v_subsubcategory;
 select fn_GetSubCategory_v10(p_post_json,p_lang,v_log) into v_subcategory;
 select fn_GetCategory_v10(p_post_json,p_lang,v_log) into v_category;
end;

set v_postid=0;
select i.postid into v_postid from imported_ids i where pimid=v_pim_id and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> postid: ',ifnull(v_postid,0),' lang:',p_lang,' pim_id:',ifnull(v_pim_id,0))); end if;

if v_postid>0 then 
begin


 	delete from cportal.ws_postmeta where post_id=v_postid;

	update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content=ifnull(v_post_content,'') ,
	post_title=ifnull(v_post_title,'') ,
	post_excerpt=ifnull(v_post_excerpt,'') ,
	post_status= case when v_status='Enabled' then 'publish' else 'draft' end,

    post_name=concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')),
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/')
    where id=     v_postid ;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> updated post: ',v_postid,' lang:',p_lang));	end if;



	update import_pim
	set json=p_post_json,
	title=ifnull(v_post_title,''),
	subtitle=ifnull(v_post_excerpt,''),
	product_type=v_product_type,
	maintext=ifnull(v_post_content,''),
	category=v_category,
	subcategory=v_subcategory,
	wp_id=v_postid
	where id=v_pim_id;
    
end;
else
begin

	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	ifnull(v_post_content,'') as post_content,
	ifnull(v_post_title,'') post_title,
	ifnull(v_post_excerpt,'') as post_excerpt,
	case when v_status='Enabled' then 'publish' else 'draft' end as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	
    
    concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')) as post_name,
    
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	0 as post_parent,
    concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/') as guid,
    
	
	0 as menu_order,
	'product' as post_type,
	'' as post_mime_type,
	0 as comment_count;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> inserted new post: ',v_postid)); end if;

	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> Define translation')); end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid; 

	if v_log=1 then    insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang)); end if;    

	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
        
	if v_count_trans >0 then  
    begin
		
		if(select count(*)  from cportal.ws_icl_translations where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
			begin
				insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
				values('post_product',v_postid,  v_trid_main_post, p_lang,'en');
			end;
			end if;
	end;
    end if;
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> Translation defined')); end if;



   select fn_CreateCategoryTree_v10(v_category,v_subcategory,v_subsubcategory  ,v_postid,p_lang,v_log)  into v_term_taxonomy_id;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> set product type:',v_product_type)); end if;



call sp_createtaxonomy_v10(case v_product_type when 'Product' then 'variable' else 'simple' end ,'product_type',v_postid,v_log);


	set v_temp_counter=0;
	select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
	if v_temp_counter>0 then
	
		update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
	else
	
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	 end if;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10-> inserting content technical detail ','')); end if;

set v_techdetails=fn_GetTechnicalDetailsProperties_v10(p_post_json,p_lang,v_postid,v_log);
 select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;

if (v_product_type='Bundle') then
begin
set v_price=ifnull(v_price,100);
	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> inserting price:',v_price)); end if;

	insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_regular_price',v_price);
    
    set v_sku=fn_GetProperty_v10(p_post_json,'Product Code',p_lang,v_log);
    insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_sku',v_sku);
    
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.bundle_products')) into v_variations ;
	set v_count_var=json_length(v_variations);
    set v_counter=0;
    
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
        set v_current_var=json_extract(v_current_var,concat('$.product'));   
if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10-> post: ',v_current_var)); end if;        
        select getTechnicalDetailsProperties_v4(v_current_var,p_lang,v_postid) into v_props;
        set v_techdetails=concat(v_techdetails,v_props);
        set v_counter=v_counter+1;
	end while;
    select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;
    
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v10 -> #################### set product as imported for further updates')); end if;

insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);
set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateMainPost_v11`(p_post_json longtext,p_parent_id bigint,p_lang varchar(2),p_integration_number bigint,v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid,v_category_thumb,v_subcategory_thumb,v_subsubcategory_thumb,v_category_trid,v_subcategory_trid,v_subsubcategory_trid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_post_title,v_props text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_current_var,v_price,v_count_var,v_sku text;
declare v_attribute varchar(200);
declare v_variations,v_techdetails longtext;

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr,cur_finished,v_meta_id int;
declare v_next_trid,v_count_trans,v_trid_main_post,v_counter int;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_CreateMainPost_v11 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET cur_finished = 1;


 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 ->  p_parent_id:: ',ifnull(p_parent_id,0),' lang:',p_lang,' p_post_json:',ifnull(p_post_json,'')));
end if;


begin
select JSON_UNQUOTE(json_extract(p_post_json,'$.name')) into v_post_title; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.type')) into v_product_type; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pim_id; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.status')) into v_status; 
select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 

select fn_GetProperty_v10(p_post_json,'Short Description',p_lang,v_log) into v_post_excerpt;
select fn_GetProperty_v10(p_post_json,'Description',p_lang,v_log) into v_post_content;

 select fn_GetSubsubCategory_v10(p_post_json,p_lang,v_log) into v_subsubcategory;
 select fn_GetSubCategory_v10(p_post_json,p_lang,v_log) into v_subcategory;
 select fn_GetCategory_v10(p_post_json,p_lang,v_log) into v_category;
 
end;

set v_postid=0;
select i.postid into v_postid from imported_ids i where pimid=v_pim_id and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> postid: ',ifnull(v_postid,0),' lang:',p_lang,' pim_id:',ifnull(v_pim_id,0))); end if;

if v_postid>0 then 
begin


 	delete from cportal.ws_postmeta where post_id=v_postid;

	update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content=ifnull(v_post_content,'') ,
	post_title=ifnull(v_post_title,'') ,
	post_excerpt=ifnull(v_post_excerpt,'') ,
	post_status= case when v_status='Enabled' then 'publish' else 'draft' end,

    post_name=concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')),
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/')
    where id=     v_postid ;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> updated post: ',v_postid,' lang:',p_lang));	end if;



	update import_pim
	set json=p_post_json,
	title=ifnull(v_post_title,''),
	subtitle=ifnull(v_post_excerpt,''),
	product_type=v_product_type,
	maintext=ifnull(v_post_content,''),
	category=v_category,
	subcategory=v_subcategory,
	wp_id=v_postid
	where id=v_pim_id;
    
end;
else
begin

	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	ifnull(v_post_content,'') as post_content,
	ifnull(v_post_title,'') post_title,
	ifnull(v_post_excerpt,'') as post_excerpt,
	case when v_status='Enabled' then 'publish' else 'draft' end as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	
    
    concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')) as post_name,
    
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	0 as post_parent,
    concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/') as guid,
    
	
	0 as menu_order,
	'product' as post_type,
	'' as post_mime_type,
	0 as comment_count;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> inserted new post: ',v_postid)); end if;

	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> Define translation')); end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid; 

	if v_log=1 then    insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang)); end if;    

	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
        
	if v_count_trans >0 then  
    begin
		
		if(select count(*)  from cportal.ws_icl_translations where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
			begin
				insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
				values('post_product',v_postid,  v_trid_main_post, p_lang,'en');
			end;
			end if;
	end;
    end if;
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> Translation defined')); end if;



if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> fn_CreateMetaDataPost_v11 --> p_post_json:',p_post_json,' p_integration_number:',p_integration_number)); end if;
	select fn_CreateMetaDataPost_v11(p_post_json,'catthumb',0,p_integration_number ,v_log) into v_category_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subcatthumb',0,p_integration_number ,v_log) into v_subcategory_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subsubcatthumb',0,p_integration_number ,v_log) into v_subsubcategory_thumb;

	select fn_GetCategory_trid_v11(p_post_json,v_log) into v_category_trid; 
    select fn_GetSubCategory_trid_v10(p_post_json,v_log) into v_subcategory_trid; 
    select fn_GetSubsubCategory_trid_v10(p_post_json,v_log) into v_subsubcategory_trid; 

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> v_category_trid:',v_category_trid)); end if;

   select fn_CreateCategoryTree_v11(v_category,v_subcategory,v_subsubcategory  ,v_postid,p_lang,v_category_thumb,v_subcategory_thumb,v_subsubcategory_thumb,v_category_trid,v_subcategory_trid,v_subsubcategory_trid,v_log)  into v_term_taxonomy_id;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> set product type:',v_product_type)); end if;



call sp_createtaxonomy_v10(case v_product_type when 'Product' then 'variable' else 'simple' end ,'product_type',v_postid,v_log);


	set v_temp_counter=0;
	select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
	if v_temp_counter>0 then
	
		update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
	else
	
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	 end if;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11-> inserting content technical detail ','')); end if;

set v_techdetails=fn_GetTechnicalDetailsProperties_v10(p_post_json,p_lang,v_postid,v_log);
 select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;

if (v_product_type='Bundle') then
begin
set v_price=ifnull(v_price,100);
	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> inserting price:',v_price)); end if;

	insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_regular_price',v_price);
    
    set v_sku=fn_GetProperty_v10(p_post_json,'Product Code',p_lang,v_log);
    insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_sku',v_sku);
    
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.bundle_products')) into v_variations ;
	set v_count_var=json_length(v_variations);
    set v_counter=0;
    
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
        set v_current_var=json_extract(v_current_var,concat('$.product'));   
if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11-> post: ',v_current_var)); end if;        
        select getTechnicalDetailsProperties_v4(v_current_var,p_lang,v_postid) into v_props;
        set v_techdetails=concat(v_techdetails,v_props);
        set v_counter=v_counter+1;
	end while;
    select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;
    
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v11 -> #################### set product as imported for further updates')); end if;

insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);
set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateMainPost_v12`(p_post_json longtext,p_parent_id bigint,p_lang varchar(2),p_integration_number bigint,v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid,v_category_thumb,v_subcategory_thumb,v_subsubcategory_thumb,v_material_thumb,v_producttype_thumb,v_category_trid,v_subcategory_trid,v_subsubcategory_trid,v_compoundid,v_materialid,v_producttypeid,v_contactmediaid bigint;
declare v_post_content,v_posttype_short_desc text;
declare v_post_excerpt,v_prop_trans,v_prop_trans_value,v_propertytranslation text;
declare v_product_type varchar(100);
declare v_post_title,v_props,v_material,v_producttypejson text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_current_var,v_price,v_count_var,v_sku,v_aux_text text;
declare v_attribute,v_dash varchar(200);
declare v_variations,v_techdetails,v_dashtrans longtext;

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr,cur_finished,v_meta_id,v_dashtranscount int;
declare v_next_trid,v_count_trans,v_trid_main_post,v_counter,v_count,v_match int;

declare v_catid,v_subcatid,v_subsubcatid,v_prop_counter,v_maxpid,v_producttypecount bigint;
declare  v_wpnamme,v_pimname,v_wpfield,v_wp_fieldmap,v_lang varchar(100);
declare v_dynamic_field bit default 0;

declare v_count_compliance,v_current_index int;
declare v_current_compliance,v_serialized_compliance varchar(2000);

 DECLARE props_cursor CURSOR FOR 
 select distinct m1.is_dynamic,m1.wp_name as wp_name,m1.pim_name as pim_name, m.wp_name as wp_field_name,m.value as wp_field_map  
from import_gi.pim_wp_mapping m
right join import_gi.pim_wp_mapping m1 on m.wp_name=concat('_',m1.wp_name) and m.wp_area='post property'  and m.description='field mapping'
 where m1.wp_area='post property'  and m1.description<>'field mapping';

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_CreateMainPost_v12 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET cur_finished = 1;
 

 


 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 ->  p_parent_id:: ',ifnull(p_parent_id,0),' lang:',p_lang,' p_post_json:',ifnull(p_post_json,''))); end if;


begin
select id into v_lang from languages where code=p_lang collate utf8mb4_unicode_ci and deleted_at is null limit 1;
select JSON_UNQUOTE(json_extract(p_post_json,'$.name')) into v_post_title; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.code')) into v_sku; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.type')) into v_product_type; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pim_id; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.status')) into v_status; 
select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 
select fn_GetProperty_v10(p_post_json,'Short Description',p_lang,v_log) into v_post_excerpt;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 ->  p_post_json:: ',ifnull(p_post_json,0),' lang:',p_lang)); end if;

select fn_GetProperty_v10(p_post_json,'Description',p_lang,v_log) into v_post_content;

 select fn_GetSubsubCategory_v10(p_post_json,p_lang,v_log) into v_subsubcategory;
 select fn_GetSubCategory_v10(p_post_json,p_lang,v_log) into v_subcategory;
 select fn_GetCategory_v11(p_post_json,p_lang,0,v_log) into v_category;
 
end;

set v_postid=0;
select i.postid into v_postid from imported_ids i where pimid=v_pim_id and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> postid: ',ifnull(v_postid,0),' lang:',p_lang,' pim_id:',ifnull(v_pim_id,0))); end if;

if v_postid>0 then 
begin


 	delete from cportal.ws_postmeta where post_id=v_postid;

	update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content=ifnull(v_post_content,'') ,
	post_title=ifnull(v_post_title,'') ,
	post_excerpt=ifnull(v_post_excerpt,'') ,
	post_status= case when v_status='Enabled' then 'publish' else 'draft' end,
    post_name=concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')),
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/')
    where id=     v_postid ;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> updated post: ',v_postid,' lang:',p_lang));	end if;

	update import_pim
	set json=p_post_json,
	title=ifnull(v_post_title,''),
	subtitle=ifnull(v_post_excerpt,''),
	product_type=v_product_type,
	maintext=ifnull(v_post_content,''),
	category=v_category,
	subcategory=v_subcategory,
	wp_id=v_postid
	where id=v_pim_id;
    
end;
else
begin

	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	ifnull(v_post_content,'') as post_content,
	ifnull(v_post_title,'') post_title,
	ifnull(v_post_excerpt,'') as post_excerpt,
	case when v_status='Enabled' then 'publish' else 'draft' end as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
    concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')) as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	0 as post_parent,
    concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/') as guid,
	0 as menu_order,
	'product' as post_type,
	'' as post_mime_type,
	0 as comment_count;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> inserted new post: ',v_postid)); end if;

	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;


select fn_AddPostMetaValue_v10(v_postid,'_sku',v_sku,v_log) into v_meta_id;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Define translation lang:',p_lang)); end if;


if p_lang='en' then
begin

set v_count_trans=0;
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid group by trid limit 1; 


	if v_log=1 then    insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang)); end if;    

	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = p_parent_id  group by trid limit 1; 
        
	if v_count_trans >0 then  
    begin
		
		if(select count(*)  from cportal.ws_icl_translations where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
			begin
				insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
				values('post_product',v_postid,  v_trid_main_post, p_lang,'en');
			end;
			end if;
	end;
    end if;
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Translation defined')); end if;



delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_cat' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_group' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_line' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_custom_type' and r.object_id=v_postid;



if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> fn_CreateMetaDataPost_v11 --> p_post_json:',p_post_json,' p_integration_number:',p_integration_number)); end if;
	select fn_CreateMetaDataPost_v11(p_post_json,'catthumb',0,p_integration_number ,v_log) into v_category_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subcatthumb',0,p_integration_number ,v_log) into v_subcategory_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subsubcatthumb',0,p_integration_number ,v_log) into v_subsubcategory_thumb;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> creating category:',p_post_json)); end if;

select fn_CreateCategoryTerm_v3(
json_extract( 
json_extract( 
p_post_json 
,'$.subcategory')
,'$.category')
,'product',p_lang,v_postid,0,null,null,p_integration_number,v_log) into v_catid;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Category created:',v_catid)); end if;


select ifnull(t.trid,0) as trid into v_category_trid
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id
inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
left join cportal.ws_icl_translations t on t.element_id=tx.term_id
where tx.taxonomy ='product_cat' and ter.term_id=v_catid collate utf8mb4_unicode_ci   collate utf8mb4_unicode_ci and icl.language_code='en' collate utf8mb4_unicode_ci   and icl.element_type='tax_product_cat'; 


select fn_CreateCategoryTerm_v3(
json_extract( 
p_post_json 
,'$.subcategory')
,'product_group',p_lang,v_postid,0,v_subcategory_thumb,v_catid,p_integration_number,v_log) into v_subcatid;


select ifnull(t.trid,0) as trid into v_subcategory_trid
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id
inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
left join cportal.ws_icl_translations t on t.element_id=tx.term_id
where tx.taxonomy ='product_group' and ter.term_id=v_subcatid collate utf8mb4_unicode_ci   collate utf8mb4_unicode_ci and icl.language_code='en' collate utf8mb4_unicode_ci   and icl.element_type='tax_product_cat'; 


select fn_CreateOrReplacePostType_v1(v_postid,'material',p_post_json,p_lang,p_integration_number ,v_log ) into v_materialid;
select fn_AddPostMetaValue_v10(v_postid,'_product_material','field_6576e42f77d86',v_log) into v_meta_id;
select fn_AddPostMetaValue_v10(v_postid,'product_material',v_materialid,v_log) into v_meta_id;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> dash number:',JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')))); end if;
if JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')) is not null then
	select import_gi.fn_SerializeDashNumber(JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')),v_log) into v_meta_id;
end if;


select fn_AddPostMetaValue_v10(v_postid,'_product_type','field_655f1073564e7',v_log) into v_meta_id; 
select fn_AddPostMetaValue_v10(v_postid,'product_type','a:1:{i:0;s:13:"Standard Size";}',v_log) into v_meta_id; 
		


select JSON_LENGTH(JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.translation'))) into v_dashtranscount;
set v_count=0;
while v_count<v_dashtranscount do
begin

	select json_extract(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.translation'),concat('$[',v_count,']')) into v_dashtrans;	
    select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.language_id')) into v_match;
    if ( v_match = v_lang) then 
		select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.property')) into v_prop_trans;
        set v_prop_trans_value=null;
        select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.translation_value')) into v_prop_trans_value;
        
        if nullif(v_prop_trans_value,'') is not null then
        
        if v_prop_trans='mmid'  then select fn_AddPostMetaValue_v10(v_postid,'milimeters_id',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmidtol' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_id_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmw' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_width',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmwtol' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_width_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizeid' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_id',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizeod' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_od',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizew' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_width',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='inchesid' then select fn_AddPostMetaValue_v10(v_postid,'inches_id',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='inchesidtol' then select fn_AddPostMetaValue_v10(v_postid,'inches_id_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='inchesw' then select fn_AddPostMetaValue_v10(v_postid,'inches_width',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='incheswtol' then select fn_AddPostMetaValue_v10(v_postid,'inches_width_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgn' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgn',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spge' then select fn_AddPostMetaValue_v10(v_postid,'weight_spge',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgf' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgf',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgcr' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgcr',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgvmq' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgvmq',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='weight_spgf' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgf',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;    
        if v_prop_trans='inchesod' then select fn_AddPostMetaValue_v10(v_postid,'inches_od',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;  
        if v_prop_trans='mmod' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_od',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;  
end if;
        
    end if;    
	set v_count=v_count+1;
end;
end while;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> dash number created:',v_meta_id)); end if;



select 
json_length( 
json_extract( 
p_post_json 
,'$.producttypes')) into v_producttypecount; 

set v_count=0;
while v_count<v_producttypecount do
	select json_extract( 
	json_extract( 
	p_post_json 
	,'$.producttypes'),concat('$[',v_count,']')) into v_producttypejson;

	select fn_CreateOrReplaceProductType (v_postid ,v_producttypejson,v_subcatid,v_materialid,v_count=0,p_lang,p_integration_number,v_log) into v_catid;

	set v_count=v_count+1;
end while;




select fn_CreateOrReplacePostType_v1(v_postid,'compound',p_post_json,p_lang,p_integration_number ,v_log ) into v_materialid;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> set compound:',json_extract(json_extract(p_post_json,concat('$[',0,']')),'$.compound'))); end if;

select fn_CreateCategoryTerm_v3(

p_post_json

,'compound',p_lang,v_postid,0,null,null,p_integration_number,v_log) into v_compoundid;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> set product type:',v_product_type)); end if;


call sp_createtaxonomy_v10('simple','product_type',v_postid,v_log);


	set v_temp_counter=0;
	select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
	if v_temp_counter>0 then
	
		update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
	else
	
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	 end if;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> inserting content technical detail ','')); end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> start dynamic field: ')); end if;       
set v_prop_counter=0;
set cur_finished=0;

 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> starting props cursor p_post_json:',p_post_json)); end if;

open props_cursor;
props_loop: loop
fetch props_cursor into v_dynamic_field,v_wpnamme,v_pimname,v_wpfield,v_wp_fieldmap;
if cur_finished then leave props_loop; end if;

	
	select fn_GetProperty_v10(p_post_json,v_pimname,p_lang,v_log) into v_prop_trans;
    
    if nullif(v_prop_trans,'') is not null then
		select fn_AddPostMetaValue_v10(v_postid,v_wpnamme,v_prop_trans,v_log) into v_meta_id;
    end if;
    
    if (v_wpfield is not null) then
		select fn_AddPostMetaValue_v10(v_postid,v_wpfield,v_wp_fieldmap,v_log) into v_meta_id;
    end if;
    
    if (v_dynamic_field=1 and nullif(v_prop_trans,'') is not null  ) then
		if (v_pimname='COMPOUND COLOUR') then 
			select fn_AddPostMetaValue_v10(v_postid,'_product_colour','field_655f107b564e8',v_log) into v_meta_id;
            select fn_AddPostMetaValue_v10(v_postid,'product_colour',concat('a:1:{i:0;s:',length(v_prop_trans),':"',v_prop_trans,'";}') ,v_log) into v_meta_id;
        end if;
		if (v_pimname='COMPLIANT') then 
			call `sp_AddComplianceToProduct`(v_postid ,v_prop_trans ,v_log );            
        end if;          
		select fn_AddPostMetaValue_v10(v_postid,replace('product_dynamic_fields_#_label','#',v_prop_counter),v_pimname,v_log) into v_meta_id;
		select fn_AddPostMetaValue_v10(v_postid,replace('product_dynamic_fields_#_value','#',v_prop_counter),v_prop_trans,v_log) into v_meta_id;
        
        select fn_AddPostMetaValue_v10(v_postid,replace('_product_dynamic_fields_#_label','#',v_prop_counter),'field_6576e4014aa7b',v_log) into v_meta_id;
		select fn_AddPostMetaValue_v10(v_postid,replace('_product_dynamic_fields_#_value','#',v_prop_counter),'field_6576e4174aa7c',v_log) into v_meta_id;
               
		if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> added dynamic field: ',v_pimname)); end if;        
		set v_prop_counter=v_prop_counter+1;
    end if;
end loop;

close props_cursor;

select fn_AddPostMetaValue_v10(v_postid,'product_dynamic_fields',v_prop_counter,v_log) into v_meta_id;
select fn_AddPostMetaValue_v10(v_postid,'_product_dynamic_fields','field_6576e3d24aa7a',v_log) into v_meta_id;



if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> end dynamic field: ',v_prop_counter,' v_postid:',v_postid)); end if;     



select JSON_UNQUOTE(json_extract( json_extract(json_extract(p_post_json ,concat('$[',0,']')),'$.dash_number') , '$.name')) into v_dash; 
select fn_AddPostMetaValue_v10(v_postid,'product_dash_number',v_dash,v_log) into v_meta_id;



insert into cportal.ws_postmeta (post_id,meta_key,meta_value)
select v_postid,wp_name,value from import_gi.pim_wp_mapping m
left join cportal.ws_postmeta pm on pm.meta_key=m.wp_name and pm.post_id=v_postid
where wp_table='ws_postmeta' and description='field mapping' and wp_area='Post Property' and use_main_product=1 and pm.meta_id is null;

if (v_product_type='Bundle') then
begin
set v_price=ifnull(v_price,1000);
	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> inserting price:',v_price)); end if;

	insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_regular_price',v_price);
    
    set v_sku=fn_GetProperty_v10(p_post_json,'Product Code',p_lang,v_log);
    insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_sku',v_sku);
    
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.bundle_products')) into v_variations ;
	set v_count_var=json_length(v_variations);
    set v_counter=0;
    
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
        set v_current_var=json_extract(v_current_var,concat('$.product'));   
if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> post: ',v_current_var)); end if;        
        select getTechnicalDetailsProperties_v4(v_current_var,p_lang,v_postid) into v_props;
        set v_techdetails=concat(v_techdetails,v_props);
        set v_counter=v_counter+1;
	end while;
    select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;
    
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> #################### set product as imported for further updates')); end if;

insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);
set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateOrReplaceAttribute_v10`(p_attr varchar(200),p_type varchar(200),p_value  varchar(200),p_lang varchar(5),v_log bit,out result bigint)
BEGIN


declare v_term_taxonomy_id,v_term_id,v_next_trid,v_count_trans,v_aux,v_trid,v_new_meta_id,v_metaid,v_res,v_total_options,v_optionid int;
declare v_attr,v_slug,v_fname,v_fvalue,v_options text;
declare v_taxonomy,v_element_type varchar(200);

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR PROCEDURE sp_CreateOrReplaceAttribute_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

SET max_sp_recursion_depth=255;
 
set p_type=lower(replace(p_type,' ','-'));

set v_taxonomy=lower(concat('pa_',replace(p_attr,' ','-')));
set v_slug=lower(concat(replace(p_value,' ','-'),'_',p_lang));
set v_element_type=lower(concat('tax_','pa_',replace(p_attr,' ','-')));
 
 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10## p_attr:',ifnull(p_attr,'N/A'),' p_type:',ifnull(p_type,'N/A'),' p_value:',ifnull(p_value,'N/A'),' p_lang:',ifnull(p_lang,'N/A')));
 end if;
 set v_log=1;


select option_id into v_optionid from cportal.ws_options where option_value like concat('%','s:15:"attribute_label";s:',length(p_attr),':"',p_attr,'";%') collate utf8mb4_unicode_ci and   option_name='_transient_wc_attribute_taxonomies'; 

if v_optionid is null then
begin
	 if v_log=1 then 
	 insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> option not exists, creating option:',ifnull(p_attr,'N/A')));
     end if;  
     
     INSERT INTO cportal.ws_woocommerce_attribute_taxonomies (`attribute_label`, `attribute_name`, `attribute_type`, `attribute_orderby`, `attribute_public`) VALUES (p_attr,lower(replace( p_attr,' ','-')), 'select', 'menu_order', 0);
     
	
    select option_value into v_options from cportal.ws_options where option_name='_transient_wc_attribute_taxonomies'; 
    select substring(v_options,3,POSITION(':' in substring(v_options,2,length(v_options)-2))) into v_total_options; 
    set v_total_options=v_total_options+1;
    set v_options=concat('a:',v_total_options,
    substring(v_options,3+POSITION(':' in substring(v_options,2,length(v_options)-2)),length(v_options)-4+length(v_total_options))); 

	
    set v_options=concat(v_options,'{i:',v_total_options-1,';O:8:"stdClass":6:{s:12:"attribute_id";s:',length(v_total_options-1),':"',v_total_options-1,'";s:14:"attribute_name";s:',
    length(replace(p_attr,' ','-')),':"',lower(replace(p_attr,' ','-')),'";s:15:"attribute_label";s:',
    length(p_attr),':"',p_attr,'";s:14:"attribute_type";s:6:"select";s:17:"attribute_orderby";s:10:"menu_order";s:16:"attribute_public";s:1:"0";}','}');
    
	update cportal.ws_options 
    set option_value=v_options
    where option_name='_transient_wc_attribute_taxonomies'; 
end;
end if;




	select tx.term_id,term_taxonomy_id into v_term_id,v_term_taxonomy_id
	from cportal.ws_term_taxonomy tx 
	inner join cportal.ws_terms ter on ter.term_id=tx.term_id
	inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
	where tx.taxonomy =v_taxonomy collate utf8mb4_unicode_ci  
    and lower(ter.slug)=v_slug  collate utf8mb4_unicode_ci 
    and icl.language_code=p_lang collate utf8mb4_unicode_ci  
    and case when p_lang='en' then icl.source_language_code  is null else icl.source_language_code ='en' collate utf8mb4_unicode_ci end
    and icl.element_type=v_element_type collate utf8mb4_unicode_ci 
    and ter.name=p_value collate utf8mb4_unicode_ci; 
    
	 if v_log=1 then 
	 insert into temp_debug(value) values(concat(
     'sp_CreateOrReplaceAttribute_v10 -> term_id:',ifnull(v_term_id,'N/A'),' taxonomy:',v_taxonomy,' slug:',v_slug,' lang:',p_lang,' type:',v_element_type,' name:',p_value,' p_type:',p_type ));
     end if;    


if(v_term_id is null) then
begin

	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> attribute value not found')); end if;

    
	select max(term_id)+1 into v_term_id from cportal.ws_terms;
	insert into cportal.ws_terms(`term_id`,`name`,`slug`) values(v_term_id,p_value,lower(concat(replace(p_value,' ','-'),'_',p_lang))); 
	
    select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy; 
	insert INTO cportal.ws_term_taxonomy(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`) values( v_term_taxonomy_id,v_term_id,p_type,'',0,0) ;
        
    
    if(p_lang<>'en') then
    begin
    
		 if v_log=1 then 
		 insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> lang:',p_lang,' taxonomy:',concat('pa_',replace(p_attr,' ','-')),' slug:',lower(concat(replace(p_value,' ','-'),'_en')),' ele type:',concat('tax_','pa_',replace(p_attr,' ','-')) ));
		 end if; 
    
		select icl.trid into v_trid
		from cportal.ws_term_taxonomy tx 
		inner join cportal.ws_terms ter on ter.term_id=tx.term_id
		inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
		where 
        tx.taxonomy =v_taxonomy collate utf8mb4_unicode_ci  
        and ter.slug=v_slug  collate utf8mb4_unicode_ci 
        and icl.language_code='en'  collate utf8mb4_unicode_ci  
        and icl.element_type=v_element_type collate utf8mb4_unicode_ci  
        limit 1; 
        
         
        if (ifnull(v_trid,0)>0) then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        else
        begin
			
			if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> creating en version -> sp_CreateOrReplaceAttribute_v10')); end if;            

            call sp_CreateOrReplaceAttribute_v10(p_attr,p_type,p_value,'en',1,@v_attr); 

			if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> en version created -> done')); end if;
            
            set v_res=@v_attr;
            if v_log=1 then  
			insert into temp_debug(value) values(concat('inside sp_CreateOrReplaceAttribute_v10 end of create en version trid:',ifnull(@v_attr,'N/A'),' p_attr:',ifnull(p_attr,'N/A'),' p_value:',ifnull(p_value,'N/A')));
			end if;
            
			select icl.trid into v_trid
			from cportal.ws_term_taxonomy tx 
			inner join cportal.ws_terms ter on ter.term_id=tx.term_id
			inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
			where 
			tx.taxonomy =concat('pa_',lower(replace(p_attr,' ','-'))) collate utf8mb4_unicode_ci  
			and lower(ter.slug)=lower(concat(replace(p_value,' ','-'),'_en'))  collate utf8mb4_unicode_ci 
			and icl.language_code='en'  collate utf8mb4_unicode_ci  
			and icl.element_type=concat('tax_','pa_',lower(replace(p_attr,' ','-'))) collate utf8mb4_unicode_ci  
			limit 1; 
            

			if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> creating translation version v_trid:',ifnull(v_trid,0),' lang:',p_lang,' taxonomy:',concat('pa_',replace(p_attr,' ','-'))));
			end if;                  
            
            insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, p_lang,'en');
        end;
        end if;    
    end;
    else
    begin


        select max(trid)+1 into v_trid from cportal.ws_icl_translations;

		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values(concat('tax_',p_type),v_term_id,  v_trid, 'en',null);                  
    end;
    end if;

	if v_log=1 then  insert into temp_debug(value) values('sp_CreateOrReplaceAttribute_v10 - > Create attribute properties');
	end if;  
	call sp_CreateAttributeProperties_v10(p_attr,v_term_id,p_value,v_log);

end;
else
begin
	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreateOrReplaceAttribute_v10 -> attribute found, nothing to do')); end if;
end;
end if;

 if v_log=1 then insert into temp_debug(value) values('sp_CreateOrReplaceAttribute_v10 -> leaving');
 end if;
 

set result=v_term_id;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreatePostVariation`(p_post_json text,p_parent bigint, p_lang varchar(2),p_price varchar(20), v_log bit)
BEGIN

declare v_attr,v_postid,v_count_props,v_counter bigint;
declare v_properties,v_current_prop,v_prop,v_prop_trans,v_translated_data,v_current_trans text;

declare v_prop_id,v_workcounter,v_translation_counter,v_lang_id int;
declare v_break bit default 0;

select JSON_UNQUOTE(json_extract(p_post_json,'$.properties')) into v_properties;

set v_count_props=json_length(v_properties);
set v_counter=0;

select id into v_lang_id from languages where code=p_lang collate utf8mb4_unicode_ci;

while v_counter<v_count_props do


set v_current_prop=json_extract(v_properties,concat('$[',v_counter,']'));

select JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.property')) into v_prop;

       
        set v_prop_id=JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.id')); 

		set v_workcounter=0;    
        set v_translation_counter=0;
        set v_translated_data='';
        
        set v_prop_trans=JSON_UNQUOTE(json_extract(p_post_json,'$.property_translations'));
        while v_translation_counter<json_length(v_prop_trans) && v_break=0 do
            set v_current_trans=json_extract(v_prop_trans,concat('$[',v_translation_counter,']'));            
            if(json_extract(v_current_trans,'$.language_id')=v_lang_id and json_extract(v_current_trans,'$.property_id')=v_prop_id) then
				begin					
					set v_translated_data=JSON_UNQUOTE(json_extract(v_current_trans,'$.translation_value'));

					if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation -> got property: ',v_prop,' translated value:',v_translated_data));
					end if;
                    
                    set v_break=1;
				end;
            end if;
            set v_translation_counter=v_translation_counter+1;
        end while;
        set @v_attr=null;        
        call CreateOrReplaceAttribute_v6(v_prop,concat('pa_',v_prop),v_translated_data,p_lang,v_log,@v_attr);
    
		select relateAttributeToPost_v6(p_parent,@v_attr,v_prop,concat('pa_',v_prop),p_lang,p_price,v_log)  into v_attr;
        
set v_counter=v_counter+1;

end while;

    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreatePostVariation_v10`(p_post_json text,p_parent bigint, p_lang varchar(2),p_price varchar(20), v_log bit, out postid bigint)
BEGIN

declare v_attr,v_postid,v_count_props,v_counter bigint;
declare v_properties,v_current_prop,v_prop,v_prop_trans,v_translated_data,v_current_trans text;
declare v_prop_id,v_workcounter,v_translation_counter,v_lang_id,v_pimid int;
declare v_break bit default 0;



declare v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_title text; 
declare v_status,v_term_name,v_term_slug,v_post_slug,v_taxonomy text;
declare v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux int;
declare v_next_trid,v_count_trans,v_trid_main_post,v_finish int;

declare cur_att_list cursor for 
select distinct tx.taxonomy,ter.term_id,ter.name,ter.slug from v_tbl_attributes tbl
inner join cportal.ws_icl_translations icl on icl.element_id=tbl.attr_id
inner join cportal.ws_terms ter on  icl.element_id=ter.term_id
inner join cportal.ws_term_taxonomy tx on ter.term_id=tx.term_id
where parent_postid=p_parent;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_CreatePostVariation_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finish = 1;
 


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> json',p_post_json)); end if;
 


select JSON_UNQUOTE(json_extract(p_post_json,'$.properties')) into v_properties;

select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pimid;

set v_count_props=json_length(v_properties);
set v_counter=0;

select id into v_lang_id from languages where code=p_lang collate utf8mb4_unicode_ci;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> starting')); end if;


begin

set v_postid=0;
set v_finish=0;

set v_post_slug=v_pimid;
open cur_att_list;
loop_att_list: LOOP
if v_finish=1 then leave loop_att_list; end if;
fetch cur_att_list into v_taxonomy,v_term_id,v_term_name,v_term_slug;
	set v_post_slug=concat(v_post_slug,'-',v_term_name);
end loop loop_att_list;
close cur_att_list;

select id into v_postid from cportal.ws_posts p 
inner join cportal.ws_icl_translations icl on icl.element_id=p.id
where post_type='product_variation' and post_excerpt=ifnull(v_post_slug,'') collate utf8mb4_unicode_ci  and post_parent=p_parent and source_language_code=case when p_lang='en' then null else p_lang end  collate utf8mb4_unicode_ci  limit 1;

select post_title into v_title from cportal.ws_posts where id=p_parent;

if v_postid>0 then 
begin

	update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content='' ,
	post_title=ifnull(v_title,'') ,
	post_excerpt=ifnull(v_post_slug,'') ,
	post_status= 'publish' ,
	post_name=replace(ifnull(v_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('?post_type=product_variation&p=',p_parent),
    post_parent=p_parent
    where id=     v_postid ;

	if v_log=1 then  insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> updated child post: ',v_postid,' lang:',p_lang)); end if;

	update import_pim
	set json='',
	title=ifnull(v_title,''),
	subtitle=ifnull(v_post_excerpt,''),
	product_type=v_product_type,
	maintext=ifnull(v_post_content,''),
	category=v_category,
	subcategory=v_subcategory,
	wp_id=v_postid
	where id=v_pim_id;
    
end;
else
begin

	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	'' as post_content,
	ifnull(v_title,'') post_title,
	ifnull(v_post_slug,'')  as post_excerpt,
	'publish' as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	p_parent as post_parent,
	concat('?post_type=product_variation&p=',p_parent) as guid,
	0 as menu_order,
	'product_variation' as post_type,
	'' as post_mime_type,
	0 as comment_count;

end;
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid; 
	
    if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang)); end if;
    
	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations where language_code = p_lang  collate utf8mb4_unicode_ci;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product_variation',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = p_parent limit 1; 
        
	if v_count_trans >0 then  
    begin
    if
		(select count(*)  from cportal.ws_icl_translations 
		where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values('post_product_variation',v_postid,  v_trid_main_post, p_lang,'en');
        end;
        end if;
	end;
    end if;
end;
end if;

end;
set v_finish=0;
set v_post_slug='';

 delete from cportal.ws_postmeta where post_id=v_postid;



insert into cportal.ws_postmeta(post_id,meta_key,meta_value)
select distinct v_postid,concat('attribute_',tx.taxonomy),''
from v_tbl_attributes tbl
inner join cportal.ws_icl_translations icl on icl.element_id=tbl.attr_id
inner join cportal.ws_terms ter on  icl.element_id=ter.term_id
inner join cportal.ws_term_taxonomy tx on ter.term_id=tx.term_id
where tbl.parent_postid=p_parent  and tbl.attr_type<>'_sku'; 

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> updating metakeys v_postid:',ifnull(v_postid,''),' v_pimid:',ifnull(v_pimid,''),' v_parent:',ifnull(p_parent,''))); end if;

update cportal.ws_postmeta h inner join 
v_tbl_attributes tbl on h.meta_key=concat('attribute_',tbl.attr_type) collate utf8mb4_unicode_ci
inner join cportal.ws_icl_translations icl on icl.element_id=tbl.attr_id
inner join cportal.ws_terms ter on  icl.element_id=ter.term_id
inner join cportal.ws_term_taxonomy tx on ter.term_id=tx.term_id  and h.meta_key=concat('attribute_',tx.taxonomy)
set h.meta_value=ter.slug 
where tbl.parent_postid=p_parent and tbl.postid=v_pimid and h.post_id=v_postid and tbl.attr_type<>'_sku'; 


insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,'_sku',tbl.attr_translation value from v_tbl_attributes tbl 
left join cportal.ws_postmeta pm on pm.post_id=v_postid and meta_key='_sku'
where tbl.postid=v_pimid and tbl.attr_name='_sku' and tbl.lang=p_lang
 and pm.post_id is null;


insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_variation=1 and wp_pm.post_id is null;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
values(v_postid,'_regular_price',p_price);

set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
end if;

set postid=v_postid;   

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v10 -> post created postid:',ifnull(v_postid,''),' lang:',p_lang)); end if;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreatePostVariation_v7`(p_post_json text,p_parent bigint, p_lang varchar(2),p_price varchar(20), v_log bit)
BEGIN

declare v_attr,v_postid,v_count_props,v_counter bigint;
declare v_properties,v_current_prop,v_prop,v_prop_trans,v_translated_data,v_current_trans text;
declare v_prop_id,v_workcounter,v_translation_counter,v_lang_id int;
declare v_break bit default 0;



declare v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_title text; 
declare v_status,v_term_name,v_term_slug text;
declare v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux int;
declare v_next_trid,v_count_trans,v_trid_main_post int;



select JSON_UNQUOTE(json_extract(p_post_json,'$.properties')) into v_properties;

set v_count_props=json_length(v_properties);
set v_counter=0;

select id into v_lang_id from languages where code=p_lang collate utf8mb4_unicode_ci;

while v_counter<v_count_props do


set v_current_prop=json_extract(v_properties,concat('$[',v_counter,']'));

select JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.property')) into v_prop;

       
        set v_prop_id=JSON_UNQUOTE(json_extract(JSON_UNQUOTE(json_extract(v_current_prop,'$.property')),'$.id')); 

		set v_workcounter=0;    
        set v_translation_counter=0;
        set v_translated_data='';
        
        set v_prop_trans=JSON_UNQUOTE(json_extract(p_post_json,'$.property_translations'));
        while v_translation_counter<json_length(v_prop_trans) and v_break=0 do
            set v_current_trans=json_extract(v_prop_trans,concat('$[',v_translation_counter,']'));            
            if(json_extract(v_current_trans,'$.language_id')=v_lang_id and json_extract(v_current_trans,'$.property_id')=v_prop_id) then
				begin					
					set v_translated_data=JSON_UNQUOTE(json_extract(v_current_trans,'$.translation_value'));

					if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v7 -> got property: ',v_prop,' translated value:',v_translated_data));
					end if;
                    
                    set v_break=1;
				end;
            end if;
            set v_translation_counter=v_translation_counter+1;
        end while;
        set @v_attr=null;        
        call CreateOrReplaceAttribute_v6(v_prop,concat('pa_',v_prop),v_translated_data,p_lang,v_log,@v_attr);
        
        
        insert into v_tbl_attributes(parent_postid,postid,attr_name ,attr_type ,attr_id,lang ,dt )
        values(p_parent,null, v_prop,concat('pa_',lower(v_prop)),@v_attr,p_lang,now());
    
set v_counter=v_counter+1;
end while;




set v_postid=0;


select ter.slug,ter.name into v_term_slug,v_term_name from cportal.ws_term_taxonomy tx inner join cportal.ws_terms ter on ter.term_id=tx.term_id where tx.term_taxonomy_id=p_term_taxonomy_id;

select id into v_postid from cportal.ws_posts p 
inner join cportal.ws_icl_translations icl on icl.element_id=p.id
where post_type='product_variation' and post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) collate utf8mb4_unicode_ci  and post_parent=p_parent and source_language_code=case when p_lang='en' then null else p_lang end limit 1;
select post_title into v_title from cportal.ws_posts where id=p_parent;

if v_postid>0 then 
begin

update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content='' ,
	post_title=ifnull(v_title,'') ,
	post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) ,
	post_status= 'publish' ,
	post_name=replace(ifnull(v_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('?post_type=product_variation&p=',p_parent_id),
    post_parent=p_parent_id
    where id=     v_postid ;

if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_CreatePostVariation_v7 -> updated child post: ',v_postid,' lang:',p_lang));
end if;

set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
    insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
 end if;

update import_pim
set json='',
title=ifnull(v_title,''),
subtitle=ifnull(v_post_excerpt,''),
product_type=v_product_type,
maintext=ifnull(v_post_content,''),
category=v_category,
subcategory=v_subcategory,
wp_id=v_postid
where id=v_pim_id;
    
end;
else
begin

	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	'' as post_content,
	ifnull(v_title,'') post_title,
	concat(ifnull(p_attribute,''),': ',v_term_name)  as post_excerpt,
	'publish' as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	p_parent_id as post_parent,
	concat('?post_type=product_variation&p=',p_parent_id) as guid,
	0 as menu_order,
	'product_variation' as post_type,
	'' as post_mime_type,
	0 as comment_count;


if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_CreatePostVariation_v7 -> inserted new post: ',v_postid));
end if;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    



end;
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid; 
        
    insert into temp_debug(value) values(concat('sp_CreatePostVariation_v7 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang));
    
	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations where language_code = p_lang  collate utf8mb4_unicode_ci;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product_variation',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
    

    
	if v_count_trans >0 then  
    begin
    
    if
		(select count(*)  from cportal.ws_icl_translations 
		where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values('post_product_variation',v_postid,  v_trid_main_post, p_lang,'en');
        end;
        end if;
	end;
    end if;
end;
end if;


insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_variation=1 and wp_pm.post_id is null;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
values(v_postid,'_regular_price',p_price);














	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreatePostVariation_v7 -> p_parent: ',p_parent,' p_price:',p_price,' p_lang:',p_lang));
	end if;
    
    
	select relateAttributesToPost_v8(p_parent,p_price,p_lang,v_log)  into v_attr;
    
    
    
    
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateRegions_v10`(v_log bit)
BEGIN

declare v_count_var,v_counter,v_aux int;
declare v_jsonstring,v_current_var text;
declare v_region_code,v_region_name,v_currency_name,v_currency_code varchar(200);
declare v_region_id,v_currency_id int;
declare v_region_deleted_at,v_currency_deleted_at varchar(100);


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_CreateRegions_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 
select json_length(jsonstring),jsonstring into v_count_var,v_jsonstring from importregions order by dt desc limit 1;

    set v_counter=0;
    
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_jsonstring,concat('$[',v_counter,']'));   



		select 
		JSON_UNQUOTE(json_extract(v_current_var ,'$.id')) as region_id,
		JSON_UNQUOTE(json_extract(v_current_var ,'$.code')) as region_code,
		JSON_UNQUOTE(json_extract(v_current_var,'$.name')) as region_name,
		nullif(nullif(JSON_UNQUOTE(json_extract(v_current_var ,'$.deleted_at')),''),'null')  as region_deleted_at,
		JSON_UNQUOTE(json_extract(json_extract(v_current_var ,'$.currency'),'$.name')) as currency_name,
		JSON_UNQUOTE(json_extract(json_extract(v_current_var ,'$.currency'),'$.code')) as currency_code,
		JSON_UNQUOTE(json_extract(json_extract(v_current_var ,'$.currency'),'$.id')) as currency_id,
		nullif(nullif(JSON_UNQUOTE(json_extract(json_extract(v_current_var,'$.currency'),'$.deleted_at')),''),'null')  as currency_deleted_at
        into v_region_id,v_region_code,v_region_name,v_region_deleted_at,v_currency_name,v_currency_code,v_currency_id,v_currency_deleted_at;
        
        select ifnull(id,0) into v_aux from pim_regions where pim_id=v_region_id;
        
        if(v_aux>0) then
			update pim_regions
            set pim_code=v_region_code,
            pim_name=v_region_name,
            deleted=if(v_region_deleted_at is null, 0,1),
            currency_id=v_currency_id
            where id=v_aux;
        else
			insert into pim_regions(pim_id,pim_code,pim_name,currency_id,date,deleted)
			values(v_region_id,v_region_code,v_region_name,v_currency_id,now(),if(v_region_deleted_at is null, 0,1));
        
        end if;

		    
        set v_counter=v_counter+1;
	end while;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`giprod`@`localhost` PROCEDURE `sp_ImportPricesFromSAP`(v_log bit)
BEGIN

declare v_user_id,v_finished int;

declare v_material,v_scalefrom,v_scaleto,v_price100,v_baseprice,v_post_id,v_customer,v_isgeneric,v_working_customer varchar(200);

DECLARE material_cursor CURSOR FOR 
select material,ScaleFrom,ScaleTo,Price100,BasePrice,post_id,case when Customer='1024495' then '' else Customer end as Customer,user_id,'false'  from (
select distinct s.material,s.ScaleFrom,s.ScaleTo,s.Price100,s.BasePrice,pmo.post_id,s.Customer,um.user_id,s.IsGeneric 
from cportal.ws_postmeta pmo inner join stg_pim_gi.stg_prices s on s.Material=pmo.meta_value
 inner join cportal.ws_usermeta um on um.meta_key='sap_customer' and um.meta_value=s.customer
 where pmo.meta_key='_sku' and s.importid=(select max(importid) from stg_pim_gi.stg_prices) and s.customer<>''
 union
 select distinct s.material,s.ScaleFrom,s.ScaleTo,s.Price100,s.BasePrice,pmo.post_id,s.Customer,0,'true'
from cportal.ws_postmeta pmo inner join stg_pim_gi.stg_prices s on s.Material=pmo.meta_value
 where pmo.meta_key='_sku' and s.importid=(select max(importid) from stg_pim_gi.stg_prices) and s.customer='1024495'
 union
  select distinct s.material,s.ScaleFrom,s.ScaleTo,s.Price100,s.BasePrice,pmo.post_id,s.Customer,um.user_id,'false'
from cportal.ws_postmeta pmo inner join stg_pim_gi.stg_prices s on s.Material=pmo.meta_value
inner join cportal.ws_usermeta um on um.meta_key='sap_customer' and um.meta_value<>''
left join (
select min(cast(scalefrom as decimal(10,3))) as scalefrom,material,customer,importid from  stg_pim_gi.stg_prices where importid=(select max(importid) from stg_pim_gi.stg_prices) 
group by material,customer,importid 
) ss on  ss.customer=um.meta_value and  ss.Material=pmo.meta_value
 where pmo.meta_key='_sku' and s.importid=(select max(importid) from stg_pim_gi.stg_prices) and s.customer='' and ss.material is not null 
 and cast(s.scalefrom as decimal(10,3))<ss.scalefrom
 ) x  order by material,user_id,x.Customer;
 
DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_ImportPrices : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

set v_finished=0;
set v_working_customer='';


open material_cursor;

loop_material: LOOP

fetch material_cursor into v_material,v_scalefrom,v_scaleto,v_price100,v_baseprice,v_post_id,v_customer,v_user_id,v_isgeneric;

if v_log=1 then insert into temp_debug(value) values(concat('sp_ImportPrices-> v_work_mat:',v_material,' v_finished:',v_finished)); end if;

if (v_working_customer<>v_customer and v_customer<>'') then
begin
    delete m from  cportal.ws_wusp_user_pricing_mapping m inner join cportal.ws_usermeta um on um.meta_key='sap_customer' and um.meta_value=v_customer and m.user_id=um.user_id;
    if v_log=1 then insert into temp_debug(value) values(concat('sp_ImportPrices-> deleting sustom prices for:',v_material,' v_customer:',v_customer)); end if;
    set v_working_customer=v_customer;
end;
end if;

if v_finished =1 then 
	leave loop_material;
end if;
 
 if v_isgeneric='true' then
 begin
	if v_log=1 then insert into temp_debug(value) values(concat('sp_ImportPrices-> updating standard prices:',v_material,' v_customer:',v_customer,' v_user_id:',v_user_id)); end if;
 

	update cportal.ws_postmeta pm
	set pm.meta_value=v_price100/100
	where pm.meta_key='_price' and pm.post_id=v_post_id;
	
	update cportal.ws_postmeta pm
	set pm.meta_value=v_price100/100
	where pm.meta_key='_regular_price' and pm.post_id=v_post_id;
	 
	
	insert into cportal.ws_postmeta (post_id,meta_key,meta_value)
	select v_post_id,'_price',v_price100/100 from dual where not exists (select meta_id from cportal.ws_postmeta where meta_key='_price' and post_id=v_post_id)
	union
	select v_post_id,'_regular_price',v_price100/100 from dual where not exists (select meta_id from cportal.ws_postmeta where meta_key='_regular_price' and post_id=v_post_id);
end;
else
begin

INSERT INTO cportal.ws_wusp_user_pricing_mapping
(product_id,user_id,price,min_qty,flat_or_discount_price)
VALUES(v_post_id,v_user_id,v_price100/100,v_scalefrom,1);
 if v_log=1 then insert into temp_debug(value) values(concat('sp_ImportPrices-> inserted prices:',v_material,' v_customer:',v_customer,' v_user_id:',v_user_id,' v_scalefrom:',v_scalefrom)); end if;
end;
end if; 

set v_finished=0;

end loop loop_material;

close material_cursor;
 
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_IterateLangs_v12`(p_post_json longtext,p_integration_number int,v_log bit )
BEGIN


declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type,v_price varchar(100);
declare v_post_title text; 
declare v_subcategory,v_category,v_status,v_current_var text;
declare v_variations longtext;

declare v_term_taxonomy_id,v_term_id,v_parent int;
declare v_next_trid,v_count_trans,v_count_var int;

declare v_str text;
declare v_pim_id bigint;
declare v_total,v_counter,v_counter_images,v_total_images,v_temp_counter int;
declare v_img_list varchar(1000);

DECLARE v_end_cur INTEGER DEFAULT 0;
declare v_aux int;
declare v_meta_id bigint;

declare v_work_lang,v_file_lang varchar(5);
DECLARE v_finished INTEGER DEFAULT 0;

declare v_count_file_dsc bigint;
declare v_file_dsc,v_file_metakey varchar(100);

DECLARE lang_cursor CURSOR FOR select code from  languages where deleted_at is null order by id;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_IterateLangs_v12 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

set v_work_lang='en';
set v_total=0;
set v_counter=0;
set v_post_excerpt='';

set v_finished=0;
set v_parent=0;

open lang_cursor;

loop_lang: LOOP

fetch lang_cursor into v_work_lang;

if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> v_work_lang:',v_work_lang,' v_finished:',v_finished)); end if;

if v_finished =1 then 
	leave loop_lang;
end if;



begin
	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> create main post lang:',v_work_lang,' parent:',v_parent,' json: ',p_post_json)); end if;
	
	call sp_CreateMainPost_v12(p_post_json,v_parent,v_work_lang,p_integration_number,v_log,@postid) ;

	set v_postid=@postid;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 -> post created: ',v_postid,' lang:',v_work_lang)); end if;
end;


begin 

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 -> starting variations')); end if;

	
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.variations')) into v_variations ;

	set v_count_var=json_length(v_variations);
	set v_counter=0;
    

    
	begin
		
		while v_counter<v_count_var do
		
			set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
			if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 -> variation nº',v_counter,' -- var:',v_current_var,' post:',v_postid,' lang:',v_work_lang)); end if;
				
			call sp_CreateAttribute_v10(v_current_var,v_postid , v_work_lang , v_log );
			
			set v_counter= v_counter+1;
		end while;
	end;

	
	delete p,icl,pm from cportal.ws_posts p
	inner join cportal.ws_icl_translations icl on icl.element_id=p.id
	inner join cportal.ws_postmeta pm on pm.post_id=p.id
	where post_type='product_variation' and post_parent=v_postid;

	set v_counter=0;
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));
        select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 
        
        set v_price=ifnull(v_price,0);
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 -> create variation -> v_postid:',v_postid,' v_price:',v_price,' v_work_lang:',v_work_lang)); end if;
        
 		call sp_CreatePostVariation_v10(v_current_var ,v_postid , v_work_lang,v_price , v_log,@res );

		
        
        set v_counter=v_counter+1;
	end while;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 -> v_finished variations')); end if;
end;


if v_work_lang='en' then 
	set v_parent=v_postid;
end if;


 










	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> Insert Images post:',v_postid)); end if;

	select json_length(json_extract(p_post_json,'$.images')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
	while v_counter_images<v_total_images do
		
		select fn_CreateMetaDataPost_v11(json_extract(json_extract(p_post_json,'$.images'),concat('$[',v_counter_images,']')),'images',v_postid,p_integration_number,v_log) into v_thumbid; 
        
		set v_img_list=concat(v_img_list,CAST(v_thumbid as char(10)),',') ;
        set v_counter_images=v_counter_images+1;
        
    end while;    

    if( length(v_img_list)>0) then
    begin
    
		set v_img_list=SUBSTRING(v_img_list,     1,    CHAR_LENGTH(v_img_list) - 1);
		
        delete from cportal.ws_postmeta where post_id=v_postid and meta_key='_product_image_gallery';
		
        select fn_AddPostMetaValue_v10(v_postid,'_product_image_gallery',v_img_list,v_log)  into v_meta_id;
		
    end;
    end if;


	select json_length(json_extract(p_post_json,'$.files')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
    set v_log=1;
    
    select fn_AddPostMetaValue_v10(v_postid,'_pdp_document', 'field_65703f7467eaa',v_log) into v_meta_id;
	while v_counter_images<v_total_images do

		select fn_CreateMetaDataPost_v11(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'files',v_postid,p_integration_number,v_log) into v_thumbid; 
        
        set v_file_metakey =null;
        select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_dsc')) into v_file_dsc;
               
        select meta_key into v_file_metakey from cportal.ws_postmeta  where meta_key like 'pdp_document_%' and meta_value=v_file_dsc and post_id=v_postid;
        set v_file_metakey = left(v_file_metakey ,length(v_file_metakey )-5);
        
        if  v_file_metakey is null then 
			select count(1) into v_count_file_dsc from cportal.ws_postmeta where post_id=v_postid and meta_key like 'pdp_document%_title';
			set v_file_metakey=concat('pdp_document_',v_count_file_dsc,'_');
			
            select fn_AddPostMetaValue_v10(v_postid,concat(v_file_metakey,'title'), JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_dsc')),v_log) into v_meta_id;
            select fn_AddPostMetaValue_v10(v_postid,concat('_',v_file_metakey,'title'), 'field_65703fc267eab',v_log) into v_meta_id;
            
            select fn_AddPostMetaValue_v10(v_postid,'pdp_document',v_count_file_dsc+1,v_log) into v_meta_id; 
        end if;
               
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> v_file_dsc=',v_file_dsc)); end if;
                
        select count(1) into v_count_file_dsc from cportal.ws_postmeta  where meta_key like concat(v_file_metakey,'%','_file') and post_id=v_postid; 
        
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12 - ###############################################-> v_file_metakey=',v_file_metakey,' v_count_file_dsc pos:',v_count_file_dsc,' v_file_dsc=',v_file_dsc)); end if;
        
        select fn_AddPostMetaValue_v10(v_postid,concat(v_file_metakey,'file'),v_thumbid,v_log) into v_meta_id;
        select fn_AddPostMetaValue_v10(v_postid,concat('_',v_file_metakey,'file'), 'field_65703fde67eac',v_log) into v_meta_id;
        
        
 		if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> p_post_json:',p_post_json)); end if;
		
        
        

        
        
  
        set v_counter_images=v_counter_images+1;
        
        
        
        
    
    end while;   


    


 

 





insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_main_product=1 and wp_pm.post_id is null; 


 delete from v_tbl_attributes;
 
 if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v12-> Loop end postid:',v_postid,' lang:',v_work_lang)); end if;


set v_finished=0;

end loop loop_lang;

close lang_cursor;


END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`jmartins`@`localhost` PROCEDURE `sp_SyncInvoices`()
BEGIN
        
    DECLARE v_finished INTEGER DEFAULT 0;
    declare v_work_meta_id bigint;
    declare v_work_invoice varchar(30);
    DECLARE invoice_cursor CURSOR FOR 
    SELECT distinct order_number FROM stg_invoices where importid=(select max(importid) from stg_invoices);

	DECLARE exit handler for SQLEXCEPTION
	 BEGIN
	  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
	   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
	  SET @full_error = CONCAT("ERROR procedure sp_SyncInvoices : ", @errno, " (", @sqlstate, "): ", @text);
	  insert into temp_debug(value) values(@full_error);
	 END;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

	open invoice_cursor;

	loop_invoice: LOOP
		fetch invoice_cursor into v_work_invoice;
		if v_finished =1 then 
			leave loop_invoice;
		end if;
        
        insert into import_gi_prod.temp_debug(value) values(concat('checking invoice:',v_work_invoice));
select pm.meta_id into v_work_meta_id  from cportal_prod.ws_postmeta pm 
        inner join 
        cportal_prod.ws_postmeta pm1 on pm.post_id=pm1.post_id
        inner join stg_invoices i on i.order_number=pm1.meta_value and pm.meta_key='sap_invoice'
        where  pm1.meta_key='sap_order_id' and i.order_number=v_work_invoice;        
		if v_work_meta_id is not null then
		begin
			insert into import_gi_prod.temp_debug(value) values(concat('updating invoice:',v_work_invoice,' with meta_id:',v_work_meta_id));
			update cportal_prod.ws_postmeta pm             
			inner join stg_invoices i 
			set meta_value=i.pdf_path
			where i.order_number=v_work_invoice and pm.meta_id=v_work_meta_id;
		end;
		else
		begin
        insert into import_gi_prod.temp_debug(value) values(concat('inserting invoice:',v_work_invoice,' with meta_id:',v_work_meta_id));
			insert into cportal_prod.ws_postmeta(post_id,meta_key,meta_value)
			select pm.post_id,'sap_invoice',i.pdf_path from cportal_prod.ws_postmeta pm 
			inner join stg_invoices i on i.order_number=pm.meta_value and pm.meta_key='sap_order_id'
			where i.order_number=v_work_invoice;
		end;
		end if;
		
		set v_finished=0;

	end loop loop_invoice;

	close invoice_cursor;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_createVariationOnAttribute_v6`(p_attribute varchar(100),p_term_taxonomy_id bigint,p_parent_id bigint,p_lang varchar(100),p_price varchar(20),v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_title text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_term_name,v_term_slug text;

declare v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr int;
declare v_next_trid,v_count_trans,v_trid_main_post int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_createVariationOnAttribute_v6 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;
  
set v_postid=0;


select ter.slug,ter.name into v_term_slug,v_term_name from cportal.ws_term_taxonomy tx inner join cportal.ws_terms ter on ter.term_id=tx.term_id where tx.term_taxonomy_id=p_term_taxonomy_id;

if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_createVariationOnAttribute_v6 -> v_term_name: ',ifnull(v_term_name,''),' v_term_slug:',ifnull(v_term_slug,''),' p_term_taxonomy_id:',p_term_taxonomy_id, ' p_parent_id:',p_parent_id,' p_lang:',p_lang));
end if;

select id into v_postid from cportal.ws_posts p where post_type='product_variation' and post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) collate utf8mb4_unicode_ci  and post_parent=p_parent_id limit 1;
select post_title into v_title from cportal.ws_posts where id=p_parent_id;

if v_postid>0 then 
begin


update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content='' ,
	post_title=ifnull(v_title,'') ,
	post_excerpt=concat(ifnull(p_attribute,''),': ',v_term_name) ,
	post_status= 'publish' ,
	post_name=replace(ifnull(v_title,''),' ','-') ,
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('?post_type=product_variation&p=',p_parent_id),
    post_parent=p_parent_id
    where id=     v_postid ;

if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_createVariationOnAttribute_v6 -> updated post: ',v_postid,' lang:',p_lang));
end if;

set v_temp_counter=0;
select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
if v_temp_counter>0 then

	update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
else

	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
    insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
 end if;

update import_pim
set json='',
title=ifnull(v_title,''),
subtitle=ifnull(v_post_excerpt,''),
product_type=v_product_type,
maintext=ifnull(v_post_content,''),
category=v_category,
subcategory=v_subcategory,
wp_id=v_postid
where id=v_pim_id;
    
end;
else
begin


	
	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	'' as post_content,
	ifnull(v_title,'') post_title,
	concat(ifnull(p_attribute,''),': ',v_term_name)  as post_excerpt,
	'publish' as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
	replace(ifnull(v_title,''),' ','-') as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	p_parent_id as post_parent,
	concat('?post_type=product_variation&p=',p_parent_id) as guid,
	0 as menu_order,
	'product_variation' as post_type,
	'' as post_mime_type,
	0 as comment_count;


if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_createVariationOnAttribute_v6 -> inserted new post: ',v_postid));
end if;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,concat('attribute_pa_',lower(p_attribute)),v_term_slug  from cportal.ws_postmeta  ;    

end;
end if;


if p_lang='en' then
begin

	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid; 
        
    insert into temp_debug(value) values(concat('sp_createVariationOnAttribute_v6 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang));
    
	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations where language_code = p_lang  collate utf8mb4_unicode_ci;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product_variation',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = p_parent_id limit 1; 
    

    
	if v_count_trans >0 then  
    begin
    
    if
		(select count(*)  from cportal.ws_icl_translations 
		where element_type = 'post_product_variation'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
        begin
			insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
			values('post_product_variation',v_postid,  v_trid_main_post, p_lang,'en');
        end;
        end if;
	end;
    end if;
end;
end if;





insert into imported_ids(pimid,postid,dt,lang,`desc`) values(v_pim_id,v_postid,now(),p_lang,'sp_createVariationOnAttribute_v6');

if v_log=1 then 
	insert into temp_debug(value) values(concat('sp_createVariationOnAttribute_v6 -> Insert wp_postmeta for variation post:',v_postid));
end if;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_variation=1 and wp_pm.post_id is null;

insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
values(v_postid,'_regular_price',p_price);


set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_createtaxonomy_v10`(p_term_name varchar(200),p_taxonomy_type varchar(200),p_object_id int,v_log bit)
BEGIN

declare v_term_id,v_term_taxonomy_id,v_aux int;
DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure createtaxonomy_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
  
 END;

if v_log=1 then insert into temp_debug(value) values(concat('createtaxonomy_v10 -> Defining term/taxonomy for term:',ifnull(p_term_name,''),' in post:',p_object_id));
end if;
 
select tx.term_id,term_taxonomy_id into v_term_id,v_term_taxonomy_id 
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id  collate utf8mb4_unicode_ci
where tx.taxonomy =p_taxonomy_type  collate utf8mb4_unicode_ci and name=p_term_name collate utf8mb4_unicode_ci and parent=0  collate utf8mb4_unicode_ci; 

if(v_term_id is null) then 
begin

select max(term_id)+1 into v_term_id from wp_terms;
INSERT INTO cportal.ws_terms
(`term_id`,`name`,`slug`,`term_group`)
values(v_term_id,p_term_name,p_term_name,0); 

select max(term_taxonomy_id)+1 into v_term_taxonomy_id from cportal.ws_term_taxonomy;

INSERT INTO cportal.ws_term_taxonomy
(`term_taxonomy_id`,`term_id`,`taxonomy`,`description`,`parent`,`count`)
select v_term_taxonomy_id,v_term_id,p_taxonomy_type,'',0,0; 

end;
end if;

select rel.term_taxonomy_id into v_aux 
from cportal.ws_term_relationships rel inner join 
cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=rel.term_taxonomy_id collate utf8mb4_unicode_ci
where rel.object_id=p_object_id  collate utf8mb4_unicode_ci and rel.term_taxonomy_id=v_term_taxonomy_id  collate utf8mb4_unicode_ci limit 1;

if(v_aux is null) then
begin
	INSERT INTO cportal.ws_term_relationships
	(`object_id`,`term_taxonomy_id`,`term_order`)
	VALUES
	(p_object_id,v_term_taxonomy_id,0);
end;
end if;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`giprod`@`localhost` PROCEDURE `sp_insertStocksIntoPortal`()
BEGIN


	update import_gi.stg_stocks stgs 
	inner join cportal.ws_postmeta pm on pm.meta_value=stgs.material
	inner join cportal.ws_postmeta pms on pm.post_id=pms.post_id and pms.meta_key='_stock'
	set pms.meta_value=stgs.StockQuantity
	where pm.meta_key='_sku' and   stgs.importid=(select max(importid) from import_gi.stg_stocks);

	insert into cportal.ws_postmeta (post_id,meta_key,meta_value)
	select pm.post_id,'stock',stgs.StockQuantity
	from
	 import_gi.stg_stocks stgs 
	inner join cportal.ws_postmeta pm on pm.meta_value=stgs.material
	left join cportal.ws_postmeta pms on pm.post_id=pms.post_id and pms.meta_key='_stock'
	where  pm.meta_key='_sku' and  stgs.importid=(select max(importid) from import_gi.stg_stocks) and pms.meta_id is null;
    
    
update import_gi.stg_stocks stgs 
right join (cportal.ws_postmeta pm   
inner join cportal.ws_postmeta pms on pm.post_id=pms.post_id and pms.meta_key='_stock'  ) on  pm.meta_value=stgs.material and stgs.importid=(select max(importid) from import_gi.stg_stocks)
set pms.meta_value=0
where pm.meta_key='_sku' and material is null;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_migrate_data_from_pim_v11`(v_log bit)
BEGIN

declare v_total,v_counter int;
declare v_str,v_post_json longtext;
declare v_integration_number int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_migrate_data_from_pim_v11: ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

set v_total=0;
set v_counter=0;

select ifnull(max(integration_id),0)+1 into v_integration_number from files_to_transfer;


select i.jsonstring into v_str from import i order by dt desc limit 1;



set v_total=json_length(v_str);

while v_counter<v_total do
	
	select json_extract(v_str,concat('$[',v_counter,']')) into v_post_json;
	
	if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v11-> iterating posts -> post:',v_post_json ));
    end if;
    
     call sp_IterateLangs_v12(v_post_json,v_integration_number,v_log );
    
	set v_counter=v_counter+1;
    
    if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v11 -> imported '));
    end if;
end while;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_delete_sp_migrate_data_from_pim_v10`(v_log bit)
BEGIN

declare v_total,v_counter int;
declare v_str,v_post_json longtext;
declare v_integration_number int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_migrate_data_from_pim_v10: ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

set v_total=0;
set v_counter=0;

select ifnull(max(integration_id),0)+1 into v_integration_number from files_to_transfer;


select i.jsonstring into v_str from import i order by dt desc limit 1;

set v_total=json_length(v_str);

while v_counter<v_total do
	
	select json_extract(v_str,concat('$[',v_counter,']')) into v_post_json;
	
	if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v10-> iterating posts -> post:',v_post_json ));
    end if;
     call sp_IterateLangs_v11(v_post_json,v_integration_number,v_log );
    
	set v_counter=v_counter+1;
    
    if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v10 -> imported '));
    end if;
end while;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_sp_CreateMainPost_v12`(p_post_json longtext,p_parent_id bigint,p_lang varchar(2),p_integration_number bigint,v_log bit,out postid bigint)
BEGIN

declare v_postid,v_thumbid,v_category_thumb,v_subcategory_thumb,v_subsubcategory_thumb,v_material_thumb,v_producttype_thumb,v_category_trid,v_subcategory_trid,v_subsubcategory_trid,v_compoundid,v_materialid,v_producttypeid,v_contactmediaid bigint;
declare v_post_content,v_posttype_short_desc text;
declare v_post_excerpt,v_prop_trans,v_prop_trans_value,v_propertytranslation text;
declare v_product_type varchar(100);
declare v_post_title,v_props,v_material,v_producttypejson text; 
declare v_subcategory,v_category,v_status,v_subsubcategory,v_current_var,v_price,v_count_var,v_sku,v_aux_text text;
declare v_attribute,v_dash varchar(200);
declare v_variations,v_techdetails,v_dashtrans longtext;

declare v_term_taxonomy_id,v_term_id,v_parent,v_pim_id,v_temp_counter,v_aux,v_attr,cur_finished,v_meta_id,v_dashtranscount int;
declare v_next_trid,v_count_trans,v_trid_main_post,v_counter,v_count,v_match int;

declare v_catid,v_subcatid,v_subsubcatid,v_prop_counter,v_maxpid,v_producttypecount bigint;
declare  v_wpnamme,v_pimname,v_wpfield,v_wp_fieldmap,v_lang varchar(100);
declare v_dynamic_field bit default 0;

declare v_count_compliance,v_current_index int;
declare v_current_compliance,v_serialized_compliance varchar(2000);

 DECLARE props_cursor CURSOR FOR 
 select distinct m1.is_dynamic,m1.wp_name as wp_name,m1.pim_name as pim_name, m.wp_name as wp_field_name,m.value as wp_field_map  
from import_gi.pim_wp_mapping m
right join import_gi.pim_wp_mapping m1 on m.wp_name=concat('_',m1.wp_name) and m.wp_area='post property'  and m.description='field mapping'
 where m1.wp_area='post property'  and m1.description<>'field mapping';

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR function sp_CreateMainPost_v12 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;
 
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET cur_finished = 1;
 

 


 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 ->  p_parent_id:: ',ifnull(p_parent_id,0),' lang:',p_lang,' p_post_json:',ifnull(p_post_json,''))); end if;


begin
select id into v_lang from languages where code=p_lang collate utf8mb4_unicode_ci and deleted_at is null limit 1;
select JSON_UNQUOTE(json_extract(p_post_json,'$.name')) into v_post_title; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.code')) into v_sku; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.type')) into v_product_type; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.id')) into v_pim_id; 
select JSON_UNQUOTE(json_extract(p_post_json,'$.status')) into v_status; 
select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 
select fn_GetProperty_v10(p_post_json,'Short Description',p_lang,v_log) into v_post_excerpt;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 ->  p_post_json:: ',ifnull(p_post_json,0),' lang:',p_lang)); end if;

select fn_GetProperty_v10(p_post_json,'Description',p_lang,v_log) into v_post_content;

 select fn_GetSubsubCategory_v10(p_post_json,p_lang,v_log) into v_subsubcategory;
 select fn_GetSubCategory_v10(p_post_json,p_lang,v_log) into v_subcategory;
 select fn_GetCategory_v11(p_post_json,p_lang,0,v_log) into v_category;
 
end;

set v_postid=0;
select i.postid into v_postid from imported_ids i where pimid=v_pim_id and lang=p_lang collate utf8mb4_unicode_ci order by dt desc limit 1;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> postid: ',ifnull(v_postid,0),' lang:',p_lang,' pim_id:',ifnull(v_pim_id,0))); end if;

if v_postid>0 then 
begin


 	delete from cportal.ws_postmeta where post_id=v_postid;

	update cportal.ws_posts
	set
    post_date=now() ,
	post_date_gmt=now() , 
	post_content=ifnull(v_post_content,'') ,
	post_title=ifnull(v_post_title,'') ,
	post_excerpt=ifnull(v_post_excerpt,'') ,
	post_status= case when v_status='Enabled' then 'publish' else 'draft' end,
    post_name=concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')),
	post_modified=now(), 
	post_modified_gmt=now(),
	guid=concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/')
    where id=     v_postid ;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> updated post: ',v_postid,' lang:',p_lang));	end if;

	update import_pim
	set json=p_post_json,
	title=ifnull(v_post_title,''),
	subtitle=ifnull(v_post_excerpt,''),
	product_type=v_product_type,
	maintext=ifnull(v_post_content,''),
	category=v_category,
	subcategory=v_subcategory,
	wp_id=v_postid
	where id=v_pim_id;
    
end;
else
begin

	select max(id)+1 into v_postid from cportal.ws_posts; 
    
	insert into cportal.ws_posts
	select v_postid as ID,1 as post_author, now() as post_date,
	now() as post_date_gmt, 
	ifnull(v_post_content,'') as post_content,
	ifnull(v_post_title,'') post_title,
	ifnull(v_post_excerpt,'') as post_excerpt,
	case when v_status='Enabled' then 'publish' else 'draft' end as post_status,
	'open' as comment_status,
	'closed' as ping_status,
	'' as post_password,
    concat(v_postid,'-',replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(',''),')',''),'+',''),'--','')) as post_name,
	'' as to_ping,
	'' as pinged,
	now() as post_modified, 
	now() as post_modified_gmt,
	'' as post_content_filtered,
	0 as post_parent,
    concat('/product/',LOWER(replace(replace(replace(replace(replace(ifnull(v_post_title,''),' ','-'),'(','-'),')','-'),'+',''),'--','')),'-',v_pim_id,'/') as guid,
	0 as menu_order,
	'product' as post_type,
	'' as post_mime_type,
	0 as comment_count;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> inserted new post: ',v_postid)); end if;

	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	
	insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;    

end;
end if;


select fn_AddPostMetaValue_v10(v_postid,'_sku',v_sku,v_log) into v_meta_id;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Define translation lang:',p_lang)); end if;


if p_lang='en' then
begin

set v_count_trans=0;
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid group by trid limit 1; 


	if v_log=1 then    insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Set translation relation - >','en post:',ifnull(v_postid,''),' trid:',ifnull(v_trid_main_post,''),' count:',ifnull(v_count_trans,''),' lang:',p_lang)); end if;    

	if v_count_trans =0 then
    begin
		select max(trid) + 1 into v_next_trid from cportal.ws_icl_translations;
        
		insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
		values('post_product',v_postid,  v_next_trid, p_lang,case when p_lang='en' then null else 'en' end);
        end;
	end if;
end;
else
begin
	
	select count(*),trid into v_count_trans,v_trid_main_post  from cportal.ws_icl_translations 
	where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = p_parent_id  group by trid limit 1; 
        
	if v_count_trans >0 then  
    begin
		
		if(select count(*)  from cportal.ws_icl_translations where element_type = 'post_product'  collate utf8mb4_unicode_ci and element_id = v_postid and trid=v_trid_main_post and language_code=p_lang collate utf8mb4_unicode_ci)=0 then
			begin
				insert into cportal.ws_icl_translations (element_type, element_id,trid, language_code,source_language_code)
				values('post_product',v_postid,  v_trid_main_post, p_lang,'en');
			end;
			end if;
	end;
    end if;
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Translation defined')); end if;



delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_cat' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_group' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_line' and r.object_id=v_postid;
delete r from cportal.ws_term_relationships r 
inner join cportal.ws_term_taxonomy tx on tx.term_taxonomy_id=r.term_taxonomy_id
inner join cportal.ws_terms t on t.term_id=tx.term_id where taxonomy='product_custom_type' and r.object_id=v_postid;



if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> fn_CreateMetaDataPost_v11 --> p_post_json:',p_post_json,' p_integration_number:',p_integration_number)); end if;
	select fn_CreateMetaDataPost_v11(p_post_json,'catthumb',0,p_integration_number ,v_log) into v_category_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subcatthumb',0,p_integration_number ,v_log) into v_subcategory_thumb;
    select fn_CreateMetaDataPost_v11(p_post_json,'subsubcatthumb',0,p_integration_number ,v_log) into v_subsubcategory_thumb;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> creating category:',p_post_json)); end if;

select fn_CreateCategoryTerm_v3(
json_extract( 
json_extract( 
p_post_json 
,'$.subcategory')
,'$.category')
,'product',p_lang,v_postid,0,null,null,p_integration_number,v_log) into v_catid;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> Category created:',v_catid)); end if;


select ifnull(t.trid,0) as trid into v_category_trid
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id
inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
left join cportal.ws_icl_translations t on t.element_id=tx.term_id
where tx.taxonomy ='product_cat' and ter.term_id=v_catid collate utf8mb4_unicode_ci   collate utf8mb4_unicode_ci and icl.language_code='en' collate utf8mb4_unicode_ci   and icl.element_type='tax_product_cat'; 


select fn_CreateCategoryTerm_v3(
json_extract( 
p_post_json 
,'$.subcategory')
,'product_group',p_lang,v_postid,0,v_subcategory_thumb,v_catid,p_integration_number,v_log) into v_subcatid;


select ifnull(t.trid,0) as trid into v_subcategory_trid
from cportal.ws_term_taxonomy tx 
inner join cportal.ws_terms ter on ter.term_id=tx.term_id
inner join cportal.ws_icl_translations icl on  icl.element_id=ter.term_id
left join cportal.ws_icl_translations t on t.element_id=tx.term_id
where tx.taxonomy ='product_group' and ter.term_id=v_subcatid collate utf8mb4_unicode_ci   collate utf8mb4_unicode_ci and icl.language_code='en' collate utf8mb4_unicode_ci   and icl.element_type='tax_product_cat'; 


select fn_CreateOrReplacePostType_v1(v_postid,'material',p_post_json,p_lang,p_integration_number ,v_log ) into v_materialid;
select fn_AddPostMetaValue_v10(v_postid,'_product_material','field_6576e42f77d86',v_log) into v_meta_id;
select fn_AddPostMetaValue_v10(v_postid,'product_material',v_materialid,v_log) into v_meta_id;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> dash number:',JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')))); end if;
if JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')) is not null then
	select import_gi.fn_SerializeDashNumber(JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.name')),v_log) into v_meta_id;
end if;


select fn_AddPostMetaValue_v10(v_postid,'_product_type','field_655f1073564e7',v_log) into v_meta_id; 
select fn_AddPostMetaValue_v10(v_postid,'product_type','a:1:{i:0;s:13:"Standard Size";}',v_log) into v_meta_id; 
		


select JSON_LENGTH(JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.translation'))) into v_dashtranscount;
set v_count=0;
while v_count<v_dashtranscount do
begin

	select json_extract(json_extract(json_extract(json_extract(p_post_json,'$[0]'),'$.dash_number'),'$.translation'),concat('$[',v_count,']')) into v_dashtrans;	
    select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.language_id')) into v_match;
    if ( v_match = v_lang) then 
		select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.property')) into v_prop_trans;
        set v_prop_trans_value=null;
        select JSON_UNQUOTE(json_extract(v_dashtrans	,'$.translation_value')) into v_prop_trans_value;
        
        if nullif(v_prop_trans_value,'') is not null then
        
        if v_prop_trans='mmid'  then select fn_AddPostMetaValue_v10(v_postid,'milimeters_id',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmidtol' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_id_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmw' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_width',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='mmwtol' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_width_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizeid' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_id',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizeod' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_od',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='nominalsizew' then select fn_AddPostMetaValue_v10(v_postid,'nominal_size_width',v_prop_trans_value,v_log) into v_meta_id; end if;
        if v_prop_trans='inchesid' then select fn_AddPostMetaValue_v10(v_postid,'inches_id',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='inchesidtol' then select fn_AddPostMetaValue_v10(v_postid,'inches_id_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='inchesw' then select fn_AddPostMetaValue_v10(v_postid,'inches_width',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='incheswtol' then select fn_AddPostMetaValue_v10(v_postid,'inches_width_tol',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgn' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgn',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spge' then select fn_AddPostMetaValue_v10(v_postid,'weight_spge',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgf' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgf',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgcr' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgcr',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='spgvmq' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgvmq',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;
        if v_prop_trans='weight_spgf' then select fn_AddPostMetaValue_v10(v_postid,'weight_spgf',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;    
        if v_prop_trans='inchesod' then select fn_AddPostMetaValue_v10(v_postid,'inches_od',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;  
        if v_prop_trans='mmod' then select fn_AddPostMetaValue_v10(v_postid,'milimeters_od',cast(v_prop_trans_value as decimal(10,3)),v_log) into v_meta_id; end if;  
end if;
        
    end if;    
	set v_count=v_count+1;
end;
end while;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> dash number created:',v_meta_id)); end if;



select 
json_length( 
json_extract( 
p_post_json 
,'$.producttypes')) into v_producttypecount; 

set v_count=0;
while v_count<v_producttypecount do
	select json_extract( 
	json_extract( 
	p_post_json 
	,'$.producttypes'),concat('$[',v_count,']')) into v_producttypejson;

	select fn_CreateOrReplaceProductType (v_postid ,v_producttypejson,v_subcatid,v_materialid,v_count=0,p_lang,p_integration_number,v_log) into v_catid;

	set v_count=v_count+1;
end while;



select fn_CreateOrReplacePostType_v1(v_postid,'compound',p_post_json,p_lang,p_integration_number ,v_log ) into v_materialid;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> set compound:',json_extract(json_extract(p_post_json,concat('$[',0,']')),'$.compound'))); end if;

select fn_CreateCategoryTerm_v3(

p_post_json

,'compound',p_lang,v_postid,0,null,null,p_integration_number,v_log) into v_compoundid;




if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> set product type:',v_product_type)); end if;


call sp_createtaxonomy_v10('simple','product_type',v_postid,v_log);


	set v_temp_counter=0;
	select count(1) into v_temp_counter from cportal.ws_postmeta  where post_id=v_postid and meta_key='hide_product';
	if v_temp_counter>0 then
	
		update cportal.ws_postmeta set meta_value='No' where post_id=v_postid and meta_key='hide_product';
	else
	
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'_hide_product','field_62aad9efa1f05'  from cportal.ws_postmeta;  
		insert into cportal.ws_postmeta select max(meta_id)+1,v_postid,'hide_product','No' from cportal.ws_postmeta;
	 end if;


if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> inserting content technical detail ','')); end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> start dynamic field: ')); end if;       
set v_prop_counter=0;
set cur_finished=0;

 if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> starting props cursor p_post_json:',p_post_json)); end if;

open props_cursor;
props_loop: loop
fetch props_cursor into v_dynamic_field,v_wpnamme,v_pimname,v_wpfield,v_wp_fieldmap;
if cur_finished then leave props_loop; end if;

	
	select fn_GetProperty_v10(p_post_json,v_pimname,p_lang,v_log) into v_prop_trans;
    
    if nullif(v_prop_trans,'') is not null then
		select fn_AddPostMetaValue_v10(v_postid,v_wpnamme,v_prop_trans,v_log) into v_meta_id;
    end if;
    
    if (v_wpfield is not null) then
		select fn_AddPostMetaValue_v10(v_postid,v_wpfield,v_wp_fieldmap,v_log) into v_meta_id;
    end if;
    
    if (v_dynamic_field=1 and nullif(v_prop_trans,'') is not null  ) then
		if (v_pimname='COMPOUND COLOUR') then 
			select fn_AddPostMetaValue_v10(v_postid,'_product_colour','field_655f107b564e8',v_log) into v_meta_id;
            select fn_AddPostMetaValue_v10(v_postid,'product_colour',concat('a:1:{i:0;s:',length(v_prop_trans),':"',v_prop_trans,'";}') ,v_log) into v_meta_id;
        end if;
		if (v_pimname='COMPLIANT') then 
			call `sp_AddComplianceToProduct`(v_postid ,v_prop_trans ,v_log );            
        end if;          
		select fn_AddPostMetaValue_v10(v_postid,replace('product_dynamic_fields_#_label','#',v_prop_counter),v_pimname,v_log) into v_meta_id;
		select fn_AddPostMetaValue_v10(v_postid,replace('product_dynamic_fields_#_value','#',v_prop_counter),v_prop_trans,v_log) into v_meta_id;
        
        select fn_AddPostMetaValue_v10(v_postid,replace('_product_dynamic_fields_#_label','#',v_prop_counter),'field_6576e4014aa7b',v_log) into v_meta_id;
		select fn_AddPostMetaValue_v10(v_postid,replace('_product_dynamic_fields_#_value','#',v_prop_counter),'field_6576e4174aa7c',v_log) into v_meta_id;
               
		if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> added dynamic field: ',v_pimname)); end if;        
		set v_prop_counter=v_prop_counter+1;
    end if;
end loop;

close props_cursor;

select fn_AddPostMetaValue_v10(v_postid,'product_dynamic_fields',v_prop_counter,v_log) into v_meta_id;
select fn_AddPostMetaValue_v10(v_postid,'_product_dynamic_fields','field_6576e3d24aa7a',v_log) into v_meta_id;



if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> end dynamic field: ',v_prop_counter,' v_postid:',v_postid)); end if;     



select JSON_UNQUOTE(json_extract( json_extract(json_extract(p_post_json ,concat('$[',0,']')),'$.dash_number') , '$.name')) into v_dash; 
select fn_AddPostMetaValue_v10(v_postid,'product_dash_number',v_dash,v_log) into v_meta_id;



insert into cportal.ws_postmeta (post_id,meta_key,meta_value)
select v_postid,wp_name,value from import_gi.pim_wp_mapping m
left join cportal.ws_postmeta pm on pm.meta_key=m.wp_name and pm.post_id=v_postid
where wp_table='ws_postmeta' and description='field mapping' and wp_area='Post Property' and use_main_product=1 and pm.meta_id is null;

if (v_product_type='Bundle') then
begin
set v_price=ifnull(v_price,1000);
	if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> inserting price:',v_price)); end if;

	insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_regular_price',v_price);
    
    set v_sku=fn_GetProperty_v10(p_post_json,'Product Code',p_lang,v_log);
    insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_sku',v_sku);
    
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.bundle_products')) into v_variations ;
	set v_count_var=json_length(v_variations);
    set v_counter=0;
    
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
        set v_current_var=json_extract(v_current_var,concat('$.product'));   
if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12-> post: ',v_current_var)); end if;        
        select getTechnicalDetailsProperties_v4(v_current_var,p_lang,v_postid) into v_props;
        set v_techdetails=concat(v_techdetails,v_props);
        set v_counter=v_counter+1;
	end while;
    select fn_AddPostMetaValue_v10(v_postid,'content_technical_detail',v_techdetails,v_log) into v_meta_id;
    
end;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_CreateMainPost_v12 -> #################### set product as imported for further updates')); end if;

insert into imported_ids(pimid,postid,dt,lang) values(v_pim_id,v_postid,now(),p_lang);
set postid=v_postid;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_sp_IterateLangs_v10`(p_post_json longtext,p_integration_number int,v_log bit )
BEGIN


declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type,v_price varchar(100);
declare v_post_title text; 
declare v_subcategory,v_category,v_status,v_current_var text;
declare v_variations longtext;

declare v_term_taxonomy_id,v_term_id,v_parent int;
declare v_next_trid,v_count_trans,v_count_var int;

declare v_str text;
declare v_pim_id bigint;
declare v_total,v_counter,v_counter_images,v_total_images,v_temp_counter int;
declare v_img_list varchar(1000);

DECLARE v_end_cur INTEGER DEFAULT 0;
declare v_aux int;
declare v_meta_id bigint;

declare v_work_lang,v_file_lang varchar(5);
DECLARE finished INTEGER DEFAULT 0;

DECLARE lang_cursor CURSOR FOR select code from  languages order by id;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_IterateLangs_v10 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

set v_work_lang='en';
set v_total=0;
set v_counter=0;
set v_post_excerpt='';

set finished=0;
set v_parent=0;

open lang_cursor;

loop_lang: LOOP

fetch lang_cursor into v_work_lang;



if finished =1 then 
	leave loop_lang;
end if;



begin
	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> create main post lang:',v_work_lang,' parent:',v_parent,' json: ',p_post_json)); end if;
	
	call sp_CreateMainPost_v10(p_post_json,v_parent,v_work_lang,v_log,@postid) ;

	set v_postid=@postid;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10 -> post created: ',v_postid,' lang:',v_work_lang)); end if;
end;


begin 

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10 -> starting variations')); end if;

	
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.variations')) into v_variations ;

	set v_count_var=json_length(v_variations);
	set v_counter=0;
    

    
begin
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10 -> variation nº',v_counter,' -- var:',v_current_var,' post:',v_postid,' lang:',v_work_lang)); end if;
			
        call sp_CreateAttribute_v10(v_current_var,v_postid , v_work_lang , v_log );
        
        set v_counter= v_counter+1;
	end while;
end;




delete p,icl,pm from cportal.ws_posts p
inner join cportal.ws_icl_translations icl on icl.element_id=p.id
inner join cportal.ws_postmeta pm on pm.post_id=p.id
where post_type='product_variation' and post_parent=v_postid;



set v_counter=0;
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));
        select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 
        
        set v_price=ifnull(v_price,0);
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10 -> create variation -> v_postid:',v_postid,' v_price:',v_price,' v_work_lang:',v_work_lang)); end if;
        
 		call sp_CreatePostVariation_v10(v_current_var ,v_postid , v_work_lang,v_price , v_log,@res );

		
        delete from cportal.ws_postmeta where post_id=@res and meta_key='_thumbnail_id';
		select fn_CreateMetaDataPost_v10(v_current_var,'thumb',@res,p_integration_number,v_log) into v_thumbid;
		insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(@res,'_thumbnail_id',v_thumbid);        
        
        set v_counter=v_counter+1;
	end while;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10 -> finished variations')); end if;
end;


if v_work_lang='en' then 
	set v_parent=v_postid;
end if;

 if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> Insert Thumbnail post:',v_postid)); end if;
 

select fn_CreateMetaDataPost_v10(p_post_json,'thumb',v_postid,p_integration_number,v_log) into v_thumbid;
insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_thumbnail_id',v_thumbid);

if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> Thumbnail post:',v_thumbid)); end if;



if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> Insert Images post:',v_postid));
end if;
	select json_length(json_extract(p_post_json,'$.images')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
	while v_counter_images<v_total_images do
		
		select fn_CreateMetaDataPost_v10(json_extract(json_extract(p_post_json,'$.images'),concat('$[',v_counter_images,']')),'images',v_postid,p_integration_number,v_log) into v_thumbid; 
		
		
        set v_img_list=concat(v_img_list,CAST(v_thumbid as char(10)),',') ;
        set v_counter_images=v_counter_images+1;
        
    end while;    

    if( length(v_img_list)>0) then
    begin
    
		set v_img_list=SUBSTRING(v_img_list,     1,    CHAR_LENGTH(v_img_list) - 1);
		
        delete from cportal.ws_postmeta where post_id=v_postid and meta_key='_product_image_gallery';
		select add_post_meta_value_v4(v_postid,'_product_image_gallery',v_img_list)  into v_meta_id;
		
    end;
    end if;


select json_length(json_extract(p_post_json,'$.files')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
	while v_counter_images<v_total_images do

		select fn_CreateMetaDataPost_v10(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'files',v_postid,p_integration_number,v_log) into v_thumbid; 
		select add_post_meta_value_v4(v_postid,concat('pdp_document_0_document_',v_counter_images,'_file'),v_thumbid) into v_meta_id;



insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> p_post_json:',p_post_json));
		select code into v_file_lang  from languages where id=json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_language_id');
        insert into temp_debug(value) values(concat('sp_IterateLangs_v10-> lang=:',v_file_lang));
        select add_post_meta_value_v4(v_postid,concat('pdp_document_0_document_',v_counter_images,'_language'),upper(v_file_lang)) into v_meta_id;




	if v_counter_images=0 then
        select add_post_meta_value_v4(v_postid,'pdp_document_0_title',
        JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_dsc'))) into v_meta_id;
end if;
        
        set v_counter_images=v_counter_images+1;
        
    end while;    
    
    if v_counter_images>0 then    
		select add_post_meta_value_v4(v_postid,'pdp_document_0_document',v_counter_images) into v_meta_id;
        
        select add_post_meta_value_v4(v_postid,'pdp_document',v_counter_images) into v_meta_id;
	end if;


    


 

 





insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_main_product=1 and wp_pm.post_id is null; 


 delete from v_tbl_attributes;
end loop loop_lang;

close lang_cursor;


END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_sp_IterateLangs_v11`(p_post_json longtext,p_integration_number int,v_log bit )
BEGIN


declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type,v_price varchar(100);
declare v_post_title text; 
declare v_subcategory,v_category,v_status,v_current_var text;
declare v_variations longtext;

declare v_term_taxonomy_id,v_term_id,v_parent int;
declare v_next_trid,v_count_trans,v_count_var int;

declare v_str text;
declare v_pim_id bigint;
declare v_total,v_counter,v_counter_images,v_total_images,v_temp_counter int;
declare v_img_list varchar(1000);

DECLARE v_end_cur INTEGER DEFAULT 0;
declare v_aux int;
declare v_meta_id bigint;

declare v_work_lang,v_file_lang varchar(5);
DECLARE v_finished INTEGER DEFAULT 0;

declare v_count_file_dsc bigint;
declare v_file_dsc,v_file_metakey varchar(100);

DECLARE lang_cursor CURSOR FOR select code from  languages order by id;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_IterateLangs_v11 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

set v_work_lang='en';
set v_total=0;
set v_counter=0;
set v_post_excerpt='';

set v_finished=0;
set v_parent=0;

open lang_cursor;

loop_lang: LOOP

fetch lang_cursor into v_work_lang;

if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> v_work_lang:',v_work_lang,' v_finished:',v_finished)); end if;

if v_finished =1 then 
	leave loop_lang;
end if;



begin
	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> create main post lang:',v_work_lang,' parent:',v_parent,' json: ',p_post_json)); end if;
	
	call sp_CreateMainPost_v11(p_post_json,v_parent,v_work_lang,p_integration_number,v_log,@postid) ;

	set v_postid=@postid;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 -> post created: ',v_postid,' lang:',v_work_lang)); end if;
end;


begin 

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 -> starting variations')); end if;

	
	select JSON_UNQUOTE(json_extract(json_extract(p_post_json,'$[0]') ,'$.variations')) into v_variations ;

	set v_count_var=json_length(v_variations);
	set v_counter=0;
    

    
	begin
		
		while v_counter<v_count_var do
		
			set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));   
			if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 -> variation nº',v_counter,' -- var:',v_current_var,' post:',v_postid,' lang:',v_work_lang)); end if;
				
			call sp_CreateAttribute_v10(v_current_var,v_postid , v_work_lang , v_log );
			
			set v_counter= v_counter+1;
		end while;
	end;

	
	delete p,icl,pm from cportal.ws_posts p
	inner join cportal.ws_icl_translations icl on icl.element_id=p.id
	inner join cportal.ws_postmeta pm on pm.post_id=p.id
	where post_type='product_variation' and post_parent=v_postid;

	set v_counter=0;
	
	while v_counter<v_count_var do
	
		set v_current_var=json_extract(v_variations,concat('$[',v_counter,']'));
        select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.prices'),'$[0]'),'$.price')) into v_price; 
        
        set v_price=ifnull(v_price,0);
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 -> create variation -> v_postid:',v_postid,' v_price:',v_price,' v_work_lang:',v_work_lang)); end if;
        
 		call sp_CreatePostVariation_v10(v_current_var ,v_postid , v_work_lang,v_price , v_log,@res );

		
        delete from cportal.ws_postmeta where post_id=@res and meta_key='_thumbnail_id';
		select fn_CreateMetaDataPost_v10(v_current_var,'thumb',@res,p_integration_number,v_log) into v_thumbid;
		insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(@res,'_thumbnail_id',v_thumbid);        
        
        set v_counter=v_counter+1;
	end while;

	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 -> v_finished variations')); end if;
end;


if v_work_lang='en' then 
	set v_parent=v_postid;
end if;

 if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> Insert Thumbnail post:',v_postid)); end if;
 

select fn_CreateMetaDataPost_v10(p_post_json,'thumb',v_postid,p_integration_number,v_log) into v_thumbid;
insert into cportal.ws_postmeta(post_id,meta_key,meta_value) values(v_postid,'_thumbnail_id',v_thumbid);

if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> Thumbnail post:',v_thumbid)); end if;



	if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> Insert Images post:',v_postid)); end if;

	select json_length(json_extract(p_post_json,'$.images')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
	while v_counter_images<v_total_images do
		
		select fn_CreateMetaDataPost_v10(json_extract(json_extract(p_post_json,'$.images'),concat('$[',v_counter_images,']')),'images',v_postid,p_integration_number,v_log) into v_thumbid; 
        
		set v_img_list=concat(v_img_list,CAST(v_thumbid as char(10)),',') ;
        set v_counter_images=v_counter_images+1;
        
    end while;    

    if( length(v_img_list)>0) then
    begin
    
		set v_img_list=SUBSTRING(v_img_list,     1,    CHAR_LENGTH(v_img_list) - 1);
		
        delete from cportal.ws_postmeta where post_id=v_postid and meta_key='_product_image_gallery';
		select add_post_meta_value_v4(v_postid,'_product_image_gallery',v_img_list)  into v_meta_id;
		
    end;
    end if;


	select json_length(json_extract(p_post_json,'$.files')) into v_total_images; 
	set v_counter_images=0;
	set v_img_list='';
	while v_counter_images<v_total_images do

		select fn_CreateMetaDataPost_v10(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'files',v_postid,p_integration_number,v_log) into v_thumbid; 
        
        set v_file_metakey =null;
        select JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_dsc')) into v_file_dsc;
               
        select meta_key into v_file_metakey from cportal.ws_postmeta  where meta_key like 'pdp_document_%' and meta_value=v_file_dsc and post_id=v_postid;
        set v_file_metakey = left(v_file_metakey ,length(v_file_metakey )-5);
        
        if  v_file_metakey is null then 
			select count(1) into v_count_file_dsc from cportal.ws_postmeta where post_id=v_postid and meta_key like 'pdp_document%_title';
			set v_file_metakey=concat('pdp_document_',v_count_file_dsc,'_');
            select add_post_meta_value_v4(v_postid,concat(v_file_metakey,'title'), JSON_UNQUOTE(json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_dsc'))) into v_meta_id;
            select add_post_meta_value_v4(v_postid,'pdp_document',v_count_file_dsc+1) into v_meta_id; 
        end if;
        
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> v_file_dsc=',v_file_dsc)); end if;
                
        select count(1) into v_count_file_dsc from cportal.ws_postmeta  where meta_key like concat(v_file_metakey,'%','_file') and post_id=v_postid; 
        
        if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11 - ###############################################-> v_file_metakey=',v_file_metakey,' v_count_file_dsc pos:',v_count_file_dsc,' v_file_dsc=',v_file_dsc)); end if;
        
        select add_post_meta_value_v4(v_postid,concat(v_file_metakey,'document_',v_count_file_dsc,'_file'),v_thumbid) into v_meta_id;
        
 		if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> p_post_json:',p_post_json)); end if;
		
        select code into v_file_lang  from languages where id=json_extract(json_extract(json_extract(p_post_json,'$.files'),concat('$[',v_counter_images,']')),'$.file_language_id');

        select add_post_meta_value_v4(v_postid,concat(v_file_metakey,'document_',v_count_file_dsc,'_language'),upper(v_file_lang)) into v_meta_id;
  
        set v_counter_images=v_counter_images+1;
        
        
		select add_post_meta_value_v4(v_postid,concat(v_file_metakey,'document'),v_count_file_dsc+1) into v_meta_id;        
        
    
    end while;   


    


 

 





insert into cportal.ws_postmeta(post_id,meta_key,meta_value) 
select v_postid,wp_name,value from pim_wp_mapping pm
left join cportal.ws_postmeta wp_pm on wp_pm.meta_key=pm.wp_name  collate utf8mb4_unicode_ci  and wp_pm.post_id=v_postid
 where wp_table='wp_postmeta' and use_main_product=1 and wp_pm.post_id is null; 


 delete from v_tbl_attributes;
 
 if v_log=1 then insert into temp_debug(value) values(concat('sp_IterateLangs_v11-> Loop end postid:',v_postid,' lang:',v_work_lang)); end if;


set v_finished=0;

end loop loop_lang;

close lang_cursor;


END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_sp_iterate_langs_v6`(p_post_json longtext,v_log bit )
BEGIN


declare v_postid,v_thumbid bigint;
declare v_post_content text;
declare v_post_excerpt text;
declare v_product_type varchar(100);
declare v_post_title text; 
declare v_subcategory,v_category,v_status text;

declare v_term_taxonomy_id,v_term_id,v_parent int;
declare v_next_trid,v_count_trans int;

declare v_str text;
declare v_pim_id bigint;
declare v_total,v_counter,v_counter_images,v_total_images,v_temp_counter int;
declare v_img_list varchar(1000);

DECLARE v_end_cur INTEGER DEFAULT 0;
declare v_aux int;
declare v_meta_id bigint;

declare v_work_lang varchar(5);
DECLARE finished INTEGER DEFAULT 0;

DECLARE lang_cursor CURSOR FOR select code from  languages order by id;

DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_iterate_langs_v6 : ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);

 END;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

set v_work_lang='en';
set v_total=0;
set v_counter=0;
set v_post_excerpt='';

set finished=0;
set v_parent=0;

open lang_cursor;

loop_lang: LOOP

fetch lang_cursor into v_work_lang;

if finished =1 then 
	leave loop_lang;
end if;

if v_log=1 then insert into temp_debug(value) values(concat('sp_iterate_langs_v6-> create main post lang:',v_work_lang,' parent:',v_parent,' json: ',p_post_json));
end if;

call createMainPost_v6(p_post_json,v_parent,v_work_lang,v_log,@postid) ;

set v_postid=@postid;

if v_log=1 then insert into temp_debug(value) values(concat('sp_iterate_langs_v6 -> post created: ',v_postid,' lang:',v_work_lang));
end if;

if v_work_lang='en' then 
	set v_parent=v_postid;
end if;





    





end loop loop_lang;

close lang_cursor;


END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `zz_sp_migrate_data_from_pim_v10`(v_log bit)
BEGIN

declare v_total,v_counter int;
declare v_str,v_post_json longtext;
declare v_integration_number int;


DECLARE exit handler for SQLEXCEPTION
 BEGIN
  GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, 
   @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
  SET @full_error = CONCAT("ERROR procedure sp_migrate_data_from_pim_v10: ", @errno, " (", @sqlstate, "): ", @text);
  insert into temp_debug(value) values(@full_error);
 END;

set v_total=0;
set v_counter=0;

select ifnull(max(integration_id),0)+1 into v_integration_number from files_to_transfer;


select i.jsonstring into v_str from import i order by dt desc limit 1;

set v_total=json_length(v_str);

while v_counter<v_total do
	
	select json_extract(v_str,concat('$[',v_counter,']')) into v_post_json;
	
	if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v10-> iterating posts -> post:',v_post_json ));
    end if;
     call sp_IterateLangs_v11(v_post_json,v_integration_number,v_log );
    
	set v_counter=v_counter+1;
    
    if v_log=1 then 
    insert into import_gi.temp_debug(value) values(concat('sp_migrate_data_from_pim_v10 -> imported '));
    end if;
end while;

END$$
DELIMITER ;
