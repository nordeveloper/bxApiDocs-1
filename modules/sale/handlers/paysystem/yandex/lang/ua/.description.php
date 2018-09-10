<?
$MESS["SALE_HPS_YANDEX"] = "Яндекс.Каса";
$MESS["SALE_HPS_YANDEX_SHOP_ID"] = "Ідентифікатор магазину в ЦПП (ShopID)";
$MESS["SALE_HPS_YANDEX_SHOP_KEY"] = "Пароль магазину";
$MESS["SALE_HPS_YANDEX_IS_TEST"] = "Тестовий режим";
$MESS["SALE_HPS_YANDEX_SHOP_ID_DESC"] = "Код магазину, який отриманий від Яндекс";
$MESS["SALE_HPS_YANDEX_SCID"] = "Номер вітрини магазину в ЦПП (scid)";
$MESS["SALE_HPS_YANDEX_SCID_DESC"] = "Номер вітрини магазину в ЦПП (scid)";
$MESS["SALE_HPS_YANDEX_CN"] = "Назва компанії(тільки латинські букви без пробілів)";
$MESS["SALE_HPS_YANDEX_CN_DESC"] = "Використовується для повернень";
$MESS["SALE_HPS_YANDEX_PAYMENT_ID"] = "Номер оплати";
$MESS["SALE_HPS_YANDEX_SHOP_KEY_DESC"] = "Пароль магазину на Яндекс";
$MESS["SALE_HPS_YANDEX_SHOULD_PAY"] = "Сума до оплати";
$MESS["SALE_HPS_YANDEX_PAYMENT_DATE"] = "Дата створення оплати";
$MESS["SALE_HPS_YANDEX_CHANGE_STATUS_PAY"] = "Автоматично оплачувати замовлення при отриманні успішного статусу оплати";
$MESS["SALE_HPS_YANDEX_PAYMENT_TYPE"] = "Тип платіжної системи";
$MESS["SALE_HPS_YANDEX_BUYER_ID"] = "Код покупця";
$MESS["SALE_HPS_YANDEX_RETURN"] = "Повернення платежів не підтримуються";
$MESS["SALE_HPS_YANDEX_RESTRICTION"] = "Обмеження по сумі платежів залежить від способу оплати, який вибере покупець";
$MESS["SALE_HPS_YANDEX_COMMISSION"] = "Без комісії для покупця";
$MESS["SALE_HPS_YANDEX_DESCRIPTION"] = "Робота через Центр Прийому Платежів <a href=\"https://kassa.yandex.ru\" target=\"_blank\">https://kassa.yandex.ru</a>
<br/>Використовується протокол commonHTTP-3.0
<br/><br/>
<input
 id=\"https_check_button\"
 type=\"button\"
 value=\"Перевірка HTTPS\"
 title=\"Перевірка доступності сайту по протоколу HTTPS. Необхідно для коректної роботи платіжної системи\"
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