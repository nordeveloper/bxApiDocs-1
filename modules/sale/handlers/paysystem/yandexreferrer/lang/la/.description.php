<?
$MESS["SALE_HPS_YANDEX"] = "Yandex.Checkout";
$MESS["SALE_HPS_YANDEX_SHOP_ID"] = "Identificador de tienda en el sistema de cobro de pagos (ShopID)";
$MESS["SALE_HPS_YANDEX_SHOP_ID_DESC"] = "ID del Shop Yandex";
$MESS["SALE_HPS_YANDEX_PAYMENT_ID"] = "# de pago";
$MESS["SALE_HPS_YANDEX_SHOP_KEY"] = "Contraseña de la tienda";
$MESS["SALE_HPS_YANDEX_SHOULD_PAY"] = "Total de la orden";
$MESS["SALE_HPS_YANDEX_SCID"] = "Identificador del mostrador del sistema de cobro (scid)";
$MESS["SALE_HPS_YANDEX_SCID_DESC"] = "Identificador del mostrador del sistema de cobro (scid)";
$MESS["SALE_HPS_YANDEX_SHOP_KEY_DESC"] = "Contraseña de la tienda tal como se utiliza en Yandex";
$MESS["SALE_HPS_YANDEX_PAYMENT_DATE"] = "Pago creado el";
$MESS["SALE_HPS_YANDEX_IS_TEST"] = "Modo de prueba";
$MESS["SALE_HPS_YANDEX_CHANGE_STATUS_PAY"] = "Cambia automático el estado de la orden a pagado cuando se recibe el pago de forma exitosa.";
$MESS["SALE_HPS_YANDEX_PAYMENT_TYPE"] = "Tipo de sistema de pago";
$MESS["SALE_HPS_YANDEX_BUYER_ID"] = "ID del cliente";
$MESS["SALE_HPS_YANDEX_RETURN"] = "No se admiten devoluciones de cargo";
$MESS["SALE_HPS_YANDEX_COMMISSION"] = "Sin comisión";
$MESS["SALE_HPS_YANDEX_REFERRER"] = "<a href=\"https://money.yandex.ru/joinups/?source=bitrix24\" target=\"_blank\">Quick registration</a>";
$MESS["SALE_HPS_YANDEX_RESTRICTION"] = "La restricción del importe de pago es un tema del método de pago seleccionado por el cliente";
$MESS["SALE_HPS_YANDEX_DESCRIPTION"] = "Payment collector engine - <a href=\"https://kassa.yandex.ru\" target=\"_blank\">https://kassa.yandex.ru</a>
<br/>Using commonHTTP-3.0 protocol
<br/><br/>
<input
	id=\"https_check_button\"
	type=\"button\"
	value=\"HTTPS check\"
	title=\"Check if the site supports HTTPS. Required by payment system\"
	onclick=\"
		var checkHTTPS = function(){
			BX.showWait()
			var postData = {
				action: 'checkHttps',
				https_check: 'Y',
				lang: BX.message('LANGUAGE_ID'),
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: '/bitrix/admin/sale_pay_system_ajax.php',
				data: postData,

				onsuccess: function (result)
				{
					BX.closeWait();
					BX.removeClass(BX('https_check_result'), 'https_check_success');
					BX.removeClass(BX('https_check_result'), 'https_check_fail');

					BX('https_check_result').innerHTML = '&nbsp;' + result.CHECK_MESSAGE;
					if (result.CHECK_STATUS == 'OK')
						BX.addClass(BX('https_check_result'), 'https_check_success');
					else
						BX.addClass(BX('https_check_result'), 'https_check_fail');
				},
				onfailure : function()
				{
					BX.closeWait();
					BX.removeClass(BX('https_check_result'), 'https_check_success');

					BX('https_check_result').innerHTML = '&nbsp;' + BX.message('SALE_PS_YANDEX_ERROR');
					BX.addClass(BX('https_check_result'), 'https_check_fail');
				}
			});
		};
		checkHTTPS();\"
	/>
<span id=\"https_check_result\"></span>
<br/>
<br/>";
?>