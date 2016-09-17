$(document).ready(function(){
   $("#imgLoad").hide();  //Скрываем прелоадер
});
var num = 10; //чтобы знать с какой записи вытаскивать данные
$(function() {
   $("#load div").click(function(){ //Выполняем если по кнопке кликнули
   $("#imgLoad").show(); //Показываем прелоадер
   $.ajax({
          url: "../test/action.php",
          type: "GET",
          data: {"num": num},
          cache: false,
          success: function(response){
              if(response == 0){  // смотрим ответ от сервера и выполняем соответствующее действие
                 alert("Больше нет записей");
                 $("#imgLoad").hide();
              }else{
                 $("#content").append(response);
                 num = num + 10;
                 $("#imgLoad").hide();
              }
           }
        });
    });
});