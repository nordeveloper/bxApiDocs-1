<?php

$ajaxActions = Array(
	"checkout"=>array(
		"json"=>true,
		"file"=> $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/checkout.php",
		"no_check_auth"=>true
	),
	"service"=>array(
		"json"=>true,
		"file"=> $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/service.php",
		"no_check_auth"=>true
	),
	"list"=>array(
		"json"=>true,
		"file"=> $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/service.php",
		"no_check_auth"=>true
	),
	"get_captcha"=>array(
		"json"=>false,
		"file"=> $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/captcha.php",
		"no_check_auth"=>true
	),
	"save_device_token"=>array(
		"json" => true,
		"needBitrixSessid"=>true,
		"file"=> $_SERVER["DOCUMENT_ROOT"] ."/bitrix/components/bitrix/mobile.data/actions/save_device_token.php"
	),
	"get_user_list" => array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/users_groups.php"
	),
	"get_group_list"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/users_groups.php"
	),
	"get_usergroup_list"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/users_groups.php"
	),
	'get_subordinated_user_list'=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/users_subordinates.php"
	),
	"get_likes"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/get_likes.php"
	),
	"logout"=> array(
		"file" => ""
	),
	"calendar"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/calendar.php"
	),
	"calendar_livefeed_view"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/calendar.livefeed.view/action.php"
	),
	"like"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/rating.vote/vote.ajax.php"
	),
	"pull"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/pull.request/ajax.php",
	),
	"im"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/im.messenger/im.ajax.php",
	),
	"im_files"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/im.messenger/".($_REQUEST["fileType"] == 'show'? 'show.file.php': 'download.file.php'),
		"json"=>false
	),
	"im_answer"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/im_answer.php"
	),
	"calls"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/im.messenger/im.ajax.php",
	),
	"task_ajax"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/tasks.base/ajax.php",
	),
	"bitrix24_ajax"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/bitrix24.business.tools.info/templates/.default/ajax.php",
	),
	"task_answer"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/task_answer.php",
	),
	"change_follow"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.ex/ajax.php"
	),
	"change_follow_default"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.ex/ajax.php"
	),
	"change_expert_mode"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.ex/ajax.php"
	),
	"change_favorites"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.ex/ajax.php"
	),
	"log_error"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.ex/ajax.php"
	),
	"get_more_destination"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"add_comment"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"edit_comment"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"delete_comment"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"get_comment"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"get_comments"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"delete_post"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/socialnetwork.blog.post/mobile/ajax.php"
	),
	"read_post"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/socialnetwork.blog.post/mobile/ajax.php"
	),
	"get_blog_post_data"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/socialnetwork.blog.post/mobile/ajax.php"
	),
	"get_blog_comment_data"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/socialnetwork.blog.post/mobile/ajax.php"
	),
	"get_log_comment_data"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"comment_activity"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/main.post.list/activity.php"
	),
	"mobile_grid_sort"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.interface.sort/ajax.php"
	),
	"mobile_grid_fields"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.interface.fields/ajax.php"
	),
	"mobile_grid_filter"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.interface.filter/ajax.php"
	),
	"crm_activity_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.activity.edit/ajax.php"
	),
	"crm_contact_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.contact.edit/ajax.php"
	),
	"crm_lead_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.lead.edit/ajax.php"
	),
	"crm_product_row_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.product_row.edit/ajax.php"
	),
	"crm_company_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.company.edit/ajax.php"
	),
	"crm_config_user_email"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.config.user_email/ajax.php"
	),
	"crm_deal_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.deal.edit/ajax.php"
	),
	"crm_deal_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.deal.list/ajax.php"
	),
	"crm_invoice_edit"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.invoice.edit/ajax.php"
	),
	"crm_location_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.location.list/ajax.php"
	),
	"crm_product_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.product.list/ajax.php"
	),
	"disk_folder_list"=> array(
		"json" => true,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/disk_folder_list.php",
	),
	"disk_uf_view"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/disk/uf.php",
	),
	"disk_download_file"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/services/disk/index.php",
	),
	"blog_image"=> array(
		"json" => false,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/blog/show_file.php"
	),
	"calendar_livefeed"=> array(
		"json" => false,
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/calendar.livefeed.view/action.php"
	),
	"file_upload_log"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"file_upload_blog"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/socialnetwork.blog.post/mobile/ajax.php"
	),
	"send_comment_writing"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php"
	),
	"bp_make_action"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/mobile_app/components/bitrix/bizproc.task/mobile/ajax.php"
	),
	"bp_livefeed_action"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/bizproc.workflow.livefeed/ajax.php"
	),
	"bp_do_task"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/bizproc_do_task_ajax.php"
	),
	"bp_show_file"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/bizproc_show_file.php"
	),
	"mobile_crm_lead_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.lead.list/ajax.php"
	),
	"mobile_crm_lead_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_lead.php"
	),
	"mobile_crm_deal_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.deal.list/ajax.php"
	),
	"mobile_crm_deal_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_deal.php"
	),
	"mobile_crm_invoice_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.invoice.list/ajax.php"
	),
	"mobile_crm_invoice_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_invoice.php"
	),
	"mobile_crm_contact_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.contact.list/ajax.php"
	),
	"mobile_crm_contact_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_contact.php"
	),
	"mobile_crm_company_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.company.list/ajax.php"
	),
	"mobile_crm_company_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_company.php"
	),
	"mobile_crm_quote_list"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.crm.quote.list/ajax.php"
	),
	"mobile_crm_quote_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_quote.php"
	),
	"mobile_crm_product_actions"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/mobile_crm_product.php"
	),
	"get_raw_data"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php'
	),
	"create_task_comment"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php'
	),
	"timeman"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . '/bitrix/tools/timeman.php'
	),
	"bizcard"=>array(
		"file"=> $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/mobile.data/actions/bizcard.php",
		"json"=> false
	),
	"vote"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . '/bitrix/tools/vote/uf.php'
	),
	"set_content_view"=> array(
		"file" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/sonet_set_content_view.php"
	),
);

return $ajaxActions;